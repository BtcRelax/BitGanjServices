<?php namespace BtcRelax;
require_once ('vendor/autoload.php');


class EasyPayApi {
  
    const base_url = 'https://api.easypay.ua/';
    const PartnerKey = 'easypay-v2-android';

    protected $RequestedSessionId;
    protected $PageId;
    protected $Last_error = '';
    protected $User;
    protected $Password;
    protected $Access_token;
    protected $Token_type;
    protected $Expires;
    protected $Refresh_token;
    protected $UserId;
    protected $ClientId;
    protected $inIssued;
    protected $inExpires;
    protected $Wallets;
    protected $_isHideMainWallet = false;
    protected $_localExpires;
	protected $UserAgent = 'okhttp/3.9.0';
	protected $AppId = array (	'05344833-05ca-4599-a282-70c402ed16b0','0716eb6f-23b4-4ac9-99b2-74e1f8ed34ce',
	'0944575e-b2bc-4667-8bb8-dacbdabb6c43','a5806a5f-dbb8-496a-a23f-aab6d2fcbce1','c954eff2-9779-4ade-8723-c4daa7bec606', 		'cd7fde18-15db-4d94-a91b-7cf8edd81209','ab5be70d-9de0-44ea-80ce-52fd6f34a5b7','06b8702c-a5e3-451b-bb04-715d0913e6b2',
	'37919a20-f9b4-4c6c-b255-460972803546','44798190-b837-47e1-881e-fdc6f733f43b','a2b6c187-3068-40a0-a4fe-7979cc918ebb',
	'932e03be-1e62-4b41-babd-338f6b90af99','05344833-05ca-4599-a282-70c402ed16b0','0716eb6f-23b4-4ac9-99b2-74e1f8ed34ce',
	'0944575e-b2bc-4667-8bb8-dacbdabb6c43','a5806a5f-dbb8-496a-a23f-aab6d2fcbce1','c954eff2-9779-4ade-8723-c4daa7bec606', 		'cd7fde18-15db-4d94-a91b-7cf8edd81209','ab5be70d-9de0-44ea-80ce-52fd6f34a5b7','06b8702c-a5e3-451b-bb04-715d0913e6b2',
	'37919a20-f9b4-4c6c-b255-460972803546','44798190-b837-47e1-881e-fdc6f733f43b',
	'a2b6c187-3068-40a0-a4fe-7979cc918ebb','932e03be-1e62-4b41-babd-338f6b90af99');
	protected $ProxyUrl = '217.27.151.75:34935';
    protected $CurrentAppId = null;

    public function __construct($pUser, $pPassword) {
        $this->User = $pUser;
        $this->Password = $pPassword;
        if (empty($pUser) || empty($pPassword)) {
            throw new \Exception("Creating epay api client without login or password");
        };
        $this->setIsHideMainWallet(true);
    }
	
	public function getUserAgent() {
		return $this->UserAgent;
	}
	
	public function getAppId() {
		$vCurrentHour = intval(date('H'));
		$vCurrentAppId = $this->AppId[$vCurrentHour];
		return $vCurrentAppId;
	}
	   
	public function getCurrentAppId() {
		return $this->CurrentAppId;
	} 

    public function setCurrentAppId($value) {
        $this->CurrentAppId = $value;        
    }

	
	public function getProxyUrl() {
		return $this->ProxyUrl;
	} 

    public function setProxyUrl($value) {
        $this->ProxyUrl = $value;        
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
        if (!empty($this->inExpires)) {
            $currentDT = new \DateTime("now") ;
            $expiresDT = new \DateTime($this->inExpires);
            if ($currentDT <  $expiresDT ) {
                $this->Last_error = null;
                $result = true;
            }
        }
        return $result;
    }

    public function setIsHideMainWallet($isHideMainWallet) {
        $this->isHideMainWallet = $isHideMainWallet;
    }

    public function getWallets() {
        $result = false;
        try {
            $vAuth = \sprintf('%s %s', $this->Token_type, $this->Access_token);
            $client = new \GuzzleHttp\Client(['http_errors' => false,'base_uri' => self::base_url]);
            $vReqId = $this->getRequestedSessionId();
            $vPageId = $this->getPageId();
            $response = $client->request('GET', '/api/wallets/get', [
                'headers' => ['User-Agent' => $this->getUserAgent(), 'Accept' => 'application/json',
                    'AppId' => $this->getCurrentAppId(), 'Authorization' => $vAuth,
                    'PartnerKey' => self::PartnerKey, 'RequestedSessionId' => $vReqId,
                    'PageId' => $vPageId, 'Locale' => 'Ua']]);
            $code = $response->getStatusCode();
            if ($code === 200) {
                $this->processResponse($response);
                $result = true;
            }
        } catch (Exception $e) {
            $this->Last_error = $e->getMessage();
        }
        return $result;
    }
    
    public function getWalletByInstrumentId($pInstrumentId)    {
        $result= $this->actionGetWallets();
        if ($result) {
            foreach ($this->Wallets as $value) {
            if ($value['instrumentId'] === $pInstrumentId) {
                $result = $value;
                    break;
                }
            }
        }
        return $result;
    }
            
