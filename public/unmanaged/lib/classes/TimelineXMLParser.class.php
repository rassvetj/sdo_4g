<?
class CTimelineXMLParser {
    var $strXML;
    var $schedule_class;
    var $shedules;
            
    function init_string($str) {
        $this->strXML = $str;
    }
        
    function _parse_array($blocks) {        
        static $id;
        static $items = array();
        
        if (count($blocks)>0) {
            foreach($blocks as $block) {
                switch($block['name']) {
                    case 'TIMELINE':
						switch ($block['attrs']['ID']){
							case 'container_relative':
								$this->schedule_class = "CTimelineSheduleRelative";
								break;
							default:
								$this->schedule_class = "CTimelineShedule";
								break;
						}
                        $this->_parse_array($block['children']);
                    break;
                    case 'ITEM':
                        $id = $block['attrs']['ID'];
                        if ($id > 0) {
                        	$class = $this->schedule_class;
                            $items[$id] = new $class();
                            $items[$id]->init(array($id,$block['attrs']['STARTDATE'],$block['attrs']['ENDDATE']));
                        }
                        $this->_parse_array($block['children']);
                    break;
                    case 'CONDITIONS':
                        if ($id > 0) {
                            $items[$id]->set_conditions_operation($block['attrs']['OPERATION']);
                        }
                        $this->_parse_array($block['children']);
                    break;
                    case 'CONDITION':
                        if ($id > 0) {
                            $condition = new CTimelineCondition();
                            $condition->init(array($block['attrs']['ID'],$block['attrs']['VALUE'],$block['attrs']['LINKWITH']));
                            $items[$id]->add_condition(&$condition);                            
                        }
                        $this->_parse_array($block['children']);
                    break;
                }
            }
        }
        
        return $items;
    }
        
    function parse() {
        if (!empty($this->strXML)) {
            $objXML = new xml2Array();
            $arrXML= $objXML->parse($this->strXML);
            if (is_array($arrXML) && count($arrXML)) {
                $this->shedules = $this->_parse_array($arrXML);
            }
        }
    }
    
    function update_shedules() {
        if (is_array($this->shedules) && count($this->shedules)) {
            while(list($k,$v) = each($this->shedules)) {
                $v->update_shedule();
            }
        }
    }

}

class CTimelineShedule {
    var $id;
    var $startdate;
    var $enddate;
    var $conditions_operation;
    var $conditions;    
    var $course_startdate;
    var $cid;
    
    function init($arr) {
        list($this->id, $this->startdate, $this->enddate) = $arr;
    }        
    
    function set_conditions_operation($op) {
        $this->conditions_operation = $op;
    }
    
    function add_condition($condition) {
        if (is_a($condition,'CTimelineCondition'))
            $this->conditions[] = $condition;
    }

    /**
    * @desc Возвращает дату начала курса в формате unixtimestamp
    */
    function _get_course_startdate() {
        if ($this->cid > 0) {
            $sql = "SELECT cBegin FROM Courses WHERE CID='".(int) $this->cid."'";
            $res = sql($sql);
            if (sqlrows($res)) {
                $row = sqlget($res); 
                $arrDate = explode('-',$row['cBegin']);
                return mktime(0,0,0,$arrDate[1],$arrDate[2],$arrDate[0]);
            }            
        }
    }
    
    function _get_shedule_info() {
        if ($this->id > 0) {
            $sql = "SELECT * FROM schedule WHERE SHEID='".(int) $this->id."'";
            $res = sql($sql);
            if (sqlrows($res)) {
                $row = sqlget($res);
                return $row;
            }
        }
    }
    
    function _get_shedule_cid() {
        $data = $this->_get_shedule_info();
        return $data['CID'];
    }
    
