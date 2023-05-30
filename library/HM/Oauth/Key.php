<?php
class HM_Oauth_Key
{

    public $id = null;
    public $key = null;
    public $secret = null;

    public function __construct($key, $secret, $id)
    {
        $this->key = $key;
        $this->secret = $secret;
        $this->id = $id;
    }

    static public function fetchKey($consumerKey, $serviceContainer)
    {
        $app = $serviceContainer->getService('OauthApp')->getOne($serviceContainer->getService('OauthApp')->fetchAll(
            $serviceContainer->getService('OauthApp')->quoteInto('consumer_key = ?', $consumerKey)
        ));

        if ($app) {
            return new HM_Oauth_Key($app->consumer_key, $app->consumer_secret, $app->app_id);
        }

        return false;
    }

}
