<?php
class HM_Controller_Plugin_FirstTime extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $serviceContainer = Zend_Registry::get('serviceContainer');
        $page = sprintf('%s-%s-%s', $request->getModuleName(), $request->getControllerName(), $request->getActionName()); 
        if ($user = $serviceContainer->getService('User')->getCurrentUser()) {
            if (($user->need_edit == HM_User_UserModel::NEED_EDIT_AFTER_FIRST_LOGIN) && (!in_array($page, array('user-edit-index', 'default-index-logout', 'default-index-restore')))) {
                $request->clearParams()
                        ->setParam('module', 'user')
                        ->setParam('controller', 'edit')
                        ->setParam('action', 'index')
                        ->setModuleName('user')
                        ->setControllerName('edit')
                        ->setActionName('index')
                        ->setDispatched(false);
                $serviceContainer->getService('Unmanaged')->setCurrentPageId('m00');
            }
        }
    }
}
