<?php namespace BtcRelax;

require 'vendor/autoload.php';
use GuzzleHttp\Client;


class PushbulletApi  {
  protected $serverUrl = "https://api.pushbullet.com/v2";
  protected $lastError = '';
  protected $me;
  protected $token;
  
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
    $client = new \GuzzleHttp\Client();
    $requestURI = \sprintf("%s/users/me", $this->serverUrl );
    $response = $client->request('GET', $requestURI , [
        'headers' => [ 'Access-Token' => $pToken ]
        ]);
    if ( $response->getStatusCode() === 200)
    {
        $json = $response->getBody();
        $this->me = \GuzzleHttp\json_decode($json, true);
        $this->token = $pToken;
        $result = true;
    } else { $this->lastError = \sprint("Server return code:%s", $response->getStatusCode()); };
    return $result;
  }
  
  function pushMessage(string $message) {
    $result = false;
    $client = new \GuzzleHttp\Client();    
    $json->body = $message;
    $json->title = 'BitGanj Shop';
    $json->type = 'note';

    $requestURI = \sprintf("%s/pushes", $this->serverUrl );
    $response = $client->request('POST', $requestURI , [
        'headers' => [ 'Access-Token' => $this->token, 'Content-Type' => 'application/json' ],
        'body' => \json_encode($json),
         ]);
    if ( $response->getStatusCode() === 200)
    {
        $json = $response->getBody();
        $this->me = \GuzzleHttp\json_decode($json, true);
        $result = true;
    } else { $this->lastError = \sprint("Server return code:%s", $response->getStatusCode()); };
    return $result;    
  }
  
}

