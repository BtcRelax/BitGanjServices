<?php 
require 'MementoApi.php';
$vApi = new \BtcRelax\MementoApi('memento.bitganj.website',8080);
$vResult = $vApi->getLibraries();
echo \sprintf("Result of getLibraries:<font color='red'>%s</font>\n<br>", $vResult ? 'true': 'false' );
echo \sprintf("Result of getLastError:<font color='red'>%s</font>\n<br>", $vApi->getLastError());
$vUser = $_GET["user"];
$vPass = $_GET["pass"];
$vResult = $vApi->init($vUser,$vPass);
if ($vResult) {
    $vSess = $vApi->getSession();
    echo \sprintf("All Ok! \n<br> Session:%s\n<br>",$vSess );
    $vResult = $vApi->getLibraries();
    echo \sprintf("Result of getLibraries:<font color='red'>%s</font>\n<br>", $vResult ? 'true': 'false' );
    echo \sprintf("Result of getLastError:<font color='red'>%s</font>\n<br>", $vApi->getLastError());
    $vLibList = $vApi->getLibraiesList();
   foreach ($vLibList as $lib) {
        $vLibInf = \sprintf("<p>Owner: %s \n LibTitle: %s \n UUID: %s \n</p>",   $lib["owner"],$lib["model"]["title"] , $lib["model"]["uuid"]);       
        echo ($vLibInf );
   }
   
    
};

