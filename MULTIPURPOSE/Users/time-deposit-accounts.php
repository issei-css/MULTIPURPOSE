<?php
// Start the session to maintain user state
session_start();

// Database simulation using session variables
if (!isset($_SESSION['accounts'])) {
    $_SESSION['accounts'] = [];
    $_SESSION['users'] = [
        'admin' => [
            'password' => 'admin123',
            'name' => 'Administrator'
        ],
        'user1' => [
            'password' => 'pass123',
            'name' => 'John Doe'
        ]
    ];
    $_SESSION['current_user'] = null;
    $_SESSION['next_account_id'] = 1000;
}

// Message handling
$message = '';
$messageType = '';

// Login processing
if (isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (isset($_SESSION['users'][$username]) && $_SESSION['users'][$username]['password'] === $password) {
        $_SESSION['current_user'] = $username;
        $message = "Welcome, " . $_SESSION['users'][$username]['name'] . "!";
        $messageType = "success";
    } else {
        $message = "Invalid username or password!";
        $messageType = "error";
    }
}

// Logout processing
if (isset($_GET['logout'])) {
    $_SESSION['current_user'] = null;
    $message = "You have been logged out successfully.";
    $messageType = "success";
}

// Create new time deposit account
if (isset($_POST['create_account'])) {
    if ($_SESSION['current_user']) {
        $amount = floatval($_POST['initial_deposit'] ?? 0);
        $term = intval($_POST['term'] ?? 0);
        $interestRate = getInterestRate($term);
        
        if ($amount >= 1000 && $term > 0) {
            $maturityDate = date('Y-m-d', strtotime("+$term months"));
            $accountId = $_SESSION['next_account_id']++;
            
            $_SESSION['accounts'][$accountId] = [
                'owner' => $_SESSION['current_user'],
                'amount' => $amount,
                'term' => $term,
                'interest_rate' => $interestRate,
                'created_date' => date('Y-m-d'),
                'maturity_date' => $maturityDate,
                'status' => 'active'
            ];
            
            $message = "Time deposit account created successfully with ID: $accountId";
            $messageType = "success";
        } else {
            $message = "Error: Minimum deposit is $1,000 and term must be selected.";
            $messageType = "error";
        }
    } else {
        $message = "Please login to create an account.";
        $messageType = "error";
    }
}

// Process withdrawal request
if (isset($_POST['withdraw']) && isset($_POST['account_id'])) {
    $accountId = $_POST['account_id'];
    
    if (isset($_SESSION['accounts'][$accountId]) && $_SESSION['accounts'][$accountId]['owner'] === $_SESSION['current_user']) {
        $account = $_SESSION['accounts'][$accountId];
        $today = new DateTime();
        $maturityDate = new DateTime($account['maturity_date']);
        
        if ($today >= $maturityDate) {
            // Calculate interest
            $interestAmount = $account['amount'] * ($account['interest_rate'] / 100) * ($account['term'] / 12);
            $totalAmount = $account['amount'] + $interestAmount;
            
            $_SESSION['accounts'][$accountId]['status'] = 'closed';
            $message = "Withdrawal successful. You received $" . number_format($totalAmount, 2) . " (Principal: $" . 
                      number_format($account['amount'], 2) . ", Interest: $" . number_format($interestAmount, 2) . ")";
            $messageType = "success";
        } else {
            // Early withdrawal penalty
            $penalty = $account['amount'] * 0.05; // 5% penalty
            $remainingAmount = $account['amount'] - $penalty;
            
            $_SESSION['accounts'][$accountId]['status'] = 'closed';
            $message = "Early withdrawal processed with penalty. You received $" . number_format($remainingAmount, 2) . 
                      " (Original: $" . number_format($account['amount'], 2) . ", Penalty: $" . number_format($penalty, 2) . ")";
            $messageType = "warning";
        }
    } else {
        $message = "Invalid account or unauthorized access.";
        $messageType = "error";
    }
}

// Helper function to determine interest rate based on term
function getInterestRate($term) {
    if ($term <= 3) return 2.0;
    if ($term <= 6) return 2.5;
    if ($term <= 12) return 3.0;
    if ($term <= 24) return 3.5;
    if ($term <= 36) return 4.0;
    return 4.5; // for terms > 36 months
}

