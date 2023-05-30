<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';
class HM_View_Infoblock_SurveyGraduate extends HM_View_Infoblock_ScreenForm
{                                          
  protected $id = 'surveyGraduate';
  protected $class = 'scrollable';
  
  public function surveyGraduate($title = null, $attribs = null, $options = null)
  {
    
	$this->_userService = $this->getService('User');		
	$user = $this->_userService->getCurrentUser();		
	
	$serviceContainer = Zend_Registry::get('serviceContainer');
    $this->id = strtolower(substr(str_replace('HM_View_Infoblock_', '', get_class($this)), 0, 1)).substr(str_replace('HM_View_Infoblock_', '', get_class($this)), 1);
    ###########################	
	
	
	# группа студента
	
	$isGraduate = 	$this->getService('StudyGroup')->isGraduate($user->MID);
	$result		= 	$this->getService('Survey')->getOne($this->getService('Survey')->fetchAll($this->getService('Survey')->quoteInto(
								array('mid_external = ?', ' AND type IN (?)'),
								array($user->mid_external, array(HM_Survey_SurveyModel::TYPE_SPO,HM_Survey_SurveyModel::TYPE_VPO))
					)));
	if(!$isGraduate || $result->survey_id){
		$title = '&nbsp;';
		$content = '
			<style>
				#'.$this->id.' { display:none; }
			</style>';			
	} else {	
		$title = 'Анкетирование выпускников';
		$content = $this->view->render('surveyGraduate.tpl');
		# принудительный переход на страницу прохождения анкеты.
		$this->_flashMessenger	= Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
		$this->_redirector		= Zend_Controller_Action_HelperBroker::getStaticHelper('ConditionalRedirector');
		
		$this->_flashMessenger->addMessage(
			array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
				  'message' => _('Вам необходимо пройти анкетирование!'))
		);	
 		
		$this->_redirector->gotoSimple('start', 'index', 'survey', array('type' => 'vpo'));
	}	
	
	##########################
    unset($attribs['param']);
    if ($title == null) return $content;
    
    return parent::screenForm($title, $content, $attribs);
    
  }
}