<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../index.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
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
        
        .contact-container {
            background-color: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 30px;
        }
        
        .contact-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .contact-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .contact-title {
            font-size: 22px;
            font-weight: bold;
            color: #212529;
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 12px 0;
            border-bottom: 1px solid #f5f5f5;
        }
        
        .contact-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        
        .contact-icon {
            width: 40px;
            height: 40px;
            background-color: #e9ecef;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: #495057;
            font-size: 18px;
        }
        
        .contact-text {
            font-size: 16px;
            color: #212529;
        }
        
        .contact-label {
            display: block;
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 3px;
        }
        
        .contact-value {
            font-weight: 500;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            color: #0d6efd;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        
        .back-link:hover {
            color: #0a58ca;
        }
        
        .back-icon {
            margin-right: 8px;
        }
    </style>
</head>
<body>
   
    
    <!-- Main Content -->
    <div class="content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="welcome">Contact Information</div>
            <div class="user-profile" id="user-profile">
                <img src="/api/placeholder/40/40" alt="User Profile">
                <div class="user-info">
                    <div class="user-name">Admin User</div>
                    <div class="user-role">administrator</div>
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
        
        <a href="admin_dashboard.php" class="back-link">
            <span class="back-icon">←</span> Back to Dashboard
        </a>
        
        <!-- Contact Information Container -->
        <div class="contact-container">
            <div class="contact-header">
                <div class="contact-title">Contact Details</div>
            </div>
            
            <div class="contact-item">
                <div class="contact-icon">📍</div>
                <div class="contact-text">
                    <span class="contact-label">Address</span>
                    <span class="contact-value">Poblacion, Pulilan, Bulacan</span>
                </div>
            </div>
            
            <div class="contact-item">
                <div class="contact-icon">📞</div>
                <div class="contact-text">
                    <span class="contact-label">Phone</span>
                    <span class="contact-value">0928-652-0262</span>
                </div>
            </div>
            
            <div class="contact-item">
                <div class="contact-icon">📧</div>
                <div class="contact-text">
                    <span class="contact-label">Email</span>
                    <span class="contact-value">sipaganjansenn@gmail.com</span>
                </div>
            </div>
            
            <div class="contact-item">
                <div class="contact-icon">💬</div>
                <div class="contact-text">
                    <span class="contact-label">Support</span>
                    <span class="contact-value">For any support concerns or technical issues, kindly reach out to our admin team directly.</span>
                </div>
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
            
            // Open the Settings dropdown by default (since we're on the Contact page)
            if (dropdownBtns.length >= 4) {
                dropdownBtns[3].click();
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