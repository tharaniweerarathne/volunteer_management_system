<?php
// db.php

$host = "localhost";       // usually localhost
$user = "root";            // your MySQL username
$pass = "";                // your MySQL password
$dbname = "volunteer_management"; // your database name

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
