<?php

class Learningsubjects_ListController extends HM_Controller_Action_Crud
{
    protected $_learningsubjectsService = null;        
    
    public function init()
    {
        $this->_learningsubjectsService = $this->getService('Learningsubjects');
        
        parent::init();
    }
    
    
    public function indexAction()
    {
     
		$this->view->setHeader(_('Учебные предметы'));
		
	    $gridId = 'grid';
    	$default = new Zend_Session_Namespace('default');
        
        $select = $this->_learningsubjectsService->getIndexSelect();
		
		
		$control_list = $this->_learningsubjectsService->getControlList();
		$control_list = array('' => _('-Все-'), '-1' => _('-Без контроля-'), '-2' => _('-С контролем-') ) + $control_list;
		
		
		$isExport 	     = $this->_getParam('_exportTogrid', false);
		$isSetEmptyQuery = ($this->isGridAjaxRequest() || $isExport) ? false : true;
		if($isSetEmptyQuery){			
			$select->where('1=0');			
		}
		
        $subjectService = $this->getService('Subject');
                
        
        $grid = $this->getGrid(
            $select,
            array(
				'learning_subject_id' => array('hidden' => true),                			                
				'id_external'         => array('title' => _('ID предмета')),
                'name'                => array('title' => _('Название')),
                'module_code'         => array('title' => _('Мод')),
                'direction'           => array('title' => _('Направление подготовки')),
                'specialisation'      => array('title' => _('Специализация')),
                'hours'               => array('title' => _('ЗЕТ')),
                'control'             => array('title' => _('Контроль')),                
                'year'                => array('title' => _('Год')),
				'semester'            => array('title' => _('Сем')),
                //'subject_name'        => array('title' => _('Курс')),
				'subject_name' => array(
					'title' => _('Курс'),
					'callback' => array(
						'function'=> array($this, 'updateCourse'),
						'params'=> array('{{subject_name}}')
					)
				),
				
				
                'name_plan'        	  => array('title' => _('Учебный план')),
                'date_update'        	  => array('title' => _('Дата изменения')),
				
				'sessions' => array(
					'title' => _('Есть сессии'),
					'callback' => array(
						'function'=> array($this, 'updateSessions'),
						'params'=> array('{{sessions}}')
					)
				),
				
				'isDO' => array(
					'title' => _('ДО'),
					'callback' => array(
						'function' => array($this, 'updateIsDO'),
						'params' => array('{{isDO}}')
					)
				),
				
				
				'comment'	=> array('title' => _('Ком')),
            ),
            array(                
                'name'           => null,
				'module_code' => array(
					'values' 	=> HM_Learningsubjects_LearningsubjectsModel::getModuleCodeFilterList(),
					'callback'	=> array(
						'function'	=> array($this, 'moduleCodeFilter'),
						'params'	=> array(),
					)
				),
				
				
                'id_external'    => null,
                'direction'      => null,
                'specialisation' => null,
                'hours'          => null,
                #'control'        => null,
                
				'control' => array(
					'values' 	=> $control_list,
					'callback'	=> array(
						'function'	=> array($this, 'controlFilter'),
						'params'	=> array(),
					)
				),
				
				
				
				
                'year'           => null,
				'semester'       => null,
                //'subject_name'   => null,
				
				'subject_name' =>
                    array(
                        'callback' => array(
                            'function'=>array($this, 'courseFilter'),
                            'params'=>array()
                    )
                ),
				
				
                'name_plan'   	 => null,
				'date_update' => array('render' => 'DateSmart'),
				
				'sessions' =>
                    array('values' => array(1 => _('нет'), 2 => _('да')),
                        'callback' => array(
                            'function'=>array($this, 'sessionsFilter'),
                            'params'=>array()
                    )
                ),
				
				'isDO' => array(
					'values' 	=> HM_Subject_SubjectModel::getFacultys(),
					'callback'	=> array(
						'function'	=> array($this, 'doFilter'),
						'params'	=> array(),
					)
				),
				
				'comment'       => null,
            ),
            $gridId
        );
		
		
		$grid->updateColumn('date_update', array(
            'format' => array(
                'DateTime',
                array('date_format' => Zend_Locale_Format::getDateTimeFormat())
            ),
            'callback' => array(
                'function' => array($this, 'updateDate'),
                'params' => array('{{date_update}}')
            )
        ));
		
        
//        $grid->addAction(
//            array('module' => 'techsupport', 'controller' => 'list', 'action' => 'send-message'),
//            array('support_request_id'),
//            _('Ответить')
//        );
//
        $grid->addAction(
            array('action' => 'delete'),
            array('learning_subject_id'),
            $this->view->icon('delete')
        );

        $grid->addMassAction(
            array('action' => 'delete-by'),
            _('Удалить'),
            _('Вы уверены?')
        );
        
        $grid->addMassAction(
            array('action' => 'assign-subject'),
            _('Привязать к учебному курсу')
        );
		
		
        $subjects = $subjectService->fetchAll(array(
            'base <> ? OR base IS NULL' => HM_Subject_SubjectModel::BASETYPE_SESSION
        ), 'name')->getList('subid', 'name');
        
        $grid->addSubMassActionSelect(
            array($this->view->url(array('action' => 'assign-subject'))),
			'subject_id_',
            $subjects,
            array()
        );
        
		
		
		$grid->addMassAction(
            array('action' => 'assign-subject-list'),
			_('Привязать к учебному курсу. Выбор курсов.')
        );
		
		
		$grid->addMassAction(
            array('action' => 'un-assign-subject'),
			_('Отвязать от учебного курса')
        );
		
		
		$grid->addMassAction(array('module' => 'learningsubjects',
			'controller' => 'list',
			'action' => 'change-faculty'),
			_('Изменить факультет'),
			_('Вы уверены?')
		);
		$grid->addSubMassActionSelect($this->view->url(array('module' => 'learningsubjects',
			'controller' => 'list',
			'action' => 'change-faculty')),
			'faculty',
			HM_Subject_SubjectModel::getFacultys()				
		);
		
		
//        $grid->updateColumn('roles',
//            array(
//                'callback' =>
//                array(
//                    'function' => array($this, 'updateRole'),
//                    'params' => array('{{MID}}', $grid)
//                )
//            )
//        );
//        
        $this->view->gridAjaxRequest = $this->isAjaxRequest();
        $this->view->grid = $grid->deploy();
    }
        
