<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['user_typ_konta'] != 'administrator') {
    header('Location: index.php');
    exit;
}
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'ogloszenie' && isset($_POST['tresc_ogloszenia'])) {
            $tresc_ogloszenia = $_POST['tresc_ogloszenia'];
            try {
                $stmt = $pdo->prepare("INSERT INTO ogloszenia (tresc, data_publikacji) VALUES (?, NOW())");
                $stmt->execute([$tresc_ogloszenia]);
                echo "Ogłoszenie zostało dodane.";
            } catch (PDOException $e) {
                echo "Błąd przy dodawaniu ogłoszenia: " . $e->getMessage();
            }
        } elseif ($_POST['action'] === 'dzial' && isset($_POST['nazwa_dzialu'], $_POST['opis_dzialu'])) {
            $nazwa_dzialu = $_POST['nazwa_dzialu'];
            $opis_dzialu = $_POST['opis_dzialu'];
            try {
                $stmt = $pdo->prepare("INSERT INTO dzialy (nazwa, opis) VALUES (?, ?)");
                $stmt->execute([$nazwa_dzialu, $opis_dzialu]);
                echo "Dział został dodany.";
            } catch (PDOException $e) {
                echo "Błąd przy dodawaniu działu: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <title>Panel Administracyjny</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>

<body>
    <header>
        <h2>Panel Administracyjny</h2>
        <nav>
            <a href="index.php">Strona główna</a>
            <a href="logout.php">Wyloguj</a>
        </nav>
    </header>
    <main>
        <section id="add-announcement">
            <h3>Dodaj ogłoszenie</h3>
            <form action="admin_panel.php" method="post">
                <textarea name="tresc_ogloszenia" required></textarea>
                <input type="hidden" name="action" value="ogloszenie">
                <button type="submit">Dodaj ogłoszenie</button>
            </form>
        </section>
        <section id="add-section">
            <h3>Dodaj dział</h3>
            <form action="admin_panel.php" method="post">
                <label for="nazwa_dzialu">Nazwa:</label>
                <input type="text" id="nazwa_dzialu" name="nazwa_dzialu" required>
                <label for="opis_dzialu">Opis:</label>
                <textarea id="opis_dzialu" name="opis_dzialu" required></textarea>
                <input type="hidden" name="action" value="dzial">
                <button type="submit">Dodaj dział</button>
            </form>
        </section>
    </main>
</body>

</html>