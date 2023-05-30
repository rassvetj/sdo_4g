<?php
class HM_Acl extends Zend_Acl
{
    const RESOURCE_USER_CONTROL_PANEL = 'user_control_panel';

    const PRIVILEGE_VIEW = 'view';
    const PRIVILEGE_EDIT = 'edit';

    private $_modules = array();

    public function __construct()
    {

        $this->addRole(new Zend_Acl_Role(HM_Role_RoleModelAbstract::ROLE_ENDUSER));

        $this->addRole(new Zend_Acl_Role(HM_Role_RoleModelAbstract::ROLE_GUEST));
        $this->addRole(new Zend_Acl_Role(HM_Role_RoleModelAbstract::ROLE_STUDENT), array(HM_Role_RoleModelAbstract::ROLE_ENDUSER));
        $this->addRole(new Zend_Acl_Role(HM_Role_RoleModelAbstract::ROLE_TEACHER));
        //$this->addRole(new Zend_Acl_Role(HM_Role_RoleModelAbstract::ROLE_DEAN));
        //$this->addRole(new Zend_Acl_Role(HM_Role_RoleModelAbstract::ROLE_AUTHOR));
        $this->addRole(new Zend_Acl_Role(HM_Role_RoleModelAbstract::ROLE_DEVELOPER));
        //$this->addRole(new Zend_Acl_Role(HM_Role_RoleModelAbstract::ROLE_EXPERT));
        //$this->addRole(new Zend_Acl_Role(HM_Role_RoleModelAbstract::ROLE_METODIST));
        //$this->addRole(new Zend_Acl_Role(HM_Role_RoleModelAbstract::ROLE_METODIST_CDO));
        $this->addRole(new Zend_Acl_Role(HM_Role_RoleModelAbstract::ROLE_MANAGER));
        $this->addRole(new Zend_Acl_Role(HM_Role_RoleModelAbstract::ROLE_ADMIN));
        $this->addRole(new Zend_Acl_Role(HM_Role_RoleModelAbstract::ROLE_DEAN));
        $this->addRole(new Zend_Acl_Role(HM_Role_RoleModelAbstract::ROLE_USER), array(HM_Role_RoleModelAbstract::ROLE_ENDUSER));
        $this->addRole(new Zend_Acl_Role(HM_Role_RoleModelAbstract::ROLE_EMPLOYEE), array(HM_Role_RoleModelAbstract::ROLE_ENDUSER));
        $this->addRole(new Zend_Acl_Role(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR)); // supervisor != enduser!
        $this->addRole(new Zend_Acl_Role(HM_Role_RoleModelAbstract::ROLE_TUTOR));

        //$this->_setDefaults();
    }



    /**
     * Добавляет к ACL ресурсы модуля по его имени
     * @param string $moduleName - имя модуля
     * @param string $applicationModule - имя модуля приложения (edo, els)
     * @return HM_Acl
     */
    public function addModuleResources($moduleName, $applicationModule = 'els')
    {
        if ( !$moduleName ) return;
        $applicationModule = (!$applicationModule)? 'els' : strtolower($applicationModule);
        $moduleName        = strtolower(trim($moduleName));
        $services          = Zend_Registry::get('serviceContainer');
        $moduleDirectory   = APPLICATION_PATH . '/modules/' . $applicationModule . '/' . $moduleName . '/acls';
        if (is_dir($moduleDirectory) && is_readable($moduleDirectory) && !$this->hasModuleResources($moduleName)) {
            $handle = opendir($moduleDirectory);
            if ($handle) {
                while(false !== ($file = readdir($handle))) {
                    if (in_array($file, array('.', '..'))) continue;
                    if (substr($file, -4) == '.php') {
                        $class = 'HM_Acl_'.substr($file, 0, -4);
                        if ( !class_exists($class, false) ) {
                            Zend_Loader::loadFile($file,$moduleDirectory);
                            $acl = new $class($services->getService('Acl'));
                            unset($acl);
                        }
                    }
                }
                $this->storeModuleName($moduleName)
                     ->_checkNewModules();
            }
        }
        return $this;
    }

