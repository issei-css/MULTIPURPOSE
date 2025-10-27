<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

include(__DIR__ . '/../config/dbconnect.php'); // ✅ correct relative path
$conn = connectDB(); // ✅ establish the DB connection

$message = "";

// Fetch loan types
$loan_types = $conn->query("SELECT * FROM loan_types");

// Insert new interest rate
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $loan_type_id = $_POST['loan_type_id'];
    $term_months = $_POST['term_months'];
    $rate = $_POST['rate'];

    if ($loan_type_id && is_numeric($term_months) && is_numeric($rate)) {
        $stmt = $conn->prepare("INSERT INTO interest_rates (loan_type_id, term_months, rate) VALUES (?, ?, ?)");
        $stmt->bind_param("iid", $loan_type_id, $term_months, $rate);
        if ($stmt->execute()) {
            $message = "Interest rate added!";
        } else {
            $message = "Error inserting rate.";
        }
    } else {
        $message = "Please fill in all fields correctly.";
    }
}

// Fetch existing interest rates
$rates = $conn->query("SELECT ir.*, lt.name AS loan_type 
                      FROM interest_rates ir 
                      JOIN loan_types lt ON ir.loan_type_id = lt.id
                      ORDER BY ir.loan_type_id, ir.term_months");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Interest Rates</title>
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
        
        .page-title {
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
        
        .content-panel {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .panel-title {
            font-size: 18px;
            font-weight: bold;
        }
        
        .message {
            background-color: #d1e7dd;
            color: #0f5132;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .error-message {
            background-color: #f8d7da;
            color: #842029;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #212529;
        }
        
        select, input[type="text"], input[type="number"] {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        select:focus, input:focus {
            outline: none;
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        
        .btn {
            padding: 10px 15px;
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
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #5c636a;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            color: #0d6efd;
            text-decoration: none;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
                
        .interest-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .interest-table th {
            text-align: left;
            padding: 12px;
            background-color: #f8f9fa;
            color: #6c757d;
            font-weight: 600;
        }
        
        .interest-table td {
            padding: 12px;
            border-top: 1px solid #e9ecef;
        }
        
        .interest-table tr:hover {
            background-color: #f8f9fa;
        }
        
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .form-col {
            flex: 1;
        }
        
        .section-divider {
            height: 1px;
            background-color: #dee2e6;
            margin: 30px 0;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    
    
    <!-- Main Content -->
    <div class="content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="page-title">Loan Interest Rates</div>
            <div class="user-profile" id="user-profile">
                <img src="/api/placeholder/40/40" alt="Admin Profile">
                <div class="user-info">
                    <div class="user-name">Admin User</div>
                    <div class="user-role">administrator</div>
                </div>
                
                <!-- User Dropdown Menu -->
                <div class="user-dropdown" id="user-dropdown">
                    <a href="profile.php" class="user-dropdown-item">
                        <i>👤</i> Profile
                    </a>
                    <a href="settings.php" class="user-dropdown-item">
                        <i>⚙️</i> Settings
                    </a>
                    <a href="#" class="user-dropdown-item logout-btn" id="logout-btn">
                        <i>🚪</i> Logout
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Add Interest Rate Panel -->
        <div class="content-panel">
            <div class="panel-header">
                <div class="panel-title">Add New Interest Rate</div>
                <a href="admin_dashboard.php" class="back-link">
                    <span>← Back to Dashboard</span>
                </a>
            </div>
            
            <?php if ($message): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label>Loan Type</label>
                            <select name="loan_type_id" required>
                                <option value="">-- Select Type --</option>
                                <?php while($lt = $loan_types->fetch_assoc()): ?>
                                    <option value="<?php echo $lt['id']; ?>"><?php echo htmlspecialchars($lt['name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label>Term (in months)</label>
                            <input type="number" name="term_months" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label>Interest Rate (%)</label>
                            <input type="number" name="rate" step="0.01" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Add Interest Rate</button>
                </div>
            </form>
        </div>
        
        <!-- Interest Rates Table Panel -->
        <div class="content-panel">
            <div class="panel-header">
                <div class="panel-title">Existing Interest Rates</div>
            </div>
            
            <table class="interest-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Loan Type</th>
                        <th>Term (months)</th>
                        <th>Rate (%)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($rates->num_rows > 0) {
                        $count = 1; 
                        while($row = $rates->fetch_assoc()): 
                    ?>
                        <tr>
                            <td><?php echo $count++; ?></td>
                            <td><?php echo htmlspecialchars($row['loan_type']); ?></td>
                            <td><?php echo $row['term_months']; ?></td>
                            <td><?php echo $row['rate']; ?>%</td>
                        </tr>
                    <?php 
                        endwhile;
                    } else {
                    ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 20px;">No interest rates defined yet.</td>
                        </tr>
                    <?php } ?>
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
            
            // Open the Loan Management dropdown by default
            if (dropdownBtns[1]) {  // Second dropdown is Loan Management
                dropdownBtns[1].click();
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