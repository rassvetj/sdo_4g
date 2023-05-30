<?php
class HM_Oauth_Token
{
    const REQUEST = 0;
    const ACCESS = 1;
    const INVALID = -1;

    public $id = null;
    public $token = '';
    public $secret = '';
    public $verify = '';
    public $tstate = -1;
    public $authdata = '';

    public $consumer = null; // consumer id

    private $_serviceContainer = null;

    public function __construct($token, $secret, $id = null, $verify = '', $state = 0, $consumer = null)
    {
        $this->token  = $token;
        $this->secret = $secret;

        $this->id     = $id;
        $this->verify = $verify;
        $this->tstate  = $state;

        $this->consumer = $consumer;
    }

    public function setServiceContainer($serviceContainer)
    {
        $this->_serviceContainer = $serviceContainer;
    }

    public function setConsumer($consumer)
    {
        $this->consumer = $consumer->id;
    }

    public function isRequest()
    {
        return ($this->tstate == self::REQUEST);
    }

    public function setState($state)
    {
        $this->tstate = $state;
    }

    public function copyAuthData($token)
    {
        $this->authdata = $token->authdata;
    }

    public function save()
    {
        $user_id = 0;

        if ($this->authdata && isset($this->authdata->MID)) {
            $user_id = $this->authdata->MID;
        }

        if ($this->id) {
            // update

            return $this->_serviceContainer->getService('OauthToken')->update(
                array(
                    'token_id'     => $this->id,
                    'app_id'       => $this->consumer,
                    'token'        => $this->token,
                    'token_secret' => $this->secret,
                    'state'        => $this->tstate,
                    'verify'       => $this->verify,
                    'user_id'      => $user_id
                )
            );
        } else {
            // insert
            return $this->_serviceContainer->getService('OauthToken')->insert(
                array(
                    'app_id'       => $this->consumer,
                    'token'        => $this->token,
                    'token_secret' => $this->secret,
                    'state'        => $this->tstate,
                    'verify'       => $this->verify,
                    'user_id'      => $user_id
                )
            );
        }
    }

    static public function checkNonce($consumerKey, $nonce, $timestamp, $serviceContainer)
    {
        if ($timestamp < time() - 5*60) {
            return HM_Oauth_Provider::BAD_TIMESTAMP;
        }

        $consumer = $serviceContainer->getService('OauthApp')->getOne(
            $serviceContainer->getService('OauthApp')->fetchAll($serviceContainer->getService('OauthApp')->quoteInto('consumer_key = ?', $consumerKey))
        );

        if ($consumer) {
            $collection = $serviceContainer->getService('OauthNonce')->fetchAll(
                $serviceContainer->getService('OauthNonce')->quoteInto(
                    array('app_id = ?', ' AND nonce = ?', ' AND ts = ?'),
                    array($consumer->app_id, $nonce, $serviceContainer->getService('OauthNonce')->getDateTime($timestamp))
                )
            );

            if (count($collection)) {
                return HM_Oauth_Provider::BAD_NONCE;
            }

            $nonce = $serviceContainer->getService('OauthNonce')->insert(
                array(
                    'app_id' => $consumer->app_id,
                    'ts' => $serviceContainer->getService('OauthNonce')->getDateTime(),
                    'nonce' => $nonce
                )
            );

            if ($nonce) {
                return HM_Oauth_Provider::OK;
            }
        }
    }

    static public function load($token, $serviceContainer)
    {
        $item = $serviceContainer->getService('OauthToken')->getOne(
            $serviceContainer->getService('OauthToken')->fetchAll(
                $serviceContainer->getService('OauthToken')->quoteInto('token = ?', $token)
            )
        );

        if ($item) {
            $token = new HM_Oauth_Token($item->token, $item->token_secret, $item->token_id, $item->verify, $item->state, $item->app_id);
            $token->setServiceContainer($serviceContainer);

            if ($item->user_id) {
                $user = $serviceContainer->getService('User')->getOne(
                    $serviceContainer->getService('User')->find($item->user_id)
                );
                if ($user) {
                    $token->authdata = $user;
                }
            }

            return $token;
        }
        return false;
    }

    public function invalidate()
    {
        if ($this->id) {
            return $this->_serviceContainer->getService('OauthToken')->update(array('token_id' => $this->id, 'state' => self::INVALID));
        }
    }

    static public function cleanup($serviceContainer)
    {

    }
}
