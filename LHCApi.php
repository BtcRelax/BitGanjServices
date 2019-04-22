<?php
namespace BtcRelax;

class lhSecurity {

    public static $method = 'AES-256-CBC';
    
    
    public static function encrypt(string $data, string $key) : string
    {
        $ivSize = openssl_cipher_iv_length(self::$method);
        $iv = openssl_random_pseudo_bytes($ivSize);

        $encrypted = openssl_encrypt($data, self::$method, $key, OPENSSL_RAW_DATA, $iv);

        // For storage/transmission, we simply concatenate the IV and cipher text
        $encrypted = base64_encode($iv . $encrypted);

        return $encrypted;
    }

  
    public static function decrypt(string $data, string $key) : string
    {
         
         
        
        $data = base64_decode($data);
        $ivSize = openssl_cipher_iv_length(self::$method);
        $iv = substr($data, 0, $ivSize);
        $data = openssl_decrypt(substr($data, $ivSize), self::$method, $key, OPENSSL_RAW_DATA, $iv);

        return $data;


    }
}

/**
 * Description of LHCApi
 *
 * @author god
 */
class LHCApi {
    protected $Core;
    protected $CurrentSession = null;
    protected $User = null;
    protected $LastError = null;
    protected $Department = null;
    protected $OrderId = null;
	protected $ThemeId = null;
	
    public function __construct() {
        global  $core;
        $this->Core = $core;
		$this->Department = LHC_DEFAULT_DEPARTMENT;
		$this->ThemeId = LHC_DEFAULT_THEME_ID;
    }

    public function getOrderId() {
        return $this->OrderId;
    }
    
	public function setOrderId($OrderId) {
        $this->OrderId = $OrderId;
    }
       
    
    public function getDepartment() {
        return $this->Department;
    }
    public function setDepartment($Department) {
        $this->Department = $Department;
    }
        
    public function getCurrentSession(): \BtcRelax\SecureSession {
        if (is_null($this->CurrentSession))
        { $this->CurrentSession = $this->Core->getCurrentSession(); }
        return $this->CurrentSession;
    }

    public function getUser() {
        if (empty($this->User)) { $this->User = $this->getCurrentSession()->getValue('CurrentUser'); }
        return $this->User;
    }

    public function getLastError() { return $this->LastError; }

    public function setLastError($LastError) { $this->LastError = $LastError; }

    public function getOfferScript() {
        $result = \sprintf("<script type=\"text/javascript\">var LHCBROWSEOFFEROptions = {domain:'shop.bitganj.website'}; (function() {" 
            . "var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;"
            . "var referrer = (document.referrer) ? encodeURIComponent(document.referrer.substr(document.referrer.indexOf('://')+1)) : '';"
            . "var location  = (document.location) ? encodeURIComponent(window.location.href.substring(window.location.protocol.length)) : '';"
            . "po.src = '%s/index.php/rus/browseoffer/getstatus/(size)/450/(height)/450/(units)/pixels/(timeout)/1/(showoverlay)/true/(canreopen)/false?r='+referrer+'&l='+location;"
            . "var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);})();</script>", LHC_URL );
        return $result;
    }
    
