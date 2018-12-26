<?php 
require 'PushbulletApi.php';
$vApi = new PushbulletApi();
$vToken = $_GET["token"];
$vResult = $vApi->init($vToken);
if ($vResult) {
    $vMe = $vApi->getMe(); 
    echo($vMe['name');
};

