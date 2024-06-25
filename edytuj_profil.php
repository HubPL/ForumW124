<?php
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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = filter_input(INPUT_POST, 'login', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    $sql = "UPDATE uzytkownicy SET login = ?, email = ? WHERE id = ?";
    $params = [$login, $email, $user_id];

    if (!empty($password)) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE uzytkownicy SET login = ?, email = ?, haslo = ? WHERE id = ?";
        $params = [$login, $email, $passwordHash, $user_id];
    }

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $profile_picture_name = basename($_FILES['profile_picture']['name']);
        $profile_picture_path = $upload_dir . $profile_picture_name;

        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $profile_picture_path)) {
            $sql = "UPDATE uzytkownicy SET login = ?, email = ?, zdjecie = ? WHERE id = ?";
            $params = [$login, $email, $profile_picture_name, $user_id];

            if (!empty($password)) {
                $sql = "UPDATE uzytkownicy SET login = ?, email = ?, haslo = ?, zdjecie = ? WHERE id = ?";
                $params = [$login, $email, $passwordHash, $profile_picture_name, $user_id];
            }
        } else {
            error_log("Nie udało się przenieść pliku. Błąd: " . $_FILES['profile_picture']['error']);
        }
    }

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        header('Location: profil.php');
        exit;
    } catch (PDOException $e) {
        error_log("Błąd bazy danych: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <title>Edytuj Profil</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>

<body>
    <header>
        <h1>Edytuj Profil</h1>
        <nav>
            <a href="index.php">Strona główna</a>
            <a href="logout.php">Wyloguj</a>
        </nav>
    </header>
    <main>
        <form action="edytuj_profil.php" method="post" enctype="multipart/form-data">
            <label for="login">Login:</label>
            <input type="text" id="login" name="login" value="<?= htmlspecialchars($user['login']) ?>" required>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            <label for="password">Hasło:</label>
            <input type="password" id="password" name="password">
            <label for="profile_picture">Zdjęcie profilowe:</label>
            <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
            <button type="submit">Zapisz zmiany</button>
        </form>
    </main>
</body>

</html>