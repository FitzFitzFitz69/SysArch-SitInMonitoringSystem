<?php
// This script adds the notifications module to the student section

// Read the original file
$homepageContent = file_get_contents('homepage.php');

// First, make sure the notifications table exists in the database
$createNotificationsTableSQL = "
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(15) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_status TINYINT(1) DEFAULT 0
);";

// Add notifications section to student sidebar
$sidebarPattern = '/<!-- Student Sidebar Links -->.*?<li><a href="#feedback".*?<\/a><\/li>\s*<\/ul>/s';
$sidebarReplacement = '<!-- Student Sidebar Links -->
                <li><a href="#profile-section" onclick="showStudentProfile(); return false;">Profile</a></li>
                <li><a href="#rules" onclick="showStudentRules(); return false;">Rules</a></li>
                <li><a href="#history" onclick="showStudentHistory(); return false;">History</a></li>
                <li><a href="#reservation" onclick="showStudentReservation(); return false;">Make Reservation</a></li>
                <li><a href="#student-lab-resources" onclick="showStudentLabResources(); return false;">Lab Resources</a></li>
                <li><a href="#feedback" onclick="showStudentFeedback(); return false;">Feedback</a></li>
                <li><a href="#notifications" onclick="showStudentNotifications(); return false;">Notifications <span id="notification-count" class="notification-badge"></span></a></li>
            </ul>';

$homepageContent = preg_replace($sidebarPattern, $sidebarReplacement, $homepageContent);

// Add the notifications section placeholder to the student section
$studentSectionsPattern = '/<div id="feedback" class="section-placeholder".*?<\/div>\s*<\/div>\s*<\/div>\s*<!-- End Student Sections -->/s';
$notificationsSection = '
            <div id="feedback" class="section-placeholder" style="display: none; margin-top: 120px;">
                <!-- Existing feedback section content -->
            </div>
            
            <!-- Notifications Section -->
            <div id="notifications" class="section-placeholder" style="display: none; margin-top: 120px;">
                <h2 style="text-align: center; margin-bottom: 30px; color: #333;">Notifications</h2>
                <a href="#" class="back-btn" onclick="showStudentProfile(); return false;" style="background-color: #E0B0FF; color: white; padding: 8px 15px; border-radius: 5px; text-decoration: none; display: inline-block; margin-bottom: 20px;">Back to Profile</a>
                
                <div class="notifications-container">
                    <?php
                    // Get notifications for current student
                    $student_id = $_SESSION["idno"];
                    $notifications_query = "SELECT * FROM notifications WHERE student_id = '$student_id' ORDER BY created_at DESC";
                    $notifications_result = mysqli_query($conn, $notifications_query);
                    
                    if (mysqli_num_rows($notifications_result) > 0) {
                        // Count unread notifications
                        $unread_count = 0;
                        $notifications = [];
                        
                        while ($notification = mysqli_fetch_assoc($notifications_result)) {
                            $notifications[] = $notification;
                            if ($notification["read_status"] == 0) {
                                $unread_count++;
                            }
                        }
                        
                        // Show unread count
                        echo "<div class='unread-count' style='margin-bottom: 15px; padding: 10px 15px; background-color: #f8f9fa; border-radius: 5px; display: inline-block;'>";
                        echo "<strong>Unread:</strong> " . $unread_count;
                        
                        // Add mark all as read button if there are unread notifications
                        if ($unread_count > 0) {
                            echo " <a href='mark_all_notifications_read.php' style='margin-left: 15px; color: #E0B0FF; text-decoration: none;'>Mark all as read</a>";
                        }
                        echo "</div>";
                        
                        // Display notifications
                        foreach ($notifications as $notification) {
                            $read_class = $notification["read_status"] == 0 ? "unread" : "read";
                            $date = date("M d, Y h:i A", strtotime($notification["created_at"]));
                            
                            echo "<div class='notification-item $read_class'>";
                            echo "<div class='notification-header'>";
                            echo "<h3>" . htmlspecialchars($notification["title"]) . "</h3>";
                            echo "<span class='notification-time'>" . $date . "</span>";
                            echo "</div>";
                            echo "<div class='notification-body'>" . nl2br(htmlspecialchars($notification["message"])) . "</div>";
                            
                            // Mark as read button for unread notifications
                            if ($notification["read_status"] == 0) {
                                echo "<div class='notification-actions'>";
                                echo "<a href='mark_notification_read.php?id=" . $notification["id"] . "' class='read-btn'>Mark as read</a>";
                                echo "</div>";
                            }
                            
                            echo "</div>";
                        }
                    } else {
                        echo "<div class='empty-notifications'>";
                        echo "<div style='text-align: center; padding: 40px 20px; background-color: #f9f9f9; border-radius: 8px;'>";
                        echo "<p style='margin-bottom: 10px; color: #666; font-size: 16px;'>No notifications yet</p>";
                        echo "<p style='color: #888; font-size: 14px;'>You'll receive notifications about reservations, sit-in sessions, and system updates here.</p>";
                        echo "</div>";
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <!-- End Student Sections -->';

$homepageContent = preg_replace($studentSectionsPattern, $notificationsSection, $homepageContent);

// Add necessary JavaScript function for showing notifications
$jsPattern = '/function showStudentFeedback\(\) {.*?}\s*/s';
$jsReplacement = 'function showStudentFeedback() {
        hideAllStudentSections();
        document.getElementById("feedback").style.display = "block";
    }
    
    function showStudentNotifications() {
        hideAllStudentSections();
        document.getElementById("notifications").style.display = "block";
        
        // Mark notifications as seen in UI immediately
        const badge = document.getElementById("notification-count");
        if (badge) {
            badge.style.display = "none";
        }
    }
    ';

