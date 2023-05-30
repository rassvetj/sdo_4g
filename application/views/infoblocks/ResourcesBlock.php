<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';

class HM_View_Infoblock_ResourcesBlock extends HM_View_Infoblock_ScreenForm
{
    protected $id = 'resources';
    protected $session;

    public function resourcesBlock($title = null, $attribs = null, $options = null)
    {
		$this->session = new Zend_Session_Namespace('infoblock_resources');
		$classifiers = Zend_Registry::get('serviceContainer')->getService('Classifier')->getTypes(HM_Classifier_Link_LinkModel::TYPE_RESOURCE);
		//$classifiers = $this->_getClassifiers();

		if (count($classifiers)) {

			if (!isset($this->session->from)) {
    			$this->session->from = '';
    		}
    		if (!isset($this->session->to)) {
    			$this->session->to = '';
    		}
    		if (!isset($this->session->classifier)) {
    			$this->session->classifier = array_shift(array_keys($classifiers));
    		}
    		$this->view->classifiers = $classifiers;
    		$this->view->session = $this->session;

		} else {
		    $collection = Zend_Registry::get('serviceContainer')->getService('Resource')->fetchAll(array(
		        'status != ?' => HM_Resource_ResourceModel::STATUS_UNPUBLISHED,
		        'parent_id = ?' => 0,
		        'location = ?' => HM_Resource_ResourceModel::LOCALE_TYPE_GLOBAL,
            ));
		    $this->view->totalResources = count($collection);
		}
    	$content = $this->view->render('resourcesBlock.tpl');
        $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/infoblocks/resources/style.css');

        if ($title == null) return $content;
        return parent::screenForm($title, $content, $attribs);
    }

    private function _getClassifiers()
    {
    	if ($types = Zend_Registry::get('serviceContainer')->getService('Classifier')->getTypes(HM_Classifier_Link_LinkModel::TYPE_RESOURCE)) {
    	    foreach ($types as $key => $name) {

    	        $children = array();
    	        foreach (Zend_Registry::get('serviceContainer')->getService('Classifier')->fetchAll(array('level = ?' => 0, 'type = ?' => $key)) as $classifier) {
    	            $children[$classifier->classifier_id] = $classifier->name;
    	        }

    	        $classifiers[$key] = array(
    	            'name' => $name,
    	            'children' => $children,
                );
    	    }
    	}
        return $classifiers;
    }
}