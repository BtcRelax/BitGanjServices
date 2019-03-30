<?php 
require 'PushbulletApi.php';
$vApi = new \BtcRelax\PushbulletApi();
$vToken = $_GET["token"];
$vResult = $vApi->init($vToken);
if ($vResult) {
    echo "Pushbullet init: Ok\n<br>" ;
    $vMe = $vApi->getMe(); 
    $vPushRes = $vApi->pushMessage("Hello");
    echo \sprintf("Pushing message result:%s", $vPushRes);
}else
{
    echo \sprintf("Pushbullet init error:%s", $vApi->getLastError());
};