    private function addWallet($pWalletName) {
        $result = false;
        try {
            //$payload = \sprintf('color=#D7CCC8&name="%s"', $pWalletName);
            $vAuth = \sprintf('%s %s', $this->Token_type, $this->Access_token);
            $client = new \GuzzleHttp\Client(['http_errors' => false,'base_uri' => self::base_url]);
            $vReqId = $this->getRequestedSessionId();
            $vPageId = $this->getPageId();
            $response = $client->request('POST', '/api/wallets/add', [
                \GuzzleHttp\RequestOptions::JSON => ['color' => '#D7CCC8', 'name' => $pWalletName ],
                'headers' => ['User-Agent' => $this->getUserAgent(), 'Accept' => 'application/json',
                    'AppId' => $this->getCurrentAppId(), 'Authorization' => $vAuth,
                    'PartnerKey' => self::PartnerKey, 'RequestedSessionId' => $vReqId,
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
                    $this->Last_error = $addResult["error"];
                };
            }
        } catch (Exception $e) {
            $this->Last_error = $e->getMessage();
        }
        return $result;
    }
   
    public function actionNewWallet($pNewWalletName) {
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
            $client = new \GuzzleHttp\Client(['http_errors' => false,'base_uri' => self::base_url]);
            $vReqId = $this->getRequestedSessionId();
            $vPageId = $this->getPageId();
            $response = $client->request('POST', '/api/token', [
                'body' => $payload,
                'headers' => ['User-Agent' => $this->getUserAgent(), 'Accept' => 'application/json',
                    'AppId' => $this->getCurrentAppId(), 'No-Authentication' => true,
                    'PartnerKey' => self::PartnerKey, 'RequestedSessionId' => $vReqId,
                    'PageId' => $vPageId, 'Locale' => 'Ua'], 'proxy' => $this->getProxyUrl(), 
			
            ]);
            $code = $response->getStatusCode();
            if ($code === 200) {
                $this->processResponse($response);
                $this->Last_error = null;
                $result = true;
            }
        } catch (\GuzzleHttp\Exception\RequestException $gexc) {
            $this->Last_error = \sprintf('Error on getting token:%s', $gexc->getMessage());
        }
        return $result;
    }

    public function getSession() {
        $result = false;
        if ($this->createAppId()){
            try {
                $client = new \GuzzleHttp\Client(['http_errors' => false,'base_uri' => self::base_url]);
                $response = $client->request('POST', '/api/system/createSession', [
                    'headers' => ['User-Agent' => $this->getUserAgent(), 'Accept' => 'application/json', 
    				'AppId' => $this->getCurrentAppId()]]);
                $code = $response->getStatusCode();
                if ($code === 200) {
                    $this->processResponse($response);
                    $this->Last_error = null;
                    $result = true;
                }
            } catch (\GuzzleHttp\Exception\RequestException $gexc) {
                    $this->Last_error = \sprintf('Error getting session:%s', $gexc->getMessage());
            }
        };
        return $result;
    }

    public function createAppId() {
        $result = !empty($this->getCurrentAppId());
        if (!$result) {
            try {
                $client = new \GuzzleHttp\Client(['http_errors' => false,'base_uri' => self::base_url]);
                $response = $client->request('POST', '/api/system/createApp', [
                    'headers' => ['User-Agent' => $this->getUserAgent(), 
                    'Content-Type' => 'application/json'  ,'Accept' => 'application/json' ]]);
                $code = $response->getStatusCode();
                if ($code === 200) {
                    $this->processResponse($response);
                    $this->Last_error = null;
                    $result = true;
                }
            } catch (\GuzzleHttp\Exception\RequestException $gexc) {
                    $this->Last_error = \sprintf('Error creating appid:%s', $gexc->getMessage());
            };
        };
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
                case 'appId':
                    $this->CurrentAppId = $value;
                    break;
                case 'requestedSessionId':
                    $this->RequestedSessionId = $value;
                    break;
                case 'pageId':
                    $this->PageId = $value;
                    break;
                case 'access_token':
                    $this->Access_token = $value;
                    break;
                case 'token_type':
                    $this->Token_type = $value;
                    break;
                case 'expires_in':
                    $this->Expires = $value;
                    $this->_localExpires = time() + $value;
                    break;
                case 'refresh_token':
                    $this->Refresh_token = $value;
                    break;
                case 'userId':
                    $this->UserId = $value;
                    break;
                case 'client_id':
                    $this->ClientId = $value;
                    break;
                case '.issued':
                    $this->inIssued = $value;
                    break;
                case '.expires':
                    $this->inExpires = $value;
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
    
    public function actionGetWalletBalanceByAddress($walletAddress) {
        $result = false;
        if ($this->actionGetWallets()) {
            foreach ($this->Wallets as $cWallet) {
                if ($cWallet['number'] === $walletAddress ) {
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

    public function getLast_error() {
        return $this->Last_error;
    }

    public function setLast_error($Last_error) {
        $this->Last_error = $Last_error;
    }

}