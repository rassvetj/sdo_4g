<?php
class HM_Acl_Message extends HM_Acl
{
    public function __construct (Zend_Acl $acl)
    {
        $resource = sprintf('mca:%s:%s:%s', 'message', 'contact', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(null, $resource); // разрешить всем, кто прошел проверку isActivityUser()? т.е. точно имеет отношение к данному курсу (dean|teacher|student|graduated)
		
		$resource = sprintf('mca:%s:%s:%s', 'message', 'send', 'instant-send');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->deny(null, $resource); 
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN,$resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN,$resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR,$resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER,$resource);
		
		// не просто разрешить всем студентам, а убедиться что он именно студент на этом курсе
		
		# запрещаем студенту отправлять сообщения без привязки к курсу.
		$serviceContainer = Zend_Registry::get('serviceContainer');
		if($serviceContainer->getService('Acl')->inheritsRole($serviceContainer->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_STUDENT)){
			$request 	= Zend_Controller_Front::getInstance()->getRequest();
			$subject_id = $request->getParam('subject_id');
			if(!empty($subject_id)){
				$this->allowForSubject($acl, HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
			}
		} else {
			$this->allowForSubject($acl, HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
		}
		
		$resource = sprintf('mca:%s:%s:%s', 'message', 'edit', 'delete');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->deny(null, $resource);         
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR,$resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER,$resource);
    }
}