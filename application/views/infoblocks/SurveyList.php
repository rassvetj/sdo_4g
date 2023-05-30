<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';
class HM_View_Infoblock_SurveyList extends HM_View_Infoblock_ScreenForm
{                                          
  protected $id = 'surveyList';
  protected $class = 'scrollable';
  
  # внешние id, для которых показывать список анкетирования
  protected $allowUsers = array(
	'100010112017',
	'10010000',
	'10010001',
	'10010002',
	'10010003',
	'10010004',
	'10010005',
	'10010006',
	'10010007',
	'10010008',
	'10010009'
  );
  
  public function surveyList($title = null, $attribs = null, $options = null)
  {
    
	$this->_userService = $this->getService('User');		
	$user = $this->_userService->getCurrentUser();		
	
	$serviceContainer = Zend_Registry::get('serviceContainer');
    $this->id = strtolower(substr(str_replace('HM_View_Infoblock_', '', get_class($this)), 0, 1)).substr(str_replace('HM_View_Infoblock_', '', get_class($this)), 1);
    ###########################	
	
	
	# группа студента
	
	# получаем список доступных аткетирований.	
	$typeList = HM_Survey_SurveyModel::getAllTypes();
	
	$result		= 	$this->getService('Survey')->fetchAll($this->getService('Survey')->quoteInto(
								array('mid_external = ?', ' AND type IN (?)'),
								array($user->mid_external, array_keys($typeList))
					));
	
	$allowTypes = $typeList; # анкетирования доступные тек. пользователю.
	foreach($result as $i){
		unset($allowTypes[$i->type]);		
	}
	
	if(empty($allowTypes) || !in_array($user->mid_external, $this->allowUsers)){
		$title = '&nbsp;';
		$content = '
			<style>
				#'.$this->id.' { display:none; }
			</style>';			
	} else {
		$this->view->allowTypes = $allowTypes;
		$title = 'Анкетирование';
		$content = $this->view->render('surveyList.tpl');
	
		# принудительный переход на страницу прохождения анкеты.
		/*
		$this->_flashMessenger	= Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
		$this->_redirector		= Zend_Controller_Action_HelperBroker::getStaticHelper('ConditionalRedirector');
		
		$this->_flashMessenger->addMessage(
			array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
				  'message' => _('Вам необходимо пройти анкетирование!'))
		);	
 		
		$this->_redirector->gotoSimple('start', 'index', 'survey', array('type' => 'vpo'));
		*/
	}	
	
	
	##########################
    unset($attribs['param']);
    if ($title == null) return $content;
    
    return parent::screenForm($title, $content, $attribs);
    
  }
}