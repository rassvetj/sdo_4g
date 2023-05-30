<?php
if(!Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(),  HM_Role_RoleModelAbstract::ROLE_ENDUSER)){
    echo $this->headSwitcher(array('module' => 'subject', 'controller' => 'list', 'action' => 'calendar', 'switcher' => 'calendar'));
}
echo $this->subjectsCalendar(
    $this->source,
    array(
        //'eventDropFunctionName'   => 'sendCalendarChange',
        //'eventResizeFunctionName' => 'sendCalendarChange',
        'editable'                => $this->editable,
        'saveDataUrl'             => $this->url(array('module'=>'subject', 'controller'=>'list','action'=>'save-calendar'))
    )
);
?>