<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$database = "bank";

$connection = new mysqli($servername, $username, $password, $database);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_client'])) {
    $imie = $_POST["imie"];
    $nazwisko = $_POST["nazwisko"];
    $telefon = $_POST["telefon"];
    $email = $_POST["email"];
    $client_username = $_POST["client_username"];
    $client_password = $_POST["client_password"];

    if (!empty($imie) && !empty($nazwisko) && !empty($telefon) && !empty($email) && !empty($client_username) && !empty($client_password)) {
        $query = "INSERT INTO klienty (username, password, email, telefon, imie, nazwisko, kredyt) 
                  VALUES ('$client_username', '$client_password', '$email', '$telefon', '$imie', '$nazwisko', 0)";
        $result = $connection->query($query);

        if ($result) {
            $successMessage = "Nowy klient został dodany!";
        } else {
            $errorMessage = "Błąd zapytania: " . $connection->error;
        }
    } else {
        $errorMessage = "Wszystkie pola są wymagane!";
    }
}

$query = "SELECT * FROM klienty";
$clients_result = $connection->query($query);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admina</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.querySelector('form');

        const modal = document.createElement('div');
        modal.style.position = 'fixed';
        modal.style.top = 0;
        modal.style.left = 0;
        modal.style.width = '100%';
        modal.style.height = '100%';
        modal.style.background = 'rgba(0,0,0,0.6)';
        modal.style.display = 'none';
        modal.style.justifyContent = 'center';
        modal.style.alignItems = 'center';
        modal.style.zIndex = 9999;

        modal.innerHTML = `
            <div style="background:#fff; padding:20px; border-radius:10px; max-width:90%; text-align:center">
                <p id="confirm-text" style="font-size:18px; margin-bottom:20px;"></p>
                <button id="confirm-yes" style="margin-right:10px; padding:8px 16px;">Tak</button>
                <button id="confirm-no" style="padding:8px 16px;">Nie</button>
            </div>
        `;
        document.body.appendChild(modal);

        const showModal = (message, callback) => {
            document.getElementById('confirm-text').textContent = message;
            modal.style.display = 'flex';
            document.getElementById('confirm-yes').onclick = () => {
                modal.style.display = 'none';
                callback(true);
            };
            document.getElementById('confirm-no').onclick = () => {
                modal.style.display = 'none';
                callback(false);
            };
        };

        form.addEventListener('submit', (e) => {
            e.preventDefault();

            const imie = form.imie.value.trim();
            const nazwisko = form.nazwisko.value.trim();
            const email = form.email.value.trim();
            const username = form.client_username.value.trim();
            const password = form.client_password.value.trim();

            if (!imie || !nazwisko || !email || !username || !password || !email.includes('@') || !email.includes('.')) {
                alert("Wprowadź poprawne dane!");
                return;
            }

            showModal(`Dodać klienta ${imie} ${nazwisko}?`, (confirmed) => {
                if (confirmed) {
                    form.submit();
                }
            });
        });
    });
    </script>

    <div class="container mt-5">
        <div class="admin-panel">
            <h1>Panel Admina</h1>
            <p>Witaj, <?= htmlspecialchars($_SESSION['username']); ?>!</p>
            <a href="index.php?action=logout" class="btn btn-danger mb-3">Wyloguj się</a>

            <h2>Dodaj Klienta</h2>
            <?php if (isset($successMessage)) echo "<p class='text-success'>$successMessage</p>"; ?>
            <?php if (isset($errorMessage)) echo "<p class='text-danger'>$errorMessage</p>"; ?>
            
            <form method="post">
                <input type="hidden" name="add_client" value="1">
                <input type="text" name="imie" class="form-control mb-2" placeholder="Imię" required>
                <input type="text" name="nazwisko" class="form-control mb-2" placeholder="Nazwisko" required>
                <input type="text" name="telefon" class="form-control mb-2" placeholder="Telefon" required>
                <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
                <input type="text" name="client_username" class="form-control mb-2" placeholder="Nazwa użytkownika" required>
                <input type="password" name="client_password" class="form-control mb-2" placeholder="Hasło" required>
                <button type="submit" class="btn btn-primary">Dodaj Klienta</button>
            </form>

            <h2 class="mt-4">Lista Klientów</h2>
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>Imię</th>
                        <th>Nazwisko</th>
                        <th>Telefon</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th>Kredyt</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($clients_result->num_rows > 0) {
                        while ($row = $clients_result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['imie']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['nazwisko']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['telefon']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                            echo "<td>" . number_format($row['kredyt'], 2) . " PLN</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>Brak klientów w systemie</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
