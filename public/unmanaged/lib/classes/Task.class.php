<?php

class CTaskResult extends CDBObject {
    var $table = 'loguser';
    
}

class CTask extends CDBObject {
    var $table = 'test';        
    
    function getList($ids = array()) {
        $ret = array();
        $sql = "SELECT * FROM test ";
        if (is_array($ids) && count($ids)) {
            $sql .= "WHERE tid IN ('".join("','", $ids)."') ";
        }
        $sql .= "ORDER BY title";
        $res = sql($sql);
        
        while($row = sqlget($res)) {
            $ret[$row['tid']] = new CTask($row); 
        }
        
        return $ret;
    }

    function get($id) {
        return parent::get(array('name' => 'tid', 'value' => $id), 'test', 'CTask');
    }
    
    function getStatus($mid, $sheid=0) {
        $status = -1;
        if ($lastResult = $this->getLastResult($mid, $sheid)) {
            $status = $lastResult->attributes['status'];
             
        }
        return $status;
    }
    
    function resultExists($mid, $sheid=0) {
        if ($this->attributes['tid']) {
            $sql = "SELECT COUNT(stid) FROM loguser WHERE tid = '".$this->attributes['tid']."' AND mid = '".(int) $mid."'".($sheid ? " AND sheid = '".(int) $sheid."'": '');
            return sqlvalue($sql);            
        }
    }
    
    function getResults($mid, $sheid=0) {
        $results = array();        
        if ($this->attributes['tid']) {
            $sql = "SELECT * FROM loguser WHERE tid = '".$this->attributes['tid']."' AND mid = '".(int) $mid."'".($sheid ? " AND sheid = '".(int) $sheid."'": '').' ORDER BY stid';
            $res = sql($sql);
            
            while($row = sqlget($res)) {
                $results[$row['stid']] = new CTaskResult($row);       
            }
        }
        return $results;
    }
    
    function getLastResult($mid, $sheid=0) {
        if ($this->attributes['tid']) {
            $sql = "SELECT * FROM loguser WHERE tid = '".$this->attributes['tid']."' AND mid = '".(int) $mid."'".($sheid ? " AND sheid = '".(int) $sheid."'": '').' ORDER BY stid DESC LIMIT 1';
            $res = sql($sql);
            
            while($row = sqlget($res)) {
                return new CTaskResult($row);       
            }
        }
    }    
    
}

?>