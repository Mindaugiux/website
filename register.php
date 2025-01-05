<?php 

include 'connect.php';

// Registration Logic
if(isset($_POST['signUp'])){
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password = md5($password);

    $checkEmail = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($checkEmail);
    if($result !== false && $result->num_rows > 0){
        echo "Email Address Already Exists !";
    }
    else{
        $insertQuery = "INSERT INTO users(username, email, password)
                        VALUES ('$username', '$email', '$password')";
        if($conn->query($insertQuery) === TRUE){
            header("location: index.php");
        }
        else{
            echo "Error: " . $conn->error;
        }
    }
}

// Login Logic
if(isset($_POST['signIn'])){
   $email = $_POST['email'];
   $password = $_POST['password'];
   $password = md5($password); // Hash password

   // Check if email exists
   $sql = "SELECT * FROM users WHERE email='$email'";
   $result = $conn->query($sql);

   if($result->num_rows > 0){
       // Verify the password
       $sql = "SELECT * FROM users WHERE email='$email' AND password='$password'";
       $result = $conn->query($sql);

       if($result->num_rows > 0){
           session_start();
           $row = $result->fetch_assoc();
           $_SESSION['email'] = $row['email'];
           header("Location: menu.php");
       } else {
           echo "Incorrect password.";
       }
   } else {
       echo "Incorrect email.";
   }
}

?>
