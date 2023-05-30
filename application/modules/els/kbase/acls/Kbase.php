<?php
class HM_Acl_Kbase extends HM_Acl
{
    public function __construct(Zend_Acl $acl)
    {
        $resource = sprintf('mca:%s:%s:%s', 'kbase', 'source', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));        
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		
		
		$resource = sprintf('mca:%s:%s:%s', 'kbase', 'source', 'import');
        $acl->addResource(new Zend_Acl_Resource($resource));                
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'kbase', 'source', 'process');
        $acl->addResource(new Zend_Acl_Resource($resource));                
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		
		
		
		$resource = sprintf('mca:%s:%s:%s', 'kbase', 'index', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(null, $resource); // всем
        $acl->deny(HM_Role_RoleModelAbstract::ROLE_GUEST, $resource);

        $acl->addModuleResources('course')
            ->addModuleResources('poll')
            ->addModuleResources('blog')
            ->addModuleResources('resource')
            ->addModuleResources('task')
            ->addModuleResources('test')
            ->addModuleResources('question')
            ->addModuleResources('exercises');
    }

}