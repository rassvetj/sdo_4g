<?php
class Library_UraitController extends HM_Controller_Action_Crud
{
	protected $_user = false;
	
	public function init()
	{
        $this->_user = $this->getService('User')->getCurrentUser();
		parent::init();
	}
	
	public function createAuthLinkAction()
    {	
		$config = Zend_Registry::get('config');
		$this->view->setHeader(_('ЮРАЙТ'));
		$this->_helper->layout()->disableLayout();
		
		$uri   = $config->library->urait->uri;
		$pid   = $config->library->urait->pid;
		$token = $config->library->urait->token;
		$email = $this->_user->EMail;
		$time  = time();
		
		
		$params = array(
			'pid'          => $pid, # Уникальный код организации
			'email'        => $email,
			'fname'        => $this->_user->FirstName,
			'lname'        => $this->_user->LastName,
			'pname'        => $this->_user->Patronymic,
			'sex'          => $this->getGender(),
			'role_id'      => $this->getRole(),
			'time'         => $time,
			'sign'         => $this->getSign(array('pid'=>$pid, 'email'=>$email, 'token'=>$token, 'time'=>$time)),
			'redirect_url' => '/',
		);
		
		$url = $uri . '?' . http_build_query($params);
		header('Location: ' . $url);
		die;		
	}
	
	# 0 — Женщина; 1 — Мужчина.
	private function getGender()
	{
		return  $this->_user->Gender == HM_User_Metadata_MetadataModel::GENDER_MALE
				? 1 : 0;
	}
	
	# 2 — Преподаватель;  3 — Студент; 4 — Библиотекарь;
	private function getRole()
	{
		return $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_TEACHER, HM_Role_RoleModelAbstract::ROLE_TUTOR))
				? 2 : 3;
	}
	
	private function getSign($params)
	{
		return md5($params['pid'] . ':' . $params['email'] . ':' . $params['token'] . ':' . $params['time']);
	}
}












