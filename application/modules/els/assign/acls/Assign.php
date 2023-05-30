<?php
class HM_Acl_Assign extends HM_Acl
{
    public function __construct (Zend_Acl $acl)
    {

        $resource = sprintf('mca:%s:%s:%s', 'assign', 'teacher', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        } else {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'assign', 'teacher', 'calendar');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		
		$resource = sprintf('mca:%s:%s:%s', 'assign', 'tutor', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        } else {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'assign', 'tutor', 'calendar');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		
		

        $resource = sprintf('mca:%s:%s:%s', 'assign', 'student', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        } else {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'assign', 'student', 'login-as');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        } else {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'assign', 'graduated', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        
        # Удаление пользователя из списка прошедших обучение курса
        $resource = sprintf('mca:%s:%s:%s', 'assign', 'graduated', 'delete');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        #$acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        
        #печать сертификатов
        $resource = sprintf('mca:%s:%s:%s', 'assign', 'graduated', 'certificates');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'assign', 'admin', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'assign', 'dean', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'assign', 'supervisor', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'assign', 'staff', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);
				
		$resource = sprintf('mca:%s:%s:%s', 'assign', 'staff', 'get-report');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'assign', 'student', 'assign-programm');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'assign', 'student', 'unassign-programm');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		
		$resource = sprintf('mca:%s:%s:%s', 'assign', 'teacher', 'teacher-course-filter');
        $acl->addResource(new Zend_Acl_Resource($resource));      
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);   

		$resource = sprintf('mca:%s:%s:%s', 'assign', 'teacher', 'tutor-course-filter');
        $acl->addResource(new Zend_Acl_Resource($resource));      
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);	

		# назначение тьютора на занятие
		$resource = sprintf('mca:%s:%s:%s', 'assign', 'tutor', 'on-lesson');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);	
		
		# удаление назначения тьютора с занятия
		$resource = sprintf('mca:%s:%s:%s', 'assign', 'tutor', 'unassign-lesson');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);	
				

    }
}