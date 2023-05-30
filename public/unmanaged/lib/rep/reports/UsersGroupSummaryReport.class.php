<?php
/**
* Отчет по обучаемым - сводные - группы
*/

class CUsersGroupSummaryReport extends CReportData {
    
    function getReportData() {
    
            if ($group = $this->getInputData(array('gid'))) {
                $grType = (int) $group['gid'][0];
                $grId = substr($group['gid'],2);
            }
            

            switch($grType) {
                case 1:
                $sql = "SELECT DISTINCT
                            People.`MID` as MID,
                            CONCAT(CONCAT(CONCAT(CONCAT(People.LastName,' '),People.FirstName),' '),People.Patronymic) AS FIO,
                            People.Login as Login,
                            People.EMail as Email
                        FROM
                            Students
                        INNER JOIN People ON (People.`MID` = Students.`mid`) 
                        WHERE Students.cgid='".$grId."' ".$this->getSQLOrderString();
                                
                break;
                case 2:
                $sql = "SELECT DISTINCT
                            People.`MID` as MID,
                            CONCAT(CONCAT(CONCAT(CONCAT(People.LastName,' '),People.FirstName),' '),People.Patronymic) AS FIO,
                            People.Login as Login,
                            People.EMail as Email
                        FROM
                            Students
                        INNER JOIN groupuser ON (Students.MID = groupuser.mid)
                        INNER JOIN People ON (People.`MID` = Students.`mid`)
                        WHERE groupuser.gid='".$grId."' ".$this->getSQLOrderString();
                break;
                default:
                $sql = "";
            }
            
            if (strlen($sql)) {
                $res = sql($sql);
                            
                if (sqlrows($res)) {
                    
                    $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);
                 
                    while($row = sqlget($res)) {
                        if (!$peopleFilter->is_filtered($row['MID'])) continue;
                        $this->data[] = $row;
                    
                    }   
                    
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
        
        switch($inputFieldName) {
            
            case 'gid':
            
                $groupFilter = new CGroupFilter_Department();
                
                $sql = "SELECT * FROM cgname ORDER BY cgid";
                $res = sql($sql);
                while($row = sqlget($res)) {
                    if (!$groupFilter->is_filtered($row['cgid'])) {
                        continue;
                    }
                    $v = '1_'.$row['cgid'];
                    $ret[$v] = $row['name'];                    
                                                            
                }
                
                $sql = "SELECT * FROM groupname ORDER BY name";
                $res = sql($sql);

                //if (sqlrows($res) && isset($row)) $ret['-1'] = "---";

                while($row = sqlget($res)) {
                    if (!$groupFilter->is_filtered($row['gid'])) {
                        continue;
                    }
                    $v = '2_'.$row['gid'];
                    $ret[$v] = $row['name'];
                    
                }            
            
            break;
            
            
        }
        
        /*
                
        switch($inputFieldName) {
                                    
            case 'gid':
                $ret .= "<select name=\"$inputFieldName\">";
                
                $sql = "SELECT * FROM cgname ORDER BY cgid";
                $res = sql($sql);
                while($row = sqlget($res)) {
                    
                    $ret .= "<option value=\"1_{$row['cgid']}\" ";
                    $v = '1_'.$row['cgid'];
                    if (isset($inputFieldData) && ($inputFieldData!==false) && ($inputFieldData==$v)) $ret.="selected";
                    $ret .= "> {$row['name']}</option>";
                    
                    $inputDataValues[$v] = $row['name'];
                    
                }
                
                $sql = "SELECT * FROM groupname ORDER BY name";
                $res = sql($sql);
                if (sqlrows($res) && isset($row)) $ret.="<option> ---</option>";
                while($row = sqlget($res)) {
                    
                    $ret .= "<option value=\"2_{$row['gid']}\" ";
                    $v = '2_'.$row['gid'];
                    if (isset($inputFieldData) && ($inputFieldData!==false) && ($inputFieldData==$v)) $ret.="selected";
                    $ret .= "> {$row['name']}</option>";
                    
                    $inputDataValues[$v] = $row['name'];
                }
                
                $ret .= "</select>";
            break;
            
        }
        
        $out['html'] = $ret;
        $out['inputDataValues'] = $inputDataValues;
        
        */
        
        return $ret;
    }
    
}


?>