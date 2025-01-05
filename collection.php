<?php
session_start();
include("connect.php");

// Default balance if user has none in database
$defaultBalance = 100;

// Total available images
$totalImages = 10; // Adjust this number to reflect the actual number of images

// Fetch or initialize user balance and collection
if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];
    $query = mysqli_query($conn, "SELECT balance, collection FROM users WHERE email = '$email'");
    if ($row = mysqli_fetch_assoc($query)) {
        $balance = $row['balance'];
        $collection = json_decode($row['collection'], true) ?? [];
    } else {
        // If user balance not found, initialize it
        $balance = $defaultBalance;
        $collection = [];
        mysqli_query($conn, "UPDATE users SET balance = $defaultBalance, collection = '[]' WHERE email = '$email'");
    }
} else {
    // Redirect if not logged in
    header("Location: index.php");
    exit();
}

// Update balance or collection on POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['newBalance'])) {
        $newBalance = intval($_POST['newBalance']);
        mysqli_query($conn, "UPDATE users SET balance = $newBalance WHERE email = '$email'");
        echo json_encode(["success" => true]);
        exit();
    } elseif (isset($_POST['wonImage'])) {
        $wonImage = $_POST['wonImage'];
        $wonImageName = pathinfo($wonImage, PATHINFO_FILENAME); // Get name without extension
        if (in_array($wonImage, $collection)) {
            echo json_encode(["success" => false, "message" => "You already own this image. You lost!"]);
        } else {
            $collection[] = $wonImage;
            mysqli_query($conn, "UPDATE users SET collection = '" . json_encode($collection) . "' WHERE email = '$email'");
            echo json_encode(["success" => true, "message" => "You won: $wonImageName"]);
        }
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Gambling System with Images</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="collectionStyle.css">
</head>
<body>
<header>
    <h1>Movie Gambling!</h1>
</header>
<div class="container">
    <h2>Spin the Wheel</h2>
    <div id="MovieWheel" style="width: 100px; height: 100px; margin: auto; display: flex; justify-content: center; align-items: center;">
        <img id="wheelImage" src="wheel.jpg" alt="wheel image" style="max-width: 100%; max-height: 100%;">
    </div>
    <p id="balance">Current Balance: $<?php echo number_format($balance, 2); ?></p>

    <p>Spin the wheel for $25 to win a movie!</p>

    <br>
    <button id="roll" disabled>Spin</button>
    <p id="result"></p>

    <h3>Your Collection:</h3>
    <div id="collection">
        <?php foreach ($collection as $image): ?>
            <div style="text-align: center; display: inline-block; margin: 5px;">
                <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($image); ?>" style="width: 100px; height: auto;">
                <p><?php echo ucfirst(str_replace('_', ' ', pathinfo($image, PATHINFO_FILENAME))); ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <p id="collectionText">You have collected <span id="collectionCount"><?php echo count($collection); ?></span> out of <span id="totalImages"><?php echo $totalImages; ?></span> movies.</p>
    <p id="specialMessage" style="color: green; font-weight: bold; display: none;">YOU ARE SIGMA!!</p>

    <a href="menu.php">Go Back</a>
</div>
<script>
    let balance = <?php echo $balance; ?>;
    const images = ["Avatar.jpg", "Avatar_The_Way_of_Water.jpg", "Avengers_Endgame.jpg", "Titanic.jpg", "Star_Wars_The_Force_Awakens.jpg", "Spider-Man_No_Way_Home.jpg", "Inside_Out_2.jpg", "Jurassic_World.jpg", "The_Lion_King.jpg", "Avengers_Infinity_War.jpg"];

    const betAmount = 25;
    const totalImages = images.length;

    if (balance >= betAmount) {
        document.getElementById('roll').disabled = false;
    }

    document.getElementById('roll').onclick = () => {
        if (balance < betAmount) {
            document.getElementById('result').innerText = "Insufficient funds!";
            return;
        }

        spinWheel(betAmount);
    };

    function updateBalance() {
        document.getElementById('balance').innerText = `Current Balance: $${balance}`;
        saveBalanceToServer();
    }

    function updateCollectionCount() {
        const collectionCount = document.querySelectorAll('#collection div').length;
        document.getElementById('collectionCount').innerText = collectionCount;

        if (collectionCount === totalImages) {
            displaySpecialMessage();
        }
    }

    function displaySpecialMessage() {
        const specialMessage = document.getElementById('specialMessage');
        specialMessage.style.display = 'block';
    }

    function saveBalanceToServer() {
        fetch("", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `newBalance=${balance}`
        });
    }

    function saveWonImage(wonImage) {
        return fetch("", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `wonImage=${wonImage}`
        }).then(response => response.json());
    }

    function spinWheel(betAmount) {
        const wheelImage = document.getElementById('wheelImage');
        let count = 0;
        const interval = setInterval(() => {
            const randomIndex = Math.floor(Math.random() * images.length);
            wheelImage.src = images[randomIndex];
            count++;
        }, 100);

        setTimeout(() => {
            clearInterval(interval);
            const wonImage = images[Math.floor(Math.random() * images.length)];
            const wonImageName = wonImage.replace(/\.[^/.]+$/, ""); // Remove extension
            balance -= betAmount;

            saveWonImage(wonImage).then(response => {
                if (response.success) {
                    document.getElementById('result').innerText = response.message;
                    addImageToCollection(wonImage, wonImageName);
                    updateCollectionCount();
                } else {
                    document.getElementById('result').innerText = response.message;
                }
                updateBalance();
            });

            wheelImage.src = wonImage;
        }, 3000);
    }

    function addImageToCollection(wonImage, wonImageName) {
        const collectionDiv = document.getElementById('collection');
        const container = document.createElement('div');
        container.style.textAlign = 'center';
        container.style.display = 'inline-block';
        container.style.margin = '5px';

        const img = document.createElement('img');
        img.src = wonImage;
        img.alt = wonImage;
        img.style.width = '100px';
        container.appendChild(img);

        // Format the image name by removing '.jpg' and replacing underscores with spaces
        const formattedName = wonImageName.replace(/_/g, ' ').replace('.jpg', '');
        const name = document.createElement('p');
        name.textContent = formattedName.charAt(0).toUpperCase() + formattedName.slice(1);  // Capitalize first letter
        container.appendChild(name);

        collectionDiv.appendChild(container);
    }
</script>
</body>
</html>
