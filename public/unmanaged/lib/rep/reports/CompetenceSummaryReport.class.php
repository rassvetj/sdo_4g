<?php

require_once('competence.lib.php');

class CCompetenceSummaryReport extends CReportData {
    
    function getReportData() {
        
            $inputData = $this->getInputData();
            
            $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);
            
            /**
            * Работа с группой
            */
            if ($inputData['gid'] && ($inputData['gid']!='-1')) {
                
                if ($group = $inputData['gid']) {
                    
                    $grType = (int) $group[0];
                    $grId = substr($group,2);

                    switch($grType) {
                    case 1:
                    $sql = "SELECT DISTINCT
                                People.MID
                            FROM
                                Students
                            INNER JOIN People ON (People.`MID` = Students.`mid`) 
                            WHERE Students.cgid='".$grId."' ";
                                
                    break;
                    case 2:
                    $sql = "SELECT DISTINCT
                                People.MID
                            FROM
                                Students
                            INNER JOIN People ON (Students.`MID` = People.`mid`)
                            INNER JOIN groupuser ON (People.MID = groupuser.mid)
                            WHERE groupuser.gid='".$grId."' ";
                    break;
                
                    }
                    
                    $res = sql($sql);
                                           
                    while($row = sqlget($res)) {                        
                        $mids[] = (int) $row['MID'];                                                
                    }
                    
                    
                }
                
            }
            
            $arrPeople = getPeopleByCompetences(array($inputData['coid']), array(0));

            if (is_array($arrPeople) && count($arrPeople)) {
                foreach($arrPeople as $k=>$v) {
                    
                    $sql = "SELECT name,owner_soid FROM structure_of_organ WHERE mid='".$v['people_mid']."'";
                    $res = sql($sql);
                    while($row = sqlget($res)) {
                        $v['position'] .= $row['name'].'<br>';                        
                        if ($row['owner_soid']>0) {
                            $sql = "SELECT name FROM structure_of_organ WHERE soid='".$row['owner_soid']."'";
                            $res = sql($sql);
                            while($row = sqlget($res))
                                $v['orgunit'] .= $row['name'].'<br>';
                        }
                    }                    
                    
                    $v['sum'] = round($v['sum']);
                    if (is_array($mids) && count($mids)) {
                        if (in_array($v['people_mid'],$mids) && $peopleFilter->is_filtered($v['people_mid']))
                            $this->data[] = $v;                        
                    } else {
                        if ($peopleFilter->is_filtered($v['people_mid']))
                            $this->data[] = $v;
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
            
            case 'coid':
            
                $sql = "SELECT coid,name FROM competence ORDER BY name";
                $res = sql($sql);
                
                while($row = sqlget($res)) {
                    
                    $ret[$row['coid']] = $row['name'];
                    
                }

            break;
            
            case 'gid':
            
                $ret[0] = _("Все");
                
                $sql = "SELECT * FROM cgname ORDER BY cgid";
                $res = sql($sql);
                while($row = sqlget($res)) {
                    
                    $v = '1_'.$row['cgid'];
                    $ret[$v] = $row['name'];
                                                            
                }
                
                $sql = "SELECT * FROM groupname ORDER BY name";
                $res = sql($sql);

                //if (sqlrows($res) && isset($row)) $ret['-1'] = "---";

                while($row = sqlget($res)) {

                    $v = '2_'.$row['gid'];
                    $ret[$v] = $row['name'];
                    
                }            
            
            break;
                        
        }
        
        return $ret;

    }
    
}


?> 
