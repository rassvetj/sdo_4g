<?php
class HM_View_Helper_FaqPreview extends HM_View_Helper_Abstract
{
	
	public function faqPreview($faq){
		//pr($news);
		$this->view->faq = $faq;

        $this->view->showUserCard = (!in_array( Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(),
                                                array(HM_Role_RoleModelAbstract::ROLE_GUEST)
        ));

        return $this->view->render('faq-preview.tpl');
	}
	
}