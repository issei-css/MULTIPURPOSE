<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'member') {
    header("Location: login.php");
    exit();
}

include(__DIR__ . '/../config/dbconnect.php');
$conn = connectDB();

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
    <title>Member Dashboard</title>
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
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>Member Portal</h2>
        </div>
        <div class="dropdown">
            <button class="dropdown-btn">Dashboard</button>
        </div>
        <div class="dropdown">
            <button class="dropdown-btn">Loan</button>
            <div class="dropdown-container">
                <a href="loan-application.php">Loan Application</a>
                <a href="loan-types.php">Loan Types</a>
                <a href="loan-amortization.php">Loan Amortization</a>
                <a href="loan-interest.php">Loan Interest</a>
            </div>
        </div>
        <div class="dropdown">
            <button class="dropdown-btn">Fixed</button>
            <div class="dropdown-container">
                <a href="fixed-deposit.php">Deposit</a>
                <a href="fixed-withdrawal.php">Withdraw</a>
            </div>
        </div>
        <div class="dropdown">
            <button class="dropdown-btn">Savings</button>
            <div class="dropdown-container">
                <a href="savings-accounts.php">Accounts</a>
                <a href="savings-interest.php">Interest</a>
            </div>
        </div>
        <div class="dropdown">
            <button class="dropdown-btn">Time Deposit</button>
            <div class="dropdown-container">
                <a href="time-deposit-accounts.php">Accounts</a>
                <a href="time-deposit-interest.php">Interest</a>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="top-bar">
            <div class="welcome">Welcome, <?php echo htmlspecialchars($user['first_name']); ?>!</div>
            <div class="user-profile" id="user-profile">
                <img src="<?php echo $profileImage; ?>" alt="User Profile">
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                    <div class="user-role"><?php echo $user['role']; ?></div>
                </div>
                <div class="user-dropdown" id="user-dropdown">
                    <a href="profile_member.php" class="user-dropdown-item"><i>👤</i> Profile</a>
                    <a href="../logout.php" class="user-dropdown-item logout-btn"><i>🚪</i> Logout</a>
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
                <div class="card-amount">₱5,000,000</div>
                <div class="card-subtitle">Home Loan</div>
            </div>

            <!-- Banking Snapshot Cards -->
            <div class="dashboard-card">
                <div class="card-header">
                    <div class="card-title">Total Savings</div>
                    <div class="card-icon">💰</div>
                </div>
                <div class="card-amount">₱250,000</div>
                <div class="card-subtitle">Across all savings accounts</div>
            </div>

            <div class="dashboard-card">
                <div class="card-header">
                    <div class="card-title">Interest Earned</div>
                    <div class="card-icon">📈</div>
                </div>
                <div class="card-amount">₱12,350</div>
                <div class="card-subtitle">Year-to-date earnings</div>
            </div>

            <div class="dashboard-card">
                <div class="card-header">
                    <div class="card-title">Upcoming Maturity</div>
                    <div class="card-icon">⏳</div>
                </div>
                <div class="card-amount">July 15, 2025</div>
                <div class="card-subtitle">Fixed Deposit #1023</div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var dropdownBtns = document.getElementsByClassName("dropdown-btn");
            for (var i = 0; i < dropdownBtns.length; i++) {
                dropdownBtns[i].addEventListener("click", function () {
                    this.classList.toggle("active");
                    var dropdownContent = this.nextElementSibling;
                    dropdownContent.style.display = dropdownContent.style.display === "block" ? "none" : "block";
                });
            }

            const userProfile = document.getElementById('user-profile');
            const userDropdown = document.getElementById('user-dropdown');

            userProfile.addEventListener('click', function (e) {
                e.stopPropagation();
                userDropdown.classList.toggle('active');
            });

            document.addEventListener('click', function (e) {
                if (!userProfile.contains(e.target)) {
                    userDropdown.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>
