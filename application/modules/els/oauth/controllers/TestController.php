<?php
class Oauth_TestController extends HM_Controller_Action
{
    private $apiKey;
    private $config;

    public function init()
    {
        parent::init();

        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->_helper->getHelper('viewRenderer')->setNoRender();


        $this->apiKey = 'SM5sYrVwBdrAHcUFPoNtTncfZnmJZ5nk';
        $this->config = array(
            'callbackUrl' => $this->view->serverUrl($this->view->url(
                array('module' => 'oauth', 'controller' => 'test', 'action' => 'callback'),
                null,
                true
            )),
            'siteUrl' => $this->view->serverUrl('/oauth/v1'),
            //'requestTokenUrl' => $this->view->serverUrl('/oauth/v1/request_token'),
            //'accessTokenUrl' => $this->view->serverUrl('/oauth/v1/access_token'),
            //'authorizeUrl' => $this->view->serverUrl('/oauth/v1/authorize'),
            'consumerKey' => '05de6544c1a11e4dd2cb6c9e9e895e',
            'consumerSecret' => '4931f8dbdf',
            //'requestScheme' => Zend_Oauth::REQUEST_SCHEME_QUERYSTRING
        );
    }

    public function indexAction()
    {
        die();
        pr($this->config);

        $consumer = new Zend_Oauth_Consumer($this->config);

        $token = $consumer->getRequestToken();

        echo sprintf('Request token: %s<br/>', $token);

        echo 'test completed';

        $_SESSION['ELS_REQUEST_TOKEN'] = serialize($token);

        $consumer->redirect();
    }

    public function callbackAction()
    {
        die();
        pr($this->config);
        $consumer = new Zend_Oauth_Consumer($this->config);

        echo 'callback';

        pr($_GET);

        echo sprintf('Request token: %s<br/>', $this->_getParam('oauth_token'));
        echo sprintf('Verifier: %s<br/>', $this->_getParam('oauth_verifier'));

        pr(unserialize($_SESSION['ELS_REQUEST_TOKEN']));

        if (!empty($_GET) && isset($_SESSION['ELS_REQUEST_TOKEN'])) {
            $token = $consumer->getAccessToken($_GET, unserialize($_SESSION['ELS_REQUEST_TOKEN']));
            pr($token);
            $_SESSION['ELS_ACCESS_TOKEN'] = serialize($token);

            $_SESSION['ELS_REQUEST_TOKEN'] = null;

            $client = $token->getHttpClient($this->config);
            $client->setUri('http://4ggit/rest/users/-1');
            $client->setMethod(Zend_Http_Client::GET);
            $response = $client->request();

            pr($response);

        } else {
            echo 'Invalid callback request. Oops. Sorry.';
        }
    }
}