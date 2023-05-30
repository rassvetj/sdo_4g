<?php
class Rest_GroupsController extends HM_Controller_Action_RestOauth
{
    protected $_defaultService = null;

    public function init()
    {
        parent::init();
        $this->_defaultService = $this->getService('StudyGroup');
    }

    public function getAction()
    {
        $id = (int) $this->_getParam('id', 0);
        if ($id > 0) {

            $group = $this->getOne($this->_defaultService->find($id));
            if ($group) {
                $values = array(
                    'group_id'  => $group->group_id,
                    'name'      => $group->name,
                    'users'     => array(),
                );

                $groupUsers = $this->getService('StudyGroupUsers')->fetchAllDependence(
                    'User',
                    $this->getService('StudyGroupUsers')->quoteInto('study_groups_users.group_id = ?', $id)
                );

                if (count($groupUsers)) {
                    foreach($groupUsers as $groupUser) {
                        if (isset($groupUser->users)) {
                            $user = $groupUser->users->current();
                            if($user) {
                                $values['users'][] = array(
                                    'user_id'    => $user->MID,
                                    'lastname'   => $user->LastName,
                                    'firstname'  => $user->FirstName,
                                    'patronymic' => $user->Patronymic,
                                );
                            }
                        }
                    }

                }

                $this->view->assign($values);
            }
        }
    }
}
