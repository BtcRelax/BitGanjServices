<?php
require 'EasyPayApi.php';
$vUser = $_GET["user"];
$vPass = $_GET["pass"];
$vProxy = $_GET["proxy"];
$vApi = new \BtcRelax\EasyPayApi($vUser,$vPass);
$vApi->setProxyServer($vProxy);
$vGetSessionRes = $vApi->getSession();
echo \sprintf("Result create session:%s\n<br>", $vGetSessionRes);
if ($vGetSessionRes) {
    $vGetTokenResult = $vApi->getToken();
    echo \sprintf("Result GetTokenResult:%s\n<br>", $vGetTokenResult);
}
