<?php
/**
 * Loan Types Information Script
 * 
 * This script provides information about various loan types
 * and helps users select the appropriate loan for their needs.
 */

// Define loan types with their details
function getLoanTypes() {
    return [
        'personal' => [
            'name' => 'Personal Loan',
            'description' => 'Unsecured loans for personal expenses like debt consolidation, home improvements, or major purchases.',
            'interest_range' => '5.99% - 35.99%',
            'term_range' => '12 - 84 months',
            'min_amount' => 1000,
            'max_amount' => 50000,
            'requirements' => [
                'Credit score minimum: 580+',
                'Proof of income',
                'Valid ID',
                'Bank account verification'
            ],
            'benefits' => [
                'No collateral required',
                'Fast approval process',
                'Flexible use of funds',
                'Fixed interest rates and payments'
            ],
            'image' => 'images/personal-loan.jpg'
        ],
        'mortgage' => [
            'name' => 'Mortgage Loan',
            'description' => 'Long-term loans used to purchase a home or property with the property serving as collateral.',
            'interest_range' => '2.75% - 7.25%',
            'term_range' => '15 - 30 years',
            'min_amount' => 50000,
            'max_amount' => 2000000,
            'requirements' => [
                'Credit score minimum: 620+',
                'Down payment (typically 3-20%)',
                'Proof of stable income',
                'Debt-to-income ratio under 43%',
                'Property appraisal'
            ],
            'benefits' => [
                'Lower interest rates than most loans',
                'Tax-deductible interest',
                'Build home equity',
                'Various program options'
            ],
            'image' => 'images/mortgage-loan.jpg'
        ],
        'auto' => [
            'name' => 'Auto Loan',
            'description' => 'Secured loans specifically for purchasing vehicles, with the vehicle serving as collateral.',
            'interest_range' => '2.49% - 12.99%',
            'term_range' => '24 - 84 months',
            'min_amount' => 5000,
            'max_amount' => 100000,
            'requirements' => [
                'Credit score minimum: 600+',
                'Proof of income',
                'Valid driver\'s license',
                'Vehicle information',
                'Down payment (sometimes required)'
            ],
            'benefits' => [
                'Competitive interest rates',
                'Quick approval process',
                'Fixed monthly payments',
                'Option to refinance later'
            ],
            'image' => 'images/auto-loan.jpg'
        ],
        'student' => [
            'name' => 'Student Loan',
            'description' => 'Education loans designed to help students pay for tuition, books, and living expenses.',
            'interest_range' => '2.75% - 12.50%',
            'term_range' => '5 - 25 years',
            'min_amount' => 1000,
            'max_amount' => 200000,
            'requirements' => [
                'Enrollment in eligible educational institution',
                'Satisfactory academic progress',
                'Credit check for private loans',
                'Co-signer may be required for some loans'
            ],
            'benefits' => [
                'Deferred payments while in school',
                'Possible income-based repayment',
                'Tax-deductible interest',
                'Loan forgiveness options for certain careers'
            ],
            'image' => 'images/student-loan.jpg'
        ],
        'business' => [
            'name' => 'Business Loan',
            'description' => 'Loans for business purposes including startup costs, expansion, equipment purchases, or working capital.',
            'interest_range' => '4.99% - 24.99%',
            'term_range' => '6 months - 10 years',
            'min_amount' => 5000,
            'max_amount' => 5000000,
            'requirements' => [
                'Business plan',
                'Business financial statements',
                'Personal and business credit history',
                'Time in business (typically 2+ years)',
                'Annual revenue requirements'
            ],
            'benefits' => [
                'Grow your business',
                'Tax-deductible interest',
                'Keep full ownership of your business',
                'Establish business credit'
            ],
            'image' => 'images/business-loan.jpg'
        ],
        'heloc' => [
            'name' => 'Home Equity Line of Credit (HELOC)',
            'description' => 'A revolving line of credit secured by your home that allows you to borrow against your home equity.',
            'interest_range' => '3.50% - 8.75%',
            'term_range' => '5 - 30 years',
            'min_amount' => 10000,
            'max_amount' => 500000,
            'requirements' => [
                'Home equity (typically 15-20% minimum)',
                'Credit score minimum: 620+',
                'Debt-to-income ratio under 43%',
                'Income verification',
                'Home appraisal'
            ],
            'benefits' => [
                'Only pay interest on what you borrow',
                'Flexible access to funds',
                'Potentially tax-deductible interest',
                'Lower interest rates than credit cards'
            ],
            'image' => 'images/heloc-loan.jpg'
        ]
    ];
}

