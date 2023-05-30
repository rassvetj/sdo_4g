<?php
class HM_Oauth_Server
{
    /**
   	 * OAuth token
   	 * @var OAuthToken
   	 */
   	protected $token;

    protected $serviceContainer;

    public function setServiceContainer($container)
    {
        $this->serviceContainer = $container;
    }

   	/**
   	 * Check if everything is OK
   	 * @throws OAuthException
   	 */
   	protected function check()
   	{
   		if(!function_exists('mhash') && !function_exists('hash_hmac')) {
   			// define exception class
   			throw new Zend_OAuth_Exception("MHash extension required for OAuth support");
   		}
   	}

   	/**
   	 * Is this functionality enabled?
   	 */
   	public static function enabled()
   	{
   		return function_exists('mhash') || function_exists('hash_hmac');
   	}

   	/**
   	 * Find consumer by key
   	 * @param $provider
   	 */
   	public function lookupConsumer($provider)
   	{
   		// check $provider->consumer_key
   		// on unknown: HM_Oauth_Provider::CONSUMER_KEY_UNKNOWN
   		// on bad key: HM_Oauth_Provider::CONSUMER_KEY_REFUSED
   		// TODO: OAuthKey::fetchKey gets consumer object that has
   		// secret and key properties
   		$consumer = HM_Oauth_Key::fetchKey($provider->consumer_key, $this->serviceContainer);
   		if(!$consumer) {
   			return HM_Oauth_Provider::CONSUMER_KEY_UNKNOWN;
   		}
   		$provider->consumer_secret = $consumer->secret;
   		$this->consumer = $consumer;
   		return HM_Oauth_Provider::OK;
   	}

   	/**
   	 * Check timestamps & nonces
   	 * @param OAuthProvider $provider
   	 */
   	public function timestampNonceChecker($provider)
   	{
   		if(empty($provider->nonce)) {
   			return HM_Oauth_Provider::BAD_NONCE;
   		}
   		if(empty($provider->timestamp)) {
   			return HM_Oauth_Provider::BAD_TIMESTAMP;
   		}
   		// TODO: checkNonce verisifes that:
   		// 1. No nonce with time after given nonce exists
   		// 2. Current nonce is not listed in the database
   		// 3. If it's ok, returns HM_Oauth_Provider::OK
   		return HM_Oauth_Token::checkNonce($provider->consumer_key, $provider->nonce, $provider->timestamp, $this->serviceContainer);
   	}

   	/**
   	 * Vefiry incoming token
   	 * @param OAuthProvider $provider
   	 */
   	public function tokenHandler($provider)
   	{
   		// TODO: returns token object by token ID, with properties:
   		// consumer, id, secret, verify, tstate
   		// tstate is one of REQUEST, ACCESS, INVALID
   		$token = HM_Oauth_Token::load($provider->token, $this->serviceContainer);
   		if(empty($token)) {
   			return HM_Oauth_Provider::TOKEN_REJECTED;
   		}
   		if($token->consumer != $this->consumer->id) {
   			return HM_Oauth_Provider::TOKEN_REJECTED;
   		}
   		if($token->tstate == HM_Oauth_Token::REQUEST) {
   			if(!empty($token->verify) && $provider->verifier == $token->verify) {
   				$provider->token_secret = $token->secret;
   				$this->token = $token;
   				return HM_Oauth_Provider::OK;
   			} else {
   				return HM_Oauth_Provider::TOKEN_USED;
   			}
   		}
   		if($token->tstate == HM_Oauth_Token::ACCESS) {
   			$provider->token_secret = $token->secret;
   			$this->token = $token;
   			return HM_Oauth_Provider::OK;
   		}
   		return HM_Oauth_Provider::TOKEN_REJECTED;
   	}

   	/**
   	* Assemble parameters from POST and GET
   	*/
   	protected function assembleData()
   	{
   		$data = $_GET;
   		$data = array_merge($data, $_POST);
   		return $data;
   	}

