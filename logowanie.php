<?php
session_start();
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = filter_input(INPUT_POST, 'login', FILTER_SANITIZE_STRING);
    $haslo = $_POST['haslo'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM uzytkownicy WHERE login = :login");
        $stmt->execute(['login' => $login]);
        $user = $stmt->fetch();

        if ($user && password_verify($haslo, $user['haslo'])) {
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_typ_konta'] = $user['typ_konta'];
            header('Location: index.php');
            exit;
        } else {
            $error = "Nieprawidłowy login lub hasło.";
        }
    } catch (PDOException $e) {
        error_log("Błąd bazy danych: " . $e->getMessage());
        $error = "Wystąpił błąd. Spróbuj ponownie później.";
    }
}
?>

<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <title>Logowanie</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>

<body>
    <header>
        <h1>Logowanie</h1>
        <nav>
            <a href="index.php">Strona główna</a>
            <a href="logout.php">Wyloguj</a>
        </nav>
    </header>
    <main>
        <form action="logowanie.php" method="post">
            <label for="login">Login:</label>
            <input type="text" id="login" name="login" required>

            <label for="haslo">Hasło:</label>
            <input type="password" id="haslo" name="haslo" required>

            <button type="submit">Zaloguj się</button>
        </form>
        <?php if (isset($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
    </main>
</body>

</html>