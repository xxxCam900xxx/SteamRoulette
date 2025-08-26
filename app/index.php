<?php
error_reporting(E_ALL & ~E_DEPRECATED);
require 'openid.php';
$openid = new LightOpenID('steam.xrayzu.com');

if (!$openid->mode) {
    echo '<h1>SteamRoulette</h1>';
    echo '<a href="?login">Mit Steam verbinden</a>';

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
        preg_match("/^https:\/\/steamcommunity\.com\/openid\/id\/(\d+)$/", $id, $matches);
        $steamId = $matches[1];
        // Weiterleitung zum Randomizer
        header("Location: steam.php?steamid=$steamId");
        exit;
    } else {
        echo "Login fehlgeschlagen.";
    }
}
?>
