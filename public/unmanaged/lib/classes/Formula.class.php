<?php

/**
 * CFormula class
 * 
 * @author Yuri Novitsky
 * @package eLearning Server
 */
class CFormula extends CDBObject {
    
    var $table = 'formula';
        
    function getFormulaConditions() { // пока разбор только для формулы типа 6
        $conditions = array();
        if (!empty($this->attributes['formula'])) {
            $lines = str_replace(array("\n","\r"),'',$this->attributes['formula']);
            $lines = explode(";",$lines);
            foreach($lines as $line) {
                $parts = explode(':',$line);
                if (count($parts)==2) {
                    $condition['condition'] = $parts[0];
                    $condition['value'] = $parts[1];
                    $conditions[] = $condition;
                }
            }
        }
        return $conditions;
    }
    
    function get($formula) {
        $sql = "SELECT * FROM formula WHERE id='".(int) $formula."'";
        $res = sql($sql);
        
        while($row = sqlget($res)) {
            return new CFormula($row);
        }
        
        return false;
    }
    
    function get_as_array($type=0) {
        $where = ''; $rows = array();
        
        if ($type) {
            $where = "WHERE type='".(int) $type."'";
        }
        
        $sql = "SELECT * FROM formula $where ORDER BY name";
        $res = sql($sql);
        
        while($row = sqlget($res)) {
            $rows[] = new CFormula($row);
        }
        return $rows;
    }
        
}

?>