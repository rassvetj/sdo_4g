<?php
    foreach ($this->pages as $page) {
        if (!$page->getLabel()) continue;
        // todo: сделать универсально
        if ($page->subjects && (count($page->subjects) > 1) && 1 == 0) { # функционал не используется + порядком увеличивает время загрузки страницы.
            echo '&nbsp;<span class="separator wmenu">&#0155;</span>&nbsp;';
?>
<ul class="dropdown-actions-menu bredcrumbs-dropdown-actions-menu">
<?php
    /*
	foreach($page->subjects as $subject) {
        if (Zend_Controller_Front::getInstance()->getRequest()->getParam('subject_id', 0) == $subject->subid) continue;
        
        $name = (strlen(_($subject->name)) > 50) ? substr(_($subject->name), 0, 50).'...' : $subject->name;
        $name = $this->escape($name);
        if (($subject->base == HM_Subject_SubjectModel::BASETYPE_SESSION) && !Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER)) {
            $name = sprintf('<span class="name">%s</span><span class="date">%s-%s</span>', $name, HM_Controller_Action::formatDate($subject->begin), HM_Controller_Action::formatDate($subject->end));
        }
        echo sprintf("<li><a href=\"%s\">%s</a></li>", $this->url(array('subject_id' => $subject->subid)), $name);
    }
	*/
?>
</ul>
<?php
        } else {
            echo '&nbsp;<span class="separator">&#0155;</span>&nbsp;';
        }
        echo $this->navigation()->htmlify($page);
    }
?>