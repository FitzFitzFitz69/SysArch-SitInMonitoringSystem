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
            margin-top: 20px;
        }

        .logout-btn:hover {
            background-color: #d8738f;
        }

        /* Main Content */
        .content {
            margin-left: 270px;
            padding: 20px;
            transition: margin-left 0.3s;
            display: flex;
            justify-content: center; /* Center content horizontally */
            align-items: center; /* Center content vertically */
            height: 100vh; /* Adjust height to account for header */
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
        }

        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            width: 400px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        .modal-content h3 {
            color: #333;
            margin-bottom: 15px;
        }

        .modal-content label {
            color: #555;
            margin: 10px 0;
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
        }

        .modal-content input[type="submit"] {
            width: 100%;
            margin-top: 15px;
            padding: 10px;
            background-color: #F7B4C6;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }

        .modal-content input[type="submit"]:hover {
            background-color: #e58aa3;
        }

        .close-btn {
            float: right;
            cursor: pointer;
            font-size: 20px;
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
            <li><a href="#profile">Profile</a></li>
            <li><a href="#announcement">Announcement</a></li>
            <li><a href="#sessions">Remaining Sessions</a></li>
            <li><a href="#rules">Sit-in Rules</a></li>
            <li><a href="#lab-rules">Lab Rules & Regulations</a></li>
            <li><a href="#history">Sit-in History</a></li>
            <li><a href="#reservation">Reservation</a></li>
        </ul>
        <button class="logout-btn" onclick="window.location.href='logout.php'">Logout</button>
    </div>

    <!-- Main Content -->
    <div class="content" id="content">
        <!-- Profile Section -->
        <div id="profile" class="info-section">
            <h3>User Information</h3>
            <p><strong>Name:</strong> <?php echo $user['firstname'] . ' ' . $user['midname'] . ' ' . $user['lastname']; ?></p>
            <p><strong>Course:</strong> <?php echo $user['course_name']; ?></p>
            <p><strong>Year Level:</strong> <?php echo $user['yearlvl']; ?></p>
            <p><strong>Email:</strong> <?php echo $user['email']; ?></p>
            <button onclick="openEditModal()">Edit Profile</button>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeEditModal()">&times;</span>
            <h3>Edit Profile</h3>
            <form action="update_profile.php" method="post">
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

                <input type="submit" value="Update Profile">
            </form>
        </div>
    </div>

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
    </script>
</body>
</html>