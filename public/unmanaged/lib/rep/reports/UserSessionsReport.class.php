<?php
/**
* Отчет по обучаемым - сводные - группы
*/

class CUserSessionsReport extends CReportData {
    
    function getReportData() {

            $inputData = $this->getInputData();
            
            if ($inputData['start']) {

                $date = explode('.',$inputData['start']);
                if ((count($date)==3) && checkdate($date[1],$date[0],$date[2])) $start = $date[2].'-'.$date[1].'-'.$date[0].' 00:00:00';
                                
            }

            if ($inputData['stop']) {

                $date = explode('.',$inputData['stop']);
                if ((count($date)==3) && checkdate($date[1],$date[0],$date[2])) $stop = $date[2].'-'.$date[1].'-'.$date[0].' 23:59:59';
                                
            }    
            
            if (isset($start)) {
                $start = strtotime($start);
                $where = "UNIX_TIMESTAMP(sessions.start) >= '$start' ";
            }
            if (isset($stop)) {
                $stop = strtotime($stop);
                if (isset($start)) $where .= ' AND ';
                $where .= "UNIX_TIMESTAMP(sessions.stop) <= '$stop' ";
            }        
            
            if (isset($where)) $where = ' WHERE '.$where;
            
            $sql = "SELECT UNIX_TIMESTAMP(sessions.start) AS start, UNIX_TIMESTAMP(sessions.stop) AS stop, sessions.ip, sessions.mid, People.Login
                    FROM sessions LEFT JOIN People ON (sessions.mid=People.MID)
                    $where
                    ".$this->getSQLOrderString(array('start','stop'));
            $res = sql($sql);
            
            if (sqlrows($res)) {
                
                $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);                
             
                while($row = sqlget($res)) {
                    
                    if (!$peopleFilter->is_filtered($row['mid']) && $row['Login']) continue;

                    if (!$row['Login']) {
                        $row['Login'] = sprintf(_('Удалённый пользователь (id: %s)'), $row['mid']);
                    }
                    $row['start'] = date("d.m.Y H:i:s",$row['start']);
                    $row['stop'] = date("d.m.Y H:i:s",$row['stop']);
                    
                    $this->data[] = $row;
                
                }   
                
            }
            
            // ОБЯЗАТЕЛЬНО!! ВЫПОЛНИТЬ ФУНКЦИЮ ПРЕДКА
            $this->data = parent::getReportData($this->data);
            
            return $this->data;
            
    }
    
    
    /**
    * Функция должна возвращать массив:
    */
    function getReportInputField($inputFieldName,$inputFieldData=false) {
        
        $ret = '';
        
                
        return $ret;
    }
    
}


?>