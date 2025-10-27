<?php
// loan-application.php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

include(__DIR__ . '/../config/dbconnect.php'); // ✅ correct relative path
$conn = connectDB(); // ✅ establish the DB connection

$message = "";
$user_id = $_SESSION['user_id'];

// Get loan types for dropdown
$loan_types = $conn->query("SELECT * FROM loan_types");

// Process loan application form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $loan_type_id = $_POST['loan_type'];
    $amount = $_POST['amount'];
    $term = $_POST['term'];
    $purpose = $_POST['purpose'];
    
    $type_query = $conn->query("SELECT default_interest FROM loan_types WHERE id = $loan_type_id");
    $type_data = $type_query->fetch_assoc();
    $interest_rate = $type_data['default_interest'];
    
    $stmt = $conn->prepare("INSERT INTO loans (user_id, loan_type_id, amount, term, interest_rate, purpose, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("iiddds", $user_id, $loan_type_id, $amount, $term, $interest_rate, $purpose);
    
    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>Loan application submitted successfully!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error submitting loan application: " . $conn->error . "</div>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Loan - Member Dashboard</title>
    <style>
        /* EXACTLY COPIED FROM Loan_Request.php */
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
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #0d6efd;
            text-decoration: none;
            font-weight: 600;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        h2 {
            margin-bottom: 20px;
            color: #212529;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: 600;
        }
        
        .alert-success {
            background-color: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #842029;
            border: 1px solid #f5c2c7;
        }
        
        .transaction-list {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        /* ADDITIONAL STYLES FOR FORM ELEMENTS TO MATCH */
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #6c757d;
            font-weight: 600;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            font-size: 14px;
        }
        
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .btn {
            padding: 8px 15px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            font-size: 14px;
        }
        
        .btn-submit {
            background-color: #0d6efd;
            color: white;
        }
        
        .btn-submit:hover {
            background-color: #0b5ed7;
        }
        
        .loan-info {
            margin-top: 30px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            border: 1px solid #e9ecef;
        }
        
        .loan-info h3 {
            margin-bottom: 10px;
            font-size: 16px;
            color: #212529;
        }
        
        .loan-info ul {
            list-style-type: none;
        }
        
        .loan-info li {
            margin-bottom: 8px;
            color: #6c757d;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <!-- Include your sidebar here -->
    
    <div class="content">
        <div class="top-bar">
            <div class="welcome">Apply for a Loan</div>
        </div>
        
        <a class="" href="member_dashboard.php">← Back to Dashboard</a>
        
        <?php echo $message; ?>
        
        <div class="transaction-list">
            <h2>Loan Application Form</h2>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="loan_type">Loan Type</label>
                    <select id="loan_type" name="loan_type" required>
                        <option value="">-- Select Loan Type --</option>
                        <?php while($type = $loan_types->fetch_assoc()): ?>
                            <option value="<?php echo $type['id']; ?>">
                                <?php echo htmlspecialchars($type['name']); ?> (<?php echo $type['default_interest']; ?>% interest)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="amount">Loan Amount (₱)</label>
                    <input type="number" id="amount" name="amount" min="1000" step="100" required>
                </div>
                
                <div class="form-group">
                    <label for="term">Loan Term (months)</label>
                    <input type="number" id="term" name="term" min="1" max="36" required>
                </div>
                
                <div class="form-group">
                    <label for="purpose">Purpose of Loan</label>
                    <textarea id="purpose" name="purpose" required></textarea>
                </div>
                
                <button type="submit" class="btn btn-submit">Submit Application</button>
            </form>
            
            <div class="loan-info">
                <h3>Loan Information</h3>
                <ul>
                    <li>Minimum loan amount: ₱1,000</li>
                    <li>Maximum loan term: 36 months</li>
                    <li>Interest rates vary by loan type</li>
                    <li>Applications are subject to approval</li>
                    <li>You will be notified of your application status within 3 business days</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>