<?php
class Help_IndexController extends HM_Controller_Action{

	private $_managedVars = array();
	private $_unmanagedLink = FALSE;
	private $_help = null;
	
	public function indexAction(){

		$params = $this->_getAllParams();
		$url = urlencode($params['url']);
  		$url = str_replace('.','%2E',$url); 
  		
  		$debug = Zend_Registry::get('config')->debug;
  		
		if($debug) $this->_redirector->gotoSimple('edit', 'index', 'help', array('url' => $url));
		
		if($params['url']) $this->parceUrl($params['url']);
		if($this->_unmanagedLink) $this->getService('Help')->testAndSave(array('link' => $this->_unmanagedLink));
		
		$where = array('role = ?' => $this->getService('User')->getCurrentUserRole());
		if(count($this->_managedVars)){
			$where['module = ?'] = $this->_managedVars['module'];
			$where['controller = ?'] = $this->_managedVars['controller'];
			$where['action = ?'] = $this->_managedVars['action'];
			$where['link_subject = ?'] = $this->_managedVars['link_subject'];
		}
		elseif ($this->_unmanagedLink){
			$where['link'] = $this->_unmanagedLink;
		}

		$this->_help = $this->getOne($this->getService('Help')->fetchAll($where));

		$this->view->help = $this->_help;

	}

	public function editAction(){
		
		$params = $this->_getAllParams();
		if($params['url']) $this->parceUrl($params['url']);
		
		if($this->_unmanagedLink) $this->getService('Help')->testAndSave(array('link' => $this->_unmanagedLink));
	
		$form = new HM_Form_Help();
		$form->setAction($this->view->url(array('module' => 'help', 'controller' => 'index', 'action' => 'edit')));
		//$form->setDefault('cancelUrl', $this->view->url(array($params['hAction'], $params['hController'], $params['hModule'])));

		if ($this->_request->isPost()) {
			if ($form->isValid($this->_request->getParams())) {
				$this->getService('Help')->update(array(
                	'help_id' => $form->getValue('help_id'),
                    'title' => $form->getValue('title', true),
                    'text' => $form->getValue('text', true),
                    'moderated' => $form->getValue('moderated')
				));

				if (count($this->_managedVars)){
				    $fk = Zend_Controller_Front::getInstance();
	                $baseUrl = $fk->getBaseUrl();
	                $urlPrefix = (strlen($baseUrl) > 1)? '/' . ltrim(rtrim($baseUrl,'/'),'/') : '';
	                
	                if ($urlPrefix && $this->_managedVars['app_module']) {
	                    $urlPrefix .= '/' .strtolower($this->_managedVars['app_module']);
	                } elseif ($this->_managedVars['app_module']) {
	                    $urlPrefix .= strtolower($this->_managedVars['app_module']);
	                }
	                 
				    $this->_redirector
				         ->gotoUrl($urlPrefix . 
				                   $this->view
				                        ->url(array(
				                                    'module'     => $this->_managedVars['module'],
				                                    'controller' => $this->_managedVars['controller'],
				                                    'action'     => $this->_managedVars['action'],
				                                    'ajax'       => NULL   
				                   )+$this->_managedVars['options'],null,true));
				}
				elseif ($this->_unmanagedLink){
					$this->_redirector->gotoUrl($this->_unmanagedLink);
				}
			}
				
		} else {
			
			$where = array('role = ?' => $this->getService('User')->getCurrentUserRole());
			if(count($this->_managedVars)){
    			if ($this->_managedVars['app_module'] != '') {
    			    $where['app_module=?'] = strtolower($this->_managedVars['app_module']);
    			} else {
    			    $where['app_module IS NULL OR app_module=?'] = '';
    			}
				$where['module = ?'] = $this->_managedVars['module'];
				$where['controller = ?'] = $this->_managedVars['controller'];
				$where['action = ?'] = $this->_managedVars['action'];
				$where['link_subject = ?'] = $this->_managedVars['link_subject'];
			}
			elseif ($this->_unmanagedLink){
				$where['link = ?'] = $this->_unmanagedLink;
			}
			
			$this->_help = $this->getOne($this->getService('Help')->fetchAll($where));
			if($this->_help){
				$values = $this->_help->getValues();
				//$values['moderated'] = 0;
				$form->setDefaults($values);
			}			
		}

		$this->view->form = $form;

	}

	public function generateAction(){

		$role = $this->_getParam('role', 'student');
		$helps = $this->getService('Help')->fetchAll(array('role = ?' => $role));
		$this->view->helps = $helps;

	}

	// parCe O_o
	protected function parceUrl($url){
		
	    // если baseUrl типа /path1/path2/path3, то он все поломает, избавляемся от него
	    $fk = Zend_Controller_Front::getInstance();
	    $baseUrl = $fk->getBaseUrl();
	    $decodedUrl = ( strlen($baseUrl)>1 )? ltrim(substr(urldecode($url),strlen(ltrim(rtrim($baseUrl,'/'),'/')) ),'/') : 
	                                          urldecode($url);
	    
		$url = parse_url($decodedUrl);
		if(strpos($url['path'], '.php')){
			$this->_unmanagedLink = $url['path'];
		}
		else{

			$path = explode('/', $url['path']);
			
			// если ссылка типа /edo/module/controlle/action то сохраняем первый элемент (application_module)
			// заодно удаляя его из массива, оставляя MCA
			$this->_managedVars ['app_module']   = ( in_array($path[0], array('edo','at','cms') ))? array_shift($path) : '';
			$this->_managedVars ['module']       = $path[0];
			$this->_managedVars ['controller']   = ($path[1]) ? $path[1] : 'index';
			$this->_managedVars ['action']       = ($path[2]) ? $path[2] : 'index';
			$this->_managedVars ['link_subject'] = intval(($options['subject_id'] || $options['resource_id'] || $options['course_id'] || $options['user_id']));
			
			$options = $this->parceOptions($path);
            $this->_managedVars ['options']      = $options;
		}
	}

	protected function parceOptions($path){

		if(count($path) < 4) return array();
		
		$options = array();
		$values = array();
		foreach($path as $key => $option){
			if($key < 3) continue;
			if($key%2) {
                if ($option) $options[] = $option;
            } else {
                if( (count($options) - count($values)) == 1) $values[] = $option;
            }
		}
		return array_combine($options, $values);
		
	}
	
	public function deleteAction(){
		
		$helpId = $this->_request->getParam('help_id', 0);
		if($helpId){
			$this->getService('Help')->delete($helpId);
		}
		$this->_redirector->gotoSimple('generate', 'index', 'help', array('role' => $helpId = $this->_request->getParam('role', 'admin')));
		
	}
	
}