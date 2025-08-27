<?php
error_reporting(E_ALL & ~E_DEPRECATED);
require 'openid.php';
session_start();

$apiKey = getenv("STEAM_KEY");
$openid = new LightOpenID('steam.xrayzu.com'); // deine Domain

// --- Login mit Steam OpenID ---
if (!$openid->mode) {
    if (isset($_GET['login'])) {
        $openid->identity = 'https://steamcommunity.com/openid';
        header('Location: ' . $openid->authUrl());
        exit;
    }
} elseif ($openid->mode == 'cancel') {
    echo "Login abgebrochen.";
    exit;
} else {
    if ($openid->validate()) {
        $id = $openid->identity;
        $steamID64 = str_replace("https://steamcommunity.com/openid/id/", "", $id);

        $_SESSION['steamid'] = $steamID64;
        header("Location: roulette.php");
        exit;
    } else {
        echo "Login fehlgeschlagen.";
        exit;
    }
}

// --- Login per Eingabe (SteamID oder Vanity Name) ---
if (isset($_POST['userinput'])) {
    $input = trim($_POST['userinput']);
    if (!empty($input)) {
        if (ctype_digit($input)) {
            // Eingabe ist numerisch -> direkt SteamID64
            $steamID64 = $input;
        } else {
            // Eingabe ist VanityURL -> ResolveVanityURL API nutzen
            $url = "https://api.steampowered.com/ISteamUser/ResolveVanityURL/v1/?key={$apiKey}&vanityurl=" . urlencode($input);
            $response = file_get_contents($url);
            $data = json_decode($response, true);

            if (isset($data['response']['steamid'])) {
                $steamID64 = $data['response']['steamid'];
            } else {
                $error = "Benutzer konnte nicht gefunden werden.";
            }
        }

        if (!empty($steamID64)) {
            $_SESSION['steamid'] = $steamID64;
            header("Location: roulette.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SteamRoulette</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
</head>
<body>
    <main>
        <img src="./images/SteamRoulette Banner.png" alt="SteamRoulette Logo" class="logo">
        <p class="description">
            SteamRoulette hilft dir, das nächste Spiel aus deiner Steam-Bibliothek zu finden.
            Melde dich mit deinem Steam-Account an oder gib deine SteamID bzw. deinen Profilnamen ein!
        </p>

        <!-- Variante 1: Eingabe -->
        <form method="post">
            <input type="text" name="userinput" placeholder="SteamID64 oder Profilname" required>
            <button type="submit" class="steam-btn">
                <span>Mit Eingabe verbinden</span>
                <i class="fa-solid fa-user"></i>
            </button>
        </form>

        <p>oder</p>

        <!-- Variante 2: Steam Login -->
        <a href="?login" class="steam-btn">
            <span>Steam verbinden</span>
            <i class="fa-brands fa-steam"></i>
        </a>

        <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
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
