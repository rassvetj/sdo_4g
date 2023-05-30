<?php
class Marksheet_ListController extends HM_Controller_Action
{
	private $serviceMarksheet = null;
	
	public function indexAction()
    {
		if(!$this->serviceMarksheet){ $this->serviceMarksheet = $this->getService('Marksheet'); }
		
		$this->view->setHeader(_('Список ведомостей'));
		
		$this->view->gridAjaxRequest = $this->isGridAjaxRequest();
		
		$select = $this->serviceMarksheet->getSelect();
		
		$fields = array(
			'marksheet_id' 			=> 'm.marksheet_id',
			'subject_external_id'	=> 'm.subject_external_id',
			'external_id' 			=> 'm.external_id',
			'faculty'				=> 'm.faculty',
			'study_base' 			=> 'm.study_base',
			'semester' 				=> 'm.semester',
			'course' 				=> 'm.course',
			'year' 					=> 'm.year',
			'dean' 					=> 'm.dean',
			'attempt' 				=> 'm.attempt',
			'form_control' 			=> 'm.form_control',
			'date_issue' 			=> 'm.date_issue',
			'isDot' 				=> 'm.isDot',
			'tutor' 				=> 'm.tutor',
			'form_study' 			=> 'm.form_study',
			'group_external_id' 	=> 'm.group_external_id',
			'discipline' 			=> 'm.discipline',
			
			'subject_id'			=> 's.subid',
			'subject_name'			=> 's.name',
			
			'students' 				=> 'm.students',
			#'rating' 				=> 'm.rating', # набранный бал для ИН.
			#'theme_type' 			=> 'm.theme_type', # для ИН
			#'theme' 				=> 'm.theme',  # для ИН
			#'mid_external' 		=> 'm.mid_external', # код студента для индивидуального направления. ИН более нет.			
			
		);
		
		$select = $this->serviceMarksheet->getSelect();
		$select->from(array('m' => 'marksheet_info'), $fields);
		$select->joinLeft(array('s' => 'subjects'), 's.external_id = m.subject_external_id', array());
		
		# TODO
		# Добавить сессии, группы, студентов.
		
		
		$grid = $this->getGrid(
			$select,
			array(
				'marksheet_id'    		=> array('hidden' => true),
				'subject_external_id'	=> array('title' => _('Код сессии 1С')),
				'external_id'			=> array('title' => _('Номер ведомости')),
				'faculty'				=> array('title' => _('Факультет')),
				'study_base'			=> array('title' => _('Основа обучения')),
				'semester'				=> array('title' => _('Семестр')),
				'course'				=> array('title' => _('Курс')),
				'year'					=> array('title' => _('Год')),
				'dean'					=> array('title' => _('Декан')),
				'attempt'				=> array('title' => _('Попытка')),
				'form_control'			=> array('title' => _('Форма контроля')),
				'date_issue'			=> array('title' => _('Дата выдачи')),
				'isDot'					=> array('title' => _('ДО')),
				'tutor'					=> array('title' => _('Преподаватель')),
				'form_study'			=> array('title' => _('Форма обучения')),
				'group_external_id'		=> array('title' => _('Код группы')),
				'discipline'			=> array('title' => _('Дисциплина')),
				
				'subject_id'			=> array('hidden' => true),
				'subject_name'			=> array('title' => _('Сессия')),
				
				'students'				=> array('title' => _('Список студентов')),
				
			),
			array(
                'marksheet_id'			=> null,
                'subject_external_id'	=> null,
                'external_id'			=> null,
                'faculty'				=> null,
                'study_base'			=> null,
                'semester'				=> null,
                'course'				=> null,
                'year'					=> null,
                'dean'					=> null,
                'attempt'				=> null,
                'form_control'			=> null,
                'date_issue' 			=> array('render' => 'DateSmart'),
                'isDot'					=> null,
                'tutor'					=> null,
                'form_study'			=> null,
                'group_external_id'		=> null,
                'discipline'			=> null,
                
                'subject_name'			=> null,
				
				'students'				=> null,
			)
		);
		
		
		$grid->updateColumn('date_issue', array(
            'format'	=> array('date', array('date_format' => HM_Locale_Format::getDateFormat())),
			'callback'	=> array(
				'function'	=> array($this, 'updateDate'),
                'params'	=> array('{{date_issue}}')
            )
        ));
		
		
		$grid->updateColumn('subject_name', array(            
			'callback'	=> array(
				'function'	=> array($this, 'updateSubjectName'),
                'params'	=> array('{{subject_name}}', '{{subject_id}}')
            )
        ));
		
		
		$grid->updateColumn('students', array(            
			'callback'	=> array(
				'function'	=> array($this, 'updateStudents'),
                'params'	=> array('{{students}}', '{{subject_id}}')
            )
        ));
		
				
		$grid->addAction(
			array('module' => 'marksheet', 'controller' => 'list', 'action' => 'assign-unassigned'), array('subject_id', 'marksheet_id'), _('Назначить')
		);
		
		$grid->setActionsCallback(
			array('function' => array($this, 'updateActions'),
				'params' => array('{{students}}')
			)
        );
		
		
		try {
			$this->view->grid = $grid->deploy();			
		} catch (Exception $e) {
			echo 'Ошибка: ',  $e->getMessage(), "\n";
		}
	}
	
	
	# в $studentd уже преобразованный html контент. Это нам и надо
	# Если есть класс user-not-assign, значит есть неназначенные студенты.
	public function updateActions($studentd_html, $actions)
	{	
		$pos = strpos($studentd_html, 'user-not-assign');
		
		if ($pos === false){
			$tmp = explode('<li>', $actions);
			unset($tmp[1]);
			$actions = implode('<li>', $tmp);
		}
		
		
		return $actions;
	}
	
	
	public function updateDate($date){        
		return $date;    
    }
	
