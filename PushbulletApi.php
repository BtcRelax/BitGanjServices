<?php
require 'vendor/autoload.php';
use GuzzleHttp\Client;


class PushbulletApi  {
  protected $serverUrl = "https://api.pushbullet.com/v2";
  protected $lastError = '';
  protected $me;
  
  function __construct() {
   }

  function getLastError() {
    return $this->lastError;
  }
  
  function getMe()
  {
      return $this->me;
  }
  
  
  function init(string $pToken)  {
    $result = false;
    $client = new GuzzleHttp\Client();
    $requestURI = \sprintf("%s/users/me", $this->serverUrl );
    $response = $client->request('GET', $requestURI , [
        'headers' => [ 'Access-Token' => $pToken ]
        ]);
    if ( $response->getStatusCode() === 200)
    {
        $this->me = $response->getBody();
            $result = true;
    } else { $this->lastError = \sprint("Server return code:%s", $response->getStatusCode()); };
    return $result;
  }
  
}

