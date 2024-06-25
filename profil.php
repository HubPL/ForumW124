<?php
// profil.php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['loggedin'])) {
    header('Location: logowanie.php');
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT login, email, zdjecie FROM uzytkownicy WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("Użytkownik nie został znaleziony.");
    }
} catch (Exception $e) {
    error_log("Błąd bazy danych: " . $e->getMessage());
    header('Location: error_page.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <title>Profil użytkownika</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>

<body>
    <header>
        <h1>Profil użytkownika</h1>
        <nav>
            <a href="index.php">Strona główna</a>
            <?php if (isset($_SESSION['loggedin'])): ?>
                <a href="logout.php">Wyloguj</a>
            <?php else: ?>
                <a href="logowanie.php">Zaloguj</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>
        <div class="profile">
            <?php if ($user['zdjecie']): ?>
                <img src="uploads/<?= htmlspecialchars($user['zdjecie']) ?>" alt="Zdjęcie profilowe" class="profile-pic">
            <?php else: ?>
                <img src="uploads/default.jpg" alt="Domyślne zdjęcie profilowe" class="profile-pic">
            <?php endif; ?>
            <p>Login: <?= htmlspecialchars($user['login']) ?></p>
            <p>Email: <?= htmlspecialchars($user['email']) ?></p>
            <a href="edytuj_profil.php">Edytuj profil</a>
        </div>
    </main>
</body>

</html>