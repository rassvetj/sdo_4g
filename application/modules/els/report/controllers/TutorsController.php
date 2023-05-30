<?php
class Report_TutorsController extends HM_Controller_Action_Crud
{
	protected $_currentLang	= 'rus'; 
    public function init()
    {
        parent::init();
       
        $this->getService('Unmanaged')->setHeader(_('Отчет по тьюторам'));
		
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$this->_currentLang = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);
    }
	
	
    public function indexAction()
    {
        $this->view->headLink()->appendStylesheet($config->url->base.'/css/rgsu_style.css');
		
		$cols = array(
			'MID' => 'p.MID',
			'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
		);

		$select = $this->getService('User')->getSelect();
		$select->from(array('p' => 'People'), $cols);				
		$select->join(array('t' => 'Tutors'), 't.MID = p.MID', array());
		$select->where('p.role_1c=2');
		
		$smtp = $select->query();
		$tutors = $smtp->fetchAll();
		
		$list = array();
		$list_tmp = array();
		
		$list[-1] = _('Выберите из списка');
		$list['all'] = _('Все');
		
		foreach ($tutors as $v) {			
			$list_tmp[$v['MID']] = $v['fio'];
		}
		
		asort($list_tmp);
		
		$list = $list + $list_tmp;
		
		$this->view->tutors = $list;
		
		$this->view->content = _('Выберите тьютора из списка');
		
    }
	
	public function getAction()
    {	
		$this->getHelper('viewRenderer')->setNoRender();
		
		$request = $this->getRequest();

        if ($request->isPost() || $request->isGet()) {
			
			$content = array();
			$tutor_id = (intval($request->getParam('tutor_id')) >= 0 ) ? $request->getParam('tutor_id') : false;	
			
			if(!$tutor_id){
				//echo 'Выберите тьютора из списка';				
				$content['message'] = 'Выберите тьютора из списка';
				$content['data'] = '';				
			} else {	
				

				$userService = $this->getService('User');
				
				$cols = array();
				
				if($tutor_id == 'all' ){
					$content['message'] = '';
					$cols['fio'] = new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)");					
				} else {
					$tutorInfo = $this->getService('User')->getById($tutor_id); //--получаем данные тьютора длявывода над таблицей. Пока фио. Но хотелось бы и должность и т.п.					
					$tutorFIO = $tutorInfo->LastName.' '.$tutorInfo->FirstName.' '.$tutorInfo->Patronymic;
					$content['message'] = $tutorFIO;					
				}
				
				$cols = $cols + array(	
					//'MID' => 'p.MID',
					
					'chair' => 'chair.name', //--Кафедра
					
					'subj_name' => 'subj.name', //--предмет					
					//'subj_name' => new Zend_Db_Expr("CAST(subj.name AS NVARCHAR(MAX))"),
					
					'subj_id' => 'subj.subid',
					//'subj_id2' => 'subj.subid',
					//'tut_id' => 'p.MID',
					//'stud_id' => 'stud.MID',
					
					'Student_Name' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p2.LastName, ' ') , p2.FirstName), ' '), p2.Patronymic)"),					
					//'groups' => new Zend_Db_Expr("GROUP_CONCAT(DISTINCT(sg.group_id))"), //--почему-то группировка не работает. WHY?
					//'group_ID' => 'sg.group_id',
					
					
					'group_Name' => 'sg.name', //--группа студента
					
					
					//'LessonID' => 'l.SHEID',
					'LessonName' => 'l.title', //--урок студента
					'LessonID' => 'l.SHEID', //--урок студента  
					
					
					//'tm_user_id' => 'tm_interview.tm_user_id',
					//'tm_to_whom' => 'tm_interview.tm_to_whom',
					//'tm_lesson_id' => 'tm_interview.tm_lesson_id',
					//'tm_interview_hash' => 'tm_interview.interview_hash',
					
					'tm_count' => 'tm_interview.tm_count',
					'tm_date_last' => 'tm_interview.tm_date_last',
					'tm_type' => 'tm_interview.tm_type',
					
					
					
					//'tmu_user_id' => 'tmu_interview.tmu_user_id',
					//'tmu_to_whom' => 'tmu_interview.tmu_to_whom',
					//'tmu_lesson_id' => 'tmu_interview.tmu_lesson_id',
					'tmu_count' => 'tmu_interview.tmu_count',
					'tmu_date_last' => 'tmu_interview.tmu_date_last',
					'tmu_type' => 'tmu_interview.tmu_type',
					
						
					'mark' => 'sch.V_STATUS',					
					'date_first_message' =>  'i.date', //--дата первого сообщения. Как правило, это прикрепленное задание
					'date_last_message' =>  'i2.date', 
					'type_last_message' =>  'i2.type', 
					'type_last_message2' =>  'i2.type', 
					//'last_message_id' =>  'i2.interview_id', //--id последнего сообщения
					
					'is_new' => new Zend_Db_Expr('CASE WHEN p2.MID = i2.user_id THEN -1 ELSE -2 END'),
					//'LessonID' => 'i.interview_id',
					//'LessonName' => 'i.title',
					
				);
				
				$select = $this->getService('User')->getSelect();
				$select->from(array('p' => 'People'), $cols);				
				
				$select->join(array('t' => 'Tutors'), 't.MID = p.MID', array());	//--выбрали всех тьюторов по наличию в таблице Tutors
				
				
				//--делаем выборку ОРГ структуры и группируем по id членов. Во избежания дублей строк.
				$subSelectSOO = $this->getService('User')->getSelect();
				$subSelectSOO->from('structure_of_organ', array(	
						'mid' => 'structure_of_organ.mid',
						'owner_soid' => 'structure_of_organ.owner_soid',
					))				
					->group(array('structure_of_organ.mid', 'structure_of_organ.owner_soid'));
				
				$select->joinLeft(array('mch' => $subSelectSOO), 'mch.mid = t.MID', array()); //--выбрать все членства в оргструктуре
				
				
				$select->joinLeft(array('chair' => 'structure_of_organ'), 'CONVERT(INT, CASE WHEN IsNumeric(CONVERT(VARCHAR(12), chair.soid_external)) = 1 then CONVERT(VARCHAR(12), chair.soid_external) else 0 End) = mch.owner_soid', array()); //--выбираем отделы, к которым принадлежат пользователи. Конвертируем, т.к. поле soid_external содержит как целое, так и текстовое значение. А MsSQL пытается текст привести к целому. В итоге падает запрос.
				
				
				
				$select->join(array('subj' => 'subjects'), 'subj.subid = t.CID', array()); //--выбрать все предметы
				
				
				
				
				$select->join(array('stud' => 'Students'), 'stud.CID = subj.subid', array()); //--id студента
				$select->join(array('p2' => 'People'), 'stud.MID = p2.MID', array()); //--ФИО студента
				
				//$select->join(array('g' => 'study_groups_users'), 'g.user_id = p2.MID', array()); //--группа учебная
				
				$subSelectSGC = $this->getService('User')->getSelect();
				$subSelectSGC->from('study_groups_custom', array(
						'user_id' => 'study_groups_custom.user_id',
						'group_id' => 'MIN(study_groups_custom.group_id)' //--выбираем группу для студента только одну, с минимальным ID
						
					));		
				$subSelectSGC->group(array('study_groups_custom.user_id'));
					
					//$smtp = $subSelectSGC->query();
					//foreach($smtp->fetchAll() as $j){
						//echo $j['user_id'].' - '.$j['group_id'].'<br>';
					//}
					
					
				
				//$select->join(array('sgc' => 'study_groups_custom'), 'sgc.user_id = p2.MID', array()); 
				$select->join(array('sgc' => $subSelectSGC), 'sgc.user_id = p2.MID', array()); 
				
				$select->join(array('sg' => 'study_groups'), 'sg.group_id = sgc.group_id', array());

				//$select->group(array('p2.MID, sg.group_id'));
				
					
				$select->join(array('l' => 'schedule'), 'l.CID = subj.subid', array()); //--Уроки
				
				$select->where('l.typeID NOT IN (?)', array_keys(HM_Event_EventModel::getExcludedTypes())); //--Что это за типы занятий?
                $select->where('l.isfree = ?', HM_Lesson_LessonModel::MODE_PLAN); //--Выводим только бесплатные или свободные??
				
				
								
				
				
				$select->join(array('sch' => 'scheduleID'), 'sch.MID = p2.MID AND sch.SHEID=l.SHEID', array());	//--выбираем оценки студентов по их id и номеру занятия.						
				//$select->joinLeft(array('sg' => 'study_groups'), 'sg.group_id = g.group_id', array());
				
				
				
				
				//присоединяем данные первого сообщения				
				$select->join(array('i' => 'interview'), 'i.to_whom = p2.MID AND i.user_id = 0 AND i.lesson_id = l.SHEID ', array());
				

				
				
				//выбираем самое последнее сообщение, чтобы знать, кто ответил последним
				
				$subSelect = $this->getService('User')->getSelect();
				$subSelect->from('interview', array(
						'interview_hash' => 'interview.interview_hash',
						'last_interview_id' => 'MAX(interview.interview_id)'
					))
					//->where('interview.lesson_id = ?', $lessonId)
					->group(array('interview.interview_hash'));
				
				$select->joinInner(array('i_last' => $subSelect), 'i_last.interview_hash = i.interview_hash', array());			
				$select->joinLeft(array('i2' => 'interview'), 'i2.interview_hash = i_last.interview_hash AND i2.interview_id = i_last.last_interview_id', array());
				
				
				//--Подсчет общего кол-ва сообщений от тьютора.
				$subSelectTM = $this->getService('User')->getSelect();
				$subSelectTM->from('interview', array(
						'tm_user_id' => 'interview.user_id',
						'tm_to_whom' => 'interview.to_whom',
						'tm_lesson_id' => 'interview.lesson_id',
						'tm_count' => 'COUNT(*)',
						'tm_date_last' => 'MAX(interview.date)',					
						'tm_type' => 'interview.type',						
						'interview_hash' => 'interview.interview_hash'						
					))
					->where('interview.to_whom != ?', 0)
					->group(array('interview.user_id', 'interview.to_whom', 'interview.lesson_id', 'interview.type', 'interview.interview_hash'));			
				$select->joinLeft(array('tm_interview' => $subSelectTM), 'tm_interview.tm_user_id = t.MID AND tm_interview.tm_lesson_id = l.SHEID
				 AND tm_interview.tm_to_whom = p2.MID AND tm_interview.tm_lesson_id = l.SHEID
				', array());	
				
				
				
				//--Подсчет общего кол-ва сообщений от студента.
				$subSelectTMu = $this->getService('User')->getSelect();
				$subSelectTMu->from('interview', array(
						'tmu_user_id' => 'interview.user_id',
						'tmu_to_whom' => 'interview.to_whom',
						'tmu_lesson_id' => 'interview.lesson_id',
						'tmu_count' => 'COUNT(*)',
						'tmu_date_last' => 'MAX(interview.date)',
						'tmu_type' => 'interview.type'
					))
					//->where('interview.lesson_id = ?', $lessonId)
					//->where('interview.to_whom != 0')
					->group(array('interview.user_id', 'interview.to_whom', 'interview.lesson_id', 'interview.type', 'interview.interview_hash'));				
				$select->joinLeft(array('tmu_interview' => $subSelectTMu), 'tmu_interview.tmu_user_id = p2.MID AND tmu_interview.tmu_lesson_id = l.SHEID', array());
				
				
				
				if(intval($tutor_id) > 0){
					$select->where('p.MID = ?', $tutor_id);
				} 
				
				$select->where('p.role_1c=2');
				
				
				
				/*
				//--Группируем по урокам
				$subSelectTG = $this->getService('User')->getSelect();
				$subSelectTG->from('Tutors', array())									
					->group(array('Tutors.MID'));			
				$subSelectTG->join(array('all_select' => $select), 'Tutors.MID = all_select.tut_id', array());
				*/
				
				
				
				$gridId = 'grid';
				
				try {
									
					$grid = $this->getGrid(
						$select,
						//$subSelectTG,
						array(																			  
							'fio' => array('title' => _('ФИО т.')),    
							'chair' => array('title' => _('Кафедра')),    
							'subj_name' => array('title' => _('Предмет')),    
							'Student_Name' => array('title' => _('ФИО ст.')),    
							'group_Name' => array('title' => _('Группа')),    
							'LessonName' => array('title' => _('Урок')),    
							'tm_count' => array('title' => _('Сообщ. т.')),    
							'tm_type' => array('title' => _('Тип сообщ. т.')),    							
							'tm_date_last' => array('title' => _('Последнее сообщ. т.')),    
							
							'tmu_count' => array('title' => _('Сообщ. ст.')),  
							'tmu_type' => array('title' => _('Тип сообщ. ст.')),  							
							'tmu_date_last' => array('title' => _('Последнее сообщ. ст.')),    
							
							'mark' => array('title' => _('Оценка')),    
							'date_first_message' => array('title' => _('Дата публикования задания')),    
							'date_last_message' => array('title' => _('Дата последнего сообщения')),    
							//'type_last_message' => array('title' => _('Тип последнего сообщения')),    
							'type_last_message' => array('hidden'=>true),
							//'is_new' => array('title' => _('Новый ответ студента')),    													
							'is_new' => array('title' => _('Статус обработки')),    													
							'LessonID' => array('hidden'=>true),
							'subj_id' => array('hidden'=>true),
							'type_last_message2' => array('hidden'=>true),							
						),
						array(
							'fio' => null,
							'chair' => null,
							'subj_name' => null,
							'Student_Name' => null,
							'mark' => null,
							'LessonName' => null,
							'group_Name' => null,														
							//'is_new' => array('values' => array('1'=>'Да')),	
							'is_new' => array('values' => array(
																'-1'=>_('Не обработано'),
																'-2'=>_('Обработано или выдано задание'),																
																)),	
							'date_first_message' => array('render' => 'DateSmart'),	
							'date_last_message' => array('render' => 'DateSmart'),															
							'tm_date_last' => array('render' => 'DateSmart'),															
							'tmu_date_last' => array('render' => 'DateSmart'),	
							'type_last_message' => array('values' => array(
																		'0'=>_('Выдано задание'),
																		'1'=>_('Вопрос преподавателю'),
																		'2'=>_('Решение на проверку'),
																		'3'=>_('Ответ преподавателя'),
																		'4'=>_('Требования на доработку'),
																		'5'=>_('Выставлена оценка'),																		
																		)),	
																		
							'tm_type' => array('values' => array(																		
																		'3'=>_('Ответ преподавателя'),
																		'4'=>_('Требования на доработку'),
																		'5'=>_('Выставлена оценка'),																		
																		)),
							'tmu_type' => array('values' => array(																
																		'1'=>_('Вопрос преподавателю'),
																		'2'=>_('Решение на проверку'),																	
																		)),
						),
						$gridId
					);			
					
					$grid->updateColumn('date_first_message', array(
						'format' => array(
							'DateTime',
							array('date_format' => Zend_Locale_Format::getDateTimeFormat())     
						),
						'callback' => array(
							'function' => array($this, 'updateDate'),
							'params' => array('{{date_first_message}}')
						)
					));	
					
					
					$grid->updateColumn('date_last_message', array(
						'format' => array(
							'DateTime',
							array('date_format' => Zend_Locale_Format::getDateTimeFormat())   
						),
						'callback' => array(
							'function' => array($this, 'updateDate'),
							'params' => array('{{date_last_message}}')
						)
					));	
					
					
					
					$grid->updateColumn('tm_date_last', array(
						'format' => array(
							'DateTime',
							array('date_format' => Zend_Locale_Format::getDateTimeFormat())   
						),
						'callback' => array(
							'function' => array($this, 'updateDate'),
							'params' => array('{{tm_date_last}}')
						)
					));	
					
					$grid->updateColumn('tmu_date_last', array(
						'format' => array(
							'DateTime',
							array('date_format' => Zend_Locale_Format::getDateTimeFormat())   
						),
						'callback' => array(
							'function' => array($this, 'updateDate'),
							'params' => array('{{tmu_date_last}}')
						)
					));	
					
					
					$grid->updateColumn('is_new', array(						
						'callback' => array(
							'function' => array($this, 'getStatusCheck'),
							'params' => array('{{is_new}}', '{{type_last_message2}}')
						)
					));	
					
					
					$grid->updateColumn('type_last_message', array(						
						'callback' => array(
							'function' => array($this, 'getTaskTypeString'),
							'params' => array('{{type_last_message}}')
						)
					));	
					
					
					
					$grid->updateColumn('tmu_type', array(						
						'callback' => array(
							'function' => array($this, 'getTaskTypeString_v2'),
							'params' => array('{{tmu_type}}')
						)
					));	
					
					
					$grid->updateColumn('tm_type', array(						
						'callback' => array(
							'function' => array($this, 'getTaskTypeString_v2'),
							'params' => array('{{tm_type}}')
						)
					));	
					
					
					
					
					
					
					$grid->updateColumn('mark', array(						
						'callback' => array(
							'function' => array($this, 'updateMark'),
							'params' => array('{{mark}}')
						)
					));	
					
					
					
					
					$grid->updateColumn('LessonName', array(						
						'callback' => array(
							'function' => array($this, 'updateLesson'),
							'params' => array('{{LessonName}}', '{{LessonID}}', '{{subj_id}}')
						)
					));	
					
					
					
					//$grid->setOptions(array(
						//'title' => 'sds',
					//));
					
					//$grid->setExport(array('xml','odt','pdf')); //--варианты экспорта
					//$grid->setNumberRecordsPerPage(25); //--строк на страницу
					
					//$grid->setParam('name','value');
					
					//$grid->setExport(array(
						//'print' => array(
							//'caption' => 'print',
							//'name' => 'print2',
							//'title' => 'print3',
						//	),				
					//));
					
					$content_grid = $grid->deploy();
					
					
					$content['data'] = $content_grid;
				
				} catch (Exception $e) {
					echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
					//var_dump($e);
				}				
				
				//echo $content_grid;			
			}
			
			$this->view->info = $content['message']; 			
			$this->view->grid = $content['data'];
		
			echo $this->view->render('tutors/ajax.tpl');			
		}		
	}
	
	public function getStatusCheck($data, $type = 0){
		
		
		if($type == 0) {
			return 'Выдано задание';			
		} else {		
			if($data == -1) {
				return '<span style="color:red;">Не обработано</span>'; 
			}
		
			return 'Обработано';		
		}
		
		
	}
	
	public function updateMark($mark){
		
		if($mark == -1) { return ''; }
		
		return $mark;		
	}
	



    public function updateDate($date)
    {
        if(!$date || empty($date)){
			return $date;
		}
		
		$dateObject = new Zend_Date($date);
        return $dateObject->toString();
    }


    /**
     * Возвращает наименование типа по его числовому представлению
     * @param int $type
     * @return string
     */
    public function getTaskTypeString( $type )
    {
        if($type >= 0){
			$ivModel = HM_Interview_InterviewModel::factory(array('type' => intval($type)));
			return  $ivModel->getType();	
		}
		return $type;
    }
	
	public function getTaskTypeString_v2( $type )
    {
        if(!$type || empty($type)){
			return '';			
		}
		
		$ivModel = HM_Interview_InterviewModel::factory(array('type' => intval($type)));
		return  $ivModel->getType();			
    }

	
	
	public function updateLesson($lesson, $lesson_id, $subject_id){
		$url = '/lesson/result/extended/lesson_id/'.$lesson_id.'/subject_id/'.$subject_id;
		return '<a target="_blank" href="'.$url.'">'.$lesson.'</a>';
		
	}




}