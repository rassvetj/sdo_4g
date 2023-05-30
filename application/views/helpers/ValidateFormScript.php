<?php
class HM_View_Helper_ValidateFormScript extends HM_View_Helper_Abstract
{
	
	public function validateFormScript($action = '', $name = 'form')
	{
		$this->view->action = $action;
        $this->view->name = $name;
		return $this->view->render('validate-form-script.tpl');
	}
}