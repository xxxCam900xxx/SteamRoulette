<?php
error_reporting(0);
$apiKey = getenv("STEAM_KEY");
$steamId = $_GET['steamid'] ?? '';

if ($steamId) {
    $url = "https://api.steampowered.com/IPlayerService/GetOwnedGames/v1/?key={$apiKey}&steamid={$steamId}&include_appinfo=1&include_played_free_games=1";

    $response = file_get_contents($url);
    $data = json_decode($response, true);

    $games = $data['response']['games'] ?? [];

    echo '<h1>SteamRoulette</h1>';

    if (!empty($games)) {
        $randomGame = $games[array_rand($games)];
        echo "<p>Dein nächstes Spiel: <strong>" . htmlspecialchars($randomGame['name']) . "</strong></p>";
        echo '<a href="steam.php?steamid='.$steamId.'">Noch ein Spiel auswählen</a>';
    } else {
        echo "<p>Oops! Keine Spiele gefunden oder SteamID ungültig. Bitte überprüfe, dass deine Bibliothek öffentlich ist.</p>";
    }
} else {
    echo "<p>Keine SteamID vorhanden.</p>";
}
?>
