<?php
class HM_StudentDebt_Schedule_ScheduleService extends HM_Service_Abstract
{
	private $skip_first_row = true;
	
	public function import($file_path, $is_remove_old_data = false)
	{
		$data = $this->readFile($file_path);
		if(empty($data)){
			return HM_StudentDebt_Schedule_ScheduleModel::ERR_FILE_EMPTY;
		}
		
		$data = $this->prepareData($data);
		if(empty($data)){
			return HM_StudentDebt_Schedule_ScheduleModel::ERR_FILE_EMPTY;
		}
		
		if(!empty($is_remove_old_data)){
			if(!$this->removeAll()){ 
				return HM_StudentDebt_Schedule_ScheduleModel::ERR_CLEAR_TABLE;
			}
		} else {
			# Если не очищаем старые данные, то удаляем из новых все старые
			$data = $this->removeExistingData($data);
		}
		
		if(empty($data)){
			return HM_StudentDebt_Schedule_ScheduleModel::ERR_NOT_NEW_DATA;
		}
		
		$is_insert = $this->insertData($data);
		if($is_insert){
			return true;
		}
		
		return HM_StudentDebt_Schedule_ScheduleModel::ERR_INSERT;		
	}
	
	/**
	 * @return bool
	 * Вставка новых данных в БД
	*/
	private function insertData($data)
	{
		if(empty($data)){ return false; }
		
		try {
			foreach($data as $key => $i){
				$i['date_created'] = new Zend_Db_Expr('NOW()');
				$this->insert($i);
			}		
		} catch (Exception $e) {
			echo 'Ошибка: ',  $e->getMessage(), "\n";
			#die;
			return false;
		}
		return true;		
	}
	
	/**
	 * @return array
	 * Удаляем из новгог набора данных существующие данные в БД
	*/
	private function removeExistingData($new_data)
	{
		$old_data = $this->fetchAll();
		if(empty($old_data)){ return $new_data; }
		
		$col_list = HM_StudentDebt_Schedule_ScheduleModel::getFileColList();		
		foreach($old_data as $i){
			$item = array();
			# формируем ключ из старых данных для поиска в новых данных
			foreach($col_list as $field_name){
				if($field_name == 'date_day'){ 
					$item[$field_name] = date('d.m.Y', strtotime($i->date)); 
					continue;
				}
				
				if($field_name == 'date_time'){ 
					$item[$field_name] = date('H:i', strtotime($i->date)); 
					continue;
				}
				
				$item[$field_name] = $i->{$field_name};
			}
			$key = $this->generateKey($item);
			
			if(array_key_exists($key, $new_data)){
				unset($new_data[$key]);
			}
		}	
		
		return $new_data;		
	}
	
	
	/**
	 * @return int - count of deleted rows
	 * удаление всех данных из таблицы
	*/
	private function removeAll()
	{
		return $this->deleteBy('1=1');
	}
	
	
	/**
	 * @return false or array
	 * Читает файл и преобразует его в массив с полями = полям в БД кроме даты
	 * пропускает первую строку!!
	*/
	private function readFile($file_path)
	{	
		$lib_path_1 = $_SERVER["DOCUMENT_ROOT"].'/../../library/spreadsheet-reader/php-excel-reader/excel_reader2.php';
		$lib_path_2 = $_SERVER["DOCUMENT_ROOT"].'/../../library/spreadsheet-reader/SpreadsheetReader.php';
		
		$file_path	= realpath($file_path);
		
		if(!file_exists($file_path)){
			return false;
		}
		
		if(!file_exists($lib_path_2)){
			return false;
		}
		
		include_once($lib_path_1);
		include_once($lib_path_2);
		
		$col_list = HM_StudentDebt_Schedule_ScheduleModel::getFileColList();
		
		$Reader = new SpreadsheetReader($file_path);
		
		
		
		
		
		
		$data = array();
		
		$row_count = 0;
		foreach ($Reader as $Row){			
			$row_count++;
			if($row_count==1 && $this->skip_first_row){ continue; }
			
			$Row = array_filter($Row);
			if(empty($Row)){ continue; }
			
			$item = array();
			foreach($col_list as $col_number => $name){
				$item[$name] = trim($Row[$col_number]);
			}
			
			$dt	= DateTime::createFromFormat('m-d-y', $item['date_day']); # for date-format ceil
			$dt = $dt ? $dt : DateTime::createFromFormat('d.m.Y', $item['date_day']);  # for text format ceil
			
			if($dt){
				$item['date_day'] = $dt->format('d-m-Y');
			}
			
			$date_day_timestamp = strtotime($item['date_day']);
			# в xls почему-то выводит вместо даты значение dd.mm.yyyy. Полагаю, из-за фотмата
			if($date_day_timestamp <= 0){
				continue;
			}
			
			$item['mid_external'] = preg_replace("/[^0-9]/", '', $item['mid_external']);
			
			# при некорректном времени минуты обрезаются при чтении файла 
			$item['date_time'] = str_replace('.', ':', $item['date_time']);			
			$tmp = explode(':', $item['date_time']);
			if($tmp[0] > 23){ 
				$item['date_time'] = '00:00'; 
			} else {			
				$item['date_time'] = str_pad(intval($tmp[0]), 2, 0, STR_PAD_LEFT).':';
				if(isset($tmp[1]))	{ $item['date_time'] .= str_pad(intval($tmp[1]), 2, 0); }
				else 				{ $item['date_time'] .= '00'; 							 }
			}
			
			$key = $this->generateKey($item); # исключаем дубликаты строк			
			$data[$key] = $item;

			
			#if($row_count >= 3000){
			#	break;
			#}
			
		}
		
		if(empty($data)){ return false; }
		return $data;
	}
	
	# без md5 ключ получается уж очень большим.
	private function generateKey($item)
	{
		return md5(implode('~', $item));
	}
	 
	
	/**
	 * @return array or false	 
	 * Преобразуем дату к записи в БД
	*/
	private function prepareData($data)
	{
		if(empty($data)){ return false; }
		
		foreach($data as $key => $i){
			
			$i['attempt']	= (int)$i['attempt'];

			$date_timestamp = strtotime($i['date_day'].' '.$i['date_time']);
			$i['date'] 		= date('Y-m-d H:i:s', $date_timestamp);
			unset($i['date_day']);
			unset($i['date_time']);			
			$data[$key] = $i;
		}
		
		return $data;		
	}
	
}