<?php
class HM_View_Helper_AddButton extends HM_View_Helper_Abstract
{
    /**
     * @param string $url  Урл главной ссылки
     * @param string $title Название
     * @param array $options - список параметоров для actions() 
     * @return string
     */
    public function addButton($url, $title = null, $options = null)
    {
        if (null == $title) $title = _('создать');
        $this->view->url     = $url;
        $this->view->title   = $title;
        $this->view->options = $options;
        
        return $this->view->render('addbutton.tpl');
    }
}