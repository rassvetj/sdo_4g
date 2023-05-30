<?php
class Infoblock_IndexController extends HM_Controller_Action
{
    public function init()
    {
        parent::init();

        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');

        $this->_helper->ContextSwitch()
                ->setAutoJsonSerialization(true)
                ->addActionContext('index', 'json')
                ->initContext();
    }

    public function indexAction()
    {
        $this->_helper->layout()->disableLayout();
        $blocks = array();
        $role = $this->_getParam('role', '');
        if (strlen($role) && in_array($role, array_keys(HM_Role_RoleModelAbstract::getBasicRoles(true, true)))) {
            $blocks = $this->getService('Infoblock')->getTree($role, true, 0, 'UTF-8');
        }
        $this->view->blocks = $blocks;
        $this->_helper->ContextSwitch()->initContext('json');
    }

    public function viewAction()
    {
        $name = $this->_getParam('name', '');
        $id   = $this->_getParam('name', '');
        $role = $this->_getParam('role', '');
        $mode = $this->_getParam('mode', 'view');

        $attribs = array();
        $block;

        if (strlen($role) && ( in_array($role, array_keys(HM_Role_RoleModelAbstract::getBasicRoles(true))) or $role == HM_Role_RoleModelAbstract::ROLE_GUEST)) {
            $block = $this->getService('Infoblock')->getBlock($name, $role);
            if (null !== strstr($name, '_')) {
                $parts = explode('_', $name);
                if (count($parts) == 2) {
                    $name = trim($parts[0]);
                    $attribs['param'] = (int) $parts[1];
                }
            }
        }
        
        if ($block) {
            $this->view->name    = $name;
            $this->view->title   = $block['title'];
            if ($mode == 'edit') {
                $this->view->content = $block['description'];
                $this->view->name    = 'screenForm';
                $attribs['data-infoblock'] = $name;
                $attribs['id']             = $id;
            }
            $this->view->attribs = $attribs;
        } else {
			
            $this->getResponse()->setHttpResponseCode(404);
            Zend_Registry::get('log_system')->debug("Infoblock '$name' not found under role '$role'");
        }
    }
}