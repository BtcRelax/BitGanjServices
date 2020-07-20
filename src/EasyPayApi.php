<?php 
namespace BtcRelax;
/**
 * EasyPayApi Class 
 *
 * @category Class
 * @package  BitGanjServices
 * @author   godJah
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://github.com/BtcRelax
 */
require_once 'vendor/autoload.php';
/**
 * EasyPayApi Class 
 *
 * @category PaymentProvidersApiClass
 * @package  BitGanjServices
 * @author   godJah <godjah@bitganj.website>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://github.com/BtcRelax
 */
class EasyPayApi
{
    const BASE_URL = 'https://api.easypay.ua/';
    const PARTHNER_KEY = 'easypay-v2-android';

    protected $RequestedSessionId;
    protected $PageId;
    protected $LastError = '';
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
    protected $isHideMainWallet = false;
    protected $localExpires;
    protected $UserAgent = 'okhttp/3.9.0';
    protected $ProxyUrl = '';
    protected $CurrentAppId = null;


    public function __construct($pUser, $pPassword) 
    {
        $this->User = $pUser;
        $this->Password = $pPassword;
        if (empty($pUser) || empty($pPassword)) {
            throw new \Exception("Creating epay api client without login or password");
        };
        $this->setIsHideMainWallet(true);
    }
    
    public function getUserAgent() 
    {
        return $this->UserAgent;
    }

    public function getCurrentAppId() 
    {
        return $this->CurrentAppId;
    } 

    public function setCurrentAppId($value) 
    {
        $this->CurrentAppId = $value;        
    }

    public function getProxyUrl() 
    {
        return $this->ProxyUrl;
    } 

