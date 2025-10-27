<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Savings Account Calculator</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8fafb;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        h1 {
            color: #1e6091;
            text-align: center;
            margin-bottom: 30px;
            font-weight: 600;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .input-tip {
            font-size: 12px;
            color: #6c757d;
            margin-top: 4px;
        }
        
        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        
        input:focus, select:focus {
            border-color: #4dabf7;
            outline: none;
            box-shadow: 0 0 0 3px rgba(77, 171, 247, 0.2);
        }
        
        .input-group {
            display: flex;
            gap: 10px;
        }
        
        .input-group input, .input-group select {
            flex: 1;
        }
        
        button {
            background-color: #1e88e5;
            color: white;
            border: none;
            padding: 14px 20px;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s;
            font-weight: 600;
        }
        
        button:hover {
            background-color: #1565c0;
        }
        
        .results {
            margin-top: 30px;
            padding: 20px;
            background-color: #f1f8ff;
            border-radius: 8px;
            border-left: 5px solid #1e88e5;
        }
        
        .results h2 {
            color: #1e6091;
            margin-top: 0;
            margin-bottom: 20px;
        }
        
        .result-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px dashed #c8d6e5;
        }
        
        .result-item:last-child {
            border-bottom: none;
        }
        
        .result-label {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .result-value {
            font-weight: 600;
            color: #1e6091;
        }
        
        .highlight {
            color: #2e7d32;
            font-weight: bold;
        }
        
        .growth-chart {
            margin-top: 30px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
            overflow: hidden;
        }
        
        th, td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        
        th {
            background-color: #e3f2fd;
            color: #1e6091;
            font-weight: 600;
        }
        
        tr:hover {
            background-color: #f5f9ff;
        }

        tr:nth-child(even) {
            background-color: #f8fafb;
        }
        
        .hidden {
            display: none;
        }
        
        .tabs {
            display: flex;
            margin-bottom: 20px;
        }
        
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            background-color: #e9ecef;
            border: none;
            flex: 1;
            text-align: center;
            transition: background-color 0.3s;
            font-weight: 600;
        }
        
        .tab:first-child {
            border-top-left-radius: 6px;
            border-bottom-left-radius: 6px;
        }
        
        .tab:last-child {
            border-top-right-radius: 6px;
            border-bottom-right-radius: 6px;
        }
        
        .tab.active {
            background-color: #1e88e5;
            color: white;
        }
        
        .goal-progress {
            margin-top: 20px;
            background-color: #e9ecef;
            border-radius: 8px;
            height: 24px;
            position: relative;
            overflow: hidden;
        }
        
        .progress-bar {
            height: 100%;
            background-color: #2e7d32;
            width: 0%;
            transition: width 1s ease-in-out;
        }
        
        .progress-text {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            text-shadow: 0 0 2px rgba(0, 0, 0, 0.5);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Savings Account Calculator</h1>
        
        <form id="savingsForm" method="post">
            <div class="form-group">
                <label for="initial">Initial Deposit ($)</label>
                <input type="number" id="initial" name="initial" min="0" step="any" required value="<?php echo isset($_POST['initial']) ? htmlspecialchars($_POST['initial']) : '1000'; ?>">
                <div class="input-tip">The amount you're starting with</div>
            </div>
            
            <div class="form-group">
                <label for="deposit">Regular Deposit ($)</label>
                <input type="number" id="deposit" name="deposit" min="0" step="any" required value="<?php echo isset($_POST['deposit']) ? htmlspecialchars($_POST['deposit']) : '100'; ?>">
                <div class="input-tip">The amount you'll regularly add to your savings</div>
            </div>
            
            <div class="form-group">
                <label for="frequency">Deposit Frequency</label>
                <select id="frequency" name="frequency">
                    <option value="monthly" <?php echo (!isset($_POST['frequency']) || $_POST['frequency'] == 'monthly') ? 'selected' : ''; ?>>Monthly</option>
                    <option value="bi-weekly" <?php echo (isset($_POST['frequency']) && $_POST['frequency'] == 'bi-weekly') ? 'selected' : ''; ?>>Bi-Weekly</option>
                    <option value="weekly" <?php echo (isset($_POST['frequency']) && $_POST['frequency'] == 'weekly') ? 'selected' : ''; ?>>Weekly</option>
                    <option value="quarterly" <?php echo (isset($_POST['frequency']) && $_POST['frequency'] == 'quarterly') ? 'selected' : ''; ?>>Quarterly</option>
                    <option value="annually" <?php echo (isset($_POST['frequency']) && $_POST['frequency'] == 'annually') ? 'selected' : ''; ?>>Annually</option>
                </select>
                <div class="input-tip">How often you'll make deposits</div>
            </div>
            
            <div class="form-group">
                <label for="interest">Annual Interest Rate (%)</label>
                <input type="number" id="interest" name="interest" min="0" step="0.01" required value="<?php echo isset($_POST['interest']) ? htmlspecialchars($_POST['interest']) : '2.5'; ?>">
                <div class="input-tip">The annual percentage yield (APY) offered by your bank</div>
            </div>
            
            <div class="form-group">
                <label for="compound">Compound Frequency</label>
                <select id="compound" name="compound">
                    <option value="daily" <?php echo (isset($_POST['compound']) && $_POST['compound'] == 'daily') ? 'selected' : ''; ?>>Daily</option>
                    <option value="monthly" <?php echo (!isset($_POST['compound']) || $_POST['compound'] == 'monthly') ? 'selected' : ''; ?>>Monthly</option>
                    <option value="quarterly" <?php echo (isset($_POST['compound']) && $_POST['compound'] == 'quarterly') ? 'selected' : ''; ?>>Quarterly</option>
                    <option value="semi-annually" <?php echo (isset($_POST['compound']) && $_POST['compound'] == 'semi-annually') ? 'selected' : ''; ?>>Semi-Annually</option>
                    <option value="annually" <?php echo (isset($_POST['compound']) && $_POST['compound'] == 'annually') ? 'selected' : ''; ?>>Annually</option>
                </select>
                <div class="input-tip">How often your bank calculates interest</div>
            </div>
            
            <div class="form-group">
                <label for="years">Time Period (Years)</label>
                <input type="number" id="years" name="years" min="1" max="50" required value="<?php echo isset($_POST['years']) ? htmlspecialchars($_POST['years']) : '5'; ?>">
                <div class="input-tip">How long you plan to save (1-50 years)</div>
            </div>
            
            <div class="form-group">
                <label for="goal">Savings Goal ($) <small>(Optional)</small></label>
                <input type="number" id="goal" name="goal" min="0" step="any" value="<?php echo isset($_POST['goal']) ? htmlspecialchars($_POST['goal']) : '10000'; ?>">
                <div class="input-tip">Your target amount (leave blank if none)</div>
            </div>
            
            <button type="submit" name="calculate">Calculate Savings Growth</button>
        </form>
        
        <?php
        if (isset($_POST['calculate'])) {
            // Get input values
            $initialDeposit = floatval($_POST['initial']);
            $regularDeposit = floatval($_POST['deposit']);
            $interestRate = floatval($_POST['interest']) / 100;
            $years = intval($_POST['years']);
            $depositFrequency = $_POST['frequency'];
            $compoundFrequency = $_POST['compound'];
            $savingsGoal = !empty($_POST['goal']) ? floatval($_POST['goal']) : 0;
            
            // Determine compound periods per year
            $compoundsPerYear = 12; // Default to monthly
            if ($compoundFrequency == 'daily') {
                $compoundsPerYear = 365;
            } elseif ($compoundFrequency == 'quarterly') {
                $compoundsPerYear = 4;
            } elseif ($compoundFrequency == 'semi-annually') {
                $compoundsPerYear = 2;
            } elseif ($compoundFrequency == 'annually') {
                $compoundsPerYear = 1;
            }
            
            // Determine deposit periods per year
            $depositsPerYear = 12; // Default to monthly
            if ($depositFrequency == 'bi-weekly') {
                $depositsPerYear = 26;
            } elseif ($depositFrequency == 'weekly') {
                $depositsPerYear = 52;
            } elseif ($depositFrequency == 'quarterly') {
                $depositsPerYear = 4;
            } elseif ($depositFrequency == 'annually') {
                $depositsPerYear = 1;
            }
            
            // Calculate rate per period
            $ratePerPeriod = $interestRate / $compoundsPerYear;
            
            // Calculate savings growth
            $balance = $initialDeposit;
            $totalDeposits = $initialDeposit;
            $totalInterest = 0;
            $yearsToGoal = 0;
            $monthsToGoal = 0;
            $goalReached = false;
            
            $growthData = [];
            $growthData[] = [
                'year' => 0,
                'month' => 0,
                'balance' => $balance,
                'deposits' => $totalDeposits,
                'interest' => 0
            ];
            
            // Calculate monthly/period growth
            for ($year = 1; $year <= $years; $year++) {
                for ($month = 1; $month <= 12; $month++) {
                    // Apply compound interest for this month
                    $monthlyCompounds = $compoundsPerYear / 12;
                    for ($c = 0; $c < $monthlyCompounds; $c++) {
                        $interestEarned = $balance * $ratePerPeriod;
                        $balance += $interestEarned;
                        $totalInterest += $interestEarned;
                    }
                    
                    // Add regular deposits (if applicable for this month)
                    $depositsThisMonth = ($month * $depositsPerYear / 12) - ((($month - 1) * $depositsPerYear) / 12);
                    $depositsThisMonth = round($depositsThisMonth);
                    
                    if ($depositsThisMonth > 0) {
                        $depositAmount = $regularDeposit * $depositsThisMonth;
                        $balance += $depositAmount;
                        $totalDeposits += $depositAmount;
                    }
                    
                    // Check if goal is reached
                    if ($savingsGoal > 0 && !$goalReached && $balance >= $savingsGoal) {
                        $yearsToGoal = $year;
                        $monthsToGoal = $month;
                        $goalReached = true;
                    }
                }
                
                // Record yearly data
                $growthData[] = [
                    'year' => $year,
                    'month' => 12,
                    'balance' => $balance,
                    'deposits' => $totalDeposits,
                    'interest' => $totalInterest
                ];
            }
            
            // Display results
            echo '<div class="results">';
            echo '<h2>Savings Projection</h2>';
            
            echo '<div class="result-item">';
            echo '<span class="result-label">Final Balance:</span>';
            echo '<span class="result-value">$' . number_format($balance, 2) . '</span>';
            echo '</div>';
            
            echo '<div class="result-item">';
            echo '<span class="result-label">Total Deposits:</span>';
            echo '<span class="result-value">$' . number_format($totalDeposits, 2) . '</span>';
            echo '</div>';
            
            echo '<div class="result-item">';
            echo '<span class="result-label">Total Interest Earned:</span>';
            echo '<span class="result-value highlight">$' . number_format($totalInterest, 2) . '</span>';
            echo '</div>';
            
            if ($savingsGoal > 0) {
                echo '<div class="result-item">';
                echo '<span class="result-label">Savings Goal:</span>';
                echo '<span class="result-value">$' . number_format($savingsGoal, 2) . '</span>';
                echo '</div>';
                
                if ($goalReached) {
                    echo '<div class="result-item">';
                    echo '<span class="result-label">Time to Reach Goal:</span>';
                    echo '<span class="result-value">' . $yearsToGoal . ' years and ' . $monthsToGoal . ' months</span>';
                    echo '</div>';
                    
                    // Calculate progress percentage - 100% if reached
                    $progressPct = 100;
                } else {
                    echo '<div class="result-item">';
                    echo '<span class="result-label">Goal Status:</span>';
                    echo '<span class="result-value">Not reached within ' . $years . ' years</span>';
                    echo '</div>';
                    
                    // Calculate progress percentage
                    $progressPct = min(100, ($balance / $savingsGoal) * 100);
                }
                
                // Display progress bar
                echo '<div class="goal-progress">';
                echo '<div class="progress-bar" style="width: ' . $progressPct . '%;"></div>';
                echo '<div class="progress-text">' . number_format($progressPct, 1) . '% of goal</div>';
                echo '</div>';
            }
            
            echo '</div>';
            
            // Display yearly growth table
            echo '<div class="growth-chart">';
            echo '<h2>Year-by-Year Growth</h2>';
            echo '<table>';
            echo '<tr>';
            echo '<th>Year</th>';
            echo '<th>Balance</th>';
            echo '<th>Deposits to Date</th>';
            echo '<th>Interest Earned</th>';
            echo '</tr>';
            
            foreach ($growthData as $data) {
                echo '<tr>';
                echo '<td>' . $data['year'] . '</td>';
                echo '<td>$' . number_format($data['balance'], 2) . '</td>';
                echo '<td>$' . number_format($data['deposits'], 2) . '</td>';
                echo '<td>$' . number_format($data['interest'], 2) . '</td>';
                echo '</tr>';
            }
            
            echo '</table>';
            echo '</div>';
            
            // PHP function to calculate when goal will be reached
            function calculateTimeToGoal($initialDeposit, $regularDeposit, $interestRate, $depositsPerYear, $compoundsPerYear, $savingsGoal) {
                if ($savingsGoal <= $initialDeposit) {
                    return [0, 0]; // Goal already reached
                }
                
                $balance = $initialDeposit;
                $ratePerPeriod = $interestRate / $compoundsPerYear;
                $years = 0;
                $months = 0;
                
                while ($balance < $savingsGoal && $years < 100) { // Limit to 100 years to prevent infinite loop
                    $months++;
                    if ($months > 12) {
                        $months = 1;
                        $years++;
                    }
                    
                    // Apply compound interest for this month
                    $monthlyCompounds = $compoundsPerYear / 12;
                    for ($c = 0; $c < $monthlyCompounds; $c++) {
                        $balance += $balance * $ratePerPeriod;
                    }
                    
                    // Add regular deposits
                    $depositsThisMonth = ($months * $depositsPerYear / 12) - ((($months - 1) * $depositsPerYear) / 12);
                    $depositsThisMonth = round($depositsThisMonth);
                    
                    if ($depositsThisMonth > 0) {
                        $balance += $regularDeposit * $depositsThisMonth;
                    }
                }
                
                return [$years, $months];
            }
        }
        ?>
    </div>
    
    <?php if (isset($_POST['calculate'])): ?>
    <script>
        // Animate progress bar on load
        document.addEventListener('DOMContentLoaded', function() {
            const progressBar = document.querySelector('.progress-bar');
            if (progressBar) {
                setTimeout(() => {
                    progressBar.style.width = '<?php echo $progressPct; ?>%';
                }, 100);
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>