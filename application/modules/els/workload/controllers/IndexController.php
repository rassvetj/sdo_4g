<?php
class Workload_IndexController extends HM_Controller_Action
{
    public function indexAction()
    {        
		$mid = '5829'; //--теукщий пользователь
		$subjectID = '3335';
		$lessonID = '15745';		
		$toWhomID = '21550'; //--id студента
		$forumID = '6';
		
		//$tt = $this->getService('Workload')->setLessonViolation($mid, $toWhomID, $lessonID, $subjectID);
		//$tt = $this->getService('WorkloadForum')->setForumViolation($mid, $forumID);	
		
		//$users = $this->getService('WorkloadSheet')->getOrgstructurePersons($mid);
		//$subjects = $this->getService('WorkloadSheet')->getSubjectList($users);		
		//pr(111111);
		
		//--выбираем все сессии, на которые назначены тьюторы из $users
		//--делаем отделную страницу для вывода сессий с кнопками
    }
	
	// Находит лекционный материал студента, в который он не заходил, у которого за сессию (задание на проверку) стоит оценка. И выставляет оценку за этот материал: имитация захода в материал (стартуем занятие).
	public function setBallAction()
    { 
		try {
 

		/*
		$this->getHelper('viewRenderer')->setNoRender();		
		
		if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
		
		$select = $this->_serviceWorkload->getSelect();
		$select->from(
			array('p' => 'People'),
			array(				
				'MID' => 'p.MID',					
				'subid' => 'subj.subid',					
				'subject_name' => 'subj.name',					
				'lesson_id' => 'l.SHEID',	
				'lesson_name' => 'l.title',	
				'V_STATUS' => 'lu.V_STATUS',	
				'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),										
			)
		);
		$select->join(
			array('s' => 'Students'),
			'p.MID = s.MID',							
			array()
		);
		$select->join(
			array('subj' => 'subjects'),
			's.CID = subj.subid',
			array()
		);
		$select->join(
			array('l' => 'schedule'),
			'l.CID = subj.subid',
			array()
		);
		$select->join(
			array('lu' => 'scheduleID'),
			'lu.SHEID = l.SHEID AND s.MID = lu.MID',
			array()
		);		 
		$select->where($this->_serviceWorkload->quoteInto('lu.V_STATUS = ?', '-1')); //--оценка не стоит
		$select->where($this->_serviceWorkload->quoteInto('l.typeID = ?', HM_Event_EventModel::TYPE_RESOURCE)); //--тип информационный речсурс
		$select->where($this->_serviceWorkload->quoteInto('l.required = ?', 1));		//--обязательный
		
		$totalForUpdate = $select->query()->fetchAll(); //--надо обновить, но надо исключить те сессии, в которых уже выставлена оценка.
		
		
		$select2 = $this->_serviceWorkload->getSelect();
		$select2->from(
			array('p' => 'People'),
			array(				
				'MID' => 'p.MID',					
				'subid' => 'subj.subid',														
				'V_STATUS' => 'lu.V_STATUS',														
			)
		);
		$select2->join(
			array('s' => 'Students'),
			'p.MID = s.MID',							
			array()
		);
		$select2->join(
			array('subj' => 'subjects'),
			's.CID = subj.subid',
			array()
		);
		$select2->join(
			array('l' => 'schedule'),
			'l.CID = subj.subid',
			array()
		);
		$select2->join(
			array('lu' => 'scheduleID'),
			'lu.SHEID = l.SHEID AND s.MID = lu.MID',
			array()
		);		 
		$select2->where($this->_serviceWorkload->quoteInto('l.typeID = ?', HM_Event_EventModel::TYPE_TASK)); //--дадание на проверку
		$select2->where($this->_serviceWorkload->quoteInto('lu.V_STATUS > ?', '0')); //--есть оценка
		$select2->group(array('p.MID', 'subj.subid', 'lu.V_STATUS'));
		$needExcludeSubjects = $select2->query()->fetchAll(); //--вессии, для которых не надо добавлять баллы
		
		
		$excluded = array();
		if(count($needExcludeSubjects)){
			foreach($needExcludeSubjects as $i){
				$excluded[$i['MID']][$i['subid']] = $i['subid'];
			}
		}
		
		$totalForUpdated = array();
		if(count($totalForUpdate)){
			foreach($totalForUpdate as $i){
				if(isset($excluded[$i['MID']][$i['subid']])){ //--если есть сессии, в которой оценка выставленна.
					$totalForUpdated[] = $i;
				}
			}
		}
		*/
		/*
		echo '<style>';
		echo 'table td {
			border: 1px solid black;
			pedding: 3px;
		}';
		echo '</style>';
		echo '<table>';
			echo '<tr>';
				echo '<td>id сессии</td>';
				echo '<td>ФИО</td>';
				echo '<td>Сессия</td>';
				echo '<td>Урок</td>';				
			echo '</tr>';
		foreach($totalForUpdated as $i){
			echo '<tr>';
				echo '<td>'.$i['subid'].'</td>';
				echo '<td>'.$i['fio'].'</td>';
				echo '<td><a target="_blank" href="/subject/index/card/subject_id/'.$i['subid'].'">'.$i['subject_name'].'</a></td>';
				echo '<td>'.$i['lesson_name'].'</td>';
			echo '</tr>';
		}
		echo '</table>';
		*/
		
		//echo 'Всего: '.count($totalForUpdated);
		/*
		if(count($totalForUpdated)){
			$count = 0;
			foreach($totalForUpdated as $i){			
				$count++;
				$lessonId = $i['lesson_id'];
				$lesson = $this->getService('Lesson')->getOne($this->getService('Lesson')->find($lessonId));
				if($lesson){
				#################
					//--почти точная копия ф-ции $this->getService('LessonAssign')->onLessonStart($lesson), но без привязки к текущему пользователю
					$userId = $i['MID'];
					if (
						($lesson->isfree == HM_Lesson_LessonModel::MODE_PLAN) &&	
						$lesson->vedomost
					) {
						$score = $lesson->onStart();

						if ($score !== false) {
							$this->getService('LessonAssign')->setUserScore($userId, $lesson->SHEID, $score, 0, true);
						}

					} elseif ($lesson->isfree == HM_Lesson_LessonModel::MODE_FREE) {

						// это для страницы "статистика изучения свободных материалов"
						$this->getService('LessonAssign')->updateWhere(array(
							'V_DONE' => HM_Lesson_Assign_AssignModel::PROGRESS_STATUS_INPROCESS,
							'launched' => date('Y-m-d H:i:s'),
						),
						array(
							'SHEID = ?'  => $lesson->SHEID,
							'MID = ?'    => $userId,
							'V_DONE = ?' => HM_Lesson_Assign_AssignModel::PROGRESS_STATUS_NOSTART
						)
					);
					}
				}
				if($count == 100){
					//break ;
				}
				#################			
			}
			echo 'Завершено';
						
		} else {
			echo 'Нет записей';
		}
		*/
		
		
		
		exit();
		
		} catch (Exception $e) {
			//echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
		}
		
    }
   

}