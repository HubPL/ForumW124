<?php
session_start();
require 'db_connect.php';

if (!isset($_POST['temat_id']) || $_SESSION['user_typ_konta'] != 'administrator') {
    header('Location: index.php');
    exit;
}

$temat_id = $_POST['temat_id'];

try {
    $stmt = $pdo->prepare("SELECT tytul, tresc FROM tematy WHERE id = ?");
    $stmt->execute([$temat_id]);
    $temat = $stmt->fetch();
    if (!$temat) {
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    echo "Błąd bazy danych: " . $e->getMessage();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['title']) && isset($_POST['content'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    try {
        $stmt = $pdo->prepare("UPDATE tematy SET tytul = ?, tresc = ? WHERE id = ?");
        $stmt->execute([$title, $content, $temat_id]);
        header('Location: topic.php?id=' . $temat_id);
        exit;
    } catch (PDOException $e) {
        echo "Błąd przy edytowaniu tematu: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <title>Edytuj Temat</title>
</head>

<body>
    <h1>Edytuj Temat</h1>
    <form action="edytuj_temat.php" method="post">
        <input type="hidden" name="temat_id" value="<?= htmlspecialchars($temat_id) ?>">
        <label for="title">Tytuł:</label>
        <input type="text" id="title" name="title" value="<?= htmlspecialchars($temat['tytul']) ?>" required>
        <label for="content">Treść:</label>
        <textarea id="content" name="content" required><?= htmlspecialchars($temat['tresc']) ?></textarea>
        <button type="submit">Zapisz Zmiany</button>
    </form>
    <form action="topic.php?id=<?= htmlspecialchars($temat_id) ?>" method="post">
        <input type="submit" value="Anuluj" />
    </form>
</body>

</html>
