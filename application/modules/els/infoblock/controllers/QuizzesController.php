<?php
class Infoblock_QuizzesController extends HM_Controller_Action
{
	public function init()
	{
		parent::init();
		$this->_helper->ContextSwitch()->addActionContext('get-questions', 'json')->initContext();
		$this->_helper->ContextSwitch()->addActionContext('answer', 'json')->initContext();
		$this->_helper->ContextSwitch()->addActionContext('many-answer', 'json')->initContext();
	}

	public function editAction()
	{
		$form = new HM_Form_QuizSettings();

		if (!$quizId = $this->_getParam('quiz_id')) {
			$select = $this->getService('Infoblock')->getSelect();
	        $select->from(array('i' => 'interface'), array('param_id'))
		        ->where(new Zend_Db_Expr($this->getService('Infoblock')->quoteInto('block = ?', 'quizzesBlock')))
	        	->limit(1);

	        if ($rowset = $select->query()->fetchAll()) {
	        	if (!empty($rowset[0]['param_id'])) {
	        		$params = unserialize($rowset[0]['param_id']);
	        		$quizId = $params['quiz_id'];
	        	}
	        }
		}

		if (!empty($quizId)) {
			$questions = $this->_getQuestions($quizId);
			$element = $form->getElement('question_id');
			$element->addMultiOptions($questions);
		}

		if (isset($params) && !empty($params['quiz_id']) && !empty($params['question_id'])) {
			$form->setDefaults($params);
		}

		$request = $this->getRequest();
		if ($request->isPost() && $form->isValid($request->getPost())) {
			$param = array(
				'quiz_id'		=> $this->_getParam('quiz_id'),
				'question_id'	=> $this->_getParam('question_id'),
			);

			$this->getService('Infoblock')->updateWhere(array('param_id' => serialize($param)), array('block = ?' => 'quizzesBlock')); // todo: what about multiple quizzes..?
			$this->_flashMessenger->addMessage(_('Настройки блока успешно сохранены'));
			$this->_redirector->gotoSimple('index', 'index', 'default');
		}

		$this->view->form = $form;
        $this->view->headScript()->appendFile(Zend_Registry::get('config')->url->base.'js/infoblocks/quizzes/quiz-settings.js');
	}
	
	public function manyEditAction()
	{
		$form = new HM_Form_QuizSettings();
		$form->removeElement('question_id');

		if (!$quizId = $this->_getParam('quiz_id')) {
			$select = $this->getService('Infoblock')->getSelect();
	        $select->from(array('i' => 'interface'), array('param_id'))
		        ->where(new Zend_Db_Expr($this->getService('Infoblock')->quoteInto('block = ?', 'manyQuizzesBlock')))
	        	->limit(1);

	        if ($rowset = $select->query()->fetchAll()) {
	        	if (!empty($rowset[0]['param_id'])) {
	        		$params = unserialize($rowset[0]['param_id']);
	        		$quizId = $params['quiz_id'];
	        	}
	        }
		}

		if (isset($params) && !empty($params['quiz_id']) && !empty($params['question_id'])) {
			$form->setDefaults($params);
		}

		$request = $this->getRequest();
		if ($request->isPost() && $form->isValid($request->getPost())) {
			$param = array(
				'quiz_id'		=> $this->_getParam('quiz_id'),
			);

			$this->getService('Infoblock')->updateWhere(array('param_id' => serialize($param)), array('block = ?' => 'manyQuizzesBlock')); // todo: what about multiple quizzes..?
			$this->_flashMessenger->addMessage(_('Настройки блока успешно сохранены'));
			$this->_redirector->gotoSimple('index', 'index', 'default');
		}

		$this->view->form = $form;
        //$this->view->headScript()->appendFile(Zend_Registry::get('config')->url->base.'js/infoblocks/quizzes/quiz-settings.js');
	}
	
	
	

	public function answerAction()
	{
		$answer = $this->_getParam('answer');
		$data = array(
			'user_id' 		=> $this->getService('User')->getCurrentUserId(),
			'quiz_id'		=> $this->_getParam('quiz_id'),
			'question_id' 	=> "0-{$this->_getParam('question_id')}",
		);
		if (count($answer)) {
			foreach ($answer as $value) {
				$data['answer_id'] = (int)$value;
                $data['junior_id'] = 0;
		        $this->getService('PollResult')->insert($data);
			}
			$this->view->result = true;
		} else {
            $this->view->result = false;
            $this->view->msg = "Выберите вариант ответа";
		}

        
	}
	
	
	public function manyAnswerAction()
	{
		$answers = $this->_getParam('answer');
		
		foreach($answers as $kod => $answer){
		    if(!count($answer)){
		        $this->view->result = false;
                $this->view->msg = "Выберите вариант ответа";
                return;
		    }
		}
		
		foreach($answers as $kod => $answer){
    		$data = array(
    			'user_id' 		=> $this->getService('User')->getCurrentUserId(),
    			'quiz_id'		=> $this->_getParam('quiz_id'),
    			'question_id' 	=> $kod,
    		);
    		if (count($answer)) {
    			foreach ($answer as $value) {
    				$data['answer_id'] = (int)$value;
                    $data['junior_id'] = 0;
    		        $this->getService('PollResult')->insert($data);
    			}
    			$this->view->result = true;
    		}
		}
	}
	

