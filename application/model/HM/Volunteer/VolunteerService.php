<?php
class HM_Volunteer_VolunteerService extends HM_Service_Abstract
{
	
	/**
	 * return true - если ты волонтер или подал заявку.
	 * return false - если нет ни одной заявки на волонтерство.
	*/
    public function isVolunteerRequestExist(){
		
		$user = $this->getService('User')->getCurrentUser();
		if(!$user->mid_external) {
			return false;
		}
		
		if($this->getVolunteerStatus($user->mid_external) !== false){
			return true;				
		}		
		return false;
	}
	
	
	
	/**
	 * return статус волонтера
	*/
	public function getVolunteerStatus($mid_external = false, $filial = 1){
		if(!$mid_external) {			
			return false;
		}		
				
				
		$subSelect = $this->getSelect();
		$subSelect->from(
            array(
                'vm' => 'volunteer_members'
            ),
            array(
				'max_date' => new Zend_Db_Expr("MAX(vm.date_create)"),			
            )
        );
		$subSelect->where('vm.external_filial_id = ?', $filial); //--Филиал - РГСУ = 1
		$subSelect->where('vm.external_member_id = ?', $mid_external);
				
		$select = $this->getSelect();
        $select->from(
            array(
                'vm' => 'volunteer_members'
            ),
            array(
				'status' => 'vm.status',
				//'date_create' => 'vm.date_create',
				//'reason' => 'vm.reason',				
            )
        );
		$select->join(
			array('sub_vm' => $subSelect),
            'sub_vm.max_date = vm.date_create',
            array()
		);	
		
		$select->where('vm.external_member_id = ?', $mid_external); 
		$row = $select->query()->fetch();
		
		if(!$row){
			return false;
		}
		
		return $row['status'];		
	}
	
	
	/**
	 * причина исключения
	*/
	public function getVolunteerReason($mid_external = false, $filial = 1){
		if(!$mid_external) {			
			return false;
		}		
				
				
		$subSelect = $this->getSelect();
		$subSelect->from(
            array(
                'vm' => 'volunteer_members'
            ),
            array(
				'max_date' => new Zend_Db_Expr("MAX(vm.date_create)"),			
            )
        );
		$subSelect->where('vm.external_filial_id = ?', $filial); //--Филиал - РГСУ = 1
		$subSelect->where('vm.external_member_id = ?', $mid_external);
				
		$select = $this->getSelect();
        $select->from(
            array(
                'vm' => 'volunteer_members'
            ),
            array(
				//'status' => 'vm.status',
				//'date_create' => 'vm.date_create',
				'reason' => 'vm.reason',				
            )
        );
		$select->join(
			array('sub_vm' => $subSelect),
            'sub_vm.max_date = vm.date_create',
            array()
		);	
		
		$select->where('vm.external_member_id = ?', $mid_external); 
		$row = $select->query()->fetch();
		
		if(!$row){
			return false;
		}
		
		return $row['reason'];		
	}
	
	
	/**
	 * - return все заявки участия в мероприятиях.
	*/
	public function getVolunteerEvents($statusEvent = HM_Volunteer_VolunteerModel::EVENT_PRESENT){
		try {
 

		
		$user = $this->getService('User')->getCurrentUser();
		
		if(!$user->mid_external) {
			return false;
		}				
		
		$select = $this->getSelect();
        $select->from(
            array(
                'vem' => 'volunteer_event_members'
            ),
            array(				
				'date_create' => 'vem.date_create',
				//'date_update' => 'vem.date_update',
				'status' => 'vem.status',
				'external_filial_id' => 'vem.external_filial_id',
				'member_function' => 'vem.member_function',
				'role' => 'vem.role_id',
				'hours' => 'vem.hours',
				'event_name' => 'e.name',
				'date_begin' => 'e.date_begin',
				'date_end' => 'e.date_end',
				'address' => 'e.address',
				'manager' => 'e.manager',
				'phone' => 'e.phone',
				'description' => 'e.description',
				'ended' => 'e.ended',
            )
        );						
		$select->join(
			array('e' => 'volunteer_events'),
            'e.external_event_id = vem.external_event_id',
            array()
		);		
		$select->where('vem.external_member_id = ?', $user->mid_external); 					

		if($statusEvent == HM_Volunteer_VolunteerModel::EVENT_PAST){ //--прошедшие
			$select->where('e.ended = ?', 1); 			
			$select->where('e.date_end <= ?', date('Y-m-d', time())); 			
		} elseif($statusEvent == HM_Volunteer_VolunteerModel::EVENT_PRESENT){ //--настоящие
			$select->where('(e.ended IS NULL OR e.ended = ? )', 0); 
			$select->where('e.date_begin <= ?', date('Y-m-d', time())); 
			$select->where('e.date_end > ?', date('Y-m-d', time())); 			
		} elseif($statusEvent == HM_Volunteer_VolunteerModel::EVENT_FUTURE){ //--будущие
			$select->where('(e.ended IS NULL OR e.ended = ? )', 0); 
			$select->where('e.date_begin > ?', date('Y-m-d', time())); 			
		} elseif($statusEvent == HM_Volunteer_VolunteerModel::EVENT_ALL){ //--все
			
		} else { //--настоящее по умолчанию
			$select->where('(e.ended IS NULL OR e.ended = ? )', 0); 
			$select->where('e.date_begin <= ?', date('Y-m-d', time())); 
			$select->where('e.date_end > ?', date('Y-m-d', time())); 			
		}
			
		$events = $select->query()->fetchAll();
		
		if(count($events) < 1){
			return false;
		}
		
		$data = array(); //--разбиваем на временные категриии
		
		$roleNames = HM_Volunteer_VolunteerModel::getRoles();
		$statNames = HM_Volunteer_VolunteerModel::getNameStatusEvents();
		
		
		foreach($events as $i){			
			$i['status'] = $statNames[$i['status']];
			$i['role'] = $roleNames[$i['role']];
			
			
			if(
				$i['ended'] == 1 &&
				strtotime($i['date_end']) <= time()
			) {
				$data[HM_Volunteer_VolunteerModel::EVENT_PAST][] = $i;
			} elseif(
				empty($i['ended']) && 
				( strtotime($i['date_begin']) <= time() ) &&
				( strtotime($i['date_end'])	> time() 	)				
			){
				$data[HM_Volunteer_VolunteerModel::EVENT_PRESENT][] = $i;
			} elseif(
				empty($i['ended']) && 
				( strtotime($i['date_begin']) > time() )				
			){
				$data[HM_Volunteer_VolunteerModel::EVENT_FUTURE][] = $i;
			} 			
		}
		
		return $data;

		} catch (Exception $e) {
			echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
		}		
	}
	
	
	
