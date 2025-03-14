<?php
session_start();
if (!isset($_SESSION['user']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: index.php");
    exit();
}

include("database.php");

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Soft delete by updating status to inactive
    $query = "UPDATE announcements SET status = 'inactive' WHERE id = '$id'";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['message'] = "Announcement deleted successfully!";
    } else {
        $_SESSION['message'] = "Error deleting announcement: " . mysqli_error($conn);
    }
}

header("Location: homepage.php");
exit();
?> 