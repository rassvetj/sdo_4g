<?php
class HM_Hostel_HostelService extends HM_Service_Abstract
{   
	public function getListAddress(){
		$select = $this->getSelect();
        $select->from('hostel_rooms',
			array(				
				'address_id',
				'address_name',
			)
		);
		$select->where('address_id IS NOT NULL');
		$select->where('address_name IS NOT NULL');
		$select->group(array('address_id', 'address_name'));
		$res = $select->query()->fetchAll();
		
		
		if(!$res){
			return array('-1' => _('Нет'));
		}
		
		$list = array('0' => _('Любое'));
		foreach($res as $i){
			$list[$i['address_id']] = $i['address_name'];			
		}
		return $list;
	}
	
	
	public function getListRooms($addres_id){
		$select = $this->getSelect();
        $select->from('hostel_rooms',
			array(				
				'room_id',
				'room_name',
			)
		);
		$select->where('address_id = ?', $addres_id);	
		$select->where('room_id IS NOT NULL');		
		$select->where('room_name IS NOT NULL');		
		$select->group(array('room_id', 'room_name'));
		$select->order(array('room_name ASC'));
		$res = $select->query()->fetchAll();
		
		if(!$res){
			return array(
					'0' => array(
							'key' 	=> '-1',
							'value' => _('Нет'),
						),
					);
		}
		//-_такой формат нужен для упорядоченного вывода  списка комнат.
		$list[] = array(
			'key' => '0',
			'value' => _('Любая'),
		);
		foreach($res as $i){					
			$list[] = array(
				'key' => $i['room_id'],
				'value' => $i['room_name'],
			);			
		}
		return $list;
	}
	
	public function getAddressNameById($addres_id){
		$res = $this->getOne(
			$this->fetchAll(
				array(
					'address_id = ?' => $addres_id
				)
			)
		);
		if($res && $res->address_name){
			return $res->address_name;
		}
		return '';
	}
	
}	