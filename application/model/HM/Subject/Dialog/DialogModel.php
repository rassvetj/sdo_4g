<?php
class HM_Subject_Dialog_DialogModel extends HM_Model_Abstract
{
   public function getDate()
   {
		$date = new Zend_Date();
		$date->set($this->date);

		return $date->toString();
	}
}
