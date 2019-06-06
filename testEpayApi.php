<?php
require 'EasyPayApi.php';
$vUser = $_GET["user"];
$vPass = $_GET["pass"];
$vApi = new \BtcRelax\EasyPayApi($vUser,$vPass);
if (isset($_GET["proxy"])) {
    $vProxy = $_GET["proxy"];
    $vApi->setProxyUrl($vProxy);
}
$vGetSessionRes = $vApi->getSession();
echo \sprintf("Result create session:%s\n<br>", $vGetSessionRes);
if ($vGetSessionRes) {
    $vGetTokenResult = $vApi->getToken();
    echo \sprintf("Result GetTokenResult:%s\n<br>", $vGetTokenResult);
}
