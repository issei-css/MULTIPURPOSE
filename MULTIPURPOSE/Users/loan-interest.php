<?php
/**
 * Loan Interest Calculator
 * 
 * A tool to calculate and compare how different interest rates
 * affect loan costs, monthly payments, and total interest paid.
 */

// Process form submission
$hasResults = false;
$loanAmount = 0;
$loanTerm = 0;
$baseRate = 0;
$compareRates = [];
$results = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $loanAmount = isset($_POST['loan_amount']) ? (float)$_POST['loan_amount'] : 0;
    $loanTerm = isset($_POST['loan_term']) ? (int)$_POST['loan_term'] * 12 : 0; // Convert years to months
    $baseRate = isset($_POST['base_rate']) ? (float)$_POST['base_rate'] / 100 : 0;
    
    // Get comparison rates
    $compareRatesInput = isset($_POST['compare_rates']) ? $_POST['compare_rates'] : '';
    $compareRatesArray = array_map('trim', explode(',', $compareRatesInput));
    
    // Add base rate to comparison array
    $compareRates = [$baseRate * 100];
    
    // Process and add valid comparison rates
    foreach ($compareRatesArray as $rate) {
        $rate = trim($rate);
        if (is_numeric($rate) && $rate >= 0 && $rate <= 100) {
            // Only add unique rates
            if (!in_array((float)$rate, $compareRates)) {
                $compareRates[] = (float)$rate;
            }
        }
    }
    
    // Sort rates in ascending order
    sort($compareRates);
    
    // Calculate results for each rate
    foreach ($compareRates as $rate) {
        $rateDecimal = $rate / 100;
        $monthlyPayment = calculateMonthlyPayment($loanAmount, $rateDecimal, $loanTerm);
        $totalInterest = calculateTotalInterest($loanAmount, $rateDecimal, $loanTerm);
        
        $results[] = [
            'rate' => $rate,
            'monthly_payment' => $monthlyPayment,
            'total_interest' => $totalInterest,
            'total_cost' => $loanAmount + $totalInterest,
        ];
    }
    
    $hasResults = true;
}

/**
 * Calculate the monthly payment for a loan
 * 
 * @param float $principal    The initial loan amount
 * @param float $rate         Annual interest rate (as a decimal, e.g., 0.05 for 5%)
 * @param int   $termMonths   Loan term in months
 * @return float              Monthly payment amount
 */
function calculateMonthlyPayment($principal, $rate, $termMonths) {
    // Convert annual rate to monthly rate
    $monthlyRate = $rate / 12;
    
    // If rate is zero, simple division
    if ($rate == 0) {
        return $principal / $termMonths;
    }
    
    // Standard amortization formula
    return $principal * ($monthlyRate * pow(1 + $monthlyRate, $termMonths)) / 
           (pow(1 + $monthlyRate, $termMonths) - 1);
}

/**
 * Calculate total interest paid over the loan term
 * 
 * @param float $principal    The initial loan amount
 * @param float $rate         Annual interest rate (as a decimal)
 * @param int   $termMonths   Loan term in months
 * @return float              Total interest paid
 */
function calculateTotalInterest($principal, $rate, $termMonths) {
    $monthlyPayment = calculateMonthlyPayment($principal, $rate, $termMonths);
    return ($monthlyPayment * $termMonths) - $principal;
}

/**
 * Calculate interest for a specific period (e.g., daily, monthly, yearly)
 * 
 * @param float $principal    The initial amount
 * @param float $rate         Annual interest rate (as a decimal)
 * @param string $period      Period type ('daily', 'monthly', 'yearly')
 * @return float              Interest for the period
 */
function calculatePeriodInterest($principal, $rate, $period = 'yearly') {
    switch ($period) {
        case 'daily':
            return $principal * ($rate / 365);
        case 'monthly':
            return $principal * ($rate / 12);
        case 'yearly':
        default:
            return $principal * $rate;
    }
}

/**
 * Format a number as currency
 * 
 * @param float $amount       Amount to format
 * @param string $currency    Currency symbol (default: $)
 * @return string             Formatted currency string
 */
function formatCurrency($amount, $currency = '$') {
    return $currency . number_format($amount, 2, '.', ',');
}

