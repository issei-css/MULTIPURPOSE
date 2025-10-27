<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

include(__DIR__ . '/../config/dbconnect.php'); // ✅ correct relative path
$conn = connectDB(); // ✅ establish the DB connection

// Get all members
$members = $conn->query("SELECT id, first_name, last_name FROM users WHERE role = 'member'");

// Prepare data per member
$data = [];
while ($m = $members->fetch_assoc()) {
    $uid = $m['id'];

    // Savings
    $savings = $conn->query("SELECT balance FROM savings_accounts WHERE user_id = $uid")->fetch_assoc()['balance'] ?? 0;

    // Fixed deposits total
    $fixed = $conn->query("SELECT SUM(amount) as total FROM fixed_deposits WHERE user_id = $uid")->fetch_assoc()['total'] ?? 0;

    // Time deposits total
    $time = $conn->query("SELECT SUM(amount) as total FROM time_deposits WHERE user_id = $uid")->fetch_assoc()['total'] ?? 0;

    $data[] = [
        'name' => $m['first_name'] . ' ' . $m['last_name'],
        'savings' => $savings,
        'fixed' => $fixed,
        'time' => $time,
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Member Deposits</title>
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
        
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .dashboard-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .card-title {
            font-size: 18px;
            font-weight: bold;
            color: #212529;
        }
        
        .card-icon {
            width: 40px;
            height: 40px;
            background-color: #e9ecef;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #495057;
        }
        
        .card-amount {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
            color: #212529;
        }
        
        .card-subtitle {
            color: #6c757d;
            font-size: 14px;
        }
        
        .transaction-list {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .transaction-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .transaction-title {
            font-size: 18px;
            font-weight: bold;
        }
        
        .view-all {
            color: #0d6efd;
            text-decoration: none;
            font-size: 14px;
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
        
        /* Table styles */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th {
            text-align: left;
            padding: 12px;
            background-color: #f8f9fa;
            color: #6c757d;
            font-weight: 600;
        }
        
        table td {
            padding: 12px;
            border-top: 1px solid #e9ecef;
        }
        
        table tr:hover {
            background-color: #f8f9fa;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #0d6efd;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .back-link:hover {
            color: #0b5ed7;
            text-decoration: underline;
        }
        
        h2 {
            margin-bottom: 20px;
            color: #212529;
        }
        
        .container {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-left: 250px;
            margin-right: 20px;
            margin-top: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
   
    
    <!-- Main Content -->
    <div class="content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="welcome">Deposit Accounts Overview</div>
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
        
        <!-- Deposit Accounts Table -->
        <div class="transaction-list">
            <div class="transaction-header">
                <div class="transaction-title">Member Deposits Overview</div>
                <a href="admin_dashboard.php" class="back-link">← Back to Dashboard</a>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Member Name</th>
                        <th>Savings (₱)</th>
                        <th>Fixed Deposits (₱)</th>
                        <th>Time Deposits (₱)</th>
                        <th>Total (₱)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; foreach ($data as $d): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($d['name']); ?></td>
                            <td>₱<?php echo number_format($d['savings'], 2); ?></td>
                            <td>₱<?php echo number_format($d['fixed'], 2); ?></td>
                            <td>₱<?php echo number_format($d['time'], 2); ?></td>
                            <td><strong>₱<?php echo number_format($d['savings'] + $d['fixed'] + $d['time'], 2); ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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
            
            // Open the Deposits dropdown by default
            if (dropdownBtns.length > 2) {
                dropdownBtns[2].click();
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
                    // This could include clearing cookies/session storage
                    // and redirecting to the login page
                    
                    // For example:
                    // Clear session storage
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