<?php
class HM_Controller_Plugin_Acl extends Zend_Controller_Plugin_Abstract
{

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
		$services = Zend_Registry::get('serviceContainer');
        $moduleDirectory = Zend_Controller_Front::getInstance()->getModuleDirectory().'/acls';
        if (is_dir($moduleDirectory) && is_readable($moduleDirectory)) {
            $handle = opendir($moduleDirectory);
            if ($handle) {
                while(false !== ($file = readdir($handle))) {
                    if (in_array($file, array('.', '..'))) continue;
                    if (substr($file, -4) == '.php') {
                        $services->getService('Acl')->storeModuleName($request->getModuleName());
                        $class = 'HM_Acl_'.substr($file, 0, -4);
                        $acl = new $class(Zend_Registry::get('serviceContainer')->getService('Acl'));
                        unset($acl);
                    }
                }
            }
        }

        $this->session = new Zend_Session_Namespace('default');
        $switch = $this->session->switch_role;
        $this->session->switch_role = 0;
        
        $resource = 'mca:'.$request->getModuleName().':'.$request->getControllerName().':'.$request->getActionName();
        $acl = Zend_Registry::get('serviceContainer')->getService('Acl');

        // если должны куда-то редиректиться после авторизации - редиректимся
        if ( Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUser()
             && !$request->isXmlHttpRequest()
             && isset($this->session->autoredirect)
             && strlen($this->session->autoredirect['url']) ) {

                $url = $this->session->autoredirect['url'];
                unset ($this->session->autoredirect);
                header('Location: '.$url);
                exit();
        }

        if ($acl->has($resource)) {
            if (!$acl->isAllowed(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), $resource)) {

				if($switch == 1) {
					header('Location: '.Zend_Registry::get('view')->serverUrl('/'));
                    exit();
				}
				else{
				    
				    $services->getService('Log')->log(
                        $services->getService('User')->getCurrentUserId(),
                        'Unauthorized access',
                        'Fail',
                        Zend_Log::WARN,
                        get_class($this),
                        $id
                    );

                    if (Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_GUEST
                        && !$request->isXmlHttpRequest()) {

                            $this->session->autoredirect['url'] = $_SERVER['REQUEST_URI'];
                            header('Location: '.Zend_Registry::get('view')->serverUrl('/'));
                            exit();
                    }

                    $request->setModuleName('default');
                    $request->setControllerName('index');
                    $request->setActionName('index');
	                throw new HM_Permission_Exception(_('Не хватает прав доступа.'));
				}
            }
        }
	    
	     $services->getService('Log')->log(
            $services->getService('User')->getCurrentUserId(),
            'Access Granted',
            'Success',
            Zend_Log::NOTICE,
            $_SERVER['REQUEST_URI']
        );
    }
}