/**
 * Format a number as percentage
 * 
 * @param float $rate         Rate to format (decimal)
 * @param int $decimals       Number of decimal places
 * @return string             Formatted percentage string
 */
function formatPercentage($rate, $decimals = 2) {
    return number_format($rate, $decimals, '.', ',') . '%';
}

/**
 * Calculate breakeven point between two loan rates
 * 
 * @param float $loanAmount   Loan amount
 * @param float $rate1        First interest rate (decimal)
 * @param float $rate2        Second interest rate (decimal)
 * @param int $maxMonths      Maximum number of months to check
 * @return int|null           Breakeven point in months, or null if not found
 */
function calculateBreakeven($loanAmount, $rate1, $rate2, $maxMonths = 360) {
    if ($rate1 == $rate2) {
        return null; // No breakeven for identical rates
    }
    
    $payment1 = calculateMonthlyPayment($loanAmount, $rate1, $maxMonths);
    $payment2 = calculateMonthlyPayment($loanAmount, $rate2, $maxMonths);
    
    $balance1 = $loanAmount;
    $balance2 = $loanAmount;
    $totalPaid1 = 0;
    $totalPaid2 = 0;
    
    for ($month = 1; $month <= $maxMonths; $month++) {
        $interest1 = $balance1 * ($rate1 / 12);
        $interest2 = $balance2 * ($rate2 / 12);
        
        $principal1 = $payment1 - $interest1;
        $principal2 = $payment2 - $interest2;
        
        $balance1 -= $principal1;
        $balance2 -= $principal2;
        
        $totalPaid1 += $payment1;
        $totalPaid2 += $payment2;
        
        // Check for breakeven point (total paid becomes equal)
        if (abs($totalPaid1 - $totalPaid2) < 0.01) {
            return $month;
        }
        
        // If both loans are paid off, no breakeven
        if ($balance1 <= 0 && $balance2 <= 0) {
            break;
        }
    }
    
    return null; // No breakeven found within maxMonths
}

/**
 * Calculate the amount saved by taking a lower rate
 * 
 * @param float $loanAmount   Loan amount
 * @param float $rate1        Lower interest rate (decimal)
 * @param float $rate2        Higher interest rate (decimal)
 * @param int $termMonths     Loan term in months
 * @return float              Total savings over loan term
 */
function calculateRateSavings($loanAmount, $rate1, $rate2, $termMonths) {
    $totalInterest1 = calculateTotalInterest($loanAmount, $rate1, $termMonths);
    $totalInterest2 = calculateTotalInterest($loanAmount, $rate2, $termMonths);
    
    return $totalInterest2 - $totalInterest1;
}

/**
 * Calculate cost of points (buying down the interest rate)
 * 
 * @param float $loanAmount       Loan amount
 * @param float $points           Number of points (1 point = 1% of loan)
 * @return float                  Cost of points
 */
function calculatePointsCost($loanAmount, $points) {
    return $loanAmount * ($points / 100);
}

/**
 * Calculate months required to recover points cost
 * 
 * @param float $pointsCost       Cost of buying points
 * @param float $monthlySavings   Monthly payment savings
 * @return int                    Months to recover cost
 */
