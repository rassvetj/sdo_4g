<?php
/**
 * Description of AbstractSqlstorage
 *
 * @author slava
 */
abstract class Es_Service_Storage_AbstractSqlstorage extends Es_Service_Storage_AbstractStorage {
    
    public function initConnection() {
        if ($this->getConnection() === null) {
            $config = $this->getConnectionConfig();
            if ($config === null) {
                throw new \Es_Exception_Runtime("Connection config does not defined!");
            }
            if ($config->useDefaultDbDriver == "1") {
                $this->setConnection(Zend_Db_Table_Abstract::getDefaultAdapter());
            } else {
                /**
                 * @todo connect through specific adapter
                 */
                throw new \Es_Exception_Runtime('Custom sql connection initiator do not implemented!');
            }
        }
    }

    /**
     * 
     * @param \Es_Entity_AbstractFilter $filter
     * @return type
     */
    public function pull(\Es_Entity_AbstractFilter $filter) {
        $sfEvent = $this->getEsEventDispatcher()->trigger(Es_Service_EventActor::EVENT_INTERNAL_PULL_PRE, $this, array(
            'groupOwner' => $filter
        ));
        return $this->_pull((($sfEvent->getReturnValue() !== null)?$sfEvent->getReturnValue():$filter));
    }

    public function push(\Es_Entity_AbstractEvent $event) {
        $sfEvent = $this->getEsEventDispatcher()->trigger(Es_Service_EventActor::EVENT_INTERNAL_PUSH_PRE, $this, array(
            'groupOwner' => $event,
        ));
        return $this->_push($sfEvent->getReturnValue());
    }
    
    public function remove(\Es_Entity_AbstractFilter $filter) {
        $this->getEsEventDispatcher()->trigger(Es_Service_EventActor::EVENT_INTERNAL_REMOVE_PRE, $this);
        return $this->_remove($filter);
    }
    
    public function unsubscribe(\Es_Entity_AbstractFilter $filter) {
        $result = $this->getEsEventDispatcher()->trigger(Es_Service_EventActor::EVENT_INTERNAL_UNSUBSCRIBE_PRE, $this,
            array('groupOwner' => $filter)
        );
        return $this->_unsubscribe($result->getReturnValue());
    }
    
    public function pullStats(\Es_Entity_AbstractFilter $filter) {
        $this->getEsEventDispatcher()->trigger(Es_Service_Dispatcher::EVENT_PULL_STATS_PRE, $this);
        return $this->_pullStats($filter);
    }

    public function getEventTypesList() {
        $this->getEsEventDispatcher()->trigger(Es_Service_EventActor::EVENT_INTERNAL_GET_EVENT_TYPES_LIST_PRE, $this);
        return $this->_getEventTypesList();
    }

    public function getGroupByUniqueName($type, $triggerInstanceId) {
        $this->getEsEventDispatcher()->trigger(Es_Service_EventActor::EVENT_INTERNAL_GET_GROUP_BY_NAME_PRE, $this);
        return $this->_getGroupByUniqueName($type, $triggerInstanceId);
    }

    public function createGroup(Es_Entity_AbstractGroup $group) {
        /**
         * @todo make connection by reference trigger
         */
        $this->initConnection();
        return $this->_createGroup($group);
    }
    
    public function pullNotifies(\Es_Entity_AbstractFilter $filter) {
        $this->getEsEventDispatcher()->trigger(Es_Service_EventActor::EVENT_INTERNAL_PULL_NOTIFIES_PRE, $this);
        return $this->_pullNotifies($filter);
    }
    
    public function updateNotify(\Es_Entity_AbstractNotify $notify) {
        $this->getEsEventDispatcher()->trigger(Es_Service_EventActor::EVENT_INTERNAL_UPDATE_NOTIFY_PRE, $this);
        return $this->_updateNotify($notify);
    }

    public function prePullListener() {
        return $this->connect();
    }

    public function prePushListener() {
        return $this->connect();
    }
    
    public function preRemoveListener() {
        return $this->connect();
    }
    
    public function preUnsubscribeListener() {
        return $this->connect();
    }

    public function preGetEventTypesListListener() {
        return $this->connect();
    }

    public function preGetGroupByUniqueNameListener() {
        return $this->connect();
    }
    
    public function prePullStatsListener() {
        return $this->connect();
    }

    public function preCreateGroupListener() {
        /**
         * @todo me
         */
    }
    
    public function prePullNotifies() {
        return $this->connect();
    }

    public function preUpdateNotify() {
        return $this->connect();
    }
    
    protected function connect() {
        return function($ev) {
            $storage = $ev->getSubject();
            $storage->initConnection();
        };
    }

    abstract function _pull(\Es_Entity_AbstractFilter $filter);
    abstract function _push(\Es_Entity_AbstractEvent $event);
    abstract function _remove(\Es_Entity_AbstractFilter $filter);
    abstract function _unsubscribe(\Es_Entity_AbstractFilter $filter);
    abstract function _getEventTypesList();
    abstract function _getGroupByUniqueName($type, $triggerInstanceId);
    abstract function _createGroup(Es_Entity_AbstractGroup $group);
    abstract function _pullStats(\Es_Entity_AbstractFilter $filter);
    abstract function _pullNotifies(\Es_Entity_AbstractFilter $filter);
    abstract function _updateNotify(\Es_Entity_AbstractNotify $notify);

}

?>
