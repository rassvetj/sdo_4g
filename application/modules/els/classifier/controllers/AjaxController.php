<?php

class Classifier_AjaxController extends HM_Controller_Action
{
    public function listAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getResponse()->setHeader('Content-type', 'text/html; charset='.Zend_Registry::get('config')->charset);

//        $lessonId = (int) $this->_getParam('lesson_id', 0);
//        $lesson = $this->getService('Lesson')->getOne($this->getService('Lesson')->findDependence('Assign', $lessonId));

        $type = (int) $this->_getParam('type', 0);
        $itemId = (int) $this->_getParam('item_id', 0);
        $itemType = (int) $this->_getParam('item_type', 0); //HM_Classifier_Link_LinkModel::TYPE_SUBJECT
        $current = $this->_getParam('current', '');
        
        // find current classifier links
        switch($current){
            case 'dean_responsibilities': $links = $this->getResponsibilities($itemId); break; // dean_responsibilities table
            default: $links = $this->getLinks($itemType, $itemId); break; // classifier_links table
        }

        $where = array(
        	sprintf('node.type = %d', $type),
        	sprintf('parent.type = %d', $type),
        );
        // find all classifiers where 1) classifierType is possible
        //$where = array();
        
        // classifierType depends of $itemType
        /*$types_collection = $this->getService('ClassifierType')->fetchAll(
        						$this->getService('ClassifierType')->quoteInto('link_types LIKE ?', '%'.$itemType.'%')
        );

        $possible_types = array();
        foreach ($types_collection as $type){
        	$possible_types[] = sprintf('node.type = %d', $type->type_id);
        }
        if(count($possible_types)){
        	$where[] = '('.implode(' OR ', $possible_types).')';
        }
          */
		// 2) $q - WTF?
        $q = urldecode($this->_getParam('q', ''));
        if (strlen($q)) {
            $q = '%'.iconv('UTF-8', Zend_Registry::get('config')->charset, $q).'%';
            $where[] = $this->getService('User')->quoteInto('LOWER(node.name) LIKE LOWER(?)', $q);
        }

		$where = implode(' AND ', $where);
        $collection = $this->getService('Classifier')->getTree($where);

        $list = array();
        if (count($collection)) {
            foreach($collection as $item) {
            	// дерево (на странице "Классификаторы") строится корректно,
            	// но при этом $item->depth возвращает совершенно неадекватные значения;
            	// в базе - действительно некорректные значения в lft и rgt
            	// похоже, что $item->level придуман для того, чтобы обойти эту проблему
            	// использую его   
                $list[$item->classifier_id] = str_repeat('-', $item->level).' '._($item->name);
            }
        }

        if (is_array($list) && count($list)) {
            $count = 0;
            foreach($list as $id => $name) {
                if ($count > 0) {
                    echo "\n";
                }
                if ($links && $links->exists('classifier_id', $id)) {
                    $id .= '+';
                }
                echo sprintf("%s=%s", $id, $name);
                $count++;
            }
        }

    }

    public function getTreeBranchAction()
    {
        $key = (int) $this->_getParam('key', 0);

        $children = $this->getService('Classifier')->getTreeContent(null, $key);

        echo Zend_Json::encode($children);
	    exit;
    }

    public function getLinks($itemType, $itemId){
        $links = false;
        if ($itemId > 0) {
            $links = $this->getService('ClassifierLink')->fetchAll(
                $this->getService('ClassifierLink')->quoteInto(
                    array('item_id = ?', ' AND type = ?'),
                    array($itemId, $itemType)
                )
            );
        }
        return $links;
    }

    public function getResponsibilities($userId){
        $links = false;
        if ($userId > 0) {
            $links = $this->getService('DeanResponsibility')->getResponsibilities($userId);
        }
        return $links;
    }
}