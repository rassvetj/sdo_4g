<?php
class HM_Controller_Plugin_Authorizer extends Zend_Controller_Plugin_Abstract
{
    const AUTHORIZER_COOKIE_NAME = 'hmkey';

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $url = $_SERVER['REQUEST_URI'];

        if (false !== strstr($url, 'index.php')) {
            $url = Zend_Registry::get('view')->serverUrl('/');
        }

        $serviceContainer = Zend_Registry::get('serviceContainer');
        if (!$serviceContainer->getService('User')->getCurrentUser() && isset($_COOKIE[self::AUTHORIZER_COOKIE_NAME])) {
            $key = trim($_COOKIE[self::AUTHORIZER_COOKIE_NAME]);

            if (strlen($key)) {
                if ($serviceContainer->getService('User')->authorizeByKey($key)) {
                    header('Location: '.$url);
                    exit();

                }                
            }            

        }
    }
}
