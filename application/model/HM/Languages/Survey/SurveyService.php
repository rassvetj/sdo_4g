<?php
class HM_Languages_Survey_SurveyService extends HM_Service_Abstract
{
	private $_all_data = false;
	
	public function getAll()
	{
		if(empty($this->_all_data)){
			$this->_all_data = $this->fetchAll('is_hidden IS NULL OR is_hidden != 1');	
		}
		return $this->_all_data;
	}
	
	
	public function getListByName($field_name)
	{
		$raw = $this->getAll();
		$data = array();
		foreach($raw as $i){
			$value 			= $i->{$field_name};
			$data[$value] 	= $value;
		}
		$data = array_filter($data);
		
		return $data;
	}
	
	public function toArray($value)
	{
		if(is_string($value)){ return (array)$value; }
		
		$data  = array();
		foreach($value as $i){ 
			$i = (array)$i;
			if(empty($i)){ continue; }
			foreach($i as $v){
				if(empty($v)){ continue; }
				$data[] = $v;
			}
		}
		$data = array_filter($data);
		return $data;
	}
   
}
