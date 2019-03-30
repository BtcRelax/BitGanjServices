<?php namespace BtcRelax;
require_once ('vendor/autoload.php');


class EasyPayApi {

    const BASE_URL = 'https://api.easypay.ua/';
    const APP_ID = 'c954eff2-9779-4ade-8723-c4daa7bec606';
    const USER_AGENT = 'okhttp/3.9.0';
    const PARTNER_KEY = 'easypay-v2-android';

    private $RequestedSessionId;
    private $PageId;
    private $LastError = '';
    private $User;
    private $Password;
    private $AccessToken;
    private $TokenType;
    private $Expires;
    private $RefreshToken;
    private $UserId;
    private $ClientId;
    private $InIssued;
    private $InExpires;
    private $Wallets;
    private $IsHideMainWallet = false;
    private $LocalExpires;
    private $ProxyServer;
    

    public function __construct($pUser, $pPassword) {
        $this->User = $pUser;
        $this->Password = $pPassword;
        if (empty($pUser) || empty($pPassword)) {
            throw new \BtcRelax\Exception\AuthentificationCritical("Creating epay api client without login or password");
        }
        $this->setIsHideMainWallet(true);
    }
    
    function getProxyServer() {
        return $this->ProxyServer;
    }

    function setProxyServer($ProxyServer) {
        $this->ProxyServer = $ProxyServer;
    }

    public function init() {
        $result = $this->isInited();
        if (!$result) {
            if ($this->getSession()) {
                if ($this->getToken()) {
                    $result = true;
                }
            }
        }
        return $result;
    }

    public function isInited() {
        $result = false;
        if (!empty($this->InExpires)) {
            $currentDT = new \DateTime("now") ;
            $expiresDT = new \DateTime($this->InExpires);
            if ($currentDT <  $expiresDT ) {
                $this->LastError = null;
                $result = true;
            }
        }
        return $result;
    }

    public function setIsHideMainWallet($isHideMainWallet) {
        $this->IsHideMainWallet = $isHideMainWallet;
    }

    public function getWallets() {
        $result = false;
        try {
            $vAuth = \sprintf('%s %s', $this->TokenType, $this->AccessToken);
            $client = new \GuzzleHttp\Client(['base_uri' => self::BASE_URL]);
            $vReqId = $this->getRequestedSessionId();
            $vPageId = $this->getPageId();
            $response = $client->request('GET', '/api/wallets/get', [
                'headers' => ['User-Agent' => self::USER_AGENT, 'Accept' => 'application/json',
                    'AppId' => self::APP_ID, 'Authorization' => $vAuth,
                    'PartnerKey' => self::PARTNER_KEY, 'RequestedSessionId' => $vReqId,
                    'PageId' => $vPageId, 'Locale' => 'Ua']]);
            $code = $response->getStatusCode();
            if ($code === 200) {
                $this->processResponse($response);
                $result = true;
            }
        } catch (Exception $e) {
            $this->LastError = $e->getMessage();
        }
        return $result;
    }
    
    public function getWalletByInstrumentId($pInstrumentId)
    {
        $result= $this->actionGetWallets();
        if ($result)
        {
            foreach ($this->Wallets as $value) {
            if ($value['instrumentId'] === $pInstrumentId) {
                $result = $value;
                    break;
                }
            }
        }
        return $result;
    }
            
    public function addWallet($pWalletName) {
        $result = false;
        try {
            //$payload = \sprintf('color=#D7CCC8&name="%s"', $pWalletName);
            $vAuth = \sprintf('%s %s', $this->TokenType, $this->AccessToken);
            $client = new \GuzzleHttp\Client(['base_uri' => self::BASE_URL]);
            $vReqId = $this->getRequestedSessionId();
            $vPageId = $this->getPageId();
            $response = $client->request('POST', '/api/wallets/add', [
                \GuzzleHttp\RequestOptions::JSON => ['color' => '#D7CCC8', 'name' => $pWalletName ],
                'headers' => ['User-Agent' => self::USER_AGENT, 'Accept' => 'application/json',
                    'AppId' => self::APP_ID, 'Authorization' => $vAuth,
                    'PartnerKey' => self::PARTNER_KEY, 'RequestedSessionId' => $vReqId,
                    'PageId' => $vPageId, 'Locale' => 'Ua']]);
            $code = $response->getStatusCode();
            if ($code === 200) {
                $addResult = $this->processAddResponse($response);
                if (empty($addResult["error"]))
                {
                    $result = $addResult['instrumentId'];
                }
                else
                {
                    $this->LastError = $addResult["error"];
                }
            }
        } catch (Exception $e) {
            $this->LastError = $e->getMessage();
        }
        return $result;
    }

    
    
    public function actionNewWallet($pNewWalletName)
    {
        $result = false;
        $initResult = $this->init();
        if ($initResult) {
            $vInstrumentId = $this->addWallet($pNewWalletName);
        }
        if ($vInstrumentId !== false) {
            $result = $this->getWalletByInstrumentId($vInstrumentId);
        }
        return $result;          
    }
     
