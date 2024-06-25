<?php
session_start();
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = filter_input(INPUT_POST, 'login', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $haslo = $_POST['haslo'];

    $hashedPassword = password_hash($haslo, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("SELECT * FROM uzytkownicy WHERE login = :login OR email = :email");
        $stmt->execute(['login' => $login, 'email' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            $error = "Użytkownik o podanym loginie lub emailu już istnieje.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO uzytkownicy (login, email, haslo, typ_konta) VALUES (:login, :email, :haslo, 'uzytkownik')");
            $stmt->execute(['login' => $login, 'email' => $email, 'haslo' => $hashedPassword]);

            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['user_typ_konta'] = 'uzytkownik';
            header('Location: index.php');
            exit;
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
    <title>Rejestracja</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>

<body>
    <header>
        <h1>Rejestracja</h1>
        <nav>
            <a href="index.php">Strona główna</a>
            <a href="logout.php">Wyloguj</a>
        </nav>
    </header>
    <main>
        <form action="rejestracja.php" method="post">
            <label for="login">Login:</label>
            <input type="text" id="login" name="login" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="haslo">Hasło:</label>
            <input type="password" id="haslo" name="haslo" required>

            <button type="submit" name="register">Zarejestruj się</button>
        </form>
        <?php if (isset($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
    </main>
</body>

</html>