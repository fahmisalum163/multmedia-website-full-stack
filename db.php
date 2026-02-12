<?php
$servername = "localhost";
$username = "root"; // badilisha kulingana na server yako
$password = "";     // weka password ya MySQL yako
$dbname = "delishhub";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>
