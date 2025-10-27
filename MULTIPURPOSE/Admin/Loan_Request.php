<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

include(__DIR__ . '/../config/dbconnect.php'); // ✅ correct relative path
$conn = connectDB(); // ✅ establish the DB connection

$message = "";

// Approve or Deny logic
if (isset($_GET['action']) && isset($_GET['loan_id'])) {
    $loan_id = $_GET['loan_id'];
    $action = $_GET['action'];

    if ($action === 'approve') {
        $conn->query("UPDATE loans SET status = 'approved' WHERE id = $loan_id");
        $message = "<div class='alert alert-success'>Loan #$loan_id approved successfully!</div>";
    } elseif ($action === 'deny') {
        $conn->query("UPDATE loans SET status = 'denied' WHERE id = $loan_id");
        $message = "<div class='alert alert-danger'>Loan #$loan_id has been denied.</div>";
    }
}

// Get all pending loans
$pending_loans = $conn->query("
    SELECT loans.*, users.first_name, users.last_name, loan_types.name AS loan_type_name
    FROM loans
    JOIN users ON loans.user_id = users.id
    JOIN loan_types ON loans.loan_type_id = loan_types.id
    WHERE loans.status = 'pending'
    ORDER BY loans.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Requests - Admin Dashboard</title>
    <style>
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
        
        /* Rest of the sidebar styles from member_dashboard.php */
        
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
        
        .transaction-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .transaction-title {
            font-size: 18px;
            font-weight: bold;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th {
            text-align: left;
            padding: 12px;
            background-color: #f8f9fa;
            color: #6c757d;
            font-weight: 600;
        }
        
        table td {
            padding: 12px;
            border-top: 1px solid #e9ecef;
        }
        
        table tr:hover {
            background-color: #f8f9fa;
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
        
        .btn-approve {
            background-color: #198754;
            color: white;
            margin-right: 8px;
        }
        
        .btn-approve:hover {
            background-color: #157347;
        }
        
        .btn-deny {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-deny:hover {
            background-color: #bb2d3b;
        }
        
        .no-requests {
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
            text-align: center;
            color: #6c757d;
        }
    </style>
</head>
<body>
    
    
    <!-- Main Content -->
    <div class="content">
        <div class="top-bar">
            <div class="welcome">Loan Applications</div>
            <!-- User profile dropdown would go here -->
        </div>
        
        <a class="back-link" href="admin_dashboard.php">← Back to Dashboard</a>
        
        <div class="transaction-list">
            <div class="transaction-header">
                <div class="transaction-title">Pending Loan Requests</div>
            </div>
            
            <?php echo $message; ?>
            
            <?php if ($pending_loans->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Member</th>
                            <th>Loan Type</th>
                            <th>Amount (₱)</th>
                            <th>Term</th>
                            <th>Interest</th>
                            <th>Purpose</th>
                            <th>Requested</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($loan = $pending_loans->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $loan['id']; ?></td>
                                <td><?php echo htmlspecialchars($loan['first_name'] . ' ' . htmlspecialchars($loan['last_name'])) ?></td>
                                <td><?php echo htmlspecialchars($loan['loan_type_name']); ?></td>
                                <td>₱<?php echo number_format($loan['amount'], 2); ?></td>
                                <td><?php echo $loan['term']; ?> months</td>
                                <td><?php echo $loan['interest_rate']; ?>%</td>
                                <td><?php echo htmlspecialchars($loan['purpose']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($loan['created_at'])); ?></td>
                                <td>
                                    <a href="?loan_id=<?php echo $loan['id']; ?>&action=approve" class="btn btn-approve">Approve</a>
                                    <a href="?loan_id=<?php echo $loan['id']; ?>&action=deny" class="btn btn-deny">Deny</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="no-requests">No pending loan requests found.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // JavaScript for dropdown functionality (same as admin_dashboard.php)
        document.addEventListener('DOMContentLoaded', function() {
            var dropdownBtns = document.getElementsByClassName("dropdown-btn");
            
            for (var i = 0; i < dropdownBtns.length; i++) {
                dropdownBtns[i].addEventListener("click", function() {
                    this.classList.toggle("active");
                    var dropdownContent = this.nextElementSibling;
                    if (dropdownContent.style.display === "block") {
                        dropdownContent.style.display = "none";
                    } else {
                        dropdownContent.style.display = "block";
                    }
                });
            }
            
            // Open the current section dropdown by default
            document.querySelector('.dropdown-btn:contains("Loan")').click();
        });
    </script>
</body>
</html>