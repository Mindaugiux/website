<?php
session_start();
include("connect.php");

// Default balance if the user has none in the database
$defaultBalance = 100;

// Fetch or initialize user balance and roulette spent
if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];
    $query = mysqli_query($conn, "SELECT balance, roulette_spent FROM users WHERE email = '$email'");
    if ($row = mysqli_fetch_assoc($query)) {
        $balance = $row['balance'];
        $rouletteSpent = $row['roulette_spent'];
    } else {
        $balance = $defaultBalance;
        $rouletteSpent = 0;
        mysqli_query($conn, "INSERT INTO users (email, balance, roulette_spent) VALUES ('$email', $defaultBalance, 0)");
    }
} else {
    header("Location: login.php");
    exit();
}

// Handle AJAX requests
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? null;

    if ($action === "placeBet") {
        $betAmount = intval($_POST['betAmount']);
        $selectedColor = $_POST['selectedColor'];

        if ($betAmount > $balance) {
            echo json_encode(["success" => false, "message" => "Insufficient funds!"]);
            exit();
        }

        $spinResult = determineSpinResult();
        $winnings = 0;

        // If green, multiply winnings by 14, otherwise, winnings match the bet amount
        if ($spinResult === $selectedColor) {
            $winnings = $spinResult === 'green' ? $betAmount * 14 : $betAmount;
            $balance += $winnings;
        } else {
            $balance -= $betAmount;
        }
        $rouletteSpent += $betAmount;

        mysqli_query($conn, "UPDATE users SET balance = $balance, roulette_spent = $rouletteSpent WHERE email = '$email'");

        echo json_encode([
            "success" => true,
            "balance" => $balance,
            "spinResult" => $spinResult,
            "winnings" => $winnings
        ]);
        exit();
    }
}

function determineSpinResult() {
    $colors = ['red', 'black', 'green'];
    $weights = [9, 9, 1]; // Odds for red and black: 9, green: 1
    $totalWeight = array_sum($weights);
    $randomNum = rand(0, $totalWeight - 1);

    $cumulativeWeight = 0;
    for ($i = 0; $i < count($colors); $i++) {
        $cumulativeWeight += $weights[$i];
        if ($randomNum < $cumulativeWeight) {
            return $colors[$i];
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Movie-Themed Roulette</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="rouletteStyle.css">
</head>
<body>
<header class="movie-header">
    <h1 class="movie-title">🎥 Roulette 🎥</h1>
</header>
<div class="container">
    <h2 class="subtitle">Spin the Wheel of Destiny!</h2>
    <div id="rouletteContainer" class="roulette-container">
        <div id="rouletteWheel" class="movie-roulette-wheel">
            <div class="wheel-section red"></div>
            <div class="wheel-section black"></div>
            <div class="wheel-section green"></div>
        </div>
        <div class="roulette-indicator"></div>
    </div>
    <p id="balance" class="movie-balance">Current Balance: $<?php echo number_format($balance, 2); ?></p>

    <label for="betAmount" class="movie-label">Enter Amount to Bet:</label>
    <input type="number" id="betAmount" class="movie-input" min="1" value="1">

    <p class="movie-label">Choose a Movie Color:</p>
    <div class="button-container">
        <button class="movie-button red-button" onclick="selectColor('red')">Bet on Red 🎬</button>
        <button class="movie-button black-button" onclick="selectColor('black')">Bet on Black 🎥</button>
        <button class="movie-button green-button" onclick="selectColor('green')">Bet on Green 🍿</button>
    </div>
    <input type="hidden" id="selectedColor">

    <br>
    <button id="roll" class="movie-roll-button" onclick="rollBet()">🎲 Roll</button>
    <p id="result" class="movie-result"></p>
    <a href="menu.php" class="movie-link">Back to Main Menu</a>
</div>

<script>
let balance = <?php echo $balance; ?>;
let selectedColor = '';

function selectColor(color) {
    selectedColor = color;
    document.getElementById('result').innerText = `Selected Color: ${color}`;
}

function rollBet() {
    const betAmount = parseInt(document.getElementById('betAmount').value);
    if (!selectedColor) {
        alert("Please select a color.");
        return;
    }
    if (betAmount > balance) {
        alert("Insufficient funds!");
        return;
    }

    fetch("roulette.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `action=placeBet&betAmount=${betAmount}&selectedColor=${selectedColor}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            balance = data.balance;
            document.getElementById('balance').innerText = `Current Balance: $${balance}`;

            // Spin the wheel
            const wheel = document.getElementById('rouletteWheel');
            const spins = Math.floor(Math.random() * 5) + 5; // Random number of full spins
            const degrees = {
                "red": 60,  // 1/3rd of the circle (adjust for accurate alignment)
                "black": 180,
                "green": 300,
            };
            const finalRotation = degrees[data.spinResult];
            const totalRotation = spins * 360 + finalRotation;

            // Start spin animation
            wheel.style.transition = 'transform 3s cubic-bezier(0.17, 0.67, 0.83, 0.67)';
            wheel.style.transform = `rotate(${totalRotation}deg)`;

            // Wait for the animation to finish, then show the result
            setTimeout(() => {
                document.getElementById('result').innerText = data.winnings > 0
                    ? `You won! Result: ${data.spinResult}, Winnings: $${data.winnings}`
                    : `You lost! Result: ${data.spinResult}`;
            }, 3000); // 3000ms (3s) matches the CSS animation duration
        } else {
            alert(data.message);
        }
    });
}
</script>
</body>
</html>




