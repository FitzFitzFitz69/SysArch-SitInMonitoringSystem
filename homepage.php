<?php
session_start();
if (!isset($_SESSION['user'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Display success message if set
if (isset($_SESSION['message'])) {
    echo "<script>alert('" . $_SESSION['message'] . "');</script>";
    unset($_SESSION['message']);
}

include("database.php");

// Fetch user data from the database
if (isset($_SESSION['idno'])) {
    $idno = $_SESSION['idno'];
    $query = "SELECT * FROM users WHERE idno='$idno'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);    
    } else {
        die("User not found in the database.");
    }
} else {
    die("User ID not set in session.");
}

// Map course codes to full names
$courses = [
    1 => "BSIT",
    2 => "BSA",
    3 => "BSCS",
    4 => "BSCRIM"
];
$user['course_name'] = $courses[$user['course']] ?? "Unknown";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* General Styles */
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background-color: #F7B4C9;
        }

        /* Header */
        .header {
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            align-items: center;
        }

        .header h1 {
            font-size: 24px;
            font-weight: 600;
            color: #fff;
            margin-left: 15px;
        }

        .logo {
            width: 50px;
            height: 50px;
            background-color: #F7B4C6;
            border-radius: 50%;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background-color: #F7B4C6;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0; /* Adjusted to reach the top of the screen */
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            transition: width 0.3s;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 20px; /* Adjust padding to move contents to the top */
        }

        /* Collapsed Sidebar */
        .sidebar.collapsed {
            width: 60px;
        }

        /* Hide everything except the button when collapsed */
        .sidebar.collapsed h2 span,
        .sidebar.collapsed ul,
        .sidebar.collapsed .logout-btn {
            display: none;
        }

        /* Toggle Button */
        .sidebar .toggle-btn {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            padding: 10px;
            position: absolute;
            top: 0;
            right: 0;
        }

        .sidebar h2 {
            color: white;
            text-align: center;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            position: relative;
            margin-top: 0; /* Adjust margin to move contents to the top */
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            transition: opacity 0.3s;
        }

        .sidebar ul li {
            margin: 15px 0;
        }

        .sidebar ul li a {
            color: white;
            text-decoration: none;
            font-size: 16px;
            display: block;
            padding: 10px;
            border-radius: 5px;
            transition: background-color 0.3s;
            white-space: nowrap; /* Prevent text from wrapping */
        }

        .sidebar ul li a:hover {
            background-color: #e58aa3;
        }

        .logout-btn {
            width: 100%;
            padding: 10px;
            background-color: #e58aa3;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 100px;
        }

        .logout-btn:hover {
            background-color: #d8738f;
        }

        /* Main Content */
        .content {
            margin-left: 270px;
            padding: 20px;
            transition: margin-left 0.3s;
            min-height: 100vh;
            margin-top: 120px; /* Increased margin to move content down */
        }

        .content.expanded {
            margin-left: 80px;
        }

        /* Info Section */
        .info-section {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
            text-align: center;
        }

        .info-section h3 {
            color: #333;
            margin-bottom: 15px;
        }

        .info-section p {
            color: #555;
            margin: 10px 0;
        }

        .info-section button {
            background-color: #F7B4C6;
            border: none;
            color: white;
            font-size: 16px;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 15px;
        }

        .info-section button:hover {
            background-color: #e58aa3;
        }

        /* Edit Profile Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            width: 400px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            position: relative;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-content h3 {
            color: #333;
            margin-bottom: 20px;
            padding-right: 30px;
        }

        .modal-content label {
            color: #555;
            margin: 10px 0;
            display: block;
        }

        .modal-content input[type="text"],
        .modal-content input[type="email"],
        .modal-content select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 2px solid #F7B4C6;
            border-radius: 5px;
            font-size: 16px;
            margin-bottom: 15px;
        }

        .modal-content input[type="submit"] {
            width: 100%;
            margin-top: 20px;
            padding: 12px;
            background-color: #F7B4C6;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
            display: block;
        }

        .modal-content input[type="submit"]:hover {
            background-color: #e58aa3;
        }

        .close-btn {
            position: absolute;
            right: 20px;
            top: 20px;
            cursor: pointer;
            font-size: 24px;
            color: #666;
        }

        /* File Input Styling */
        .file-input-container {
            position: relative;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .file-input {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-label {
            display: inline-block;
            padding: 10px 20px;
            background-color: #F7B4C6;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            white-space: nowrap;
        }

        .file-label:hover {
            background-color: #e58aa3;
        }

        .file-name {
            color: #666;
            flex-grow: 1;
        }

        .current-photo {
            margin: 20px 0;
            text-align: center;
        }

        .current-photo p {
            margin-bottom: 10px;
            color: #666;
        }

        .current-photo img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #F7B4C6;
        }

        /* Admin Dashboard Styles */
        .admin-dashboard {
            width: 100%;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .stats-section {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
            margin-top: 40px; /* Added margin to move stats section down */
        }

        .stats-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            flex: 1;
            min-width: 300px;
        }

        .chart-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            flex: 1;
            min-width: 300px;
            height: 400px;
            display: flex;
            flex-direction: column;
        }

        .chart-box canvas {
            flex: 1;
            width: 100% !important;
            height: 100% !important;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 15px;
        }

        .stat-item {
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #F7B4C6;
        }

        .stat-label {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }

        /* Announcements Styles */
        .announcements-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .announcements-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .new-announcement-btn {
            background: #F7B4C6;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .new-announcement-btn:hover {
            background: #e58aa3;
        }

        .announcement-item {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
        }

        .announcement-item:last-child {
            border-bottom: none;
        }

        .announcement-item h4 {
            color: #333;
            margin-bottom: 10px;
        }

        .announcement-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
            color: #666;
            font-size: 14px;
        }

        .delete-btn {
            background: #ff4444;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
        }

        .delete-btn:hover {
            background: #cc0000;
        }

        textarea {
            width: 100%;
            height: 150px;
            padding: 10px;
            margin-top: 5px;
            border: 2px solid #F7B4C6;
            border-radius: 5px;
            font-size: 16px;
            resize: vertical;
        }

        /* Student Dashboard Styles */
        .student-dashboard {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            gap: 30px;
            padding: 20px;
            margin-top: 40px;
        }

        .student-info-container {
            flex: 1;
            min-width: 400px;
        }

        .student-announcements {
            flex: 1;
            min-width: 400px;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .info-section {
            background-color: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            text-align: center;
            height: 100%;
        }

        .info-section h3 {
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
            padding-bottom: 10px;
            border-bottom: 2px solid #F7B4C6;
        }

        .info-section p {
            color: #555;
            margin: 15px 0;
            font-size: 16px;
        }

        .info-section button {
            background-color: #F7B4C6;
            border: none;
            color: white;
            font-size: 16px;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 20px;
        }

        .info-section button:hover {
            background-color: #e58aa3;
        }

        .info-section img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 20px;
            border: 3px solid #F7B4C6;
        }

        /* Student Announcements Section */
        .student-announcements h3 {
            color: #333;
            font-size: 24px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #F7B4C6;
        }

        .student-announcements .announcement-item {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .student-announcements .announcement-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        .student-announcements .announcement-item h4 {
            color: #333;
            font-size: 18px;
            margin-bottom: 12px;
            font-weight: 600;
        }

        .student-announcements .announcement-item p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 12px;
            font-size: 15px;
        }

        .student-announcements .announcement-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #888;
            font-size: 14px;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }

        .student-announcements .announcement-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .student-announcements .announcement-meta span::before {
            content: "📅";
        }

        /* Students Section Styles */
        .students-section {
            margin-top: 40px;
        }

        .section-title {
            color: #333;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .students-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .left-controls {
            display: flex;
            gap: 10px;
        }

        .right-controls {
            display: flex;
            gap: 10px;
        }

        .search-input {
            padding: 10px;
            border: 2px solid #F7B4C6;
            border-radius: 5px;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #F7B4C6;
            color: white;
        }

        .actions {
            text-align: center;
        }

        .edit-btn, .delete-btn {
            padding: 5px 10px;
            border: none;
            cursor: pointer;
        }

        .edit-btn {
            background-color: #F7B4C6;
            color: white;
        }

        .delete-btn {
            background-color: #ff4444;
            color: white;
        }

        .add-btn, .reset-btn {
            padding: 12px 25px;
            border: none;
            cursor: pointer;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .add-btn {
            background-color: #F7B4C6;
            color: white;
            box-shadow: 0 2px 4px rgba(247, 180, 198, 0.2);
        }

        .add-btn:hover {
            background-color: #f59eb5;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(247, 180, 198, 0.3);
        }

        .reset-btn {
            background-color: #ff4444;
            color: white;
            box-shadow: 0 2px 4px rgba(255, 68, 68, 0.2);
        }

        .reset-btn:hover {
            background-color: #ff3333;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(255, 68, 68, 0.3);
        }

        .edit-btn, .delete-btn {
            padding: 8px 16px;
            border: none;
            cursor: pointer;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            margin: 0 5px;
        }

        .edit-btn {
            background-color: #F7B4C6;
            color: white;
            box-shadow: 0 2px 4px rgba(247, 180, 198, 0.2);
        }

        .edit-btn:hover {
            background-color: #f59eb5;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(247, 180, 198, 0.3);
        }

        .delete-btn {
            background-color: #ff4444;
            color: white;
            box-shadow: 0 2px 4px rgba(255, 68, 68, 0.2);
        }

        .delete-btn:hover {
            background-color: #ff3333;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(255, 68, 68, 0.3);
        }

        /* Announcement Modal Styles */
        #announcementModal .modal-content {
            width: 600px;
            max-width: 90%;
            padding: 40px;
            border-radius: 15px;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        #announcementModal .modal-content h3 {
            color: #333;
            font-size: 24px;
            margin-bottom: 25px;
            text-align: center;
            position: relative;
            padding-bottom: 15px;
        }

        #announcementModal .modal-content h3:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background-color: #F7B4C6;
            border-radius: 2px;
        }

        #announcementModal textarea {
            min-height: 200px;
            margin-bottom: 20px;
            padding: 15px;
            font-size: 15px;
            line-height: 1.5;
            border: 2px solid #eee;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        #announcementModal textarea:focus {
            border-color: #F7B4C6;
            outline: none;
            box-shadow: 0 0 0 3px rgba(247, 180, 198, 0.2);
        }

        #announcementModal input[type="text"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 2px solid #eee;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        #announcementModal input[type="text"]:focus {
            border-color: #F7B4C6;
            outline: none;
            box-shadow: 0 0 0 3px rgba(247, 180, 198, 0.2);
        }

        #announcementModal input[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #F7B4C6;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        #announcementModal input[type="submit"]:hover {
            background-color: #f59eb5;
            transform: translateY(-1px);
        }

        #announcementModal .close-btn {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 24px;
            color: #666;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background-color: #f8f9fa;
        }

        #announcementModal .close-btn:hover {
            background-color: #F7B4C6;
            color: white;
        }

        /* Table Styles */
        .table-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-top: 20px;
            border: 2px solid #F7B4C6;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        th {
            background-color: #F7B4C6;
            color: white;
            font-weight: 600;
            padding: 15px;
            text-align: left;
            font-size: 15px;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e58aa3;
        }

        th:not(:last-child) {
            border-right: 1px solid #e58aa3;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            color: #333;
            font-size: 14px;
        }

        td:not(:last-child) {
            border-right: 1px solid #eee;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background-color: #f8f9fa;
        }

        .actions {
            text-align: center;
            white-space: nowrap;
            background-color: #fff;
        }

        /* Button Styles */
        .add-btn, .reset-btn {
            padding: 12px 25px;
            border: none;
            cursor: pointer;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 2px solid;
        }

        .add-btn {
            background-color: white;
            color: #F7B4C6;
            border-color: #F7B4C6;
            box-shadow: 0 2px 4px rgba(247, 180, 198, 0.2);
        }

        .add-btn:hover {
            background-color: #F7B4C6;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(247, 180, 198, 0.3);
        }

        .reset-btn {
            background-color: white;
            color: #ff4444;
            border-color: #ff4444;
            box-shadow: 0 2px 4px rgba(255, 68, 68, 0.2);
        }

        .reset-btn:hover {
            background-color: #ff4444;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(255, 68, 68, 0.3);
        }

        .edit-btn, .delete-btn {
            padding: 8px 16px;
            border: none;
            cursor: pointer;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            margin: 0 5px;
            border: 2px solid;
        }

        .edit-btn {
            background-color: white;
            color: #F7B4C6;
            border-color: #F7B4C6;
            box-shadow: 0 2px 4px rgba(247, 180, 198, 0.2);
        }

        .edit-btn:hover {
            background-color: #F7B4C6;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(247, 180, 198, 0.3);
        }

        .delete-btn {
            background-color: white;
            color: #ff4444;
            border-color: #ff4444;
            box-shadow: 0 2px 4px rgba(255, 68, 68, 0.2);
        }

        .delete-btn:hover {
            background-color: #ff4444;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(255, 68, 68, 0.3);
        }

        /* Students Controls Styles */
        .students-controls {
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            background-color: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 2px solid #F7B4C6;
        }

        /* Search Input Styles */
        .search-input {
            padding: 12px 20px;
            border: 2px solid #eee;
            border-radius: 8px;
            font-size: 15px;
            width: 300px;
            transition: all 0.3s ease;
            background-color: white;
        }

        .search-input:focus {
            border-color: #F7B4C6;
            outline: none;
            box-shadow: 0 0 0 3px rgba(247, 180, 198, 0.2);
        }

        .search-input::placeholder {
            color: #999;
        }

        /* Section Title Styles */
        .section-title {
            color: #333;
            font-size: 28px;
            margin-bottom: 30px;
            position: relative;
            padding-bottom: 15px;
        }

        .section-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100px;
            height: 3px;
            background-color: #F7B4C6;
            border-radius: 2px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <img src="logo ccs.png" class="logo" alt="Logo">
            <h1>CCS SIT-IN MONITORING SYSTEM</h1>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <h2>
            <span>Dashboard</span>
            <button class="toggle-btn" onclick="toggleSidebar()">&#9776;</button> <!-- Toggle button -->
        </h2>
        <ul>
            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) { ?>
                <li><a href="#home">Home</a></li>
                <li><a href="#students">Students</a></li>
                <li><a href="#sit-in">Sit-in</a></li>
                <li><a href="#view-sit-in-records">View Sit-in Records</a></li>
                <li><a href="#sit-in-reports">Sit-in Reports</a></li>
                <li><a href="#feedback-reports">Feedback Reports</a></li>
                <li><a href="#reservation">Reservation</a></li>
            <?php } else { ?>
                <li><a href="#profile">Profile</a></li>
                <li><a href="#announcement">Announcement</a></li>
                <li><a href="#sessions">Remaining Sessions</a></li>
                <li><a href="#rules">Sit-in Rules</a></li>
                <li><a href="#lab-rules">Lab Rules & Regulations</a></li>
                <li><a href="#history">Sit-in History</a></li>
                <li><a href="#reservation">Reservation</a></li>
                <li><a href="#notifications">Notifications</a></li>
            <?php } ?>
        </ul>
        <button class="logout-btn" onclick="window.location.href='logout.php'">Logout</button>
    </div>

    <!-- Main Content -->
    <div class="content" id="content">
        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) { ?>
            <!-- Admin Dashboard -->
            <div class="admin-dashboard">
                <!-- Home Section -->
                <div id="home" class="home-section">
                    <!-- Statistics Section -->
                    <div class="stats-section">
                        <div class="stats-box">
                            <h3>Statistics</h3>
                            <div class="stats-grid">
                                <div class="stat-item">
                                    <div class="stat-value"><?php
                                        $query = "SELECT COUNT(*) as total FROM users WHERE status = 'active' AND idno != '00'";
                                        $result = mysqli_query($conn, $query);
                                        $row = mysqli_fetch_assoc($result);
                                        echo $row['total'];
                                    ?></div>
                                    <div class="stat-label">Total Students</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-value"><?php
                                        $query = "SELECT COUNT(*) as total FROM sit_in_records WHERE status = 'active'";
                                        $result = mysqli_query($conn, $query);
                                        $row = mysqli_fetch_assoc($result);
                                        echo $row['total'];
                                    ?></div>
                                    <div class="stat-label">Currently Sitting In</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-value"><?php
                                        $query = "SELECT COUNT(*) as total FROM sit_in_records WHERE status = 'completed'";
                                        $result = mysqli_query($conn, $query);
                                        $row = mysqli_fetch_assoc($result);
                                        echo $row['total'];
                                    ?></div>
                                    <div class="stat-label">Total Sit-ins</div>
                                </div>
                            </div>
                        </div>
                        <div class="chart-box">
                            <h3>Programming Languages Usage</h3>
                            <canvas id="languageChart"></canvas>
                        </div>
                    </div>

                    <!-- Announcements Section -->
                    <div class="announcements-section">
                        <div class="announcements-header">
                            <h3>Announcements</h3>
                            <button onclick="openAnnouncementModal()" class="new-announcement-btn">New Announcement</button>
                        </div>
                        <div class="announcements-list">
                            <?php
                            $query = "SELECT * FROM announcements WHERE status = 'active' ORDER BY date_posted DESC";
                            $result = mysqli_query($conn, $query);
                            while ($announcement = mysqli_fetch_assoc($result)) {
                                echo '<div class="announcement-item">';
                                echo '<h4>' . htmlspecialchars($announcement['title']) . '</h4>';
                                echo '<p>' . htmlspecialchars($announcement['content']) . '</p>';
                                echo '<div class="announcement-meta">';
                                echo '<span>Posted on: ' . date('M d, Y h:i A', strtotime($announcement['date_posted'])) . '</span>';
                                echo '<button onclick="deleteAnnouncement(' . $announcement['id'] . ')" class="delete-btn">Delete</button>';
                                echo '</div>';
                                echo '</div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Students Section -->
                <div id="students" class="students-section" style="display: none;">
                    <h2 class="section-title">Students Information</h2>
                    <div class="students-controls">
                        <div class="left-controls">
                            <button onclick="openAddStudentModal()" class="action-btn add-btn">Add Student</button>
                            <button onclick="resetAllSessions()" class="action-btn reset-btn">Reset All Sessions</button>
                        </div>
                        <div class="right-controls">
                            <input type="text" id="studentSearch" onkeyup="searchStudents()" placeholder="Search students..." class="search-input">
                        </div>
                    </div>
                    <div class="table-container">
                        <table id="studentsTable">
                            <thead>
                                <tr>
                                    <th>ID Number</th>
                                    <th>Name</th>
                                    <th>Year Level</th>
                                    <th>Course</th>
                                    <th>Remaining Sessions</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT * FROM users WHERE idno != '00' ORDER BY lastname ASC";
                                $result = mysqli_query($conn, $query);
                                while ($student = mysqli_fetch_assoc($result)) {
                                    $course_name = isset($courses[$student['course']]) ? $courses[$student['course']] : 'Unknown';
                                    $remaining_sessions = isset($student['remaining_sessions']) ? $student['remaining_sessions'] : 10;
                                    
                                    echo '<tr>';
                                    echo '<td>' . htmlspecialchars($student['idno']) . '</td>';
                                    echo '<td>' . htmlspecialchars($student['lastname'] . ', ' . $student['firstname'] . ' ' . $student['midname']) . '</td>';
                                    echo '<td>' . htmlspecialchars($student['yearlvl']) . '</td>';
                                    echo '<td>' . htmlspecialchars($course_name) . '</td>';
                                    echo '<td>' . htmlspecialchars($remaining_sessions) . '</td>';
                                    echo '<td class="actions">';
                                    echo '<button onclick="editStudent(\'' . $student['idno'] . '\')" class="edit-btn">Edit</button> ';
                                    echo '<button onclick="deleteStudent(\'' . $student['idno'] . '\')" class="delete-btn">Delete</button>';
                                    echo '</td>';
                                    echo '</tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php } else { ?>
            <!-- Student Dashboard -->
            <div class="student-dashboard">
                <div class="student-info-container">
        <div id="profile" class="info-section">
            <h3>User Information</h3>
            <?php if (!empty($user['photo'])): ?>
                            <img src="<?php echo 'uploads/' . $user['photo']; ?>" alt="User Photo">
            <?php endif; ?>
            <p><strong>Name:</strong> <?php echo $user['firstname'] . ' ' . $user['midname'] . ' ' . $user['lastname']; ?></p>
            <p><strong>Course:</strong> <?php echo $user['course_name']; ?></p>
            <p><strong>Year Level:</strong> <?php echo $user['yearlvl']; ?></p>
            <p><strong>Email:</strong> <?php echo $user['email']; ?></p>
            <button onclick="openEditModal()">Edit Profile</button>
        </div>
                </div>

                <!-- Student Announcements Section -->
                <div class="student-announcements">
                    <h3>Announcements</h3>
                    <div class="announcements-list">
                        <?php
                        $query = "SELECT * FROM announcements WHERE status = 'active' ORDER BY date_posted DESC";
                        $result = mysqli_query($conn, $query);
                        while ($announcement = mysqli_fetch_assoc($result)) {
                            echo '<div class="announcement-item">';
                            echo '<h4>' . htmlspecialchars($announcement['title']) . '</h4>';
                            echo '<p>' . htmlspecialchars($announcement['content']) . '</p>';
                            echo '<div class="announcement-meta">';
                            echo '<span>Posted on: ' . date('M d, Y h:i A', strtotime($announcement['date_posted'])) . '</span>';
                            echo '</div>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>

    <!-- Edit Profile Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeEditModal()">&times;</span>
            <h3>Edit Profile</h3>
            <form action="update_profile.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="current_photo" value="<?php echo $user['photo']; ?>">
                
                <label for="firstname">First Name:</label>
                <input type="text" name="firstname" value="<?php echo $user['firstname']; ?>" required>

                <label for="midname">Middle Name:</label>
                <input type="text" name="midname" value="<?php echo $user['midname']; ?>">

                <label for="lastname">Last Name:</label>
                <input type="text" name="lastname" value="<?php echo $user['lastname']; ?>" required>

                <label for="email">Email:</label>
                <input type="email" name="email" value="<?php echo $user['email']; ?>" required>

                <label for="course">Course:</label>
                <select name="course" required>
                    <option value="1" <?php echo ($user['course'] == 1) ? 'selected' : ''; ?>>BSIT</option>
                    <option value="2" <?php echo ($user['course'] == 2) ? 'selected' : ''; ?>>BSA</option>
                    <option value="3" <?php echo ($user['course'] == 3) ? 'selected' : ''; ?>>BSCS</option>
                    <option value="4" <?php echo ($user['course'] == 4) ? 'selected' : ''; ?>>BSCRIM</option>
                </select>

                <label for="yearlvl">Year Level:</label>
                <select name="yearlvl" required>
                    <option value="1" <?php echo ($user['yearlvl'] == 1) ? 'selected' : ''; ?>>1</option>
                    <option value="2" <?php echo ($user['yearlvl'] == 2) ? 'selected' : ''; ?>>2</option>
                    <option value="3" <?php echo ($user['yearlvl'] == 3) ? 'selected' : ''; ?>>3</option>
                    <option value="4" <?php echo ($user['yearlvl'] == 4) ? 'selected' : ''; ?>>4</option>
                </select>

                <label for="photo">Photo:</label>
                <div class="file-input-container">
                    <input type="file" name="photo" id="photo" accept="image/*" class="file-input">
                    <label for="photo" class="file-label">Choose File</label>
                    <span id="file-name" class="file-name">No file chosen</span>
                </div>
                <?php if (!empty($user['photo'])): ?>
                    <div class="current-photo">
                        <p>Current Photo:</p>
                        <img src="<?php echo 'uploads/' . $user['photo']; ?>" alt="Current Photo">
                    </div>
                <?php endif; ?>

                <label for="password">New Password (leave blank to keep current):</label>
                <input type="password" name="password">

                <input type="submit" value="Update Profile">
            </form>
        </div>
    </div>

    <!-- Add Student Modal -->
    <div id="addStudentModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeAddStudentModal()">&times;</span>
            <h3>Add New Student</h3>
            <form action="add_student.php" method="post">
                <label for="idno">ID Number:</label>
                <input type="text" name="idno" required>

                <label for="firstname">First Name:</label>
                <input type="text" name="firstname" required>

                <label for="midname">Middle Name:</label>
                <input type="text" name="midname">

                <label for="lastname">Last Name:</label>
                <input type="text" name="lastname" required>

                <label for="email">Email:</label>
                <input type="email" name="email" required>

                <label for="course">Course:</label>
                <select name="course" required>
                    <option value="1">BSIT</option>
                    <option value="2">BSA</option>
                    <option value="3">BSCS</option>
                    <option value="4">BSCRIM</option>
                </select>

                <label for="yearlvl">Year Level:</label>
                <select name="yearlvl" required>
                    <option value="1">1st Year</option>
                    <option value="2">2nd Year</option>
                    <option value="3">3rd Year</option>
                    <option value="4">4th Year</option>
                </select>

                <label for="password">Password:</label>
                <input type="password" name="password" required>

                <input type="submit" value="Add Student">
            </form>
        </div>
    </div>

    <!-- New Announcement Modal -->
    <div id="announcementModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeAnnouncementModal()">&times;</span>
            <h3>New Announcement</h3>
            <form action="post_announcement.php" method="post">
                <label for="title">Title:</label>
                <input type="text" name="title" required>

                <label for="content">Content:</label>
                <textarea name="content" required></textarea>

                <input type="submit" value="Post Announcement">
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Toggle sidebar
        function toggleSidebar() {
            const sidebar = document.getElementById("sidebar");
            const content = document.getElementById("content");
            sidebar.classList.toggle("collapsed");
            content.classList.toggle("expanded");
        }

        // Open Edit Profile Modal
        function openEditModal() {
            document.getElementById("editModal").style.display = "flex";
        }

        // Close Edit Profile Modal
        function closeEditModal() {
            document.getElementById("editModal").style.display = "none";
        }

        // Close modal if clicked outside
        window.onclick = function (event) {
            const modal = document.getElementById("editModal");
            if (event.target === modal) {
                closeEditModal();
            }
        };

        // File input name display
        document.getElementById('photo').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || 'No file chosen';
            document.getElementById('file-name').textContent = fileName;
        });

        // Open Announcement Modal
        function openAnnouncementModal() {
            document.getElementById("announcementModal").style.display = "flex";
        }

        // Close Announcement Modal
        function closeAnnouncementModal() {
            document.getElementById("announcementModal").style.display = "none";
        }

        // Delete Announcement
        function deleteAnnouncement(id) {
            if (confirm('Are you sure you want to delete this announcement?')) {
                window.location.href = 'delete_announcement.php?id=' + id;
            }
        }

        // Language Usage Chart
        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) { ?>
            const ctx = document.getElementById('languageChart').getContext('2d');
            const languageData = {
                labels: ['C#', 'C', 'Java', 'ASP.Net', 'PHP'],
                datasets: [{
                    data: [
                        <?php
                        $languages = ['C#', 'C', 'Java', 'ASP.Net', 'PHP'];
                        foreach ($languages as $lang) {
                            $query = "SELECT COUNT(*) as count FROM sit_in_records WHERE language_used = '$lang'";
                            $result = mysqli_query($conn, $query);
                            $row = mysqli_fetch_assoc($result);
                            echo $row['count'] . ',';
                        }
                        ?>
                    ],
                    backgroundColor: [
                        '#FF6B6B',
                        '#4ECDC4',
                        '#45B7D1',
                        '#96CEB4',
                        '#FFEEAD'
                    ]
                }]
            };

            new Chart(ctx, {
                type: 'pie',
                data: languageData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        <?php } ?>

        // Students Section Functions
        function searchStudents() {
            var input = document.getElementById("studentSearch");
            var filter = input.value.toUpperCase();
            var table = document.getElementById("studentsTable");
            var tr = table.getElementsByTagName("tr");

            for (var i = 1; i < tr.length; i++) {
                var tdName = tr[i].getElementsByTagName("td")[1];
                var tdId = tr[i].getElementsByTagName("td")[0];
                if (tdName || tdId) {
                    var nameText = tdName.textContent || tdName.innerText;
                    var idText = tdId.textContent || tdId.innerText;
                    if (nameText.toUpperCase().indexOf(filter) > -1 || idText.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }

        function editStudent(idno) {
            // Implement edit student functionality
            window.location.href = 'edit_student.php?id=' + idno;
        }

        function deleteStudent(idno) {
            if (confirm('Are you sure you want to delete this student?')) {
                window.location.href = 'delete_student.php?id=' + idno;
            }
        }

        function resetAllSessions() {
            if (confirm('Are you sure you want to reset all student sessions to 10?')) {
                window.location.href = 'reset_sessions.php';
            }
        }

        // Navigation Functions
        function showSection(sectionId) {
            // Hide all sections first
            document.querySelectorAll('.admin-dashboard > div').forEach(div => {
                div.style.display = 'none';
            });
            // Show the selected section
            document.getElementById(sectionId).style.display = 'block';
        }

        // Add click event listeners to all navigation links
        document.addEventListener('DOMContentLoaded', function() {
            // Show home section by default
            showSection('home');

            // Add click handlers for all navigation links
            document.querySelectorAll('.sidebar ul li a').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const sectionId = this.getAttribute('href').substring(1);
                    showSection(sectionId);
                });
            });
        });

        // Show students section when clicking the menu item
        document.querySelector('a[href="#students"]').addEventListener('click', function(e) {
            e.preventDefault();
            // Hide all sections first
            document.querySelectorAll('.admin-dashboard > div').forEach(div => {
                div.style.display = 'none';
            });
            // Show students section
            document.getElementById('students').style.display = 'block';
        });

        // Open Add Student Modal
        function openAddStudentModal() {
            document.getElementById("addStudentModal").style.display = "flex";
        }

        // Close Add Student Modal
        function closeAddStudentModal() {
            document.getElementById("addStudentModal").style.display = "none";
        }

        // Announcement Modal Functions
        function openAnnouncementModal() {
            document.getElementById('announcementModal').style.display = 'block';
        }

        function closeAnnouncementModal() {
            document.getElementById('announcementModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            var addModal = document.getElementById('addStudentModal');
            var announcementModal = document.getElementById('announcementModal');
            var editModal = document.getElementById('editModal');
            
            if (event.target == addModal) {
                addModal.style.display = "none";
            }
            if (event.target == announcementModal) {
                announcementModal.style.display = "none";
            }
            if (event.target == editModal) {
                editModal.style.display = "none";
            }
        }

        // File input display
        document.getElementById('photo').addEventListener('change', function(e) {
            var fileName = e.target.files[0] ? e.target.files[0].name : 'No file chosen';
            document.getElementById('file-name').textContent = fileName;
        });
    </script>   
</body>
</html>