   	/**
   	 * Create OAuth provider
   	 *
   	 * Checks current request for OAuth valitidy
   	 * @param bool $add_rest add REST endpoint as request path
   	 */
   	public function __construct($req_path = '', $serviceContainer = null)
   	{
        $this->serviceContainer = $serviceContainer;
   		$this->check();
   		$this->provider = new HM_Oauth_Provider();
   		$this->provider->setConsumerHandler(array($this,'lookupConsumer'));
   		$this->provider->setTimestampNonceHandler(array($this,'timestampNonceChecker'));
   		$this->provider->setTokenHandler(array($this,'tokenHandler'));
   		if(!empty($req_path)) {
   			$this->provider->setRequestTokenPath($req_path);  // No token needed for this end point
   		}
   		$this->provider->checkOAuthRequest(null, $this->assembleData());
   		if(mt_rand() % 10 == 0) {
   			// TODO: cleanup old tokens and nonces
            HM_Oauth_Token::cleanup($this->serviceContainer);
   		}
   	}

   	/**
   	 * Generate request token string
   	 * @return string
   	 */
   	public function requestToken()
   	{
   		// TODO: create a new token string with token/secret pair
   		$token = new HM_Oauth_Token(self::generateToken(), self::generateToken());
        $token->setServiceContainer($this->serviceContainer);
   		// TODO: set consumer key
   		$token->setConsumer($this->consumer);
   		// TODO: save token
   		$token->save();

   		return "oauth_token={$token->token}&oauth_token_secret={$token->secret}";
   	}

   	public static function generateToken()
   	{
   		return HM_Oauth_Provider::generateToken(32);
   	}

   	/**
   	 * Generate access token string - must have validated request token
   	 * @return string
   	 */
   	public function accessToken()
   	{
   		if(empty($this->token) || $this->token->tstate != HM_OAuth_Token::REQUEST) {
   			return null;
   		}
   		// TODO: invalidate request token
   		$this->token->invalidate();
   		// TODO: create a new token string with token/secret pair
   		$token = new HM_Oauth_Token(self::generateToken(), self::generateToken());
        $token->setServiceContainer($this->serviceContainer);
   		// TODO: set token state
   		$token->setState(HM_OAuth_Token::ACCESS);
   		// TODO: set consumer key
   		$token->setConsumer($this->consumer);
   		// TODO: transfer authorization data from request token
   		$token->copyAuthData($this->token);
   		// TODO: save token
   		$token->save($this->serviceContainer);
   		return "oauth_token={$token->token}&oauth_token_secret={$token->secret}";
   	}

   	/**
   	 * Return authorization URL
   	 * @return string
   	 */
   	public function authUrl()
   	{
   		// TODO: here goes the authorization URL
   		return $this->serverUrl('/oauth/v1/authorize');
   	}

   	/**
   	 * Fetch current token if it is authorized
   	 * @return OAuthToken|null
   	 */
   	public function authorizedToken()
   	{
   		if($this->token->tstate == HM_Oauth_Token::ACCESS) {
   			return $this->token;
   		}
   		return null;
   	}

    public function authorizedUserId()
    {
        $token = $this->authorizedToken();
        if ($token && $token->authdata){
            return $token->authdata->MID;
        }
    }

    public function authorizedUser()
    {
        $token = $this->authorizedToken();
        if ($token && $token->authdata){
            return $token->authdata;
        }
    }

   	/**
   	 * Fetch authorization data from current token
   	 * @return mixed Authorization data or null if none
   	 */
   	public function authorization()
   	{
   		if($this->token->tstate == HM_Oauth_Token::ACCESS) {
   			return $this->token->authdata;
   		}
   		return null;
   	}

   	/**
   	 * Report OAuth problem as string
   	 */
   	static public function reportProblem(Exception $e)
   	{
   		return HM_Oauth_Provider::reportProblem($e);
   	}

   	/**
   	 * Authorize token for access
   	 * Note that this request is not OAuth-authenticated - this should be called
   	 * from regularly authenticated session
   	 * @param string $token_str Token string
   	 * @param mixed $authdata Authorization data to be attached to the token, such as user ID and permissions
   	 * @return string|false verifier value on success (should be sent to the client), false on failure
   	 */
   	static public function authorizeToken($token_str, $authdata, $serviceContainer = null)
   	{
   		// TODO: load token object
   		$token = HM_Oauth_Token::load($token_str, $serviceContainer);
   		if(empty($token) || empty($token->consumer) || $token->tstate != HM_Oauth_Token::REQUEST) {
   			return false;
   		}
   		$token->authdata = $authdata;
   		$token->verify = self::generateToken();
   		$token->save();
   		return $token->verify;
   	}
}