<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

include('../config/dbconnect.php');

$username = $_SESSION['username'];

$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$profileImage = (!empty($user['profile_image']) && file_exists("../" . $user['profile_image']))
    ? "../" . $user['profile_image']
    : "../assets/default.png";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            display: flex;
            background-color: #f5f5f5;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background-color: #212529;
            color: #fff;
            padding: 20px;
            display: flex;
            flex-direction: column;
            height: 100vh;
            position: fixed;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding-bottom: 20px;
            margin-bottom: 20px;
            border-bottom: 1px solid #444;
        }
        
        .dropdown {
            margin-bottom: 10px;
        }
        
        .dropdown-btn {
            width: 100%;
            background-color: #212529;
            color: #f8f9fa;
            padding: 12px 0;
            font-size: 18px;
            font-weight: bold;
            border: none;
            text-align: left;
            cursor: pointer;
            outline: none;
            transition: 0.3s;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .dropdown-btn:hover {
            color: #ffffff;
        }
        
        .dropdown-btn:after {
            content: '\002B';
            font-weight: bold;
            float: right;
            margin-left: 5px;
        }
        
        .active:after {
            content: "\2212";
        }
        
        .dropdown-container {
            display: none;
            padding-left: 15px;
            overflow: hidden;
        }
        
        .dropdown-container a {
            color: #adb5bd;
            text-decoration: none;
            padding: 10px;
            display: block;
            transition: all 0.3s;
            border-radius: 5px;
        }
        
        .dropdown-container a:hover {
            background-color: #31373d;
            color: #ffffff;
        }
        
        .content {
            flex: 1;
            margin-left: 250px;
            padding: 30px;
        }
        
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .welcome {
            font-size: 24px;
            font-weight: bold;
        }
        
        .user-profile {
            display: flex;
            align-items: center;
            background-color: #343a40;
            padding: 5px 15px;
            border-radius: 30px;
            color: white;
            cursor: pointer;
            position: relative;
        }
        
        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
        }
        
        .user-info {
            display: flex;
            flex-direction: column;
        }
        
        .user-name {
            font-weight: bold;
        }
        
        .user-role {
            font-size: 12px;
            opacity: 0.7;
        }
        
        .user-dropdown {
            position: absolute;
            top: 60px;
            right: 0;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            width: 150px;
            display: none;
            z-index: 100;
        }
        
        .user-dropdown.active {
            display: block;
        }
        
        .user-dropdown-item {
            padding: 12px 15px;
            display: flex;
            align-items: center;
            color: #212529;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .user-dropdown-item:hover {
            background-color: #f8f9fa;
        }
        
        .user-dropdown-item i {
            margin-right: 10px;
            font-size: 18px;
        }
        
        .logout-btn {
            color: #dc3545;
            font-weight: 600;
            border-top: 1px solid #dee2e6;
        }
        
        .profile-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .profile-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .profile-title {
            font-size: 24px;
            font-weight: bold;
            color: #212529;
            margin-bottom: 20px;
        }
        
        .profile-image {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        
        .profile-image img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid #e9ecef;
            object-fit: cover;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .profile-info {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }
        
        .info-box {
            padding: 15px;
            border-radius: 8px;
            background-color: #f8f9fa;
            transition: all 0.3s;
        }
        
        .info-box:hover {
            background-color: #e9ecef;
        }
        
        .info-box label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 5px;
        }
        
        .info-box span {
            font-size: 16px;
            color: #212529;
            font-weight: 500;
        }
        
        .button-group {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-primary {
            background-color: #0d6efd;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #0b5ed7;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #5c636a;
        }
        
        .back-icon {
            margin-right: 8px;
        }
        
        .edit-icon {
            margin-right: 8px;
        }
    </style>
</head>
<body>
   
    
    <!-- Main Content -->
    <div class="content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="welcome">My Profile</div>
            <div class="user-profile" id="user-profile">
                <img src="<?php echo $profileImage; ?>" alt="User Profile">
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($user['first_name']); ?></div>
                    <div class="user-role"><?php echo $user['role']; ?></div>
                </div>
                
                <!-- User Dropdown Menu -->
                <div class="user-dropdown" id="user-dropdown">
                    <a href="profile.php" class="user-dropdown-item">
                        <i>👤</i> Profile
                    </a>
                    
                    <a href="#" class="user-dropdown-item logout-btn" id="logout-btn">
                        <i>🚪</i> Logout
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Profile Container -->
        <div class="profile-container">
            <div class="profile-header">
                <div class="profile-image">
                    <img src="<?php echo $profileImage; ?>" alt="Profile Picture">
                </div>
                <div class="profile-title">
                    <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['middle_name'] . ' ' . $user['last_name']); ?>
                </div>
            </div>
            
            <div class="profile-info">
                <div class="info-box">
                    <label>Full Name:</label>
                    <span><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['middle_name'] . ' ' . $user['last_name']); ?></span>
                </div>
                
                <div class="info-box">
                    <label>Username:</label>
                    <span><?php echo htmlspecialchars($user['username']); ?></span>
                </div>
                
                <div class="info-box">
                    <label>Email:</label>
                    <span><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
                
                <div class="info-box">
                    <label>Birthday:</label>
                    <span><?php echo htmlspecialchars($user['birthday']); ?></span>
                </div>
                
                <div class="info-box">
                    <label>Gender:</label>
                    <span><?php echo htmlspecialchars($user['gender']); ?></span>
                </div>
                
                <div class="info-box">
                    <label>Age:</label>
                    <span><?php echo htmlspecialchars($user['age']); ?></span>
                </div>
                
                <div class="info-box">
                    <label>Role:</label>
                    <span><?php echo ucfirst($user['role']); ?></span>
                </div>
                
                <div class="info-box">
                    <label>Account Created At:</label>
                    <span><?php echo htmlspecialchars($user['created_at']); ?></span>
                </div>
            </div>
            
            <div class="button-group">
                <a href="admin_dashboard.php" class="btn btn-secondary">
                    <span class="back-icon">←</span> Back to Dashboard
                </a>
                <a href="edit_profile.php" class="btn btn-primary">
                    <span class="edit-icon">✏️</span> Edit Profile
                </a>
            </div>
        </div>
    </div>
    
    <script>
        // JavaScript for dropdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            var dropdownBtns = document.getElementsByClassName("dropdown-btn");
            
            for (var i = 0; i < dropdownBtns.length; i++) {
                dropdownBtns[i].addEventListener("click", function() {
                    this.classList.toggle("active");
                    var dropdownContent = this.nextElementSibling;
                    if (dropdownContent.style.display === "block") {
                        dropdownContent.style.display = "none";
                    } else {
                        dropdownContent.style.display = "block";
                    }
                });
            }
            
            // Default open Dashboard dropdown
            if (dropdownBtns.length > 0) {
                dropdownBtns[0].click();
            }
            
            // User profile dropdown functionality
            const userProfile = document.getElementById('user-profile');
            const userDropdown = document.getElementById('user-dropdown');
            
            userProfile.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdown.classList.toggle('active');
            });
            
            // Close dropdown when clicking elsewhere
            document.addEventListener('click', function(e) {
                if (!userProfile.contains(e.target)) {
                    userDropdown.classList.remove('active');
                }
            });
            
            // Logout functionality
            const logoutBtn = document.getElementById('logout-btn');
            logoutBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                // You can add a confirmation dialog if needed
                if (confirm('Are you sure you want to logout?')) {
                    // Perform logout actions
                    sessionStorage.clear();
                    localStorage.removeItem('user_token');
                    
                    // Redirect to index.php in the parent directory
                    window.location.href = '../index.php';
                }
            });
        });
    </script>
</body>
</html>