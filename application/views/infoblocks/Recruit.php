<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';
class HM_View_Infoblock_Recruit extends HM_View_Infoblock_ScreenForm
{                                          
  protected $id = 'recruit';
  protected $class = 'scrollable';
  
  public function recruit($title = null, $attribs = null, $options = null)
  {
    
	$this->_userService = $this->getService('User');		
	$user = $this->_userService->getCurrentUser();		
	
	$serviceContainer = Zend_Registry::get('serviceContainer');
    $this->id = strtolower(substr(str_replace('HM_View_Infoblock_', '', get_class($this)), 0, 1)).substr(str_replace('HM_View_Infoblock_', '', get_class($this)), 1);
    ###########################	
	
	$recruitInfo = $this->getService('Recruits')->getRecruitInfo($user->mid_external);
	if(empty($recruitInfo)){
		$title = '&nbsp;';
		$content = '
			<style>
				#recruit { display:none; }
			</style>';			
	} else {	
		$title = 'Военно-учетный стол';
		$content = $this->view->render('recruit.tpl');
	}	
	##########################
    unset($attribs['param']);
    if ($title == null) return $content;
    
    return parent::screenForm($title, $content, $attribs);
    
  }
}