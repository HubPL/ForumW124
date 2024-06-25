<?php
session_start();
require 'db_connect.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$topic_id = $_GET['id'];

try {
    $stmtTopic = $pdo->prepare("SELECT tematy.tytul, tematy.tresc, tematy.data_publikacji, tematy.zdjecie as zdjecie_tematu, uzytkownicy.login, uzytkownicy.zdjecie as zdjecie_uzytkownika
                                FROM tematy 
                                JOIN uzytkownicy ON tematy.id_uzytkownika = uzytkownicy.id 
                                WHERE tematy.id = ?");
    $stmtTopic->execute([$topic_id]);
    $topic = $stmtTopic->fetch();
    if (!$topic) {
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    error_log("Błąd bazy danych: " . $e->getMessage());
    header('Location: index.php');
    exit;
}

try {
    $stmtReplies = $pdo->prepare("SELECT odpowiedzi.id, odpowiedzi.tresc, odpowiedzi.data_publikacji, uzytkownicy.login, uzytkownicy.zdjecie as zdjecie_uzytkownika
                                  FROM odpowiedzi 
                                  JOIN uzytkownicy ON odpowiedzi.id_uzytkownika = uzytkownicy.id 
                                  WHERE odpowiedzi.id_tematu = ? 
                                  ORDER BY odpowiedzi.data_publikacji ASC");
    $stmtReplies->execute([$topic_id]);
    $replies = $stmtReplies->fetchAll();
} catch (PDOException $e) {
    error_log("Błąd bazy danych: " . $e->getMessage());
    $replies = [];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_SESSION['loggedin'])) {
        $replyContent = $_POST['content'];
        $user_id = $_SESSION['user_id'];
        try {
            $stmt = $pdo->prepare("INSERT INTO odpowiedzi (tresc, data_publikacji, id_uzytkownika, id_tematu) VALUES (?, NOW(), ?, ?)");
            $stmt->execute([$replyContent, $user_id, $topic_id]);
            header('Location: topic.php?id=' . $topic_id);
            exit;
        } catch (PDOException $e) {
            echo "Błąd przy dodawaniu odpowiedzi: " . $e->getMessage();
        }
    } else {
        $replyContent = $_POST['content'];
        $pseudo = $_POST['pseudo'];
        $default_photo = 'default.jpg';
        $default_email = 'guest@example.com';
        $temporary_password = password_hash('temporary_password', PASSWORD_DEFAULT);

        try {
            $stmtUser = $pdo->prepare("INSERT INTO uzytkownicy (login, zdjecie, email, haslo) VALUES (?, ?, ?, ?)");
            $stmtUser->execute([$pseudo, $default_photo, $default_email, $temporary_password]);
            $user_id = $pdo->lastInsertId();

            $stmt = $pdo->prepare("INSERT INTO odpowiedzi (tresc, data_publikacji, id_uzytkownika, id_tematu) VALUES (?, NOW(), ?, ?)");
            $stmt->execute([$replyContent, $user_id, $topic_id]);
            header('Location: topic.php?id=' . $topic_id);
            exit;
        } catch (PDOException $e) {
            echo "Błąd przy dodawaniu odpowiedzi: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <title>Temat: <?= htmlspecialchars($topic['tytul']) ?></title>
    <link rel="stylesheet" type="text/css" href="styles.css?v=1.0">
</head>

<body>
    <header>
        <h1><?= htmlspecialchars($topic['tytul']) ?></h1>
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
        <article>
            <img src="uploads/<?= htmlspecialchars($topic['zdjecie_uzytkownika']) ?>" alt="Zdjęcie profilowe"
                class="profile-pic-small">
            Autor: <?= htmlspecialchars($topic['login']) ?>, Data:
            <?= $topic['data_publikacji'] ?>
            <div><?= nl2br(htmlspecialchars($topic['tresc'])) ?></div>
            <?php if ($topic['zdjecie_tematu']): ?>
                <img class='topic-image' src="uploads/<?= htmlspecialchars($topic['zdjecie_tematu']) ?>"
                    alt="Zdjęcie tematu">

            <?php endif; ?>
        </article>

        <?php if (!empty($replies)): ?>
            <section class="replies">
                <h2>Odpowiedzi</h2>
                <ul>
                    <?php foreach ($replies as $reply): ?>
                        <li>
                            <img src="uploads/<?= htmlspecialchars($reply['zdjecie_uzytkownika']) ?>" alt="Zdjęcie profilowe"
                                class="profile-pic-small">Autor: <?= htmlspecialchars($reply['login']) ?>, Data:
                            <?= $reply['data_publikacji'] ?>
                            <p><?= htmlspecialchars($reply['tresc']) ?></p>
                            <?php if (isset($_SESSION['loggedin']) && $_SESSION['user_typ_konta'] == 'administrator'): ?>
                                <form action="edytuj_odpowiedz.php" method="post" style="display:inline;">
                                    <input type="hidden" name="odpowiedz_id" value="<?= $reply['id'] ?>">
                                    <button type="submit">Edytuj</button>
                                </form>
                                <form action="usun_odpowiedz.php" method="post" style="display:inline;">
                                    <input type="hidden" name="odpowiedz_id" value="<?= $reply['id'] ?>">
                                    <button type="submit">Usuń</button>
                                </form>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php else: ?>
            <p>Brak odpowiedzi w tym temacie.</p>
        <?php endif; ?>

        <section class="add-reply">
            <h2>Dodaj Odpowiedź</h2>
            <form action="topic.php?id=<?= htmlspecialchars($topic_id) ?>" method="post">
                <?php if (!isset($_SESSION['loggedin'])): ?>
                    <label for="pseudo">Pseudonim:</label>
                    <input type="text" id="pseudo" name="pseudo" required>
                <?php endif; ?>
                <label for="content">Treść:</label>
                <textarea id="content" name="content" required></textarea>
                <button type="submit">Dodaj Odpowiedź</button>
            </form>
        </section>
    </main>
</body>

</html>