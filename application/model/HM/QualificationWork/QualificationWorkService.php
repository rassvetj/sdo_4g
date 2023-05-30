<?php
class HM_QualificationWork_QualificationWorkService extends HM_Service_Abstract
{
	public function getByUser($mid_external){		
		return $this->getOne($this->fetchAll($this->quoteInto('mid_external = ?', $mid_external)));
	}
	
}