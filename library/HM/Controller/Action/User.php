<?php
class HM_Controller_Action_User extends HM_Controller_Action_Extended
{
    protected $_userId = null;
    
    protected $service = 'User';
    protected $idParamName  = 'user_id';
    protected $idFieldName = 'MID';
    

    public function init()
    {
        $userId = $this->_getParam('user_id', 0);

        if (($userId != $this->getService('User')->getCurrentUserId()) &&
            $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TEACHER)
        ) {
            $this->_setParam('withoutContextMenu', true);
        }

        parent::init();

        $this->getService('Unmanaged')->getController()->page_id = 'm00';

        if ($userId > 0) {
            if (!$this->getService('Acl')->isCurrentAllowed(HM_Acl::RESOURCE_USER_CONTROL_PANEL, HM_Acl::PRIVILEGE_VIEW)) {
                $userId = $this->getService('User')->getCurrentUserId();
            }
        } else {
            $userId = $this->getService('User')->getCurrentUserId();
        }

        if ($this->getRequest()->getActionName() == 'card') {
            $this->view->setHeader(_('Личный кабинет'));            
            if ($userId != $this->getService('User')->getCurrentUserId()) {
                $user = $this->getOne($this->getService('User')->find($userId));
                if ($user) {
                    $this->view->setHeader(sprintf(_('Пользователь %s'), $user->getName()));
                }
            }
        }

        $this->_userId = $userId;
       
        // если пользователь не админ и смотрит не свою карточку
        // то скрываем меню "Редактирование учетной записи"
        if ( $userId != $this->getService('User')->getCurrentUserId() &&
             !$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ADMIN)
             //!in_array($this->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_ADMIN))
        ) {
             $this->view->addContextNavigationModifier(
                 new HM_Navigation_Modifier_Remove_Page('resource', 'cm:user:page1')
             );
             
        }
    }
}