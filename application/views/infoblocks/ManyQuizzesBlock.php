<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';

class HM_View_Infoblock_ManyQuizzesBlock extends HM_View_Infoblock_ScreenForm
{
	protected $id = 'many-quizzes';
    protected $session;

	public function manyQuizzesBlock($title = null, $attribs = null, $options = null)
	{
		$this->session = new Zend_Session_Namespace('infoblock_many_quizzes');
		$serviceContainer = Zend_Registry::get('serviceContainer');
        $this->session->questionId = array();
		$role = $serviceContainer->getService('User')->getCurrentUserRole();
		$userId = $serviceContainer->getService('User')->getCurrentUserId();
//		$isModerator = (($role == HM_Role_RoleModelAbstract::ROLE_DEAN) || ($role == HM_Role_RoleModelAbstract::ROLE_ADMIN) || ($role == HM_Role_RoleModelAbstract::ROLE_MANAGER)); // todo: использовать абстрактный метод получения модераторов на уровне портала
		$isModerator = $serviceContainer->getService('News')->isUserActivityPotentialModerator($userId);

		$this->view->isModerator = $isModerator; // todo
		$select = $this->getService('Test')->getSelect();
		$select->from(array('i' => 'interface'), array('param_id'))
	        ->where(new Zend_Db_Expr($serviceContainer->getService('Infoblock')->quoteInto('block = ?', 'manyQuizzesBlock')))
        	->limit(1);
		
        if ($rowset = $select->query()->fetchAll()) {
        	if (!empty($rowset[0]['param_id'])) {
        		$params = unserialize($rowset[0]['param_id']);
        		$quizId = $params['quiz_id'];
        		$questionId = $params['question_id'];
        	}else{
        		$this->view->enabled = false;
        	    $content = $this->view->render('manyQuizzesBlock.tpl');
        	    return parent::screenForm($title, $content, $attribs);
        	}
        }
		
		$quiz = $this->getOne($this->getService('Poll')->find($quizId));
		$ids = explode(HM_Poll_PollModel::QUESTION_SEPARATOR, $quiz->data);

        if ($questions = $this->getService('Question')->fetchAll(array("kod IN (?)" => $ids))) {
		    $this->view->questions = $questions;
		} else {
			$this->view->questions = array();
		}
        //pr($questions); die();
        $this->view->question = array();
        $this->view->answers = array();
        $userAnswers = $resultsEnabled = $answersDisabled = array();
        
        $cookieQuizIds = isset($_COOKIE['many-quizzes-quiz-id']) ? Zend_Json::decode($_COOKIE['many-quizzes-quiz-id']) : array();
        $cookieQuestionIds = isset($_COOKIE['many-quizzes-question-id']) ? Zend_Json::decode($_COOKIE['many-quizzes-question-id']) : array();
        
		foreach($questions as $question){
			$this->session->questionId[] = $question->kod;

	        $qdata = explode(HM_Test_Abstract_AbstractModel::QUESTION_SEPARATOR, $question->qdata);
	        $name = array_shift($qdata);
	        $this->view->question[$question->kod] = $name;
	        $answers = array();
	        while(is_array($qdata) && count($qdata)) {
	        	$answers[array_shift($qdata)] = array_shift($qdata);
	        }
	        $this->view->answers[$question->kod] = $answers;
	        $this->view->type[$question->kod] = $question->qtype;
	        $this->view->quizId = $quizId;
	        $this->view->questionId[$question->kod] = $questionId;
	        $this->view->enabled = true;

		    
	        if ($userId) {
		        $service = $serviceContainer->getService('PollResult');
		        $select = $service->getSelect();
		        $select->from(array('qr' => 'quizzes_results'), array('answer_id'))
		        	->where('user_id = ?', $userId)
			        ->where('question_id = ?', $question->kod)
			        ->where('(lesson_id = 0 OR lesson_id IS NULL)');
		        $userAnswers[$question->kod] = array();
			    if ($rowset = $select->query()->fetchAll()) {
			        
			    	foreach ($rowset as $row) {
				        $userAnswers[$question->kod][] = $row['answer_id'];
			    	}
			    }
		    }
		    
		    $answersDisabled[$question->kod] = count($userAnswers[$question->kod]) || (in_array($quizId, $cookieQuizIds) && in_array($question->kod, $cookieQuestionIds));
		    $resultsEnabled[$question->kod] = $answersDisabled[$question->kod] || $isModerator;
		}
		
		$this->view->resultsEnabled = $resultsEnabled;
		$this->view->answersDisabled = $answersDisabled;
		$this->view->userAnswers = $userAnswers;

    	$content = $this->view->render('manyQuizzesBlock.tpl');

		$this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/infoblocks/many-quizzes/style.css');
		$this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/test.css');
        $this->view->headScript()->appendFile(Zend_Registry::get('config')->url->base.'js/lib/jquery/jquery.checkbox.js');
        $this->view->headScript()->appendFile(Zend_Registry::get('config')->url->base.'js/lib/jquery/jquery.cookie.js');
        $this->view->headScript()->appendFile(Zend_Registry::get('config')->url->base.'js/infoblocks/many-quizzes/script.js');

		if ($title == null) return $content;
		return parent::screenForm($title, $content, $attribs);
	}
}