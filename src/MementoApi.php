<?php namespace BtcRelax;

use GuzzleHttp\Client;

class MementoApi
{
    protected $serverUrl;
    protected $serverPort;
    protected $session = null;
    protected $LibrariesList = [];
    protected $lastError = '';
  
    public function __construct($pServer, $pPort = 8080)
    {
        $this->serverUrl = $pServer;
        $this->serverPort = $pPort;
    }

    public function getLastError()
    {
        return $this->lastError;
    }
  
    public function getLibraiesList()
    {
        return $this->LibrariesList;
    }
    
    public function getLibraries()
    {
        $result = false;
        if ($this->isInited()) {
            $client = new GuzzleHttp\Client();
            $requestURI = \sprintf("%s:%s/ms", $this->serverUrl, $this->serverPort);
            $response = $client->request('GET', $requestURI, [
          'query' => ['cmd_name' => 'get_libs3'],
          'headers' => [ 'User-Agent' => 'bitganj', 'protocol'     => '1', 'cmd' => 'get_libs3' , 'session' => $this->session ]
          ]);
            if ($response->getStatusCode() === 200) {
                $json = $response->getBody();
                $data = \GuzzleHttp\json_decode($json, true);
                $this->LibrariesList = $data['libraries'];
                $result = true;
                $this->lastError = '';
            } else {
                $this->lastError = \sprint("Server retuen code:%s", $response->getStatusCode());
            };
        } else {
            $this->lastError = "Api not initialised!";
        };
        return $result;
    }
  
    public function init(string $pUser, string $pPassword)
    {
        $client = new GuzzleHttp\Client();
        $requestURI = \sprintf("%s:%s/ms", $this->serverUrl, $this->serverPort);
        $response = $client->request('GET', $requestURI, [
        'query' => ['pass' => $pPassword, 'login' => $pUser , 'cmd_name' => 'auth'],
        'headers' => [ 'User-Agent' => 'bitganj', 'protocol'     => '1', 'cmd' => 'auth']
        ]);
        if ($response->getStatusCode() === 200) {
            $sessionsObjects = $response->getHeader('session');
            $this->session = array_pop($sessionsObjects);
            $this->lastError = '';
        } else {
            $this->lastError = \sprint("Server retuen code:%s", $response->getStatusCode());
        };
        return $this->isInited();
    }

    public function getSession()
    {
        return $this->session ;
    }

    public function isInited()
    {
        return empty($this->getSession()) ? false : true;
    }
}
