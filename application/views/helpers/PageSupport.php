<?php
class HM_View_Helper_PageSupport extends HM_View_Helper_Abstract
{

    public function pageSupport()
    {
        $services    = Zend_Registry::get('serviceContainer');
        $aclService  = $services->getService('Acl');
        $userService = $services->getService('User');
        $userRole    = $userService->getCurrentUserRole();
        
        if(!$aclService->inheritsRole($userRole, HM_Role_RoleModelAbstract::ROLE_GUEST)) {
            $this->view->headScript()->appendFile($this->view->serverUrl('/js/lib/jquery/jquery.tipTip.minified.js'));
            $this->view->headLink()->appendStylesheet($this->view->serverUrl('/css/jquery/tipTip.css'));
            $this->view->headLink()->appendStylesheet(
                $this->view->serverUrl('/css/content-modules/page-support.css')
            );

            return $this->view->render('page-support.tpl');
        }
    }
    
}