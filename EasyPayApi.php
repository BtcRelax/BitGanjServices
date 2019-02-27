<?php namespace BtcRelax;
require_once ('vendor/autoload.php');


class EasyPayApi {

    const base_url = 'https://api.easypay.ua/';
    const AppId = 'c954eff2-9779-4ade-8723-c4daa7bec606';
    const UserAgent = 'okhttp/3.9.0';
    const PartnerKey = 'easypay-v2-android';

    public $RequestedSessionId;
    public $PageId;
    public $Last_error = '';
    public $User;
    public $Password;
    public $Access_token;
    public $Token_type;
    public $Expires;
    public $Refresh_token;
    public $UserId;
    public $ClientId;
    public $inIssued;
    public $inExpires;
    public $Wallets;
    public $_isHideMainWallet = false;
    public $_localExpires;

    public function __construct($pUser, $pPassword) {
        $this->User = $pUser;
        $this->Password = $pPassword;
        if (empty($pUser) || empty($pPassword)) {
            throw new \BtcRelax\Exception\AuthentificationCritical("Creating epay api client without login or password");
        }
        $this->setIsHideMainWallet(true);
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
            $client = new \GuzzleHttp\Client(['base_uri' => self::base_url]);
            $vReqId = $this->getRequestedSessionId();
            $vPageId = $this->getPageId();
            $response = $client->request('GET', '/api/wallets/get', [
                'headers' => ['User-Agent' => self::UserAgent, 'Accept' => 'application/json',
                    'AppId' => self::AppId, 'Authorization' => $vAuth,
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
            $vAuth = \sprintf('%s %s', $this->Token_type, $this->Access_token);
            $client = new \GuzzleHttp\Client(['base_uri' => self::base_url]);
            $vReqId = $this->getRequestedSessionId();
            $vPageId = $this->getPageId();
            $response = $client->request('POST', '/api/wallets/add', [
                \GuzzleHttp\RequestOptions::JSON => ['color' => '#D7CCC8', 'name' => $pWalletName ],
                'headers' => ['User-Agent' => self::UserAgent, 'Accept' => 'application/json',
                    'AppId' => self::AppId, 'Authorization' => $vAuth,
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

//    public function actionAddWallet($pWalletName) {
//        $initResult = $this->init();
//        if ($initResult) {
//            $initResult = $this->addWallet($pWalletName);
//        }
//        return $initResult;
//    }
    
    
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
            $client = new \GuzzleHttp\Client(['base_uri' => self::base_url]);
            $vReqId = $this->getRequestedSessionId();
            $vPageId = $this->getPageId();
            $response = $client->request('POST', '/api/token', [
                'body' => $payload,
                'headers' => ['User-Agent' => self::UserAgent, 'Accept' => 'application/json',
                    'AppId' => self::AppId, 'No-Authentication' => true,
                    'PartnerKey' => self::PartnerKey, 'RequestedSessionId' => $vReqId,
                    'PageId' => $vPageId, 'Locale' => 'Ua']]);
            $code = $response->getStatusCode();
            if ($code === 200) {
                $this->processResponse($response);
                $this->Last_error = null;
                $result = true;
            }
        } catch (\GuzzleHttp\Exception\RequestException $gexc) {
            //\BtcRelax\Log::general($gexc, \BtcRelax\Log::ERROR);
                //$this->response(\sprintf('Child request error:%s', $gexc->getMessage() ),$gexc->getCode() );
                $this->Last_error = \sprintf('Error on getting token:%s', $gexc->getMessage());
        }
        //catch (Exception $e) {
        //    $this->Last_error = $e->getMessage();
        //}
        return $result;
    }

    public function getSession() {
        $result = false;
        try {
            $client = new \GuzzleHttp\Client(['base_uri' => self::base_url]);
            $response = $client->request('POST', '/api/system/createSession', [
                'headers' => ['User-Agent' => self::UserAgent,
                    'Accept' => 'application/json', 'AppId' => self::AppId]]);
            $code = $response->getStatusCode();
            if ($code === 200) {
                $this->processResponse($response);
                $this->Last_error = null;
                $result = true;
            }
        } catch (\GuzzleHttp\Exception\RequestException $gexc) {
            //\BtcRelax\Log::general($gexc, \BtcRelax\Log::ERROR);
                //$this->response(\sprintf('Child request error:%s', $gexc->getMessage() ),$gexc->getCode() );
                $this->Last_error = \sprintf('Error getting session:%s', $gexc->getMessage());
        }
        //catch (\GuzzleHttp\Exception $e) {
        //    $this->Last_error = $e->getMessage();
        //}
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

    public function getLast_error() {
        return $this->Last_error;
    }

    public function setLast_error($Last_error) {
        $this->Last_error = $Last_error;
    }

}
