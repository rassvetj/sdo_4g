<?php
class HM_Languages_Assign_Base_BaseService extends HM_Service_Abstract
{
	public function getByUserCode($mid_external)
	{
		return $this->fetchAll($this->quoteInto('mid_external = ?', $mid_external));
	}
	
	public function getCurrentLanguageCode($mid_external, $semester, $language_id = false)
	{
		if($language_id){
			return $this->getOne($this->fetchAll($this->quoteInto(
				array('mid_external = ? ', ' AND semester = ? ', ' AND language_id = ?'), 
				array($mid_external, $semester, $language_id)
			)))->language_id;
		}
		
		return $this->getOne($this->fetchAll($this->quoteInto(array('mid_external = ? ', ' AND semester = ? '), array($mid_external, $semester))))->language_id;
	}
}