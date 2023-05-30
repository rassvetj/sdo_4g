<?php
class Survey_SingleController extends HM_Controller_Action_Crud
{
    protected $_user 	= NULL; # текущий юзер
    private $_type_id	= NULL; # тип тестирования
   
	
	public function init(){	
		
		parent::init();	
		
		$request		= $this->getRequest();
		$this->_type_id = (int)$request->getParam('type', false);	
		$typeList = HM_Survey_SurveyModel::getAllTypes();
		if(!isset($typeList[$this->_type_id])){
			$this->_flashMessenger->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
					  'message' => _('Анкетирование не найдено'))
			);			
			$this->_redirect('/');
		}
		
		if($this->getRequest()->getActionName() != 'finish'){
		
			$this->_user   = $this->getService('User')->getCurrentUser();	
			$result = $this->getService('Survey')->getOne($this->getService('Survey')->fetchAll($this->getService('Survey')->quoteInto(
									array('mid_external = ?', ' AND type = ?'),
									array($this->_user->mid_external, $this->_type_id)
					  )));
			if(!empty($result->survey_id)){
				$this->_flashMessenger->addMessage(
					array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
						  'message' => _('Вы уже проходили это анкетирование'))
				);			
				$this->_redirect('/');			
			}
		}
		
		
			
	}
	
	
	public function indexAction()
    {			
		$this->view->headLink()->appendStylesheet($config->url->base.'/css/rgsu_style.css');
		$isStart = (int)$this->getRequest()->getParam('start', false);
		$all_types	 = HM_Survey_SurveyModel::getAllTypes();
		
		$this->view->type_name		= $all_types[$this->_type_id];
		
		if($this->_type_id == HM_Survey_SurveyModel::TYPE_WHO_WORK){
			$rules_tpl_name = '_who_work';
		} elseif($this->_type_id == HM_Survey_SurveyModel::TYPE_PROF_MOTIVE){
			$rules_tpl_name = '_prof_motive';
		} elseif($this->_type_id == HM_Survey_SurveyModel::TYPE_PROF_FUTURE){
			$rules_tpl_name = '_prof_future';
		} elseif($this->_type_id == HM_Survey_SurveyModel::TYPE_EXPRESS_DIAG){
			$rules_tpl_name = '_express_diag';		
		} elseif($this->_type_id == HM_Survey_SurveyModel::TYPE_PROF_SELF){
			$rules_tpl_name = '_prof_self';
		}
		
		
		try {
			$this->view->rules = $this->view->render('single/rules/'.$rules_tpl_name.'.tpl');
		} catch (Exception $e) {
			$this->_flashMessenger->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
					  'message' => _('Нет данных'))
			);			
			$this->_redirect('/');
		}
		
		
		if($isStart == 1){
		
			# находим список вопросов и выводим их по очереди, сохраняя в сессии результат
			$first_question = $this->getService('SurveyQuestions')->getFirst($this->_type_id);		
			$first_q = $first_question->question_id; # первый вопрос.
			if(empty($first_q)){
				$this->_flashMessenger->addMessage(
					array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
						  'message' => _('Не найдено ни одного вопроса'))
				);			
				$this->_redirect('/');
				
			}		
			$this->_redirector->gotoSimple('process', 'single', 'survey', array('type' => $this->_type_id, 'q' => $first_q));		
		}
    }
	
	/**
	 * тест идет
	*/
	public function processAction()
    {			
		$this->view->headLink()->appendStylesheet($config->url->base.'/css/rgsu_style.css');
		$all_types	 = HM_Survey_SurveyModel::getAllTypes();
		
		$this->view->type_name		= $all_types[$this->_type_id];
		$this->view->form = new HM_Form_Single();
    }
	
	
	public function finishAction(){
		
		$this->view->headLink()->appendStylesheet($config->url->base.'/css/rgsu_style.css');
		
		$res = $this->getService('Survey')->getResultByType($this->_type_id);
		if(!$res){
			$this->_redirect('/');
		}
				
		$all_types	 = HM_Survey_SurveyModel::getAllTypes();		
		$raw_data	 = json_decode($res->data_details, true);
		$total_point = $raw_data['result']['total_point'];
		
		if($this->_type_id == HM_Survey_SurveyModel::TYPE_PROF_MOTIVE){			
			arsort($total_point);
			$this->view->prof_motive_groups = HM_Survey_SurveyModel::getProfMotiveList();			
		
		} elseif($this->_type_id == HM_Survey_SurveyModel::TYPE_PROF_FUTURE){	
			$this->view->headScript()->appendFile('https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.min.js');
			ksort($total_point);
			$this->view->prof_future_groups = HM_Survey_SurveyModel::getProfFutureGroups();			
		
		} elseif($this->_type_id == HM_Survey_SurveyModel::TYPE_EXPRESS_DIAG){
			$this->view->headScript()->appendFile('https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.min.js');	
		
		} elseif($this->_type_id == HM_Survey_SurveyModel::TYPE_PROF_SELF){
			$this->view->headScript()->appendFile('https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.min.js');
			$this->view->prof_self_groups = HM_Survey_SurveyModel::getProfSelfList();			
		}

		
		
		
		#$total_point = 0;
		$this->view->total_point	= $total_point;
		#$questions	= $this->getService('SurveyQuestions')->getAllByType($this->_type_id);
		/*
		$result = array();
		if(!empty($questions)){
			foreach($questions as $q){
				$result[$q->question_id] = array(
					'question_name' => $q->name,
					'answer_name' 	=> $raw_data[$q->question_id]['answer_name'],
				);
			}
		}
		*/
		#$this->view->result 		= $result;		
		$tpl_name = false;
		switch ($this->_type_id) {
			case HM_Survey_SurveyModel::TYPE_WHO_WORK :
				$tpl_name = '_who_work' ;
				break;	
			case HM_Survey_SurveyModel::TYPE_PROF_MOTIVE :
				$tpl_name = '_prof_motive' ;
				break;	
			case HM_Survey_SurveyModel::TYPE_PROF_FUTURE :
				$tpl_name = '_prof_future' ;
				break;	
			case HM_Survey_SurveyModel::TYPE_EXPRESS_DIAG :
				$tpl_name = '_express_diag' ;
				break;
			case HM_Survey_SurveyModel::TYPE_PROF_SELF :
				$tpl_name = '_prof_self' ;
				break;				
		}
		$sub_tpl_name = HM_Survey_SurveyModel::getTpl($this->_type_id, $total_point);
		
		
		try {			
			$this->view->result_text	= $this->view->render('single/result/'.$tpl_name.'/'.$sub_tpl_name.'.tpl');
			$this->view->result 		= $this->view->render('single/result/'.$tpl_name.'.tpl');
		} catch (Exception $e) {
			#echo 'Выброшено исключение: ',  $e->getMessage(), "\n";			
		}
		
		
		$this->view->date_finish 	= date('d.m.Y в H:i', strtotime($res->DateCreated));
		$this->view->type_name		= $all_types[$res->type];
	}
	
	
	
	
	public function saveAction(){		
	
		$this->getHelper('viewRenderer')->setNoRender();
		$key 		 	= $this->_user->MID.'~'.$this->_type_id; 
		
		
		$currentData 		 		= $this->getService('SurveyQuestions')->getSessionData(); # результат предыдущих ответов
		$data 				 		= (empty($currentData)) ? array() : $currentData; 
		$totalQuestions 	 		= (int) $this->getService('SurveyQuestions')->getCount($this->_type_id); # всего вопросов
		$data[$key]['total'] 		= $totalQuestions;
		$data[$key]['type_id'] 		= $this->_type_id;
		$data[$key]['mid_external']	= $this->_user->mid_external;
		# предпоследний вопрос. Изменить имя кнопки на - закончить анкетирование
		if($totalQuestions == count($data[$key]['answers'])+1){
			$data[$key]['isLast'] = 1;
		}
		
		
		$request		= $this->getRequest();
		$allParams 		= $request->getParams();
		$qestion_id		= (int)$request->getParam('q', false);
		$answerList 	= $this->getService('SurveyAnswers')->getAnswerList($this->_type_id);
		$otherAnswers	= HM_Survey_Answers_AnswersModel::otherAnswers();
		
		
		$result = array();
		foreach($allParams as $name_field => $v){		
			$variants = $answerList[$name_field];
			
			if($variants === NULL){ # не является полем вопроса
				continue;
			} elseif(empty($variants)){ # текстовое поле				
				$data[$key]['answers'][$qestion_id]['question_code']	= $name_field;
				$data[$key]['answers'][$qestion_id]['answer_name'] 		= $v;
			} elseif(!empty($variants)) { # поле radio
				$data[$key]['answers'][$qestion_id]['question_code'] 	= $name_field;
				if(in_array($v, $otherAnswers)){					
					$data[$key]['answers'][$qestion_id]['answer_name']	= $allParams[$name_field.'_other'];
					$data[$key]['answers'][$qestion_id]['answer_id']	= $v;
				} else {					
					$data[$key]['answers'][$qestion_id]['answer_name']	= $answerList[$name_field][$v];
					$data[$key]['answers'][$qestion_id]['answer_id']	= $v;
				}
			}
		}

		$this->getService('SurveyQuestions')->saveSessionData($data);
					
		# Вопросы закончились, сохраняем в БД и редирект на страницу успеха
		if($totalQuestions == count($data[$key]['answers'])){
			$isSave = $this->getService('Survey')->saveSessionData($data[$key]);
			
			$this->getService('SurveyQuestions')->clearSessionData();
			if(!$isSave && $this->_type_id == HM_Survey_SurveyModel::TYPE_PROF_FUTURE){
				$this->_flashMessenger->addMessage(
					array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
						  'message' => _('Результаты тестирования некорректны, необходимо пройти тест ещё раз'))
				);				
				$this->_redirector->gotoSimple('index', 'single', 'survey', array('type' => $this->_type_id));
			}			
			$this->_redirector->gotoSimple('finish', 'single', 'survey', array('type' => $this->_type_id));
			
		}
		$skip_questions = (!empty($data[$key]['answers'])) ? array_keys($data[$key]['answers']) : array();
		$next_question 	= $this->getService('SurveyQuestions')->getNext($this->_type_id, $skip_questions);
		
		$this->_redirector->gotoSimple('process', 'single', 'survey', array('type' => $this->_type_id, 'q' => $next_question->question_id));
	}
   
}