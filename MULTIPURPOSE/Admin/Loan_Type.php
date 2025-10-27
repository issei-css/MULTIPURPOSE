<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

include(__DIR__ . '/../config/dbconnect.php'); // ✅ correct relative path
$conn = connectDB(); // ✅ establish the DB connection

$message = "";

// Add loan type
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $default_interest = $_POST['default_interest'] ?? '';

    if ($name && is_numeric($default_interest)) {
        $stmt = $conn->prepare("INSERT INTO loan_types (name, description, default_interest) VALUES (?, ?, ?)");
        $stmt->bind_param("ssd", $name, $description, $default_interest);
        if ($stmt->execute()) {
            $message = "Loan type added successfully!";
        } else {
            $message = "Error saving loan type.";
        }
    } else {
        $message = "Please complete all fields correctly.";
    }
}

// Fetch all loan types
$loan_types = $conn->query("SELECT * FROM loan_types ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Types Management</title>
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
        
        .welcome {
            font-size: 24px;
            font-weight: bold;
        }
        
        h2 {
            color: #212529;
            margin-bottom: 20px;
            font-size: 24px;
        }
        
        h3 {
            color: #212529;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .message {
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            background-color: #d1e7dd;
            color: #0f5132;
            font-weight: 500;
        }
        
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .form-section, .table-section {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        label {
            font-weight: 600;
            color: #495057;
            font-size: 14px;
        }
        
        input, textarea {
            padding: 10px 12px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            font-size: 14px;
            width: 100%;
        }
        
        textarea {
            min-height: 80px;
            resize: vertical;
        }
        
        .submit-btn {
            padding: 10px 15px;
            background-color: #0d6efd;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .submit-btn:hover {
            background-color: #0b5ed7;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        th {
            text-align: left;
            padding: 12px;
            background-color: #f8f9fa;
            color: #6c757d;
            font-weight: 600;
        }
        
        td {
            padding: 12px;
            border-top: 1px solid #e9ecef;
            color: #212529;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #0d6efd;
            text-decoration: none;
            font-weight: 500;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    
    <!-- Main Content -->
    <div class="content">
        <!-- Top Bar -->
        
        
        <a class="back-link" href="admin_dashboard.php">← Back to Dashboard</a>
        
        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div class="grid">
            <div class="form-section">
                <h3>Add New Loan Type</h3>
                <form method="POST">
                    <label>Loan Type Name</label>
                    <input type="text" name="name" required>
                    
                    <label>Description</label>
                    <textarea name="description" rows="3"></textarea>
                    
                    <label>Default Interest Rate (%)</label>
                    <input type="number" name="default_interest" step="0.01" required>
                    
                    <button type="submit" class="submit-btn">Add Loan Type</button>
                </form>
            </div>
            
            <div class="table-section">
                <h3>Existing Loan Types</h3>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Type Name</th>
                            <th>Description</th>
                            <th>Interest (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $loan_types->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['description']); ?></td>
                                <td><?php echo htmlspecialchars($row['default_interest']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script>
        // JavaScript for dropdown functionality (same as member_dashboard.php)
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
            
            // User profile dropdown functionality
            const userProfile = document.getElementById('user-profile');
            const userDropdown = document.createElement('div');
            userDropdown.className = 'user-dropdown';
            userDropdown.innerHTML = `
                <a href="profile.php" class="user-dropdown-item">
                    <i>👤</i> Profile
                </a>
                <a href="settings.php" class="user-dropdown-item">
                    <i>⚙️</i> Settings
                </a>
                <a href="../logout.php" class="user-dropdown-item logout-btn">
                    <i>🚪</i> Logout
                </a>
            `;
            userProfile.appendChild(userDropdown);
            
            userProfile.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdown.classList.toggle('active');
            });
            
            document.addEventListener('click', function(e) {
                if (!userProfile.contains(e.target)) {
                    userDropdown.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>