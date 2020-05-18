<?php
require 'src/EasyPayApi.php';

            echo \sprintf("Error creating EasyPay api:%s", $exc->getMessage()); 
    }
} else { ?>
<form action="#">
  User name:<br>
  <input type="text" name="user" value="380999411601"><br>
  Password:<br>
  <input type="text" name="pass" value="1TestPass"><br>
  Proxy:<br>
  <input type="text" name="proxy" value="Selgod:Q0a0LfB@185.166.216.90:45785"><br><br>
    <input type="hidden" name="start_debug" value="1">
    <input type="hidden" name="debug_host" value="localhost">
    <input type="hidden" name="debug_port" value="10137">
  <input type="submit" value="Submit">
</form>    
<?php } ?>
        