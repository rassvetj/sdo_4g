<?php
/**
 * Created by PhpStorm.
 * User: sitnikov
 * Date: 19.05.14
 * Time: 13:32
 */

class Subject_AjaxController extends HM_Controller_Action {

    public function setMarkAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }

        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();
       // $this->getResponse()->setHeader('Content-type', 'application/json; charset=UTF-8');

        $response = 0;
        $subjectId = $this->_getParam('subject_id', 0);
        $score = $this->_getParam('score', 0);
        $userId = $this->getService('User')->getCurrentUserId();

        if (!$subjectId) {

        }

        if (!$score) {

        }
        
        $subjectMarkService = $this->getService('SubjectMark');
        
        $result = $subjectMarkService->setMark($score, $subjectId, $userId);
        
        //pr($result->mark);
        if ($result) {
            echo $result->mark;
        }
    }
	
	
	 public function validateExternalIdAction(){
		if (!$this->getRequest()->isXmlHttpRequest()) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }

        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();
		
		
		$external_id = $this->_getParam('external_id', 0);
        $subject_id = $this->_getParam('subject_id', 0);
		
		if(empty($external_id) || empty($external_id)){			
			#echo json_encode(array('error' => 'не указан id из 1С'));
			die;
		}
		
		$subjService = $this->getService('Subject');		
		$same = $subjService->fetchAll($subjService->quoteInto(array('external_id = ? ', ' AND subid != ?'), array($external_id, $subject_id)))->getList('subid', 'name');
		if(!empty($same)){			
			$links = array();
			foreach($same as $id => $name){
				$links[] = '<a href="'.$this->view->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'card', 'subject_id' => $id)).'" target="_blank">'.$name.'</a>';
			}
			echo json_encode(array('error' => 'Такой id уже есть в сессиях: '.implode(', ',$links)));						
		}
		die;
	 }
	 
	 
	public function getSubjectListAction()
	{
		$data   = array();
		$items  = array();
		$search = trim($this->_getParam('search', false));
		
		if (!$this->getRequest()->isXmlHttpRequest()) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }
		
		if (!$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_DEAN))){
			header('HTTP/1.0 404 Not Found');
            exit;
		}
		
		if(mb_strlen($search) >= 3){
			$subjects = $this->getService('Subject')->fetchAll($this->getService('Subject')->quoteInto(
				array('(name LIKE ?', ' OR external_id LIKE ? )'),
				array('%' . $search . '%', '%' . $search . '%')				 
			), 'name ASC');
			
			#$full_collection = $this->getService('Dean')->getSubjectsResponsibilities($this->getService('User')->getCurrentUserId());
			#$allowIds        = $full_collection->getList('subid');
			
			if(!empty($subjects)){
				foreach($subjects as $subject){
					#if(!array_key_exists($subject->subid, $allowIds)){ continue; }
					$items[] = array(
						'id'   => $subject->subid,
						'text' => $subject->name . ($subject->external_id ? ' (' . $subject->external_id . ')' : ''),
					);
				}
			}
		}
		$data['items'] = $items;
		echo json_encode($data);
		die;
	}
	 
} 