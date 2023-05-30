<?php
class Report_BallTutorsController extends HM_Controller_Action_Crud
{
    protected $_faculties  = array(); # факультеты mid => fio
    protected $_departments  = array(); # кафедры mid => fio
	
	const CACHE_NAME = 'Report_BallTutorsController';
	protected $_reportBallTutors 	= array(); # отчет по выставленным оценкам тьютора.
	protected $_reportTutorID 		= NULL; # выбранный тьютор
	protected $_countStudents 		= NULL; 
	protected $_serviceStudent 		= NULL; 
	protected $_countAssignStudents	= array(); # кол-во назначенных на сессию студентов, доступных тьютору без учета прошедших обучение.
	protected $_currentLang	= 'rus'; 
	
	public function saveToCache()
    {
        return Zend_Registry::get('cache')->save(
            array(
                'reportBallTutors' 	=> $this->_reportBallTutors,                                  
                'reportTutorID' 	=> $this->_reportTutorID,                                  
                'reportType' 		=> $this->_reportType,                                  
                'countStudents' 	=> $this->_countStudents,                                  
            ),
            self::CACHE_NAME
        );
    }

    public function clearCache()
    {
        return Zend_Registry::get('cache')->remove(self::CACHE_NAME);
    }

    public function restoreFromCache()
    {
        if ($actions = Zend_Registry::get('cache')->load(self::CACHE_NAME)) {
            $this->_reportBallTutors  	= $actions['reportBallTutors'];            
            $this->_reportTutorID  		= $actions['reportTutorID'];            
            $this->_reportType  		= $actions['reportType'];            
            $this->_countStudents  		= $actions['countStudents'];            
            $this->_restoredFromCache = true;
            return true;
        }
        return false;
    }
	
	
	public function init()
    {
        parent::init();
       
        $this->getService('Unmanaged')->setHeader(_('Отчет по оценкам тьюторов'));
		
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$this->_currentLang = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);
    }
	
	
    public function indexAction()
    {        
		$this->clearCache();
		
		$this->view->headLink()->appendStylesheet($config->url->base.'/css/rgsu_style.css');
		$this->view->headLink()->appendStylesheet('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/css/select2.min.css');		
		$this->view->headScript()->appendFile('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/js/select2.min.js');
		
		$tutors = $this->getService('Report')->getTutorList(); 

		if($this->_currentLang == 'eng') {
			foreach($tutors as $key => $value) 
				$tutors[$key] = $this->translit($value);
			
			$tutors[''] = 'All';		
		}		
		
		// $this->view->tutors = $this->getService('Report')->getTutorList();
		$this->view->tutors = $tutors;
    }
	
	public function getAction()
    {	
		$this->getHelper('viewRenderer')->setNoRender();
		
		$request = $this->getRequest();
        if ($request->isPost() || $request->isGet()) {
			try {
			##########
				$tutor_id 		= intval($request->getParam('tutor_id', false));
				$report_type 	= intval($request->getParam('report_type', HM_Report_ReportModel::REPORT_TYPE_BALL));
				
				$serviceUser 		 		= $this->getService('User');		
				$serviceSubject 	 		= $this->getService('Subject');		
				$serviceGroup   	 		= $this->getService('StudyGroup');	
				$serviceSGProgramm   		= $this->getService('StudyGroupProgramm');	
				$this->_serviceOrgstructure = $this->getService('Orgstructure');
				
				$this->restoreFromCache();				
				if($this->_reportTutorID === $tutor_id && $this->_reportType === $report_type){						
						$totalData 		= $this->_reportBallTutors;
						$countStudents 	= $this->_countStudents;
				} else {										
						####
						# Сессии, которые будут исключены из основного запроса
						$subSelect = $serviceUser->getSelect();
						$subSelect->from(array('subj' => 'subjects'), array('subj' => 'subj.subid'));
						$subSelect->join(array('l' => 'schedule'), 'l.CID = subj.subid', array());
						
						if($report_type == HM_Report_ReportModel::REPORT_TYPE_BALL){
							$subSelect->where('l.typeID IN (?)', HM_Event_EventModel::TYPE_TEST); # тип "тест"
						} elseif($report_type == HM_Report_ReportModel::REPORT_TYPE_PROVE){
							$subSelect->where('l.typeID NOT IN (?)', array_keys(HM_Event_EventModel::getExcludedTypes()));            
							$subSelect->where('l.typeID NOT IN (?)', array(HM_Event_EventModel::TYPE_TASK, HM_Event_EventModel::TYPE_JOURNAL)); # исключаем уроки с типом "задание", "журнал"
						}
						
						$subSelect->where('l.isfree = ?', HM_Lesson_LessonModel::MODE_PLAN);
						$subSelect->group('subj.subid');
						####
						
						$select = $serviceUser->getSelect();
						
						if($tutor_id > 0){ $select->where($serviceUser->quoteInto('t.MID = ?', $tutor_id)); }
									
						$select->from(array('t' => 'Tutors'), array(
							'tutor_id'			=> 'p.MID',
							'fio'				=> new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
							'subject_id'		=> 'subj.subid',
							'subject_name'		=> 'subj.name',
							'lesson_id'			=> 'l.SHEID',
							'lesson_name'		=> 'l.title',
							'lesson_type'		=> 'l.typeID',
							'zet'				=> 'subj.zet',
							'semester'			=> 'subj.semester',
						));
						$select->join(array('p' => 'People'), 't.MID = p.MID', array());
						$select->join(array('subj' => 'subjects'), 'subj.subid = t.CID', array());
						$select->join(array('l' => 'schedule'), 'l.CID = subj.subid', array());
						
						
						$select->where('l.typeID NOT IN (?)', array_keys(HM_Event_EventModel::getExcludedTypes()));  
						$select->where('l.isfree = ?', HM_Lesson_LessonModel::MODE_PLAN);
						$select->where($serviceUser->quoteInto('subj.subid NOT IN (?)', $subSelect));
						$select->where('subj.base = ?', 2); 
						
						
						if($report_type == HM_Report_ReportModel::REPORT_TYPE_BALL){
							$select->where('subj.begin >= ?', '2016-09-01');
							$select->where($serviceUser->quoteInto(array("(subj.isDO = ? ", " OR subj.isDO IS NULL)"), array(HM_Subject_SubjectModel::FACULTY_OTHER)));
							$select->where($serviceUser->quoteInto(
								array("(l.title LIKE ? ", " OR l.title LIKE ? ", " OR l.title LIKE ? ", " OR l.title LIKE ?)"),
								array('%Академическая активность%', '%Задание к разделу%', '%Рубежный контроль%', '%Итоговый контроль%')
							));
						} elseif($report_type == HM_Report_ReportModel::REPORT_TYPE_PROVE){							
							$select->where('subj.isDO = ?', HM_Subject_SubjectModel::FACULTY_DO);
						}
						$select->where('subj.time_ended_debt IS NULL');
						
						$res = $select->query()->fetchAll();
						unset($select);
						unset($subSelect);
						
						if(empty($res)){
							echo 'Нет данных';
							die;
						}
						
						$tutorsIDs = array();
						$lessonIDs = array();
						$subjectIDs = array();
						$totalData = array(); # конечный результат отчета
						foreach($res as $i){
							$tutorsIDs[$i['tutor_id']] 		= $i['tutor_id'];
							$lessonIDs[$i['lesson_id']] 	= $i['lesson_id'];
							$subjectIDs[$i['subject_id']] 	= $i['subject_id'];
							
							$totalData[$i['tutor_id'].'~'.$i['subject_id']] = array(
								'tutor_name' 		=> $i['fio'],
								'subject_id'		=> $i['subject_id'],
								'subject_name'		=> $i['subject_name'],
								'zet'				=> $i['zet'],						
								'availableStudents' => $serviceSubject->getAvailableStudents($i['tutor_id'], $i['subject_id']),
								'faculty'			=> $this->getFaculty($i['tutor_id']),
								'department'		=> $this->getDepartment($i['tutor_id']),
								'lesson_id'			=> $i['lesson_id'],
								'semester'			=> ( $i['semester'] % 2 ) ? ('не четный') : ('четный'),
							);
						}
						
						if(empty($tutorsIDs) || empty($lessonIDs) || empty($subjectIDs)){
							echo 'Нет данных';
							die;
						}
						
						#### Находим слушателей + завершивших обучение на сессии				
						$subUsers = $serviceUser->getSelect();
						$subUsers->from(array('Students'),	array('MID','CID'));
						
						$subGrad = $serviceUser->getSelect();
						$subGrad->from(array('graduated'),	array('MID','CID'));
						
						$subUSelect = $serviceUser->getSelect();
						$subUSelect->union(array($subUsers, $subGrad));
						
						$studentSelect = $serviceUser->getSelect();			
						$studentSelect->from(array('s' => $subUSelect), array('subject_id' => 's.CID', 'student_id' => 's.MID'));				
						$studentSelect->join(array('p' => 'People'), 'p.MID = s.MID', array());
						$studentSelect->where('s.CID > ?', 0);
						$studentSelect->where('s.MID > ?', 0);
						$studentSelect->where($serviceUser->quoteInto('s.CID IN (?)', $subjectIDs));
						$studentSelect->group(array('s.CID', 's.MID'));
						$resStudent = $studentSelect->query()->fetchAll();
						
						$assignStudents = array();
						if(!empty($resStudent)){
							foreach($resStudent as $i){
								$assignStudents[$i['subject_id']][] = $i['student_id'];
							}
						}
						unset($resStudent);								
						unset($studentSelect);								
						unset($subUSelect);								
						unset($subUsers);								
						unset($subGrad);								
						####
						
						
						if($report_type == HM_Report_ReportModel::REPORT_TYPE_PROVE){
							# отбираем данные по последнему сообщению: решение на проверку.							
							$subProveSelect = $serviceUser->getSelect();
							$subProveSelect->from('interview', array('interview_id'=> new Zend_Db_Expr('MAX(interview_id)')));
							$subProveSelect->group(array('lesson_id', 'interview_hash'));
							
							$proveSelect = $serviceUser->getSelect();
							$proveSelect->from(array('m' => 'interview'), array(
								'm.lesson_id',
								'm.user_id',
							));
							$proveSelect->join(array('m2' => $subProveSelect), 'm2.interview_id = m.interview_id', array());
							
							$proveSelect->where('type IN (?)', array(HM_Interview_InterviewModel::MESSAGE_TYPE_TEST, HM_Interview_InterviewModel::MESSAGE_TYPE_QUESTION));
							$proveSelect->group(array('m.lesson_id', 'm.user_id'));
							$resProve =  $proveSelect->query()->fetchAll();
							$proveData = array(); # набор студентов, которые последние написали в уроке.
							if(!empty($resProve)){
								foreach($resProve as $p){
									$proveData[$p['lesson_id']][] = $p['user_id'];
								}
							}
							unset($resProve);
							unset($proveSelect);
							unset($subProveSelect);														
						}
						
						
						#### вычисляем кол-во студентов как пересечение между доступных тьютору и всего назначено на сессию
						foreach($totalData as $key => $i){
							
							if($i['availableStudents'] === false){ # доступны все студенты. 
								$tmpStudentIDs = $assignStudents[$i['subject_id']];
							} else {
								$tmpStudentIDs = (!empty($assignStudents[$i['subject_id']]) && !empty($i['availableStudents']))?(array_intersect($assignStudents[$i['subject_id']], $i['availableStudents'])):(array());
							}
							
							$totalData[$key]['countStudents'] 	= count($tmpStudentIDs);
							$totalData[$key]['groups'] 			= (empty($tmpStudentIDs))?(false):($serviceGroup->getGroupListOnUserIDs($tmpStudentIDs));
							$totalData[$key]['programms']		= (empty($totalData[$key]['groups']))?(false):($serviceSGProgramm->getProgrammsByGroups(array_keys($totalData[$key]['groups'])));
							$totalData[$key]['totalNeedBallStudentd'] =  ($totalData[$key]['countStudents']) * 3 * intval($totalData[$key]['zet']);
							$totalData[$key]['assignStudents']	= $this->getAssignCount($i['subject_id'], $tmpStudentIDs, $i['tutor_id']);
						
							if($report_type == HM_Report_ReportModel::REPORT_TYPE_PROVE){
								if(isset($proveData[$i['lesson_id']])){
									if($i['availableStudents'] === false){ # доступны все студенты. 
										$totalData[$key]['proveCount'] = count($proveData[$i['lesson_id']]);
									} else {
										$totalData[$key]['proveCount'] = count(array_intersect($proveData[$i['lesson_id']], $tmpStudentIDs));
									}
								} else {
									$totalData[$key]['proveCount'] = 0;
								}
							}
											
							unset($totalData[$key]['availableStudents']);
						}
						
						
						#### Берем только послеюнюю выставленную оценку.
						$subMessageSelect = $serviceUser->getSelect();
						$subMessageSelect->from('interview', array('interview_id'=> new Zend_Db_Expr('MAX(interview_id)')));
						$subMessageSelect->where('type = ?', HM_Interview_InterviewModel::MESSAGE_TYPE_BALL);
						$subMessageSelect->group(array('lesson_id', 'user_id', 'to_whom'));
						####
						
						#### Отбор кол-во выставленных оценок в уроке.
						$messageSelect = $serviceUser->getSelect();
						$messageSelect->from(array('m' => 'interview'), array(
							'lesson_id'		=> 'm.lesson_id',					
							'tutor_id'		=> 'm.user_id',									
							'count_students'=> new Zend_Db_Expr('COUNT(m.to_whom)'),					
						));
						$messageSelect->join(array('m2' => $subMessageSelect), 'm2.interview_id = m.interview_id', array());
						$messageSelect->where('m.type = ?', HM_Interview_InterviewModel::MESSAGE_TYPE_BALL);
						$messageSelect->where($serviceUser->quoteInto('m.user_id IN (?)', $tutorsIDs));
						$messageSelect->where($serviceUser->quoteInto('m.lesson_id IN (?)', $lessonIDs));
						$messageSelect->group(array('m.lesson_id', 'm.user_id'));
						####
						
						$resMes = $messageSelect->query()->fetchAll();
						unset($messageSelect);
						unset($subMessageSelect);						
						unset($subjectIDs);
						unset($tutorsIDs);
						$mesData = array();
						if(!empty($resMes)){
							foreach($resMes as $m){
								$mesData[$m['tutor_id']][$m['lesson_id']] = $m['count_students'];
							}
						}
						
						
						#берем кол-во выставленных оценок в уроке для журнала
						$jSelect = $serviceUser->getSelect();
						$jSelect->from(array('lu' => 'scheduleID'), array(
							'lesson_id'		=> 'lu.SHEID',												
							'count_students'=> new Zend_Db_Expr('COUNT(lu.MID)'),					
						));
						$jSelect->join(array('l' => 'schedule'), 'l.SHEID = lu.SHEID', array());
						$jSelect->where('l.typeID = ?', HM_Event_EventModel::TYPE_JOURNAL);
						$jSelect->where('lu.V_STATUS > ?', 0);
						
						$jSelect->where($serviceUser->quoteInto('l.SHEID IN (?)', $lessonIDs));
						$jSelect->group(array('lu.SHEID'));
						$resJournal = $jSelect->query()->fetchAll();
						$jData = array();
						if(!empty($resJournal)){
							foreach($resJournal as $m){
								$jData[$m['lesson_id']] = $m['count_students'];
							}
						}
						
						######
						unset($lessonIDs);
						
						
						
						
						$data = array();						
						foreach($res as $i){
							if($i['lesson_type'] == HM_Event_EventModel::TYPE_JOURNAL){
								$ballCount = (isset($jData[$i['lesson_id']])) ? ($jData[$i['lesson_id']]) : (0);					
							} else {
								$ballCount = (isset($mesData[$i['tutor_id']][$i['lesson_id']])) ? ($mesData[$i['tutor_id']][$i['lesson_id']]) : (0);													
							}
							#$ballCount = (isset($mesData[$i['tutor_id']][$i['lesson_id']])) ? ($mesData[$i['tutor_id']][$i['lesson_id']]) : (0);					
							$type = false;
							if(stristr($i['lesson_name'], 'Академическая активность') !== FALSE) { # тип 1
								$type = 1;
							} elseif(stristr($i['lesson_name'], 'Задание к разделу') !== FALSE) { # тип 2
								$type = 2;
							} elseif(stristr($i['lesson_name'], 'Рубежный контроль') !== FALSE) { # тип 3
								$type = 3;
							} elseif(stristr($i['lesson_name'], 'Итоговый контроль') !== FALSE) { # тип 4
								$type = 4;
							}	
							
							if($type){									
								$totalData[$i['tutor_id'].'~'.$i['subject_id']]['ballCount'][$type] = $totalData[$i['tutor_id'].'~'.$i['subject_id']]['ballCount'][$type] + $ballCount;												
								$totalData[$i['tutor_id'].'~'.$i['subject_id']]['ballCountDetail'][$type][$i['lesson_id']] = $ballCount;									
							}
						}
						unset($res);				
						unset($mesData);
						
						$this->_reportBallTutors 	= $totalData;
						$this->_countStudents 		= $countStudents;	
						$this->_reportTutorID 		= $tutor_id;						
						$this->_reportType 			= $report_type;						
						$this->saveToCache();
				}
				
				$this->view->countStudents 	= $countStudents;
				$this->view->data 			= $totalData;
				
				#$this->_serviceReport->clearCache();
				if($report_type == HM_Report_ReportModel::REPORT_TYPE_PROVE){
					echo $this->view->render('ball-tutors/ajax_prove.tpl');				
				} elseif($report_type == HM_Report_ReportModel::REPORT_TYPE_BALL){
					echo $this->view->render('ball-tutors/ajax.tpl');
				}				
				

			##########
			} catch (Exception $e) {
				echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
			}									
		}		
	}
	
	
	public function getFaculty($user_id){
		if(empty($user_id)){ return ''; }
		
		if(isset($this->_faculties[$user_id])){ return $this->_faculties[$user_id]; }
		if(!$this->_serviceOrgstructure){ $this->_serviceOrgstructure = $this->getService('Orgstructure');	}
		$this->_faculties[$user_id] = $this->_serviceOrgstructure->getFaculty($user_id);
		return $this->_faculties[$user_id];	
	}
	
	public function getDepartment($user_id){
		if(empty($user_id)){ return ''; }
		
		if(isset($this->_departments[$user_id])){ return $this->_departments[$user_id]; }
		if(!$this->_serviceOrgstructure){ $this->_serviceOrgstructure = $this->getService('Orgstructure');	}
		$this->_departments[$user_id] = $this->_serviceOrgstructure->getDepartmentName($user_id);
		return $this->_departments[$user_id];	
	}
	
	
	public function getCsvAction(){		
		$this->_helper->getHelper('layout')->disableLayout();
		Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
		$this->getHelper('viewRenderer')->setNoRender();
		$filename = 'Отчет по оценкам тьюторов_от_'.date('d.m.Y H.i');
		$this->getResponse()->setHeader('Content-type', 'application/octetstream; charset=UTF-8');
		$this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="'.$filename.'.csv');
		
		$this->restoreFromCache();						
		#$this->_reportBallTutors;
		#$this->_countStudents;
		
		$fields = array(
			_('ФИО тьютора'),			
			_('Факультет'),
			_('Кафедры'),
			_('Сессия'),
			_('ID сессии'),			
			_('ЗЕТ'),
			_('Семестр'),			
			_('Количество студентов'),
			_('Необходимо проверить работ всего'),
			_('Группы студентов'),
			_('Программы обучения'),
			
			_('Академическая активность выставил'),
			_('Академическая активность осталось'),
			_('Академическая активность выставил детально'),
			
			_('Задание к разделу выставил'),
			_('Задание к разделу осталось'),
			_('Задание к разделу выставил детально'),
			
			_('Рубежный контроль выставил'),
			_('Рубежный контроль осталось'),
			_('Рубежный контроль выставил детально'),
			
			_('Итоговый контроль выставил'),
			_('Итоговый контроль осталось'),
			_('Итоговый контроль выставил детально'),
			_('Курс завершен'),
			
			_('Ссылка на сессию'),
		);		
		//добавялем BOM
		echo "\xEF\xBB\xBF";
		echo implode(';', $fields);
		echo "\r\n";
		$delim = ';';
		foreach($this->_reportBallTutors as $i){            
			echo $i['tutor_name'].$delim;
			echo $i['faculty'].$delim;
			echo $i['department'].$delim;
			echo $i['subject_name'].$delim;
			echo $i['subject_id'].$delim;			
			echo $i['zet'].$delim;
			echo $i['semester'].$delim;
			echo $i['countStudents'].$delim;
			echo $i['totalNeedBallStudentd'].$delim;
			
			$groups = (!empty($i['groups']))?(implode(', ', $i['groups'])):('Нет');
			echo $groups.$delim;
			
			$programms = (!empty($i['programms']))?(implode(', ', $i['programms'])):('Нет');
			echo $programms.$delim;
			
			echo $i['ballCount'][1].$delim;
			$need = ($i['totalNeedBallStudentd'] - $i['ballCount'][1]);
			$need = ($need < 0)?(0):($need);
			echo  $need.$delim;			
			
			$detail_1 = (!empty($i['ballCountDetail'][1])) ? (implode(', ', $i['ballCountDetail'][1])) : ('');
			echo  $detail_1.$delim;
			
			echo $i['ballCount'][2].$delim;
			$need = ($i['totalNeedBallStudentd'] - $i['ballCount'][2]);
			
			$need = ($need < 0)?(0):($need);
			echo  $need.$delim;			
			
			$detail_2 = (!empty($i['ballCountDetail'][2])) ? (implode(', ', $i['ballCountDetail'][2])) : ('');
			echo  $detail_2.$delim;
			
			echo $i['ballCount'][3].$delim;
			$need = ($i['totalNeedBallStudentd'] - $i['ballCount'][3]);
			$need = ($need < 0)?(0):($need);
			echo  $need.$delim;			
			
			$detail_3 = (!empty($i['ballCountDetail'][3])) ? (implode(', ', $i['ballCountDetail'][3])) : ('');
			echo  $detail_3.$delim;
					
			echo $i['ballCount'][4].$delim;			
			$need = ($i['totalNeedBallStudentd'] - $i['ballCount'][4]);
			$need = ($need < 0)?(0):($need);
			echo  $need.$delim;		
			
			$detail_4 = (!empty($i['ballCountDetail'][4])) ? (implode(', ', $i['ballCountDetail'][4])) : ('');
			echo  $detail_4.$delim;
			
			$isGrad = ($i['assignStudents'] > 0 ? 'Нет' : 'Да');
			echo $isGrad.$delim;
			
			echo 'http://'.$_SERVER['SERVER_NAME'].$this->view->baseUrl($this->view->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'card', 'subject_id' => $i['subject_id']))).$delim;
			
			echo "\r\n";
		}				
	}
	
	
	public function getCsvProveAction(){		
		$this->_helper->getHelper('layout')->disableLayout();
		Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
		$this->getHelper('viewRenderer')->setNoRender();
		$filename = 'Отчет по оценкам тьюторов_от_'.date('d.m.Y H.i');
		$this->getResponse()->setHeader('Content-type', 'application/octetstream; charset=UTF-8');
		$this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="'.$filename.'.csv');
		
		$this->restoreFromCache();						
		#$this->_reportBallTutors;
		#$this->_countStudents;
		
		$fields = array(
			_('ФИО тьютора'),			
			_('Факультет'),
			_('Кафедры'),
			_('Сессия'),
			_('ID сессии'),			
			_('ЗЕТ'),
			_('Количество студентов'),
			_('Работа прикреплена, но не проверена'),
			_('Группы студентов'),
			_('Программы обучения'),
			_('Ссылка на сессию'),
		);		
		//добавялем BOM
		echo "\xEF\xBB\xBF";
		echo implode(';', $fields);
		echo "\r\n";
		$delim = ';';
		foreach($this->_reportBallTutors as $i){            
			echo $i['tutor_name'].$delim;
			echo $i['faculty'].$delim;
			echo $i['department'].$delim;
			echo $i['subject_name'].$delim;
			echo $i['subject_id'].$delim;			
			echo $i['zet'].$delim;
			echo $i['countStudents'].$delim;
			echo $i['proveCount'].$delim;
			
			$groups = (!empty($i['groups']))?(implode(', ', $i['groups'])):('Нет');
			echo $groups.$delim;
			
			$programms = (!empty($i['programms']))?(implode(', ', $i['programms'])):('Нет');
			echo $programms.$delim;
			
			echo 'http://'.$_SERVER['SERVER_NAME'].$this->view->baseUrl($this->view->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'card', 'subject_id' => $i['subject_id']))).$delim;
			
			echo "\r\n";
		}				
	}
	
	/**
	 * Кол-во обучающихся студентов на сессии в данный момент с учетом доступных студентов тьютору
	*/
	public function getAssignCount($subject_id, $available_students, $tutor_id){
		if(empty($available_students)){ return 0; }
		
		if(isset($this->_countAssignStudents[$subject_id][$tutor_id])){	return $this->_countAssignStudents[$subject_id][$tutor_id];	}
		
		if(!$this->_serviceStudent){ $this->_serviceStudent = $this->getService('Student'); }
		
		$students 											= $this->_serviceStudent->getAssignStudents($subject_id);
		$this->_countAssignStudents[$subject_id][$tutor_id] = count(	array_intersect($students, $available_students)	);
		
		return $this->_countAssignStudents[$subject_id][$tutor_id];
	}
	
	//Транслит с русского на английски1
	public function translit($str='') {
		
		$cyrForTranslit = array('а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п',
						   'р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',
						   'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П',
						   'Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я'); 
		$latForTranslit = array('a','b','v','g','d','e','yo','zh','z','i','y','k','l','m','n','o','p',
							'r','s','t','u','f','h','ts','ch','sh','sch','','y','','ae','yu','ya',
							'A','B','V','G','D','E','Yo','Zh','Z','I','Y','K','L','M','N','O','P',
							'R','S','T','U','F','H','Ts','Ch','Sh','Sch','','Y','','Ae','Yu','Ya'); 
							
		return str_replace($cyrForTranslit, $latForTranslit, $str);
	}	

}