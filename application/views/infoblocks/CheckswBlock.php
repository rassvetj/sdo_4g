<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';

class HM_View_Infoblock_CheckswBlock extends HM_View_Infoblock_ScreenForm
{
    protected $id = 'resources';
    protected $session;

    public function checkswBlock($title = null, $attribs = null, $options = null)
    {
    	$content = $this->view->render('checkswBlock.tpl');
        $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/infoblocks/checksw/style.css');

        if ($title == null) return $content;
        return parent::screenForm($title, $content, $attribs);
    }
}