<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>LHC Helper test</title>
    </head>
    <body>
        <?php
            require 'LHCApi.php';
            $vLhcApi = new \BtcRelax\LHCApi();
            $vLhcApi->setDepartment(1);
            //$vLhcApi->setThema();
            $vLhcApi->setUserNameAlias("TestUserName");
            echo $vLhcApi->fillChatWidget('CustomerId', true ,  'https://help.bitganj.website' );
        ?>
    </body>
</html>