    function _update_conditions_sql() {
        if (is_array($this->conditions) && count($this->conditions)) {
            $progress = $avgbal = $sumbal = false;
            while(list($k,$v) = each($this->conditions)) {
                $v->value = trim($v->value);
                switch($v->id) {
                    case 1:
                        if (strpos($v->value,'-')===false) $v->value .= '-';
                        $cond_sheids[] = $v->linkwith;
                        $cond_marks[] = $v->value;
//                        $sql[] = "cond_sheid='".(($v->linkwith) ? $v->linkwith : -1)
//                                ."', cond_mark='".(($v->value) ? $v->value : '-')."-'";
                    break;
                    case 2:
                        if ($v->value) {
                            $progress = true;
                            $sql[] = "cond_progress='".$v->value."'";
                        }
                    break;
                    case 3:
                        if ($v->value) {
                            $avgbal = true;
                            $sql[] = "cond_avgbal='".$v->value."'";
                        }
                    break;
                    case 4:
                        if ($v->value) {
                            $sumbal = true;
                            $sql[] = "cond_sumbal='".$v->value."'";
                        }
                    break;
                }
            }
            
            if (!$progress) {
                $sql[] = "cond_progress='0'";
            }
            
            if (!$avgbal) {
                $sql[] = "cond_avgbal='0'";
            }
            
            if (!$sumbal) {
                $sql[] = "cond_sumbal='0'";
            }
            
            if (is_array($cond_sheids) && count($cond_sheids) && is_array($cond_marks) && count($cond_marks))
                $sql[] = "cond_sheid='".join('#',$cond_sheids)."', cond_mark='".join('#',$cond_marks)."'";
            else 
                $sql[] = "cond_sheid='-1', cond_mark='-'";
        } else {
            $sql[] = "cond_sheid='-1', cond_mark='-', cond_progress='0', cond_avgbal='0', cond_sumbal='0'";
        }
        if (is_array($sql) && count($sql)) return ', '.join(',',$sql);
    }
    
    function _update_shedule() {
		$iname++;
        if ($this->id > 0) {
            switch($this->conditions_operation) {
                case 'and':
                    $conditions_operation = 0;
                break;
                case 'or':
                    $conditions_operation = 1;
                break;
            }
			// чтоб коректно отображалось добавляем 1сек, (переводим время в полночь)		
			$datess = date('Y-m-d H:i:s',strtotime(substr($GLOBALS['adodb']->DBTimestamp(strtotime($this->enddate)),1,-1))+1);
            /*$sql = "UPDATE schedule 
                    SET begin = ".$GLOBALS['adodb']->DBTimestamp(strtotime($this->startdate)).", 
                        end=".$GLOBALS['adodb']->DBTimestamp(strtotime($this->enddate)).", 
                        cond_operation='".(int) $conditions_operation."'
                        ".$this->_update_conditions_sql()."
                    WHERE SHEID='".(int) $this->id."'";*/			
			$sql = "UPDATE schedule 
                    SET begin = ".$GLOBALS['adodb']->DBTimestamp(strtotime($this->startdate)).", 
                        end='".$datess."', 
                        cond_operation='".(int) $conditions_operation."'
                        ".$this->_update_conditions_sql()."
                    WHERE SHEID='".(int) $this->id."'";									
            sql($sql);
        }
    }      
    
    function _process_dates() {
        if ($this->course_startdate) {
            intval($this->startdate /= 1000);
            intval($this->enddate /= 1000);
            $this->enddate -= 1;
            $this->startdate = date('Y-m-d H:i:s',($this->course_startdate + $this->startdate));
            $this->enddate = date('Y-m-d H:i:s',($this->course_startdate + $this->enddate));            
        }
    }
    
    function update_shedule() {
        if ($this->id > 0) {
            $this->cid = $this->_get_shedule_cid();
            if ($this->cid) {
                $this->course_startdate = $this->_get_course_startdate();
                $this->_process_dates();
                pr($this);
                $this->_update_shedule();
            }
        }
    }    
}

class CTimelineSheduleRelative extends CTimelineShedule {
    
    function _process_dates() {
        intval($this->startdate /= 1000);
        intval($this->enddate /= 1000);
        $this->startdate += 1;
        $this->enddate += 59*60 + 59;
    }

    function _update_shedule() {
        if ($this->id > 0) {
            switch($this->conditions_operation) {
                case 'and':
                    $conditions_operation = 0;
                break;
                case 'or':
                    $conditions_operation = 1;
                break;
            }
            $sql = "UPDATE schedule 
                    SET startday=".$GLOBALS['adodb']->Quote($this->startdate).", 
                        stopday=".$GLOBALS['adodb']->Quote($this->enddate).", 
                        cond_operation='".(int) $conditions_operation."'
                        ".$this->_update_conditions_sql()."
                    WHERE SHEID='".(int) $this->id."'";
            sql($sql);
        }
    }  
}

class CTimelineCondition {
    var $id;
    var $value;
    var $linkwith;
    
    function init($arr) {
        list($this->id,$this->value,$this->linkwith) = $arr;
    }            
}

?>