$homepageContent = preg_replace($jsPattern, $jsReplacement, $homepageContent);

// Add CSS for notifications
$notificationsCss = '
        /* Notifications Styles */
        .notifications-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .notification-item {
            background-color: white;
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            border-left: 4px solid #ccc;
            transition: all 0.2s ease;
        }
        
        .notification-item.unread {
            border-left-color: #E0B0FF;
            background-color: #fcfaff;
        }
        
        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .notification-header h3 {
            margin: 0;
            font-size: 16px;
            color: #333;
        }
        
        .notification-time {
            color: #888;
            font-size: 12px;
        }
        
        .notification-body {
            color: #555;
            line-height: 1.5;
            margin-bottom: 10px;
        }
        
        .notification-actions {
            text-align: right;
            padding-top: 10px;
            border-top: 1px solid #f0f0f0;
        }
        
        .read-btn {
            display: inline-block;
            padding: 5px 12px;
            background-color: #f0f0f0;
            color: #666;
            border-radius: 4px;
            font-size: 12px;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        
        .read-btn:hover {
            background-color: #e0e0e0;
            color: #333;
        }
        
        .notification-badge {
            background-color: #E0B0FF;
            color: white;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 18px;
            height: 18px;
            font-size: 11px;
            margin-left: 5px;
        }
        
        /* Hide notification badge when zero */
        .notification-badge:empty {
            display: none;
        }';

// Add the CSS to the style tag
$stylePattern = '/<style>(.*?)<\/style>/s';
$styleReplacement = '<style>$1
    ' . $notificationsCss . '
</style>';

$homepageContent = preg_replace($stylePattern, $styleReplacement, $homepageContent);

// Add JavaScript to update notification count on page load
$bodyPattern = '/<body.*?>/';
$notificationCountScript = '<body onload="updateNotificationCount()">
    <script>
    function updateNotificationCount() {
        // Get unread notification count from PHP
        <?php
        if (isset($_SESSION["idno"]) && !isset($_SESSION["is_admin"])) {
            $student_id = $_SESSION["idno"];
            $count_query = "SELECT COUNT(*) as count FROM notifications WHERE student_id = \'$student_id\' AND read_status = 0";
            $count_result = mysqli_query($conn, $count_query);
            $unread_count = mysqli_fetch_assoc($count_result)["count"] ?? 0;
            
            echo "const unreadCount = $unread_count;";
        } else {
            echo "const unreadCount = 0;";
        }
        ?>
        
        // Update notification badge
        const badge = document.getElementById("notification-count");
        if (badge && unreadCount > 0) {
            badge.textContent = unreadCount;
            badge.style.display = "inline-flex";
        }
    }
    </script>';

$homepageContent = preg_replace($bodyPattern, $notificationCountScript, $homepageContent);

// Create the mark_notification_read.php file
$markReadFile = '<?php
session_start();
include("database.php");

// Check if user is logged in
if (!isset($_SESSION["idno"])) {
    header("Location: index.php");
    exit();
}

$student_id = $_SESSION["idno"];

// Check if notification ID is provided
if (isset($_GET["id"]) && is_numeric($_GET["id"])) {
    $notification_id = mysqli_real_escape_string($conn, $_GET["id"]);
    
    // Update the notification as read
    $update_query = "UPDATE notifications SET read_status = 1 
                    WHERE id = \'$notification_id\' AND student_id = \'$student_id\'";
    
    if (mysqli_query($conn, $update_query)) {
        $_SESSION["message"] = "Notification marked as read.";
    } else {
        $_SESSION["message"] = "Error updating notification: " . mysqli_error($conn);
    }
}

// Redirect back to notifications page
header("Location: homepage.php#notifications");
exit();
?>';
file_put_contents("mark_notification_read.php", $markReadFile);

// Create the mark_all_notifications_read.php file
$markAllReadFile = '<?php
session_start();
include("database.php");

// Check if user is logged in
if (!isset($_SESSION["idno"])) {
    header("Location: index.php");
    exit();
}

$student_id = $_SESSION["idno"];

// Update all notifications as read for this student
$update_query = "UPDATE notifications SET read_status = 1 
                WHERE student_id = \'$student_id\' AND read_status = 0";

if (mysqli_query($conn, $update_query)) {
    $_SESSION["message"] = "All notifications marked as read.";
} else {
    $_SESSION["message"] = "Error updating notifications: " . mysqli_error($conn);
}

// Redirect back to notifications page
header("Location: homepage.php#notifications");
exit();
?>';
file_put_contents("mark_all_notifications_read.php", $markAllReadFile);

// Create test notification for development
$createTestNotification = '<?php
session_start();
include("database.php");

// Check if notifications table exists
$table_query = mysqli_query($conn, "SHOW TABLES LIKE \'notifications\'");
if (mysqli_num_rows($table_query) == 0) {
    // Create notifications table
    $create_table = "CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id VARCHAR(15) NOT NULL,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        read_status TINYINT(1) DEFAULT 0
    )";
    
    if (!mysqli_query($conn, $create_table)) {
        die("Error creating notifications table: " . mysqli_error($conn));
    }
    
    echo "Notifications table created.<br>";
}

if (isset($_GET["test"]) && $_GET["test"] == "create") {
    // Create test notification for first student
    $students_query = "SELECT idno FROM users WHERE idno != \'00\' LIMIT 1";
    $students_result = mysqli_query($conn, $students_query);
    
    if (mysqli_num_rows($students_result) > 0) {
        $student = mysqli_fetch_assoc($students_result);
        $student_id = $student["idno"];
        
        $title = "Test Notification";
        $message = "This is a test notification to verify the notifications system is working.";
        
        $insert_query = "INSERT INTO notifications (student_id, title, message) 
                         VALUES (\'$student_id\', \'$title\', \'$message\')";
        
        if (mysqli_query($conn, $insert_query)) {
            echo "Test notification created for student ID: $student_id<br>";
        } else {
            echo "Error creating test notification: " . mysqli_error($conn) . "<br>";
        }
    } else {
        echo "No students found in the database.<br>";
    }
}

echo "<a href=\'homepage.php\'>Return to Homepage</a>";
?>';
file_put_contents("create_test_notification.php", $createTestNotification);

// Write the updated content back to the file
file_put_contents('homepage.php', $homepageContent);

echo "Notifications module added to the student section successfully!";
?> 