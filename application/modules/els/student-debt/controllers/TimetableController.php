<?php
class StudentDebt_TimetableController extends HM_Controller_Action
{
    
    public function init()
    {
		parent::init();
    }
	
	private function _getGridId()
	{
		return 'gridStudentDebt';
	}
	
	public function indexAction()
	{
		$config = Zend_Registry::get('config');
		$this->view->setHeader(_('Расписание ликвидации задолженностей'));
		$this->view->headLink()->appendStylesheet($config->url->base.'css/rgsu_style.css');
		
		#$user_group_name 		= $this->getService('StudyGroupUsers')->getUserGroupName($this->getService('User')->getCurrentUserId());
		#$user_group_name 		= 'СКД-Б-0-Д-2017-1'; # для тестов
		
		$user = $this->getService('User')->getCurrentUser();
		
		$serviceDebtSchedule 	= $this->getService('StudentDebtSchedule');
		$this->view->data 		= $serviceDebtSchedule->fetchAll($serviceDebtSchedule->quoteInto('mid_external = ?', $user->mid_external), 'date ASC');		
		$this->view->data 		= $this->prepareData($this->view->data);
		$this->view->fields 	= HM_StudentDebt_Schedule_ScheduleModel::getExportFieldList();
	}
    
    
    public function managerAction()
    {
		$config = Zend_Registry::get('config');
		$this->view->setHeader(_('Расписание ликвидации задолженностей. Управление'));		
		$this->view->headLink()->appendStylesheet($config->url->base.'css/rgsu_style.css');		
		
		$this->view->form		= new HM_Form_ImportSchedule();
		$serviceDebtSchedule	= $this->getService('StudentDebtSchedule');
		
		$fields = array(
			'schedule_id',
			'mid_external',
			'student_fio',
			'group_name',
			'chair',
			'discipline',
			'study_form',
			'specialty',
			'course',
			'control_form',
			'teacher',
			'date',
			'place',
			'programm',
			'semester',
			'attempt',
			'language',
			'commission_url',
			'date_created',
		);
		
		$select = $serviceDebtSchedule->getSelect();
		$select->from('student_debts_schedule', $fields);		 
		//$select->where($serviceUser->quoteInto('mid_external IN (?)', $mid_externals));
		
		$grid = $this->getGrid($select,
            array(
                'schedule_id'    => array('hidden' => true),
                'mid_external'   => array('title' => _('Код студента')),
                'student_fio'    => array('title' => _('Студент')),
                'group_name'     => array('title' => _('Группа')),
                'chair'          => array('title' => _('Кафедра')),
                'discipline'     => array('title' => _('Дисциплина')),
                'study_form'     => array('title' => _('Форма')),
                'specialty'      => array('title' => _('Специальность')),
                'course'         => array('title' => _('Курс')),
                'control_form'   => array('title' => _('Контроль')),
                'teacher'        => array('title' => _('Тьютор')),
                'date'           => array('title' => _('Дата')),
                'place'          => array('title' => _('Место')),
                'programm'       => array('title' => _('Программа')),
                'semester'       => array('title' => _('Семестр')),
                'attempt'        => array('title' => _('Попытка')),
                'language'       => array('title' => _('Язык')),
                'commission_url' => array('title' => _('Ссылка')),
                'date_created'   => array('title' => _('Дата создания')),
			),
			array(
				'mid_external'   => null,				
				'student_fio'    => null,
				'group_name'     => null,
				'chair'          => null,
				'discipline'     => null,
				'study_form'     => null,
				'specialty'      => null,
				'course'         => null,
				'control_form'   => null,
				'teacher'        => null,				
				'date'           => array('render' => 'Date'),
				'place'          => null,
				'programm'       => null,
				'semester'       => null,
				'attempt'        => null,
				'language'       => null,
				'commission_url' => null,				
				'date_created'   => array('render' => 'Date'),
			),
			$this->_getGridId()
		);
		
		$grid->updateColumn('date', array('callback' => array(
			'function' => array($this, 'updateDateTime'),
            'params'   => array('{{date}}')
		)));
		$grid->updateColumn('date_created', array('callback' => array(
			'function' => array($this, 'updateDateTime'),
            'params'   => array('{{date_created}}')
		)));
		$grid->updateColumn('commission_url', array('callback' => array(
			'function' => array($this, 'updateUrl'),
            'params'   => array('{{commission_url}}')
		)));
		
		$grid->addAction(
            array('module' => 'student-debt', 'controller' => 'timetable', 'action' => 'delete-one'),
            array('schedule_id'),
            $this->view->icon('delete')
        );

        $grid->addMassAction(
            array('module' => 'student-debt', 'controller' => 'timetable', 'action' => 'delete-mass'),
            _('Удалить'),
            _('Вы уверены?')
        );
		
        
		$this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid            = $grid->deploy();
		
		#$this->view->data		= $serviceDebtSchedule->fetchAll('1=1', 'group_name, date ASC');
		#$this->view->fields 	= HM_StudentDebt_Schedule_ScheduleModel::getExportFieldList();
    }
	
