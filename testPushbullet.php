<?php 
require 'PushbulletApi.php';
$vApi = new PushbulletApi();
$vToken = $_GET["token"];
$vResult = $vApi->init($vToken);
if ($vResult) {
    echo($vApi->getMe());
};