    /**
     * Добавляет имя модуля в список модулей ресурсы которых имеются в ACL
     * @param $moduleName
     * @return HM_Acl
     */
    public function storeModuleName($moduleName)
    {
        if (!$moduleName) return $this;
        $moduleName = strtolower($moduleName);
        if (!$this->hasModuleResources($moduleName)) $this->_modules[] = $moduleName;
        return $this;
    }

    /**
     * Проверяет инициализированы ли в ACL ресурсы заданного модуля
     * @param $moduleName
     * @return bool
     */
    public function hasModuleResources($moduleName)
    {
        if (!$moduleName) return false;
        $moduleName = strtolower($moduleName);
        return in_array($moduleName,$this->_modules);
    }

    /**
     * Проверяет имеются ли в списке ресурсов ресурсы модулей, которые не добавлены,
     * и добавляет ресурсы этих модулей в ACL
     */
    private function _checkNewModules()
    {
        $resources = $this->getResources();
        if ( !$resources ) return;
        foreach ($resources as $resource) {
            list( ,$moduleName) = explode(':',$resource);
            if ( $moduleName && !$this->hasModuleResources($moduleName) ) {
                $this->addModuleResources($moduleName);
            }
        }
    }

    public function isCurrentAllowed($resource, $privilege = null)
    {
        if ($this->has($resource)) {
            if (!$this->isAllowed(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), $resource, $privilege)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Одни и те же модуль-контроллер-действие настраиваются по-разному внутри уч.курса и вне него
     *
     * @return unknown
     */
    protected function isSubjectContext()
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        return $request->getParam('subject_id');
    }

    /**
     * Adds a Resource having an identifier unique to the ACL
     *
     * The $parent parameter may be a reference to, or the string identifier for,
     * the existing Resource from which the newly added Resource will inherit.
     *
     * @param  Zend_Acl_Resource_Interface|string $resource
     * @param  Zend_Acl_Resource_Interface|string $parent
     * @throws Zend_Acl_Exception
     * @return Zend_Acl Provides a fluent interface
     */
    public function addResource($resource, $parent = null)
    {
        if (!$this->has($resource)) {
            return parent::addResource($resource, $parent);
        }
    }

    public function inheritsRole($role, $inherit, $onlyParents = false, $testedAcc = array())
    {
        if ($role == $inherit) return true;

        $unionRoles = array(
            HM_Role_RoleModelAbstract::ROLE_STUDENT,
            HM_Role_RoleModelAbstract::ROLE_EMPLOYEE,
            HM_Role_RoleModelAbstract::ROLE_CHIEF,
            HM_Role_RoleModelAbstract::ROLE_USER
        );


        if(!is_array($inherit)
            && in_array($inherit, $unionRoles)){
            $inherit = $unionRoles;
        }elseif(is_array($inherit)){
            foreach($inherit as $tRole){
                if(in_array($tRole, $unionRoles)){
                    $inherit = array_merge($inherit, $unionRoles);
                    break;
                }
            }
        }

        if(count($testedAcc) > 0 ){

            foreach($testedAcc as $testedRole){
                if(is_array($inherit) && in_array($testedRole, $inherit)){
                    $key = array_search($testedRole, $inherit);
                    unset($inherit[$key]);
                }
            }
        }


        if (is_array($inherit)) {
            foreach($inherit as $item) {
                $testedAcc[] = $item;
                if ($this->inheritsRole($role, $item, $onlyParents, $testedAcc)) {
                    return true;
                }
            }
        } else {
            return parent::inheritsRole($role, $inherit, $onlyParents);
        }
        return false;
    }

    // не просто разрешить всем студентам, а убедиться что он именно студент на этом курсе
    public function allowForSubject($acl, $role, $resource)
    {
        if ($subjectId = $this->isSubjectContext()) {
            switch ($role) {
                case HM_Role_RoleModelAbstract::ROLE_ENDUSER:
                    $subjects = Zend_Registry::get('serviceContainer')->getService('Student')->getSubjects($userId)->getList('subid');
                    if (in_array($subjectId, $subjects)) {
                        $acl->allow($role, $resource);
                    }
                break;
            }
        } else {
            // в глобальном контексте
            $acl->allow($role, $resource);
        }
        return true;
    }
}