<?php
class HM_Diplom_Option_OptionService extends HM_Service_Abstract
{
	/**
	 * @return bool
	 * Вставка новых данных в БД
	*/
	public function add($data)
	{
		if(empty($data)){ return false; }

		$data['date_created'] = new Zend_Db_Expr('NOW()');
		
		try {			
			$this->insert($data);
		} catch (Exception $e) {
			echo 'Ошибка: ',  $e->getMessage(), "\n";
			return false;
		}
		return true;		
	}
	
}