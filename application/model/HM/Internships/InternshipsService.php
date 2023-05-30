<?php
class HM_Internships_InternshipsService extends HM_Service_Abstract
{	
	
	public function add($data)
	{
		$data = array(
			'type'			=> $data['type'],
			'MID'			=> $data['MID'],
			'fio'			=> $data['fio'],
			'phone'			=> $data['phone'],
			'email'			=> $data['email'],
			'date_created'	=> date('Y-m-d H:i:s'),
			'languages'		=> $data['languages'],
		);
		return $this->insert($data);
	}
	
	
	public function getLanguageNameByCode($code)
	{	
		if(empty($code)){ return false; }
		
		$select = $this->getSelect();
		$select->from('Students_language_list', '*');
		$select->where($this->quoteInto('code = ?', $code));
		$row = $select->query()->fetchObject();
		return trim($row->name);
	}
	
	public function getDegreeNameByCode($code)
	{
		$list = HM_Internships_InternshipsModel::getDegreeList();
		return $list[$code];
	}
	
	
	# ключ - id языка, значение - id уровня
	public function prepareDegree($raw)
	{
		$data = array();
		foreach($raw as $language_code => $degree_code){
			$key = $language_code.'~'.$degree_code; 
			
			$data[$key] = array(
				'language_code' => $language_code,
				'degree_code' 	=> $degree_code,
				
				'language_name' => $this->getLanguageNameByCode($language_code),
				'degree_name' 	=> $this->getDegreeNameByCode($degree_code),
			);
		}
		return $data;
	}
	
	
	# преобразуем массив данных в строку для сохранения в БД
	public function convertDegreeToString($raw)
	{
		$data = array();
		foreach($raw as $i){
			$data[] = $i['language_code'].'::'.$i['language_name'].'::'.$i['degree_code'].'::'.$i['degree_name'];
		}
		return implode('~~', $data);			
	}
	
	# преобразуем строку в массив данных при извлечении из БД
	public function convertDegreeToArray($str)
	{
		$data = array();
		$rows = explode('~~', $str);
		foreach($rows as $i){
			$parts = explode('::', $i);
			$data[] = array(
				'language_code' => $parts[0],
				'language_name' => $parts[1],
				
				'degree_code' 	=> $parts[2],
				'degree_name' 	=> $parts[3],
			);
		}
		return $data;		
	}
	
	public function isHasAccess()
	{
		$type_list_allow = HM_Internships_InternshipsModel::getTypeListAllow();
		if(empty($type_list_allow)){
			return false;
		}
		
		# доступно всем
		return true;
		
		$allow_users = array(5829, 65193, 72145);
		if(in_array($this->getService('User')->getCurrentUserId(), $allow_users)){
			return true;
		}
		return false;
	}
	
	
}