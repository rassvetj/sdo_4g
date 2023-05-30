<?php

class User_AjaxController extends HM_Controller_Action
{
    public function usersListAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }

        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();
        $this->getResponse()->setHeader('Content-type', 'application/json; charset=UTF-8');

        $q = strtolower(trim($this->_request->getParam('tag')));
        $res = array();
        if(!empty($q)) {
			# Если это студент, то только студенты его группы и преподы аго сессий - занести в кэш с интервалом в 1 день.
			$criteria = '';			
			if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_STUDENT)){
				$allowMessageUsers = $this->getService('Message')->getAllowMessageUsers( $this->_request->getParam('subject_id') );
				$criteria = ' AND 1=0 ';
				if(!empty($allowMessageUsers)){
					$criteria = $this->getService('User')->quoteInto(' AND (MID IN (?))', $allowMessageUsers);
				}
			}
			
            $q = '%'.$q.'%';
            $where = '('.
                $this->getService('User')->quoteInto('LOWER(LastName) LIKE LOWER(?)', $q).
                $this->getService('User')->quoteInto('OR LOWER(FirstName) LIKE LOWER(?)', $q).
                $this->getService('User')->quoteInto('OR LOWER(Patronymic) LIKE LOWER(?)', $q).
                $this->getService('User')->quoteInto('OR LOWER(Login) LIKE LOWER(?)', $q).
                $this->getService('User')->quoteInto("OR LOWER(CONCAT(CONCAT(CONCAT(CONCAT(LastName, ' '), FirstName), ' '), Patronymic)) LIKE LOWER(?)", $q).
            ')';
			$where = $where.$criteria;
			
            $collection = $this->getService('User')->fetchAll($where);
            foreach($collection as $user) {
                $o = new stdClass();
                $o->key = $user->getName();
                $o->value = $user->MID;
                $res[] = $o;
            }
        }

        echo Zend_Json::encode($res);
    }
	
	
	public function studentsListAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }

        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();
        $this->getResponse()->setHeader('Content-type', 'application/json; charset=UTF-8');

        $q 		= strtolower(trim($this->_request->getParam('search')));        
		$data	= array();
		
		if(empty($q) || mb_strlen($q) < 3 ){
			echo Zend_Json::encode($data);
			die;
		}
		
		$serviceUser = $this->getService('User');
		
		$select = $serviceUser->getSelect();
		$select->from($serviceUser->getMapper()->getAdapter()->getTableName(), array('MID', 'FirstName', 'LastName', 'Patronymic'));
		$select->where('role_1c = ?', HM_User_UserModel::ROLE_1C_STUDENT);
		$select->where('blocked != ?', HM_User_UserModel::STATUS_BLOCKED);
		$select->where($serviceUser->quoteInto("LOWER(CONCAT(CONCAT(CONCAT(CONCAT(LastName, ' '), FirstName), ' '), Patronymic)) LIKE LOWER(?)", "%".$q."%"));
		
		$res = $select->query()->fetchAll();		
		if(empty($res)){ 
			echo Zend_Json::encode($data);
			die;
		}
		
		foreach($res as $i){
			$data[] = array(
				'id'   => $i['MID'],
				'text' => $i['LastName'].' '.$i['FirstName'].' '.$i['Patronymic'],
			);
		}		
		echo Zend_Json::encode($data);
		die;
    }

}