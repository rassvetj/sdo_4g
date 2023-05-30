<?php
class HM_RecordCard_RecordCardService extends HM_Service_Abstract
{	
	private $_ftp_server = 'srv-fs-1';
	private $_ftp_login  = 'uopphoto';
	private $_ftp_pass   = 'SdCv64478!';
	
	public function getIndexSelect() {
        $select = $this->getSelect();
        $select->from(
            array(
                //'r' => 'RegCardStud'
                'r' => 'record_cards'
            ),
            array(
                //'RegCardID'       => 'r.RegCardID',
                'record_card_id'    => 'r.record_card_id',
                'StudyCode'			=> 'r.StudyCode',
                'TypeOrder'			=> 'r.TypeOrder',
                'Reason'			=> 'r.Reason',
                'Code'				=> 'r.Code',
                //'DateFrom'		=> 'r.DateFrom',
                'DateFrom'			=> new Zend_Db_Expr('CONVERT(VARCHAR, r.DateFrom, 104)'),
                'StatusStud'		=> 'r.StatusStud',
                //'DateTake'		=> 'r.DateTake',
                'DateTake'			=> new Zend_Db_Expr('CONVERT(VARCHAR, r.DateTake, 104)'),
                'YearStudy'			=> 'r.YearStudy',
                'Faculty'			=> 'r.Faculty',
                'Speciality'		=> 'r.Speciality',
                'Specialization'	=> 'r.Specialization',
                'Form'				=> 'r.Form',
                'Course'			=> 'r.Course',
                'Based'				=> 'r.Based',
                'TypeProgram'		=> 'r.TypeProgram',
                'Curriculum'		=> 'r.Curriculum',
                'DistLearning'		=> 'r.DistLearning',
                'SvsuFinance'		=> 'r.SvsuFinance',
                'Note'				=> 'r.Note',
                'OrderNote'			=> 'r.OrderNote',				
				//'DateCreate'		=> new Zend_Db_Expr('CONVERT(VARCHAR, r.DateCreate, 104)'),
				'sortDateTake'		=> 'r.DateTake',				
            )
        );
        return $select;
    }	
	
	
	/**
	 * Первый документ о зачислении со статусом "учится"
	*/
	public function getFirstActualOrder($mid_external){
		if($mid_external === false){
			return false;
		}
		
		$select = $this->getSelect();
		$select->from($this->getMapper()->getAdapter()->getTableName());
		$select->where($this->quoteInto(
			array(' StudyCode = ? ', ' AND StatusStud = ? ', ' AND (YearStudy IS NOT NULL OR YearStudy!=?) '), 
			array($mid_external, HM_RecordCard_RecordCardModel::STATUS_STUDY, '')
		));
		$select->order('DateFrom');
		#fedyaev
		#print_r($select);
		#exit;
		$first_doc = $select->query()->fetchObject();
		
		if(empty($first_doc)){ return false; }		
		
		$temp = explode('/', $first_doc->YearStudy);
		$first_doc->year_begin = (isset($temp[0])) ? ($temp[0]) : (false);
		
		# поправка на специальность и специализацию в случае их смены
		$res = $select->query()->fetchAll();
		foreach($res as $i){
			if(!in_array($i['Reason'], array('На другую специальность', 'Изменение группы', 'Восстановлении и переводе на другую специальность', 'Из филиала в головной вуз'))){ continue; }			
			$first_doc->Faculty 		= $i['Faculty'];
			$first_doc->Speciality 		= $i['Speciality'];
			$first_doc->Specialization	= $i['Specialization'];
		}
		
		return $first_doc;		
	}
	
	
	/**
	 * приказ о доступе к ГИА.
	*/
	public function getGIA($mid_external){
		if($mid_external === false){
			return false;
		}
		
		$res = $this->getOne($this->fetchAll(
					array(
						'StudyCode = ?' => $mid_external,
						'StatusStud = ?' => HM_RecordCard_RecordCardModel::STATUS_STUDY,
						'YearStudy IS NOT NULL' => '',
						'TypeOrder = ?' => HM_RecordCard_RecordCardModel::TYPE_GIA,
					)
				));
				
		if(!$res){
			return false;
		}		
		return $res;
	}
	
	
	public function getGOS($mid_external){
		if($mid_external === false){
			return false;
		}
		
		$q = $this->getSelect();
		$q->from(
				'student_state_exams',
				array(
					'name',
					'date_exam',
					'ball',
				)
		);
		$q->where('mid_external = ?', $mid_external);		
		$res = $q->query()->fetchAll();
		if(!count($res)){
			return false;
		}
		return $res;
	}
	
