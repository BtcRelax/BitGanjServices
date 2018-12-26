<?php 
require 'PushbulletApi.php';
$vApi = new \BtcRelax\PushbulletApi();
$vToken = $_GET["token"];
$vResult = $vApi->init($vToken);
if ($vResult) {
    $vMe = $vApi->getMe(); 
    $vApi->pushMessage("Hello");
};

