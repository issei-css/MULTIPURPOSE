<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
}
$userId = $_SESSION['user_id'];

function connectDB() {
    $host = 'sql304.infinityfree.com';
    $db = 'if0_38740142_sipagan_project_multipurpose';
    $user = 'if0_38740142';
    $pass = 'sipagan1weaz';
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("DB Error: " . $e->getMessage());
    }
}

function withdrawFromFixedDeposit($userId, $accountId, $amount) {
    $pdo = connectDB();
    try {
        $stmt = $pdo->prepare("SELECT *, (amount - IFNULL(withdrawn_amount, 0)) AS available FROM fixed_deposits WHERE id = :id AND user_id = :user_id AND status = 'active'");
        $stmt->execute([':id' => $accountId, ':user_id' => $userId]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$account) {
            return ['success' => false, 'message' => 'Account not found or not active'];
        }

        if ($amount <= 0 || $amount > $account['available']) {
            return ['success' => false, 'message' => "Invalid amount. Max available: ₱" . number_format($account['available'], 2)];
        }

        if ($amount == $account['available']) {
            $stmt = $pdo->prepare("UPDATE fixed_deposits SET status = 'broken', withdrawn_amount = amount WHERE id = :id");
            $stmt->execute([':id' => $accountId]);
        } else {
            $stmt = $pdo->prepare("UPDATE fixed_deposits SET withdrawn_amount = IFNULL(withdrawn_amount, 0) + :amount WHERE id = :id");
            $stmt->execute([':amount' => $amount, ':id' => $accountId]);
        }

        return ['success' => true, 'message' => "Withdrawal successful: ₱" . number_format($amount, 2)];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accountId = intval($_POST['account_id']);
    $amount = floatval($_POST['amount']);
    $result = withdrawFromFixedDeposit($userId, $accountId, $amount);
    $_SESSION['withdraw_result'] = $result;
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$pdo = connectDB();
$stmt = $pdo->prepare("SELECT *, (amount - IFNULL(withdrawn_amount, 0)) AS available FROM fixed_deposits WHERE user_id = :user_id AND status = 'active'");
$stmt->execute([':user_id' => $userId]);
$accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$result = $_SESSION['withdraw_result'] ?? null;
unset($_SESSION['withdraw_result']);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Fixed Deposit Withdrawal</title>
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--light-bg);
            color: var(--text-color);
            margin: 0;
            padding: 40px;
        }

        .container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            margin-bottom: 20px;
            color: var(--primary-color);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        select, input[type="number"] {
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
            padding: 12px;
            font-size: 16px;
            border-radius: 4px;
            width: 100%;
            cursor: pointer;
        }

        button:hover {
            background-color: #2980b9;
        }

        .back-btn {
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 20px;
        }

        .back-btn:hover {
            background-color: #45a049;
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
    </style>
</head>
<body>
    <div class="container">
        <h2>Withdraw From Fixed Deposit</h2>

        <a href="member_dashboard.php" class="back-btn">← Back to Dashboard</a>

        <?php if ($result): ?>
            <div class="alert <?= $result['success'] ? 'success' : 'error' ?>">
                <?= htmlspecialchars($result['message']) ?>
            </div>
        <?php endif; ?>

        <?php if (count($accounts) === 0): ?>
            <p>No active fixed deposits available for withdrawal.</p>
        <?php else: ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="account_id">Select Account</label>
                    <select name="account_id" id="account_id" required>
                        <option value="">-- Choose Account --</option>
                        <?php foreach ($accounts as $acc): ?>
                            <option value="<?= $acc['id'] ?>">
                                ID #<?= $acc['id'] ?> — ₱<?= number_format($acc['available'], 2) ?> available
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="amount">Withdrawal Amount (₱)</label>
                    <input type="number" name="amount" id="amount" min="0.01" step="0.01" required>
                </div>

                <button type="submit">Withdraw</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
