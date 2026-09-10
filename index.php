<?php
require 'config/config.php';
require 'config/functions.php';
if(isset($_SESSION['user_id'])){
header('Location: '. BASE_URL . '/app/' . $_SESSION['user_role']. '/index.php');
exit;
}

$error = '';

if($_SERVER['REQUEST_METHOD'] ==='POST'){
$login = trim($_POST['login'] ??'');
$password = $_POST['password'] ?? '';

if (loginUser($pdo,$login,$password)){
header('Location: '. BASE_URL . '/app/' . $_SESSION['user_role']. '/index.php');
exit;


}
$error = 'Invalid Login Credentials';



}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in</title>
</head>
<body>

    <?php if ($error): ?>
        <p><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>


    <h1>User Login</h1>
    <form method="POST">
        <label> Username or email</label>
        <input type="text"
        name="login"
        required
        >
        <br>
        <label>Password</label>
        <input type="password"
               name="password"
               required
        >
        <br>
        <button type = "submit">Sign in</button>
</form>
</body>
</html>