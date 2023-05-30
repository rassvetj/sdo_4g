<?php
class HM_Acl_Report
{

    public function __construct(Zend_Acl $acl)
    {
        $resource = sprintf('mca:%s:%s:%s', 'report', 'list', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'report', 'list', 'edit');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'report', 'list', 'delete');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'report', 'list', 'tree');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'report', 'generator', 'construct');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'report', 'generator', 'grid');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'report', 'generator', 'save');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'report', 'tutors', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));                
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource); //--Наблюдатель
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource); //--Организатор обучения
		
		$resource = sprintf('mca:%s:%s:%s', 'report', 'tutors', 'get');
        $acl->addResource(new Zend_Acl_Resource($resource));        
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		//--отчет по студентам
		$resource = sprintf('mca:%s:%s:%s', 'report', 'students', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));                
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource); 
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource); 
		
		$resource = sprintf('mca:%s:%s:%s', 'report', 'students', 'get');
        $acl->addResource(new Zend_Acl_Resource($resource));        
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource); 
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource); 
		
		//--отчет по оценкам тьюторов
		$resource = sprintf('mca:%s:%s:%s', 'report', 'ball-tutors', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));                
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource); 
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource); 
		
		$resource = sprintf('mca:%s:%s:%s', 'report', 'ball-tutors', 'get');
        $acl->addResource(new Zend_Acl_Resource($resource));        
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource); 
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource); 
		
		$resource = sprintf('mca:%s:%s:%s', 'report', 'ball-tutors', 'get-csv');
        $acl->addResource(new Zend_Acl_Resource($resource));        
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource); 
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource); 
		
		$resource = sprintf('mca:%s:%s:%s', 'report', 'ball-tutors', 'get-csv-prove');
        $acl->addResource(new Zend_Acl_Resource($resource));        
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource); 
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource); 
		
		//--отчет по новостям
		$resource = sprintf('mca:%s:%s:%s', 'report', 'news', 'index'); 
        $acl->addResource(new Zend_Acl_Resource($resource));                
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource); 
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'report', 'news', 'get');
        $acl->addResource(new Zend_Acl_Resource($resource));                
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource); 
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);
		
		
		# отчет по оценкам тьюторов
		$resource = sprintf('mca:%s:%s:%s', 'report', 'ball', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'report', 'ball', 'get');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		#непроверенные работы
		$resource = sprintf('mca:%s:%s:%s', 'report', 'unchecked-works-new', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'report', 'unchecked-works-new', 'get');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		
		$resource = sprintf('mca:%s:%s:%s', 'report', 'unlinked-program', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'report', 'unlinked-program', 'get');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'report', 'unlinked-program', 'remove-assigns');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'report', 'unlinked-program', 'assign-graduated');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		# Сессии студентов
		$resource = sprintf('mca:%s:%s:%s', 'report', 'student-subjects', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'report', 'student-subjects', 'get');
        $acl->addResource(new Zend_Acl_Resource($resource));        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'report', 'student-subjects', 'get-csv');
        $acl->addResource(new Zend_Acl_Resource($resource));        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'report', 'assign-student', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'report', 'assign-student', 'get');
        $acl->addResource(new Zend_Acl_Resource($resource));        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'report', 'debt-subject', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'report', 'debt-subject', 'get');
        $acl->addResource(new Zend_Acl_Resource($resource));        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'report', 'timetable', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'report', 'timetable', 'get');
        $acl->addResource(new Zend_Acl_Resource($resource));        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		
		$resource = sprintf('mca:%s:%s:%s', 'report', 'debt-student', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'report', 'debt-student', 'get');
        $acl->addResource(new Zend_Acl_Resource($resource));        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'report', 'external', 'list');
        $acl->addResource(new Zend_Acl_Resource($resource));        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'report', 'journal', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'report', 'journal', 'get');
        $acl->addResource(new Zend_Acl_Resource($resource));        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'report', 'journal', 'export-lesson');
        $acl->addResource(new Zend_Acl_Resource($resource));        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
		
    }
}