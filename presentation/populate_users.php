<?php
$conn = new mysqli("localhost", "root", "", "volunteer_management");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Organizer details
$name = "Green Future Volunteers";
$email = "greenfuture1organizer@example.com";
$passwordPlain = "Vol123!";  
$hashedPassword = password_hash($passwordPlain, PASSWORD_DEFAULT);

$telephoneNo = "0771234567";
$location = "Negombo";
$gender = "Prefer not to say";
$role = "Organizer";

// Insert query (NO userId because AUTO_INCREMENT)
$sql = "INSERT INTO users 
        (name, email, password, telephoneNo, location, gender, role) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssss", $name, $email, $hashedPassword, $telephoneNo, $location, $gender, $role);

if ($stmt->execute()) {
    echo "Organizer added successfully! New ID: " . $stmt->insert_id;
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>