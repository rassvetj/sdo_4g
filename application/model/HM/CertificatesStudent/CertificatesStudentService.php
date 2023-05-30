<?php
class HM_CertificatesStudent_CertificatesStudentService extends HM_Service_Abstract
{
	public function getById($item_id)
	{
		$item_id = (int)$item_id;
		if(empty($item_id)){ return false; }
		return $this->getOne($this->fetchAll($this->quoteInto('certificate_id = ?', $item_id)));
	}
	
	public function addNumber($user_id, $type_id, $raw_data)
	{
		
		$data = array(			
			'fio_dative' 		=> $raw_data['fio_dative'],
			'date_birth' 		=> date('Y-m-d', strtotime($raw_data['date_birth'])),
			'current_course' 	=> (int)$raw_data['current_course'],
			'study_form' 		=> $raw_data['study_form'],
			'based' 			=> $raw_data['based'],
			'direction' 		=> $raw_data['direction'],
			'direction_code' 	=> $raw_data['direction_code'],
			'date_graduation' 	=> date('Y-m-d', strtotime($raw_data['date_graduation'])),
			
			'MID' 				=> (int)$user_id,
			'type_id' 			=> (int)$type_id,			
			'date_created'		=> new Zend_Db_Expr('NOW()'),
		);
		
		
		$new_item = $this->insert($data);
		
		$number   = $this->generateNumber($new_item->certificate_id);
		$isUpdate = $this->updateWhere(array('number' => $number), array('certificate_id = ? ' => $new_item->certificate_id));
		if($isUpdate){ return $number; }
		return false;
	}
	
	
	public function generateNumber($id)
	{
		$prefix = HM_CertificatesStudent_CertificatesStudentModel::PREFIX_NUMBER;
		
		# случился сбой в индексах. Поэтому, 1487 и 1488. Делаем рокировку для будущих справок		
		switch ($id) {
			case 489:
				$id = 1487;
				break;
			case 490:
				$id = 1488;
				break;			
			case 1487:
				$id =  489;
				break;
			case 1488:
				$id =  490;
				break;
		}
		
		return $prefix.str_pad($id, 5, '0', STR_PAD_LEFT);
	}
	
	
	public function getConfirmingStudentItems($user_id)
	{
		$user_id = (int)$user_id;
		if(empty($user_id)){ return false; }
		
		return $this->fetchAll($this->quoteInto(array('MID = ? AND ', 'type_id = ?'), array($user_id, HM_CertificatesStudent_CertificatesStudentModel::TYPE_CONFIRMING_STUDENT )));		
	}
	
	
	/**
	 * Может ли заказать новую справку с новым номером
	*/
	public function isCanCreateNewConfirmingStudent($user_id, $items = false)
	{
		if(!$items){
			$items = $this->getConfirmingStudentItems($user_id);
		}
		
		$limit_per_day = (int)HM_CertificatesStudent_CertificatesStudentModel::LIMIT_PER_DAY;
		# ограничение по колличеству
		if(!empty($limit_per_day)){
			$items_by_day = 0; #созданных за сутки заявок.
			
			$dt = new DateTime();
			$dt->sub(new DateInterval('P1D')); # -1 день
			$start_timesamp = $dt->getTimestamp();
			
			foreach($items as $i){
				$cur_timestamp = strtotime($i->date_created);
				if($cur_timestamp >= $start_timesamp){
					$items_by_day++;
				}
			}
			
			if($items_by_day < $limit_per_day){ return true; }
			return false;
		}
		
		
		# ограничение по времени, если не задано ограничение по колличеству.
		$last_timestamp = 0;
		foreach($items as $i){
			$cur_timestamp = strtotime($i->date_created);
			if($last_timestamp < $cur_timestamp){
				$last_timestamp = $cur_timestamp;	
			}
		}
		
		if( ($last_timestamp + HM_CertificatesStudent_CertificatesStudentModel::TIME_DELAY) > time() ){
			return false;
		}
		return true;
	}
	
	
}