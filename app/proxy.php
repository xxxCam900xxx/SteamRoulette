<?php
error_reporting(0);
if (!isset($_GET['appid'])) {
    http_response_code(400);
    echo json_encode(["error" => "appid fehlt"]);
    exit;
}

$appid = intval($_GET['appid']);
$url = "https://store.steampowered.com/api/appdetails?appids=" . $appid;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($ch);
curl_close($ch);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

echo $response;