	/**
	 * - return все заявки участия в мероприятиях. В виде запроса для грида
	*/
	public function getSelectEvents(){
		try {
 

		
		$user = $this->getService('User')->getCurrentUser();
		
		if(!$user->mid_external) {
			return false;
		}				
		
		$select = $this->getSelect();
        $select->from(
            array(
                'vem' => 'volunteer_event_members'
            ),
            array(				
				'event_name' => 'e.name',
				'date_create' => 'vem.date_create',
				'status' => 'vem.status',
				'date_begin' => 'e.date_begin',
				'date_end' => 'e.date_end',
				'event_member_id' => 'vem.event_member_id',
				
				//'date_update' => 'vem.date_update',
				
				//'external_filial_id' => 'e.external_filial_id',
				//'filial' => 'f.name',
				
				'address' => 'e.address',
				'manager' => 'e.manager',
				'phone' => 'e.phone',
				'member_function' => 'vem.member_function',
				'role' => 'vem.role_id',
				'hours' => 'vem.hours',
				
				//'description' => 'e.description',
				//'ended' => 'e.ended',
				'ended' => new Zend_Db_Expr("
					CASE  
						WHEN e.ended = 1 THEN '".HM_Volunteer_VolunteerModel::EVENT_PAST."' 
						ELSE 
							CASE  
								WHEN e.date_begin > '".date('Y-m-d',time())."' THEN '".HM_Volunteer_VolunteerModel::EVENT_FUTURE."' 
								ELSE 
									CASE  
										WHEN e.date_begin <= '".date('Y-m-d',time())."' AND e.date_end >= '".date('Y-m-d',time())."' THEN '".HM_Volunteer_VolunteerModel::EVENT_PRESENT."' 
										ELSE 
											CASE  
												WHEN e.date_end < '".date('Y-m-d',time())."' THEN '".HM_Volunteer_VolunteerModel::EVENT_PAST."' 
												ELSE ''
											END												
									END		
							END							
					END
				"),
				/*
				CASE  
  WHEN ebv.db_no IN (22978, 23218, 23219) THEN 'WECS 9500' 
  ELSE 'WECS 9520' 
END as wecs_system 
				*/
				
            )
        );						
		$select->join(
			array('e' => 'volunteer_events'),
            'e.external_event_id = vem.external_event_id',
            array()
		);	
		
		$select->join(
			array('f' => 'filials'),
            'e.external_filial_id = f.external_filial_id',
            array()
		);
		
		$select->where('vem.external_member_id = ?', $user->mid_external); 					
		/*
		if($statusEvent == HM_Volunteer_VolunteerModel::EVENT_PAST){ //--прошедшие
			$select->where('e.ended = ?', 1); 			
			$select->where('e.date_end <= ?', date('Y-m-d', time())); 			
		} elseif($statusEvent == HM_Volunteer_VolunteerModel::EVENT_PRESENT){ //--настоящие
			$select->where('(e.ended IS NULL OR e.ended = ? )', 0); 
			$select->where('e.date_begin <= ?', date('Y-m-d', time())); 
			$select->where('e.date_end > ?', date('Y-m-d', time())); 			
		} elseif($statusEvent == HM_Volunteer_VolunteerModel::EVENT_FUTURE){ //--будущие
			$select->where('(e.ended IS NULL OR e.ended = ? )', 0); 
			$select->where('e.date_begin > ?', date('Y-m-d', time())); 			
		} elseif($statusEvent == HM_Volunteer_VolunteerModel::EVENT_ALL){ //--все
			
		} else { //--настоящее по умолчанию
			$select->where('(e.ended IS NULL OR e.ended = ? )', 0); 
			$select->where('e.date_begin <= ?', date('Y-m-d', time())); 
			$select->where('e.date_end > ?', date('Y-m-d', time())); 			
		}
		*/
		
		//$events = $select->query()->fetchAll();
		//var_dump($events);
		return $select;
	
		
		//$content_grid = $grid->deploy();
		//echo $content_grid;
		
		/*		
		$events = $select->query()->fetchAll();
		
		if(count($events) < 1){
			return false;
		}
		
		$data = array(); //--разбиваем на временные категриии
		
		$roleNames = HM_Volunteer_VolunteerModel::getRoles();
		$statNames = HM_Volunteer_VolunteerModel::getNameStatusEvents();
		
		
		foreach($events as $i){			
			$i['status'] = $statNames[$i['status']];
			$i['role'] = $roleNames[$i['role']];
			
			
			if(
				$i['ended'] == 1 &&
				strtotime($i['date_end']) <= time()
			) {
				$data[HM_Volunteer_VolunteerModel::EVENT_PAST][] = $i;
			} elseif(
				empty($i['ended']) && 
				( strtotime($i['date_begin']) <= time() ) &&
				( strtotime($i['date_end'])	> time() 	)				
			){
				$data[HM_Volunteer_VolunteerModel::EVENT_PRESENT][] = $i;
			} elseif(
				empty($i['ended']) && 
				( strtotime($i['date_begin']) > time() )				
			){
				$data[HM_Volunteer_VolunteerModel::EVENT_FUTURE][] = $i;
			} 			
		}
		
		return $data;
		*/

		} catch (Exception $e) {
			//echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
		}		
	}
	
	/**
	 * return array;
	 * Список всех мероприятий определенного филиала, доступных волонтеру
	*/
	public function getListEvents($filial = 1){
		$user = $this->getService('User')->getCurrentUser();
		
		$data = array(
			'' => 'Нет',
		);
		
		if(!$user->mid_external) {
			return $data;
		}
		
		$select = $this->getSelect();
        $select->from(
            array(
                'e' => 'volunteer_events'
            ),
            array(				
				'volunteer_event_id' => 'e.volunteer_event_id',
				'name' => 'e.name',				
				'external_event_id' => 'e.external_event_id',				
            )
        );						
				
		$select->where('e.date_begin > ?', date('Y-m-d',time())); 		
		
		$rows = $select->query()->fetchAll();
		
		if(count($rows) < 1){
			return $data;
		}
		
		$selectBusy = $this->getSelect();
        $selectBusy->from(
            array('e' => 'volunteer_event_members'),
            array('external_event_id' => 'e.external_event_id')
        );	
		$selectBusy->where('e.external_member_id = ?', $user->mid_external); 
		$rowsBusy = $selectBusy->query()->fetchAll();
			
		$eventBusyIDs = array();
		foreach($rowsBusy as $i){
			$eventBusyIDs[] = $i['external_event_id'];			
		}

		foreach($rows as $r){
			
			if(!empty($r['volunteer_event_id']) && !empty($r['name']) && !in_array($r['external_event_id'],$eventBusyIDs) ){
				$data[$r['volunteer_event_id']] = $r['name'];
			}
		}		
		return $data;		
	}
	
	/**
	 * - создаем заявку на вступление в волонтеры в БД
	 *   return boolean
	*/
	public function sendVolunteerMemberReqest(){
		
		$user = $this->getService('User')->getCurrentUser();
		if(!$user->mid_external) {
			return false;
		}		
		
		if($this->isVolunteerRequestExist()){
			return false;
		}

		$isInsert = $this->insert(
			array(
				'external_member_id' => $user->mid_external,
				'date_create' => date('Y-m-d',time()),				
				'status' => 0, //--вписать из модели константу				
				'external_filial_id' => 1, //--определять потом филиал студента по оргструктуре??
				'external_1c_id' => NULL,
			)		
		);
		
		if($isInsert){
			return true;
		}				
		return false;
	}
	
	
	/**
	 * подаем заявку на участие в меропирятии.
 	*/
	public function sendEventRequest($eventId = false){
		$user = $this->getService('User')->getCurrentUser();
		if(!$user->mid_external) {
			return false;
		}
		
		$event = $this->getEvent($eventId);
		
		if(!$event){
			return false;
		}
				
		if($event['ended'] == 1){ //--если неактивна заявка
			return false;
		}
		
		if(strtotime($event['date_begin']) < time() ){ //--мероприятие уже началось.
			return false;
		}
		
		if($this->isEventRequestExist($user->mid_external, $event['external_event_id'])){ //--уже записан на это мкроприятие
			return false;
		}
		
		$dVol = Zend_Db_Table::getDefaultAdapter();		
		$data = array(
			'external_member_id' => $user->mid_external,
			'external_event_id' => $event['external_event_id'],
			'date_create' => date('Y-m-d',time()),
			'status' => HM_Volunteer_VolunteerModel::EVENT_NEW,			
			'external_filial_id' => 1,
		);
		if($dVol->insert('volunteer_event_members', $data)){
			return true;
		}
		return false;		
	}
	
	public function getEvent($eventId = false){

		if(!$eventId){
			return false;
		}

		$select = $this->getSelect();
        $select->from(
            array(
                'e' => 'volunteer_events'
            ),
            array(
				'e.*',
				'filial_name' => 'f.name'
			)
        );	
		$select->where('e.volunteer_event_id = ?', $eventId); 	
		
		$select->join( 
			array('f' => 'filials'),
            'f.external_filial_id = e.external_filial_id', 
            array()
		);
		
		$row = $select->query()->fetch();
		
		if($row){
			return $row;	
		}
		
		return false;
	}
	
	
	
	/**
	 *  записан ли студент на мероприятие.
	*/
	public function isEventRequestExist($mid_external, $extEventId = false){
		if(!$mid_external){
			return false;
		}		
		
		if(!$extEventId){
			return false;
		}
		
		$select = $this->getSelect();
        $select->from(
            array(
                'vem' => 'volunteer_event_members'
            ),
            array(
				'vem.event_member_id',				
			)
        );	
		$select->where('vem.external_event_id = ?', $extEventId); 
		$select->where('vem.external_member_id = ?', $mid_external); 
		$select->limit(1);
		
		$rows = $select->query()->fetchAll();
		
		if(count($rows) > 0){
			return true;
		}		
		return false;
	}
	
	//public function getIndexSelect() {
	/*
    public function getImportSelect() {
        $select = $this->getSelect();
        $select->from(
            array(
                'st' => 'Students'
            ),
            array(
				'CID' => 'st.CID',
				'SID' => 'st.SID',
				'session_external_id' => 'subj.external_id',											
				'time_ended_debtor' => 'st.time_ended_debtor',
				'mid_external' => 'p.mid_external',
				'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
				'session_name' => 'subj.name',				
				'base' => 'subj.base',				
				'MID' => 'st.MID',				
            )
        );

		$select->join(
			array(
				'p' => 'People'
			),
            'p.MID = st.MID',
            array(
			)
		);
		
		$select->join(
			array(
				'subj' => 'subjects'
			),
            'subj.subid = st.CID',
            array(
			)
		);		
		
		$select->where('subj.base = ?', HM_Subject_SubjectModel::BASETYPE_SESSION); //-- 2 - Отбираем сессии.
		$select->where('p.mid_external IS NOT NULL'); 
		$select->where('subj.external_id IS NOT NULL'); 
		$select->where('st.time_ended_debtor IS NULL'); 
        
        return $select;
    }    
	*/
}