// Function to check if account has matured
function hasMatured($maturityDate) {
    $today = new DateTime();
    $maturity = new DateTime($maturityDate);
    return $today >= $maturity;
}

// Function to calculate current value of a time deposit
function calculateCurrentValue($account) {
    $principal = $account['amount'];
    $interestRate = $account['interest_rate'];
    $term = $account['term'];
    
    // Simple interest calculation
    $interest = $principal * ($interestRate / 100) * ($term / 12);
    return $principal + $interest;
}

// Calculate days remaining until maturity
function daysToMaturity($maturityDate) {
    $today = new DateTime();
    $maturity = new DateTime($maturityDate);
    
    if ($today > $maturity) {
        return 0;
    }
    
    $diff = $today->diff($maturity);
    return $diff->days;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Time Deposit Account System</title>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #34495e;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background-color: var(--primary-color);
            color: white;
            padding: 1rem;
            margin-bottom: 2rem;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        header h1 {
            font-size: 1.8rem;
        }
        
        nav ul {
            display: flex;
            list-style: none;
        }
        
        nav ul li {
            margin-left: 15px;
        }
        
        nav ul li a {
            color: white;
            text-decoration: none;
        }
        
        nav ul li a:hover {
            text-decoration: underline;
        }
        
        main {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .card {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
            flex: 1;
            min-width: 300px;
        }
        
        .card h2 {
            color: var(--primary-color);
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        input[type="text"],
        input[type="password"],
        input[type="number"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        
        button, .btn {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }
        
        button:hover, .btn:hover {
            background-color: var(--primary-color);
        }
        
        .btn-success {
            background-color: var(--success-color);
        }
        
        .btn-warning {
            background-color: var(--warning-color);
        }
        
        .btn-danger {
            background-color: var(--danger-color);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        table th, table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        table th {
            background-color: var(--light-color);
        }
        
        .alert {
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-info span {
            margin-right: 15px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .status-active {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-matured {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .status-closed {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        footer {
            text-align: center;
            margin-top: 2rem;
            padding: 1rem;
            background-color: var(--primary-color);
            color: white;
            border-radius: 5px;
        }
        
        @media (max-width: 768px) {
            main {
                flex-direction: column;
            }
            
            .card {
                min-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Time Deposit Account System</h1>
            <nav>
                <ul>
                    <?php if ($_SESSION['current_user']): ?>
                        <li class="user-info">
                            <span>Welcome, <?php echo $_SESSION['users'][$_SESSION['current_user']]['name']; ?></span>
                            <a href="?logout=1" class="btn btn-danger">Logout</a>
                        </li>
                    <?php else: ?>
                        <li><a href="#">Home</a></li>
                        <li><a href="#">About</a></li>
                        <li><a href="#">Contact</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </header>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <main>
            <?php if (!$_SESSION['current_user']): ?>
                <!-- Login Form -->
                <div class="card">
                    <h2>Login to Your Account</h2>
                    <form method="post" action="">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        <button type="submit" name="login">Login</button>
                    </form>
                </div>

                <!-- Information Card -->
                <div class="card">
                    <h2>About Time Deposits</h2>
                    <p>A time deposit (also known as a term deposit or certificate of deposit) is a financial instrument provided by banks which provides higher interest rates than a regular savings account.</p>
                    <p>Key features:</p>
                    <ul>
                        <li>Fixed term (duration)</li>
                        <li>Higher interest rates than regular savings</li>
                        <li>Early withdrawal may incur penalties</li>
                    </ul>
                    <h3>Our Interest Rates</h3>
                    <table>
                        <tr>
                            <th>Term</th>
                            <th>Interest Rate (p.a.)</th>
                        </tr>
                        <tr>
                            <td>1-3 months</td>
                            <td>2.0%</td>
                        </tr>
                        <tr>
                            <td>4-6 months</td>
                            <td>2.5%</td>
                        </tr>
                        <tr>
                            <td>7-12 months</td>
                            <td>3.0%</td>
                        </tr>
                        <tr>
                            <td>13-24 months</td>
                            <td>3.5%</td>
                        </tr>
                        <tr>
                            <td>25-36 months</td>
                            <td>4.0%</td>
                        </tr>
                        <tr>
                            <td>Over 36 months</td>
                            <td>4.5%</td>
                        </tr>
                    </table>
                </div>
            <?php else: ?>
                <!-- Create New Time Deposit Account -->
                
                <div class="card">
                    <a href="member_dashboard.php" class="btn btn-success">← Back to Dashboard</a>
                </div>

                <div class="card">
                    <div class="card">
                    <a href="member_dashboard.php" class="btn btn-success">← Back to Dashboard</a>
                </div>
                    <h2>Open a New Time Deposit</h2>
                    <form method="post" action="">
                        <div class="form-group">
                            <label for="initial_deposit">Initial Deposit Amount ($)</label>
                            <input type="number" id="initial_deposit" name="initial_deposit" min="1000" step="0.01" required>
                            <small>Minimum deposit: $1,000</small>
                        </div>
                        <div class="form-group">
                            <label for="term">Term (Months)</label>
                            <select id="term" name="term" required>
                                <option value="">Select Term</option>
                                <option value="3">3 months (2.0%)</option>
                                <option value="6">6 months (2.5%)</option>
                                <option value="12">12 months (3.0%)</option>
                                <option value="24">24 months (3.5%)</option>
                                <option value="36">36 months (4.0%)</option>
                                <option value="48">48 months (4.5%)</option>
                            </select>
                        </div>
                        <button type="submit" name="create_account" class="btn-success">Create Account</button>
                    </form>
                </div>
                
                <!-- User's Time Deposit Accounts -->
                <div class="card">
                    <h2>Your Time Deposit Accounts</h2>
                    <?php
                    $userAccounts = [];
                    foreach ($_SESSION['accounts'] as $id => $account) {
                        if ($account['owner'] === $_SESSION['current_user']) {
                            $userAccounts[$id] = $account;
                        }
                    }
                    
                    if (empty($userAccounts)): ?>
                        <p>You don't have any time deposit accounts yet.</p>
                    <?php else: ?>
                        <table>
                            <tr>
                                <th>Account ID</th>
                                <th>Principal</th>
                                <th>Term</th>
                                <th>Interest Rate</th>
                                <th>Maturity Date</th>
                                <th>Current Value</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                            <?php foreach ($userAccounts as $id => $account): 
                                $isMatured = hasMatured($account['maturity_date']);
                                $currentValue = calculateCurrentValue($account);
                                $daysRemaining = daysToMaturity($account['maturity_date']);
                                
                                $statusClass = 'status-active';
                                $status = 'Active';
                                
                                if ($account['status'] === 'closed') {
                                    $statusClass = 'status-closed';
                                    $status = 'Closed';
                                } elseif ($isMatured) {
                                    $statusClass = 'status-matured';
                                    $status = 'Matured';
                                }
                            ?>
                                <tr>
                                    <td><?php echo $id; ?></td>
                                    <td>$<?php echo number_format($account['amount'], 2); ?></td>
                                    <td><?php echo $account['term']; ?> months</td>
                                    <td><?php echo $account['interest_rate']; ?>%</td>
                                    <td><?php echo $account['maturity_date']; ?></td>
                                    <td>$<?php echo number_format($currentValue, 2); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $statusClass; ?>">
                                            <?php echo $status; ?>
                                            <?php if ($account['status'] === 'active' && !$isMatured): ?>
                                                (<?php echo $daysRemaining; ?> days left)
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($account['status'] !== 'closed'): ?>
                                            <form method="post" action="" style="display: inline;">
                                                <input type="hidden" name="account_id" value="<?php echo $id; ?>">
                                                <button type="submit" name="withdraw" class="btn-warning" 
                                                    <?php echo ($isMatured) ? '' : 'onclick="return confirm(\'Early withdrawal will incur a 5% penalty. Continue?\');"'; ?>>
                                                    Withdraw
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span>N/A</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> Time Deposit Account System | All Rights Reserved</p>
        </footer>
    </div>
</body>
</html>