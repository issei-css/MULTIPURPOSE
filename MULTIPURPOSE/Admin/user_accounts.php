<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../index.php");
  exit();
}

include(__DIR__ . '/../config/dbconnect.php'); // ✅ correct relative path
$conn = connectDB(); // ✅ establish the DB connection
$users = $conn->query("SELECT * FROM users");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Accounts</title>
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
        
        .back-link {
            display: inline-flex;
            align-items: center;
            color: #0d6efd;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        
        .back-link:hover {
            color: #0a58ca;
        }
        
        .back-link::before {
            content: "←";
            margin-right: 8px;
        }
        
        .data-container {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .data-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .data-title {
            font-size: 18px;
            font-weight: bold;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th {
            text-align: left;
            padding: 12px;
            background-color: #f8f9fa;
            color: #6c757d;
            font-weight: 600;
        }
        
        .data-table td {
            padding: 12px;
            border-top: 1px solid #e9ecef;
        }
        
        .data-table tr:hover {
            background-color: #f8f9fa;
        }
        
        .role-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        
        .role-admin {
            background-color: #cfe2ff;
            color: #0a58ca;
        }
        
        .role-member {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        
        .role-staff {
            background-color: #e2e3e5;
            color: #41464b;
        }
        
        .btn {
            padding: 8px 15px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
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
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #bb2d3b;
        }
        
        .action-btns {
            display: flex;
            gap: 8px;
        }
    </style>
</head>
<body>
   
    
    <!-- Main Content -->
    <div class="content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="welcome">User Accounts</div>
            <div class="user-profile" id="user-profile">
                <img src="/api/placeholder/40/40" alt="Admin Profile">
                <div class="user-info">
                    <div class="user-name">Admin</div>
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
        
        <!-- Back Link -->
        <a href="admin_dashboard.php" class="back-link">Back to Dashboard</a>
        
        <!-- User Data Container -->
        <div class="data-container">
            <div class="data-header">
                <div class="data-title">All User Accounts</div>
              
            </div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created</th>
                        
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; while ($u = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></td>
                        <td><?= htmlspecialchars($u['username']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td>
                            <span class="role-badge role-<?= strtolower($u['role']) ?>">
                                <?= ucfirst($u['role']) ?>
                            </span>
                        </td>
                        <td><?= date('Y-m-d', strtotime($u['created_at'])) ?></td>
                        
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script>
        // JavaScript for dropdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Dropdown menus
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
            
            // Set active menu item based on current page
            const currentPath = window.location.pathname;
            const filename = currentPath.substring(currentPath.lastIndexOf('/') + 1);
            
            const menuLinks = document.querySelectorAll('.dropdown-container a');
            menuLinks.forEach(link => {
                if (link.getAttribute('href') === filename) {
                    link.style.backgroundColor = '#31373d';
                    link.style.color = '#ffffff';
                    link.parentElement.style.display = 'block';
                    link.parentElement.previousElementSibling.classList.add('active');
                }
            });
            
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
                
                if (confirm('Are you sure you want to logout?')) {
                    sessionStorage.clear();
                    localStorage.removeItem('user_token');
                    window.location.href = '../index.php';
                }
            });
        });
        
        // Confirm delete function
        function confirmDelete(userId) {
            if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                window.location.href = 'delete_user.php?id=' + userId;
            }
        }
    </script>
</body>
</html>