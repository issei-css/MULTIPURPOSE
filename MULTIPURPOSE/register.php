<?php
session_start();
include('config/dbconnect.php');

$conn = connectDB();
$message = "";

// Handle registration
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $username = $_POST['username'];
    $birthday = $_POST['birthday'];
    $gender = $_POST['gender'];
    $age = $_POST['age'];
    $email = $_POST['email'];
    $password = $_POST['password']; 
    $profile_image = "assets/default.png";
    $role = "member";

    $stmt = $conn->prepare("INSERT INTO users (
        first_name, middle_name, last_name, username,
        birthday, gender, age, email, password,
        profile_image, role
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "ssssssissss",
        $first_name, $middle_name, $last_name, $username,
        $birthday, $gender, $age, $email, $password,
        $profile_image, $role
    );

    if ($stmt->execute()) {
        header("Location: index.php");
        exit();
    } else {
        $message = "Error: " . $stmt->error;
    }
    
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register</title>
  <style>
    body {
      background-color: #121212;
      color: #eee;
      font-family: 'Segoe UI', sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .register-box {
      background: #1e1e1e;
      padding: 30px;
      border-radius: 12px;
      width: 400px;
      box-shadow: 0 0 10px #000;
    }
    .register-box h2 {
      text-align: center;
      color: #90caf9;
    }
    .register-box input,
    .register-box select {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      background: #2a2a2a;
      color: #fff;
      border: 1px solid #444;
      border-radius: 5px;
    }
    .register-box button {
      width: 100%;
      padding: 10px;
      background-color: #3f51b5;
      color: white;
      border: none;
      border-radius: 5px;
      font-weight: bold;
      cursor: pointer;
    }
    .message {
      text-align: center;
      color: #4caf50;
      margin-top: 10px;
    }
  </style>
</head>
<body>

<div class="register-box">
  <h2>Register</h2>
  <?php if ($message): ?>
    <p class="message"><?= htmlspecialchars($message) ?></p>
  <?php endif; ?>
  <form method="POST">
    <input type="text" name="first_name" placeholder="First Name" required>
    <input type="text" name="middle_name" placeholder="Middle Name">
    <input type="text" name="last_name" placeholder="Last Name" required>
    <input type="text" name="username" placeholder="Username" required>
    <input type="date" name="birthday" required>
    <select name="gender" required>
      <option value="">Select Gender</option>
      <option value="Male">Male</option>
      <option value="Female">Female</option>
    </select>
    <input type="number" name="age" placeholder="Age" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Register</button>
  </form>
</div>

</body>
</html>
