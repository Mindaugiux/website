<?php
session_start();
include("connect.php");

// Default balance if user has none in database
$defaultBalance = 100;

// Fetch or initialize user balance
if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];
    $query = mysqli_query($conn, "SELECT balance, slots_spent FROM users WHERE email = '$email'");
    if ($row = mysqli_fetch_assoc($query)) {
        $balance = $row['balance'];
        $slotsSpent = $row['slots_spent'];
    } else {
        // If user balance not found, initialize it
        $balance = $defaultBalance;
        $slotsSpent = 0;
        mysqli_query($conn, "UPDATE users SET balance = $defaultBalance, slots_spent = 0 WHERE email = '$email'");
    }
} else {
    // Redirect if not logged in
    header("Location: login.php");
    exit();
}
// Update balance and slots_spent on POST request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['newBalance']) && isset($_POST['slotsSpent'])) {
    $newBalance = intval($_POST['newBalance']);
    $newSlotsSpent = intval($_POST['slotsSpent']);
    mysqli_query($conn, "UPDATE users SET balance = $newBalance, slots_spent = $newSlotsSpent WHERE email = '$email'");
    echo json_encode(["success" => true]);
    exit();
}


// Update balance on POST request (used by JS later)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['newBalance'])) {
    $newBalance = intval($_POST['newBalance']);
    mysqli_query($conn, "UPDATE users SET balance = $newBalance WHERE email = '$email'");
    echo json_encode(["success" => true]);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Slots Game</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="slotsStyle.css">
</head>
<body>
<header>
    <h1>SLOTS GAME</h1>
</header>
<div class="container">
    <p id="balance">Current Balance: $<?php echo number_format($balance, 2); ?></p>
    <br>
    <label for="betAmount">Enter Amount to Bet:</label>
    <input type="number" id="betAmount" min="1" value="1" oninput="validateBet()">
    <p id="error-message" style="color: red; display: none;">Please enter a valid bet amount.</p>
    <br>
    <div>
        <div class="reel" id="reel1">🍒</div>
        <div class="reel" id="reel2">🍒</div>
        <div class="reel" id="reel3">🍒</div>
    </div>
    <button id="spin" disabled>Spin</button>
    <button id="showWinnings">Show Potential Winnings</button>
    <p id="result"></p>
    <p id="potentialWinnings"></p>
</div>

<a href="menu.php">Go Back</a>

<script>
    let balance = <?php echo $balance; ?>; // Balance fetched from PHP
    const symbols = ['🍒', '🍋', '🔔', '⭐', '💎'];
    const probabilities = [30, 20, 15, 10, 5]; // Berries, Lemons, Bells, Stars, Diamonds
    const winningsMultiplier = [2, 3, 5, 10, 20]; // Corresponding multipliers for each symbol

    // Function to validate the bet amount and enable/disable the Spin button
    function validateBet() {
        const betAmount = document.getElementById('betAmount').value;
        const spinButton = document.getElementById('spin');
        const errorMessage = document.getElementById('error-message');

        // Check if bet amount is valid
        if (betAmount && parseInt(betAmount) > 0) {
            spinButton.disabled = false;
            errorMessage.style.display = "none";
        } else {
            spinButton.disabled = true;
            errorMessage.style.display = "block";
        }
    }

    document.getElementById('spin').onclick = () => {
        const betAmount = parseInt(document.getElementById('betAmount').value);

        if (!betAmount || betAmount <= 0) {
            document.getElementById('error-message').innerText = "Please enter a valid bet amount.";
            return;
        }

        if (betAmount > balance) {
            document.getElementById('result').innerText = "Insufficient funds!";
            return;
        }

        startSpinAnimation(() => finalizeSpin(betAmount));
    };

    document.getElementById('showWinnings').onclick = () => {
        const betAmount = parseInt(document.getElementById('betAmount').value);
        if (isNaN(betAmount) || betAmount < 1) {
            document.getElementById('potentialWinnings').innerText = "Enter a valid bet amount.";
            return;
        }

        let message = `Potential Winnings for a $${betAmount} bet:\n`;
        for (let i = 0; i < symbols.length; i++) {
            const winnings = betAmount * winningsMultiplier[i];
            message += `${symbols[i]} - $${winnings}\n`;
        }

        document.getElementById('potentialWinnings').innerText = message;
    };

   let slotsSpent = <?php echo $slotsSpent; ?>; // Slots spent fetched from PHP

function updateBalance() {
    document.getElementById('balance').innerText = `Current Balance: $${balance}`;
    saveBalanceToServer();
}

function saveBalanceToServer() {
    fetch("", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `newBalance=${balance}&slotsSpent=${slotsSpent}`
    });
}

    function startSpinAnimation(callback) {
    const reel1 = document.getElementById('reel1');
    const reel2 = document.getElementById('reel2');
    const reel3 = document.getElementById('reel3');

    let spinCount = 0;
    const maxSpins = 20; // Adjust for longer animations
    const interval = 100;

    // Add spin-animation class to reels
    reel1.classList.add('spin-animation');
    reel2.classList.add('spin-animation');
    reel3.classList.add('spin-animation');

    const spinner = setInterval(() => {
        reel1.innerText = getRandomSymbol();
        reel2.innerText = getRandomSymbol();
        reel3.innerText = getRandomSymbol();
        spinCount++;

        if (spinCount >= maxSpins) {
            clearInterval(spinner);
            // Remove spin-animation class
            reel1.classList.remove('spin-animation');
            reel2.classList.remove('spin-animation');
            reel3.classList.remove('spin-animation');
            callback();
        }
    }, interval);
}

function finalizeSpin(betAmount) {
    const finalReel1 = getRandomSymbol();
    const finalReel2 = getRandomSymbol();
    const finalReel3 = getRandomSymbol();

    // Show final symbols
    document.getElementById('reel1').innerText = finalReel1;
    document.getElementById('reel2').innerText = finalReel2;
    document.getElementById('reel3').innerText = finalReel3;

    if (finalReel1 === finalReel2 && finalReel2 === finalReel3) {
        const winnings = betAmount * winningsMultiplier[symbols.indexOf(finalReel1)];
        balance += winnings;
        document.getElementById('result').innerText = `You won! 🎉 ${finalReel1} ${finalReel2} ${finalReel3}. You earned $${winnings}.`;
    } else {
        balance -= betAmount;
        slotsSpent += betAmount; // Track money spent
        document.getElementById('result').innerText = `You lost! ${finalReel1} ${finalReel2} ${finalReel3}.`;
    }

    updateBalance();
}

    function getRandomSymbol() {
        const totalProbability = probabilities.reduce((acc, prob) => acc + prob, 0);
        let random = Math.random() * totalProbability;
        for (let i = 0; i < symbols.length; i++) {
            if (random < probabilities[i]) {
                return symbols[i];
            }
            random -= probabilities[i];
        }
    }
</script>
</body>
</html>
