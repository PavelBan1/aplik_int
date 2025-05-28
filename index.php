<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$database = "bank";

$connection = new mysqli($servername, $username, $password, $database);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: index.php");
    exit();
}

if (isset($_POST['login'])) {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    $query = "SELECT * FROM adminy WHERE username = '$user' AND password = '$pass'";
    $result = $connection->query($query);

    if ($result->num_rows > 0) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $user;
        $_SESSION['role'] = 'Admin';
        header("Location: admin.php");
        exit();
    }

    $query = "SELECT * FROM klienty WHERE username = '$user' AND password = '$pass'";
    $result = $connection->query($query);

    if ($result->num_rows > 0) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $user;
        $_SESSION['role'] = 'Klient';
        header("Location: klient.php");
        exit();
    } else {
        $error = "Nieprawidłowa nazwa użytkownika lub hasło!";
    }
}

if (!isset($_SESSION['logged_in'])) {
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Bankowy - Logowanie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <h1>System Bankowy</h1>
        <?php if (isset($error)) { echo "<p class='text-danger'>$error</p>"; } ?>
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Nazwa użytkownika:</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Hasło:</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary">Zaloguj się</button>
        </form>
    </div>
</body>
</html>
<?php
    exit();
}
?>
