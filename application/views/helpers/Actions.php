<?php
class HM_View_Helper_Actions extends HM_View_Helper_Abstract
{
	/**
	 * 
	 * generate actions list from actions.xml and $options param
	 * @param String $name
	 * @param Array of Array('title', 'url') $options
	 * @param unknown_type $params
	 * @param unknown_type $attribs
	 */
    public function actions($name, $options = array(), $params = null,$attribs = null)
    {
		$this->view->headScript()->appendFile($this->view->serverUrl('/js/content-modules/breadcrumbs.js'));       
    	$options = (count($options)) ? $options : $this->getXMLOptions();

    	if(!empty($params)){
    	    foreach($params as $key => $val){
    	        foreach($options as $k => &$v){
    	            $v['url'] = str_replace('%' . $key .'%', $val, $v['url']);
    	        }
    	    }
    	}
    	if(empty($options)) return false;
    	
    	$main = array_shift($options);
    	$this->view->options    = $options;
    	$this->view->name       = $name;
    	$this->view->attribs    = $attribs;
    	$this->view->main    = $main;
    	
        return $this->view->render('actions.tpl');
    }

    protected function getXMLOptions(){
    	
    	$id = Zend_Registry::get('serviceContainer')->getService('Unmanaged')->getCurrentPageId();
    	$options = Zend_Registry::get('serviceContainer')->getService('Unmanaged')->getCurrentPageLinks($id);
    	if (is_array($options) && count($options)) {
    		foreach($options as &$option) {
    			$option['title'] = iconv('UTF-8', Zend_Registry::get('config')->charset, $option['title']);
    		}
    	}
    	else $options = array();
    	
    	return $options;
    }
    
}