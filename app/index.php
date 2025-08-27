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

    <a href="https://github.com/xxxCam900xxx/SteamRoulette" class="github-corner" aria-label="View source on GitHub"><svg width="80" height="80" viewBox="0 0 250 250" style="fill:#3D3D3D; color:#fff; position: absolute; top: 0; border: 0; left: 0; transform: rotate(-90deg);" aria-hidden="true">
            <path d="M0,0 L115,115 L130,115 L142,142 L250,250 L250,0 Z" />
            <path d="M128.3,109.0 C113.8,99.7 119.0,89.6 119.0,89.6 C122.0,82.7 120.5,78.6 120.5,78.6 C119.2,72.0 123.4,76.3 123.4,76.3 C127.3,80.9 125.5,87.3 125.5,87.3 C122.9,97.6 130.6,101.9 134.4,103.2" fill="currentColor" style="transform-origin: 130px 106px;" class="octo-arm" />
            <path d="M115.0,115.0 C114.9,115.1 118.7,116.5 119.8,115.4 L133.7,101.6 C136.9,99.2 139.9,98.4 142.2,98.6 C133.8,88.0 127.5,74.4 143.8,58.0 C148.5,53.4 154.0,51.2 159.7,51.0 C160.3,49.4 163.2,43.6 171.4,40.1 C171.4,40.1 176.1,42.5 178.8,56.2 C183.1,58.6 187.2,61.8 190.9,65.4 C194.5,69.0 197.7,73.2 200.1,77.6 C213.8,80.2 216.3,84.9 216.3,84.9 C212.7,93.1 206.9,96.0 205.4,96.6 C205.1,102.4 203.0,107.8 198.3,112.5 C181.9,128.9 168.3,122.5 157.7,114.1 C157.9,116.9 156.7,120.9 152.7,124.9 L141.0,136.5 C139.8,137.7 141.6,141.9 141.8,141.8 Z" fill="currentColor" class="octo-body" />
        </svg></a>
    <style>
        .github-corner:hover .octo-arm {
            animation: octocat-wave 560ms ease-in-out
        }

        @keyframes octocat-wave {

            0%,
            100% {
                transform: rotate(0)
            }

            20%,
            60% {
                transform: rotate(-25deg)
            }

            40%,
            80% {
                transform: rotate(10deg)
            }
        }

        @media (max-width:500px) {
            .github-corner:hover .octo-arm {
                animation: none
            }

            .github-corner .octo-arm {
                animation: octocat-wave 560ms ease-in-out
            }
        }
    </style>


</body>

</html>