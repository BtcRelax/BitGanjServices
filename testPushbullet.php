<html lang="en"><head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Pushbullet tester</title>
    <!-- Bootstrap -->
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

  </head>
  <body>

<?php
    require 'vendor/autoload.php';
    require 'src/PushbulletApi.php';
    $vApi=new \BtcRelax\PushbulletApi();
    $vToken=$_REQUEST["token"];
    if (isset($vToken)) {
        $vResult=$vApi->init($vToken);
        if ($vResult) {
            echo "Pushbullet init: Ok\n<br>";
            $vMe=$vApi->getMe();
            $vPushRes=$vApi->pushMessage("Hello");
            echo \sprintf("Pushing message result:%s", $vPushRes);
        } else {
            echo \sprintf("Pushbullet init error:%s", $vApi->getLastError());
        }
        ;
    } else {
        ?>
		<form id="paramsInputForm">
                <div class="form-group">
                <label for="paramsUser">Token</label>
                <input type="text" class="form-control" id="paramsToken" placeholder="Token for login" name="token">
                </div>
                <button type="submit" class="btn btn-primary">Check</button>
            </form>	
	<?php
    }
    ;
?>
  
</body></html>
