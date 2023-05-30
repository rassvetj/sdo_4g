<?php 
# Отображаются данные только занятий с типом "Задание".
# Позже м.б. и другие типы.

class Lesson_PastController extends HM_Controller_Action
{
	private $lesson_id  = null;
	private $lesson		= null;
	private $subject_id = null;
	private $user_id 	= null;

	
    public function init()
    {
        parent::init();
		
		$this->lesson_id 	= (int) $this->_getParam('lesson_id');
        $this->subject_id 	= (int) $this->_getParam('subject_id');
		
		$this->lesson 		= $this->getService('Lesson')->getOne($this->getService('Lesson')->find($this->lesson_id));
		
		if ($this->lesson->typeID != HM_Event_EventModel::TYPE_TASK){
			$this->_flashMessenger->addMessage(array('message' => _('Время обучения на курсе закончилось.'), 					'type' => HM_Notification_NotificationModel::TYPE_ERROR));
			$this->_flashMessenger->addMessage(array('message' => _('Доступны для просмотра только занятия с типом "Задание"'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
			$this->_redirector->gotoUrl('/');						
		} 	

		$this->user_id		= $this->getService('User')->getCurrentUserId();
		
    }
	
	public function indexAction()
	{
		
		if ($this->lesson->typeID == HM_Event_EventModel::TYPE_TASK){ 
			$this->taskAction();
		}	
		return;
	}
	
	public function taskAction()
	{
		$this->view->setHeader(_('Результат занятия').' "'.$this->lesson->title.'"');
		
		$condition 					= array();       
        $condition['lesson_id = ?'] = $this->lesson_id;
		$condition[] 				= '(user_id = ' . $this->user_id .' OR to_whom = ' . $this->user_id . ')';
		
		$messages 	= $this->getService('Interview')->fetchAllHybrid('User', 'Files', 'File', $condition, array('interview_id'));
		$mark 		= $this->getService('LessonAssign')->getOne($this->getService('LessonAssign')->fetchAll(array('MID = ?' => $this->user_id, "SHEID = ?" => $this->lesson->SHEID)));
		
		if ($mark->V_STATUS != HM_Scale_Value_ValueModel::VALUE_NA) { $this->view->mark = $mark->V_STATUS;	}
		else 														{ $this->view->mark = ""; 				}
				
		$this->view->messages = $messages;

		$this->render('task');
	}
}