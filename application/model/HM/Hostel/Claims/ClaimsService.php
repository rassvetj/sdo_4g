<?php

class HM_Hostel_Claims_ClaimsService extends HM_Hostel_HostelService
{
	
	/**
	 * @return select
	*/
	public function getDefaultSelect($mid_external){
		if(!$mid_external){
			return false;
		}
		$select = $this->getSelect();
        $select->from(array('c' => 'hostel_claims'),
			array(				
				'type' 			=> 'c.type',	
				'address_name' 	=> 'r.address_name',
				'room_name' 	=> 'r.room_name',				
				'date_create' 	=> 'c.date_create',
				'date_update' 	=> 'c.date_update',
				'status' 		=> 'c.status',							
				'date_reject' 	=> 'c.date_reject',
				'reason_reject' => 'c.reason_reject',
				'address_name' 	=> 'c.address_name',
				'claim_id' 		=> 'c.claim_id',
			)
		);		
		$select->joinLeft(array('r' => 'hostel_rooms'),
			'r.room_id = c.room_id',
			array()
		);
		$select->where('c.mid_external = ?', $mid_external);
		
		return $select;		
	}
	
	
	
	public function createClaim($mid_external, $type_id, $addres_id, $room_id){
		
		$addressName = $this->getService('Hostel')->getAddressNameById($addres_id);
		
		$isInsert = $this->insert(
				array(
					'mid_external' => $mid_external,
					'room_id' => $room_id,
					'address_name' => $addressName,
					'date_create' => new Zend_Db_Expr('NOW()'),				
					'status' => HM_Hostel_Claims_ClaimsModel::STATUS_NEW,
					'type' => $type_id,					
				)
		);				
		if($isInsert){
			return true;			
		}
		return false;	
	}
	
	
	public function isExsistNewOrder($mid_external){
		if(!$mid_external){
			return false;
		}
		$res = $this->getOne(
				$this->fetchAll(array(
					'mid_external = ?' => $mid_external,
					'status = ?' => HM_Hostel_Claims_ClaimsModel::STATUS_NEW,
				))
			);
		if($res){
			return true;
		}
		return false;
	}
}