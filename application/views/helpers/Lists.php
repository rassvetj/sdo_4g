<?php
class HM_View_Helper_Lists extends HM_View_Helper_Abstract
{

	public function lists($name, $list1Options, $list2Options)
	{
        $this->view->name  = $name;
        $this->view->list1 = $list1Options;
        $this->view->list2 = $list2Options;
		return $this->view->render('lists.tpl');
	}
}