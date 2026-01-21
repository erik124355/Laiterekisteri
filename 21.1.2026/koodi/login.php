<?php
    session_start();
    //jos käyttäjä on jo kirjautunut, siiretään se index.php
    if (isset($_SESSION['KayttajaID'])) {
        header("Location:index.php");
        exit;
    }

    require "db.php";

    if (isset($_POST['login'])) {
        $tunnus = $_POST['kayttajatunnus'];
        $salasana = $_POST['salasana'];

        $stmt = $pdo->prepare("SELECT * FROM kayttajat WHERE Kayttajatunnus = ?");
        $stmt->execute([$tunnus]);
        $user = $stmt->fetch();

        if ($user && password_verify($salasana, $user['Salasana'])) {
            $_SESSION['KayttajaID'] = $user['KayttajaID'];
            $_SESSION['Kayttajatunnus'] = $user['Kayttajatunnus'];
            $_SESSION['Rooli'] = $user['Rooli'];

            header("Location:index.php");
            exit;
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

        <form method="post" action="login.php">
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
        <p>Olet Tieto- ja viestintätekniikan osaston Laiterekisterin ja varausjärjestelmän etusivulla. Ole hyvä ja kirjaudu sisään järjestelmään.<p>
    </div>

<script>
</script>
</body>
</html>