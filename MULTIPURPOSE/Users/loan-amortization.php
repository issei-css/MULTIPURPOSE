<?php
/**
 * Loan Amortization Calculator
 * 
 * This file contains a complete loan amortization calculator with HTML form interface
 * and functions to calculate loan details.
 */

// Process form submission
$hasResults = false;
$loanAmount = 0;
$interestRate = 0;
$loanTerm = 0;
$extraPayment = 0;
$monthlyPayment = 0;
$totalInterest = 0;
$schedule = [];
$impact = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $loanAmount = isset($_POST['loan_amount']) ? (float)$_POST['loan_amount'] : 0;
    $interestRate = isset($_POST['interest_rate']) ? (float)$_POST['interest_rate'] / 100 : 0;
    $loanTerm = isset($_POST['loan_term']) ? (int)$_POST['loan_term'] * 12 : 0; // Convert years to months
    $extraPayment = isset($_POST['extra_payment']) ? (float)$_POST['extra_payment'] : 0;
    
    // Calculate monthly payment
    $monthlyPayment = calculateMonthlyPayment($loanAmount, $interestRate, $loanTerm);
    
    // Calculate total interest
    $totalInterest = calculateTotalInterest($loanAmount, $interestRate, $loanTerm);
    
    // Generate amortization schedule
    $schedule = generateAmortizationSchedule($loanAmount, $interestRate, $loanTerm);
    
    // Calculate impact of extra payments
    if ($extraPayment > 0) {
        $impact = calculateExtraPaymentImpact($loanAmount, $interestRate, $loanTerm, $extraPayment);
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
 * Generate a complete amortization schedule for a loan
 * 
 * @param float $principal    The initial loan amount
 * @param float $rate         Annual interest rate (as a decimal)
 * @param int   $termMonths   Loan term in months
 * @return array              Complete amortization schedule
 */
function generateAmortizationSchedule($principal, $rate, $termMonths) {
    $monthlyPayment = calculateMonthlyPayment($principal, $rate, $termMonths);
    $monthlyRate = $rate / 12;
    $balance = $principal;
    $schedule = [];
    
    for ($month = 1; $month <= $termMonths; $month++) {
        // Calculate interest for this month
        $interestPayment = $balance * $monthlyRate;
        
        // Calculate principal for this month
        $principalPayment = $monthlyPayment - $interestPayment;
        
        // If this is the last payment, adjust for rounding errors
        if ($month == $termMonths) {
            $principalPayment = $balance;
            $monthlyPayment = $principalPayment + $interestPayment;
        }
        
        // Update remaining balance
        $balance -= $principalPayment;
        
        // Correct potential negative balance due to floating point precision
        if (abs($balance) < 0.001) {
            $balance = 0;
        }
        
        // Add this payment to the schedule
        $schedule[] = [
            'month' => $month,
            'payment' => $monthlyPayment,
            'principal' => $principalPayment,
            'interest' => $interestPayment,
            'balance' => $balance
        ];
    }
    
    return $schedule;
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
 * Calculate extra payment impact on loan term and interest savings
 * 
 * @param float $principal        The initial loan amount
 * @param float $rate             Annual interest rate (as a decimal)
 * @param int   $termMonths       Original loan term in months
 * @param float $extraPayment     Additional monthly payment
 * @return array                  Impact analysis with new term and savings
 */
function calculateExtraPaymentImpact($principal, $rate, $termMonths, $extraPayment) {
    $monthlyRate = $rate / 12;
    $regularPayment = calculateMonthlyPayment($principal, $rate, $termMonths);
    $totalPayment = $regularPayment + $extraPayment;
    
    $balance = $principal;
    $month = 0;
    $totalInterest = 0;
    
    // Calculate new payoff term with extra payments
    while ($balance > 0 && $month < 1200) { // 1200 = 100 years max to prevent infinite loops
        $month++;
        $interestPayment = $balance * $monthlyRate;
        $principalPayment = $totalPayment - $interestPayment;
        
        // Make sure we don't overpay
        if ($principalPayment > $balance) {
            $principalPayment = $balance;
        }
        
        $totalInterest += $interestPayment;
        $balance -= $principalPayment;
        
        if ($balance <= 0.001) {
            $balance = 0;
            break;
        }
    }
    
    // Calculate original total interest
    $originalTotalInterest = calculateTotalInterest($principal, $rate, $termMonths);
    $interestSavings = $originalTotalInterest - $totalInterest;
    $monthsSaved = $termMonths - $month;
    
    return [
        'new_term_months' => $month,
        'months_saved' => $monthsSaved,
        'interest_savings' => $interestSavings
    ];
}

/**
 * Build a Year-Month selector for displaying specific payment periods
 */
function buildYearMonthSelector($termMonths) {
    $years = ceil($termMonths / 12);
    $html = '<select name="display_period" id="display_period" class="form-control">';
    $html .= '<option value="all">Full Schedule</option>';
    $html .= '<option value="first12" selected>First Year</option>';
    
    for ($year = 1; $year <= $years; $year++) {
        $html .= '<option value="year' . $year . '">Year ' . $year . '</option>';
    }
    
    $html .= '</select>';
    return $html;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Amortization Calculator</title>
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
        
        /* Results Styles */
        .results {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .summary-box {
            background-color: #f8f9fa;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .summary-item {
            text-align: center;
        }
        
        .summary-label {
            font-size: 14px;
            color: #7f8c8d;
            margin-bottom: 5px;
        }
        
        .summary-value {
            font-size: 24px;
            font-weight: 700;
            color: #2c3e50;
        }
        
        /* Table Styles */
        .table-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .amortization-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .amortization-table th,
        .amortization-table td {
            padding: 12px 15px;
            text-align: right;
            border-bottom: 1px solid #ddd;
        }
        
        .amortization-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .amortization-table tr:hover {
            background-color: #f1f1f1;
        }
        
        .amortization-table th:first-child,
        .amortization-table td:first-child {
            text-align: center;
        }
        
        /* Extra Payment Impact Box */
        .impact-box {
            background-color: #e8f4fc;
            border-radius: 6px;
            padding: 20px;
            margin-top: 30px;
            border-left: 5px solid #3498db;
        }
        
        .impact-title {
            color: #2980b9;
            margin-bottom: 15px;
        }
        
        .impact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        @media (max-width: 768px) {
            .calculator-form,
            .summary-grid,
            .impact-grid {
                grid-template-columns: 1fr;
            }
            
            .table-controls {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Loan Amortization Calculator</h1>
            <p class="description">Calculate your loan payments, interest, and amortization schedule</p>
            <a class="back-link" href="member_dashboard.php">← Back to Dashboard</a>
        </div>
        
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="calculator-form">
            <div>
                <div class="form-group">
                    <label for="loan_amount">Loan Amount ($)</label>
                    <input type="number" name="loan_amount" id="loan_amount" class="form-control" step="1000" min="1" value="<?php echo $loanAmount; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="interest_rate">Annual Interest Rate (%)</label>
                    <input type="number" name="interest_rate" id="interest_rate" class="form-control" step="0.01" min="0" max="100" value="<?php echo $interestRate * 100; ?>" required>
                </div>
            </div>
            
            <div>
                <div class="form-group">
                    <label for="loan_term">Loan Term (Years)</label>
                    <input type="number" name="loan_term" id="loan_term" class="form-control" min="1" max="50" value="<?php echo $loanTerm / 12; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="extra_payment">Extra Monthly Payment ($) - Optional</label>
                    <input type="number" name="extra_payment" id="extra_payment" class="form-control" min="0" value="<?php echo $extraPayment; ?>">
                </div>
            </div>
            
            <div>
                <div class="form-group" style="margin-top: 32px;">
                    <button type="submit" class="btn">Calculate</button>
                </div>
            </div>
        </form>
        
        <?php if ($hasResults): ?>
        <div class="results">
            <div class="summary-box">
                <div class="summary-grid">
                    <div class="summary-item">
                        <div class="summary-label">Monthly Payment</div>
                        <div class="summary-value"><?php echo formatCurrency($monthlyPayment); ?></div>
                    </div>
                    
                    <div class="summary-item">
                        <div class="summary-label">Total Principal</div>
                        <div class="summary-value"><?php echo formatCurrency($loanAmount); ?></div>
                    </div>
                    
                    <div class="summary-item">
                        <div class="summary-label">Total Interest</div>
                        <div class="summary-value"><?php echo formatCurrency($totalInterest); ?></div>
                    </div>
                    
                    <div class="summary-item">
                        <div class="summary-label">Total Cost</div>
                        <div class="summary-value"><?php echo formatCurrency($loanAmount + $totalInterest); ?></div>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($impact)): ?>
            <div class="impact-box">
                <h3 class="impact-title">Impact of Extra Payment</h3>
                <div class="impact-grid">
                    <div class="summary-item">
                        <div class="summary-label">New Loan Term</div>
                        <div class="summary-value"><?php echo floor($impact['new_term_months'] / 12) . ' years ' . ($impact['new_term_months'] % 12) . ' months'; ?></div>
                    </div>
                    
                    <div class="summary-item">
                        <div class="summary-label">Time Saved</div>
                        <div class="summary-value"><?php echo floor($impact['months_saved'] / 12) . ' years ' . ($impact['months_saved'] % 12) . ' months'; ?></div>
                    </div>
                    
                    <div class="summary-item">
                        <div class="summary-label">Interest Savings</div>
                        <div class="summary-value"><?php echo formatCurrency($impact['interest_savings']); ?></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="table-controls">
                <h3>Amortization Schedule</h3>
                <div class="form-group">
                    <label for="display_period">Display Period</label>
                    <?php echo buildYearMonthSelector($loanTerm); ?>
                </div>
            </div>
            
            <div id="schedule-container">
                <table class="amortization-table">
                    <thead>
                        <tr>
                            <th>Payment #</th>
                            <th>Payment</th>
                            <th>Principal</th>
                            <th>Interest</th>
                            <th>Remaining Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Display first year by default
                        $displaySchedule = array_slice($schedule, 0, 12);
                        foreach ($displaySchedule as $payment): 
                        ?>
                        <tr>
                            <td><?php echo $payment['month']; ?></td>
                            <td><?php echo formatCurrency($payment['payment']); ?></td>
                            <td><?php echo formatCurrency($payment['principal']); ?></td>
                            <td><?php echo formatCurrency($payment['interest']); ?></td>
                            <td><?php echo formatCurrency($payment['balance']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        // JavaScript to handle showing different periods of the amortization schedule
        document.addEventListener('DOMContentLoaded', function() {
            const displayPeriodSelect = document.getElementById('display_period');
            if (!displayPeriodSelect) return;
            
            displayPeriodSelect.addEventListener('change', function() {
                const selectedPeriod = this.value;
                const scheduleContainer = document.getElementById('schedule-container');
                
                // We'll use AJAX to fetch the data for the selected period
                // For a real implementation, you would need to add an AJAX handler
                // For now, we'll just demonstrate the concept
                
                if (selectedPeriod === 'first12') {
                    console.log('Showing first 12 months');
                    // You'd replace this with actual AJAX call
                } else if (selectedPeriod === 'all') {
                    console.log('Showing all months');
                    // You'd replace this with actual AJAX call
                } else if (selectedPeriod.startsWith('year')) {
                    const year = selectedPeriod.replace('year', '');
                    console.log('Showing year ' + year);
                    // You'd replace this with actual AJAX call
                }
            });
        });
    </script>
</body>
</html>