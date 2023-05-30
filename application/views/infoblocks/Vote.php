<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';
class HM_View_Infoblock_Vote extends HM_View_Infoblock_ScreenForm
{                                          
  protected $id = 'vote';
  protected $class = 'scrollable';
  
  public function vote($title = null, $attribs = null, $options = null)
  {	
	$this->_userService = $this->getService('User');		
	$user = $this->_userService->getCurrentUser();		
	
	$serviceContainer = Zend_Registry::get('serviceContainer');
    $this->id = strtolower(substr(str_replace('HM_View_Infoblock_', '', get_class($this)), 0, 1)).substr(str_replace('HM_View_Infoblock_', '', get_class($this)), 1);
    ###########################	
    $isShow = false; 
    if($user->isInvalid && $isShow){
		
		$title = 'Голосование';		
		$content = '
			<div style="overflow-x: scroll;">
				<iframe src="https://docs.google.com/forms/d/e/1FAIpQLSeZrnIl6um4wLnvCsZE71FaIQqc11K9XbQ5HkPGl4nz-EJDLA/viewform?embedded=true" width="760" height="500" frameborder="0" marginheight="0" marginwidth="0">Загрузка...</iframe>
			</div>
		';
	} else {
		$title = '&nbsp;';
		$content = '
			<style>			
				#vote { display:none; }
			</style>';		
	}
	
	##########################
    unset($attribs['param']);
    if ($title == null) return $content;
    
    return parent::screenForm($title, $content, $attribs);
    
  }
}