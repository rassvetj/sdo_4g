<?php
class StudentCertificate_ListController extends HM_Controller_Action {
	
	public function indexAction()
	{
		$config             = Zend_Registry::get('config');
		$serviceCertificate = $this->getService('StudentCertificate');
		$user               = $this->getService('User')->getCurrentUser();
		$gridId             = 'grid';
		
		$this->view->setHeader(_('Все заявки'));		
		$this->view->headLink()->appendStylesheet($config->url->base.'css/rgsu_style.css');
				
		$select = $serviceCertificate->getSelect();
		
		$fields = array(
			'CertID'        => 'cs.CertID',        
			'StudyCode'		=> 'cs.StudyCode',
			'Type'          => 'cs.Type',
			'Number'        => 'cs.Number',
			'Destination'   => 'cs.Destination',
			'Status'        => 'cs.Status',
			'DateCreate'	=> 'cs.DateCreate',
			'Faculty'		=> 'cs.Faculty',
			'GroupName'		=> 'cs.GroupName',
			'Address'		=> new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(cs.City, ', ') , cs.Street), ', '), cs.Postcode)"),
			'Additional'	=> 'cs.CertID',		
			
			'Employer' 		=> 'cs.Employer',
			'Direction' 	=> 'cs.Direction',
			'Course' 		=> 'cs.Course',
			'Year' 			=> 'cs.Year',
			'Submission' 	=> 'cs.Submission',
			'date_from' 	=> 'cs.date_from',
			'date_to' 		=> 'cs.date_to',
			'place_work' 	=> 'cs.place_work',
			'period' 		=> 'cs.period',
			
			'document_series' 		=> 'cs.document_series',
			'document_number' 		=> 'cs.document_number',
			'document_issue_date' 	=> 'cs.document_issue_date',
			'document_issue_by' 	=> 'cs.document_issue_by',
			'privilege_type' 		=> 'cs.privilege_type',
			'privilege_date' 		=> 'cs.privilege_date',
			'document_status' 		=> 'cs.document_status',
			'date_update' 			=> 'cs.date_update',
		);
		
		$select->from(array('cs' => 'CertStud'), $fields);
		
		if(empty($user->mid_external)){
			$select->where('1=0');
		} else {
			$select->where($serviceCertificate->quoteInto('StudyCode=?', $user->mid_external));
		}
		
		
		$grid = $this->getGrid(
            $select,
            array(
                'student_certificate_id' => array('hidden' => true),                
                'StudyCode' 	         => array('hidden' => true),				     
				'CertID'	             => array('hidden' => true),
				'Type' 		             => array(
					'title'    => _('Тип'),
					'callback' => array('function' => array($this, 'updateType'), 'params' => array('{{Type}}')),
				),   
				'Number'	    => array('title' => _('Кол-во')),                
				'Destination'	=> array('title' => _('Место требования')),                
				'Status' 	    => array('hidden' => true),								
				'DateCreate'	=> array(
					'title'    => _('Дата'),
					'callback' => array('function' => array($this, 'updateDate'), 'params' => array('{{DateCreate}}'))
				),    
				'Faculty'		=> array('title' => _('Факультет')),                
				'GroupName'		=> array('title' => _('Группа')),                
				'Address'		=> array('title' => _('Адрес')),                
				'Additional'	=> array('title' => _('Дополнительно')), 
				
				
				'Employer' 		=> array('hidden' => true),
				'Direction' 	=> array('hidden' => true),
				'Course' 		=> array('hidden' => true),
				'Year' 			=> array('hidden' => true),
				'Submission' 	=> array('hidden' => true),
				'date_from' 	=> array('hidden' => true),
				'date_to' 		=> array('hidden' => true),
				'place_work' 	=> array('hidden' => true),
				'period' 		=> array('hidden' => true),
				
				'document_series' 		=> array('hidden' => true),
				'document_number' 		=> array('hidden' => true),
				'document_issue_date' 	=> array('hidden' => true),
				'document_issue_by' 	=> array('hidden' => true),
				'privilege_type' 		=> array('hidden' => true),
				'privilege_date' 		=> array('hidden' => true),
				'document_status' 		=> array('hidden' => true),
				'date_update' 			=> array('hidden' => true),
				
            ),
            array(
				'Type' 			=> array('values' => HM_StudentCertificate_StudentCertificateModel::getTypes()),
				'Status' 		=> array('values' => HM_StudentCertificate_StudentCertificateModel::getStatuses()),
                'Destination' 	=> null,                
				'DateCreate' 	=> array('render' => 'DateSmart'),				
				'Faculty' 		=> null,
				'GroupName' 	=> null,
				'Address' 		=> null,
				'Additional' 	=> null,
            ),
            $gridId
        );		
		
		
		$grid->updateColumn('DateCreate', array(
            'format' => array(
                'DateTime',
                array('date_format' => Zend_Locale_Format::getDateTimeFormat())
            ),
            'callback' => array(
                'function' => array($this, 'updateDate'),
                'params' => array('{{DateCreate}}')
            )
        ));
		
		
		$grid->updateColumn('Additional', array(            
            'callback' => array(
                'function' 	=> array($this, 'updateAdditional'),
                'params' 	=> array(
								'{{Employer}}', '{{Direction}}', '{{Course}}', '{{Year}}', '{{Submission}}', '{{date_from}}', '{{date_to}}', '{{place_work}}', '{{period}}',
								'{{document_series}}', '{{document_number}}', '{{document_issue_date}}', '{{document_issue_by}}', '{{privilege_type}}', '{{privilege_date}}',
								'{{document_status}}', '{{date_update}}'
								)
            )
        ));
		
		
		$this->view->gridAjaxRequest = $this->isAjaxRequest();
		$this->view->grid            = $grid->deploy();
		
	}
	
	public function updateType($type)
	{
        $types = HM_StudentCertificate_StudentCertificateModel::getTypes();
        return $types[$type];
    }
	
	public function updateDate($date)
    {
		if (!strtotime($date)) return '';		
        return $date;
    }
	
	public function updateAdditional($employer, $direction, $course, $year, $submission, $date_from, $date_to, $place_work, $period,
									$document_series, $document_number, $document_issue_date, $document_issue_by, $privilege_type, $privilege_date, $document_status, $date_update
	){
		$data = array();
		
		if(!empty($employer)){
			$data[] = '<p> <span style="color: #A8A6A6;">Работодатель:</span> '.$employer.'</p>';
		}
		
		if(!empty($direction)){
			$data[] = '<p><span style="color: #A8A6A6;">Направление:</span> '.$direction.'</p>';
		}
		
		if(!empty($course)){
			$data[] = '<p><span style="color: #A8A6A6;">Курс:</span> '.$course.'</p>';
		}
		
		if(!empty($year)){
			$data[] = '<p><span style="color: #A8A6A6;">Год:</span> '.$year.'</p>';
		}
		
		if(!empty($submission)){
			$data[] = '<p><span style="color: #A8A6A6;">Место представления:</span> '.$submission.'</p>';
		}
		
		if(!empty($date_from)){
			$data[] = '<p><span style="color: #A8A6A6;">Период с</span> '.date('d.m.Y', strtotime($date_from)).' <span style="color: #A8A6A6;">по</span> '.date('d.m.Y', strtotime($date_to)).'</p>';
			
		}
		
		if(!empty($place_work)){
			$data[] = '<p><span style="color: #A8A6A6;">Место работы:</span> '.$place_work.'</p>';
		}
		
		if(!empty($period)){
			$data[] = '<p><span style="color: #A8A6A6;">Период начисления стипендии:</span> '.$period.'</p>';
		}
		
		if(!empty($document_series)){
			$data[] = '<p><span style="color: #A8A6A6;">Серия:</span> '.$document_series.'</p>';
		}
		
		if(!empty($document_number)){
			$data[] = '<p><span style="color: #A8A6A6;">Серия:</span> '.$document_number.'</p>';
		}
		
		if(!empty($document_issue_date)){
			$data[] = '<p><span style="color: #A8A6A6;">Дата выдачи:</span> '.date('d.m.Y', strtotime($document_issue_date)).'</p>';
		}
		
		if(!empty($document_issue_by)){
			$data[] = '<p><span style="color: #A8A6A6;">Кем выдан:</span> '.$document_issue_by.'</p>';
		}
		
		if(!empty($privilege_type)){			
			$list_privilege = HM_StudentCertificate_StudentCertificateModel::getPrivilegeTypeList();			
			$data[] = '<p><span style="color: #A8A6A6;">Вид льготы:</span> '.$list_privilege[$privilege_type].'</p>';
		}
		
		if(!empty($privilege_date)){
			$data[] = '<p><span style="color: #A8A6A6;">Срок действия льготы:</span> '.date('d.m.Y', strtotime($privilege_date)).'</p>';
		}
		
		if(!empty($document_status)){
			$data[] = '<p><span style="color: #A8A6A6;">Статус:</span> '.$document_status.'</p>';
		}
		
		if(!empty($date_update)){
			$data[] = '<p><span style="color: #A8A6A6;">Изменен:</span> '.date('d.m.Y', strtotime($date_update)).'</p>';
		}
		
		
		if(count($data) > 1){
			array_unshift($data, '<p class="total">Подробно</p>');
		}
		
		return implode('', $data);
		
	}
	
	
	
}



