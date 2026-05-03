<?php
$name = $_POST['name'];
$email = $_POST['email'];
$subject = $_POST['subject'];
$message = $_POST['message'];

//Database connection
$conn = new mysqli('localhost', 'root', '', 'coffee_shop');
if($conn->connect_error){
    die('Connection Failed : '.$conn->connect_error);
}else{
    $stmt = $conn->prepare("insert into contact_messages(name, email, subject, message)
    values(?, ?, ?, ?)");
    $stmt->bind_param("ssss",$name, $email, $subject, $message);
    $stmt->execute();
    
    header("Location: contact-success.html");
    exit();
    
    $stmt->close();
    $conn->close();
}
?>