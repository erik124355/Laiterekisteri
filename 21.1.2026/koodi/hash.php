<?php

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $password = $_POST["password"];
    $hash = password_hash($password, PASSWORD_BCRYPT);

    echo "<h3>Your hashed password:</h3>";
    echo "<textarea style='width:100%; height:80px;'>$hash</textarea>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Password Hash</title>
</head>
<body style="font-family: Arial; max-width: 400px;">
    <h2>Password Hash Generator</h2>
    <form method="POST">
        <label>Enter password:</label><br>
        <input type="text" name="password" required style="width:100%; padding:8px;">
        <br><br>
        <button type="submit" style="padding:10px 20px;">Generate hash</button>
    </form>
</body>
</html>