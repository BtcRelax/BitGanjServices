<?php 
require 'MementoApi.php';
$vApi = new MementoApi('memento.bitganj.website',8080);
$vResult = $vApi->getLibraries();
echo \sprintf("Result of getLibraries:<font color='red'>%s</font>\n<br>", $vResult ? 'true': 'false' );
echo \sprintf("Result of getLastError:<font color='red'>%s</font>\n<br>", $vApi->getLastError());
$vResult = $vApi->init($vUser,$vPass);
if ($vResult) {
    $vSess = $vApi->getSession();
    echo \sprintf("All Ok! \n<br> Session:%s\n<br>",$vSess );
    $vResult = $vApi->getLibraries();
    echo \sprintf("Result of getLibraries:<font color='red'>%s</font>\n<br>", $vResult ? 'true': 'false' );
    echo \sprintf("Result of getLastError:<font color='red'>%s</font>\n<br>", $vApi->getLastError());
};

