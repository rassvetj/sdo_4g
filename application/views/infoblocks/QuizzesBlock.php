<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';

class HM_View_Infoblock_QuizzesBlock extends HM_View_Infoblock_ScreenForm
{
	protected $id = 'quizzes';
    protected $session;

	/**
	 * @todo Избавиться от лишних запросов к БД;
	 * похоже нужно менять структуру interface, list,
	 */
	public function quizzesBlock($title = null, $attribs = null, $options = null)
	{		
		$this->session = new Zend_Session_Namespace('infoblock_quizzes');
		$serviceContainer = Zend_Registry::get('serviceContainer');

		$role = $serviceContainer->getService('User')->getCurrentUserRole();
		$userId = $serviceContainer->getService('User')->getCurrentUserId();
//		$isModerator = (($role == HM_Role_RoleModelAbstract::ROLE_DEAN) || ($role == HM_Role_RoleModelAbstract::ROLE_ADMIN) || ($role == HM_Role_RoleModelAbstract::ROLE_MANAGER)); // todo: использовать абстрактный метод получения модераторов на уровне портала
		$isModerator = $serviceContainer->getService('News')->isUserActivityPotentialModerator($userId);

		$select = $serviceContainer->getService('Infoblock')->getSelect();
        $select->from(array('i' => 'interface'), array('param_id'))
	        ->where(new Zend_Db_Expr($serviceContainer->getService('Infoblock')->quoteInto('block = ?', 'quizzesBlock')))
        	->limit(1);

        if ($rowset = $select->query()->fetchAll()) {
        	if (!empty($rowset[0]['param_id'])) {
        		$params = unserialize($rowset[0]['param_id']);
        		$quizId = $params['quiz_id'];
        		$questionId = $params['question_id'];

        		if ($questionId) {
        			if ($question = $serviceContainer->getService('Question')->getOne($serviceContainer->getService('Question')->find("0-{$questionId}"))) {

        				$this->session->questionId = $questionId;

				        $qdata =explode(HM_Test_Abstract_AbstractModel::QUESTION_SEPARATOR, $question->qdata);
				        $this->view->question = array_shift($qdata);
				        $answers = array();
				        while(is_array($qdata) && count($qdata)) {
				        	$answers[array_shift($qdata)] = array_shift($qdata);
				        }
				        $this->view->answers = $answers;
				        $this->view->type = $question->qtype;
				        $this->view->quizId = $quizId;
				        $this->view->questionId = $questionId;
				        $this->view->enabled = true;

					    $userAnswers = array();
				        if ($userId) {
					        $service = $serviceContainer->getService('PollResult');
					        $select = $service->getSelect();
					        $select->from(array('qr' => 'quizzes_results'), array('answer_id'))
					        	->where('user_id = ?', $userId)
						        ->where('question_id = ?', "0-{$questionId}")
						        ->where('lesson_id IS NULL');

						    if ($rowset = $select->query()->fetchAll()) {
						    	foreach ($rowset as $row) {
							        $userAnswers[] = $row['answer_id'];
						    	}
						    }
					    }
					    $this->view->answersDisabled = count($userAnswers) || (($quizId == $_COOKIE['quizzes-quiz-id']) && ($questionId == $_COOKIE['quizzes-question-id']));
					    $this->view->resultsEnabled = $this->view->answersDisabled || $isModerator;
				    	$this->view->userAnswers = $userAnswers;
	        		}
        		}        		
        		else{
        			$this->view->enabled = false;
        		}
        	}
        }

    	$this->view->isModerator = $isModerator; // todo

    	$content = $this->view->render('quizzesBlock.tpl');

		$this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/infoblocks/quizzes/style.css');
		$this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/test.css');
        $this->view->headScript()->appendFile(Zend_Registry::get('config')->url->base.'js/lib/jquery/jquery.checkbox.js');
        $this->view->headScript()->appendFile(Zend_Registry::get('config')->url->base.'js/lib/jquery/jquery.cookie.js');
        $this->view->headScript()->appendFile(Zend_Registry::get('config')->url->base.'js/infoblocks/quizzes/script.js');

		if ($title == null) return $content;
		return parent::screenForm($title, $content, $attribs);
	}
}