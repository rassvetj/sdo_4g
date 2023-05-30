<?php
class Report_BallController extends HM_Controller_Action_Crud
{
    # default date begin 2016-09-01
	protected $_report_content  = array(); # данные текущего отчета
    protected $_reportParams  	= NULL;   # набор параметров отчета, по которым 
	
	const CACHE_NAME = 'Report_BallController';
	
	
	public $_fields = array(
		'1'  => 'ФИО тьютора',
		'2'  => 'л/пр.з',
		'2.2'  => 'л/пр.з занятий',
		'3'  => 'Факультет (по сессии)',
		'4'  => 'Кафедра (по сессии)',
		'5'  => 'Дисциплина (по сессии)',
		'6'  => 'ID сессии (из 1С)',
		'7'  => 'Дата начала',
		'8'  => 'Дата окончания',
		'9'  => 'ЗЕТ',
		'10' => 'Семестр (четный/нечетный)',
		'11' => 'Количество студентов',
		'12' => 'Группы студентов',
		'13' => 'Программы обучения',
		'14' => '%заполнения журналов-лекции АА',
		'15' => '%заполнения журналов-практика АА',
		'16' => '%заполнения журналов-лаб.работы АА',
		'17' => '%выставленных оценок за рубежные контроли',
		'18' => 'Рубежный контроль выставил детально (кол-во оценок по каждому РК через запятую. Как в предыдущей версии Отчета по оценкам тьютора)',
		'19' => '%выставленных оценок за ИПЗ',
		'20' => '%выполнения плана',
		'21' => 'Тип',
		'22' => 'Ссылка на сессию',
		'23' => 'Ко-во лекций',
		'24' => 'Кол-во ПрЗ',
		'25' => 'Кол-во Лаб.',
	);
	