    public function assignSubjectAction() {
		$subjectId 		= (int) $this->_getParam('subject_id_', 0);
		$postMassIds 	= $this->_getParam('postMassIds_grid', '');

		if(empty($subjectId)){
			$this->_flashMessenger->addMessage(array(
                'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Не выбран курс')
            ));  
			$this->_redirectToIndex();
		}
		
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            
			if (count($ids)) {                
				#$serviceLSAssign = $this->getService('LearningsubjectsAssign');				
				
				foreach($ids as $id) {					
                    $data = array(
                        'learning_subject_id' => $id,
                        'subject_id'          => $subjectId,
                        'date_update'         => new Zend_Db_Expr('NOW()'),
                    );
                    $result = $this->_learningsubjectsService->update($data);
					
					if(!$result){
						$this->_flashMessenger->addMessage(array(
							'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
							'message' => _('Не удалось связать курс с предметом №'.$id)
						)); 
					}
                }
            }
        }
		
        if($result){
            $this->_flashMessenger->addMessage(_('Курс успешно привязан!'));
        }
        $this->_redirectToIndex();
    }
	
	/**
	 * Отвязка курса от предмета.
	*/
	public function unAssignSubjectAction() {	
		try { 
			$postMassIds = $this->_getParam('postMassIds_grid', '');
			if (strlen($postMassIds)) {
				$ids = explode(',', $postMassIds);
				if (count($ids)) {
					#$serviceLSAssign = $this->getService('LearningsubjectsAssign');	
					foreach($ids as $id) {
						$data = array(
							'learning_subject_id' => $id,
							'subject_id'          => '',
							'date_update'         => new Zend_Db_Expr('NOW()'),
						);					
						$result = $this->_learningsubjectsService->update($data); # для совместимости со старым функционалом. В последствии избавиться от этого и перейти на LearningsubjectsAssign						
					}
				}
			}			
			if($result){
				$this->_flashMessenger->addMessage(_('Курс успешно отвязан!'));
			}        
		} catch (Exception $e) {
			$this->_flashMessenger->addMessage(array(
                'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Не удалось отвязать курс')
            ));                     
		}		
		$this->_redirectToIndex();
    }
	
    
    public function delete($id) {
        $this->_learningsubjectsService->delete($id);
    }
    
    public function updateStatus($status) {
        $statuses = HM_Techsupport_TechsupportModel::getStatuses();
        return $statuses[$status];
    }
    
    public function updateDate_($date) {
        $date = date('d.m.Y', strtotime($date));
        return $date;
    }
	
	public function updateCourse($course) {
        if(empty($course)){
			return _('нет');
		}
        return $course;
    }
	
	public function updateSessions($sessions) {
        if(empty($sessions)){
			return _('нет');
		}
        return _('да');
    }
	
	
	
	public function courseFilter($data){
        $value=$data['value'];
        $select=$data['select'];
        
		if ($value == 'нет'){         
			$select->where('s.name IS NULL');
        } else {
			$select->where($this->quoteInto('LOWER(s.name) LIKE LOWER(?)', '%'.$value.'%'));						
		}		
    }
	
	public function sessionsFilter($data){
		$value=$data['value'];
		$select=$data['select'];
		if($value == 1){ # нет сессий
			$select->having('MAX(session.subid) IS NULL');
		} elseif($value == 2){ # есть сессии
			$select->having('MAX(session.subid) IS NOT NULL');
		} 		
	}
	
	
	/**
	 * - список всех курсов, но отфильтрованных по полям выбранного предмета
	*/
	public function assignSubjectListAction() {
		//$this->_helper->viewRenderer->setNoRender(true);
		$this->view->setHeader(_('Выбор курсов'));
		
		try {
			
			
		if($this->getRequest()->isPost()){
			$gridId = 'grid';
			$subj = trim($this->_getParam('postMassIds_'.$gridId, ''));
			$subj_ar = explode(',',$subj);
			
			if(count($subj_ar) < 1){ //--редирект назад
				$this->_redirector->gotoSimple('index', 'list', 'learningsubjects');
			}
			
			$subj_ar = array_filter($subj_ar);
			
			if(count($subj_ar) < 1){ //--редирект назад
				$this->_redirector->gotoSimple('index', 'list', 'learningsubjects');
			}
			
			
			$select = $this->_learningsubjectsService->getIndexSelect();
			$select->where('ls.learning_subject_id IN ('.implode(',', $subj_ar).')');		
			
			$arResult = $select->query()->fetchAll();
			
			$filters = array();
			//--получаем дфнные полей для фильтра от предметов. Если данные не совпадают, то не фильтруем поле.		
			foreach($arResult as $s){			
				foreach($s as $k => $v){							
					if(!isset($filters[$k])){
						$filters[$k] = $v;
					} elseif($filters[$k] != $v){
						$filters[$k] = false;				
					}
				}
			}		
			$filters = array_filter ($filters); //--удаляем пустые фильтры
			
			
			//--Если слишком длинные фильтры, обрезаем до 100 символов
			if(isset($filters['name'])){
				$filters['name'] = $this->cutStr($filters['name']);
			}
			
			if(isset($filters['direction'])){
				$filters['direction'] = $this->cutStr($filters['direction']);
			}
			
			
			//--фикс для корректной работы grid. Т.к. фильтр grid с POST плохо ладит
			$gridId = 'grid';
			$params = array();
			
			//if(isset($filters['specialisation'])){						
			if(isset($filters['direction'])){			
				//$params['classifiers'.$gridId] = $filters['specialisation'];
				$params['classifiers'.$gridId] = str_replace('.', '~', $filters['direction']);				
			}
			
			if(isset($filters['hours'])){
				$params['zet'.$gridId] = $filters['hours'];
			}
			
			if(isset($filters['name'])){				
				$params['name'.$gridId] = str_replace('.', '%2E', $filters['name']);    
			}
			
			$typeC = array( //--Изменить данные в БД. Текст поменять на int, такой, как и для предметов.
				'Приём экзаменов' => '1',
				'Приём зачётов' => '2',
				'Приём дифференцированных зачетов' => '3',
    		);
			
			if(isset($filters['control']) && isset($typeC[$filters['control']])){
				$params['exam_type'.$gridId] = $typeC[$filters['control']];
			}
			
			$params['subj'] = implode('-', $subj_ar); //--для вывода инф. о предмете над гридом.
			$params['gridmod'] = 'ajax'; //--для сброса кэшированных фильтров.
			
			
			$this->_redirector->gotoSimple('assign-subject-list', 'list', 'learningsubjects', $params);				
		}
		
		$this->_request->setParam("classifiers", str_replace('~', '.', $this->_request->getParam('classifiersgrid')));
		
		$sub_param = trim($this->_getParam('subj', 0));		
		foreach(explode('-', $sub_param) as $i){
			$subjs[] = intval($i);			
		}
		$subjs = array_filter($subjs);
		

		if(count($subjs) > 0){
			$select = $this->_learningsubjectsService->getIndexSelect();
			$select->where('ls.learning_subject_id IN ('.implode(',', $subjs).')');					
			$arResult = $select->query()->fetchAll();
		} else {
			$arResult = false;
		}
		
		if($arResult){
			$this->view->learningSubjects = array();
			$examTypes = HM_Subject_SubjectModel::getExamTypes();			
			
			foreach($arResult as $i){				
			
				//$this->view->learningSubjects[] = $i['name'];
				$this->view->learningSubjects[] = array(
					'name' => $i['name'],
					'zet' => $i['hours'],
					'direction' => $i['direction'],
					'specialisation' => $i['specialisation'],
					'year' => $i['year'],
					'control' => $i['control'], //$examTypes[$i['control']],
					'semester' => $i['semester'], 
				);									
			}				
		}
		
		
		//--делаем селект всех курсов
		$fields = array(
            'subid' => 's.subid',            
            'name' => 's.name',            
            'classifiers' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT c.name)'),
            'year_of_publishing'  => 's.year_of_publishing', 
            'zet'         => 's.zet',
			'hours_total'=> 's.hours_total',  
			'exam_type'=> 's.exam_type',
        );	
	
		$select = $this->getService('Subject')->getSelect();

        $select->from(array('s' => 'subjects'),
            $fields
            )            
            ->joinLeft(
                array('cl' => 'classifiers_links'),
                's.subid = cl.item_id AND cl.type = ' . HM_Classifier_Link_LinkModel::TYPE_SUBJECT, // классификатор уч.курсов
                array()
            )
            ->joinLeft(
                array('c' => 'classifiers'),
                'cl.classifier_id = c.classifier_id',
                array()
            )
			->where('s.base <> ? OR s.base IS NULL', HM_Subject_SubjectModel::BASETYPE_SESSION)
            ->group(array(
                's.subid',                
                's.name',
                's.year_of_publishing',                           
                's.zet',
				's.hours_total',
				's.exam_type',
            ));
		
		
		
		
		$grid = $this->getGrid($select, array(
            'subid' => array('hidden' => true),
			
            'name' => array(
                'title' => _('Название'),                
            ),						
			'classifiers' => array(
                'title' => _('Классификаторы'),
                'callback' => array(
                    'function' => array($this, 'updateClassifiers'),
                    'params' => array('{{classifiers}}')
                )
            ),			
			'zet' => array(
                'title' => _('ЗЕТ')
            ),
			
			'year_of_publishing' => array(
                'title' => _('Год издания')
            ),  

			'hours_total' => array(
                    'title' => _('Часы')
            ),	

			'exam_type' => array(
                'title' => _('Контроль'),
                'callback' => array(
                    'function' => array($this, 'updateExamType'),
                    'params' => array('{{exam_type}}')
                )
            ),	

			
        ),
            array(  
				'name' => null,
				'classifiers' => null,
				'zet' => null,
				'year_of_publishing' => null,
				'hours_total' => null,
				'exam_type' => array('values' => HM_Subject_SubjectModel::getExamTypes()),				
                ),
				 'grid'
        );
		
		
		$grid->addMassAction(
            array('action' => 'assign-subject-new'),
            _('Привязать к учебному курсу')
        );
		
		$this->view->gridAjaxRequest = $this->isAjaxRequest();
		$this->view->grid = $grid->deploy();
		//echo $grid->deploy();
		
		
		} catch (Exception $e) {
			//echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
		}
	}
	
	
	
	 public function assignSubjectNewAction() {
        $this->_helper->viewRenderer->setNoRender(true);
		$len_subjectIDs = $this->_getParam('subj', false); //--предметы. М.б. несколько
        $courseID = $this->_getParam('postMassIds_grid', false); //--курсы. М.б. только один.
		
		
		$len_subjects = array_map('intval',  explode('-', $len_subjectIDs)  );		
		$len_subjects = array_filter($len_subjects);
		
		$cours = array_map('intval',  explode(',', $courseID)  );		
		$cours = array_filter($cours);
		
        $first_subjectId = (int) reset($cours);  # Временно для совместимости со старым функционалом: Если выбрано несколько сессий, то назначается только первый.

		if(count($len_subjects) < 1){
			$this->_flashMessenger->addMessage(_('Не выбрано ни одного предмета!'));            
		} elseif(count($cours) != 1){
			$this->_flashMessenger->addMessage(_('Можно выбрать тольк один курс!'));            					
		} else {
			#$serviceLSAssign = $this->getService('LearningsubjectsAssign');			
			foreach($len_subjects as $id) {
				$data = array(
					'learning_subject_id' => $id,
					'subject_id'          => $first_subjectId,
					'date_update'         => new Zend_Db_Expr('NOW()'),					
				);		
				$result = $this->_learningsubjectsService->update($data);
				
				if(!$result){
					$this->_flashMessenger->addMessage(array(
						'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
						'message' => _('Не удалось связать курс с предметом №'.$id)
					)); 
				}
			
			}
			
			if($result){				
				$this->_flashMessenger->addMessage(_('Курс успешно привязан!'));
			}			
			$this->_redirectToIndex();			
		} 
		
		$url = $this->getRequest()->getHeader('Referer');			
		$this->_redirect($url);
    }	
	
	public function updateExamType($examType)
    {
        $examTypes = HM_Subject_SubjectModel::getExamTypes();
        return $examTypes[$examType];
    }
	
	public function cutStr($str, $len = 100){
		if(!$str){
			return false;
		}
		
		$str = substr($str, 0, $len);
		$str = rtrim($str, "!,.-");
		
		if(mb_strlen($str) >= $len){
			$str = substr($str, 0, strrpos($str, ' '));
		}
		
		return $str;
	}
	
	
	public function updateDate($date)
    {
        if (!strtotime($date)) return '';
        return $date;
    }
	
	
	
	public function setCommentAction(){
		if (!$this->getRequest()->isXmlHttpRequest()) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }
		
		$this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();
		
		$ids = $this->_getParam('ids', false);
        $text = $this->_getParam('text', '');
		
		if(!$ids || empty($ids)){
			echo json_encode(array('error' => _('Необходимо выбрать предмет')));
			die;
		}
		$ids = (array)$ids;
		$ids = array_map('intval', $ids);
		
		$text= strip_tags($text);
		$isUpdate = $this->getService('Learningsubjects')->updateWhere(
			array('comment' => $text),
			$this->getService('Learningsubjects')->quoteInto('learning_subject_id IN (?)', $ids)
		);
		if($isUpdate){
			$this->_flashMessenger->addMessage(_('Комментарий успешно изменен'));
			echo json_encode(array('success' => 1));
			die;
		}	
		echo json_encode(array('error' => _('Не удалось обновить записи')));
		die;		
	}
	
	public function updateIsDO($type){
		$facultList = HM_Subject_SubjectModel::getFacultys();
		return $facultList[$type];		
	}
	
	public function doFilter($data){
		$value  = (int)$data['value'];
		$select = $data['select'];
		if(empty($value)){
			$select->where("(ls.isDO = 0 OR ls.isDO IS NULL OR ls.isDO = '')");
		} else {
			$select->where("ls.isDO = ?", $value);
		} 
	}
	
	//--меняет принадлежность сессии - ФДО, ФДО_Б или прочие.
	public function changeFacultyAction(){
		
		$faculty = (int)$this->_getParam('faculty', HM_Subject_SubjectModel::FACULTY_OTHER);
		$learningsubjects = explode(',',$this->_getParam('postMassIds_grid',array()));
		
		if(!count($learningsubjects)){
			$this->_flashMessenger->addMessage(_('Не удалось изменить записи. Не выбраны предметы'));
		} else {		
			try {
				$isUpdate = $this->getService('Learningsubjects')->updateWhere(
					array('isDO' => $faculty),
					array($this->quoteInto('learning_subject_id IN (?)', $learningsubjects))
				);
				if($isUpdate){
					$this->_flashMessenger->addMessage(_('Операция выполнена успешно'));
				} else {
					$this->_flashMessenger->addMessage(_('Не удалось изменить записи'));
				}		
			} catch (Exception $e) {				
				$this->_flashMessenger->addMessage(_('Не удалось изменить записи'));
			}
		}
        $this->_redirectToIndex();
	}
	
	
	
	public function controlFilter($data){
		$value  = trim($data['value']);
		$select = $data['select'];
		
		if(empty($value)){
			return true;
		}
		
		if($value == '-1'){
			$select->where("(ls.control='' OR ls.control IS NULL)");
			return true;
		}
		
		if($value == '-2'){
			$select->where("(ls.control != '' AND ls.control IS NOT NULL)");
			return true;
		}
		
		$select->where($this->quoteInto('ls.control=?', $value));
		return true;
	}
	
	public function moduleCodeFilter($data){
		$value  = trim($data['value']);
		$select = $data['select'];
		
		if(empty($value)){
			return true;
		}
		
		if($value == HM_Learningsubjects_LearningsubjectsModel::MODULE_CODE_FILTER_NO){
			$select->where("(ls.module_code='' OR ls.module_code IS NULL)");
			return true;
		}
		
		if($value == HM_Learningsubjects_LearningsubjectsModel::MODULE_CODE_FILTER_YES){
			$select->where("(ls.module_code != '' AND ls.module_code IS NOT NULL)");
			return true;
		}
		
		$select->where($this->quoteInto('ls.module_code=?', $value));
		return true;
	}
	
	
	
	
    
	
}