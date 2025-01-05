<?php
session_start();
include("connect.php");
$balance = 0;

// Fetch user's balance if logged in
if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];
    $query = mysqli_query($conn, "SELECT balance FROM users WHERE email = '$email'");
    if ($row = mysqli_fetch_assoc($query)) {
        $balance = $row['balance'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Menu page</title>
    <meta name=" viewport" content=" width=device-width, initial-scale=1.0"/>
    <meta name="description" content="Menu page for website">
    <meta name="keywords" content="menu, gambling">
    <meta name="author" content="Akcininkai inc.">
</head>

<body>
<header>
    <div class="top-menu">
        <!-- Figmoj yra pinigu amount displayed, bet cia nematau. Kolkas logout button cia imetu, nes kitur nesiziuri ir figmoj nera.-->
        <a id="logout" href="logout.php">LOG OUT</a>
		
	<div class="balance-container">
		<p>Your Balance: $<?php echo number_format($balance, 2); ?></p>
	</div>

        <a id="collection" href="collection.php">COLLECTION</a>

        <a id="profile" href="profile.php">PROFILE</a>
     
    </div>
    </header>

    <div class="middle-menu">
        <a id="slots" href="slots.php"> SLOTS </a>
        <a id="roullete" href="roulette.php"> ROULETTE </a>
        <!-- Nebus sito kaip suprantu <a id="mines" href="mines.html">MINES</a> -->
    </div>


<!-- perkeliau i virsu <a href="logout.php">Logout</a>-->
<!--Ar sito reikia?-->

    <footer>
        <div class="footer">
            <p>© 2024 Akcininkai inc. All rights reserved</p>
        </div>
    </footer>

</body>

<style>
    body {
    margin: 0;
    padding: 0;
    font-family: "Kode Mono", monospace;
    background-color: #000;
    color: #FFF;
    overflow: hidden;
    background-image: url('menuback.jpg');
    background-size: cover;
    background-position: top;
}

#logout {
    color: #ff0000;
    font-family: fantasy;
    letter-spacing: 0.1em;
    font-size: 200%;
    position: absolute;
    top: 0.5em;
    left: 0.5em;
}

#collection {
    color: #F3DE1D;
    font-family: fantasy;
    letter-spacing: 0.1em;
    font-size: 200%;
    position: absolute;
    top: 0.5em;
    left: 37em;
}

#profile {
    color: #F3DE1D;
    font-family: fantasy;
    letter-spacing: 0.1em;
    font-size: 200%;
    position: absolute;
    top: 0.5em;
    right: 0.5em;
}

.balance-container {
    color: #FFF;
    font-family: fantasy;
    letter-spacing: 0.1em;
    font-size: 1.5em;
    top: 2em;
    right: 0.5em;
    position: absolute;
}

#profile:hover, #collection:hover {
    transform: scale(1.1);
    filter: brightness(1.2);
    transition: transform 0.3s ease, filter 0.3s ease;
    text-shadow: 0px 0px 8px rgba(0, 0, 0, 0.8),
                 0px 0px 12px rgba(255, 251, 3, 0.8);
}

#logout:hover {
    transform: scale(1.1);
    filter: brightness(1.2);
    transition: transform 0.3s ease, filter 0.3s ease;
    text-shadow: 0px 0px 8px rgba(0, 0, 0, 0.8),
                 0px 0px 12px rgba(225, 18, 18, 0.8);
}

#logout, #profile, #collection {
    cursor: pointer;
    text-decoration: none;
}

#slots {
    position: fixed;
    top: 40%;
    left: 20%;
    font-family: fantasy;
    letter-spacing: 0.1em;
    font-size: 4em;
    color: #F3DE1D;
    cursor: pointer;
    text-decoration: none;
    z-index: 100; /* Ensure it appears on top */
}

#roullete {
    position: fixed;
    font-family: fantasy;
    letter-spacing: 0.1em;
    font-size: 4em;
    top: 40%;
    left: 60%;
    color: #F3DE1D;
    cursor: pointer;
    text-decoration: none;
    z-index: 100; /* Ensure it appears on top */
}

#roullete:hover, #slots:hover {
    transform: scale(1.3);
    filter: brightness(1.2);
    transition: transform 0.3s ease, filter 0.3s ease;
}

.footer {
    position: fixed;
    bottom: 0;
    width: 100%;
    background-color: #000000;
    color: #FFF;
    text-align: center;
    padding: 10px 0;
    z-index: 10;
}
</style>