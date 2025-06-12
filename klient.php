<?php
session_start();

$timeout = 900;

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}

$_SESSION['last_activity'] = time();

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'Klient') {
    header("Location: index.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$database = "bank";

$connection = new mysqli($servername, $username, $password, $database);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

$username = $_SESSION['username'];
$query = "SELECT imie, kredyt FROM klienty WHERE username = '$username'";
$result = $connection->query($query);
$imie = "Klient";
$kredyt = 0;

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $imie = $row['imie'];
    $kredyt = $row['kredyt'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['take_credit'])) {
    $credit_amount = floatval($_POST['credit_amount']);
    
    if ($credit_amount > 0) {
        $query = "UPDATE klienty SET kredyt = kredyt + $credit_amount WHERE username = '$username'";
        if ($connection->query($query)) {
            $kredyt += $credit_amount;
            $successMessage = "Przyznano kredyt w wysokości " . number_format($credit_amount, 2) . " PLN.";
        } else {
            $errorMessage = "Błąd przyznawania kredytu: " . $connection->error;
        }
    } else {
        $errorMessage = "Wprowadź poprawną kwotę kredytu!";
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Klienta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="klient.css">
</head>
<body>
    <?php
    $sessionExpiresAt = $_SESSION['last_activity'] + 900;
    $formattedTime = date("H:i:s", $sessionExpiresAt);
    ?>
    <div style="
        position: fixed;
        top: 50px;
        right: 20px;
        background-color: #e9f7ef;
        color: #155724;
        border: 1px solid #c3e6cb;
        padding: 10px 15px;
        border-radius: 10px;
        font-family: Arial, sans-serif;
        font-size: 14px;">
        Sesja wygaśnie o <strong><?= $formattedTime ?></strong>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function () {
        $('form').on('submit', function (e) {
            const amount = parseFloat($('input[name="credit_amount"]').val());

            if (isNaN(amount) || amount <= 0) {
                e.preventDefault();
                alert("Wprowadź poprawną kwotę kredytu!");
                return false;
            }

            if (!confirm("Czy na pewno chcesz wziąć kredyt w wysokości " + amount.toFixed(2) + " PLN?")) {
                e.preventDefault();
                return false;
            }
        });

        $('.text-success, .text-danger').hide().fadeIn(1000);

        $('input[name="credit_amount"]').on('input', function () {
            const val = $(this).val();
            if (val < 0) {
                $(this).css('border', '2px solid red');
            } else {
                $(this).css('border', '');
            }
        });
    });
    </script>

    <div class="container text-center mt-5">
        <h1>Panel Klienta</h1>
        <p>Witaj, <?= htmlspecialchars($imie); ?>!</p>
        <p>Twoje konto bankowe jest aktywne.</p>
        <p>Aktualny kredyt: <strong><?= number_format($kredyt, 2); ?> PLN</strong></p>

        <h2 class="mt-4">Weź Kredyt</h2>
        <?php if (isset($successMessage)) echo "<p class='text-success'>$successMessage</p>"; ?>
        <?php if (isset($errorMessage)) echo "<p class='text-danger'>$errorMessage</p>"; ?>
        <form method="post">
            <input type="number" step="0.01" name="credit_amount" class="credit-input mb-2" placeholder="Kwota kredytu" required>
            <button type="submit" name="take_credit" class="btn btn-primary">Weź Kredyt</button>
        </form>
        <a href="index.php?action=logout" class="btn btn-danger mt-3">Wyloguj się</a>
    </div>
</body>
</html>
