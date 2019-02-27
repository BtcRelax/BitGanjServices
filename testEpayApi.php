<?php
require 'EasyPayApi.php';
$vApi = new \BtcRelax\EasyPayApi("380999411601","FirstPass$");
$vGetSessionRes = $vApi->getSession();
echo \sprintf("Result create session:%s\n", $vGetSessionRes);
if ($vGetSessionRes) {
    $vGetTokenResult = $vApi->getToken();
    echo \sprintf("Result create session:<font color='red'>%s</font>\n<br>", $vGetTokenResult);
}
