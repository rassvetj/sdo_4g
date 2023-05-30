<?php
class HM_View_Helper_NewsPreview extends HM_View_Helper_Abstract
{
	
	public function newsPreview($news, $fullView = false){
		//pr($news);
		$this->view->news = $news;
		$this->view->fullView = $fullView;

        $this->view->showUserCard = (!in_array( Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(),
                                                array(HM_Role_RoleModelAbstract::ROLE_GUEST)
        ));

        return $this->view->render('news-preview.tpl');
	}
	
}