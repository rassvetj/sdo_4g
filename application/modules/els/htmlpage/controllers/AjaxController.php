<?php

class Htmlpage_AjaxController extends HM_Controller_Action
{
    public function getTreeBranchAction()
    {
        $key = $this->_getParam('key', 0);
        $field = (!is_numeric($key)) ? 'role' : 'group_id';
        
        if($field == 'role') $children = $this->getService('HtmlpageGroup')->getTreeContent(0, $key);
        else $children = $this->getService('HtmlpageGroup')->getTreeContent($key);

        if(count($children))
        	echo Zend_Json::encode($children);
	    exit;
    }
}