	public function updateSubjectName($name, $id)
	{
		if(empty($name) || empty($id)){
			return _('нет');
		}
		$url = $this->view->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'card', 'subject_id' => $id));
		return '<a href="'.$url.'" target="_blank">'.$name.'</a>';
	}
	
	public function updateStudents($mid_external_str, $subject_id)
	{
		if(empty($mid_external_str)){ return _('нет'); }
		
		$mid_externals = explode(',', $mid_external_str);
		$mid_externals = array_map('trim', $mid_externals);
		$mid_externals = array_filter($mid_externals);
		if(empty($mid_externals)){ return _('нет'); }
		
		$result 			= array();
		$count				= count($mid_externals);
		
		$user_collection	= $this->getService('User')->getByIdExternal($mid_externals);
		$mid_externals		= array_flip($mid_externals);
		
		$vedomost_user_ids 	= array();
		foreach($user_collection as $user){
			$vedomost_user_ids[$user->MID]	= $user->MID;
			$url 							= $this->view->url(array('module' => 'user', 'controller' => 'edit', 'action' => 'card', 'user_id' => $user->MID));			
			$result[$user->MID]				= '<a href="'.$url.'" target="_blank">'.$user->FirstName.' '.$user->LastName.' '.$user->Patronymic.'</a>';
			unset($mid_externals[$user->mid_external]);
		}
		asort($result);
		
		# получаем назначения студентов на указанную сессию:
		$assign_students	= $this->getService('Student')->getAssignStudents($subject_id);
		$not_assign			= array_diff($vedomost_user_ids, $assign_students);
		
		# отдельные циклы для верной сортировки и нумерации строк.
		$row_num 		= 0;
		$has_not_assign = false;
		foreach($result as $mid => $fio){
			$row_num++;
			$not_assign_class = '';
			if(in_array($mid, $not_assign)){
				$has_not_assign		= true;
				$not_assign_class	= 'user-not-assign';
			}			
			$result[$mid] = '<p class="'.$not_assign_class.'">'.$row_num.'. '.$fio.'</p>';
		}
		
		$has_not_found	= false;
		foreach($mid_externals as $mid_external => $i){
			$row_num++;
			$result[] 		= '<p class="user-not-found">'.$row_num.'. не найден '.$mid_external.'</p>';
			$has_not_found	= true;
		}
		
		$classes = '';
		if($has_not_found)	{ $classes = 'user-not-found'; }
		if($has_not_assign)	{ $classes = 'user-not-assign'; } # приоритетнее. Поэтому перезаписываем
		
		$result_header	= ($count > 1) ? '<p class="total '.$classes.' ">всего ' .  $count . '</p>' : '';
		
		return '<span class="user-list ">'.$result_header.''.implode(' ', $result).'</span>';
		
	}
	
	
	# назначаем всех неназначенных студентов без учета даты начала обучения
	public function assignUnassignedAction()
	{
		$subject_id		= (int) $this->_getParam('subject_id', 0);
		$marksheet_id	= (int) $this->_getParam('marksheet_id', 0);
		$request 		= $this->getRequest();
		
		if(empty($subject_id) || empty($marksheet_id)){			 
			 $this->_flashMessenger->addMessage(array('message' => _('Не заданы параметры'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
			 $this->_redirector->gotoSimple('index', $this->_controller, $this->_module, array());
		}
		
		
		if(!$this->serviceMarksheet){ $this->serviceMarksheet = $this->getService('Marksheet'); }
		$marksheet = $this->serviceMarksheet->getById($marksheet_id);
		if(empty($marksheet) || !count($marksheet)){
			$this->_flashMessenger->addMessage(array('message' => _('Ведомость не найдена'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
			$this->_redirector->gotoSimple('index', $this->_controller, $this->_module, array());
		}
		
		
		$mid_externals = explode(',', $marksheet->students);
		$mid_externals = array_map('trim', $mid_externals);
		$mid_externals = array_filter($mid_externals);
		
		$vedomost_user_ids	= $this->getService('User')->getByIdExternal($mid_externals)->getList('MID');
		
		# при каждом назначении проверяется, назначен ли студент + проверка на наличие сессии. Чтобы сократить кол-во запросов для каждого студента, выполним это:
		$assign_students	= $this->getService('Student')->getAssignStudents($subject_id);
		$not_assign			= array_diff($vedomost_user_ids, $assign_students);
		
		if(empty($not_assign)){
			$this->_flashMessenger->addMessage(array('message' => _('Нет студентов для назначения'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
			$this->_redirector->gotoSimple('index', $this->_controller, $this->_module, array());
		}
		
		
		$serviceSubject 		= $this->getService('Subject');
		$ignore_begin_learning 	= true;
		foreach($not_assign as $mid){
			$serviceSubject->assignStudent($subject_id, $mid, false, $ignore_begin_learning);
		}
		
		$this->_flashMessenger->addMessage(_('Студенты назначены'));
		$this->_redirector->gotoSimple('index', $this->_controller, $this->_module, array());
		
		die;
	}
	
}



