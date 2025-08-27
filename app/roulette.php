<?php
error_reporting(0);
session_start();
if (!isset($_SESSION['steamid'])) {
    header("Location: index.php");
    exit;
}

$steamid = $_SESSION['steamid'];
$apiKey = getenv("STEAM_KEY");

// --- Profil holen ---
$profileUrl = "https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v2/?key={$apiKey}&steamids={$steamid}";
$profileJson = @file_get_contents($profileUrl);
$profileData = $profileJson ? json_decode($profileJson, true) : null;
$player = $profileData['response']['players'][0] ?? null;

// --- Spiele holen ---
$gamesUrl = "https://api.steampowered.com/IPlayerService/GetOwnedGames/v1/?key={$apiKey}&steamid={$steamid}&include_appinfo=1&include_played_free_games=1";
$gamesJson = @file_get_contents($gamesUrl);
$gamesData = $gamesJson ? json_decode($gamesJson, true) : null;
$games = $gamesData['response']['games'] ?? [];
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SteamRoulette</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <style>
        .hidden { display: none; }
    </style>
</head>

<body>
    <main>
        <div class="profileCard">
            <?php if ($player): ?>
                <div class="content">
                    <img src="<?php echo $player['avatarfull']; ?>" alt="Avatar">
                    <div class="infos">
                        <h1><?php echo htmlspecialchars($player['personaname']); ?></h1>
                        <p>SteamID: <?php echo $steamid; ?></p>
                    </div>
                </div>
                <?php if (!empty($games)): ?>
                    <button id="spinBtn" onclick="spin()"><i class="fa-solid fa-arrows-spin"></i> Spin!</button>
                <?php endif; ?>
            <?php else: ?>
                <p>⚠ Profil konnte nicht geladen werden.</p>
            <?php endif; ?>
        </div>

        <?php if (!empty($games)): ?>
            <div class="roulette-container" id="roulette-container">
                <div class="roulette-track" id="roulette-track"></div>
                <div class="roulette-line"></div>
            </div>
            <div id="result" class="hidden tinyResult"></div>
        <?php else: ?>
            <p>⚠ Keine Spiele gefunden. Stelle sicher, dass deine Bibliothek öffentlich ist!</p>
        <?php endif; ?>

        <a class="logout" href="logout.php">Logout</a>
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

    <script>
        let games = <?php echo json_encode($games); ?>;
        let cardWidth = 160; // inkl. margin
        let chosenIndex = null;

        function renderGames() {
            const track = document.getElementById("roulette-track");
            track.innerHTML = "";
            for (let i = 0; i < 8; i++) {
                games.forEach(g => {
                    let card = document.createElement("div");
                    card.className = "roulette-card";
                    card.innerHTML = `
                        <img src="https://cdn.cloudflare.steamstatic.com/steam/apps/${g.appid}/capsule_184x69.jpg" alt="${g.name}">
                        <small>${g.name}</small>
                    `;
                    track.appendChild(card);
                });
            }
        }

        function spin() {
            if (!games.length) return;

            const track = document.getElementById("roulette-track");
            const rouletteContainer = document.getElementById("roulette-container");
            const resultDiv = document.getElementById("result");

            // Ansicht zurücksetzen
            rouletteContainer.classList.remove("hidden");
            resultDiv.classList.add("hidden");
            resultDiv.innerHTML = "";

            // --- Wichtig: Animation reset ---
            track.style.transition = "none";
            track.style.transform = "translateX(0)";
            void track.offsetHeight; // Force Reflow

            // Zufälliges Spiel wählen
            chosenIndex = Math.floor(Math.random() * games.length);
            let chosen = games[chosenIndex];

            let rounds = 3;
            let stopIndex = games.length * rounds + chosenIndex;
            let offset = -(stopIndex * cardWidth) + (350 - cardWidth / 2);

            // Neue Animation starten
            setTimeout(() => {
                track.style.transition = "transform 4s cubic-bezier(0.25, 1, 0.5, 1)";
                track.style.transform = `translateX(${offset}px)`;
            }, 50);

            setTimeout(() => {
                fetch(`gameinfo.php?appid=${chosen.appid}`)
                    .then(res => res.json())
                    .then(info => {
                        // Roulette ausblenden, Ergebnis zeigen
                        rouletteContainer.classList.add("hidden");
                        resultDiv.classList.remove("hidden");

                        resultDiv.innerHTML = `                      
<img src="https://cdn.cloudflare.steamstatic.com/steam/apps/${chosen.appid}/header.jpg" alt="GameImage">
<div>
    <div class="gameInfo">
        <h1>${chosen.name}</h1>
        <p>${info.description}</p>
    </div>
    <div class="userDetails">
        <p><strong>Spielzeit:</strong> ${(chosen.playtime_forever/60).toFixed(1)} Std.</p>
        <p><strong>Achievements:</strong> ${info.achievements.achieved}/${info.achievements.total}
            (${info.achievements.missing} fehlen)</p>
    </div>
    <div class="gameDetails">
        <p><strong>Release:</strong> ${info.release_date}</p>
        <p><strong>Entwickler:</strong> ${info.developers?.join(", ")}</p>
        <p><strong>Publisher:</strong> ${info.publishers?.join(", ")}</p>
    </div>
</div>
                        `;
                    });
            }, 4200);
        }

        renderGames();
    </script>
</body>
</html>
