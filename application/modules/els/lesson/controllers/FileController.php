<?php
class Lesson_FileController extends HM_Controller_Action_Subject
{
	protected $_zip = null; # ZipArchive class
	
	public function init(){
		
		$this->lesson_id 	= (int) $this->_getParam('lesson_id', 0);
		$this->subject_id	= (int) $this->_getParam('subject_id', 0);
		
		if( !$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TUTOR)
			&&
			!$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TEACHER)
		){
			$this->_helper->getHelper('FlashMessenger')->addMessage(array(
				'type'		=> HM_Notification_NotificationModel::TYPE_ERROR, 
				'message'	=> _('У Вас нет прав к этому разделу или Вы не назначены на выбраное занятие.')
			));		
			$this->_helper->redirector->gotoSimple('index', 'list', 'lesson', array('subject_id' => $this->subject_id));
		}
		
		
		if(empty($this->lesson_id)){
			$this->_helper->getHelper('FlashMessenger')->addMessage(array(
				'type'		=> HM_Notification_NotificationModel::TYPE_ERROR, 
				'message'	=> _('Не указано занятие.')
			));			
			$this->_helper->redirector->gotoSimple('index', 'list', 'lesson', array('subject_id' => $this->subject_id));	
		}
		
		parent::init();
	}
	
	public function getAction(){
		
		$serviceAssign 		= $this->getService('LessonAssign');
		$students 			= $serviceAssign->fetchAll($serviceAssign->quoteInto(array('SHEID = ?'), array($this->lesson_id)))->getList('MID');	
		$students 			= array_filter($students);
				
		if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TUTOR)){
			$availableStudents 	= $this->getService('Subject')->getAvailableStudents($this->getService('User')->getCurrentUserId(), $this->subject_id);
			if($availableStudents !== false){
				$students 			= array_intersect($students, $availableStudents);
			}
		}
		
		if(empty($students)){			
			$this->_helper->getHelper('FlashMessenger')->addMessage(array(
				'type'		=> HM_Notification_NotificationModel::TYPE_ERROR, 
				'message'	=> _('В занятии нет назначенных студентов.')
			));			
			$this->_helper->redirector->gotoSimple('index', 'list', 'lesson', array('subject_id' => $this->subject_id));	
		}
		
		$serviceZip			= $this->getService('FilesZip');
		$serviceLesson 		= $this->getService('Lesson');		
		$serviceInterview 	= $this->getService('Interview');
		
		$condition	= $serviceInterview->quoteInto(array('user_id IN (?)', ' AND lesson_id = ?'), array($students, $this->lesson_id));
		$messages 	= $serviceInterview->fetchAllHybrid('User', 'Files', 'File', $condition, array('interview_id'));
		$lesson 	= $serviceLesson->getLesson($this->lesson_id);
		$zipName 	= $lesson->title.'_(от_'.date('d.m.Y_H.i.s').')';
		
		if(!$serviceZip->createZip()){
			$this->_helper->getHelper('FlashMessenger')->addMessage(array(
				'type'		=> HM_Notification_NotificationModel::TYPE_ERROR, 
				'message'	=> _('Не удалось создать архив.')
			));			
			$this->_helper->redirector->gotoSimple('index', 'list', 'lesson', array('subject_id' => $this->subject_id));
		}		
		
		$isEmpty = true;
		foreach($messages as $message){			
			
			if(empty($message->file)){ continue; }				
			$user	= $message->user->current();
			
            if($user->blocked == 1){ continue; }
			
			$fio	= $user->LastName.' '.$user->FirstName.' '.$user->Patronymic;
			
			foreach($message->file as $file){
				$fileName	= $fio.'/'.$file->name;				
				if(!$serviceZip->addFileToZip($fileName, $file)){ continue; }				
				$isEmpty = false;				
			}
		}
        unset($messages);
		
		$zipPath = $serviceZip->getZipPath();
		$serviceZip->close();		
		
		if(!$isEmpty){
			$serviceZip->sendZip($zipPath, $zipName);			
		}
			
		$this->_helper->getHelper('FlashMessenger')->addMessage(array(
			'type'		=> HM_Notification_NotificationModel::TYPE_ERROR, 
			'message'	=> _('В занятии нет прикрепленных файлов.')
		));			
		$this->_helper->redirector->gotoSimple('index', 'list', 'lesson', array('subject_id' => $this->subject_id));
		die;
		
	}
	
}