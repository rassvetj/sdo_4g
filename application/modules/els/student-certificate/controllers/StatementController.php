<?php
//class Certificate_IndexController extends HM_Controller_Action {
class StudentCertificate_StatementController extends HM_Controller_Action {
	
	protected $_studCertService = null;

    protected $_studentCertificateID  = 0;
	protected $_facultet = '';
	
	public function init(){
		parent::init();
	}
	
	
	
	
	public function indexAction()
    {			
		$this->_redirector->gotoSimple('index', 'student-certificate', 'default');
		
		$this->view->setHeader(_('Заявления'));	
		$config = Zend_Registry::get('config');		
		$this->view->headLink()->appendStylesheet($config->url->base.'css/rgsu_style.css');		
		$this->view->form = new HM_Form_Statement();
		
		#$custom_tpl = $this->view->render('statement/_parts/form.tpl');
		#$this->view->form = $custom_tpl;
		$this->view->gridAjaxRequest 	= $this->isAjaxRequest();
		$this->view->grid 				= $this->getGridContent();
	}
	
	
	public function saveAction(){
		$this->getHelper('viewRenderer')->setNoRender();
		$user	= $this->getService('User')->getCurrentUser();		
		$form	= new HM_Form_Statement();
		
		$return = array(
            'code' => 0,
            'message' => _('Заполните все поля')
        );
		
		$request = $this->getRequest();
		
		if ($request->isPost() || $request->isGet()) {
			$form = $this->modifyForm($form);
			
			if ($form->isValid($request->getParams())) {
				
				$data = array(
					'type_id'		=> (int)$request->getParam('type_id'),
					'mid_external'	=> $user->mid_external,
					'status_id'		=> HM_StudentCertificate_Statement_StatementModel::STATUS_NEW,
					'date_create'	=> new Zend_Db_Expr("NOW()"),
				);
				$all_fielsd  = HM_StudentCertificate_Statement_StatementModel::getAllFields();
				$date_fields = array('date_birth', 'passport_issued_date', 'date_begin', 'date_end');
				foreach($all_fielsd as $field_name){
					if(isset($data[$field_name])){ continue; }
					$field_value = $request->getParam($field_name, false);
					$field_value = trim(strip_tags($field_value));
					if(empty($field_value)){ continue; }
					
					
					if(in_array($field_name, $date_fields)){
						$field_value = date('Y-m-d', strtotime($field_value));
					}
					$data[$field_name] = $field_value;
				}
				
				try {
					$isInsert = $this->getService('StudentCertificateStatement')->insert($data);
				} catch (Exception $e) {}
				
				if(empty($isInsert)){					
					$return['message'] = 'Не удалось создать заявление. Попробуйте позже.';
				} else {
					$return['code']		= 1;
					$return['message']	= 'Заявление успешно добавлено.';
					$form				= new HM_Form_Statement(); # сброс полей формы
				}				
				
				echo $this->view->notifications(array(array(
					'type' => $return['code'] != 1 ? HM_Notification_NotificationModel::TYPE_ERROR : HM_Notification_NotificationModel::TYPE_SUCCESS,
					'message' => $return['message']
				)), array('html' => true));
			
			}			
		}
		#$this->view->form = $form;
		#$custom_tpl = $this->view->render('statement/_parts/form.tpl');
		#echo $custom_tpl;
		echo $form;	
	}
	
	
	
	protected function modifyForm($form){
		$request 		 = $this->getRequest();
		$all_fields 	 = $request->getParams();
		$type_id 		 = (int)$request->getParam('type_id');
		$required_fields =  HM_StudentCertificate_Statement_StatementModel::getRequiredFields($type_id);
		
		foreach($all_fields as $field_name => $val){
			$el = $form->getElement($field_name);
			if(!is_object($el)){ continue; }			
			$form->getElement($field_name)->setOptions(array('Required' => false));			
		}
		
		if(empty($required_fields)){ return $form; }
		
		foreach($required_fields as $field_name){
			$el = $form->getElement($field_name);	
			if(!is_object($el)){ continue; }			
			$form->getElement($field_name)->setOptions(array('Required' => true));	
		}		
		return $form;		
	}
	
	
	
