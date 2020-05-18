<?php require('BtcRelax/core.inc');
$core = \BtcRelax\Core::getIstance();
$vAM = \BtcRelax\Core::createAM();
$vCurrentSession = $vAM->getCurrentSession();
$vState = $vCurrentSession->getState();
$vReq = $core->getRequest();
echo (\sprintf("Session Id:%s has state:%s \n<br>", session_id(), $vState ));
echo (\sprintf("Session context:%s \n<br>", session_encode()));
switch ($vState) {
    case \BtcRelax\SecureSession::STATUS_ROOT :
    case \BtcRelax\SecureSession::STATUS_USER :
    case \BtcRelax\SecureSession::STATUS_BANNED : 
        if (array_key_exists('logout', $vReq)) {
            $vAM->SignOut();
            echo (\sprintf("After sign out session id:%s has state:%s \n<br>", session_id(), $vCurrentSession->getState()));             
        } else { ?>    
            <form name="Logout form" action="login_as.php" method="POST">
                    <a href="<?php echo(SERVER_URL); ?>" target="_blank"> Goto shop </a><br>
                    <input type="hidden" name="logout" value="true" />
                    <input type="submit" value="Logoff" />
            </form>
        <?php }
        break;
    default:
        if (array_key_exists('userid', $vReq)) {
        $vCustomerId = $vReq['userid'];  
        $vUser = $vAM->getUserById($vCustomerId);
        $vIdents = $vUser->getIdentifiers();
        $vIdent = $vIdents[0];
        $vSignInRes = $vAM->SignIn($vIdent);
        echo (\sprintf("Sign in result:%s and session id:%s has state:%s \n<br>", $vSignInRes, session_id(), $vCurrentSession->getState())); ?>
            <form name="Logout form" action="login_as.php" method="POST">
                <a href="<?php echo(SERVER_URL); ?>" target="_blank"> Goto shop </a><br>
                <input type="hidden" name="logout" value="true" />
                <input type="submit" value="Logoff" />
            </form>
        <?php } else { ?>
            <form name="Login form" action="login_as.php" method="POST">
                <input type="text" name="userid" value="" />
                <input type="submit" value="Login" />
            </form>                       
        <?php }
        break;
}
