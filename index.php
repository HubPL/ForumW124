<?php
session_start();

if (isset($_SESSION['username'])) {
    $cookie_name = "username";
    $cookie_value = $_SESSION['username'];
    setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/");
}
?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <title>Forum Mercedesa W124</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>

<body>
    <?php
    session_start();
    require 'db_connect.php';

    try {
        $stmtAnnouncements = $pdo->query("SELECT * FROM ogloszenia ORDER BY data_publikacji DESC LIMIT 3");
        $announcements = $stmtAnnouncements->fetchAll();
    } catch (PDOException $e) {
        error_log("Błąd bazy danych przy pobieraniu ogłoszeń: " . $e->getMessage());
        $announcements = [];
    }

    try {
        $stmtSections = $pdo->query("SELECT d.id, d.nazwa, d.opis, t.id AS temat_id, t.tytul, t.data_publikacji
                                     FROM dzialy d
                                     LEFT JOIN tematy t ON d.id = t.id_dzialu
                                     ORDER BY d.nazwa ASC, t.data_publikacji DESC");
        $sections = [];



        while ($row = $stmtSections->fetch()) {
            if (!isset($sections[$row['id']])) {
                $sections[$row['id']] = [
                    'id' => $row['id'],
                    'nazwa' => $row['nazwa'],
                    'opis' => $row['opis'],
                    'tematy' => [],
                ];
            }
            if ($row['temat_id']) {
                $sections[$row['id']]['tematy'][] = [
                    'id' => $row['temat_id'],
                    'tytul' => $row['tytul'],
                    'data_publikacji' => $row['data_publikacji']
                ];
            }
        }
    } catch (PDOException $e) {
        error_log("Błąd bazy danych: " . $e->getMessage());
    }
    ?>

    <header>
        <h1>Forum Mercedesa W124</h1>
        <nav>
            <a href="index.php">Strona główna</a>
            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']): ?>
                <a href="profil.php">Panel użytkownika</a>
                <a href="logout.php">Wyloguj</a>
                <?php if ($_SESSION['user_typ_konta'] == 'administrator'): ?>
                    <a href="admin_panel.php">Panel Administracyjny</a>
                <?php endif; ?>
            <?php else: ?>
                <a href="logowanie.php">Logowanie</a>
                <a href="rejestracja.php">Rejestracja</a>
            <?php endif; ?>
        </nav>
    </header>

    <main>
        <section id="announcements">
            <h2>Ostatnie ogłoszenia</h2>
            <?php if (!empty($announcements)): ?>
                <?php foreach ($announcements as $ogloszenie): ?>
                    <div class='announcement'>
                        <div class='announcement-content'><?= nl2br(htmlspecialchars($ogloszenie['tresc'])) ?></div>
                        <p class='announcement-date'>Data publikacji: <?= $ogloszenie['data_publikacji'] ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Brak ogłoszeń.</p>
            <?php endif; ?>
        </section>

        <section id="sections">
            <h2>Działy</h2>
            <?php foreach ($sections as $dzial): ?>
                <div class='section'>
                    <h3><a href="dzial.php?id=<?= $dzial['id'] ?>"><?= htmlspecialchars($dzial['nazwa']) ?></a></h3>
                    <p><?= htmlspecialchars($dzial['opis']) ?></p>
                    <?php if (!empty($dzial['tematy'])): ?>
                        <ul>
                            <?php foreach (array_slice($dzial['tematy'], 0, 3) as $temat): ?>
                                <li><a href="topic.php?id=<?= $temat['id'] ?>"><?= htmlspecialchars($temat['tytul']) ?></a> -
                                    <?= $temat['data_publikacji'] ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>Brak tematów w tym dziale.</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </section>
    </main>
</body>

</html>