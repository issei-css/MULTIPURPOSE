<?php
// Process form submission
$result = '';
$principal = '';
$rate = '';
$time = '';
$compounding = '';
$depositDate = '';
$maturityDate = '';
$maturityAmount = '';
$interestEarned = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $principal = isset($_POST['principal']) ? (float)$_POST['principal'] : 0;
    $rate = isset($_POST['rate']) ? (float)$_POST['rate'] : 0;
    $time = isset($_POST['time']) ? (float)$_POST['time'] : 0;
    $compounding = isset($_POST['compounding']) ? $_POST['compounding'] : 'annually';
    
    // Validate input
    if ($principal <= 0 || $rate <= 0 || $time <= 0) {
        $result = '<div class="alert alert-danger">Please enter valid positive numbers for all fields.</div>';
    } else {
        // Calculate deposit details
        $rate = $rate / 100; // Convert percentage to decimal
        
        // Set compounding frequency
        $n = 1; // Default: annually
        if ($compounding == 'semi-annually') {
            $n = 2;
        } elseif ($compounding == 'quarterly') {
            $n = 4;
        } elseif ($compounding == 'monthly') {
            $n = 12;
        } elseif ($compounding == 'daily') {
            $n = 365;
        }
        
        // Calculate maturity amount using compound interest formula
        // A = P(1 + r/n)^(nt)
        $maturityAmount = $principal * pow((1 + ($rate / $n)), ($n * $time));
        $interestEarned = $maturityAmount - $principal;
        
        // Calculate dates
        $depositDate = date('Y-m-d');
        $maturityDate = date('Y-m-d', strtotime('+' . ($time * 365) . ' days'));
        
        // Format results for display
        $maturityAmount = number_format($maturityAmount, 2);
        $interestEarned = number_format($interestEarned, 2);
        
        $result = '<div class="alert alert-success">Calculation completed successfully!</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Time Deposit Interest Calculator</title>
    <style>
        :root {
            --primary-color: #1e88e5;
            --secondary-color: #f5f5f5;
            --accent-color: #ff9800;
            --text-color: #333;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: #f9f9f9;
            margin: 0;
            padding: 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .calculator-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .calculator-form, .calculator-results {
            flex: 1;
            min-width: 300px;
            background-color: white;
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--box-shadow);
        }
        
        .calculator-results {
            background-color: var(--secondary-color);
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 16px;
        }
        
        button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            border-radius: var(--border-radius);
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s;
            margin-top: 10px;
        }
        
        button:hover {
            background-color: #1565c0;
        }
        
        .alert {
            padding: 10px;
            border-radius: var(--border-radius);
            margin-bottom: 15px;
        }
        
        .alert-danger {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ef9a9a;
        }
        
        .alert-success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #a5d6a7;
        }
        
        .result-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding: 10px;
            background-color: white;
            border-radius: var(--border-radius);
        }
        
        .result-label {
            font-weight: 600;
        }
        
        .result-value {
            font-weight: 400;
        }
        
        .highlight {
            color: var(--accent-color);
            font-weight: 700;
            font-size: 1.2em;
        }
        
        .section-title {
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 8px;
            margin-bottom: 20px;
            color: var(--primary-color);
        }
        
        @media (max-width: 768px) {
            .calculator-container {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Time Deposit Interest Calculator</h1>
            <p>Calculate how much your savings will grow with our competitive time deposit rates</p>
        </div>
        <div class="card">
                    <a href="member_dashboard.php" class="btn btn-success">← Back to Dashboard</a>
                </div>
        <?php if (!empty($result)): ?>
            <?php echo $result; ?>
        <?php endif; ?>
        
        <div class="calculator-container">
            <div class="calculator-form">
                <h2 class="section-title">Calculate Your Returns</h2>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="form-group">
                        <label for="principal">Principal Amount ($)</label>
                        <input type="number" id="principal" name="principal" min="0" step="0.01" value="<?php echo $principal; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="rate">Interest Rate (% per year)</label>
                        <input type="number" id="rate" name="rate" min="0" step="0.01" value="<?php echo $rate; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="time">Time Period (years)</label>
                        <input type="number" id="time" name="time" min="0" step="0.1" value="<?php echo $time; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="compounding">Compounding Frequency</label>
                        <select id="compounding" name="compounding">
                            <option value="annually" <?php if ($compounding == 'annually') echo 'selected'; ?>>Annually</option>
                            <option value="semi-annually" <?php if ($compounding == 'semi-annually') echo 'selected'; ?>>Semi-Annually</option>
                            <option value="quarterly" <?php if ($compounding == 'quarterly') echo 'selected'; ?>>Quarterly</option>
                            <option value="monthly" <?php if ($compounding == 'monthly') echo 'selected'; ?>>Monthly</option>
                            <option value="daily" <?php if ($compounding == 'daily') echo 'selected'; ?>>Daily</option>
                        </select>
                    </div>
                    
                    <button type="submit">Calculate</button>
                </form>
            </div>
            
            <div class="calculator-results">
                <h2 class="section-title">Investment Summary</h2>
                
                <?php if (!empty($maturityAmount)): ?>
                    <div class="result-item">
                        <span class="result-label">Principal Amount:</span>
                        <span class="result-value">$<?php echo number_format($principal, 2); ?></span>
                    </div>
                    
                    <div class="result-item">
                        <span class="result-label">Interest Rate:</span>
                        <span class="result-value"><?php echo $rate * 100; ?>% per year</span>
                    </div>
                    
                    <div class="result-item">
                        <span class="result-label">Time Period:</span>
                        <span class="result-value"><?php echo $time; ?> years</span>
                    </div>
                    
                    <div class="result-item">
                        <span class="result-label">Compounding:</span>
                        <span class="result-value"><?php echo ucfirst($compounding); ?></span>
                    </div>
                    
                    <div class="result-item">
                        <span class="result-label">Deposit Date:</span>
                        <span class="result-value"><?php echo $depositDate; ?></span>
                    </div>
                    
                    <div class="result-item">
                        <span class="result-label">Maturity Date:</span>
                        <span class="result-value"><?php echo $maturityDate; ?></span>
                    </div>
                    
                    <div class="result-item">
                        <span class="result-label">Interest Earned:</span>
                        <span class="result-value highlight">$<?php echo $interestEarned; ?></span>
                    </div>
                    
                    <div class="result-item">
                        <span class="result-label">Maturity Amount:</span>
                        <span class="result-value highlight">$<?php echo $maturityAmount; ?></span>
                    </div>
                <?php else: ?>
                    <p>Enter your deposit details to see your projected returns.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>