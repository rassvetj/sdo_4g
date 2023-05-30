<?php
class HM_Languages_Assign_AssignService extends HM_Service_Abstract
{
	public function getBySubject($subject_id)
	{
		return $this->fetchAll($this->quoteInto('CID = ?', intval($subject_id)));
	}
	
	public function insert($raw)
	{
		$data = array(
			'MID'			=> (int)$raw['MID'],
			'CID' 			=> (int)$raw['CID'],
			'language_code' => trim($raw['language_code']),
			'author_id'		=> $this->getService('User')->getCurrentUserId(),
			'date_created'	=> date('Y-m-d H:i:s'),
		);
		return parent::insert($data);
	}
	
	public function update($raw)
	{
		$data = array(
			'assign_id'		 => (int)$raw['assign_id'],
			'MID'			 => (int)$raw['MID'],
			'CID' 			 => (int)$raw['CID'],
			'language_code'  => trim($raw['language_code']),
			'last_author_id' => $this->getService('User')->getCurrentUserId(),
			'date_updated'	 => date('Y-m-d H:i:s'),
		);
		return parent::update($data);
	}
	
	public function getRecommendedLanguageCode($item, $items)
	{
		$position = false;
		foreach($items as $key => $i){
			if($i->MID == $item->MID){
				$position = $key;
				break;
			}
		}		
		if($position === false){ return false; }
		
		return $this->getLanguageLvel($position, $items->count());
		
		
		/*
		if(!($balls instanceof HM_Collection)){ return false; }
		
		$data = array();
		foreach($balls as $model){
			$data[$model->mid] = $model->getBall();			
		}
		*/
		
		#if($ball < 30){ return HM_Languages_Assign_AssignModel::LEVEL_BEGIN; }
		#if($ball < 60){ return HM_Languages_Assign_AssignModel::LEVEL_MIDDLE; }
		#return HM_Languages_Assign_AssignModel::LEVEL_HIGH;
	}
	
	public function getLanguageLvel($position, $total)
	{
		$item_in_part = ceil($total / intval(HM_Languages_Assign_AssignModel::LEVEL_COUNT));
		
		for ($i = 1; $i <= HM_Languages_Assign_AssignModel::LEVEL_COUNT; $i++) {
			$to		= $item_in_part * $i;
			$from 	= $to - $item_in_part;
			
			if($from <= $position && $position < $to ){ return HM_Languages_Assign_AssignModel::getLevelCodeByPosition($i); }			
		}
		return false;
	}
	
	
	/*
	** сортировка по набранным баллам от большего к меньшему
	*/
	public function sortByBall($raw)
	{	
		if(!($raw instanceof HM_Collection)){ return false; }
		
		$items = new HM_Collection();
		$items->setModelClass($raw->getModelClass());
		
		$balls = $raw->getList('MID', 'ball');
		arsort($balls);
		
		foreach($balls as $MID => $ball){
			$item = $raw->exists('MID', $MID);
			if(!$item){ continue; }
			$items->offsetSet($items->key(), $item);
			$items->next();
		}
		return $items;
	}
	
	
}
