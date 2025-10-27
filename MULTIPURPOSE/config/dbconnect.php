<?php
function connectDB() {
    $host = 'localhost';     // or your database host
    $user = 'root';          // your DB username
    $pass = '';              // your DB password
    $db   = 'sipagan_project_multipurpose'; // your database name

    $conn = new mysqli($host, $user, $pass, $db);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}
?>
