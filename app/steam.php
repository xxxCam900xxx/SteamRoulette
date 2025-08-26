<?php
error_reporting(0);
$apiKey = getenv("STEAM_KEY");
$steamId = $_GET['steamid'] ?? '';

if ($steamId) {
    $url = "https://api.steampowered.com/IPlayerService/GetOwnedGames/v1/?key={$apiKey}&steamid={$steamId}&include_appinfo=1&include_played_free_games=1";
    
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    $games = $data['response']['games'] ?? [];

    if (!empty($games)) {
        // Zufälliges Spiel auswählen
        $randomGame = $games[array_rand($games)];
        echo "Dein nächstes Spiel: " . htmlspecialchars($randomGame['name']);
    } else {
        echo "Keine Spiele gefunden oder SteamID ungültig.";
    }
} else {
    echo "Bitte SteamID eingeben.";
}
?>
