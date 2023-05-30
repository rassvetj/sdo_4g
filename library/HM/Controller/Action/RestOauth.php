<?php
class HM_Controller_Action_RestOauth extends HM_Controller_Action_Rest
{
    
    /**
     * @var HM_Oauth_Server
     */
    protected $_server = null;

    protected $_debug = false;

    public function init()
    {
        parent::init();

        if (!$this->_debug) {
            try {
                $this->_server = new HM_Oauth_Server($this->view->serverUrl('/oauth/v1/request_token'), Zend_Registry::get('serviceContainer'));
            } catch (Zend_Oauth_Exception $e) {
                Zend_Registry::get('log_system')->debug($e->getMessage()."\n".$e->getTraceAsString());
                echo HM_Oauth_Server::reportProblem($e);
                exit();
            }
        }

    }

}