	public function getQuestionsAction()
	{
		if ($quizId = $this->_getParam('quiz_id')) {
			$questions = $this->_getQuestions($quizId);
	        foreach ($questions as &$value) {
	        	$value = iconv(Zend_Registry::get('config')->charset, 'UTF-8', $value);
	        }
	        $this->view->questions = $questions;
		}
	}

	private function _getQuestions($quizId)
	{
		$questions = array(_('Выберите вопрос'));
        $quiz = $this->getOne($this->getService('Poll')->find($quizId));

        $ids = explode(HM_Poll_PollModel::QUESTION_SEPARATOR, $quiz->data);
        $select = $this->getService('Test')->getSelect();
        $select->from(array('t' => 'list'),array('kod'  => 't.kod',
                                                'qdata' => 't.qdata',
                                			    'qtype'  => 't.qtype')
                     )
       	->where("t.qtype IN (1,2)")
       	->where("t.kod IN (?)", $ids);

       	if ($rowset = $select->query()->fetchAll()) {
			foreach ($rowset as $row) {
				$questions[$this->updateKod($row['kod'])] = $this->updateQdata($row['qdata']);
			}
        }
		return $questions;
	}

    public function updateQdata($field){
        list($str) =explode(HM_Test_Abstract_AbstractModel::QUESTION_SEPARATOR, $field);
        if(strlen($srt) > $this->testNameLen){
            $str = substr($str, 0, $this->testNameLen);
        }
        return $str;
    }

    public function updateKod($field){
        return (int)str_replace('0-', '', $field);
    }

    protected function _initChart()
    {
        $this->_helper->layout()->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
		$chartType = $this->getRequest()->getControllerName();

        $title = $chartType . '_' . date('Y-m-d_H-i');
		$this->_helper->ContextSwitch()->addContext(
            'csv',
            array(
                'suffix' => 'csv',
                'headers' => array(
                	'Content-Type' => 'text/csv',
	                'Content-Disposition' => "attachment; filename=\"{$title}.csv\"",
            	),
            )
        );
        header('Pragma: cache'); // интеллигентныый способ не работает.(
		$this->_helper->ContextSwitch()->addActionContext('get-settings', 'xml')->initContext();
		$this->_helper->ContextSwitch()->addActionContext('get-data', array('xml', 'csv'))->initContext();
    }

	public function getDataAction()
	{
		$this->_initChart();
		$this->session = new Zend_Session_Namespace('infoblock_quizzes');


		
		if (($questionId = $this->session->questionId) || $this->_getParam('kod', '') != '') {
		    
		    if($this->_getParam('kod', '') != ''){
		        $questionId = $this->_getParam('kod', '');
		    }else{
		        $questionId = "0-" . $questionId;
		    }
		    
			if ($question = $this->getService('Question')->getOne($this->getService('Question')->find($questionId))) {


		        $qdata =explode(HM_Test_Abstract_AbstractModel::QUESTION_SEPARATOR, $question->qdata);
		        array_shift($qdata);
		        $answers = array();
		        while(is_array($qdata) && count($qdata)) {
		        	$answers[array_shift($qdata)] = array_shift($qdata);
		        }

				$service = $this->getService('PollResult');
		        $select = $service->getSelect();
				$data = array();

		        $select = $service->getSelect();
		        $select->from(array('qr' => 'quizzes_results'), array('answer_id', 'cnt' => new Zend_Db_Expr('COUNT(answer_id)')))
			        ->where('question_id = ?', $questionId)
			        ->where('lesson_id = 0  OR lesson_id IS NULL')
			        ->group('answer_id')
			        ->order(new Zend_Db_Expr('COUNT(answer_id) DESC'));
			    if ($rowset = $select->query()->fetchAll()) {

			    	foreach ($rowset as $row) {
				        $data[$answers[$row['answer_id']]] = $row['cnt'];
			    	}
			    }
		    }
	    }
		$this->view->data = $data;
	}

	public function getSettingsAction()
	{
		$this->_initChart();
	}

}