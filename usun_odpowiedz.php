<?php
session_start();
require 'db_connect.php';

if (!isset($_POST['odpowiedz_id']) || $_SESSION['user_typ_konta'] != 'administrator') {
    header('Location: index.php');
    exit;
}

$odpowiedz_id = $_POST['odpowiedz_id'];

try {
    $stmt = $pdo->prepare("DELETE FROM odpowiedzi WHERE id = ?");
    $stmt->execute([$odpowiedz_id]);
    header('Location: topic.php?id=' . $_POST['temat_id']);
    exit;
} catch (PDOException $e) {
    echo "Błąd przy usuwaniu odpowiedzi: " . $e->getMessage();
    exit;
}
?>