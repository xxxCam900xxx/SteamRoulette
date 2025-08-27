<?php
session_start();
$apiKey = getenv("STEAM_KEY");

$appid = intval($_GET['appid'] ?? 0);
$steamid = $_SESSION['steamid'] ?? null;

if (!$appid || !$steamid) {
    http_response_code(400);
    echo json_encode(["error" => "Fehlende Parameter"]);
    exit;
}

// --- Storefront Infos ---
$storeUrl = "https://store.steampowered.com/api/appdetails?appids={$appid}&l=german";
$storeJson = @file_get_contents($storeUrl);
$storeData = $storeJson ? json_decode($storeJson, true) : null;
$store = $storeData[$appid]['data'] ?? null;

// --- Achievements Schema ---
$schemaUrl = "https://api.steampowered.com/ISteamUserStats/GetSchemaForGame/v2/?key={$apiKey}&appid={$appid}";
$schemaJson = @file_get_contents($schemaUrl);
$schemaData = $schemaJson ? json_decode($schemaJson, true) : null;
$totalAchievements = $schemaData['game']['availableGameStats']['achievements'] ?? [];
$total = count($totalAchievements);

// --- Player Achievements ---
$playerUrl = "https://api.steampowered.com/ISteamUserStats/GetPlayerAchievements/v1/?key={$apiKey}&steamid={$steamid}&appid={$appid}";
$playerJson = @file_get_contents($playerUrl);
$playerData = $playerJson ? json_decode($playerJson, true) : null;
$achieved = 0;
if (!empty($playerData['playerstats']['achievements'])) {
    foreach ($playerData['playerstats']['achievements'] as $a) {
        if ($a['achieved'] == 1) $achieved++;
    }
}

$result = [
    "name" => $store['name'] ?? "",
    "description" => $store['short_description'] ?? "",
    "release_date" => $store['release_date']['date'] ?? "",
    "developers" => $store['developers'] ?? [],
    "publishers" => $store['publishers'] ?? [],
    "achievements" => [
        "total" => $total,
        "achieved" => $achieved,
        "missing" => max(0, $total - $achieved)
    ]
];

header("Content-Type: application/json; charset=utf-8");
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
