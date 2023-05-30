<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';

class HM_View_Infoblock_UsersSystemCounterBlock extends HM_View_Infoblock_ScreenForm
{
    protected $id = 'usersSystemCounter';
    
    public function usersSystemCounterBlock($title = null, $attribs = null, $options = null)
    {

        $services = Zend_Registry::get('serviceContainer');
        
        $this->view->from = date('d.m.Y', strtotime('-7 DAY'));
        $this->view->to = date('d.m.Y');
        
        $stats = $services->getService('Session')->getUsersStats($this->view->from, $this->view->to);
        
        $this->view->stats = $stats;
        
        
		$content = $this->view->render('usersSystemCounterBlock.tpl');
		
        if ($title == null) return $content;
        return parent::screenForm($title, $content, $attribs);
    }
}