<?php
session_start();
if (!isset($_SESSION['user'])) {
    // Redirect to login if the user is not logged in
    header("Location: index.php");
    exit();
}

include("database.php");

// Get the user's ID number from the session
$idno = $_SESSION['idno'];

// Process the form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and retrieve form data
    $firstname = mysqli_real_escape_string($conn, $_POST['firstname']);
    $lastname = mysqli_real_escape_string($conn, $_POST['lastname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $course = mysqli_real_escape_string($conn, $_POST['course']);
    $yearlvl = mysqli_real_escape_string($conn, $_POST['yearlvl']);

    // Update the user's information in the database
    $query = "UPDATE users SET 
              firstname = '$firstname', 
              lastname = '$lastname', 
              email = '$email', 
              course = '$course', 
              yearlvl = '$yearlvl' 
              WHERE idno = '$idno'";

    if (mysqli_query($conn, $query)) {
        // Redirect to homepage with a success message
        $_SESSION['message'] = "Profile updated successfully!";
        header("Location: homepage.php");
        exit();
    } else {
        // Handle database errors
        die("Error updating profile: " . mysqli_error($conn));
    }
} else {
    // Redirect to homepage if the form wasn't submitted
    header("Location: homepage.php");
    exit();
}
?>