	public function getGridContent(){
		$grid_id			= 'grid-statement';
		$user				= $this->getService('User')->getCurrentUser();
		$serviceStatement	= $this->getService('StudentCertificateStatement');
		$select = $serviceStatement->getSelect();
		$select->from(array('s' => 'student_statements'), array(
			'type_id'		=> 's.type_id',	
			'fio'			=>	new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(s.LastName, ' ') , s.FirstName), ' '), s.Patronymic)"),
			'reason'		=> 's.reason',	
			'additional'	=> 's.type_id',
			
			'date_birth'			=> 's.date_birth',			
			'passport_series'		=> 's.passport_series',
			'passport_number'		=> 's.passport_number',			
			'passport_issued_name'	=> 's.passport_issued_name',
			'passport_issued_date'	=> 's.passport_issued_date',
			'passport_issued_code'	=> 's.passport_issued_code',
			'address_registration'	=> 's.address_registration',
			'address_birth'			=> 's.address_birth',
			'date_begin'			=> 's.date_begin',
			'date_end'				=> 's.date_end',
			'remand_type_id'		=> 's.remand_type_id',
			
			
			'status_id'				=> 's.status_id',
			'date_create'			=> 's.date_create',
			
			'external_info'			=> 's.status_id',			
			'statement_id_1c'		=> 's.statement_id_1c',
			'order_id'				=> 's.order_id',
			'order_date'			=> 's.order_date',
			
			
			
		));
		$select->where($serviceStatement->quoteInto('s.mid_external = ?', $user->mid_external));
		
		$grid = $this->getGrid(
            $select,
            array(
				'date_birth' 		=> array('hidden' => true),
				'passport_series'	=> array('hidden' => true),
				'passport_number'	=> array('hidden' => true),
				'passport_issued_name'	=> array('hidden' => true),
				'passport_issued_date'	=> array('hidden' => true),
				'passport_issued_code'	=> array('hidden' => true),
				'address_registration'	=> array('hidden' => true),
				'address_birth'			=> array('hidden' => true),
				'date_begin'			=> array('hidden' => true),
				'date_end'				=> array('hidden' => true),
				'remand_type_id'		=> array('hidden' => true),
				'statement_id_1c'		=> array('hidden' => true),
				'order_id'				=> array('hidden' => true),
				'order_date'			=> array('hidden' => true),

			
				'type_id' 		=> array(
					'title' => _('Тип'),
					'callback' => array('function' => array($this, 'updateType'), 'params' => array('{{type_id}}')),
				),			
				'fio'		=> array('title' => _('ФИО')),                
				'reason'	=> array('title' => _('Причина')),   
				
				
				'additional' 		=> array(
					'title' => _('Подробно'),
					'callback' => array(
						'function' 	=> array($this, 'updateAdditional'), 
						'params' 	=> array('{{additional}}', '{{date_birth}}', '{{passport_series}}', '{{passport_number}}', '{{passport_issued_name}}', '{{passport_issued_date}}', '{{passport_issued_code}}',
												'{{address_registration}}', '{{address_birth}}', '{{date_begin}}', '{{date_end}}', '{{remand_type_id}}'
																											)),
				),
				
				
				'status_id'			=> array('hidden' => true),
				/*
				'status_id' 		=> array(
					'title' => _('Статус'),
					'callback' => array('function' => array($this, 'updateStatus'), 'params' => array('{{status_id}}')),
				),
				*/

				'date_create' 		=> array(
					'title' 	=> _('Дата создания'),
					'format' 	=> array('date', array('date_format' => HM_Locale_Format::getDateFormat())),
					'callback' 	=> array('function' => array($this, 'updateDate'), 'params' => array('{{date_create}}')),
				),


				'external_info' 		=> array(
					'title' => _('Дополнительно'),
					'callback' => array('function' => array($this, 'updateExternalInfo'), 'params' => array('{{external_info}}', '{{statement_id_1c}}', '{{order_id}}', '{{order_date}}')),
				),				
				
            ),
            array(
				'type_id'		=> array('values' => HM_StudentCertificate_Statement_StatementModel::getTypes()),
				'fio' 			=> null,
				'reason' 		=> null,	
				'status_id'		=> array('values' => HM_StudentCertificate_Statement_StatementModel::getStatuses()),
				'date_create'	=> array('render' => 'DateSmart'),											
            ),
            $grid_id
        );
		
		
		$grid->setExport(array('print'));
		return $grid->deploy();		
	}
	
	
	public function updateType($type_id){
		$types = HM_StudentCertificate_Statement_StatementModel::getTypes();
		return $types[$type_id];		
	}
	
	public function updateStatus($status_id){
		$statuses = HM_StudentCertificate_Statement_StatementModel::getStatuses();
		return $statuses[intval($status_id)];
	}
	
	public function updateDate($date){
		return $date;		
	}
	
	public function updateAdditional($type_id, $date_birth, $passport_series, $passport_number, $passport_issued_name, $passport_issued_date, $passport_issued_code, $address_registration, $address_birth,
										$date_begin, $date_end, $remand_type_id){
		$str = '';
		if($type_id == HM_StudentCertificate_Statement_StatementModel::TYPE_CHANGE_FIO){
			if(strtotime($date_birth) > 0){
				$str .= 'Дата рождения: <b>'.date('d.m.Y', strtotime($date_birth)).'</b><br />';
			}			
			$str .= 'Паспорт: серия <b>'.$passport_series.'</b>, номер <b>'.$passport_number.'</b>, выдан <b>'.$passport_issued_name.'</b>';
			
			if(strtotime($passport_issued_date) > 0){
				$str .= ', <b>'.date('d.m.Y', strtotime($passport_issued_date)).'</b>';
			}
			$str .= ', код подразделения <b>'.$passport_issued_code.'</b>.<br />';			
			$str .= 'Адрес регистрации: <b>'.$address_registration.'</b><br />';
			$str .= 'Место рождения: <b>'.$address_birth.'</b><br />';
		
		} elseif($type_id == HM_StudentCertificate_Statement_StatementModel::TYPE_ACADEM_HOLIDAY){
			$str .= 'Период с <b>'.date('d.m.Y', strtotime($date_begin)).'</b> по <b>'.date('d.m.Y', strtotime($date_end)).'</b><br />';
		
		} elseif($type_id == HM_StudentCertificate_Statement_StatementModel::TYPE_REMAND){
			$remand_types = HM_StudentCertificate_Statement_StatementModel::getRemandTypes();
			$str .= 'Основание: <b>'.$remand_types[$remand_type_id].'</b><br />';
		}
		
		return $str;
	}
	
	
	public function updateExternalInfo($status_id, $statement_id_1c, $order_id, $order_date){		
		$str = '';
		if($status_id == HM_StudentCertificate_Statement_StatementModel::STATUS_IN_WORK){
			$str .= 'Присвоен номер обращения <span style="white-space:nowrap;">№<b>'.$statement_id_1c.'</b></span>';
		} elseif($status_id == HM_StudentCertificate_Statement_StatementModel::STATUS_READY){
			$str .= 'Издан приказ от <b>'.date('d.m.Y', strtotime($order_date)).'</b> №<b>'.$order_id.'</b>';
		}
		return $str;		
	}
	
	public function getGridAction(){
		$this->getHelper('viewRenderer')->setNoRender();
		echo $this->getGridContent();		
	}
	
}