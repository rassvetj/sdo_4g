<?php
class Rest_UsersController extends HM_Controller_Action_RestOauth
{
    /*
    * @var HM_User_UserService
    */
    protected $_defaultService = null;

    public function init()
    {
        parent::init();
        $this->_defaultService = $this->getService('User');
    }

    public function getAction()
    {
        $id = (int) $this->_getParam('id', 0);
        if ($id <= 0) {
            $id = $this->_server->authorizedUserId();
        }

        if ($id > 0) {
            $user = $this->getOne($this->_defaultService->find($id));
            if ($user) {
                $values = array(
                    'user_id'    => $user->MID,
                    'lastname'   => $user->LastName,
                    'firstname'  => $user->FirstName,
                    'patronymic' => $user->Patronymic,
                    'roles'      => array(),
                    'groups'     => array(),
                    'photo_url'  => $user->getPhoto()
                );

                $currentRole = $this->_defaultService->getCurrentUserRole();

                $roles = $this->_defaultService->getUserRoles($id);
                if (is_array($roles) && count($roles)) {
                    foreach($roles as $role) {
                        $values['roles'][] = array(
                            'role' => $role,
                            'current' => (boolean) ($role == $currentRole)
                        );
                    }
                }

                $userGroups = $this->getService('StudyGroupUsers')->fetchAllDependence(
                    'StudyGroup',
                    $this->getService('StudyGroupUsers')->quoteInto('study_groups_users.user_id = ?', $id)
                );

                if (count($userGroups)) {
                    foreach($userGroups as $userGroup) {
                        if (isset($userGroup->groups)) {
                            $group = $userGroup->groups->current();
                            if($group) {
                                $values['groups'][] = array(
                                    'group_id'    => $group->group_id,
                                    'title'       => $group->name,
                                );
                            }
                        }
                    }
                }


                /*
                $groups = $this->_defaultService->getGroups($id);
                if (count($groups)) {
                    foreach($groups as $group)
                    {
                        $values['groups'][] = array(
                            'group_id' => $group->gid,
                            'title' => $group->name
                        );
                    }
                }
                */
                $this->view->assign($values);
            }
        }
    }
}