	public function saveToCache()
    {
        return Zend_Registry::get('cache')->save(
            array(                
                'report_content' => $this->_report_content,                                                  
                'reportParams'   => $this->_reportParams,                                                  
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
            $this->_report_content  	= $actions['report_content'];
            $this->_reportParams  		= $actions['reportParams'];
            $this->_restoredFromCache 	= true;
            return true;
        }
        return false;
    }
	
	
	public function init()
    {
        parent::init();       
        $this->getService('Unmanaged')->setHeader(_('О выставлении оценок'));
    }
	
	public function indexAction()
    {        
		$this->clearCache();		
		$this->view->headLink()->appendStylesheet($config->url->base.'/css/rgsu_style.css');		
		$this->view->headLink()->appendStylesheet('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/css/select2.min.css');		
		$this->view->headScript()->appendFile('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/js/select2.min.js');
		
		$this->view->form = new HM_Form_Ball();		
    }
	
	public function getAction()
    {	
		$this->getHelper('viewRenderer')->setNoRender();
		
		$request = $this->getRequest();
        if (!$request->isPost() && $request->isGet()) {
			$this->view->content = '';
			echo $this->view->render('ball/ajax.tpl');	
			die;			
		}
			
		$this->view->data 	 = $this->getReportContent();

		$this->view->fields  = $this->_fields;
		
		$this->view->content = $this->view->render('ball/parts/_base.tpl');
		echo $this->view->render('ball/ajax.tpl');	
	}
	
	public function csvAction(){		
		$this->_helper->getHelper('layout')->disableLayout();
		Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
		$this->getHelper('viewRenderer')->setNoRender();
		
		$filename = 'Оценки в СДО-'.date('d.m.Y H.i');
		$this->getResponse()->setHeader('Content-type', 'application/octetstream; charset=UTF-8');
		$this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="'.$filename.'.csv');
		
		$this->restoreFromCache();	
		$this->view->content = $this->_report_content;
		$this->view->fields  = $this->_fields;
		echo $this->view->render('ball/parts/_csv.tpl');				
	}
	
	public function getReportContent(){
		$request 			= $this->getRequest();
		$subject_type_id 	= $request->getParam('subject_type_id', 1); # -1 - все,  1 - ДО
		$tutor_id 			= (int) $request->getParam('tutor_id', 0); # 0 - все
		$chair_name 		= $request->getParam('chair_name', 0); # 0 - все, -1 - без кафедры
		$faculty_name 		= $request->getParam('faculty_name', 0); # 0 - все, -1 - без кафедры
		
		$subject_begin 		= $request->getParam('subject_begin', '01.09.2016'); # Дата начала сессии больше или равна, чем
		
		$date 				= DateTime::createFromFormat('d.m.Y', $subject_begin);
		
		$subject_begin		= ($date) ? $date->format('Y-m-d') : '2016-09-01';
		
		
		
		
		$this->serviceUser			= ($this->serviceUser) 			? $this->serviceUser 		: $this->getService('User');
		$this->serviceSubject		= ($this->serviceSubject)		? $this->serviceSubject 	: $this->getService('Subject');
		$this->serviceGroup			= ($this->serviceGroup)			? $this->serviceGroup 		: $this->getService('StudyGroup');
		$this->serviceSGProgramm	= ($this->serviceSGProgramm)	? $this->serviceSGProgramm 	: $this->getService('StudyGroupProgramm');
		$this->serviceTutorAssign	= ($this->serviceTutorAssign)	? $this->serviceTutorAssign : $this->getService('LessonAssignTutor');
		$this->serviceJournal		= ($this->serviceJournal)		? $this->serviceJournal 	: $this->getService('LessonJournal');
		$this->serviceInterview		= ($this->serviceInterview)		? $this->serviceInterview 	: $this->getService('Interview');
		$this->serviceLesson		= ($this->serviceLesson)		? $this->serviceLesson 		: $this->getService('Lesson');
	
	
		$reportParams 		= $subject_type_id.':'.$tutor_id.':'.$chair_name.':'.$faculty_name.':'.$subject_begin;
		
		
		$this->restoreFromCache();
		if($this->_reportParams === $reportParams){		
			return $this->_report_content;
		}					
		$data 					= array();
		$this->_reportParams 	= $reportParams;
		$this->_report_content	= $data;
		$this->saveToCache();
		
		################
		
		### Сессии, которые будут исключены из основного запроса
		#$subSelect = $this->serviceUser->getSelect();
		#$subSelect->from(array('subj' => 'subjects'), array('subj' => 'subj.subid'));
		#$subSelect->join(array('l' => 'schedule'), 'l.CID = subj.subid', array());
		#$subSelect->where('l.typeID IN (?)', HM_Event_EventModel::TYPE_TEST); # тип "тест"
		#$subSelect->where('l.isfree = ?', HM_Lesson_LessonModel::MODE_PLAN);
		#$subSelect->group('subj.subid');		
		###
		
		$select = $this->serviceUser->getSelect();
					
		if($tutor_id > 0){ $select->where($this->serviceUser->quoteInto('t.MID = ?', $tutor_id)); }
		
		$select->from(array('t' => 'Tutors'), array(
			'tutor_id'				=> 'p.MID',
			'fio'					=> new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
			'subject_id'			=> 'subj.subid',
			'subject_name'			=> 'subj.name',
			'lesson_id'				=> 'l.SHEID',
			'lesson_name'			=> 'l.title',
			'lesson_type'			=> 'l.typeID',
			'zet'					=> 'subj.zet',
			'semester'				=> 'subj.semester',
			'subject_external_id'	=> 'subj.external_id',
			'subject_faculty'		=> 'subj.faculty',
			'subject_chair'			=> 'subj.chair',
			'roles'					=> 't.roles',
			'roles_lessons'			=> 'tl.role_id',
			'subject_lection'		=> 'subj.lection',
			'subject_lab'			=> 'subj.lab',
			'subject_practice'		=> 'subj.practice',		
			'subject_begin'			=> 'subj.begin',		
			'subject_end'			=> 'subj.end',		
			'subject_isDO'			=> 'subj.isDO',		
		));
		$select->join(array('p' 	=> 'People'), 	't.MID = p.MID', 	  array());
		$select->join(array('subj'	=> 'subjects'), 'subj.subid = t.CID', array());
		$select->join(array('l' 	=> 'schedule'), 'l.CID = subj.subid', array());
		$select->joinLeft(array('tl'=> 'Tutors_lessons'), 'tl.MID = p.MID AND tl.CID = subj.subid AND tl.LID = l.SHEID', array());
				
		//$select->where($this->serviceUser->quoteInto('subj.subid NOT IN (?)', $subSelect));
		$select->where('l.typeID NOT IN (?)', 	array_keys(HM_Event_EventModel::getExcludedTypes()));  
		$select->where('l.isfree = ?', 			HM_Lesson_LessonModel::MODE_PLAN);		
		$select->where('subj.base = ?', 2); 		
		$select->where('subj.begin >= ?', $subject_begin);
		$select->where('subj.time_ended_debt IS NULL');
		$select->where('subj.time_ended_debt_2 IS NULL');
		
		$select->where($this->serviceUser->quoteInto(
			array("(l.title LIKE ? ", " OR l.title LIKE ? ", " OR l.title LIKE ? ", " OR l.title LIKE ?)", " OR l.typeID IN (?)"),
			array('%Академическая активность%', '%Задание к разделу%', '%Рубежный контроль%', '%Итоговый контроль%', array(HM_Event_EventModel::TYPE_JOURNAL_LECTURE,HM_Event_EventModel::TYPE_JOURNAL_PRACTICE,HM_Event_EventModel::TYPE_JOURNAL_LAB))
		));
		
		if($subject_type_id == -1){ # все
			
		} elseif($subject_type_id == HM_Subject_SubjectModel::FACULTY_OTHER){ # 0 - неДО			
			$select->where('(subj.isDO = ? OR subj.isDO IS NULL)', HM_Subject_SubjectModel::FACULTY_OTHER);
		} else {			
			$select->where('subj.isDO = ?', (int)$subject_type_id);			
		}
		
		
		if($chair_name == -1){ # без кафедры			
			$select->where("(subj.chair = '' OR subj.chair IS NULL)");
		} elseif($chair_name == '0'){ # 0 - все			
			
		} else {						
			$select->where($this->serviceUser->quoteInto("subj.chair = ?", $chair_name));
		}
		
		
		if($faculty_name == -1){ # без факультета			
			$select->where("(subj.faculty = '' OR subj.faculty IS NULL)");
		} elseif($faculty_name == '0'){ # 0 - все			
			
		} else {						
			$select->where($this->serviceUser->quoteInto("subj.faculty = ?", $faculty_name));
		}
		
		$select->where('(p.blocked IS NULL OR p.blocked = ?)', HM_User_UserModel::STATUS_ACTIVE);
		
		$res  = $select->query()->fetchAll();
		#pr($res);
		
		#die;
		unset($select);
		#unset($subSelect);
		if(empty($res)){
			return $data;			
		}
		
		# проход ради объединения назначений в занятиях
		$isAssinOnLesson = array(); # есть назначение в сессии на какие-нибудь занятие
		foreach($res as $i){
			if($i['roles_lessons'] === NULL){ continue; }
			$row_key = $i['tutor_id'].'~'.$i['subject_id'];
			$isAssinOnLesson[$row_key] = true;			
		}
		
		$tutorsIDs 	= array();		
		$lessonIDs 	= array();
		$subjectIDs = array();
		$journalPercentComplete = array();
		$boundaryControl 		= array(); # сумма кол-ва выставленных оценок в рубжном контроле всех занятий сессии.
		$boundaryControlDetail	= array(); # кол-ва выставленных оценок в рубжном контроле по каждому занятию отдельно
		$ipz					= array(); # ИПЗ
		foreach($res as $i){
			$row_key = $i['tutor_id'].'~'.$i['subject_id'];
			
			$lessonIDs[$i['lesson_id']] = $i['lesson_id'];			
			# половиним часы лекций, лаб и пр., т.к. в журнале один день - это 2 часа (или один академ час), 
			if($this->isLectureAvailable($i['roles'], $i['lesson_type'], $isAssinOnLesson, $i['roles_lessons'])){				
				$percent = $this->serviceJournal->journalPercentComplete($i['lesson_id'], ceil($i['subject_lection']/2), $i['tutor_id'], $i['subject_id']);
				if($percent !== false){
					$journalPercentComplete[ $row_key ]['lecture'][$i['lesson_id']] = $percent;
				}
				
			} elseif($this->isPracticeAvailable($i['roles'], $i['lesson_type'], $isAssinOnLesson, $i['roles_lessons'])){				
				$percent = $this->serviceJournal->journalPercentComplete($i['lesson_id'], ceil($i['subject_practice']/2), $i['tutor_id'], $i['subject_id']);
				if($percent !== false){
					$journalPercentComplete[ $row_key ]['practice'][$i['lesson_id']] = $percent;			
				}
				
			} elseif($this->isLabAvailable($i['roles'], $i['lesson_type'], $isAssinOnLesson, $i['roles_lessons'])){				
				$percent = $this->serviceJournal->journalPercentComplete($i['lesson_id'], ceil($i['subject_lab']/2), $i['tutor_id'], $i['subject_id']);
				if($percent !== false){
					$journalPercentComplete[ $row_key ]['lab'][$i['lesson_id']] = $percent;				
				}
				
			}
			
			# учитывает только роль лектор без доп ролей. Т.е. если будут доп роли, то и будет доступ к прочим занятиям.
			# для лектора не надо следить за ИПЗ и рубжным контролем
			if($i['roles']  != HM_Lesson_Assign_Tutor_TutorModel::ROLE_LECTOR){
				
				if($this->serviceLesson->isBoundaryControl($i['lesson_name'])){	 #рубжном контроле			
					if(!isset($boundaryControl[ $row_key ])){ $boundaryControl[ $row_key ] = 0; }
					$countTutorMarks = $this->serviceInterview->getCountTutorMarks($i['tutor_id'], $i['lesson_id'], $i['subject_id']); # кол-во выставленных оценок тьютором
					$boundaryControl[ $row_key ] += $countTutorMarks;
					$boundaryControlDetail[ $row_key ][$i['lesson_id']] = $countTutorMarks;
				
				} elseif($this->serviceLesson->isTotalPractic($i['lesson_name'])){ #ИПЗ
					$countTutorMarks = $this->serviceInterview->getCountTutorMarks($i['tutor_id'], $i['lesson_id'], $i['subject_id']); # кол-во выставленных оценок тьютором
					$ipz[$row_key][$i['lesson_id']] = $countTutorMarks;				
				}
				
			}
			
			#boundary control = кол-во выставленных оценок во всех РК/(кол-во зет*кол-во студентов)*100%
			
			//if(!isset($journalPercentComplete[$i['subject_id']][$i['lesson_id']])){
				//$journalPercentComplete[$i['subject_id']][$i['lesson_id']] = $this->serviceJournal->journalPercentComplete($i['lesson_id']);
			//}
			
			
			# создаем массив уроков, в котором считаем % прохождения в рамках одного занятия к общему кол-ву оцениваемых дней.
			# Если это лектор, то следим за журналом-лекция
			# Если это практик, то следим и за журналом практики и за выставлением оценок в рубежных контролях и ИПЗ
			# Если это лаборант, то следим за журналом-лаб.работы 
			# если ведет и лекции и практики – выводим все значения
			
			
			if(isset($data[ $row_key ])){ 
				$data[ $row_key ]['roles_lessons'][] = $i['roles_lessons'];
				continue; 			
			}
			
			$subjectIDs[$i['subject_id']]	= $i['subject_id'];
			$tutorsIDs[$i['tutor_id']] 		= $i['tutor_id'];			
			
			
			
			$data[ $row_key ] = array(
				'tutor_name' 			=> $i['fio'],				
				'subject_id'			=> $i['subject_id'],				
				'subject_name'			=> $i['subject_name'],
				'subject_external_id'	=> $i['subject_external_id'],
				'subject_faculty'		=> $i['subject_faculty'],
				'subject_chair'			=> $i['subject_chair'],
				'zet'					=> $i['zet'],					
				'semester'				=> ( $i['semester'] % 2 ) ? ('не четный') : ('четный'),
				'availableStudents' 	=> $this->serviceSubject->getAvailableStudents($i['tutor_id'], $i['subject_id']),				
				'roles'					=> $this->getRolesName($i['roles']),
				'roles_lessons'			=> array($i['roles_lessons']), #$this->getMultipleRolesName($i['roles_lessons']),
				'subject_begin'			=> $i['subject_begin'],
				'subject_end'			=> $i['subject_end'],
				'subject_isDO'			=> $this->updateIsDO($i['subject_isDO']),
				//'percent_lab'			=> '',
				//'percent_practice'	=> '',
				//'percent_lecture'		=> '',
				'subject_lab'			=> $i['subject_lab'],
				'subject_practice'		=> $i['subject_practice'],
				'subject_lection'		=> $i['subject_lection'],
			);
		}
		
		if(!empty($journalPercentComplete)){
			foreach($journalPercentComplete as $key => $v){
				if(!isset($data[$key])){ continue; }
				if(!empty($v['lab'])){
					$data[$key]['percent_lab'] 	   = implode(' / ', $v['lab']);
					$data[$key]['percent_lab_avg'] = array_sum($v['lab']) / count($v['lab']);
				}
				
				if(!empty($v['practice'])){
					$data[$key]['percent_practice'] 	= implode(' / ', $v['practice']);
					$data[$key]['percent_practice_avg'] = array_sum($v['practice']) / count($v['practice']);
				}
				
				if(!empty($v['lecture'])){
					$data[$key]['percent_lecture'] 		= implode(' / ', $v['lecture']);
					$data[$key]['percent_lecture_avg'] 	= array_sum($v['lecture']) / count($v['lecture']);
				}				
			}
			unset($journalPercentComplete);			
		}
		
		
		
		if(empty($tutorsIDs) || empty($lessonIDs) || empty($subjectIDs)){
			unset($res);
			unset($tutorsIDs);
			unset($lessonIDs);
			unset($subjectIDs);
			unset($rolesIDs);
			$data = array();
			return $data;
		}
		# M_Report_ReportModel::REPORT_TYPE_PROVE - не учитываем
		
		
		#### Находим слушателей. Завершивших обучение пока не учитываем
		$subUsers = $this->serviceUser->getSelect();
		$subUsers->from(array('Students'),	array('MID','CID'));
		
		#$subGrad = $this->serviceUser->getSelect();
		#$subGrad->from(array('graduated'),	array('MID','CID'));
		
		$subUSelect = $this->serviceUser->getSelect();
		#$subUSelect->union(array($subUsers, $subGrad));
		$subUSelect->union(array($subUsers));
		
		$studentSelect = $this->serviceUser->getSelect();			
		$studentSelect->from(array('s' => $subUSelect), array('subject_id' => 's.CID', 'student_id' => 's.MID'));				
		$studentSelect->join(array('p' => 'People'), 'p.MID = s.MID', array());
		$studentSelect->where('s.CID > ?', 0);
		$studentSelect->where('s.MID > ?', 0);
		$studentSelect->where($this->serviceUser->quoteInto('s.CID IN (?)', $subjectIDs));
		$studentSelect->group(array('s.CID', 's.MID'));
		$resStudent = $studentSelect->query()->fetchAll();
		$assignStudents = array();		
		if(!empty($resStudent)){
			foreach($resStudent as $i){
				$assignStudents[$i['subject_id']][$i['student_id']] = $i['student_id'];
			}
		}		
		unset($studentSelect);
		unset($resStudent);	
		unset($subUSelect);								
		unset($subUsers);								
		
		#if($this->getService('User')->getCurrentUserId() == 5829){
			# получаем список всех активных пользователей
			$activeUsers 		= array();
			$selectActiveUsers 	= $this->serviceUser->getSelect();
			$selectActiveUsers->from(array('People'), array('MID'));
			$selectActiveUsers->where($this->serviceUser->quoteInto('(blocked != ?)', HM_User_UserModel::STATUS_BLOCKED));
			$resActiveUsers 	= $selectActiveUsers->query()->fetchAll();
			if(!empty($resActiveUsers)){
				foreach($resActiveUsers as $u){
					$activeUsers[$u['MID']] = $u['MID'];
				}
			}			
		#}
		
		
		#### вычисляем кол-во студентов как пересечение между доступных тьютору и всего назначено на сессию
		foreach($data as $key => $i){
			$data[ $key ]['roles_lessons'] = $this->getMultipleRolesName($i['roles_lessons']);
		
			if($i['availableStudents'] === false){ # доступны все студенты. 
				$tmpStudentIDs = $assignStudents[$i['subject_id']];
			} else {
				$tmpStudentIDs = (!empty($assignStudents[$i['subject_id']]) && !empty($i['availableStudents']))		?		array_intersect($assignStudents[$i['subject_id']], $i['availableStudents'])		:		array();
			}
			
			# NEEDED FIXED!!! Костыль, который оставляет только активных пользователей.
			if(!empty($tmpStudentIDs)){
				$new_tmpStudentIDs = array();
				foreach($tmpStudentIDs as $u){
					if(isset($activeUsers[$u])){
						$new_tmpStudentIDs[$u] = $u;
					}
				}
				$tmpStudentIDs = $new_tmpStudentIDs;
			}
			
			
			#if($this->getService('User')->getCurrentUserId() == 5829 && $i['subject_id'] == 18557){
				#pr($assignStudents[$i['subject_id']]);
				#pr($i['availableStudents']);
				#pr($tmpStudentIDs);
			#}
			
			
			$data[$key]['countStudents'] 		= count($tmpStudentIDs);
			$data[$key]['groups'] 				= empty($tmpStudentIDs)			?	false	:	$this->serviceGroup->getGroupListOnUserIDs($tmpStudentIDs);
			$data[$key]['programms']			= empty($data[$key]['groups'])	?	false	:	$this->serviceSGProgramm->getProgrammsByGroups(array_keys($data[$key]['groups']));
			//$data[$key]['totalNeedBallStudentd']= intval($data[$key]['countStudents']) * 3 * intval($data[$key]['zet']);	
							
			unset($data[$key]['availableStudents']);
		}
		
		
		if(!empty($boundaryControl)){			
			foreach($boundaryControl as $key => $count_balls){
				if(!isset($data[$key])){ continue; }

				$divider = intval($data[$key]['countStudents']) * intval($data[$key]['zet']);
				$data[$key]['percent_boundary_control'] = ($divider > 0) ? round( (intval($count_balls) / $divider * 100) ) : 0;
				#echo '('.$count_balls.' / '.$data[$key]['countStudents'].' * '.$data[$key]['zet'].') * 100';
			}
			unset($boundaryControl);
		}
		
		
		if(!empty($boundaryControlDetail)){
			foreach($boundaryControlDetail as $key => $lessons){
				if(!isset($data[$key])){ continue; }
				if(empty($lessons)){ continue; }
				$data[$key]['boundary_control_detail'] = implode(', ', $lessons);
			}
			unset($boundaryControlDetail);
		}
		
		if(!empty($ipz)){
			foreach($ipz as $key => $lessons){
				if(!isset($data[$key])){ continue; }
				if(empty($lessons)){ continue; }
				$tmp = array();
				foreach($lessons as $lesson_id => $count_balls){
					$tmp[] = round($count_balls / intval($data[$key]['countStudents']) * 100);
				}
				$data[$key]['percent_ipz'] 		= implode(' / ', $tmp);
				$data[$key]['percent_ipz_avg'] 	= round(array_sum($tmp) / count($tmp));
			}
			unset($ipz);
		}
		
		foreach($data as $key => $row){
			$params_count = 0;
			$sum		  = 0;
			if(isset($row['percent_lab_avg'])){
				$sum += $row['percent_lab_avg'];
				$params_count++;				
			}
			
			if(isset($row['percent_practice_avg'])){
				$sum += $row['percent_practice_avg'];
				$params_count++;
			}
			
			if(isset($row['percent_lecture_avg'])){
				$sum += $row['percent_lecture_avg'];
				$params_count++;
			}
			
			if(isset($row['percent_boundary_control'])){
				$sum += $row['percent_boundary_control'];
				$params_count++;
			}
			
			if(isset($row['percent_ipz_avg'])){
				$sum += $row['percent_ipz_avg'];
				$params_count++;
			}
			
			if(empty($params_count)){ continue; }
			
			$data[$key]['percent_plan_ready'] = round($sum / $params_count);
			
		}
		
		
		/*		
	•	л/пр.з (прим.1)
	•	Группы студентов
	•	Программы обучения
	•	%заполнения журналов-лекции АА (прим.2)
	•	%заполнения журналов-практика АА (прим.2)
	•	%заполнения журналов-лаб.работы АА (прим.2)
	•	%выставленных оценок за рубежные контроли (прим.3)
	•	Рубежный контроль выставил детально (кол-во оценок по каждому РК через запятую. Как в предыдущей версии Отчета по оценкам тьютора)
	•	%выставленных оценок за ИПЗ (прим.4)
	•	%выполнения плана (прим.5)
		**/
		
		################		
		$this->_report_content	= $data;
		$this->saveToCache();
		return $data;
		
	}
	
	
	/**
	 * получает массив названий ролей назначения
	*/
	public function getRolesName($role_code){		
		if(!$role_code){ return ''; }
		return str_replace(',', '+', HM_Lesson_Assign_Tutor_TutorModel::getRolesName($role_code));
	}
	
	
	/**
	 * В случае, когда разных ролей несколько из разнах источников.
	 * @param $role_codes array 
	*/
	public function getMultipleRolesName($role_codes){		
		if(empty($role_codes)){ return ''; }
		$total_roles = array();
		foreach($role_codes as $role){
			$role_names = HM_Lesson_Assign_Tutor_TutorModel::getRolesName($role);
			$tmp 		= explode(',',$role_names);			
			foreach($tmp as $i){
				$total_roles[$i] = $i;	
			}			
		}
		$total_roles = array_filter($total_roles);
		return implode('+', $total_roles);		
	}
	
	/**
	 * доступно ли занятие Лекция тьютору по роли.
	 * Если это не лекция или недоступна по роли, то false	
	 * Если $role_lesson !== NULL, значит есть назначение на это занятие и должен быть доступ к нему, вне зависимости от роли в назначении на занятие, 
	*/
	public function isLectureAvailable($role_id, $lesson_type, $isAssinOnLesson, $role_lesson){
		if($lesson_type != HM_Event_EventModel::TYPE_JOURNAL_LECTURE){ return false; }
		
		if(empty($role_id) && empty($isAssinOnLesson)){ return true; } # нет роли в сессии и нет назначений на занятия => доступны все занятия
		
		# нет роли в сессии, но есть роль в занятии и берем ее.
		/*
		if(empty($role_id)){
			$role_id = $role_lesson;			
		}
		*/
		if((HM_Lesson_Assign_Tutor_TutorModel::ROLE_LECTOR & $role_id) == HM_Lesson_Assign_Tutor_TutorModel::ROLE_LECTOR){
			return true;
		}
		
		# Есть какая-то роль, но неважно какая, важно, что есть назначение на это занятие.
		if($role_lesson !== NULL){
			return true;			
		}
		
		
		
		return false;
	}
	
	
	/**
	 * доступно ли занятие Практика тьютору по роли.
	 * Если это не Практика или недоступна по роли, то false	 
	 * Если $role_lesson !== NULL, значит есть назначение на это занятие и должен быть доступ к нему, вне зависимости от роли в назначении на занятие, 
	*/
	public function isPracticeAvailable($role_id, $lesson_type, $isAssinOnLesson, $role_lesson){
		if($lesson_type != HM_Event_EventModel::TYPE_JOURNAL_PRACTICE){ return false; }
		
		if(empty($role_id) && empty($isAssinOnLesson)){ return true; } # нет роли в сессии и нет назначений на занятия => доступны все занятия
		
		# нет роли в сессии, но есть роль в занятии и берем ее.
		/*
		if(empty($role_id)){
			$role_id = $role_lesson;			
		}
		*/
		
		if((HM_Lesson_Assign_Tutor_TutorModel::ROLE_PRACTICE & $role_id) == HM_Lesson_Assign_Tutor_TutorModel::ROLE_PRACTICE){
			return true;
		}
		
		# Есть какая-то роль, но неважно какая, важно, что есть назначение на это занятие.
		if($role_lesson !== NULL){
			return true;			
		}
		
		return false;
	}
	
	/**
	 * доступно ли занятие Л.р. тьютору по роли.
	 * Если это не Л.р. или недоступна по роли, то false	 
	 * Если $role_lesson !== NULL, значит есть назначение на это занятие и должен быть доступ к нему, вне зависимости от роли в назначении на занятие, 
	*/
	public function isLabAvailable($role_id, $lesson_type, $isAssinOnLesson, $role_lesson){
		if($lesson_type != HM_Event_EventModel::TYPE_JOURNAL_LAB){ return false; }
		
		if(empty($role_id) && empty($isAssinOnLesson)){ return true; } # нет роли в сессии и нет назначений на занятия => доступны все занятия
		
		# нет роли в сессии, но есть роль в занятии и берем ее.
		/*
		if(empty($role_id)){
			$role_id = $role_lesson;			
		}
		*/
		
		if((HM_Lesson_Assign_Tutor_TutorModel::ROLE_LAB & $role_id) == HM_Lesson_Assign_Tutor_TutorModel::ROLE_LAB){
			return true;
		}
		
		# Есть какая-то роль, но неважно какая, важно, что есть назначение на это занятие.
		if($role_lesson !== NULL){
			return true;			
		}
		
		
		return false;
	}	
		
	/**
	 * Тип сессии - ДО, неДО, ДПО
	*/
	public function updateIsDO($type_id){
		$type_id 	= (int)$type_id;
		$types 		= HM_Subject_SubjectModel::getFacultys();
		return $types[$type_id];		
	}
		
	

	
}
?>