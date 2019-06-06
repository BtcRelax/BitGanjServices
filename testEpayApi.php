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
    echo \sprintf("Generated app id:%s\n<br>", $vApi->getCurrentAppId() );
    $vGetTokenResult = $vApi->getToken();
    echo \sprintf("Result GetTokenResult:%s\n<br>", $vGetTokenResult);
    if ($vGetTokenResult) {
        $vNewName = substr(str_shuffle(md5(time())), 0, 5);
        $vNewWalletResult = $vApi->addWallet($vNewName);
        echo \sprintf("Try to create new wallet name:%s\n<br> Got result:%s", $vNewName , $vNewWalletResult );        
    }
}