	public function importAction()
	{
		$remove_old = (int)$this->_getParam('remove_old', 0);
		$request 	= $this->getRequest();
		
		$form = new HM_Form_ImportSchedule();
		
		if (!is_object($form->file)) {
			$this->_flashMessenger->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('Некорректные данные'))
			);		
			$this->_redirector->gotoSimple('manager', 'timetable', 'student-debt');
		}
		
		if (!$form->isValid($request->getParams())){
			$this->_flashMessenger->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('Загрузите файл'))
			);
			$this->_redirector->gotoSimple('manager', 'timetable', 'student-debt');
		}
		
		if(!$form->file->isUploaded() || !$form->file->receive() || !$form->file->isReceived()){
			$this->_flashMessenger->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('Не удалось загрузить файл'))
			);
			$this->_redirector->gotoSimple('manager', 'timetable', 'student-debt');
		}
		
		$error_code = $this->getService('StudentDebtSchedule')->import($form->file->getFileName(), $remove_old);		
		
		if($error_code !== true){
			$this->_flashMessenger->addMessage(
				array(	'type'		=> HM_Notification_NotificationModel::TYPE_ERROR,
						'message' 	=> HM_StudentDebt_Schedule_ScheduleModel::getErrorText($error_code))
			);
			$this->_redirector->gotoSimple('manager', 'timetable', 'student-debt');			
		}
		
		$this->_flashMessenger->addMessage(
			array(	'type' 		=> HM_Notification_NotificationModel::TYPE_SUCCESS,
					'message'	=> _('Импорт завершен без ошибок'))
		);
		$this->_redirector->gotoSimple('manager', 'timetable', 'student-debt');
		die;
	}
	
	
	// Выгрузка в csv
	public function exportAction()
	{
		$this->_helper->getHelper('layout')->disableLayout();        
		Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();
		
		$report_type = $this->_getParam('type', false);
		
		$serviceDebtSchedule	= $this->getService('StudentDebtSchedule');
		$data					= $serviceDebtSchedule->fetchAll('1=1', 'group_name, date ASC');
		$filename				= 'Student_debt_timetable';
		$description			= 'Расписание ликвидации задолженностей от '.date('d.m.Y H:i:s');
		
		switch ($report_type) {
			case 'csv':
				echo $this->getCsv($filename, $data, $description);
				break;				
			default:
				die('Неверные данные');
		}
		die;
	}
	
	private function getCsv($filename, $items, $description)
	{
		$this->getResponse()->setHeader('Content-type', 'application/octetstream; charset=UTF-8');
		$this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="'.$filename.'.csv');		
		$this->getResponse()->sendResponse();
		
		$outputData = array();		
		$fields 	= HM_StudentDebt_Schedule_ScheduleModel::getExportFieldList();
		
		//добавялем BOM
        $data = "\xEF\xBB\xBF";
		
		foreach($items as $i) {
			$item = array();
			foreach($fields as $code => $name){
				
				if($code == 'date_day'){
					$item[] = date('d.m.Y', strtotime($i->date));
					continue;
				}
				
				if($code == 'date_time'){
					$item[] = date('H:i', strtotime($i->date));
					continue;
				}
				
				$item[] = $i->{$code};
			}			
			$outputData[] = $item;
		}
		
		$data .= $description;
		$data .= "\r\n";
		$data .= implode(';', $fields);
		$data .= "\r\n";
		foreach($outputData as $row){            
			$data .= implode(';', $row);
			$data .= "\r\n";
		}
		return $data;
	}
	
	
	public function getExampleAction()
	{
		$filename		= 'student_debt_timetable_example';
		
		$this->getResponse()->setHeader('Content-type', 'application/octetstream; charset=UTF-8');
		$this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="'.$filename.'.csv');		
		$this->getResponse()->sendResponse();
		
				
		$fields 		= HM_StudentDebt_Schedule_ScheduleModel::getExportFieldList();		
		$outputData 	= array(
			array(
				'652321',
				'Акимова Юлия Сергеевна',
				'ГФ-ПОЛ-Б-0-Д-2018-А',
				'3 семестр,Осенний (2 курс)',
				'2',
				'Политологии и международных отношений',
				'Очная',
				'Приём зачётов',
				'ПОЛ-Б-0-Д-2018-1',
				'Политические отношения и политический процесс в России',
				'Английский',
				'Авцинова Галина Ивановна',
				'13.03.2020',
				'13.50',
				'ВП2-207',
				'ya.ru',
			),
			array(
				'652321',
				'Акимова Юлия Сергеевна',
				'ГФ-ПОЛ-Б-0-Д-2018-А',
				'3 семестр,Осенний (2 курс)',
				'2',
				'Политологии и международных отношений',
				'Очная',
				'Приём экзаменов',
				'ПОЛ-Б-0-Д-2018-1',
				'Теория политики',
				'нет',
				'Авцинова Галина Ивановна',
				'13.03.2020',
				'13.50',
				'ВП2-207',
				'https://ya.ru',
			),
			array(
				'644714','Александров Павел Владимирович',
				'ГФ-ПОЛ-Б-0-Д-2018-А',
				'3 семестр,Осенний (2 курс)',
				'2',
				'Политологии и международных отношений',
				'Очная',
				'Приём зачётов',
				'ПОЛ-Б-0-Д-2018-1',
				'Политические отношения и политический процесс в России',
				'нет',
				'Авцинова Галина Ивановна',
				'13.03.2020',
				'13.50',
				'ВП2-207',
				'www.ya.ru',
			),			
		);
		
		//добавялем BOM
        $data = "\xEF\xBB\xBF";		
		$data .= implode(';', $fields);
		$data .= "\r\n";
		foreach($outputData as $row){            
			$data .= implode(';', $row);
			$data .= "\r\n";
		}
		echo $data;
		die;
	}
	
	private function prepareData($raw)
	{
		if(empty($raw)){ return $raw; }
		$mid_externals 		= array();
		$list_people 		= array(); 
		
		foreach($raw as $i){
			$i->teacher_list = array();
			
			$teacher_mid_external = explode(',', $i->teacher_mid_external);
			$teacher_mid_external = array_filter($teacher_mid_external);
			$teacher_mid_external = array_map('trim', $teacher_mid_external);
			if(!empty($teacher_mid_external)){
				$teacher_list = array();
				foreach($teacher_mid_external as $mid_external){
					$mid_externals[$mid_external] = $mid_external;					
					$teacher_list[$mid_external]['mid_external'] = $mid_external;
				}
				$i->teacher_list = $teacher_list;
			}
		}
		
		
		$serviceUser = $this->getService('User');
		if(!empty($mid_externals)){
			$select = $serviceUser->getSelect();
			$select->from('People', array('mid_external', 'EMail', 'LastName', 'FirstName', 'Patronymic'));		 
			$select->where($serviceUser->quoteInto('mid_external IN (?)', $mid_externals));
			$res = $select->query()->fetchAll();
			if(!empty($res)){
				foreach($res as $i){
					$list_people[$i['mid_external']] = array(
						'mid_external' 	=> $i['mid_external'],
						'EMail' 		=> $i['EMail'],
						'fio' 			=> $i['LastName'] . ' ' . $i['FirstName'] . ' ' . $i['Patronymic'],
					);
				}
			}
		}
		
		foreach($raw as $i){
			$teacher_list = $i->teacher_list;
			foreach($teacher_list as $mid_external => $teacher){
				$teacher_list[$mid_external] = $list_people[$mid_external];
			}
			$i->teacher_list = $teacher_list;
		}
		
		return $raw;
	}
	
	public function updateDateTime($date) {
        $date = date('d.m.Y H:i', strtotime($date));
        return $date;
    }
	
	public function updateUrl($url) {
        return '<a href="' . $url . '" target="_blank">' . $url . '</a>';
    }
	
	public function deleteOneAction()
	{
		$scheduleId = (int)$this->_getParam('schedule_id', 0);
        if(!$scheduleId){
			$this->_flashMessenger->addMessage(array('message' => _('Параметр не задан'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
			$this->_redirector->gotoSimple('manager', 'timetable', 'student-debt');
			die;
		}
		
		if($this->getService('StudentDebtSchedule')->delete($scheduleId)){
			$this->_flashMessenger->addMessage(_('Запись удалена'));
		} else {
			$this->_flashMessenger->addMessage(array('message' => _('Не удалось удалить запись'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
		}
        $this->_redirector->gotoSimple('manager', 'timetable', 'student-debt');
		die;
	}
	
	public function deleteMassAction()
	{
		$ids = explode(',', $this->_request->getParam('postMassIds_' . $this->_getGridId()));
		
		foreach ($ids as $scheduleId) {
            $scheduleId = (int)$scheduleId;
            if($scheduleId) {
                $this->getService('StudentDebtSchedule')->delete($scheduleId);
            }
        }
        $this->_flashMessenger->addMessage(_('Записи успешно удалены'));
        $this->_redirector->gotoSimple('manager', 'timetable', 'student-debt');
		die;
	}
	
	
	
	
    
}