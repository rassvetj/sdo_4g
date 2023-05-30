<?php
class Orgstructure_IndexController extends HM_Controller_Action_Orgstructure
{
    protected $_adapter;
    protected $_childrenMap = array();
    
    public function indexAction()
    {
        $orgId = (int) $this->_getParam('org_id', 0);

        $item = $this->getOne($this->getService('Orgstructure')->findDependence('User', $orgId));

        $this->view->item = $item;
    }
    
    // восстанавливет left-right-level по owner_soid
    public function repairAction()
    {
        $this->_adapter = $this->getService('User')->getMapper()->getAdapter()->getAdapter();
        
        $select = $this->getService('Orgstructure')->getSelect()->from('structure_of_organ', array('soid', 'owner_soid'));
        $rowset = $select->query()->fetchAll();
        foreach($rowset as $row) {
        
            if (++$_childrenMapCount%1000 == 0) Zend_Registry::get('log_system')->log('children map loop: ' . $_childrenMapCount, Zend_Log::ERR);
        
            if (!isset($this->_childrenMap[$row['owner_soid']])) {
                $this->_childrenMap[$row['owner_soid']] = array($row['soid']);
            } else {
                $this->_childrenMap[$row['owner_soid']][] = $row['soid'];
            }
        }
        
        Zend_Registry::get('log_system')->log('recursive update start', Zend_Log::ERR);
        if (count($this->_childrenMap[0])) {
            $left = 0;
            foreach ($this->_childrenMap[0] as $soid) {
                $left = $this->_update($soid, ++$left);
            }
        }
        
        
        $this->_flashMessenger->addMessage(array(
            'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
            'message' => _('Оргструктура восстановлена')
        ));
        $this->_redirector->gotoSimple('index', 'list', 'orgstructure');
        
    }
    
    protected function _update($soid, $left = 1, $level = 0, $ownerSoid = 0)
    {
        static $_updateCount;
        if (++$_updateCount%1000 == 0) Zend_Registry::get('log_system')->log('recursive update loop: ' . $_updateCount, Zend_Log::ERR);
    
        $right = $left + 1;
        if (is_array($this->_childrenMap[$soid]))
        foreach($this->_childrenMap[$soid] as $childSoid) {
            $right = $this->_update($childSoid, $right, $level + 1, $soid);
        }
    
        $this->_adapter->query("UPDATE structure_of_organ SET lft={$left}, rgt={$right}, level={$level} WHERE soid={$soid}");
        return ++$right;
    }    
}