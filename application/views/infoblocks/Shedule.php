<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';
class HM_View_Infoblock_Shedule extends HM_View_Infoblock_ScreenForm
{
    
    protected $id = 'shedule';
    
    public function shedule($title = null, $attribs = null, $options = null)
    {
        $content = $this->view->render('sheduleBlock.tpl');
        if ($title == null) return $content;
        return parent::screenForm($title, $content, $attribs);
    }
}