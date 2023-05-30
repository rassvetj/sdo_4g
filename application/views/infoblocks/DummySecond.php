<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';
class HM_View_Infoblock_DummySecond extends HM_View_Infoblock_ScreenForm
{                                          
    
    protected $id = 'dummySecond';
    
    public function dummySecond($title = null, $attribs = null, $options = null)
    {
        $content = $this->view->render('dummySecondBlock.tpl');
        if ($title == null) return $content;
        return parent::screenForm($title, $content, $attribs);
    }
}