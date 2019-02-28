<?php
require 'EasyPayApi.php';
$vApi = new \BtcRelax\EasyPayApi("380999411601","FirstPass$1");
$vGetSessionRes = $vApi->getSession();
echo \sprintf("Result create session:%s\n<br>", $vGetSessionRes);
if ($vGetSessionRes) {
    $vGetTokenResult = $vApi->getToken();
    echo \sprintf("Result GetTokenResult:%s\n<br>", $vGetTokenResult);
}
