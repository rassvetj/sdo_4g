<?php
class HM_Acl_Faq
{

    public function __construct(Zend_Acl $acl)
    {
        $this->getService('Activity')->initializeActivityCabinet('', '', 0);
        $isModerator = $this->getService('Activity')->isUserActivityPotentialModerator(
            $this->getService('User')->getCurrentUserId()
        );

        // Просмотр карточки
        $resource = sprintf('mca:%s:%s:%s', 'faq', 'list', 'new');
        $acl->addResource(new Zend_Acl_Resource($resource));

        if ($isModerator) {
            $acl->allow($this->getService('User')->getCurrentUserRole(), $resource);
        } else {
            $acl->deny($this->getService('User')->getCurrentUserRole(), $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'faq', 'list', 'edit');
        $acl->addResource(new Zend_Acl_Resource($resource));

        if ($isModerator) {
            $acl->allow($this->getService('User')->getCurrentUserRole(), $resource);
        } else {
            $acl->deny($this->getService('User')->getCurrentUserRole(), $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'faq', 'list', 'delete');
        $acl->addResource(new Zend_Acl_Resource($resource));

        if ($isModerator) {
            $acl->allow($this->getService('User')->getCurrentUserRole(), $resource);
        } else {
            $acl->deny($this->getService('User')->getCurrentUserRole(), $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'faq', 'list', 'delete-by');
        $acl->addResource(new Zend_Acl_Resource($resource));

        if ($isModerator) {
            $acl->allow($this->getService('User')->getCurrentUserRole(), $resource);
        } else {
            $acl->deny($this->getService('User')->getCurrentUserRole(), $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'faq', 'list', 'publish');
        $acl->addResource(new Zend_Acl_Resource($resource));

        if ($isModerator) {
            $acl->allow($this->getService('User')->getCurrentUserRole(), $resource);
        } else {
            $acl->deny($this->getService('User')->getCurrentUserRole(), $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'faq', 'list', 'unpublish');
        $acl->addResource(new Zend_Acl_Resource($resource));

        if ($isModerator) {
            $acl->allow($this->getService('User')->getCurrentUserRole(), $resource);
        } else {
            $acl->deny($this->getService('User')->getCurrentUserRole(), $resource);
        }

    }

    public function getService($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

}