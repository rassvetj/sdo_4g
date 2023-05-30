<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';
class HM_View_Infoblock_ExchangeStudent extends HM_View_Infoblock_ScreenForm
{                                          
  protected $id = 'exchange_student';
  protected $class = 'scrollable';
  
  public function exchangeStudent($title = null, $attribs = null, $options = null)
  {	
  	$request = Zend_Controller_Front::getInstance()->getRequest();
	$lng = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);
  
    if($lng == 'eng') $title = 'Training in foreign partner universities';  
	else  $title = _('Обучение в зарубежных вузах');
	
	$this->_userService = $this->getService('User');		
	$user = $this->_userService->getCurrentUser();		
	
	$serviceContainer = Zend_Registry::get('serviceContainer');
    $this->id = strtolower(substr(str_replace('HM_View_Infoblock_', '', get_class($this)), 0, 1)).substr(str_replace('HM_View_Infoblock_', '', get_class($this)), 1);
    ###########################	
	 
	$allowPeopleGUID = array(
		'601222','614464','615986','615987','615990','615989','615993','616881','614462','614463','616008','615984','605357','634703',
		'626140','626149','626154','626155','626158','626159','626161','626166','626169','626173','626174','641709',
		'77',
	);	
	


	if(in_array($user->mid_external, $allowPeopleGUID)) {
		$content = $this->view->render('exchangeStudent_chosen.tpl'); # взярт шаблон из doc файла	
	} else {
		// $content = $this->view->render('exchangeStudent_all.tpl');	
		if($lng == 'eng') $content = $this->view->render('exchangeStudent_all_en.tpl');
		else $content = $this->view->render('exchangeStudent_all.tpl');
	}
	
	
	
	
	##########################
    unset($attribs['param']);
    if ($title == null) return $content;
    
    return parent::screenForm($title, $content, $attribs);
    
  }
}