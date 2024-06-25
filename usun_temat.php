<?php
session_start();
require 'db_connect.php';

if (!isset($_POST['temat_id']) || $_SESSION['user_typ_konta'] != 'administrator') {
    header('Location: index.php');
    exit;
}

$temat_id = $_POST['temat_id'];

try {
    // Najpierw usuń wszystkie odpowiedzi powiązane z tematem
    $stmt = $pdo->prepare("DELETE FROM odpowiedzi WHERE id_tematu = ?");
    $stmt->execute([$temat_id]);

    // Następnie usuń temat
    $stmt = $pdo->prepare("DELETE FROM tematy WHERE id = ?");
    $stmt->execute([$temat_id]);

    header('Location: index.php');
    exit;
} catch (PDOException $e) {
    echo "Błąd przy usuwaniu tematu: " . $e->getMessage();
    exit;
}
?>