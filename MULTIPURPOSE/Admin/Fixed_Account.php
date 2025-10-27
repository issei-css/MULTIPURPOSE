<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

include(__DIR__ . '/../config/dbconnect.php'); // ✅ correct relative path
$conn = connectDB(); // ✅ establish the DB connection

$message = "";

$users = $conn->query("SELECT id, first_name, last_name FROM users WHERE role = 'member'");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_POST['user_id'];
    $amount = $_POST['amount'];
    $term = $_POST['term_months'];
    $start_date = $_POST['start_date'];

    $stmt = $conn->prepare("INSERT INTO fixed_accounts (user_id, amount, term_months, start_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("idis", $user_id, $amount, $term, $start_date);

    if ($stmt->execute()) {
        $message = "Fixed deposit created successfully.";
    } else {
        $message = "Error adding deposit.";
    }
}

$deposits = $conn->query("
    SELECT f.*, u.first_name, u.last_name 
    FROM fixed_accounts f 
    JOIN users u ON f.user_id = u.id
    ORDER BY f.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fixed Deposits - Admin Dashboard</title>
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
            color: #6c757d;
            text-decoration: none;
            margin-bottom: 20px;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .back-link:hover {
            color: #212529;
        }
        
        .section-header {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #212529;
        }
        
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
        }
        
        .message-success {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        
        .message-error {
            background-color: #f8d7da;
            color: #842029;
        }
        
        .form-card {
            background-color: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .form-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #212529;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #495057;
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border-radius: 5px;
            border: 1px solid #ced4da;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            border-color: #86b7fe;
            outline: 0;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        
        .btn {
            padding: 12px 20px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            font-size: 16px;
        }
        
        .btn-primary {
            background-color: #0d6efd;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #0b5ed7;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .data-table th {
            text-align: left;
            padding: 15px;
            background-color: #f8f9fa;
            color: #495057;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }
        
        .data-table td {
            padding: 15px;
            border-top: 1px solid #e9ecef;
        }
        
        .data-table tr:hover {
            background-color: #f8f9fa;
        }
        
        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-active {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #664d03;
        }
        
        .status-closed {
            background-color: #e2e3e5;
            color: #41464b;
        }
    </style>
</head>
<body>
    <!-- Sidebar Navigation -->
   
    
    <!-- Main Content -->
    <div class="content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="welcome">Fixed Deposit Accounts</div>
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
        
        <a href="admin_dashboard.php" class="back-link">← Back to Dashboard</a>
        
        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'Error') !== false ? 'message-error' : 'message-success'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <!-- Add Fixed Deposit Form -->
        <div class="form-card">
            <div class="form-title">Add New Fixed Deposit</div>
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Member</label>
                    <select name="user_id" class="form-control" required>
                        <option value="">-- Select Member --</option>
                        <?php while($u = $users->fetch_assoc()): ?>
                            <option value="<?php echo $u['id']; ?>">
                                <?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Amount (₱)</label>
                    <input type="number" name="amount" step="0.01" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Term (months)</label>
                    <input type="number" name="term_months" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Add Fixed Deposit</button>
            </form>
        </div>
        
        <!-- Existing Fixed Deposits Table -->
        <div class="section-header">Existing Fixed Deposits</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Member</th>
                    <th>Amount</th>
                    <th>Term (months)</th>
                    <th>Start Date</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; while($row = $deposits->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                        <td>₱<?php echo number_format($row['amount'], 2); ?></td>
                        <td><?php echo $row['term_months']; ?></td>
                        <td><?php echo $row['start_date']; ?></td>
                        <td>
                            <span class="status status-<?php echo strtolower($row['status']); ?>">
                                <?php echo ucfirst($row['status']); ?>
                            </span>
                        </td>
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
                    localStorage.removeItem('user_token');
                    
                    // Redirect to login page
                    window.location.href = '../index.php';
                }
            });
        });
    </script>
</body>
</html>