    public function setProxyUrl($value) 
    {
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

    /**
     *  Summ of every wallet balance in current account
     * 
     * @return : number
     */
    public function getTotalBalance() 
    {
        $vResult = 0;
        $this->getWallets();
        foreach ($this->Wallets as $value) {
            $vResult += $value['balance'];
        }
        return $vResult;
    }

    /**
     *  Check are session already initiated?
     *  @result - return true if session started and not expired
     */
    public function isInited() 
    {
        $result = false;
        if (!empty($this->inExpires)) {
            $currentDT = new \DateTime("now");
            $expiresDT = new \DateTime($this->inExpires);
            if ($currentDT <  $expiresDT ) {
                $this->LastError = null;
                $result = true;
            }
        }
        return $result;
    }

    public function setIsHideMainWallet($isHideMainWallet) {
        $this->isHideMainWallet = $isHideMainWallet;
    }

    public function getWallets() 
    {
        $result = false;
        try {
            $vAuth = \sprintf('%s %s', $this->Token_type, $this->Access_token);
            $client = new \GuzzleHttp\Client(['http_errors' => false,'base_uri' => self::BASE_URL]);
            $vReqId = $this->getRequestedSessionId();
            $vPageId = $this->getPageId();
            $response = $client->request('GET', '/api/wallets/get', ['headers' => ['User-Agent' => $this->getUserAgent(), 'Accept' => 'application/json', 'AppId' => $this->getCurrentAppId(), 'Authorization' => $vAuth, 'PartnerKey' => self::PARTHNER_KEY, 'RequestedSessionId' => $vReqId, 'PageId' => $vPageId, 'Locale' => 'Ua', 'proxy' => $this->getProxyUrl() ]]);
            $code = $response->getStatusCode();
            if ($code === 200) {
                $this->processResponse($response);
                $result = true;
            } else { 
                $this->setLastError(\sprintf("Result code:%s - %s", $code, $response->getBody())); 
            };
        } catch (\Exception $e) {
            $this->LastError = $e->getMessage();
        }
        return $result;
    }
    
    public function getWalletByInstrumentId($pInstrumentId)    
    {
        $result= $this->actionGetWallets();
        if ($result) {
            $result = false;
            foreach ($this->Wallets as $value) {
                if ($value['instrumentId'] === $pInstrumentId) {
                    $result = $value;
                    break;
                }
            }
        }
        return $result;
    }
    
    public function getWalletByNumber($pWalletNumber) 
    {
        $result= $this->actionGetWallets();
        if ($result) {
            $result = false;
            foreach ($this->Wallets as $value) {
                if ($value['number'] === $pWalletNumber) {
                    $result = $value;
                    break;
                }
            }
        }
        return $result;        
    } 
            
    public function addWallet($pWalletName) 
    {
        $result = false;
        try {
            $vAuth = \sprintf('%s %s', $this->Token_type, $this->Access_token);
            $client = new \GuzzleHttp\Client(['http_errors' => false,'base_uri' => self::BASE_URL]);
            $vReqId = $this->getRequestedSessionId();
            $vPageId = $this->getPageId();
            $response = $client->request('POST', '/api/wallets/add', [\GuzzleHttp\RequestOptions::JSON => ['color' => '#D7CCC8', 'name' => $pWalletName ],'headers' => ['User-Agent' => $this->getUserAgent(), 'Accept' => 'application/json','AppId' => $this->getCurrentAppId(), 'Authorization' => $vAuth,'PartnerKey' => self::PARTHNER_KEY, 'RequestedSessionId' => $vReqId, 'PageId' => $vPageId, 'Locale' => 'Ua', 'proxy' => $this->getProxyUrl() ]]);
            $code = $response->getStatusCode();
            if ($code === 200) {
                $addResult = $this->processAddResponse($response);
                if (empty($addResult["error"])) {
                    $result = $addResult['instrumentId'];
                } else {
                    $this->LastError = $addResult["error"];
                };
            }
        } catch (\Exception $e) {
            $this->setLastError(\sprintf("Error while creating wallet named:%s with message:%s", $pWalletName, $e->getMessage()));
        }
        return $result;
    }
    
    public function deleteWalletByNumber($pWalletNumber)
    {
        $result = false;
        try {
            $vWallet = $this->getWalletByNumber($pWalletNumber);
            if ($vWallet !== false) {
                $vWalletId = $vWallet['id'];
                $vAuth = \sprintf('%s %s', $this->Token_type, $this->Access_token);
                $client = new \GuzzleHttp\Client(['http_errors' => false,'base_uri' => self::BASE_URL]);
                $vReqId = $this->getRequestedSessionId();
                $vPageId = $this->getPageId();
                $vURI = \sprintf("/api/wallets/delete/%s", $vWalletId);
                $response = $client->request('DELETE', $vURI, ['headers' => ['User-Agent' => $this->getUserAgent(), 'Accept' => 'application/json','AppId' => $this->getCurrentAppId(), 'Authorization' => $vAuth,'PartnerKey' => self::PARTHNER_KEY, 'RequestedSessionId' => $vReqId,'PageId' => $vPageId, 'Locale' => 'Ua', 'proxy' => $this->getProxyUrl()]]);
                $code = $response->getStatusCode();
                if ($code === 200) {
                        $result = true;
                        $this->setLastError();
                } else {
                    $this->setLastError(\sprintf("Error while deleting wallet number:%s with message:%s", $pWalletNumber, $response->getBody())); };              
            } else { $this->setLastError(\sprintf("Cant find instrument id by wallet number:%s",$pWalletNumber)); }
        } catch (\Exception $e) {
            $this->setLastError(\sprintf("Error while deleting wallet number:%s with message:%s", $pWalletNumber, $e->getMessage()));
        }
        return $result;        
    }
   
    public function actionNewWallet($pNewWalletName) {
        $result = false;
        $initResult = $this->init();
        if ($initResult) {
            $vInstrumentId = $this->addWallet($pNewWalletName);
            if ($vInstrumentId !== false) {
                $result = $this->getWalletByInstrumentId($vInstrumentId);
                $this->setLastError();
            };
        };
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
                            <p>Error message:%s</p></div><script>$( function() \{$( \"#dialog\" ).dialog();} );</script>", $this->getLastError()); 
        }
        return $vHtml;        
    }



    public function getToken() {
        $result = false;
        try {
            $payload = \sprintf('client_id=easypay-v2-android&grant_type=password&username=%s&password=%s', $this->User, $this->Password);
            $client = new \GuzzleHttp\Client(['http_errors' => false,'base_uri' => self::BASE_URL]);
            $vReqId = $this->getRequestedSessionId();
            $vPageId = $this->getPageId();
            $response = $client->request('POST', '/api/token', ['body' => $payload, 'headers' => ['User-Agent' => $this->getUserAgent(), 'Accept' => 'application/json', 'AppId' => $this->getCurrentAppId(), 'No-Authentication' => true, 'PartnerKey' => self::PARTHNER_KEY, 'RequestedSessionId' => $vReqId, 'PageId' => $vPageId, 'Locale' => 'Ua', 'proxy' => $this->getProxyUrl() ]]);
            $code = $response->getStatusCode();
            if ($code === 200) {
                $this->processResponse($response);
                $this->LastError = null;
                $result = true;
            } else { $this->LastError = \sprintf("Error on getting token: %s -%s", $code, $response->getBody() ); }
        } catch (\GuzzleHttp\Exception\RequestException $gexc) {
            $this->LastError = \sprintf('Error on getting token:%s', $gexc->getMessage());
        }
        return $result;
    }

    public function getSession() 
    {
        $result = false;
        if ($this->createAppId()){
            try {
                $client = new \GuzzleHttp\Client(['http_errors' => false,'base_uri' => self::BASE_URL]);
                $response = $client->request('POST', '/api/system/createSession', ['headers' => ['User-Agent' => $this->getUserAgent(), 'Accept' => 'application/json', 'AppId' => $this->getCurrentAppId(), 'proxy' => $this->getProxyUrl() ]]);
                $code = $response->getStatusCode();
                if ($code === 200) {
                    $this->processResponse($response);
                    $this->LastError = null;
                    $result = true;
                }
            } catch (\GuzzleHttp\Exception\RequestException $gexc) {
                    $this->LastError = \sprintf('Error getting session:%s', $gexc->getMessage());
            }
        };
        return $result;
    }

    public function createAppId() {
        $result = !empty($this->getCurrentAppId());
        if (!$result) {
            try {
                $client = new \GuzzleHttp\Client(['http_errors' => false,'base_uri' => self::BASE_URL]);
                $response = $client->request('POST', '/api/system/createApp', [ 'headers' => ['User-Agent' => $this->getUserAgent(), 'Content-Type' => 'application/json'  ,'Accept' => 'application/json', 'proxy' => $this->getProxyUrl() ]]);
                $code = $response->getStatusCode();
                if ($code === 200) {
                    $this->processResponse($response);
                    $this->LastError = null;
                    $result = true;
                }
            } catch (\GuzzleHttp\Exception\RequestException $gexc) {
                    $this->LastError = \sprintf('Error creating appid:%s', $gexc->getMessage());
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

    private function processResponse($response) 
    {
        $json = $response->getBody();
        $data = \GuzzleHttp\json_decode($json, true);
        $this->fillAnswer($data);

    }

    private function fillAnswer(array $pParams)
    {
        foreach ($pParams as $key => $value) {
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
                    $this->localExpires = time() + $value;
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
                    $this->setLastError(null);
                    return $result;
                }
            }
        }
        if (FALSE === $result) { $this->setLastError("Wallet not found!"); };
        return $result;
    }

    public function renderGetWallets() {
        if ($this->actionGetWallets()) {
            $vHtml = "<table border = \"1\" width = \"1\" cellspacing = \"3\" cellpadding = \"3\"><thead>";
            $vHtml .= "<tr><th>Тип кошелька</th><th>Instrument Id</th><th>Название кошелька</th><th>Номер кошелька</th><th>Балланс кошелька</th></tr></thead><tbody>";
            $vRows = "";
            foreach ($this->Wallets as $value) {
                if ($this->isHideMainWallet && $value['walletType'] !== 'Current') {
                    $vRows .= \sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>', $value['walletType'] === 'Current' ? 'Основной' : 'Дополнительный', $value['instrumentId'] , $value['name'], $value['number'], $value['balance']);
                };
            };
            $vHtml .= \sprintf('%s</tbody></table>', $vRows);
        } else {
            $vHtml = \sprintf("Произошла ошибка:%s", $this->getLastError());
        };
        return $vHtml;
    }

    protected function getRequestedSessionId() 
    {
        return $this->RequestedSessionId;
    }

    protected function getPageId() 
    {
        return $this->PageId;
    }

    public function getLastError() 
    {
        return $this->LastError;
    }

    public function setLastError($pMessage = null) {
        $this->LastError = $pMessage;
    }

}