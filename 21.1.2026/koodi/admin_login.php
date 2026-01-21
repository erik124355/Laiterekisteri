<?php
    session_start();


    require "db.php";

    if (isset($_POST['login'])) {
        $tunnus = $_POST['kayttajatunnus'];
        $salasana = $_POST['salasana'];

            $stmt = $pdo->prepare("SELECT * FROM kayttajat WHERE Kayttajatunnus = ?");
            $stmt->execute([$tunnus]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($salasana, $user['Salasana'])) {
                if ($user['Rooli'] === 'Admin') { // tarkistetaan jos rooli on admin
                    $_SESSION['KayttajaID'] = $user['KayttajaID'];
                    $_SESSION['Kayttajatunnus'] = $user['Kayttajatunnus'];
                    $_SESSION['Rooli'] = $user['Rooli'];

                    header("Location:admin.php");
                    exit;
                } else {
                    header("Location:login.php?error=2");
                    exit;
                }
            } else {
                header("Location:login.php?error=1");
                exit;
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
<body>
    <!--Kirjautumis form-->
    <div class="login-container">
        <h2>Kirjaudu sisään</h2>

        <form method="post" action="admin_login.php">
            <label>Käyttäjätunnus</label>
            <input type="text" name="kayttajatunnus" required>

            <label>Salasana</label>
            <input type="password" name="salasana" required>

            <button type="submit" name="login">Kirjaudu</button>
        </form>
    <!--Nääytetään virhe viesti jos jokin menee pieleen-->
        <?php
        if (isset($_GET['error'])) {
            echo "<p class='error'>Virheellinen tunnus tai salasana</p>";
        }
        ?>
        <p>Admin paneeli.<p>
    </div>

<script>
</script>
</body>
</html>