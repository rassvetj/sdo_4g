<?php
class Oauth_V1Controller extends HM_Controller_Action
{

    /**
     * @var HM_Oauth_Server
     */
    private $_server = null;

    private $_oauthError = false;

    public function init()
    {
        parent::init();

        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->_helper->getHelper('viewRenderer')->setNoRender();

        if ($this->_request->getActionName() != 'authorize') {
            try {
                $this->_server = new HM_Oauth_Server($this->view->serverUrl('/oauth/v1/request_token'), Zend_Registry::get('serviceContainer'));
            } catch (Zend_Oauth_Exception $e) {
                Zend_Registry::get('log_system')->debug($e->getMessage()."\n".$e->getTraceAsString());
                echo HM_Oauth_Server::reportProblem($e);
                $this->_oauthError = true;
            }
        }

    }

    public function indexAction()
    {

    }

    public function requesttokenAction()
    {
        if (!$this->_oauthError) {
            echo $this->_server->requestToken();
        }
    }

    public function accesstokenAction()
    {
        if (!$this->_oauthError) {
            echo $this->_server->accessToken();
        }
    }

    public function authorizeAction()
    {
        $oauth_callback = $this->_getParam('oauth_callback', '');
        $oauth_token = $this->_getParam('oauth_token', '');

        if (strlen($oauth_token)) {

            $token = HM_Oauth_Token::load($oauth_token, Zend_Registry::get('serviceContainer'));

            if ($token && $token->isRequest()) {
                if (in_array($this->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_GUEST))) {
                    $this->_redirector->gotoUrl($this->view->serverUrl($this->view->url(array('module' => 'default', 'controller' => 'index', 'action' => 'index'))).'?oauth_token='.$oauth_token.'&oauth_callback='.$oauth_callback);
                } else {
                    $verifier = HM_Oauth_Server::authorizeToken($oauth_token, $this->getService('User')->getCurrentUser(), Zend_Registry::get('serviceContainer'));
                    $this->_redirector->gotoUrl($oauth_callback.'?&oauth_token='.$oauth_token.'&oauth_verifier='.$verifier);
                }
            } else {
                echo _('Данный oauth_token не найден.');
            }

        } else {
            echo _('Не указан oauth_token.');
        }


    }
}