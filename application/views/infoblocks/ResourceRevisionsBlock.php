<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';

class HM_View_Infoblock_ResourceRevisionsBlock extends HM_View_Infoblock_ScreenForm
{
    protected $id = 'resourceRevisionsBlock';

    public function resourceRevisionsBlock($title = null, $attribs = null, $options = null)
    {

        $services = Zend_Registry::get('serviceContainer');
        $resource = $options['subject'];
        $revisions = $services->getService('ResourceRevision')->fetchAll(array('resource_id = ?' => $resource->resource_id), 'revision_id DESC');

        $users = array();
        if (count($revisions)) {
            $userIds = $revisions->getList('created_by');
            if (count($usersCollection = $services->getService('User')->fetchAll(array('MID IN (?)' => $userIds)))) {
                foreach ($usersCollection as $user) {
                    $users[$user->MID] = $user->getName();
                }
            }
        }

        $this->view->resource = $resource;
        $this->view->revisions = $revisions;
        $this->view->users = $users;
        $this->view->restoreable = $services->getService('Acl')->inheritsRole($services->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_MANAGER, HM_Role_RoleModelAbstract::ROLE_DEVELOPER));

        $content = $this->view->render('resourceRevisionsBlock.tpl');
        if ($title == null) return $content;
        return parent::screenForm($title, $content, $attribs);
    }
}