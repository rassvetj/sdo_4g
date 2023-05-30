<?php
class Infoblock_RandomSubjectsController extends HM_Controller_Action
{
	public function init()
	{
		parent::init();
		$this->_helper->ContextSwitch()->addActionContext('next', 'json')->initContext();
	}

	public function nextAction(){
		
		$this->view->result = true;
		$user_id = $this->getService('User')->getCurrentUserId();		
		$classifier_type = 1; //HM_View_Infoblock_RandomSubjects::CLASSIFIER_TYPE
		$count = 20; //HM_View_Infoblock_RandomSubjects::COUNT_ITEM
		
		$this->session = new Zend_Session_Namespace('infoblock_random_subjects');
		$subjects = (is_array($this->session->subjects)) ? $this->session->subjects : array();
		
		if (!count($subjects)) {
            if ($user_id) {
                $relevant_subjects = $this->getService('ClassifierLink')->getRelevantSubjectsForUser((int) $user_id, $classifier_type);
            } else {
                $relevant_subjects = array();
            }
            
            $free_subjects = (count($relevant_subjects) < $count) ? $this->getService('Subject')->getFreeSubjects($count-count($relevant_subjects), $user_id) : array();
            $subjects = array_merge($relevant_subjects, $free_subjects);
		}
		
		$subject_id = array_shift($subjects);
		if ($subject_id) {
            $subjects[] = $subject_id;
		}
		$subject = $this->getService('Subject')->getOne($this->getService('Subject')->find($subject_id));
		$this->session->subjects = $subjects;
		
		$this->view->id = $subject_id;
		$this->view->title = iconv(Zend_Registry::get('config')->charset, 'UTF-8', $subject->name);
		$this->view->description = iconv(Zend_Registry::get('config')->charset, 'UTF-8', $subject->description);
		$this->view->url = '<a href="'.$this->view->url(array(
											'module' => 'user', 
											'controller' => 'reg', 
											'action' => 'subject', 
											'subid' => $subject->subid
											)).'">'.
											iconv(Zend_Registry::get('config')->charset, 'UTF-8', _('подать заявку'))
											.'</a>';
		
	}

}