<?php
session_start();
include(__DIR__ . '/config/dbconnect.php');

$conn = connectDB(); // Now this works

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validate inputs
    if (empty($username) || empty($password)) {
        $_SESSION['login_error'] = "Username and password are required!";
        header("Location: index.php");
        exit();
    }

    $query = "SELECT id, username, password, role FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        error_log("Database error: " . $conn->error);
        $_SESSION['login_error'] = "System error. Please try again later.";
        header("Location: index.php");
        exit();
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Plaintext password check (consider upgrading to password_hash())
        if ($password === $row['password']) {
            // Set all session variables
            $_SESSION['user_id'] = $row['id'];  // Primary key from users table
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            
            // Debug output (remove in production)
            error_log("Login successful for user ID: " . $row['id']);

            // Role-based redirection
            $redirect = match($row['role']) {
                'admin' => 'Admin/admin_dashboard.php',
                'member' => 'Users/member_dashboard.php',
                default => 'index.php?error=invalid_role'
            };
            
            header("Location: $redirect");
            exit();
        } else {
            $_SESSION['login_error'] = "Invalid password!";
        }
    } else {
        $_SESSION['login_error'] = "User not found!";
    }

    $stmt->close();
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Login</title>
  <style>
    * {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background-color: #121212;
  color: #e0e0e0;
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden;
  position: relative;
}

.login-container {
  width: 100%;
  max-width: 400px;
  padding: 20px;
}

.login-box {
  background-color: #1f1f1f;
  padding: 30px;
  border-radius: 12px;
  box-shadow: 0 0 20px rgba(0, 0, 0, 0.6);
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.login-box h2 {
  color: #ffffff;
  margin-bottom: 10px;
  text-align: center;
  font-weight: 600;
}

.login-box input[type="text"],
.login-box input[type="password"] {
  background-color: #2b2b2b;
  border: 1px solid #444;
  color: #ffffff;
  padding: 12px;
  border-radius: 6px;
  font-size: 1rem;
  transition: border-color 0.2s;
}

.login-box input[type="text"]:focus,
.login-box input[type="password"]:focus {
  border-color: #90caf9;
  outline: none;
}

.login-box input::placeholder {
  color: #aaa;
}

.login-box button {
  background-color: #3f51b5;
  color: #fff;
  border: none;
  padding: 12px;
  border-radius: 6px;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.3s;
}

.login-box button:hover {
  background-color: #303f9f;
}

.show-password {
  font-size: 0.9rem;
  color: #ccc;
  display: flex;
  align-items: center;
  gap: 5px;
}

.register-link {
  font-size: 0.9rem;
  text-align: center;
  color: #ccc;
}

.register-link a {
  color: #90caf9;
  text-decoration: none;
}

.register-link a:hover {
  text-decoration: underline;
}

.login-box p {
  color: #ff6b6b;
  font-size: 0.9rem;
  text-align: center;
  margin: -5px 0 5px 0;
}

.rainbow {
  position: absolute;
  bottom: 0;
  width: 100%;
  height: 2px;
  background: linear-gradient(to right, red, orange, yellow, green, blue, indigo, violet);
  opacity: 0.2;
  animation: moveRainbow 3s linear infinite;
}

@keyframes moveRainbow {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(100%); }
}

  </style>
</head>
<body>
  <div class="login-container">
    <form class="login-box" method="POST" action="">
      <h2>Login</h2>

      <!-- Error Message -->
      <?php if (isset($_SESSION['login_error'])): ?>
        <p><?= htmlspecialchars($_SESSION['login_error']) ?></p>
        <?php unset($_SESSION['login_error']); ?>
      <?php endif; ?>

      <input type="text" name="username" placeholder="Username" required>
      <input type="password" name="password" id="password" placeholder="Password" required>

      <label class="show-password">
        <input type="checkbox" onclick="togglePassword()"> Show Password
      </label>

      <button type="submit">Login</button>

      <p class="register-link">Don't have an account? <a href="register.php">Register</a></p>
    </form>
  </div>

  <script>
    function togglePassword() {
      const password = document.getElementById("password");
      password.type = password.type === "password" ? "text" : "password";
    }
  </script>

  <?php for ($i = 0; $i < 26; $i++): ?>
    <div class="rainbow"></div>
  <?php endfor; ?>
</body>
</html>