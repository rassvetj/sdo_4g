<?php
/**
 * Description of MssqlviewStorage
 *
 * @author slava
 */
class Es_Service_Storage_MssqlviewStorage extends Es_Service_Storage_AbstractSqlstorage {
    
    public function _pull(\Es_Entity_AbstractFilter $filter) {
        /**
         * pull data from VIEW
         */
    }

    public function _push(\Es_Entity_AbstractEvent $event) {
        /**
         * blackhole, data pull from VIEW
         */
    }
    
    public function _remove(\Es_Entity_AbstractFilter $filter) {
        
    }

    public function _unsubscribe(\Es_Entity_AbstractFilter $filter) {
        
    }

    public function _getEventTypesList() {
        /**
         * implement me
         */
    }

    public function _getGroupByUniqueName($type, $triggerInstanceId) {
    
    }

    public function _createGroup(Es_Entity_AbstractGroup $group) {
    
    }
    
    public function _pullStats(\Es_Entity_AbstractFilter $filter) {
        
    }
    
    public function _pullNotifies(\Es_Entity_AbstractFilter $filter) {
        
    }
    
    public function _updateNotify(\Es_Entity_AbstractNotify $notify) {
        
    }
    
}

?>
