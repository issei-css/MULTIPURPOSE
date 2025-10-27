<?php
/**
 * Fixed Account System
 * 
 * This system handles fixed deposit accounts with PHP backend functionality
 * and HTML/CSS user interface.
 */

// Database connection configuration
$db_config = [
    'host' => 'sql304.infinityfree.com',
    'username' => 'if0_38740142',
    'password' => 'sipagan1weaz',
    'database' => 'if0_38740142_sipagan_project_multipurpose'
];

/**
 * Connect to the database
 *
 * @return PDO|null Database connection or null on failure
 */
function connectDB() {
    global $db_config;
    
    try {
        $dsn = "mysql:host={$db_config['host']};dbname={$db_config['database']}";
        $pdo = new PDO($dsn, $db_config['username'], $db_config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        return null;
    }
}

/**
 * Get available fixed deposit interest rates
 * 
 * @return array List of interest rates by term
 */
function getFixedInterestRates() {
    $pdo = connectDB();
    
    if (!$pdo) {
        return ['success' => false, 'message' => 'Database connection failed'];
    }
    
    try {
        $stmt = $pdo->query("SELECT * FROM fixed_interest_rates ORDER BY term_months");
        $rates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $rateList = [];
        foreach ($rates as $rate) {
            $rateList[$rate['term_months']] = $rate['interest_rate'];
        }
        
        return ['success' => true, 'rates' => $rateList];
    } catch (PDOException $e) {
        error_log("Error fetching interest rates: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to fetch interest rates'];
    }
}

/**
 * Create a new fixed deposit account
 *
 * @param int $userId User ID
 * @param float $amount Deposit amount
 * @param int $termMonths Term in months
 * @return array Result of operation
 */
function createFixedAccount($userId, $amount, $termMonths) {
    $pdo = connectDB();
    
    if (!$pdo) {
        return ['success' => false, 'message' => 'Database connection failed'];
    }
    
    try {
        // Validate inputs
        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Amount must be greater than zero'];
        }
        
        if ($termMonths < 1) {
            return ['success' => false, 'message' => 'Term must be at least 1 month'];
        }
        
        // Get the interest rate for this term
        $rates = getFixedInterestRates();
        if (!$rates['success']) {
            return ['success' => false, 'message' => 'Failed to get interest rates'];
        }
        
        $interestRate = isset($rates['rates'][$termMonths]) ? $rates['rates'][$termMonths] : 5.0;
        
        // Calculate maturity date
        $startDate = date('Y-m-d');
        
        // Insert the fixed deposit account
        $stmt = $pdo->prepare("
            INSERT INTO fixed_deposits 
                (user_id, amount, term_months, start_date, status)
            VALUES 
                (:user_id, :amount, :term, :start_date, 'active')
        ");
        
        $stmt->execute([
            ':user_id' => $userId,
            ':amount' => $amount,
            ':term' => $termMonths,
            ':start_date' => $startDate
        ]);
        
        $accountId = $pdo->lastInsertId();
        
        // Calculate maturity amount for display
        $maturityAmount = calculateMaturityAmount($amount, $interestRate, $termMonths);
        $maturityDate = date('Y-m-d', strtotime("+$termMonths months"));
        
        return [
            'success' => true, 
            'message' => 'Fixed deposit account created successfully',
            'account_id' => $accountId,
            'maturity_date' => $maturityDate,
            'maturity_amount' => $maturityAmount
        ];
    } catch (PDOException $e) {
        error_log("Error creating fixed account: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to create account'];
    }
}

/**
 * Calculate the maturity amount for a fixed deposit
 *
 * @param float $principal Principal amount
 * @param float $rate Annual interest rate (as a percentage)
 * @param int $termMonths Term in months
 * @return float Maturity amount
 */
function calculateMaturityAmount($principal, $rate, $termMonths) {
    // Convert rate to decimal and term to years
    $rateDecimal = $rate / 100;
    $termYears = $termMonths / 12;
    
    // Calculate using compound interest formula
    $maturityAmount = $principal * pow((1 + $rateDecimal), $termYears);
    
    return round($maturityAmount, 2);
}

/**
 * Get fixed accounts for a user
 *
 * @param int $userId User ID
 * @return array User's fixed accounts
 */
function getUserFixedAccounts($userId) {
    $pdo = connectDB();
    
    if (!$pdo) {
        return ['success' => false, 'message' => 'Database connection failed'];
    }
    
    try {
        // First get all the accounts
        $stmt = $pdo->prepare("
            SELECT * FROM fixed_deposits 
            WHERE user_id = :user_id
            ORDER BY start_date DESC
        ");
        
        $stmt->execute([':user_id' => $userId]);
        $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get interest rates to include in the account data
        $rates = getFixedInterestRates();
        if (!$rates['success']) {
            $rateList = [];
        } else {
            $rateList = $rates['rates'];
        }
        
        // Enhance each account with calculated fields
        foreach ($accounts as &$account) {
            $termMonths = $account['term_months'];
            $account['interest_rate'] = isset($rateList[$termMonths]) ? $rateList[$termMonths] : 5.0;
            $account['maturity_date'] = date('Y-m-d', strtotime($account['start_date'] . " + $termMonths months"));
            $account['maturity_amount'] = calculateMaturityAmount($account['amount'], $account['interest_rate'], $termMonths);
            $account['deposit_amount'] = $account['amount']; // For compatibility with the view
        }
        
        return ['success' => true, 'accounts' => $accounts];
    } catch (PDOException $e) {
        error_log("Error fetching fixed accounts: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to fetch accounts'];
    }
}

/**
 * Break a fixed deposit before maturity
 *
 * @param int $accountId Account ID
 * @param int $userId User ID (for security verification)
 * @return array Result of operation
 */
function breakFixedDeposit($accountId, $userId) {
    $pdo = connectDB();
    
    if (!$pdo) {
        return ['success' => false, 'message' => 'Database connection failed'];
    }
    
    try {
        // First verify ownership
        $stmt = $pdo->prepare("
            SELECT * FROM fixed_deposits 
            WHERE id = :account_id AND user_id = :user_id AND status = 'active'
        ");
        
        $stmt->execute([
            ':account_id' => $accountId,
            ':user_id' => $userId
        ]);
        
        $account = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$account) {
            return ['success' => false, 'message' => 'Account not found or not active'];
        }
        
        // Calculate penalty (usually a reduced interest rate)
        $penaltyAmount = calculatePenalty($account);
        $withdrawalAmount = $account['amount'] + $penaltyAmount;
        
        // Update the account status
        $stmt = $pdo->prepare("
            UPDATE fixed_deposits 
            SET status = 'broken'
            WHERE id = :account_id
        ");
        
        $stmt->execute([
            ':account_id' => $accountId
        ]);
        
        return [
            'success' => true,
            'message' => 'Fixed deposit broken successfully',
            'withdrawal_amount' => $withdrawalAmount
        ];
    } catch (PDOException $e) {
        error_log("Error breaking fixed deposit: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to break fixed deposit'];
    }
}

/**
 * Calculate penalty for breaking a fixed deposit early
 *
 * @param array $account Account details
 * @return float Interest amount after penalty
 */
function calculatePenalty($account) {
    // Get the period the deposit has been held
    $startDate = new DateTime($account['start_date']);
    $currentDate = new DateTime(date('Y-m-d'));
    $monthsHeld = $startDate->diff($currentDate)->m + ($startDate->diff($currentDate)->y * 12);
    
    // If held for less than minimum term, apply a reduced rate
    $reducedRate = $account['interest_rate'] / 2; // Example: Half the original rate
    
    // Calculate interest earned so far
    $interestEarned = $account['amount'] * ($reducedRate / 100) * ($monthsHeld / 12);
    
    return round($interestEarned, 2);
}

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    
    // Check if user is logged in (In a real app, you would have proper authentication)
    $userId = $_SESSION['user_id'] ?? 1; // Default to user ID 1 for demo purposes
    
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'create_fixed_account') {
            $amount = floatval($_POST['amount']);
            $termMonths = intval($_POST['term']);
            
            $result = createFixedAccount($userId, $amount, $termMonths);
            
            // Store result for display
            $_SESSION['result'] = $result;
            
            // Redirect to prevent form resubmission
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } elseif ($action === 'break_deposit') {
            $accountId = intval($_POST['account_id']);
            
            $result = breakFixedDeposit($accountId, $userId);
            
            // Store result for display
            $_SESSION['result'] = $result;
            
            // Redirect to prevent form resubmission
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }
    }
}

// Start or resume session
session_start();

// For demo purposes, set a user ID
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
}

// Get user's fixed accounts
$userId = $_SESSION['user_id'];
$accountsData = getUserFixedAccounts($userId);

// Get result message if exists
$result = $_SESSION['result'] ?? null;
unset($_SESSION['result']); // Clear the result

// Get interest rates for the form
$interestRates = getFixedInterestRates();
$rates = $interestRates['success'] ? $interestRates['rates'] : [];

// Include the HTML template
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fixed Deposit Account System</title>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --text-color: #333;
            --light-bg: #f9f9f9;
            --border-color: #ddd;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--light-bg);
            color: var(--text-color);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background-color: var(--primary-color);
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        
        header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-info .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--secondary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-weight: bold;
        }
        
        .dashboard {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
        }
        
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .card-header {
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 15px;
            margin-bottom: 20px;
            font-weight: bold;
            font-size: 18px;
            color: var(--primary-color);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        input[type="text"],
        input[type="number"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 16px;
        }
        
        button {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        
        button:hover {
            background-color: #2980b9;
        }
        
        .account-summary {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .summary-box {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            flex: 1;
            margin-right: 15px;
            text-align: center;
        }
        
        .summary-box:last-child {
            margin-right: 0;
        }
        
        .summary-value {
            font-size: 24px;
            font-weight: bold;
            color: var(--secondary-color);
            margin: 10px 0;
        }
        
        .summary-label {
            color: #777;
            font-size: 14px;
        }
        
        .accounts-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .accounts-table th,
        .accounts-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        
        .accounts-table th {
            background-color: #f2f2f2;
            font-weight: 500;
        }
        
        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .status.active {
            background-color: rgba(46, 204, 113, 0.2);
            color: var(--success-color);
        }
        
        .status.broken {
            background-color: rgba(231, 76, 60, 0.2);
            color: var(--accent-color);
        }
        
        .status.matured {
            background-color: rgba(243, 156, 18, 0.2);
            color: var(--warning-color);
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert.success {
            background-color: rgba(46, 204, 113, 0.2);
            color: var(--success-color);
            border: 1px solid rgba(46, 204, 113, 0.3);
        }
        
        .alert.error {
            background-color: rgba(231, 76, 60, 0.2);
            color: var(--accent-color);
            border: 1px solid rgba(231, 76, 60, 0.3);
        }
        
        .action-btn {
            padding: 6px 12px;
            font-size: 14px;
        }
        
        .action-btn.danger {
            background-color: var(--accent-color);
        }
        
        .action-btn.danger:hover {
            background-color: #c0392b;
        }
        
        .interest-rate-info {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        
        .back-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .back-btn:hover {
            background-color: #45a049;
        }
        
        @media (max-width: 768px) {
            .dashboard {
                grid-template-columns: 1fr;
            }
            
            .account-summary {
                flex-direction: column;
            }
            
            .summary-box {
                margin-right: 0;
                margin-bottom: 15px;
            }
            
            .accounts-table {
                font-size: 14px;
            }
            
            header .container {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .header-actions {
                margin-top: 15px;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div style="display: flex; justify-content: space-between; width: 100%; align-items: center;">
                <div class="logo">BankPro Fixed Deposits</div>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <a href="member_dashboard.php" class="back-btn">Back to Dashboard</a>
                    <div class="user-info">
                        <div class="avatar">U</div>
                        <div>
                            <div>User #<?php echo $userId; ?></div>
                            <div style="font-size: 14px; opacity: 0.8;">Demo Account</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <div class="container">
        <?php if ($result): ?>
            <div class="alert <?php echo $result['success'] ? 'success' : 'error'; ?>">
                <?php echo $result['message']; ?>
                <?php if ($result['success'] && isset($result['maturity_amount'])): ?>
                    <div>
                        <strong>Maturity Amount:</strong> $<?php echo number_format($result['maturity_amount'], 2); ?> 
                        <strong>Maturity Date:</strong> <?php echo $result['maturity_date']; ?>
                    </div>
                <?php endif; ?>
                <?php if ($result['success'] && isset($result['withdrawal_amount'])): ?>
                    <div>
                        <strong>Withdrawal Amount:</strong> $<?php echo number_format($result['withdrawal_amount'], 2); ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="dashboard">
            <div class="left-panel">
                <div class="card">
                    <div class="card-header">Create Fixed Deposit</div>
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                        <input type="hidden" name="action" value="create_fixed_account">
                        
                        <div class="form-group">
                            <label for="amount">Deposit Amount ($)</label>
                            <input type="number" id="amount" name="amount" step="0.01" min="100" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="term">Term (Months)</label>
                            <select id="term" name="term" required onchange="updateInterestRate()">
                                <option value="">Select term</option>
                                <?php foreach ($rates as $term => $rate): ?>
                                    <option value="<?php echo $term; ?>"><?php echo $term; ?> months (<?php echo $rate; ?>%)</option>
                                <?php endforeach; ?>
                            </select>
                            <div id="selected-rate" class="interest-rate-info">
                                Select a term to see interest rate
                            </div>
                        </div>
                        
                        <button type="submit">Create Fixed Deposit</button>
                    </form>
                </div>
                
                <div class="card">
                    <div class="card-header">Interest Calculator</div>
                    <div class="form-group">
                        <label for="calc_amount">Amount ($)</label>
                        <input type="number" id="calc_amount" step="0.01" min="100">
                    </div>
                    
                    <div class="form-group">
                        <label for="calc_term">Term (Months)</label>
                        <select id="calc_term" onchange="updateCalculatorRate()">
                            <option value="">Select term</option>
                            <?php foreach ($rates as $term => $rate): ?>
                                <option value="<?php echo $term; ?>"><?php echo $term; ?> months</option>
                            <?php endforeach; ?>
                        </select>
                        <div id="calculator-rate" class="interest-rate-info">
                            Select a term to see interest rate
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="calc_rate">Interest Rate (%)</label>
                        <input type="number" id="calc_rate" step="0.01" min="1" max="10" value="5.00" readonly>
                    </div>
                    
                    <button type="button" onclick="calculateInterest()">Calculate</button>
                    
                    <div id="calculator-result" style="margin-top: 15px; display: none;">
                        <div style="font-weight: bold; margin-bottom: 5px;">Maturity Amount</div>
                        <div id="maturity-amount" style="font-size: 24px; color: var(--secondary-color);">$0.00</div>
                        <div style="font-size: 14px; color: #777; margin-top: 5px;">
                            Interest Earned: <span id="interest-earned">$0.00</span>
                        </div>
                    </div>
                </div>

                <div class="card" style="text-align: center;">
                    <a href="member_dashboard.php" class="back-btn" style="display: block;">Back to Dashboard</a>
                </div>
            </div>
            
            <div class="right-panel">
                <div class="account-summary">
                    <div class="summary-box">
                        <div class="summary-label">Total Deposits</div>
                        <div class="summary-value">
                            <?php
                                $totalDeposits = 0;
                                $activeDeposits = 0;
                                $totalAccounts = 0;
                                
                                if ($accountsData['success'] && isset($accountsData['accounts'])) {
                                    $totalAccounts = count($accountsData['accounts']);
                                    
                                    foreach ($accountsData['accounts'] as $account) {
                                        $totalDeposits += $account['amount'];
                                        if ($account['status'] === 'active') {
                                            $activeDeposits++;
                                        }
                                    }
                                }
                                
                                echo '$' . number_format($totalDeposits, 2);
                            ?>
                        </div>
                    </div>
                    
                    <div class="summary-box">
                        <div class="summary-label">Active Deposits</div>
                        <div class="summary-value"><?php echo $activeDeposits; ?></div>
                    </div>
                    
                    <div class="summary-box">
                        <div class="summary-label">Total Accounts</div>
                        <div class="summary-value"><?php echo $totalAccounts; ?></div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">Your Fixed Deposits</div>
                    
                    <?php if ($accountsData['success'] && !empty($accountsData['accounts'])): ?>
                        <table class="accounts-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Amount</th>
                                    <th>Term</th>
                                    <th>Interest</th>
                                    <th>Start Date</th>
                                    <th>Maturity Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($accountsData['accounts'] as $account): ?>
                                    <tr>
                                        <td><?php echo $account['id']; ?></td>
                                        <td>$<?php echo number_format($account['amount'], 2); ?></td>
                                        <td><?php echo $account['term_months']; ?> months</td>
                                        <td><?php echo $account['interest_rate']; ?>%</td>
                                        <td><?php echo $account['start_date']; ?></td>
                                        <td><?php echo $account['maturity_date']; ?></td>
                                        <td>
                                            <span class="status <?php echo $account['status']; ?>">
                                                <?php echo ucfirst($account['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($account['status'] === 'active'): ?>
                                                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" onsubmit="return confirm('Are you sure you want to break this deposit? You may incur penalties.');">
                                                    <input type="hidden" name="action" value="break_deposit">
                                                    <input type="hidden" name="account_id" value="<?php echo $account['id']; ?>">
                                                    <button type="submit" class="action-btn danger">Break</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No fixed deposit accounts found. Create your first fixed deposit to get started!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Interest rates from PHP
        const interestRates = <?php echo json_encode($rates); ?>;
        
        function updateInterestRate() {
            const termSelect = document.getElementById('term');
            const selectedTerm = termSelect.value;
            const rateInfo = document.getElementById('selected-rate');
            
            if (selectedTerm && interestRates[selectedTerm]) {
                rateInfo.textContent = `Interest rate: ${interestRates[selectedTerm]}%`;
            } else {
                rateInfo.textContent = 'Select a term to see interest rate';
            }
        }
        
        function updateCalculatorRate() {
            const termSelect = document.getElementById('calc_term');
            const selectedTerm = termSelect.value;
            const rateInput = document.getElementById('calc_rate');
            const rateInfo = document.getElementById('calculator-rate');
            
            if (selectedTerm && interestRates[selectedTerm]) {
                rateInput.value = interestRates[selectedTerm];
                rateInfo.textContent = `Interest rate: ${interestRates[selectedTerm]}%`;
            } else {
                rateInput.value = '5.00';
                rateInfo.textContent = 'Select a term to see interest rate';
            }
        }
        
        function calculateInterest() {
            const amount = parseFloat(document.getElementById('calc_amount').value);
            const termMonths = parseInt(document.getElementById('calc_term').value);
            const rate = parseFloat(document.getElementById('calc_rate').value);
            
            if (isNaN(amount) || isNaN(termMonths) || isNaN(rate)) {
                alert('Please enter valid numbers');
                return;
            }
            
            // Convert to years for calculation
            const termYears = termMonths / 12;
            
            // Calculate maturity amount using compound interest
            const maturityAmount = amount * Math.pow((1 + (rate / 100)), termYears);
            const interestEarned = maturityAmount - amount;
            
            document.getElementById('maturity-amount').textContent = '$' + maturityAmount.toFixed(2);
            document.getElementById('interest-earned').textContent = '$' + interestEarned.toFixed(2);
            document.getElementById('calculator-result').style.display = 'block';
        }
    </script>
</body>
</html>