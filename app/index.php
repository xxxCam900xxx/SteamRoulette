<?php
error_reporting(E_ALL & ~E_DEPRECATED);
require 'openid.php';
session_start();

$openid = new LightOpenID('steam.xrayzu.com'); // deine Domain

if (!$openid->mode) {
    if (isset($_GET['login'])) {
        $openid->identity = 'https://steamcommunity.com/openid';
        header('Location: ' . $openid->authUrl());
        exit;
    }
} elseif ($openid->mode == 'cancel') {
    echo "Login abgebrochen.";
} else {
    if ($openid->validate()) {
        $id = $openid->identity;
        $steamID64 = str_replace("https://steamcommunity.com/openid/id/", "", $id);

        $_SESSION['steamid'] = $steamID64;

        header("Location: roulette.php");
        exit;
    } else {
        echo "Login fehlgeschlagen.";
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>SteamRoulette</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
</head>
<body>
    <main>
        <img src="./images/SteamRoulette Banner.png" alt="SteamRoulette Logo" class="logo">
        <p class="description">
            SteamRoulette hilft dir, das nächste Spiel aus deiner Steam-Bibliothek zu finden.
            Melde dich einfach mit deinem Steam-Account an und lass den Zufall entscheiden!
        </p>
        <a href="?login" class="steam-btn">
            <span>Steam verbinden</span>
            <i class="fa-brands fa-steam"></i>
        </a>
    </main>
    <footer>
        <p>Diese Anwendung steht in keiner Verbindung zu Valve oder Steam. 
        Es werden keine persönlichen Daten gespeichert – die SteamID wird nur zur Abfrage deiner Bibliothek genutzt. 
        Nutzung erfolgt auf eigene Gefahr.</p>
        <span>xrayzu &copy; 2025 SteamRoulette – Spin & Play!</span>
    </footer>

    <div class="decoration">
        <div class="balken" id="left-1"></div>
        <div class="balken" id="left-2"></div>
        <div class="balken" id="right-1"></div>
        <div class="balken" id="right-2"></div>
    </div>
</body>
</html>
