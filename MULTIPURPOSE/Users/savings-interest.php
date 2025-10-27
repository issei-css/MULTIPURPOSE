<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Savings Interest Calculator</title>
    <style>
        :root {
            --primary-color: #2e86de;
            --primary-dark: #1c5596;
            --secondary-color: #10ac84;
            --light-gray: #f5f6fa;
            --mid-gray: #dcdde1;
            --dark-gray: #7f8fa6;
            --text-color: #2f3542;
            --border-radius: 8px;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background-color: var(--light-gray);
            color: var(--text-color);
            line-height: 1.6;
        }
        
        .container {
            background-color: #fff;
            border-radius: var(--border-radius);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        h1 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .subtitle {
            text-align: center;
            color: var(--dark-gray);
            margin-top: -15px;
            margin-bottom: 30px;
        }
        
        .calculator-layout {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
        }
        
        .calculator-inputs {
            flex: 1;
            min-width: 300px;
        }
        
        .calculator-results {
            flex: 1;
            min-width: 300px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-color);
        }
        
        .label-with-tip {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .tip-icon {
            cursor: help;
            display: inline-block;
            width: 18px;
            height: 18px;
            background-color: var(--dark-gray);
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 18px;
            font-size: 12px;
            font-weight: bold;
            position: relative;
        }
        
        .tip-icon:hover::after {
            content: attr(data-tip);
            position: absolute;
            background-color: var(--dark-gray);
            color: white;
            padding: 10px;
            border-radius: var(--border-radius);
            width: 200px;
            top: 100%;
            right: 0;
            z-index: 10;
            font-weight: normal;
            font-size: 14px;
            line-height: 1.4;
        }
        
        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--mid-gray);
            border-radius: var(--border-radius);
            font-size: 16px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        
        input:focus, select:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(46, 134, 222, 0.2);
        }
        
        button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 14px 20px;
            font-size: 16px;
            border-radius: var(--border-radius);
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s;
            font-weight: 600;
        }
        
        button:hover {
            background-color: var(--primary-dark);
        }
        
        .results-panel {
            background-color: var(--light-gray);
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .results-panel h2 {
            color: var(--primary-color);
            margin-top: 0;
            font-size: 20px;
            margin-bottom: 15px;
            border-bottom: 1px solid var(--mid-gray);
            padding-bottom: 10px;
        }
        
        .result-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
        }
        
        .result-label {
            font-weight: 600;
        }
        
        .result-value {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .highlight {
            color: var(--secondary-color);
        }
        
        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-radius: var(--border-radius);
            overflow: hidden;
        }
        
        .tab {
            padding: 12px 20px;
            cursor: pointer;
            background-color: var(--mid-gray);
            border: none;
            flex: 1;
            text-align: center;
            transition: background-color 0.3s;
            font-weight: 600;
        }
        
        .tab.active {
            background-color: var(--primary-color);
            color: white;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .comparison-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            border-radius: var(--border-radius);
            overflow: hidden;
        }
        
        .comparison-table th, .comparison-table td {
            padding: 12px;
            text-align: left;
            border: 1px solid var(--mid-gray);
        }
        
        .comparison-table th {
            background-color: #e6f2ff;
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .comparison-table tr:nth-child(even) {
            background-color: #f7faff;
        }
        
        .comparison-table tr:hover {
            background-color: #e6f2ff;
        }
        
        .interest-chart {
            margin-top: 20px;
            background-color: white;
            border-radius: var(--border-radius);
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }
        
        .chart-bar {
            height: 250px;
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            margin-top: 20px;
            padding-bottom: 30px;
            border-bottom: 2px solid var(--mid-gray);
            position: relative;
        }
        
        .chart-column {
            width: 40px;
            background-color: var(--primary-color);
            border-radius: 6px 6px 0 0;
            position: relative;
            transition: height 1s ease-out;
        }
        
        .chart-label {
            position: absolute;
            bottom: -30px;
            width: 100%;
            text-align: center;
            font-size: 12px;
            color: var(--dark-gray);
        }
        
        .chart-value {
            position: absolute;
            bottom: -20px;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .chart-column.interest {
            background-color: var(--secondary-color);
        }
        
        .chart-legend {
            display: flex;
            justify-content: center;
            margin-top: 40px;
            gap: 30px;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            font-size: 14px;
        }
        
        .legend-color {
            width: 15px;
            height: 15px;
            border-radius: 3px;
            margin-right: 8px;
        }
        
        .legend-label {
            color: var(--dark-gray);
        }
        
        .info-box {
            background-color: #e6f2ff;
            border-left: 4px solid var(--primary-color);
            padding: 15px;
            margin: 20px 0;
            border-radius: 0 var(--border-radius) var(--border-radius) 0;
        }
        
        .info-box h3 {
            margin-top: 0;
            color: var(--primary-color);
            font-size: 18px;
        }
        
        .info-box p {
            margin-bottom: 0;
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .calculator-layout {
                flex-direction: column;
            }
            
            .chart-bar {
                height: 200px;
            }
            
            .chart-column {
                width: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Savings Interest Calculator</h1>
        <p class="subtitle">Calculate and compare interest earnings on your savings</p>
        
        <div class="calculator-layout">
            <div class="calculator-inputs">
                <form id="interestForm" method="post">
                    <div class="form-group">
                        <div class="label-with-tip">
                            <label for="principal">Principal Amount ($)</label>
                            <span class="tip-icon" data-tip="The initial amount you deposit into your savings account.">?</span>
                        </div>
                        <input type="number" id="principal" name="principal" min="1" step="any" required value="<?php echo isset($_POST['principal']) ? htmlspecialchars($_POST['principal']) : '5000'; ?>">
                    </div>
                    
                    <div class="form-group">
                        <div class="label-with-tip">
                            <label for="interest_rate">Annual Interest Rate (%)</label>
                            <span class="tip-icon" data-tip="The annual interest rate offered by your bank, before compounding.">?</span>
                        </div>
                        <input type="number" id="interest_rate" name="interest_rate" min="0.01" step="0.01" required value="<?php echo isset($_POST['interest_rate']) ? htmlspecialchars($_POST['interest_rate']) : '3.5'; ?>">
                    </div>
                    
                    <div class="form-group">
                        <div class="label-with-tip">
                            <label for="compound_frequency">Compounding Frequency</label>
                            <span class="tip-icon" data-tip="How often the interest is calculated and added to your principal. More frequent compounding leads to higher returns.">?</span>
                        </div>
                        <select id="compound_frequency" name="compound_frequency">
                            <option value="daily" <?php echo (isset($_POST['compound_frequency']) && $_POST['compound_frequency'] == 'daily') ? 'selected' : ''; ?>>Daily (365/year)</option>
                            <option value="weekly" <?php echo (isset($_POST['compound_frequency']) && $_POST['compound_frequency'] == 'weekly') ? 'selected' : ''; ?>>Weekly (52/year)</option>
                            <option value="monthly" <?php echo (!isset($_POST['compound_frequency']) || $_POST['compound_frequency'] == 'monthly') ? 'selected' : ''; ?>>Monthly (12/year)</option>
                            <option value="quarterly" <?php echo (isset($_POST['compound_frequency']) && $_POST['compound_frequency'] == 'quarterly') ? 'selected' : ''; ?>>Quarterly (4/year)</option>
                            <option value="semi-annually" <?php echo (isset($_POST['compound_frequency']) && $_POST['compound_frequency'] == 'semi-annually') ? 'selected' : ''; ?>>Semi-Annually (2/year)</option>
                            <option value="annually" <?php echo (isset($_POST['compound_frequency']) && $_POST['compound_frequency'] == 'annually') ? 'selected' : ''; ?>>Annually (1/year)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <div class="label-with-tip">
                            <label for="time_period">Time Period (Years)</label>
                            <span class="tip-icon" data-tip="How long you plan to keep your money in the savings account.">?</span>
                        </div>
                        <input type="number" id="time_period" name="time_period" min="1" max="30" required value="<?php echo isset($_POST['time_period']) ? htmlspecialchars($_POST['time_period']) : '5'; ?>">
                    </div>
                    
                    <div class="form-group">
                        <div class="label-with-tip">
                            <label for="compare_rate">Compare with Interest Rate (%) <small>(Optional)</small></label>
                            <span class="tip-icon" data-tip="Add a second interest rate to compare results side by side.">?</span>
                        </div>
                        <input type="number" id="compare_rate" name="compare_rate" min="0" step="0.01" value="<?php echo isset($_POST['compare_rate']) ? htmlspecialchars($_POST['compare_rate']) : '4.5'; ?>">
                    </div>
                    
                    <button type="submit" name="calculate">Calculate Interest</button>
                </form>
            </div>
            
            <?php if (isset($_POST['calculate'])): ?>
            <div class="calculator-results">
                <div class="tabs">
                    <button class="tab active" onclick="openTab(event, 'resultsTab')">Interest Results</button>
                    <button class="tab" onclick="openTab(event, 'comparisonTab')">Comparison</button>
                </div>
                
                <?php
                // Get input values
                $principal = floatval($_POST['principal']);
                $interestRate = floatval($_POST['interest_rate']) / 100;
                $timePeriod = intval($_POST['time_period']);
                $compoundFrequency = $_POST['compound_frequency'];
                $compareRate = !empty($_POST['compare_rate']) ? floatval($_POST['compare_rate']) / 100 : 0;
                
                // Determine compound periods per year
                $compoundsPerYear = 12; // Default to monthly
                if ($compoundFrequency == 'daily') {
                    $compoundsPerYear = 365;
                } elseif ($compoundFrequency == 'weekly') {
                    $compoundsPerYear = 52;
                } elseif ($compoundFrequency == 'quarterly') {
                    $compoundsPerYear = 4;
                } elseif ($compoundFrequency == 'semi-annually') {
                    $compoundsPerYear = 2;
                } elseif ($compoundFrequency == 'annually') {
                    $compoundsPerYear = 1;
                }
                
                // Calculate effective annual rate (EAR)
                $effectiveRate = pow(1 + ($interestRate / $compoundsPerYear), $compoundsPerYear) - 1;
                
                // Calculate final amount
                $finalAmount = $principal * pow(1 + ($interestRate / $compoundsPerYear), $compoundsPerYear * $timePeriod);
                $totalInterest = $finalAmount - $principal;
                
                // Calculate comparison rate results if provided
                $showComparison = false;
                if ($compareRate > 0) {
                    $showComparison = true;
                    $compareFinalAmount = $principal * pow(1 + ($compareRate / $compoundsPerYear), $compoundsPerYear * $timePeriod);
                    $compareTotalInterest = $compareFinalAmount - $principal;
                    $interestDifference = $compareTotalInterest - $totalInterest;
                }
                
                // Generate yearly growth data
                $yearlyData = [];
                for ($year = 0; $year <= $timePeriod; $year++) {
                    $yearAmount = $principal * pow(1 + ($interestRate / $compoundsPerYear), $compoundsPerYear * $year);
                    $yearInterest = $yearAmount - $principal;
                    
                    if ($showComparison) {
                        $compareYearAmount = $principal * pow(1 + ($compareRate / $compoundsPerYear), $compoundsPerYear * $year);
                        $compareYearInterest = $compareYearAmount - $principal;
                    } else {
                        $compareYearAmount = 0;
                        $compareYearInterest = 0;
                    }
                    
                    $yearlyData[] = [
                        'year' => $year,
                        'amount' => $yearAmount,
                        'interest' => $yearInterest,
                        'compare_amount' => $compareYearAmount,
                        'compare_interest' => $compareYearInterest
                    ];
                }
                
                // Function to format percentage
                function formatPercent($value) {
                    return number_format($value * 100, 2) . '%';
                }
                ?>
                
                <div id="resultsTab" class="tab-content active">
                    <div class="results-panel">
                        <h2>Interest Summary</h2>
                        
                        <div class="result-item">
                            <span class="result-label">Initial Principal:</span>
                            <span class="result-value">$<?php echo number_format($principal, 2); ?></span>
                        </div>
                        
                        <div class="result-item">
                            <span class="result-label">Interest Rate:</span>
                            <span class="result-value"><?php echo number_format($interestRate * 100, 2); ?>%</span>
                        </div>
                        
                        <div class="result-item">
                            <span class="result-label">Effective Annual Rate (EAR):</span>
                            <span class="result-value"><?php echo formatPercent($effectiveRate); ?></span>
                        </div>
                        
                        <div class="result-item">
                            <span class="result-label">Time Period:</span>
                            <span class="result-value"><?php echo $timePeriod; ?> year<?php echo $timePeriod > 1 ? 's' : ''; ?></span>
                        </div>
                        
                        <div class="result-item">
                            <span class="result-label">Final Balance:</span>
                            <span class="result-value">$<?php echo number_format($finalAmount, 2); ?></span>
                        </div>
                        
                        <div class="result-item">
                            <span class="result-label">Total Interest Earned:</span>
                            <span class="result-value highlight">$<?php echo number_format($totalInterest, 2); ?></span>
                        </div>
                    </div>
                    
                    <div class="interest-chart">
                        <h2>Interest Growth Over Time</h2>
                        <div class="chart-bar">
                            <?php
                            // Find the max value for scaling
                            $maxValue = $finalAmount;
                            
                            // Generate chart bars
                            $barCount = min(10, $timePeriod + 1);
                            $step = max(1, floor($timePeriod / ($barCount - 1)));
                            
                            for ($i = 0; $i < $barCount; $i++) {
                                $yearIndex = $i * $step;
                                if ($yearIndex > $timePeriod) continue;
                                
                                $data = $yearlyData[$yearIndex];
                                $heightPercentage = ($data['amount'] / $maxValue) * 100;
                                $interestPercentage = ($data['interest'] / $maxValue) * 100;
                                $principalPercentage = (($data['amount'] - $data['interest']) / $maxValue) * 100;
                                
                                echo '<div style="display: flex; flex-direction: column; align-items: center;">';
                                echo '<div style="height: ' . $heightPercentage . '%; width: 40px; display: flex; flex-direction: column-reverse;">';
                                echo '<div class="chart-column" style="height: ' . $principalPercentage . '%;"></div>';
                                echo '<div class="chart-column interest" style="height: ' . $interestPercentage . '%;"></div>';
                                echo '</div>';
                                echo '<div class="chart-label">Year ' . $data['year'] . '</div>';
                                echo '<div class="chart-value">$' . number_format($data['amount'], 0) . '</div>';
                                echo '</div>';
                            }
                            ?>
                        </div>
                        <div class="chart-legend">
                            <div class="legend-item">
                                <div class="legend-color" style="background-color: var(--primary-color);"></div>
                                <div class="legend-label">Principal</div>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color" style="background-color: var(--secondary-color);"></div>
                                <div class="legend-label">Interest</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-box">
                        <h3>What is Compound Interest?</h3>
                        <p>Compound interest is interest calculated on the initial principal and also on the accumulated interest from previous periods. The more frequently interest is compounded, the more you earn over time. This is why Einstein reportedly called it "the eighth wonder of the world."</p>
                    </div>
                </div>
                
                <div id="comparisonTab" class="tab-content">
                    <?php if ($showComparison): ?>
                    <div class="results-panel">
                        <h2>Rate Comparison</h2>
                        
                        <div class="result-item">
                            <span class="result-label">Rate 1:</span>
                            <span class="result-value"><?php echo number_format($interestRate * 100, 2); ?>%</span>
                        </div>
                        
                        <div class="result-item">
                            <span class="result-label">Rate 2:</span>
                            <span class="result-value"><?php echo number_format($compareRate * 100, 2); ?>%</span>
                        </div>
                        
                        <div class="result-item">
                            <span class="result-label">Final Balance (Rate 1):</span>
                            <span class="result-value">$<?php echo number_format($finalAmount, 2); ?></span>
                        </div>
                        
                        <div class="result-item">
                            <span class="result-label">Final Balance (Rate 2):</span>
                            <span class="result-value">$<?php echo number_format($compareFinalAmount, 2); ?></span>
                        </div>
                        
                        <div class="result-item">
                            <span class="result-label">Interest Difference:</span>
                            <span class="result-value highlight">$<?php echo number_format($interestDifference, 2); ?></span>
                        </div>
                    </div>
                    
                    <table class="comparison-table">
                        <tr>
                            <th>Year</th>
                            <th>Balance at <?php echo number_format($interestRate * 100, 2); ?>%</th>
                            <th>Interest Earned</th>
                            <th>Balance at <?php echo number_format($compareRate * 100, 2); ?>%</th>
                            <th>Interest Earned</th>
                            <th>Difference</th>
                        </tr>
                        
                        <?php foreach ($yearlyData as $data): ?>
                        <tr>
                            <td><?php echo $data['year']; ?></td>
                            <td>$<?php echo number_format($data['amount'], 2); ?></td>
                            <td>$<?php echo number_format($data['interest'], 2); ?></td>
                            <td>$<?php echo number_format($data['compare_amount'], 2); ?></td>
                            <td>$<?php echo number_format($data['compare_interest'], 2); ?></td>
                            <td>$<?php echo number_format($data['compare_interest'] - $data['interest'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <div class="info-box">
                        <h3>Comparison Not Available</h3>
                        <p>Enter a comparison interest rate to see a side-by-side comparison of how different rates affect your savings growth over time.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function openTab(evt, tabName) {
            // Hide all tab content
            var tabcontent = document.getElementsByClassName("tab-content");
            for (var i = 0; i < tabcontent.length; i++) {
                tabcontent[i].className = tabcontent[i].className.replace(" active", "");
            }
            
            // Remove active class from all tabs
            var tabs = document.getElementsByClassName("tab");
            for (var i = 0; i < tabs.length; i++) {
                tabs[i].className = tabs[i].className.replace(" active", "");
            }
            
            // Show current tab and add active class
            document.getElementById(tabName).className += " active";
            evt.currentTarget.className += " active";
        }
    </script>
</body>
</html>