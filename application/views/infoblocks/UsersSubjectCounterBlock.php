<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';

class HM_View_Infoblock_UsersSubjectCounterBlock extends HM_View_Infoblock_ScreenForm
{
    protected $id = 'usersSubjectCounter';
    
    public function usersSubjectCounterBlock($title = null, $attribs = null, $options = null)
    {
 
        $subjectId = (int)$options['subject_id'];
        
        $services = Zend_Registry::get('serviceContainer');
        
        $this->view->from = date('d.m.Y', strtotime('-7 DAY'));
        $this->view->to = date('d.m.Y');
        
        $res = $services->getService('Lesson')->getUsersStats($this->view->from, $this->view->to, $subjectId);
        $this->view->subjectId = $subjectId;
        $this->view->counter = $res;
        
		$content = $this->view->render('usersSubjectCounterBlock.tpl');

        if ($title == null) return $content;
        return parent::screenForm($title, $content, $attribs);
    }
}