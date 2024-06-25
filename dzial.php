<?php
session_start();
require 'db_connect.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$dzial_id = $_GET['id'];

try {
    $stmtDzial = $pdo->prepare("SELECT nazwa, opis FROM dzialy WHERE id = ?");
    $stmtDzial->execute([$dzial_id]);
    $dzial = $stmtDzial->fetch();
    if (!$dzial) {
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    error_log("Błąd bazy danych: " . $e->getMessage());
    header('Location: index.php');
    exit;
}

try {
    $stmtTematy = $pdo->prepare("SELECT t.id, t.tytul, t.data_publikacji, t.zdjecie as zdjecie_tematu, u.zdjecie as zdjecie_uzytkownika FROM tematy t 
    JOIN uzytkownicy u ON t.id_uzytkownika = u.id 
    WHERE t.id_dzialu = ? 
    ORDER BY t.data_publikacji DESC");

    $stmtTematy->execute([$dzial_id]);
    $tematy = $stmtTematy->fetchAll();
} catch (PDOException $e) {
    error_log("Błąd bazy danych: " . $e->getMessage());
    $tematy = [];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['loggedin'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $user_id = $_SESSION['user_id'];
    $image_name = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $image_name = basename($_FILES['image']['name']);
        $image_path = $upload_dir . $image_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
            echo "Plik został przesłany.";
        } else {
            echo "Nie udało się przesłać pliku.";
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO tematy (tytul, tresc, data_publikacji, id_uzytkownika, id_dzialu, zdjecie) VALUES (?, ?, NOW(), ?, ?, ?)");
        $stmt->execute([$title, $content, $user_id, $dzial_id, $image_name]);
        header('Location: dzial.php?id=' . $dzial_id);
        exit;
    } catch (PDOException $e) {
        echo "Błąd przy dodawaniu tematu: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <title>Dział: <?= htmlspecialchars($dzial['nazwa']) ?></title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>

<body>
    <header>
        <h1>Dział: <?= htmlspecialchars($dzial['nazwa']) ?></h1>
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
        <p><?= htmlspecialchars($dzial['opis']) ?></p>
        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']): ?>
            <section>
                <h2>Dodaj Temat</h2>
                <form action="dzial.php?id=<?= htmlspecialchars($dzial_id) ?>" method="post" enctype="multipart/form-data">
                    <label for="title">Tytuł:</label>
                    <input type="text" id="title" name="title" required>
                    <label for="content">Treść:</label>
                    <textarea id="content" name="content" required></textarea>
                    <label for="image">Zdjęcie:</label>
                    <input type="file" id="image" name="image">
                    <button type="submit">Dodaj Temat</button>
                </form>
            </section>
        <?php endif; ?>

        <?php if (!empty($tematy)): ?>
            <ul class="topic-list">
                <?php foreach ($tematy as $temat): ?>
                    <li>
                        <img src="uploads/<?= htmlspecialchars($temat['zdjecie_uzytkownika']) ?>" alt="Zdjęcie profilowe"
                            class="profile-pic-small">
                        <a href="topic.php?id=<?= $temat['id'] ?>"><?= htmlspecialchars($temat['tytul']) ?></a> -
                        <?= htmlspecialchars($temat['data_publikacji']) ?>
                        <?php if (isset($_SESSION['loggedin']) && $_SESSION['user_typ_konta'] == 'administrator'): ?>
                            <form action="edytuj_temat.php" method="post" style="display:inline;">
                                <input type="hidden" name="temat_id" value="<?= $temat['id'] ?>">
                                <button type="submit">Edytuj</button>
                            </form>
                            <form action="usun_temat.php" method="post" style="display:inline;">
                                <input type="hidden" name="temat_id" value="<?= $temat['id'] ?>">
                                <button type="submit">Usuń</button>
                            </form>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Brak tematów w tym dziale.</p>
        <?php endif; ?>
    </main>
</body>

</html>