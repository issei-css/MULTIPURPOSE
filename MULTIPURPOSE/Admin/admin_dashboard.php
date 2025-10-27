<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

include(__DIR__ . '/../config/dbconnect.php'); // ✅ correct relative path
$conn = connectDB(); // ✅ establish the DB connection

$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$profileImage = (!empty($user['profile_image']) && file_exists("../" . $user['profile_image']))
    ? "../" . $user['profile_image']
    : "../assets/default.png";

function safeCount($conn, $query) {
    $res = $conn->query($query);
    if (!$res) {
        error_log("Database query failed: " . $conn->error . " | Query: " . $query);
        return 0;
    }

    $row = $res->fetch_assoc();
    return $row['total'] ?? 0;
}

$totalLoans = safeCount($conn, "SELECT COUNT(*) AS total FROM loans");
$totalSavings = safeCount($conn, "SELECT COUNT(*) AS total FROM accounts WHERE type = 'savings'");
$totalDeposits = safeCount($conn, "SELECT COUNT(*) AS total FROM accounts WHERE type = 'time_deposit'");
$totalMembers = safeCount($conn, "SELECT COUNT(*) AS total FROM users WHERE role = 'member'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
        
        .transaction-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .transaction-table th {
            text-align: left;
            padding: 12px;
            background-color: #f8f9fa;
            color: #6c757d;
            font-weight: 600;
        }
        
        .transaction-table td {
            padding: 12px;
            border-top: 1px solid #e9ecef;
        }
        
        .transaction-table tr:hover {
            background-color: #f8f9fa;
        }
        
        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-completed {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #664d03;
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
    </style>
</head>
<body>
    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>Admin Portal</h2>
        </div>
        
        <div class="dropdown">
            <button class="dropdown-btn">Dashboard</button>
        </div>
        
        <div class="dropdown">
            <button class="dropdown-btn">Loan</button>
            <div class="dropdown-container">
                <a href="Loan_Request.php">Loan Application</a>
                <a href="Loan_Type.php">Loan Type</a>
                <a href="Loan_Amortization.php">Loan Amortization</a>
                <a href="Loan_Interest.php">Loan Interest</a>
            </div>
        </div>
        
        <div class="dropdown">
            <button class="dropdown-btn">Fixed</button>
            <div class="dropdown-container">
                <a href="Fixed_Account.php">Accounts</a>
                <a href="Fixed_Interest.php">Interest</a>
            </div>
        </div>
        
        <div class="dropdown">
            <button class="dropdown-btn">Savings</button>
            <div class="dropdown-container">
                <a href="Savings_Account.php">Accounts</a>
                <a href="Savings_Interest.php">Interest</a>
            </div>
        </div>
        
        <div class="dropdown">
            <button class="dropdown-btn">Time Deposit</button>
            <div class="dropdown-container">
                <a href="Deposit_Accounts.php">Accounts</a>
                <a href="Deposit_Interest.php">Interest</a>
            </div>
        </div>
        
        <div class="dropdown">
            <button class="dropdown-btn">Management</button>
            <div class="dropdown-container">
                <a href="member_profile.php">Member Profile</a>
                <a href="user_accounts.php">User Accounts</a>
            </div>
        </div>
        
        <div class="dropdown">
            <button class="dropdown-btn">About</button>
            <div class="dropdown-container">
                <a href="about_us.php">About Us</a>
                <a href="contact_us.php">Contact Us</a>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="welcome">Welcome, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>!</div>
            <div class="user-profile" id="user-profile">
                <img src="<?php echo $profileImage; ?>" alt="User Profile">
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . htmlspecialchars($user['last_name']))?></div>
                    <div class="user-role">admin</div>
                </div>
                
                <!-- User Dropdown Menu -->
                <div class="user-dropdown" id="user-dropdown">
                    <a href="profile.php" class="user-dropdown-item">
                        <i>👤</i> Profile
                    </a>
                    <a href="../logout.php" class="user-dropdown-item logout-btn" id="logout-btn">
                        <i>🚪</i> Logout
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Dashboard Cards -->
        <div class="dashboard-cards">
            <div class="dashboard-card">
                <div class="card-header">
                    <div class="card-title">Total Loans</div>
                    <div class="card-icon">📝</div>
                </div>
                <div class="card-amount"><?php echo $totalLoans; ?></div>
                <div class="card-subtitle">Active loan applications</div>
            </div>
            
            <div class="dashboard-card">
                <div class="card-header">
                    <div class="card-title">Savings Accounts</div>
                    <div class="card-icon">💰</div>
                </div>
                <div class="card-amount"><?php echo $totalSavings; ?></div>
                <div class="card-subtitle">Member savings accounts</div>
            </div>
            
            <div class="dashboard-card">
                <div class="card-header">
                    <div class="card-title">Time Deposits</div>
                    <div class="card-icon">⏱️</div>
                </div>
                <div class="card-amount"><?php echo $totalDeposits; ?></div>
                <div class="card-subtitle">Active time deposits</div>
            </div>
            
            <div class="dashboard-card">
                <div class="card-header">
                    <div class="card-title">Total Members</div>
                    <div class="card-icon">👥</div>
                </div>
                <div class="card-amount"><?php echo $totalMembers; ?></div>
                <div class="card-subtitle">Registered members</div>
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
            
            // Open the Dashboard dropdown by default
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
        });
    </script>
</body>
</html>