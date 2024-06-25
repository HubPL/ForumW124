<?php
session_start();
require 'db_connect.php';

if (!isset($_POST['odpowiedz_id']) || $_SESSION['user_typ_konta'] != 'administrator') {
    header('Location: index.php');
    exit;
}

$odpowiedz_id = $_POST['odpowiedz_id'];

try {
    $stmt = $pdo->prepare("SELECT tresc FROM odpowiedzi WHERE id = ?");
    $stmt->execute([$odpowiedz_id]);
    $odpowiedz = $stmt->fetch();
    if (!$odpowiedz) {
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    echo "Błąd bazy danych: " . $e->getMessage();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['content'])) {
    $content = $_POST['content'];
    try {
        $stmt = $pdo->prepare("UPDATE odpowiedzi SET tresc = ? WHERE id = ?");
        $stmt->execute([$content, $odpowiedz_id]);
        header('Location: topic.php?id=' . $_POST['temat_id']);
        exit;
    } catch (PDOException $e) {
        echo "Błąd przy edytowaniu odpowiedzi: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <title>Edytuj Odpowiedź</title>
</head>

<body>
    <h1>Edytuj Odpowiedź</h1>
    <form action="edytuj_odpowiedz.php" method="post">
        <input type="hidden" name="odpowiedz_id" value="<?= htmlspecialchars($odpowiedz_id) ?>">
        <input type="hidden" name="temat_id" value="<?= htmlspecialchars($_POST['temat_id']) ?>">
        <label for="content">Treść:</label>
        <textarea id="content" name="content" required><?= htmlspecialchars($odpowiedz['tresc']) ?></textarea>
        <button type="submit">Zapisz Zmiany</button>
    </form>
    <form action="topic.php?id=<?= htmlspecialchars($_POST['temat_id']) ?>" method="post">
        <input type="submit" value="Anuluj" />
    </form>
</body>

</html>