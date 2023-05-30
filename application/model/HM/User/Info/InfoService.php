<?php
class HM_User_Info_InfoService extends HM_Service_Abstract
{
	private $_midWithHalfAccessCache = NULL;

   public function getByCode($mid_external)
   {
	   return $this->getOne($this->fetchAll($this->quoteInto('mid_external = ?', $mid_external)));
   }
   
	public function getCurrentUserInfo()
	{
		return $this->getOne($this->fetchAll($this->quoteInto('mid_external = ?', $this->getService('User')->getCurrentUser()->mid_external)));
	}

	public function getMidWithHalfAccess()
	{
		return false;
		
		if($this->_midWithHalfAccessCache === NULL){
			$this->_midWithHalfAccessCache = array();
			$select = $this->getSelect();
			$select->from(array('pi'=>'People_info'), array('p.MID'));
			$select->join(array('p' =>'People'), 'p.mid_external=pi.mid_external', array());
			$select->where($this->quoteInto('pi.status IN (?)', HM_User_Info_InfoModel::getHalfAccessStatuses()));
			$res = $select->query()->fetchAll();

			if(!empty($res)){
				foreach($res as $item){
       		  $this->_midWithHalfAccessCache[$item['MID']] = $item['MID'];
      		}
			}
		}
      return $this->_midWithHalfAccessCache;
	}
    
    
}