	public function getGOSFrom1C($mid_external)
	{
		$mid_external = trim($mid_external);
		if(empty($mid_external)){
			return false;
		}
		
		$data       = array();
		$config     = Zend_Registry::get('config')->soap->student_recordbook;
		$soapClient = new Zend_Soap_Client();
		$params     = array('StudyCode' => $mid_external);
		
		$soapClient->setWsdl($config->wsdl);
		$soapClient->setHttpLogin($config->login);
		$soapClient->setHttpPassword($config->password);
		
		try {
			$response = $soapClient->Get_study_cards($params);
		} catch (Exception $e) {			
			error_log($e);
			return false;
		} 
		
		$items = $response->return->package_student_state_exams->student_state_exams;
		if(empty($items)){
			return false;
		}
		
		if(is_object($items)){
			$data[] = array(
				'name'      => $items->Name,
				'date_exam' => $items->Date_exam,
				'ball'      => $items->Ball,
			);
			return $data;
		}
		
		foreach($items as $item){
			$data[] = array(
				'name'      => $item->Name,
				'date_exam' => $item->Date_exam,
				'ball'      => $item->Ball,
			);
		}
		return $data;
	}
	
	public function getGraduationWork($mid_external){
		if($mid_external === false){
			return false;
		}
		
		$q = $this->getSelect();
		$q->from('student_graduation_works',
				array(
					'type_work',					
					'theme',
					'manager',
					'date_graduation_work',
					'ball',
					'date_commission',
					'protocol_number',
					'qualification',
					'rank',
					'chair',
					'members_commission',
					'diplom_series',
					'diplom_number',
					'date_diplom',
				)
		);
		$q->where('mid_external = ?', $mid_external);
		
		$g = $q->query()->fetch();
		if(!$g){			
			return false;
		}
		return $g;
	}
	
	public function getGraduationWorkFrom1C($mid_external)
	{
		$mid_external = trim($mid_external);
		if(empty($mid_external)){
			return false;
		}
		
		$config     = Zend_Registry::get('config')->soap->student_recordbook;
		$soapClient = new Zend_Soap_Client();
		$params     = array('StudyCode' => $mid_external);
		
		$soapClient->setWsdl($config->wsdl);
		$soapClient->setHttpLogin($config->login);
		$soapClient->setHttpPassword($config->password);
		
		try {
			$response = $soapClient->Get_study_cards($params);
		} catch (Exception $e) {			
			error_log($e);
			return false;
		} 
		
		$item = $response->return->package_student_graduation_works->student_graduation_works;
		if(empty($item)){
			return false;
		}
		
		if(is_array($item)){
			$item = reset($item);
		}
		
		return array(
			'type_work'            => $item->Type_work,
			'theme'                => $item->Theme,
			'manager'              => $item->Manager,
			'date_graduation_work' => $item->Date_graduation_work,
			'ball'                 => $item->Ball,
			'date_commission'      => $item->Date_commission,
			'protocol_number'      => $item->Protocol_number,
			'qualification'        => $item->Qualification,
			'rank'                 => $item->Rank,
			'chair'                => $item->Chair,
			'members_commission'   => $item->Members_commission,
			'diplom_series'        => $item->Diplom_series,
			'diplom_number'        => $item->Diplom_number,
			'date_diplom'          => $item->Date_diplom,
		);
	}
	
	
	public function getRecordbookNumber($mid_external){
		$res = $this->getRecordbookInfo($mid_external);
		return $res->number;
	}
	
