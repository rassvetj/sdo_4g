<?php
class HM_View_Infoblock_ScreenForm extends HM_View_Infoblock_Abstract
{
    protected $id = 'screenform';

    public function screenForm($title, $content, $attribs) {		
		
        if (null === $title) return $content;
        $infoblockTitle = str_replace('HM_View_Infoblock_', '', get_class($this));        
        $infoblockTitle[0] = strtolower($infoblockTitle[0]);

        if(!isset($attribs['id'])){
            $attribs['id'] = $infoblockTitle; 
        } else if ($attribs['id'] === '') {
            unset($attribs['id']);
            $this->id = null;
        }else{
            $this->id = $attribs['id'];
        }
        
        if($this->class!=''){
           $attribs = $this->view->htmlAttribsPrepare($attribs, array('class' => $this->class));
        }

        if (!isset($attribs['data-infoblock'])) {
            $attribs['data-infoblock'] = $infoblockTitle;
        }
        //if (!isset($attribs['data-undeletable'])) {
        //    $attribs['data-undeletable'] = <value>;
        //}
        
        $attribs = $this->view->htmlAttribsPrepare($attribs, array('class' => array('infoblock-'.$attribs['data-infoblock'])));
		
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$lng = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);
	
		/* if($lng == "eng") {	
			$translationFile = APPLICATION_PATH.'/../data/locales/en_US/'.$title.'.html';
		    if(!file_exists($translationFile)) 
				$content .= '<br/>Failed to load infoblock translation!'; 
			else
				$content = file_get_contents($translationFile);
		} */

        $this->view->attribs = $attribs;
        $this->view->title   = $title;
        $this->view->content = $content;
        return $this->view->render('screenform.tpl');
    }
}