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
    <title>SteamRoulette</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
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
                    <button onclick="spin()"><i class="fa-solid fa-arrows-spin"></i> Spin!</button>
                <?php endif; ?>
            <?php else: ?>
                <p>⚠ Profil konnte nicht geladen werden.</p>
            <?php endif; ?>
        </div>

        <?php if (!empty($games)): ?>
            <div class="roulette-container">
                <div class="roulette-track" id="roulette-track"></div>
                <div class="roulette-line"></div>
            </div>
            <div id="result"></div>
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

    <script>
        let games = <?php echo json_encode($games); ?>;
        let cardWidth = 160; // inkl. margin
        let chosenIndex = null;

        function renderGames() {
            const track = document.getElementById("roulette-track");
            track.innerHTML = "";
            // Duplizieren für genug "Laufbahn"
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

            // 1. Einmal zufälliges Game wählen
            chosenIndex = Math.floor(Math.random() * games.length);
            let chosen = games[chosenIndex];

            // 2. Zielposition berechnen (mit Extra-Runden für "Spannung")
            let rounds = 3; // wie oft sich das Rad dreht
            let stopIndex = games.length * rounds + chosenIndex;
            let offset = -(stopIndex * cardWidth) + (350 - cardWidth / 2); // 350 = Hälfte von 700px container

            // 3. Animation starten
            track.style.transition = "transform 4s cubic-bezier(0.25, 1, 0.5, 1)";
            track.style.transform = `translateX(${offset}px)`;

            // 4. Nach Animation → Ergebnis anzeigen
            setTimeout(() => {
                document.getElementById("result").innerHTML = `
                    <h2>${chosen.name}</h2>
                    <img src="https://cdn.cloudflare.steamstatic.com/steam/apps/${chosen.appid}/header.jpg" alt="${chosen.name}">
                    <p>Spielzeit: ${(chosen.playtime_forever/60).toFixed(1)} Std.</p>
                `;
            }, 4200);
        }

        renderGames();
    </script>
</body>

</html>