	/**
	 * массив номеров зачеток по указанным id студентов
	*/
	public function getRecordbookNumbers($userIDs = null){
		
		$select = $this->getSelect();
		$select->from(array('r' => 'student_recordbooks'),
				array(
					'mid' => 'p.mid',
					'recordbook_number' => 'r.number',
				)
		);
		$select->join(array('p' => 'People'), 'p.mid_external = r.mid_external', array() );
		if(!empty($userIDs)){ 
			$select->where($this->quoteInto('p.mid IN (?)', $userIDs));	
		}
		$res = $select->query()->fetchAll();
		if(empty($res)){ return false; }
		$data = array();
		foreach($res as $i){
			$data[$i['mid']] = $i['recordbook_number'];
		}
		return $data;		
	}
	
		
	public function getRecordbookInfo($mid_external){	
		if(empty($mid_external)){ return false; }		
		
		$select = $this->getSelect();
		$select->from('student_recordbooks', array('student_recordbook_id', 'mid_external', 'number', 'semester', 'study_form', 'guid', 'fio_dative', 'date_birth', 'date_graduation'));
		$select->where($this->quoteInto('mid_external = ?', $mid_external));
		return $select->query()->fetchObject();
	}	
	
	
	
	public function getPhoto($mid_external)
	{
		$info 	   				= $this->getRecordbookInfo($mid_external);
		$server_file_name		= $info->guid.'.jpg';
		$tmpfname_local_file	= tempnam(sys_get_temp_dir(), 'ava');
		
		$conn_id  = ftp_connect($this->_ftp_server);
		if(!$conn_id){ return false; }
		
		$is_login = @ftp_login($conn_id, $this->_ftp_login, $this->_ftp_pass);
		if(!$is_login){ 
			ftp_close($conn_id);
			return false; 
		}
		ftp_pasv($conn_id, true);
		
		$is_get_file = @ftp_get($conn_id, $tmpfname_local_file, $server_file_name, FTP_BINARY);
		if(!$is_get_file){ 
			ftp_close($conn_id);
			return false; 
		}
		ftp_close($conn_id);
		
		$content = file_get_contents($tmpfname_local_file);
		
		@unlink($tmpfname_local_file);
		return $content;
	}
	
	/**
	 * @return collection
	 * получаем все договоры студента
	*/
	public function getUserOrders($mid_external)
	{
		return $this->fetchAll($this->quoteInto('StudyCode = ?', $mid_external));
	}
	
	
	/**
	 * кол-во дней отсрочки. На данный момент только академ.
	 * @return int	 
	*/
	public function getDelayDays($mid_external)
	{
		if(empty($mid_external)){ return 0; }
		
		$res =	$this->fetchAll(
								$this->quoteInto(array('StudyCode = ?', ' AND TypeOrder = ? '), array($mid_external, HM_RecordCard_RecordCardModel::TYPE_ORDER_ACADEMIC_LEAVE)),
								'DateTake'
								);
		if(empty($res)){ return false; }
		
		$days = 0;
		# сортировка обязательна.
		# сначала ищен открытие периода отсрочки.
		$date_start = false;
		$date_end	= false;
		foreach($res as $i){
					
			if(in_array($i->Reason, HM_RecordCard_RecordCardModel::getAcademicLeaveStartReasons())){
				$date_start = $i->DateTake;
			}
			
			if(in_array($i->Reason, HM_RecordCard_RecordCardModel::getAcademicLeaveEndReasons())){
				$date_end = $i->DateTake;				
			}
			
			if($date_start !== false && $date_end !== false ){ 
				$timestamp_start 	= strtotime($date_start);
				$timestamp_end		= strtotime($date_end);
				
				if($timestamp_start <= 0 || $timestamp_end <= 0){
					$date_start = $date_end = false;
					continue;
				}
				
				$dt_start	= new DateTime();
				$dt_end		= new DateTime();
				
				$dt_start->setTimestamp($timestamp_start);
				$dt_end->setTimestamp($timestamp_end);
				
				$interval 	 = $dt_start->diff($dt_end);
				$days 		+= (int)$interval->format('%a');
				
				$date_start = $date_end = false;
				continue;	
			}
		}
		return $days;
	}
	
	
	
	
}