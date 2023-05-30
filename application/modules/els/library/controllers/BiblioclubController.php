<?php
class Library_BiblioclubController extends HM_Controller_Action_Crud
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
		$this->view->setHeader(_('ЭБС "Университетская библиотека online"'));
		$this->_helper->layout()->disableLayout();
		
		$uri        = $config->library->biblioclub->uri;
		$secret_key = $config->library->biblioclub->secret_key;
		$domain     = $config->library->biblioclub->domain;
		$time       = time();
		
		$params = array(
			'page'        => 'main_ub_red',
			'action'      => 'auth_for_org',
			'domain'      => $domain,
			'user_id'     => $this->_user->MID,
			'type'        => $this->getType(),
			'login'       => $this->_user->Login,
			'time'        => $time,
			'sign'        => $this->getSign(array('user_id' => $this->_user->MID, 'secret_key' => $secret_key, 'time' => $time)),
			'first_name'  => $this->_user->FirstName,
			'last_name'   => $this->_user->LastName,
			'parent_name' => $this->_user->Patronymic,
			'utf'         => 1, # 1 - UTF-8, 0 - Windows-1251.
		);
		
		$url = $uri . '?' . http_build_query($params);
		header('Location: ' . $url);
		die;
	}
	
	#type = 5 - преподаватель, 6 - студент, 7 - абонент (в ссылке указываем только цифру "5", "6" или "7")
	private function getType()
	{
		return $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_TEACHER, HM_Role_RoleModelAbstract::ROLE_TUTOR))
			? 5 : 6;
	}
	
	private function getSign($params)
	{
		return md5($params['user_id'] . $params['secret_key'] . $params['time']);
	}
}












