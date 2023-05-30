<?php
class HM_Languages_LanguagesService extends HM_Service_Abstract
{
	public function getLevels()
	{
		return $this->fetchAll(array('isLevel = ?' => 1))->getList('code', 'name');
	}
	
	public function getAll()
	{
		return $this->fetchAll();
	}
}