        //put your code here
    public function getWidgetScript() {
        $vCurrentSession = $this->getCurrentSession(); $script = "";
        $vIsHasLHCAccount = $vCurrentSession->getValue('isHasLHCAccount');
        $vCurrentUser = $this->getUser();
        if ($vIsHasLHCAccount !== true) {
            if ($vCurrentUser instanceof \BtcRelax\Model\User) {
                $vIdCustomer = $vCurrentUser->getIdCustomer();
                if (!empty($vIdCustomer)) {
                    $vIdUserName = $vCurrentUser->getPropertyValue("alias_nick");
                    $script = $this->fillChatWidget($vIdCustomer, LHC_ENCRYPT , $vIdUserName, LHC_URL);
                    }   
                }
            }
        if (empty($script)) { $script = $this->fillFAQWidget('main', LHC_URL ); }
        return $script ;
    }
    public function fillFAQWidget(string $vIdentifier, string $vServerUrl ) {
        return \sprintf("<script type=\"text/javascript\">
                    var LHCFAQOptions = {status_text:'Вопросы?',url:'',identifier:'%s'};
                    (function() { var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true; 
                    po.src = '%s/index.php/rus/faq/getstatus/(position)/bottom_right/(top)/450/(units)/pixels/(theme)/2';
                    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
                    })();</script>", $vIdentifier, $vServerUrl  );
    }
	
	private function getPaidChat()
	{
		$vResult = "";
		if (is_int($this->getOrderId()))
		{
			$vOrderId = $this->getOrderId();
			$vSecretValidationHash = 'e3874f6dc621efff146b0db55935fb27c4b5a0e8';
			$vOrderIdHash = \sha1($vSecretValidationHash.sha1($vSecretValidationHash.$vOrderId));
			$vResult = \sprintf("LHCChatOptions.attr_paid = {phash:'%s',pvhash:'%s'};", $vOrderId, $vOrderIdHash );
		}
		return $vResult;
	}
	
    public function fillChatWidget($vIdCustomer, $vIsEncrypt , $vIdUserName , $vServerUrl ) {
       $vNeedEncrypt = boolval($vIsEncrypt);
	   $vPaidChat = $this->getPaidChat;
	//$vDepartmentUrl = $this->getDepartment() === true ? "/(department)/" . $this->getDepartment()  : "";
	   $vDepartmentUrl = "/(department)/" . $this->getDepartment() ;        
       $vIdCustomerTitle  = $vNeedEncrypt === true ? \BtcRelax\lhSecurity::encrypt($vIdCustomer,'d-fD_f90sF_Sdf0sdf_SDFSDF)SDF_SDF_SD)F_F') : $vIdCustomer  ; 
       $script = \sprintf("<script type=\"text/javascript\">var LHCChatOptions = {};
                            LHCChatOptions.opt = {widget_height:340,widget_width:300,popup_height:520,popup_width:500,domain:'bitganj.website'};
                            LHCChatOptions.attr = new Array();
                            LHCChatOptions.attr.push({'name':'CustomerId','value':'%s','type':'hidden','size':0,'encrypted':%s});
                            LHCChatOptions.attr_prefill = new Array();
                            LHCChatOptions.attr_prefill.push({'name':'username','value':'%s'});
							%s
							LHCChatOptions.attr_online = new Array();
							LHCChatOptions.attr_online.push({'name':'CustomerId','value':'%s'});
							LHCChatOptions.attr_online.push({'name':'username','value':'%s'});

                            (function() { var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
                            var referrer = (document.referrer) ? encodeURIComponent(document.referrer.substr(document.referrer.indexOf('://')+1)) : '';
                            var location  = (document.location) ? encodeURIComponent(window.location.href.substring(window.location.protocol.length)) : '';
                            po.src = '%s/index.php/rus/chat/getstatus/(click)/internal/(position)/bottom_right/(ma)/br/(hide_offline)/true/(top)/350/(units)/pixels%s/(theme)/2?r='+referrer+'&l='+location;
                            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);})();</script>", 
                            $vIdCustomerTitle,$vNeedEncrypt ,$vIdUserName ,$vPaidChat ,$vIdCustomer ,$vIdUserName ,$vServerUrl, $vDepartmentUrl);    
        return $script ;
    }
    
    public function getMenuItemForUser(\BtcRelax\Model\User $vUser) {
        $result = $vUser->getPropertyValue("lhc_uid");        
        if (FALSE !== $result) {
        $params = ['r' => 'chat/chattabs', 'u' => $result ,  'secret_hash' => LHC_SHASH];
        $vGeneratedLink = $this->generateAutoLoginLink($params);
        $vUrl = \sprintf("<a target=\"_blank\" href=\"%s/%s\">Помощь</a>",LHC_URL,$vGeneratedLink ); }
        return $result !== false? $vUrl: $result ;
    }
    
    function generateAutoLoginLink($params){
        $vCurrentSession = $this->getCurrentSession(); $vCurrentSession->setValue('isHasLHCAccount', true);
        $dataRequest = array();$dataRequestAppend = array();
        // Destination ID
        if (isset($params['r'])){
            $dataRequest['r'] = $params['r']; $dataRequestAppend[] = '/(r)/'.rawurlencode(base64_encode($params['r']));
        }
        // User ID
        if (isset($params['u']) && is_numeric($params['u'])){
            $dataRequest['u'] = $params['u']; $dataRequestAppend[] = '/(u)/'.rawurlencode($params['u']);
        }
        // Username
        if (isset($params['l'])){
            $dataRequest['l'] = $params['l']; $dataRequestAppend[] = '/(l)/'.rawurlencode($params['l']);
        }
        if (!isset($params['l']) && !isset($params['u'])) {
         throw new Exception('Username or User ID has to be provided');
        }
        // Expire time for link
        if (isset($params['t'])){
         $dataRequest['t'] = $params['t'];
         $dataRequestAppend[] = '/(t)/'.rawurlencode($params['t']);
        }
        $hashValidation = sha1($params['secret_hash'].sha1($params['secret_hash'].implode(',', $dataRequest)));
        return "index.php/user/autologin/{$hashValidation}".implode('', $dataRequestAppend);
    }
}


