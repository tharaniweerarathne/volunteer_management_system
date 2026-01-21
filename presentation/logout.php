<?php
session_start();
session_unset();    // clearing all session variables
session_destroy();  // destroy the session completely
?>

<script>
    alert("You have logged out successfully!");
    window.location.href = "index.php"; 
</script>
