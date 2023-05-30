<?php
class Marksheet_SetController extends HM_Controller_Action
{
  
	/**
	 * Выставляет оценку в занятии, блокируя доступ к прикреплению решения на проверку.
	*/
	public function blockedTaskAction()
    {
		$curent_user_id = $this->getService('User')->getCurrentUserId();
		
		if(!$curent_user_id){
			$this->_flashMessenger->addMessage(_('Необходимо авторизоваться'));
			$this->_redirector->gotoSimple('index', 'index', 'default');
		}
		
		$request 		= $this->getRequest();
		$group_id 		= (int)$request->getParam('group_id', false);
		$subject_id		= $request->getParam('subject_id', false);
		
		#$tmp 			= explode('_', $group_id);
		#$group_id 		= (int)$tmp[1];
		
		if(empty($group_id)){
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type'    => HM_Notification_NotificationModel::TYPE_ERROR,
					  'message' => _('Не задана группа'))
			);
			$this->_redirector->gotoSimple('index', 'index', 'marksheet', array('subject_id' => $subject_id));			
		}
		
		if(empty($subject_id)){
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type'    => HM_Notification_NotificationModel::TYPE_ERROR,
					  'message' => _('Не задана сессия'))
			);
			$this->_redirector->gotoSimple('index', 'index', 'marksheet', array('subject_id' => $subject_id));			
		}
		
		# format array(	student_id => array(lesson_id)	)
		$students_total = $this->getService('Lesson')->getBlockedTaskUsers($subject_id, $curent_user_id, $group_id);
		
		/*
		# получить всех доступных студентов группы для указанного тьютора
		$students_group		= $this->getService('StudyGroup')->getUsers($group_id); # студенты группы		
		$students_subject	= $this->getService('Student')->getUsersIds($subject_id); # студенты, обучающиеся в данный момент на указанной сессии
		$students_available	= $this->getService('Subject')->getAvailableStudents($curent_user_id, $subject_id); # доступные студенты тьютору
			
		$students_total  	= array_intersect($students_group, $students_subject);
		
		# false - значит доступны все студенты
		if($students_available !== false){			
			$students_total = array_intersect($students_total, $students_available);
		}
		*/
		if(empty($students_total)){
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type'    => HM_Notification_NotificationModel::TYPE_ERROR,
					  'message' => _('Нет доступных студентов в выбранной группе'))
			);
			$this->_redirector->gotoSimple('index', 'index', 'marksheet', array('subject_id' => $subject_id));	
		} 
		
		
		# получаем все занятия с типом задание
		/*
		$lesson_ids = $this->getService('Lesson')->fetchAll($this->getService('Lesson')->quoteInto(array('CID = ? AND ', ' typeID = ?'),array($subject_id, HM_Event_EventModel::TYPE_TASK)))->getList('SHEID');		
		if(empty($lesson_ids)){
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type'    => HM_Notification_NotificationModel::TYPE_ERROR,
					  'message' => _('В сессии нет занятий с типом "Задание"'))
			);	
			$this->_redirector->gotoSimple('index', 'index', 'marksheet', array('subject_id' => $subject_id));	
			die;
		}
		*/
			
		/*	
		$exist_ball = array(); # есть оценки в занятиях. student_id => lessons_isd
		$select = $this->getService('User')->getSelect();
		$select->from(array('i' => 'interview'), array(
							'lesson_id' 	=> 'i.lesson_id',
							'student_id'	=> 'i.to_whom',							
		));		
		$select->where('lesson_id IN (?)', $lesson_ids);
		$select->where('to_whom IN (?)', array_keys($students_total));
		$select->where('type = ?', HM_Interview_InterviewModel::MESSAGE_TYPE_BALL);
		$select->group(array('lesson_id', 'to_whom'));
		$res = $select->query()->fetchAll();	
		if(!empty($res)){
			foreach($res as $i){
				$exist_ball[$i['student_id']][$i['lesson_id']] = $i['lesson_id'];
			}
		}
		*/
		
		$date 		  = new HM_Date();
		$insert_count = 0;
		foreach($students_total as $student_id => $lessons){
			foreach($lessons as $lesson_id){
				#if(isset($exist_ball[$student_id][$lesson_id])){ continue; }
				
				$interview_hash = 0;
				$item			= $this->getService('Interview')->getOne($this->getService('Interview')->fetchAll($this->getService('Interview')->quoteInto(array('lesson_id = ? AND ', ' to_whom = ? AND ', ' type = ?'),array($lesson_id, $student_id, HM_Interview_InterviewModel::MESSAGE_TYPE_TASK))));
				$interview_hash = $item->interview_hash;
				if(empty($interview_hash)){
					$interview_hash = mt_rand(999999, 999999999);
				}
				
				$data = array(
					'user_id' 			=> $curent_user_id,
					'to_whom' 			=> $student_id,
					'lesson_id' 		=> $lesson_id,
					'title' 			=> '',
					'question_id' 		=> '',
					'type' 				=> HM_Interview_InterviewModel::MESSAGE_TYPE_BALL,
					'message' 			=> 'Прием материалов завершен',
					'date' 				=> $date->toString(),
					'interview_hash' 	=> $interview_hash,	
					'ball'				=> 0,
				);
				
				$isInseret = $this->getService('Interview')->insert($data);
				if(!$isInseret){
					$this->_helper->getHelper('FlashMessenger')->addMessage(
						array('type'    => HM_Notification_NotificationModel::TYPE_ERROR,
							  'message' => _('Не удалось выставить оценку студенту №'.$student_id.' в занятии №'.$lesson_id))
					);
				} else {
					$insert_count++;
				}				
			}			
		}
		
		if(empty($insert_count)){
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type'    => HM_Notification_NotificationModel::TYPE_NOTICE,
					  'message' => _('Ни одна оценка не была выставлена'))
			);
		} else {
			$this->_helper->getHelper('FlashMessenger')->addMessage(
					array(	'type'    => HM_Notification_NotificationModel::TYPE_SUCCESS,
							'message' => _('Выставлено '.$insert_count.' оценок'))
			);
		}
		$this->_redirector->gotoSimple('index', 'index', 'marksheet', array('subject_id' => $subject_id));	
		die;
		
	}
	

}

