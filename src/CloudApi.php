<?php namespace BtcRelax;

class CloudApi
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
    
    public function inint(string $pUser, string $pPassword)
    {
        require_once 'HTTP/Request2.php';
        $request = new HTTP_Request2();
        $request->setUrl('https://cloud.fastfen.club/ocs/v2.php/apps/serverinfo/api/v1/info?format=json');
        $request->setMethod(HTTP_Request2::METHOD_GET);
        $request->setConfig(array(
          'follow_redirects' => true
        ));
        $request->setHeader(array(
          'Authorization' => 'Basic Z29kOk0zR25SLUFDOHpiLXNyWm1ELTJEem9xLXQ5THRF'
        ));
        try {
            $response = $request->send();
            if ($response->getStatus() == 200) {
                echo $response->getBody();
            } else {
                echo 'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
            $response->getReasonPhrase();
            }
        } catch (HTTP_Request2_Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }
}
