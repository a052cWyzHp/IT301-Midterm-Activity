<?php
require 'config.php';

$stmt = $pdo->query("SELECT COUNT(*) FROM login");
$count = $stmt->fetchColumn();
if ($count == 0) {
    $insert = $pdo->prepare("INSERT INTO login (username, password) VALUES (:username, :password)");
    $insert->execute([
        ':username' => 'admin',
        ':password' => 'root'
    ]);
}   

$errorMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Example query to check user credentials
    $stmt = $pdo->prepare("SELECT userID FROM login WHERE username = :username AND password = :password");
    $stmt->execute(['username' => $username, 'password' => $password]);
    $user = $stmt->fetch();
    if (empty($username) || empty($password)) {
        $errorMessage = 'Please fill in all fields.';
    } else {
        if ($user) {
            $_SESSION['userID'] = $user['userID'];
            $_SESSION['username'] = $username;
            header('Location: index.php');
            exit();
        } else {
            $errorMessage = 'Invalid username or password. Please try again.';
        }
    }

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<style>
.loginBox {
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    align-items: center;
    height: 50vh;
}
</style>
<body>

<div class="loginBox">
<form action="" method="POST">
    <label>Username</label><br><input type="text" id="username" name="username" placeholder="Enter your username"><br><br>
    <label>Password</label><br><input type="text" id="password" name="password" placeholder="Enter your password"><br><br>
    <center><button type="submit">Log In</button></center>
</form>
<?php if (!empty($errorMessage)) : ?>
    <div class="errorText"><?= htmlspecialchars($errorMessage) ?></div>
<?php endif; ?>
</div>

</body>
</html>