<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

include(__DIR__ . '/../config/dbconnect.php'); // ✅ correct relative path
$conn = connectDB(); // ✅ establish the DB connection
$message = "";

// Handle form submissions for all types
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['type']) && $_POST['type'] === 'savings') {
        $min = $_POST['min_balance'];
        $max = $_POST['max_balance'];
        $rate = $_POST['interest_rate'];

        $stmt = $conn->prepare("INSERT INTO savings_interest_rates (min_balance, max_balance, interest_rate) VALUES (?, ?, ?)");
        $stmt->bind_param("ddd", $min, $max, $rate);
        $stmt->execute();
    }

    if (isset($_POST['type']) && $_POST['type'] === 'fixed') {
        $term = $_POST['term_months'];
        $rate = $_POST['interest_rate'];

        $stmt = $conn->prepare("INSERT INTO fixed_deposit_interest (term_months, interest_rate) VALUES (?, ?)");
        $stmt->bind_param("id", $term, $rate);
        $stmt->execute();
    }

    if (isset($_POST['type']) && $_POST['type'] === 'time') {
        $term = $_POST['term_months'];
        $rate = $_POST['interest_rate'];

        $stmt = $conn->prepare("INSERT INTO time_deposit_interest (term_months, interest_rate) VALUES (?, ?)");
        $stmt->bind_param("id", $term, $rate);
        $stmt->execute();
    }

    $message = "Interest rule added successfully!";
}


$savingsRules = $conn->query("SELECT * FROM savings_interest_rates ORDER BY min_balance ASC");
$fixedRules = $conn->query("SELECT * FROM fixed_deposit_interest ORDER BY term_months ASC");
$timeRules = $conn->query("SELECT * FROM time_deposit_interest ORDER BY term_months ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deposit Interest Settings</title>
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
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #0d6efd;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: color 0.3s;
        }
        
        .back-link:hover {
            color: #0b5ed7;
        }
        
        .container {
            flex: 1;
            margin-left: 0;
            padding: 30px;
            width: 100%;
        }
        
        h2 {
            font-size: 28px;
            margin-bottom: 25px;
            color: #212529;
        }
        
        .message {
            background-color: #d1e7dd;
            color: #0f5132;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        section {
            background-color: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        h3 {
            font-size: 20px;
            margin-bottom: 20px;
            color: #212529;
            padding-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
        }
        
        form {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #495057;
        }
        
        input[type="number"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input[type="number"]:focus {
            border-color: #0d6efd;
            outline: none;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25);
        }
        
        button[type="submit"] {
            padding: 10px 20px;
            background-color: #0d6efd;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s;
            align-self: end;
        }
        
        button[type="submit"]:hover {
            background-color: #0b5ed7;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table thead th {
            text-align: left;
            padding: 12px;
            background-color: #f8f9fa;
            color: #6c757d;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }
        
        table tbody td {
            padding: 12px;
            border-top: 1px solid #e9ecef;
        }
        
        table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            
            .container {
                margin-left: 0;
                padding: 20px;
            }
            
            form {
                grid-template-columns: 1fr;
            }
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

        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- Savings -->
        <section>

         <div class="transaction-list">
            <div class="transaction-header">
                
                <a href="admin_dashboard.php" class="back-link">← Back to Dashboard</a>
            </div>
            <h3>Savings Interest Rules</h3>
            <form method="POST">
                <input type="hidden" name="type" value="savings">
                <div>
                    <label>Min Balance (₱)</label>
                    <input type="number" name="min_balance" step="0.01" required>
                </div>
                <div>
                    <label>Max Balance (₱)</label>
                    <input type="number" name="max_balance" step="0.01" required>
                </div>
                <div>
                    <label>Interest Rate (%)</label>
                    <input type="number" name="interest_rate" step="0.01" required>
                </div>
                <div>
                    <button type="submit">Add Savings Rule</button>
                </div>
            </form>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Min Balance</th>
                        <th>Max Balance</th>
                        <th>Interest Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i=1; while($r = $savingsRules->fetch_assoc()): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td>₱<?= number_format($r['min_balance'], 2) ?></td>
                            <td>₱<?= number_format($r['max_balance'], 2) ?></td>
                            <td><?= $r['interest_rate'] ?>%</td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>

        <!-- Fixed -->
        <section>
            <h3>Fixed Deposit Interest Rules</h3>
            <form method="POST">
                <input type="hidden" name="type" value="fixed">
                <div>
                    <label>Term (months)</label>
                    <input type="number" name="term_months" required>
                </div>
                <div>
                    <label>Interest Rate (%)</label>
                    <input type="number" name="interest_rate" step="0.01" required>
                </div>
                <div>
                    <button type="submit">Add Fixed Rule</button>
                </div>
            </form>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Term</th>
                        <th>Interest Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i=1; while($r = $fixedRules->fetch_assoc()): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= $r['term_months'] ?> months</td>
                            <td><?= $r['interest_rate'] ?>%</td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>

        <!-- Time -->
        <section>
            <h3>Time Deposit Interest Rules</h3>
            <form method="POST">
                <input type="hidden" name="type" value="time">
                <div>
                    <label>Term (months)</label>
                    <input type="number" name="term_months" required>
                </div>
                <div>
                    <label>Interest Rate (%)</label>
                    <input type="number" name="interest_rate" step="0.01" required>
                </div>
                <div>
                    <button type="submit">Add Time Rule</button>
                </div>
            </form>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Term</th>
                        <th>Interest Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i=1; while($r = $timeRules->fetch_assoc()): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= $r['term_months'] ?> months</td>
                            <td><?= $r['interest_rate'] ?>%</td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
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
            
            // Highlight active dropdown
            const currentPage = window.location.pathname.split("/").pop();
            const links = document.querySelectorAll('.dropdown-container a');
            links.forEach(link => {
                if (link.getAttribute('href') === currentPage) {
                    link.classList.add('active');
                    // Open the parent dropdown
                    const parentDropdown = link.closest('.dropdown-container');
                    if (parentDropdown) {
                        parentDropdown.style.display = 'block';
                        const dropdownBtn = parentDropdown.previousElementSibling;
                        if (dropdownBtn) {
                            dropdownBtn.classList.add('active');
                        }
                    }
                }
            });
            
            // User profile dropdown functionality
            const userProfile = document.getElementById('user-profile');
            const userDropdown = document.getElementById('user-dropdown');
            
            if (userProfile && userDropdown) {
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
            }
            
            // Logout functionality
            const logoutBtn = document.getElementById('logout-btn');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    if (confirm('Are you sure you want to logout?')) {
                        sessionStorage.clear();
                        localStorage.removeItem('user_token');
                        window.location.href = '../index.php';
                    }
                });
            }
        });
    </script>
</body>
</html>