function calculatePointsBreakeven($pointsCost, $monthlySavings) {
    if ($monthlySavings <= 0) {
        return PHP_INT_MAX; // Infinite if no savings
    }
    
    return ceil($pointsCost / $monthlySavings);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Interest Calculator & Comparison</title>
    <style>
        /* CSS Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        /* Main Styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .description {
            color: #7f8c8d;
            font-size: 16px;
        }
        
        /* Form Styles */
        .calculator-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .btn {
            padding: 12px 20px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #2980b9;
        }
        
        .help-text {
            display: block;
            margin-top: 5px;
            font-size: 14px;
            color: #7f8c8d;
        }
        
        /* Results Styles */
        .results {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .comparison-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .comparison-table th,
        .comparison-table td {
            padding: 15px;
            text-align: right;
            border-bottom: 1px solid #ddd;
        }
        
        .comparison-table th {
            background-color: #f3f6f9;
            font-weight: 600;
            color: #2c3e50;
            text-align: center;
        }
        
        .comparison-table tr:hover {
            background-color: #f9f9f9;
        }
        
        .comparison-table .rate-column {
            text-align: center;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .best-rate {
            background-color: #e8f8f5 !important;
            font-weight: 700;
        }
        
        /* Interest Tools Styles */
        .tools-section {
            margin-top: 40px;
        }
        
        .tools-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .tool-card {
            background-color: #f8f9fa;
            border-radius: 6px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .tool-title {
            font-size: 18px;
            color: #2c3e50;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .section-title {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            color: #2c3e50;
        }
        
        /* Chart Container */
        .chart-container {
            margin-top: 30px;
            height: 400px;
            position: relative;
        }
        
        /* Points Calculator */
        .points-calculator {
            background-color: #f1f9fe;
            border-radius: 6px;
            padding: 20px;
            margin-top: 30px;
        }
        
        .points-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }
        
        .points-results {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #d1e6f9;
        }

        .savings-card {
            background-color: #e8f8f5;
            border-radius: 6px;
            padding: 15px;
            text-align: center;
            margin-top: 20px;
        }
        
        .savings-amount {
            font-size: 24px;
            font-weight: 700;
            color: #16a085;
            margin: 10px 0;
        }
        
        .savings-label {
            font-size: 14px;
            color: #7f8c8d;
        }
        
        @media (max-width: 768px) {
            .calculator-form,
            .tools-grid {
                grid-template-columns: 1fr;
            }
            
            .comparison-table {
                font-size: 14px;
            }
            
            .comparison-table th,
            .comparison-table td {
                padding: 10px 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Loan Interest Calculator & Comparison</h1>
            <a class="back-link" href="member_dashboard.php">← Back to Dashboard</a>s
            <p class="description">Compare different interest rates and see how they affect your loan costs</p>
        </div>
        
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="calculator-form">
            <div>
                <div class="form-group">
                    <label for="loan_amount">Loan Amount ($)</label>
                    <input type="number" name="loan_amount" id="loan_amount" class="form-control" step="1000" min="1" value="<?php echo $loanAmount; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="loan_term">Loan Term (Years)</label>
                    <input type="number" name="loan_term" id="loan_term" class="form-control" min="1" max="50" value="<?php echo $loanTerm / 12; ?>" required>
                </div>
            </div>
            
            <div>
                <div class="form-group">
                    <label for="base_rate">Base Interest Rate (%)</label>
                    <input type="number" name="base_rate" id="base_rate" class="form-control" step="0.01" min="0" max="100" value="<?php echo $baseRate * 100; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="compare_rates">Compare With Rates (%)</label>
                    <input type="text" name="compare_rates" id="compare_rates" class="form-control" value="<?php echo implode(', ', array_slice($compareRates, 1)); ?>">
                    <span class="help-text">Enter rates separated by commas (e.g., 4.5, 5.25, 6)</span>
                </div>
            </div>
            
            <div>
                <div class="form-group" style="margin-top: 32px;">
                    <button type="submit" class="btn">Calculate & Compare</button>
                </div>
            </div>
        </form>
        
        <?php if ($hasResults): ?>
        <div class="results">
            <h2 class="section-title">Interest Rate Comparison</h2>
            
            <table class="comparison-table">
                <thead>
                    <tr>
                        <th>Interest Rate</th>
                        <th>Monthly Payment</th>
                        <th>Total Interest</th>
                        <th>Total Cost</th>
                        <th>Savings vs Highest Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Find the highest rate for comparison
                    $highestRate = end($results)['rate'] / 100;
                    $lowestRate = $results[0]['rate'] / 100;
                    
                    foreach ($results as $index => $result): 
                        $isBaseRate = abs($result['rate'] - ($baseRate * 100)) < 0.0001;
                        $isLowestRate = $index === 0;
                        $savings = calculateRateSavings(
                            $loanAmount, 
                            $result['rate'] / 100, 
                            $highestRate, 
                            $loanTerm
                        );
                    ?>
                    <tr class="<?php echo $isLowestRate ? 'best-rate' : ''; ?>">
                        <td class="rate-column"><?php echo formatPercentage($result['rate']); ?></td>
                        <td><?php echo formatCurrency($result['monthly_payment']); ?></td>
                        <td><?php echo formatCurrency($result['total_interest']); ?></td>
                        <td><?php echo formatCurrency($result['total_cost']); ?></td>
                        <td>
                            <?php if ($result['rate'] < end($results)['rate']): ?>
                                <?php echo formatCurrency($savings); ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="savings-card">
                <div class="savings-label">Total Savings with Lowest Rate vs. Highest Rate</div>
                <div class="savings-amount">
                    <?php 
                    $totalSavings = calculateRateSavings($loanAmount, $lowestRate, $highestRate, $loanTerm);
                    echo formatCurrency($totalSavings); 
                    ?>
                </div>
                <div class="savings-label">
                    That's 
                    <?php 
                    $monthlySavings = $totalSavings / $loanTerm;
                    echo formatCurrency($monthlySavings); 
                    ?> saved per month!
                </div>
            </div>
            
            <div class="chart-container">
                <!-- For a real implementation, you would add a chart library here -->
                <div style="text-align: center; padding: 20px; color: #7f8c8d;">
                    A chart showing the comparison of interest rates would appear here.
                    <br>For a production version, include a JavaScript charting library like Chart.js.
                </div>
            </div>
            
            <div class="points-calculator">
                <h3 class="tool-title">Loan Points Calculator</h3>
                <p>Calculate if buying points to lower your interest rate is worth it.</p>
                
                <div class="points-form">
                    <div class="form-group">
                        <label for="points_amount">Number of Points</label>
                        <input type="number" id="points_amount" class="form-control" step="0.125" min="0" max="10" value="1">
                        <span class="help-text">Typically 1 point = 0.25% rate reduction</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="rate_reduction">Rate Reduction (%)</label>
                        <input type="number" id="rate_reduction" class="form-control" step="0.01" min="0" max="2" value="0.25">
                    </div>
                    
                    <div class="form-group">
                        <button type="button" id="calculate_points" class="btn">Calculate</button>
                    </div>
                </div>
                
                <div class="points-results" id="points_results" style="display: none;">
                    <div class="tools-grid">
                        <div class="tool-card">
                            <div class="tool-title">Cost of Points</div>
                            <div id="points_cost" style="font-size: 20px; font-weight: 600; color: #e74c3c;">$0.00</div>
                        </div>
                        
                        <div class="tool-card">
                            <div class="tool-title">Monthly Savings</div>
                            <div id="monthly_savings" style="font-size: 20px; font-weight: 600; color: #27ae60;">$0.00</div>
                        </div>
                        
                        <div class="tool-card">
                            <div class="tool-title">Break-Even Point</div>
                            <div id="breakeven_months" style="font-size: 20px; font-weight: 600; color: #3498db;">0 months</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="tools-section">
                <h2 class="section-title">Interest Tools</h2>
                <div class="tools-grid">
                    <div class="tool-card">
                        <div class="tool-title">Interest Cost Calculator</div>
                        <div class="form-group">
                            <label for="interest_principal">Principal Amount ($)</label>
                            <input type="number" id="interest_principal" class="form-control" value="10000">
                        </div>
                        <div class="form-group">
                            <label for="interest_rate">Interest Rate (%)</label>
                            <input type="number" id="interest_rate" class="form-control" step="0.01" value="5">
                        </div>
                        <div class="form-group">
                            <label for="interest_period">Period</label>
                            <select id="interest_period" class="form-control">
                                <option value="daily">Daily</option>
                                <option value="monthly">Monthly</option>
                                <option value="yearly" selected>Yearly</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <button type="button" id="calculate_interest" class="btn">Calculate</button>
                        </div>
                        <div id="interest_result" style="margin-top: 15px; font-size: 18px; font-weight: 600;"></div>
                    </div>
                    
                    <div class="tool-card">
                        <div class="tool-title">Rate Increase Impact</div>
                        <p>See how a 1% rate increase affects your monthly payment.</p>
                        <div id="rate_increase_result" style="margin-top: 15px;">
                            <?php 
                            if ($hasResults && count($results) > 0) {
                                $basePayment = $results[0]['monthly_payment'];
                                $onePercentMore = calculateMonthlyPayment(
                                    $loanAmount, 
                                    ($results[0]['rate'] + 1) / 100, 
                                    $loanTerm
                                );
                                $difference = $onePercentMore - $basePayment;
                                $percentIncrease = ($difference / $basePayment) * 100;
                                
                                echo "<p>At <strong>" . formatPercentage($results[0]['rate']) . "</strong>: " . formatCurrency($basePayment) . "/month</p>";
                                echo "<p>At <strong>" . formatPercentage($results[0]['rate'] + 1) . "</strong>: " . formatCurrency($onePercentMore) . "/month</p>";
                                echo "<p>Difference: <strong>" . formatCurrency($difference) . "/month</strong></p>";
                                echo "<p>That's <strong>" . number_format($percentIncrease, 2) . "%</strong> more per month</p>";
                                echo "<p>Or <strong>" . formatCurrency($difference * $loanTerm) . "</strong> over the life of the loan</p>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Points Calculator
            const calculatePointsBtn = document.getElementById('calculate_points');
            if (calculatePointsBtn) {
                calculatePointsBtn.addEventListener('click', function() {
                    const loanAmount = parseFloat(document.getElementById('loan_amount').value) || 0;
                    const points = parseFloat(document.getElementById('points_amount').value) || 0;
                    const rateReduction = parseFloat(document.getElementById('rate_reduction').value) || 0;
                    const currentRate = parseFloat(document.getElementById('base_rate').value) || 0;
                    const loanTerm = parseInt(document.getElementById('loan_term').value) * 12 || 0;
                    
                    // Cost of points
                    const pointsCost = loanAmount * (points / 100);
                    
                    // Monthly payments before and after points
                    const monthlyPaymentBefore = calculateMonthlyPaymentJS(loanAmount, currentRate / 100, loanTerm);
                    const monthlyPaymentAfter = calculateMonthlyPaymentJS(loanAmount, (currentRate - rateReduction) / 100, loanTerm);
                    
                    const monthlySavings = monthlyPaymentBefore - monthlyPaymentAfter;
                    const breakEvenMonths = Math.ceil(pointsCost / monthlySavings);
                    
                    // Update the DOM
                    document.getElementById('points_cost').textContent = formatCurrencyJS(pointsCost);
                    document.getElementById('monthly_savings').textContent = formatCurrencyJS(monthlySavings);
                    document.getElementById('breakeven_months').textContent = isFinite(breakEvenMonths) ? 
                        breakEvenMonths + ' months (' + Math.floor(breakEvenMonths / 12) + ' years ' + 
                        (breakEvenMonths % 12) + ' months)' : 'Never';
                    
                    // Show results
                    document.getElementById('points_results').style.display = 'block';
                });
            }
            
            // Interest Calculator
            const calculateInterestBtn = document.getElementById('calculate_interest');
            if (calculateInterestBtn) {
                calculateInterestBtn.addEventListener('click', function() {
                    const principal = parseFloat(document.getElementById('interest_principal').value) || 0;
                    const rate = parseFloat(document.getElementById('interest_rate').value) / 100 || 0;
                    const period = document.getElementById('interest_period').value;
                    
                    let interestAmount = 0;
                    let periodLabel = '';
                    
                    switch (period) {
                        case 'daily':
                            interestAmount = principal * (rate / 365);
                            periodLabel = 'daily';
                            break;
                        case 'monthly':
                            interestAmount = principal * (rate / 12);
                            periodLabel = 'monthly';
                            break;
                        case 'yearly':
                            interestAmount = principal * rate;
                            periodLabel = 'yearly';
                            break;
                    }
                    
                    document.getElementById('interest_result').textContent = 
                        'Interest: ' + formatCurrencyJS(interestAmount) + ' ' + periodLabel;
                });
            }
        });
        
        // JavaScript version of calculateMonthlyPayment
        function calculateMonthlyPaymentJS(principal, rate, termMonths) {
            const monthlyRate = rate / 12;
            
            if (rate === 0) {
                return principal / termMonths;
            }
            
            return principal * (monthlyRate * Math.pow(1 + monthlyRate, termMonths)) / 
                   (Math.pow(1 + monthlyRate, termMonths) - 1);
        }
        
        // JavaScript version of formatCurrency
        function formatCurrencyJS(amount) {
            return '$' + amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        }
    </script>
</body>
</html>