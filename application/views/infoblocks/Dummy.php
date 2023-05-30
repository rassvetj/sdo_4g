<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';
class HM_View_Infoblock_Dummy extends HM_View_Infoblock_ScreenForm
{                                          
    
    protected $id = 'dummy';
    
    public function dummy($title = null, $attribs = null, $options = null)
    {
        $content = $this->view->render('dummyBlock.tpl');
        if ($title == null) return $content;
        return parent::screenForm($title, $content, $attribs);
    }
}