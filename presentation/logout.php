<?php
session_start();
session_unset();    
session_destroy();  
?>

<script>
    alert("You have logged out successfully!");
    window.location.href = "index.php"; 
</script>
