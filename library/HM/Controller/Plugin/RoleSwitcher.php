<?php
class HM_Controller_Plugin_RoleSwitcher extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $serviceContainer = Zend_Registry::get('serviceContainer');
        $userId           = $serviceContainer->getService('User')->getCurrentUserId();
        $userRole         = $serviceContainer->getService('User')->getCurrentUserRole(true); // нужен обязательно roleUnion; 
        $userRoles        = $serviceContainer->getService('User')->getUserRoles($userId);
        
        if ( $userId && !in_array($userRole, $userRoles) ) {
            
            if ( count($userRoles) ) {
                $serviceContainer->getService('User')->switchRole(array_shift($userRoles));
            } else {
                $serviceContainer->getService('User')->logout();
            }
            header('Location: '.Zend_Registry::get('view')->serverUrl('/'));
            exit();
        }
    }
}
