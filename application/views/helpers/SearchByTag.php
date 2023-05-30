<?php
class HM_View_Helper_SearchByTag extends HM_View_Helper_Abstract
{
    public function searchByTag($resourceModel, $count)
    {
        $this->view->resourceModel = $resourceModel;
        $this->view->count = $count;
        return $this->view->render('searchByTag.tpl');
    }
}