<?php
class CScheduleAssign extends CDBObject {
    var $table = 'scheduleID';
    
    function getParams($strParams) {
        $params = array();
        
        $arrParams = explode(';', $strParams);
        if (is_array($arrParams) && count($arrParams)) {
            foreach($arrParams as $param) {
                $param = explode('=', $param);
                if (count($param) == 2) {
                    $params[trim($param[0])] = trim($param[1]);
                }
            }
        }
        
        return $params;
    }   
    
    function get($id, $mid) {
        $sql = "SELECT * FROM scheduleID WHERE SHEID = '".(int) $id."' AND MID ='".(int) $mid."'";
        $res = sql($sql);
        
        if ($row = sqlget($res)) {
            $row['toolParams'] = CScheduleAssign::getParams($row['toolParams']);
            return new CScheduleAssign($row);
        }
    }
        
    function getTests() {
        $tests = array();
        if (($this->attributes['SHEID']) && isset($this->attributes['toolParams']['tests_testID'])) {
            foreach(explode('#', $this->attributes['toolParams']['tests_testID']) as $testId) {
                if ($test = CTask::get($testId)) {
                    $tests[$testId] = $test;
                }
            }            
        }
        
        return $tests;
    }
    
}

class CSchedule extends CDBObject {
    var $table = 'schedule';
    
    function get($id, $mid = 0) {
        $schedule = parent::get(array('name' => 'SHEID', 'value' => $id), 'schedule', 'CSchedule');
        
        if ($schedule && $mid) {
            $schedule->attributes['assign'] = CScheduleAssign::get($schedule->attributes['SHEID'], $mid);
        }
        
        return $schedule;
    }
    
    function getAssign($mid) {
        if ($this->attributes['SHEID']) {
            return CScheduleAssign::get($this->attributes['SHEID'], $mid);
        }
    }

    function getTests() {
        $tests = array();
        if (($this->attributes['SHEID']) && isset($this->attributes['assign'])) {
            $tests = $this->attributes['assign']->getTests();
        }
        
        return $tests;
    }    
}
?>