/**
 * Get details for a specific loan type
 * 
 * @param string $loanType The type of loan to retrieve
 * @return array|null The loan details or null if not found
 */
function getLoanTypeDetails($loanType) {
    $loanTypes = getLoanTypes();
    
    if (isset($loanTypes[$loanType])) {
        return $loanTypes[$loanType];
    }
    
    return null;
}

/**
 * Recommend loan types based on purpose
 * 
 * @param string $purpose The purpose for the loan
 * @return array List of recommended loan types
 */
function recommendLoanTypes($purpose) {
    $loanTypes = getLoanTypes();
    $recommendations = [];
    
    switch (strtolower($purpose)) {
        case 'home purchase':
        case 'buying a house':
        case 'real estate':
            $recommendations[] = $loanTypes['mortgage'];
            $recommendations[] = $loanTypes['heloc'];
            break;
        
        case 'car purchase':
        case 'vehicle':
        case 'auto':
            $recommendations[] = $loanTypes['auto'];
            $recommendations[] = $loanTypes['personal'];
            break;
        
        case 'education':
        case 'college':
        case 'university':
        case 'school':
            $recommendations[] = $loanTypes['student'];
            $recommendations[] = $loanTypes['personal'];
            break;
        
        case 'business':
        case 'startup':
        case 'entrepreneurship':
            $recommendations[] = $loanTypes['business'];
            $recommendations[] = $loanTypes['personal'];
            break;
        
        case 'home improvement':
        case 'renovation':
        case 'remodel':
            $recommendations[] = $loanTypes['heloc'];
            $recommendations[] = $loanTypes['personal'];
            break;
        
        case 'debt consolidation':
        case 'consolidate debt':
            $recommendations[] = $loanTypes['personal'];
            $recommendations[] = $loanTypes['heloc'];
            break;
        
        default:
            $recommendations[] = $loanTypes['personal'];
            break;
    }
    
    return $recommendations;
}

/**
 * Calculate estimated monthly payment
 * 
 * @param float $loanAmount The loan principal amount
 * @param float $interestRate Annual interest rate (as a percentage)
 * @param int $termMonths Loan term in months
 * @return float The estimated monthly payment
 */
function calculateMonthlyPayment($loanAmount, $interestRate, $termMonths) {
    // Convert interest rate to decimal and then to monthly rate
    $monthlyRate = ($interestRate / 100) / 12;
    
    // Calculate monthly payment using the formula:
    // P = L[c(1 + c)^n]/[(1 + c)^n - 1]
    // Where:
    // P = monthly payment
    // L = loan amount
    // c = monthly interest rate (decimal)
    // n = number of payments (term in months)
    
    if ($monthlyRate == 0) {
        // If interest rate is 0, simply divide loan amount by term
        return $loanAmount / $termMonths;
    }
    
    $payment = $loanAmount * ($monthlyRate * pow(1 + $monthlyRate, $termMonths)) / (pow(1 + $monthlyRate, $termMonths) - 1);
    
    return round($payment, 2);
}

// Handle selected loan type
$selectedLoan = isset($_GET['type']) ? $_GET['type'] : '';
$loanDetails = null;

if (!empty($selectedLoan)) {
    $loanDetails = getLoanTypeDetails($selectedLoan);
}

// Handle loan purpose recommendation
$purpose = isset($_POST['purpose']) ? $_POST['purpose'] : '';
$recommendations = [];

if (!empty($purpose)) {
    $recommendations = recommendLoanTypes($purpose);
}

