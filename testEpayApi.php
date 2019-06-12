<?php
require 'EasyPayApi.php';
if (isset($_GET["user"]) && isset($_GET["pass"])) {
    $vUser = $_GET["user"];
    $vPass = $_GET["pass"];
        try {
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
                    $vNewWalletInstrumentId = $vApi->addWallet($vNewName);
                    echo \sprintf("Try to create new wallet name:%s\n<br> Got result:%s\n<br>", $vNewName , $vNewWalletInstrumentId );        
                    echo ("Rendering wallets:");
                    echo $vApi->renderGetWallets();
                    $vNewWallet = $vApi->getWalletByInstrumentId($vNewWalletInstrumentId);
                    $vNewWalletNumber = $vNewWallet['number'];
                    echo (\sprintf("Getting wallet number: %s\n<br>", $vNewWalletNumber));
                    $vDeleteResult = $vApi->deleteWalletByNumber($vNewWalletNumber);
                    echo (\sprintf("Delete wallet by number:%s, result: %s\n<br>",$vNewWalletNumber, $vDeleteResult));
                }
            }
        } catch (Exception $exc) {
            echo \sprintf("Error creating EasyPay api:%s", $exc->getMessage()); 
        }
} else { ?>
<form action="#">
  User name:<br>
  <input type="text" name="user" value="380999411601"><br>
  Password:<br>
  <input type="text" name="pass" value="1TestPass"><br>
  Proxy:<br>
  <input type="text" name="proxy" value="217.27.151.75:34935"><br><br>
    <input type="hidden" name="start_debug" value="1">
    <input type="hidden" name="debug_host" value="localhost">
    <input type="hidden" name="debug_port" value="10137">
  <input type="submit" value="Submit">
</form>    
<?php } ?>
        