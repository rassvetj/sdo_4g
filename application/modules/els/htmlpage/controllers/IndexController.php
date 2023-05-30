<?php
class Htmlpage_IndexController extends HM_Controller_Action{
	
	public function viewAction(){
		
		$page_id = $this->_request->getParam('htmlpage_id', 0);
		
		if($page_id) $page = $this->getService('Htmlpage')->getOne($this->getService('Htmlpage')->find($page_id));
		if(!$page_id || !$page) {
			$this->_flashMessenger->addMessage(_('Страница не найдена'));
			$this->_redirector->gotoSimple('index', 'index', 'default');
		}

        if($page->url != ''){
            $this->_redirector->gotoUrlAndExit($page->url);
        }
		//$this->getService('Unmanaged')->setSubHeader($page->name);
		$this->view->page = $page;
		
	}
	
}