<?php
session_start();
include("connect.php");

// Default balance if user has none in database
$defaultBalance = 100;
$hourlyIncrement = 50; // Increment $50 every hour

// Fetch or initialize user balance and spent stats
if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];

    // Fetch user data from the database
    $query = mysqli_query($conn, "SELECT username, balance, roulette_spent, slots_spent, last_update FROM users WHERE email = '$email'");
    if ($row = mysqli_fetch_assoc($query)) {
        $username = $row['username'];
        $balance = $row['balance'];
        $rouletteSpent = $row['roulette_spent'];
        $slotsSpent = $row['slots_spent'];
        $lastUpdate = $row['last_update'];

        // Calculate time difference
        $currentTime = time();
        $lastUpdateTime = strtotime($lastUpdate);
        $timeDifference = $currentTime - $lastUpdateTime;
        $hoursPassed = floor($timeDifference / 3600);

        // Calculate next available press time
        $nextPressTime = $lastUpdateTime + 3600;
        $timeUntilNextPress = max(0, $nextPressTime - $currentTime);
    } else {
        // If user balance not found, initialize it
        $balance = $defaultBalance;
        $rouletteSpent = 0;
        $slotsSpent = 0;
        $currentTime = date('Y-m-d H:i:s');
        mysqli_query($conn, "UPDATE users SET balance = $defaultBalance, roulette_spent = 0, slots_spent = 0, last_update = '$currentTime' WHERE email = '$email'");
        $timeUntilNextPress = 0;
    }
} else {
    // Redirect if not logged in
    header("Location: login.php");
    exit();
}

// Handle the button press to add $50
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addHourlyMoney'])) {
    $currentTime = time();
    $lastUpdateTime = strtotime($lastUpdate);
    $nextPressTime = $lastUpdateTime + 3600;

    if ($currentTime >= $nextPressTime) {
        $balance += $hourlyIncrement;
        $newUpdateTime = date('Y-m-d H:i:s', $currentTime);
        mysqli_query($conn, "UPDATE users SET balance = $balance, last_update = '$newUpdateTime' WHERE email = '$email'");
        echo json_encode(["success" => true, "balance" => $balance, "nextPressTime" => $currentTime + 3600]);
    } else {
        echo json_encode(["success" => false, "message" => "You need to wait before pressing again."]);
    }
    exit();
}

// Handle roulette roll
if (isset($_POST['rouletteRoll']) && $balance >= $rouletteCost) {
    $balance -= $rouletteCost;
    $rouletteSpent += $rouletteCost;
    mysqli_query($conn, "UPDATE users SET balance = $balance, roulette_spent = $rouletteSpent WHERE email = '$email'");
    echo json_encode(["success" => true, "balance" => $balance, "rouletteSpent" => $rouletteSpent]);
    exit();
}

// Handle slots roll
if (isset($_POST['slotsRoll']) && $balance >= $slotsCost) {
    $balance -= $slotsCost;
    $slotsSpent += $slotsCost;
    mysqli_query($conn, "UPDATE users SET balance = $balance, slots_spent = $slotsSpent WHERE email = '$email'");
    echo json_encode(["success" => true, "balance" => $balance, "slotsSpent" => $slotsSpent]);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile and Stats</title>
    <link rel="stylesheet" href="profileStyle.css" type="text/css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Menu page for website">
    <meta name="keywords" content="profile, gambling">
    <meta name="author" content="Akcininkai inc.">
</head>
<body>
    
<header>
    <div class="top-menu">
        <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
    </div>
</header>

<!-- Back to menu button in the top-right corner -->
<button id="backToMenuButton" onclick="window.location.href='menu.php';">Back to menu</button>

<div class="container">
    <p id="balance">Current Balance: $<?php echo number_format($balance, 2); ?></p>
    <p>Money Spent on Roulette: $<?php echo number_format($rouletteSpent, 2); ?></p>
    <p>Money Spent on Slots: $<?php echo number_format($slotsSpent, 2); ?></p>
    <p id="nextPress">Next Press Available In: <span id="timer">0</span> seconds</p>
    <br>
    <button id="addHourlyMoney" disabled>Add $50</button>
    <br><br>
</div>

<div class="lower-menu">
    <p>© 2024 Akcininkai inc. All rights reserved</p>
</div>

<script>
    let balance = <?php echo $balance; ?>;
    let rouletteSpent = <?php echo $rouletteSpent; ?>;
    let slotsSpent = <?php echo $slotsSpent; ?>;
    let timeUntilNextPress = <?php echo $timeUntilNextPress; ?>;
    const addHourlyMoneyButton = document.getElementById('addHourlyMoney');
    const balanceDisplay = document.getElementById('balance');
    const rouletteSpentDisplay = document.getElementById('rouletteSpent');
    const slotsSpentDisplay = document.getElementById('slotsSpent');
    const timerDisplay = document.getElementById('timer');

    // Update the timer every second
    function updateTimer() {
        if (timeUntilNextPress > 0) {
            timeUntilNextPress--;
            timerDisplay.innerText = timeUntilNextPress;
            addHourlyMoneyButton.disabled = true;
        } else {
            timerDisplay.innerText = "0";
            addHourlyMoneyButton.disabled = false;
        }
    }

    setInterval(updateTimer, 1000);

    // Handle adding $50
    addHourlyMoneyButton.addEventListener('click', () => {
        fetch("", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "addHourlyMoney=true"
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                balance = data.balance;
                balanceDisplay.innerText = `Current Balance: $${balance.toFixed(2)}`;
                timeUntilNextPress = 3600;
            } else {
                alert(data.message);
            }
        });
    });
</script>

</body>
</html>

