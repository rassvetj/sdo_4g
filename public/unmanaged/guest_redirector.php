<?php
define('HARDCODE_WITHOUT_SESSION',true);
define('HARDCODE_WITHOUT_GETTEXT',true);

require_once("../../application/cmd/cmdBootstraping.php");
$services = Zend_Registry::get('serviceContainer');

if ($services->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_GUEST) {
    $session = new Zend_Session_Namespace('default');
    $session->autoredirect['url'] = $_SERVER['REQUEST_URI'];
    header('Location: '.Zend_Registry::get('view')->serverUrl('/'));
    exit();
}

Zend_Registry::_unsetInstance();