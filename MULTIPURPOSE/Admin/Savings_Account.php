<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

include(__DIR__ . '/../config/dbconnect.php'); // ✅ correct relative path
$conn = connectDB(); // ✅ establish the DB connection

$message = "";

// Fetch member list
$users = $conn->query("SELECT id, first_name, last_name FROM users WHERE role = 'member'");

// Handle balance update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_POST['user_id'];
    $balance = $_POST['balance'];

    // Check if user has savings account
    $check = $conn->query("SELECT * FROM savings_accounts WHERE user_id = $user_id");
    if ($check->num_rows > 0) {
        $conn->query("UPDATE savings_accounts SET balance = $balance WHERE user_id = $user_id");
        $message = "Balance updated.";
    } else {
        $conn->query("INSERT INTO savings_accounts (user_id, balance) VALUES ($user_id, $balance)");
        $message = "Savings account created.";
    }
}

// Fetch all savings accounts
$savings = $conn->query("
    SELECT sa.*, u.first_name, u.last_name 
    FROM savings_accounts sa
    JOIN users u ON sa.user_id = u.id
    ORDER BY sa.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Savings Accounts</title>
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
            display: inline-block;
            margin-bottom: 20px;
            color: #0d6efd;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .back-link:hover {
            color: #0a58ca;
        }
        
        h2 {
            margin-bottom: 25px;
            color: #212529;
            font-size: 28px;
        }
        
        h3 {
            margin: 25px 0 15px 0;
            color: #343a40;
            font-size: 22px;
        }
        
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            background-color: #d1e7dd;
            color: #0f5132;
            border-left: 4px solid #0f5132;
        }
        
        form {
            background-color: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
        }
        
        select, input[type="number"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        select:focus, input[type="number"]:focus {
            border-color: #86b7fe;
            outline: 0;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        
        .submit-btn {
            background-color: #0d6efd;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .submit-btn:hover {
            background-color: #0b5ed7;
        }
        
        hr {
            border: 0;
            height: 1px;
            background-color: #dee2e6;
            margin: 30px 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        th {
            text-align: left;
            padding: 15px;
            background-color: #f8f9fa;
            color: #6c757d;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }
        
        td {
            padding: 15px;
            border-top: 1px solid #e9ecef;
            color: #212529;
        }
        
        tbody tr:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    
    
    <!-- Main Content -->
    <div class="content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="welcome">Savings Accounts</div>
            <div class="user-profile" id="user-profile">
                <img src="/api/placeholder/40/40" alt="Admin Profile">
                <div class="user-info">
                    <div class="user-name"><?php echo $_SESSION['username']; ?></div>
                    <div class="user-role">Administrator</div>
                </div>
                
                <!-- User Dropdown Menu -->
                <div class="user-dropdown" id="user-dropdown">
                    <a href="admin_profile.php" class="user-dropdown-item">
                        <i>👤</i> Profile
                    </a>
                    <a href="admin_settings.php" class="user-dropdown-item">
                        <i>⚙️</i> Settings
                    </a>
                    <a href="#" class="user-dropdown-item logout-btn" id="logout-btn">
                        <i>🚪</i> Logout
                    </a>
                </div>
            </div>
        </div>
        
        <a href="admin_dashboard.php" class="back-link">← Back to Dashboard</a>
        
        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <!-- Add/Update Savings Account Form -->
        <form method="POST">
            <h3 style="margin-top: 0;">Manage Savings Account</h3>
            
            <label for="user_id">Select Member</label>
            <select name="user_id" id="user_id" required>
                <option value="">-- Select Member --</option>
                <?php while($u = $users->fetch_assoc()): ?>
                    <option value="<?php echo $u['id']; ?>">
                        <?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            
            <label for="balance">Balance (₱)</label>
            <input type="number" name="balance" id="balance" step="0.01" required>
            
            <button type="submit" class="submit-btn">Save Account</button>
        </form>
        
        <!-- Savings Accounts Table -->
        <h3>All Savings Accounts</h3>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Member</th>
                    <th>Balance</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; while($row = $savings->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                        <td>₱<?php echo number_format($row['balance'], 2); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($row['created_at'])); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
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
            
            // Open the Savings dropdown by default
            dropdownBtns[2].click();
            
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
                
                // Confirmation dialog
                if (confirm('Are you sure you want to logout?')) {
                    // Perform logout actions
                    sessionStorage.clear();
                    localStorage.removeItem('admin_token');
                    
                    // Redirect to login page
                    window.location.href = '../index.php';
                }
            });
        });
    </script>
</body>
</html>