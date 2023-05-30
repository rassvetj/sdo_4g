<?php
class HM_Controller_Action_Helper_ConditionalRedirector extends Zend_Controller_Action_Helper_Redirector
{
	
	protected $_params = null;
	
	public function setCondition($params, $url)
	{
		Zend_Registry::get('session_redirector')->conditions[$params['module'].$params['controller'].$params['action']] = null;						
		Zend_Registry::get('session_redirector')->conditions[$params['module'].$params['controller'].$params['action']][] = array('params' => $params, 'url' => $url);
	}
	
    public function addCondition($params, $url)
    {
        Zend_Registry::get('session_redirector')->conditions[$params['module'].$params['controller'].$params['action']][] = array('params' => $params, 'url' => $url);
    }

    public function setGotoSimple($action, $controller = null, $module = null, array $params = array())
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $params['ajax'] = 'true';
        }
        return parent::setGotoSimple($action, $controller, $module, $params);
    }

	
	protected function _redirect($url)
	{
	    $services = Zend_Registry::get('serviceContainer');
	    
	     $services->getService('Log')->log(
            $services->getService('User')->getCurrentUserId(),
            'Access Granted',
            'Success',
            Zend_Log::NOTICE,
            $_SERVER['REQUEST_URI']
        );
	    
		if (Zend_Registry::isRegistered('session_redirector')) {
			$redirector = Zend_Registry::get('session_redirector');
            
			$request = $this->getRequest();
			$key = strtolower($request->getModuleName().$request->getControllerName().$request->getActionName());
			 
			if ((null !== $redirector) 
			    && isset($redirector->conditions) 
			    && isset($redirector->conditions[$key]) 
			    && is_array($redirector->conditions[$key]) 
			    && count($redirector->conditions[$key])) {
			    				
                foreach($redirector->conditions[$key] as $index => $condition) {
                	$params = $condition['params'];
                    if (isset($params['method'])) {
                    	switch(strtolower($params['method'])) {
                    		case 'get':
                                if (!$request->isGet()) continue;
                    			break;
                    		case 'post':
                    			if (!$request->isPost()) continue;
                    			break;
                    		default:
                    			continue;
                    	}
                    }
                    
                    if (isset($params['role']) && ($params['role'] != Zend_Auth::getInstance()->getIdentity()->role)) continue;

                    if (isset($params['params']) && is_array($params['params']) && count($params['params'])) {
                    	foreach($params['params'] as $name => $value) {
                    		if ($value != $request->getParam($name)) continue;
                    	}
                    }
                    
                    if (isset($condition['url'])) {
                    	if (is_array($condition['url'])) {
                    		$router = $this->getFrontController()->getRouter();
                            $url    = $router->assemble($condition['url'], 'default', true);
                    	} elseif (is_string($condition['url'])) {
                    		$url = $condition['url'];
                    	}                    	
                    }
                    
                    unset($redirector->conditions[$key][$index]);
                    break;                    
                }	            
			}
		}

        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->setCode(302);
        }

		if (!preg_match('#^(https?|ftp)://#', $url)) {
            $host  = (isset($_SERVER['SERVER_NAME'])?$_SERVER['SERVER_NAME']:'');
            $proto = (isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=="off") ? 'https' : 'http';
            $port  = (isset($_SERVER['SERVER_PORT'])?$_SERVER['SERVER_PORT']:80);
            $uri   = $proto . '://' . $host;
            if ((('http' == $proto) && (80 != $port)) || (('https' == $proto) && (443 != $port))) {
                $uri .= ':' . $port;
            }

            $url = $uri . '/' . ltrim($url, '/');

        }	  	
		
		parent::_redirect($url);
	}
}