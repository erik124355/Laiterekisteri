<?php
    require "db.php";
    session_start();

    //jos käyttäjä ei ole kirjautunut, siiretään se login.php
    if (!isset($_SESSION['KayttajaID'])) {
        header("Location:login.php");
        exit;
    }

    $kayttajaID = $_SESSION['KayttajaID'];

    $stmt = $pdo->prepare("
        SELECT v.VarausID, l.Nimi AS Laite, l.Varastohuone, l.Kaappi, l.Hylly, v.Varaus_alku, v.Varaus_loppu
        FROM varaukset v
        JOIN laitteet l ON v.LaiteID = l.LaiteID
        WHERE v.KayttajaID = ?
        ORDER BY v.Varaus_alku ASC
    ");
    $stmt->execute([$kayttajaID]);
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // PALAUTA VARAUS
if (isset($_GET['palauta'])) {
    $varausID = $_GET['palauta'];

    $stmt = $pdo->prepare("DELETE FROM varaukset WHERE VarausID = ? AND KayttajaID = ?");
    $stmt->execute([$varausID, $kayttajaID]);

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// päivitä varaus päättöpäivä
if (isset($_POST['paivita_loppu'])) {
    $varausID = $_POST['varaus_id'];
    $new_loppu = $_POST['new_loppu'];

    $stmt = $pdo->prepare("SELECT Varaus_alku FROM varaukset WHERE VarausID = ? AND KayttajaID = ?");
    $stmt->execute([$varausID, $kayttajaID]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && strtotime($new_loppu) > strtotime($row['Varaus_alku'])) {
        $stmt = $pdo->prepare("UPDATE varaukset SET Varaus_loppu = ? WHERE VarausID = ? AND KayttajaID = ?");
        $stmt->execute([$new_loppu, $varausID, $kayttajaID]);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $error_message = "Uuden päättymispäivän on oltava aloituspäivän jälkeen!";
    }
}
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laiterekisteri</title>
</head>
<link rel="stylesheet" href="style.css">
<body class="site-wrapper">
    <div id="mySidenav" class="sidenav" style="width: 250px;"> <div class="nav-img">
            <img src="assets/logo1.png" alt="Logo" width="300" height="100">
        </div>        
        <a href="#" onclick="showSection('omat-varaukset')">Omat varaukset</a>
        <a href="#" onclick="showSection('varaa-tasta')">Varaa täältä</a>
        <a href="logout.php">Kirjaudu ulos</a>
    </div>

    <main class="content-area">
        <div id="main-content" style="margin-left: 260px;">
            <section id="omat-varaukset">
                <h2>Omat varaukset</h2>
                
                <?php if (count($reservations) > 0): ?>
                    <table border="1" cellpadding="8" cellspacing="0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Laite</th>
                                <th>Varastohuone</th>
                                <th>Kaappi</th>
                                <th>Hylly</th>
                                <th>Varaus alkaa</th>
                                <th>Varaus päättyy</th>
                                <th>Toiminnot</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservations as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['VarausID']) ?></td>
                                <td><?= htmlspecialchars($r['Laite']) ?></td>
                                <td><?= htmlspecialchars($r['Varastohuone']) ?></td>
                                <td><?= htmlspecialchars($r['Kaappi']) ?></td>
                                <td><?= htmlspecialchars($r['Hylly']) ?></td>
                                <td><?= htmlspecialchars($r['Varaus_alku']) ?></td>
                                <td><?= htmlspecialchars($r['Varaus_loppu']) ?></td>
                                <td>
                                    <!-- Palauta button -->
                                    <a href="?palauta=<?= $r['VarausID'] ?>" onclick="return confirm('Haluatko varmasti palauttaa tämän varauksen?')">Palauta</a>
                                    <br><br>
                                    <!-- Update deadline form -->
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="varaus_id" value="<?= $r['VarausID'] ?>">
                                        <input type="datetime-local" name="new_loppu" value="<?= date('Y-m-d\TH:i', strtotime($r['Varaus_loppu'])) ?>" required>
                                        <button type="submit" name="paivita_loppu">Päivitä päättymispäivä</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Sinulla ei ole varauksia.</p>
                <?php endif; ?>
            </section>

            <section id="varaa-tasta" style="display:none;">
                <h2>Varaa täältä</h2>

                <?php
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['laiteID'], $_POST['varaus_alku'], $_POST['varaus_loppu'])) {
                    $laiteID = $_POST['laiteID'];
                    $varaus_alku = $_POST['varaus_alku'];
                    $varaus_loppu = $_POST['varaus_loppu'];

                    if (strtotime($varaus_alku) >= strtotime($varaus_loppu)) {
                        echo "<p style='color:red;'>Varaus alkaa ennen kuin loppuu. Korjaa tiedot!</p>";
                    } else {
                        // Tarkistetaan jos laite on varattu sille päivälle
                        $stmt = $pdo->prepare("
                            SELECT * FROM varaukset 
                            WHERE LaiteID = ? 
                            AND ((Varaus_alku <= ? AND Varaus_loppu >= ?) 
                                OR (Varaus_alku <= ? AND Varaus_loppu >= ?))
                        ");
                        $stmt->execute([$laiteID, $varaus_alku, $varaus_alku, $varaus_loppu, $varaus_loppu]);

                        if ($stmt->rowCount() > 0) {
                            //jos on varattu:
                            echo "<p style='color:red;'>Tämä laite on varattu tuolloin.</p>";
                        } else {
                            $stmt = $pdo->prepare("INSERT INTO varaukset (KayttajaID, LaiteID, Varaus_alku, Varaus_loppu) VALUES (?, ?, ?, ?)");
                            if ($stmt->execute([$kayttajaID, $laiteID, $varaus_alku, $varaus_loppu])) {
                                //jos ei ole varattu:
                                echo "<p style='color:green;'>Varaus onnistui!</p>";
                            } else {
                                echo "<p style='color:red;'>Varausta ei voitu tallentaa.</p>";
                            }
                        }
                    }
                }

                $devices = $pdo->query("SELECT LaiteID, Nimi, Varastohuone, Kaappi, Hylly FROM laitteet ORDER BY Nimi ASC")->fetchAll(PDO::FETCH_ASSOC);
                ?>

                <form method="POST">
                    <label for="filter">Suodata laitetta nimellä:</label><br>
                    <input type="text" id="filter" placeholder="Kirjoita laitenimi..."><br><br>

                    <label for="laiteID">Valitse laite:</label><br>
                    <select name="laiteID" id="laiteID" required>
                        <option value="">-- Valitse laite --</option>
                        <?php foreach ($devices as $d): ?>
                            <option value="<?= $d['LaiteID'] ?>" data-name="<?= htmlspecialchars(strtolower($d['Nimi'])) ?>">
                                <?= htmlspecialchars($d['Nimi']) ?> (Huone: <?= htmlspecialchars($d['Varastohuone']) ?>, Kaappi: <?= htmlspecialchars($d['Kaappi']) ?> Hylly: <?= htmlspecialchars($d['Hylly']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select><br><br>

                    <label for="varaus_alku">Varaus alkaa:</label><br>
                    <input type="datetime-local" name="varaus_alku" id="varaus_alku" required><br><br>

                    <label for="varaus_loppu">Varaus päättyy:</label><br>
                    <input type="datetime-local" name="varaus_loppu" id="varaus_loppu" required><br><br>

                    <button type="submit">Varaa</button>
                </form>
            </section>
        </div>
    </main>
<script>
    function showSection(sectionId) {
        // Piilottaa kaikki sisällöt
        document.getElementById('omat-varaukset').style.display = 'none';
        document.getElementById('varaa-tasta').style.display = 'none';

        // Näyttää valittu sisältö
        document.getElementById(sectionId).style.display = 'block';
        
        // Optional: Update the page heading or state
        console.log("Switched to: " + sectionId);
    }

    //suodatus varaukseen laitteen nimellä
    document.getElementById('filter').addEventListener('input', function() {
        const filterValue = this.value.toLowerCase();
        const options = document.getElementById('laiteID').options;

        for (let i = 0; i < options.length; i++) {
            const option = options[i];
            const name = option.getAttribute('data-name') || '';
            if (name.includes(filterValue) || option.value === "") {
                option.style.display = '';
            } else {
                option.style.display = 'none';
            }
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
    // Create hamburger button if it doesn't exist
    if (!document.querySelector('.menu-toggle')) {
        const menuToggle = document.createElement('button');
        menuToggle.className = 'menu-toggle';
        menuToggle.innerHTML = '☰';
        menuToggle.setAttribute('aria-label', 'Toggle menu');
        document.body.insertBefore(menuToggle, document.body.firstChild);
    }
    
    // Create overlay if it doesn't exist
    if (!document.querySelector('.sidebar-overlay')) {
        const overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);
    }
    
    const sidenav = document.getElementById('mySidenav');
    const menuToggle = document.querySelector('.menu-toggle');
    const overlay = document.querySelector('.sidebar-overlay');
    
    // Toggle menu
    menuToggle.addEventListener('click', function() {
        sidenav.classList.toggle('open');
        overlay.classList.toggle('active');
    });
    
    // Close menu when clicking overlay
    overlay.addEventListener('click', function() {
        sidenav.classList.remove('open');
        overlay.classList.remove('active');
    });
    
    // Close menu when clicking a link (mobile)
    const sidenavLinks = sidenav.querySelectorAll('a');
    sidenavLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                sidenav.classList.remove('open');
                overlay.classList.remove('active');
            }
        });
    });
});

</script>
<footer class="site-footer">
    <?php
        include "footer.php";
    ?>
</footer>
</body>
</html>