// Handle payment calculator
$calculatedPayment = null;
$calculatorAmount = isset($_POST['calc_amount']) ? floatval($_POST['calc_amount']) : '';
$calculatorRate = isset($_POST['calc_rate']) ? floatval($_POST['calc_rate']) : '';
$calculatorTerm = isset($_POST['calc_term']) ? intval($_POST['calc_term']) : '';

if (!empty($calculatorAmount) && !empty($calculatorRate) && !empty($calculatorTerm)) {
    $calculatedPayment = calculateMonthlyPayment($calculatorAmount, $calculatorRate, $calculatorTerm);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <a class="back-link" href="member_dashboard.php">← Back to Dashboard</a>
    <title>Loan Types and Information</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f9f9f9;
            color: #333;
        }
        .container {
            max-width: 1100px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        h1, h2, h3 {
            color: #2a5885;
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        .loan-types {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-bottom: 40px;
        }
        .loan-type {
            width: 30%;
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .loan-type:hover {
            transform: translateY(-5px);
        }
        .loan-type h3 {
            margin-top: 0;
            text-align: center;
        }
        .loan-detail {
            margin-bottom: 30px;
            padding: 20px;
            border-radius: 5px;
            background-color: #f1f7ff;
            border-left: 5px solid #2a5885;
        }
        .loan-detail h2 {
            margin-top: 0;
        }
        .loan-detail ul {
            padding-left: 20px;
        }
        .loan-detail li {
            margin-bottom: 8px;
        }
        .section {
            margin-bottom: 40px;
            padding: 20px;
            border-radius: 5px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        .calculator {
            background-color: #f1f7ff;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        .recommendations {
            background-color: #f1fff1;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        form {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="number"],
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #2a5885;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #1e3f66;
        }
        .result {
            font-size: 18px;
            font-weight: bold;
            color: #2a5885;
            margin-top: 15px;
        }
        .loan-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .loan-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            background-color: #fff;
        }
        .apply-btn {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 4px;
            margin-top: 10px;
        }
        .apply-btn:hover {
            background-color: #45a049;
        }
        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            background-color: #f1f1f1;
            border: 1px solid #ddd;
            border-bottom: none;
            border-radius: 5px 5px 0 0;
            margin-right: 5px;
        }
        .tab.active {
            background-color: #fff;
            border-bottom: 1px solid #fff;
            margin-bottom: -1px;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Loan Types and Information</h1>
        
        <div class="tabs">
            <div class="tab active" onclick="openTab(event, 'all-loans')">All Loan Types</div>
            <div class="tab" onclick="openTab(event, 'loan-finder')">Loan Finder</div>
            <div class="tab" onclick="openTab(event, 'loan-calculator')">Payment Calculator</div>
            <?php if ($loanDetails): ?>
                <div class="tab" onclick="openTab(event, 'loan-details')">Loan Details</div>
            <?php endif; ?>
        </div>
        
        <div id="all-loans" class="tab-content active">
            <p>Explore our various loan types to find the perfect financing solution for your needs:</p>
            
            <div class="loan-grid">
                <?php foreach(getLoanTypes() as $key => $loan): ?>
                    <div class="loan-card">
                        <h3><?php echo $loan['name']; ?></h3>
                        <p><?php echo $loan['description']; ?></p>
                        <p><strong>Interest:</strong> <?php echo $loan['interest_range']; ?></p>
                        <p><strong>Term:</strong> <?php echo $loan['term_range']; ?></p>
                        <p><strong>Amount:</strong> $<?php echo number_format($loan['min_amount']); ?> - $<?php echo number_format($loan['max_amount']); ?></p>
                        <a href="?type=<?php echo $key; ?>" class="apply-btn">View Details</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div id="loan-finder" class="tab-content">
            <div class="recommendations">
                <h2>Find the Right Loan for You</h2>
                <p>Tell us what you need financing for, and we'll recommend the best loan types for your needs.</p>
                
                <form method="post" action="">
                    <label for="purpose">Loan Purpose:</label>
                    <input type="text" id="purpose" name="purpose" placeholder="e.g., home purchase, education, car purchase, business, etc." required>
                    <button type="submit">Get Recommendations</button>
                </form>
                
                <?php if (!empty($recommendations)): ?>
                    <h3>Recommended Loan Types for: "<?php echo htmlspecialchars($purpose); ?>"</h3>
                    <div class="loan-grid">
                        <?php foreach($recommendations as $loan): ?>
                            <div class="loan-card">
                                <h3><?php echo $loan['name']; ?></h3>
                                <p><?php echo $loan['description']; ?></p>
                                <p><strong>Interest:</strong> <?php echo $loan['interest_range']; ?></p>
                                <p><strong>Term:</strong> <?php echo $loan['term_range']; ?></p>
                                <a href="loan-application.php" class="apply-btn">Apply Now</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div id="loan-calculator" class="tab-content">
            <div class="calculator">
                <h2>Loan Payment Calculator</h2>
                <p>Estimate your monthly payments based on loan amount, interest rate, and term.</p>
                <form method="post" action="">
                    <div>
                        <label for="calc_amount">Loan Amount ($):</label>
                        <input type="number" id="calc_amount" name="calc_amount" value="<?php echo $calculatorAmount; ?>" min="1000" step="1000" required>
                    </div>
                    
                    <div>
                        <label for="calc_rate">Interest Rate (%):</label>
                        <input type="number" id="calc_rate" name="calc_rate" value="<?php echo $calculatorRate; ?>" min="0.1" max="30" step="0.1" required>
                    </div>
                    
                    <div>
                        <label for="calc_term">Loan Term (months):</label>
                        <input type="number" id="calc_term" name="calc_term" value="<?php echo $calculatorTerm; ?>" min="12" max="360" step="12" required>
                    </div>
                    
                    <button type="submit">Calculate Payment</button>
                </form>
                
                <?php if ($calculatedPayment !== null): ?>
                    <div class="result">
                        <p>For a $<?php echo number_format($calculatorAmount, 2); ?> loan at <?php echo $calculatorRate; ?>% for <?php echo $calculatorTerm; ?> months:</p>
                        <p>Estimated Monthly Payment: $<?php echo number_format($calculatedPayment, 2); ?></p>
                        <p>Total Repayment: $<?php echo number_format($calculatedPayment * $calculatorTerm, 2); ?></p>
                        <p>Total Interest Paid: $<?php echo number_format(($calculatedPayment * $calculatorTerm) - $calculatorAmount, 2); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($loanDetails): ?>
            <div id="loan-details" class="tab-content <?php echo empty($recommendations) && $calculatedPayment === null ? 'active' : ''; ?>">
                <div class="loan-detail">
                    <h2><?php echo $loanDetails['name']; ?></h2>
                    <p><?php echo $loanDetails['description']; ?></p>
                    
                    <h3>Key Details</h3>
                    <ul>
                        <li><strong>Interest Rate Range:</strong> <?php echo $loanDetails['interest_range']; ?></li>
                        <li><strong>Term Range:</strong> <?php echo $loanDetails['term_range']; ?></li>
                        <li><strong>Loan Amount Range:</strong> $<?php echo number_format($loanDetails['min_amount']); ?> - $<?php echo number_format($loanDetails['max_amount']); ?></li>
                    </ul>
                    
                    <h3>Requirements</h3>
                    <ul>
                        <?php foreach($loanDetails['requirements'] as $requirement): ?>
                            <li><?php echo $requirement; ?></li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <h3>Benefits</h3>
                    <ul>
                        <?php foreach($loanDetails['benefits'] as $benefit): ?>
                            <li><?php echo $benefit; ?></li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <p><a href="loan-application.php" class="apply-btn">Apply for this Loan</a></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        function openTab(evt, tabName) {
            var i, tabContent, tabLinks;
            
            tabContent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabContent.length; i++) {
                tabContent[i].classList.remove("active");
            }
            
            tabLinks = document.getElementsByClassName("tab");
            for (i = 0; i < tabLinks.length; i++) {
                tabLinks[i].classList.remove("active");
            }
            
            document.getElementById(tabName).classList.add("active");
            evt.currentTarget.classList.add("active");
        }
    </script>
</body>
</html>