<?php
class HM_View_PageHeader extends Zend_View
{
    public function __toString()
    {
        $this->setScriptPath(APPLICATION_PATH . '/views/extended');
        return $this->render('header.tpl');
    }
    
    public function getTitle()
    {
        $title = '';
        if (!empty($this->panelTitle)) {
            $title .= _($this->panelTitle);
        }
        if (!empty($this->panelTitle) && !empty($this->pageTitle)) {
            $title .= ' › ';
        }
        if (!empty($this->pageTitle)) {
            $title .= _($this->pageTitle);
        }
        return $title;
    }
}