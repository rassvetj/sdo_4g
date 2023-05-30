<?php

abstract class HM_Role_RoleModelAbstract extends HM_Model_Abstract
{
/*    const ROLE_GUEST        = 'guest';
    const ROLE_STUDENT      = 'student';
    const ROLE_TEACHER      = 'teacher';
    const ROLE_DEAN         = 'dean';
    const ROLE_AUTHOR       = 'author';
    const ROLE_DEVELOPER    = 'developer';
    const ROLE_EXPERT       = 'expert';
    const ROLE_METODIST     = 'metodist';
    const ROLE_METODIST_CDO = 'metodistCDO';
    const ROLE_MANAGER      = 'manager';
    const ROLE_ADMIN        = 'admin';

    static public function getBasicRoles()
    {
        return array(
            self::ROLE_GUEST => _('Гость'),
            self::ROLE_STUDENT => _('Слушатель'),
            self::ROLE_TEACHER => _('Преподаватель'),
            self::ROLE_DEAN => _('Менеджер по обучению'),
            self::ROLE_AUTHOR => _('Автор'),
            self::ROLE_DEVELOPER => _('Разработчик'),
            self::ROLE_EXPERT => _('Эксперт'),
            self::ROLE_METODIST => _('Методист'),
            self::ROLE_METODIST_CDO => _('Методист ЦДО'),
            self::ROLE_MANAGER => _('Менеджер БЗ'),
            self::ROLE_ADMIN => _('Администратор')
        );
    }*/
    
    
    
    const ROLE_GUEST       = 'guest';
    const ROLE_USER        = 'user';
    const ROLE_SUPERVISOR  = 'supervisor';
    const ROLE_STUDENT     = 'student';
    const ROLE_TEACHER     = 'teacher';
    const ROLE_DEVELOPER   = 'developer';
    const ROLE_MANAGER     = 'manager';
    const ROLE_ADMIN       = 'admin';
    const ROLE_DEAN        = 'dean';
    const ROLE_ENDUSER     = 'enduser';
    const ROLE_EMPLOYEE    = 'employee';
    const ROLE_CHIEF       = 'chief';
    const ROLE_TUTOR       = 'tutor';
    //const ROLE_METODIST    = 'metodist';//costyl, ibo code bez nego ne rabotaet -NAH!

    /**
     * Возвращает самую главную роль из переданного массива
     * 
     * @param array $roles
     * @return string
     */
    static public function getMaxRole($roles)
    {
        global $profiles_basic_ids;
        
        $result = self::ROLE_ENDUSER;
        $max = 0;
        
        foreach ($roles as $role) {
            
            $roleValue = isset($profiles_basic_ids[$role]) ? (float) $profiles_basic_ids[$role] : 0;
            
            if ($roleValue > $max) {
                $max = $roleValue;
                $result = $role;
            }
        }
        
        return $result;
    }

    static public function getBasicRoles($all = true, $withRoleUnion = false)
    {

        if($all == true){
            $roles = array(
                self::ROLE_GUEST     => _('Гость'),
                self::ROLE_USER      => _('Пользователь'),
                self::ROLE_SUPERVISOR => _('Наблюдатель'),
                self::ROLE_EMPLOYEE   => _('Сотрудник'),
                self::ROLE_STUDENT   => _('Слушатель'),
                self::ROLE_TEACHER   => _('Преподаватель'),
                self::ROLE_DEAN      => _('Организатор обучения'),
                self::ROLE_DEVELOPER => _('Разработчик ресурсов'),
                self::ROLE_MANAGER   => _('Менеджер базы знаний'),
                self::ROLE_ADMIN     => _('Администратор'),
                self::ROLE_TUTOR     => _('Тьютор'),
                //self::ROLE_METODIST  => _('Методист')
            );
        }else{
            $roles = array(
                // self::ROLE_GUEST => _('Гость'),
                self::ROLE_USER => _('Пользователь'),
                self::ROLE_SUPERVISOR => _('Наблюдатель'),
                self::ROLE_EMPLOYEE   => _('Сотрудник'),
                self::ROLE_STUDENT   => _('Слушатель'),
                self::ROLE_TEACHER   => _('Преподаватель'),
                self::ROLE_DEAN => _('Организатор обучения'),
                self::ROLE_DEVELOPER => _('Разработчик ресурсов'),
                self::ROLE_MANAGER   => _('Менеджер базы знаний'),
                self::ROLE_ADMIN     => _('Администратор'),
                self::ROLE_TUTOR     => _('Тьютор'),
                //self::ROLE_METODIST  => _('Методист')
            );
        }

        if($withRoleUnion == true){
            unset($roles[self::ROLE_GUEST], $roles[self::ROLE_USER], $roles[self::ROLE_CHIEF], $roles[self::ROLE_EMPLOYEE], $roles[self::ROLE_STUDENT]);
            $guest = $all ? array(self::ROLE_GUEST => _('Гость')) : array();
            $enduser = array(self::ROLE_ENDUSER => _('Пользователь'));
            $roles = $guest + $enduser + $roles;
        }


        $event = new sfEvent(null, HM_Extension_ExtensionService::EVENT_FILTER_BASIC_ROLES);
        Zend_Registry::get('serviceContainer')->getService('EventDispatcher')->filter($event, $roles);
        $roles = $event->getReturnValue();

        return $roles;

    }
    
     
    
    
    
}
