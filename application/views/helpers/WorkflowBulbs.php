<?php
class HM_View_Helper_WorkflowBulbs extends HM_View_Helper_Abstract
{
    public function workflowBulbs ($model)
    {
        $this->view->model = $model;
        $this->view->statesTypes = Zend_Registry::get('serviceContainer')->getService('State')->getStatesTypes($model->getProcess()->getType());
        return $this->view->render('workflowBulbs.tpl');
    }
}
