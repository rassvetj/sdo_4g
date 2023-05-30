<?php
/**
*            -     -    
*/

require_once($sitepath.'metadata.lib.php');

class CLevelUpNote extends CReportData {
    
    function getReportData() {
        //необходимое условие: 1курс - 1человек - 1экзамен        
        $inputData = $this->getInputData();
        $mids      = $inputData['MID'];
        $dummy     = array_walk($mids,'intval');
        $result    = array();
        
        //дата
        //факультет
        $result['date'] = date('d.m.Y',time());
        $result['department'] = 'ДУ';
        
        //студенты
        $sql = "SELECT tm.level, p.FirstName, p.LastName, p.Patronymic, p.Information
                FROM tracks2mid tm
                LEFT JOIN People p ON p.mid = tm.mid
                WHERE tm.mid IN ('".implode("','",$mids)."')";
        $res = sql($sql);
        $students = array();
        $level = 0;
        while ($row = sqlget($res)) {
            //старая фамилия
            $metadataTypes = explode(';',REGISTRATION_FORM);
            if (is_array($metadataTypes) && count($metadataTypes)) {
                foreach($metadataTypes as $metadataType) {
                    $metadata = read_metadata (stripslashes($row['Information']), $metadataType);
                    foreach ($metadata as $meta){
                        if ($meta['name'] == 'old_last_name') {
                            $oldLastName = $meta['value']?'('.$meta['value'].') ':'';    
                        }
                    }                    
                }
            }
                        
            if (is_int($level = $row['level']/2)) $students[$level][] = $row['LastName'].'&nbsp;'.$oldLastName.$row['FirstName'].'&nbsp;'.$row['Patronymic'];
        }
        
        $smarty = new Smarty_els();
        $smarty->assign('items', $students);
        $result['students'] = $smarty->fetch('LevelUpNote_students.tpl');
        
        

            $this->data[] = $result;           
            
            //       !!              
            $this->data = parent::getReportData($this->data);
            
            return $this->data;
            
    }
    
    
    /**
    * Функция должна возвращать массив:
    */
    function getReportInputField($inputFieldName,$inputFieldData=false) {
        
        $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);
        
        $ret = '';
        
        switch($inputFieldName) {
            
            case 'MID':
               
                $sql = "SELECT People.MID, People.LastName, People.FirstName, People.Patronymic, People.Login 
                        FROM tracks2mid tm
                        LEFT JOIN People ON (People.MID=tm.mid)
                        WHERE tm.level>0
                        ORDER BY LastName, FirstName, Login";
                $res = sql($sql);
                while($row = sqlget($res)) {
                    if (!$peopleFilter->is_filtered($row['MID'])) continue;
                    $v = $row['MID'];
                    $ret[$v] = $row['LastName'].' '.$row['FirstName'].(strlen($row['Patronymic']) ? ' '.$row['Patronymic'] : '').' ('.$row['Login'].') ';
                                                            
                }                            
                break;
                
        }        
        
        return $ret;
    }
    
}




?>