    public function renderAddWallet($pWalletName) {
        $addWalletResult = $this->actionNewWallet($pWalletName);
        if ($addWalletResult !== false) {
            $this->getWallets();
            $vNewWallet = $this->getWalletByInstrumentId($addWalletResult);
            $vHtml = \sprintf("<div id=\"dialog\" title=\"Wallet created!\"><p>New wallet name:%s</p><p>New wallet number:%s</p></div>"
                    . "<script>$( function() \{$( \"#dialog\" ).dialog();} );</script>", $vNewWallet['name'], $vNewWallet['number']);     
        } else {
            $vHtml = \sprintf("<div id=\"dialog\" title=\"Error create wallet!\">
                            <p>Error message:%s</p></div><script>$( function() \{$( \"#dialog\" ).dialog();} );</script>", $this->getLast_error()); 
        }
        return $vHtml;        
    }

    public function getToken() {
        $result = false;
        try {
            $payload = \sprintf('client_id=easypay-v2-android&grant_type=password&username=%s&password=%s', $this->User, $this->Password);
            $client = new \GuzzleHttp\Client(['base_uri' => self::BASE_URL]);
            $vReqId = $this->getRequestedSessionId();
            $vPageId = $this->getPageId();
            $vRequestParams = [
                'body' => $payload,
                'headers' => ['User-Agent' => self::USER_AGENT, 'Accept' => 'application/json',
                    'AppId' => self::APP_ID, 'No-Authentication' => true,
                    'PartnerKey' => self::PARTNER_KEY, 'RequestedSessionId' => $vReqId,
                    'PageId' => $vPageId, 'Locale' => 'Ua']];
            $vProxy = $this->getProxyServer();
            if (!empty($vProxy)) {
                $vRequestParams += ['proxy' => $vProxy ];
            }
            $response = $client->request('POST', '/api/token', $vRequestParams );
            $code = $response->getStatusCode();
            if ($code === 200) {
                $this->processResponse($response);
                $this->LastError = null;
                $result = true;
            }
        } catch (\GuzzleHttp\Exception\RequestException $gexc) {
            $vResponse = $gexc->response();
            $this->LastError = \sprintf('Error on getting token:%s', $vResponse->getMessage());
        }
        return $result;
    }

    public function getSession() {
        $result = false;
        try {
            $client = new \GuzzleHttp\Client(['base_uri' => self::BASE_URL]);
            $response = $client->request('POST', '/api/system/createSession', [
                'headers' => ['User-Agent' => self::USER_AGENT,
                    'Accept' => 'application/json', 'AppId' => self::APP_ID]]);
            $code = $response->getStatusCode();
            if ($code === 200) {
                $this->processResponse($response);
                $this->LastError = null;
                $result = true;
            }
        } catch (\GuzzleHttp\Exception\RequestException $gexc) {
                $this->LastError = \sprintf('Error getting session:%s', $gexc->getMessage());
        }
        return $result;
    }

    private function processAddResponse($response) {
        $json = $response->getBody();
        $data = \GuzzleHttp\json_decode($json, true);
        $result = array("id"=> $data['id'] , "instrumentId" => $data['instrumentId'], "error" => $data["error"]);
        return $result;
    }

    private function processResponse($response) {
        $json = $response->getBody();
        $data = \GuzzleHttp\json_decode($json, true);
        foreach ($data as $key => $value) {
            switch ($key) {
                case 'requestedSessionId':
                    $this->RequestedSessionId = $value;
                    break;
                case 'pageId':
                    $this->PageId = $value;
                    break;
                case 'access_token':
                    $this->AccessToken = $value;
                    break;
                case 'token_type':
                    $this->TokenType = $value;
                    break;
                case 'expires_in':
                    $this->Expires = $value;
                    $this->LocalExpires = time() + $value;
                    break;
                case 'refresh_token':
                    $this->RefreshToken = $value;
                    break;
                case 'userId':
                    $this->UserId = $value;
                    break;
                case 'client_id':
                    $this->ClientId = $value;
                    break;
                case '.issued':
                    $this->InIssued = $value;
                    break;
                case '.expires':
                    $this->InExpires = $value;
                    break;
                case 'wallets':
                    $this->Wallets = $value;
                    break;
                default:
                    break;
            }
        }
    }

    public function actionGetWallets() {
        $result = false;
        $initResult = $this->init();
        if ($initResult) {
            $result = $this->getWallets();
        }
        return $result;
    }
    
    public function actionGetWalletBalanceByAddress($walletAddress)
    {
        $result = false;
        if ($this->actionGetWallets())
        {
            foreach ($this->Wallets as $cWallet) {
                if ($cWallet['number'] === $walletAddress )
                {
                    $result = $cWallet['balance'];
                    $this->setLast_error(null);
                    return $result;
                }
            }
        }
        if (FALSE === $result) { $this->setLast_error("Wallet not found!"); };
        return $result;
    }

    public function renderGetWallets() {
        if ($this->actionGetWallets()) {
            $vHtml = "<table border = \"1\" width = \"1\" cellspacing = \"3\" cellpadding = \"3\"><thead>";
            $vHtml .= "<tr><th>Тип кошелька</th><th>Название кошелька</th><th>Номер кошелька</th><th>Балланс кошелька</th></tr></thead><tbody>";
            $vRows = "";
            foreach ($this->Wallets as $value) {
                if ($this->isHideMainWallet && $value['walletType'] !== 'Current') {
                    $vRows .= \sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>', $value['walletType'] === 'Current' ? 'Основной' : 'Дополнительный', $value['name'], $value['number'], $value['balance']);
                };
            };
            $vHtml .= \sprintf('%s</tbody></table>', $vRows);
        } else {
            $vHtml = \sprintf("Произошла ошибка:%s", $this->getLast_error());
        };
        return $vHtml;
    }

    protected function getRequestedSessionId() {
        return $this->RequestedSessionId;
    }

    protected function getPageId() {
        return $this->PageId;
    }

    public function getLastError() {
        return $this->LastError;
    }

    public function setLastError($LastError) {
        $this->LastError = $LastError;
    }

}
