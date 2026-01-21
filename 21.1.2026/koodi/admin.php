<?php
    require "db.php";
    session_start();

    //jos käyttäjä ei ole kirjautunut, siiretään se login.php
    if (!isset($_SESSION['KayttajaID'])) {
        header("Location:admin_login.php");
        exit;
    }

    $kayttajaID = $_SESSION['KayttajaID'];

    // LAITTEEN LISÄÄMINEN
    if (isset($_POST['lisaa_laite'])) {
        $nimi = $_POST['nimi'];
        $ryhma = $_POST['ryhma'];
        $huone = $_POST['huone'];
        $kaappi = $_POST['kaappi'];
        $hylly = $_POST['hylly'];

        $sql = "INSERT INTO laitteet (Nimi, Laiteryma, Varastohuone, Kaappi, Hylly) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nimi, $ryhma, $huone, $kaappi, $hylly]);
        header("Location: admin.php");
    }

    // LAITTEEN MUOKKAUS
    if (isset($_POST['paivita_laite'])) {
        $id = $_POST['laite_id'];
        $nimi = $_POST['nimi'];
        $ryhma = $_POST['ryhma'];
        $huone = $_POST['huone'];
        $kaappi = $_POST['kaappi'];
        $hylly = $_POST['hylly'];

        $sql = "UPDATE laitteet SET Nimi=?, Laiteryma=?, Varastohuone=?, Kaappi=?, Hylly=? WHERE LaiteID=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nimi, $ryhma, $huone, $kaappi, $hylly, $id]);
        header("Location: admin.php");
    }

    // LAITTEEN POISTAMINEN
    if (isset($_GET['poista_laite'])) {
        $id = $_GET['poista_laite'];
        $checkSql = "SELECT COUNT(*) FROM varaukset WHERE LaiteID = ?";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([$id]);
        if ($checkStmt->fetchColumn() > 0) {
            echo "<script>alert('Laitetta ei voi poistaa, koska se on varattu!'); window.location.href='admin.php';</script>";
        } else {
            $sql = "DELETE FROM laitteet WHERE LaiteID = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            header("Location: admin.php");
        }
    }

    // KÄYTTÄJÄN MUOKKAUS
    if (isset($_POST['paivita_kayttaja'])) {
        $id = $_POST['kayttaja_id'];
        $kayttajanimi = $_POST['kayttajanimi'];
        $salasana = $_POST['salasana'];
        $rooli = $_POST['rooli'];

        if (!empty($salasana)) {
            $hashedPassword = password_hash($salasana, PASSWORD_DEFAULT);
            $sql = "UPDATE kayttajat SET Kayttajatunnus=?, Salasana=?, Rooli=? WHERE KayttajaID=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$kayttajanimi, $hashedPassword, $rooli, $id]);
        } else {
            $sql = "UPDATE kayttajat SET Kayttajatunnus=?, Rooli=? WHERE KayttajaID=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$kayttajanimi, $rooli, $id]);
        }

        header("Location: admin.php");
        exit;
    }

    // KÄYTTÄJÄN POISTAMINEN
    if (isset($_GET['poista_kayttaja'])) {
        $id = $_GET['poista_kayttaja'];
        
        // Estetään itseään poistaminen vahingossa
        if ($id == $_SESSION['KayttajaID']) {
            echo "<script>alert('Et voi poistaa omaa tunnustasi!'); window.location.href='admin.php';</script>";
        } else {
            $sql = "DELETE FROM kayttajat WHERE KayttajaID = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            header("Location: admin.php");
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
        <!-- navigointi sivupalkissa-->  
        <a href="#" onclick="showSection('laitteet')">Laitteet</a>
        <a href="#" onclick="showSection('kayttajat')">Kayttajat</a>
        <a href="admin_logout.php">Kirjaudu ulos</a>
    </div>

    <main class="content-area">
        <div id="main-content" style="margin-left: 260px;">
            <section id="laitteet"><!--formi jolla lisätään laitteita-->
                <h2>Laiterekisteri</h2>
                
                <div style="margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; background: #f9f9f9;">
                    <h3>Lisää uusi laite</h3>
                    <form action="admin.php" method="POST">
                        <input type="text" name="nimi" placeholder="Laitteen nimi" required>
                        
                        <select name="ryhma" required>
                            <option value="">Valitse tyyppi</option>
                            <option value="Pöytätietokoneet">Pöytätietokoneet</option>
                            <option value="Kannettavat_tietokoneet">Kannettavat tietokoneet</option>
                            <option value="Tabletit">Tabletit</option>
                            <option value="Näytöt">Näytöt</option>
                            <option value="Näppäimistöt">Näppäimistöt</option>
                            <option value="Hiiret">Hiiret</option>
                            <option value="Webkamerat">Webkamerat</option>
                            <option value="Kuulokkeet">Kuulokkeet</option>
                            <option value="Kaiuttimet">Kaiuttimet</option>
                        </select>

                        <input type="text" name="kaappi" placeholder="Huone (esim. A2TS16)" required>
                        <input type="text" name="kaappi" placeholder="Kaappi (esim. 16.1)" required>
                        <input type="text" name="hylly" placeholder="Hylly (esim. 16.1.1)" required>
                        
                        <button type="submit" name="lisaa_laite">Lisää laite</button>
                    </form>
                </div>

                <table border="1" style="width:100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: #eee;">
                            <th>ID</th>
                            <th>Nimi</th>
                            <th>Tyyppi</th>
                            <th>Sijainti (Huone/Kaappi/Hylly)</th>
                            <th>Toiminnot</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM laitteet";
                        $result = $pdo->query($sql);
                        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                            echo "<tr>";
                            echo "<td>" . $row['LaiteID'] . "</td>";
                            echo "<td>" . htmlspecialchars($row['Nimi']) . "</td>";
                            echo "<td>" . $row['Laiteryma'] . "</td>";
                            echo "<td>" . $row['Varastohuone'] . " / " . $row['Kaappi'] . " / " . $row['Hylly'] . "</td>";
                            echo "<td>
                                    <a href='admin.php?poista_laite=" . $row['LaiteID'] . "' onclick='return confirm(\"Haluatko varmasti poistaa?\")'>Poista</a>
                                </td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </section>

            <section id="kayttajat" style="display:none;">
                <h2>Käyttäjien hallinta</h2>

                <div id="kayttaja-muokkaus-container" style="display:none; margin-bottom: 20px; padding: 15px; border: 1px solid #007bff; background: #e7f1ff;">
                    <h3>Muokkaa käyttäjää</h3>
                    <form action="admin.php" method="POST">
                        <input type="hidden" name="kayttaja_id" id="edit-k-id">
                        <input type="text" name="kayttajanimi" id="edit-k-kayttajanimi" placeholder="Käyttäjätunnus" required>
                        <input type="password" name="salasana" id="edit-k-salasana" placeholder="Uusi salasana (jätä tyhjäksi, jos ei vaihdeta)">
                        
                        <select name="rooli" id="edit-k-rooli" required>
                            <option value="Opettaja">Opettaja</option>
                            <option value="Admin">Admin</option>
                        </select>
                        
                        <button type="submit" name="paivita_kayttaja">Tallenna muutokset</button>
                        <button type="button" onclick="document.getElementById('kayttaja-muokkaus-container').style.display='none'">Peruuta</button>
                    </form>
                </div>

                <table border="1" style="width:100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: #eee;">
                            <th>ID</th>
                            <th>Käyttäjätunnus</th>
                            <th>Rooli</th>
                            <th>Toiminnot</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql_k = "SELECT KayttajaID, Kayttajatunnus, Rooli FROM kayttajat"; // Ei haeta salasanaa turhaan
                        $res_k = $pdo->query($sql_k);
                        while ($k_row = $res_k->fetch(PDO::FETCH_ASSOC)) {
                            echo "<tr>";
                            echo "<td>" . $k_row['KayttajaID'] . "</td>";
                            echo "<td>" . htmlspecialchars($k_row['Kayttajatunnus']) . "</td>";
                            echo "<td>" . $k_row['Rooli'] . "</td>";
                            echo "<td>
                                    <button onclick='muokkaaKayttajaa(" . json_encode($k_row) . ")'>Muokkaa</button>
                                    <a href='admin.php?poista_kayttaja=" . $k_row['KayttajaID'] . "' 
                                    onclick='return confirm(\"Haluatko varmasti poistaa tämän käyttäjän?\")' 
                                    style='color:red; margin-left:10px;'>Poista</a>
                                </td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </section>
        </div>
    </main>
<script>
    function showSection(sectionId) {
        // Piilottaa kaikki sisällöt
        document.getElementById('laitteet').style.display = 'none';
        document.getElementById('kayttajat').style.display = 'none';

        // Näyttää valittu sisältö
        document.getElementById(sectionId).style.display = 'block';
        
        // Optional: Update the page heading or state
        console.log("Switched to: " + sectionId);
    }

    function muokkaaKayttajaa(kayttaja) {
        const container = document.getElementById('kayttaja-muokkaus-container');
        container.style.display = 'block';

        document.getElementById('edit-k-id').value = kayttaja.KayttajaID;
        document.getElementById('edit-k-kayttajanimi').value = kayttaja.Kayttajatunnus;
        document.getElementById('edit-k-salasana').value = ''; // never fill hashed password
        document.getElementById('edit-k-rooli').value = kayttaja.Rooli;

        container.scrollIntoView({ behavior: 'smooth' });
    }

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