<?php 

	$request = Zend_Controller_Front::getInstance()->getRequest();
	$lng = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);

	if($lng == 'eng') echo $this->page->translation;
	else echo $this->page->text;




?>