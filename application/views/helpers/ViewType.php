<?php
class HM_View_Helper_ViewType extends HM_View_Helper_Abstract
{
    public function viewType($name, $otions = null, $params = null, $attribs = null)
    {
        $this->view->changeUrl = $otions['url'].'?viewType='.(($this->view->viewType == 'table') ? 'default' : 'table');
        return $this->view->render('viewtype.tpl');
    }
}