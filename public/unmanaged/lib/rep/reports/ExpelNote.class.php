<?php
/**
*            -     -    
*/

require_once($sitepath.'metadata.lib.php');

class CExpelNote extends CReportData {
    
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
        //специальность
        $sql = "SELECT tm.level, tm.started, t.name, p.FirstName, p.LastName, p.Patronymic, p.Information
                FROM tracks2mid tm
                LEFT JOIN People p ON p.mid = tm.mid
                LEFT JOIN tracks t ON t.trid = tm.trid
                WHERE tm.mid IN ('".implode("','",$mids)."')";
        $res = sql($sql);
        $students   = array();
        $year2level = array();
        $level = 0;
        while ($row = sqlget($res)) {
            //специальность
            $track = $row['name'];
            //учебный год
            $date = getdate($row['started'] + ($row['level']) * 15768000); // 15768000 - секунд в семестре ;)
            $year2level[$row['level']] = ($date['mon']>6)?$date['year']." / ".($date['year']+1):($date['year']-1)." / ".$date['year'];
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
                        
            $students[$row['level']][] = $row['LastName'].'&nbsp;'.$oldLastName.$row['FirstName'].'&nbsp;'.$row['Patronymic'];
        }
        
        $smarty = new Smarty_els();
        $smarty->assign('items', $students);
        $smarty->assign('track', $track);
        $smarty->assign('years', $year2level);
        $result['students'] = $smarty->fetch('ExpelNote_students.tpl');
        
        

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
        
        $ret = array();
        
        switch($inputFieldName) {
            
            case 'MID':
               
                $sql = "SELECT People.MID, People.LastName, People.FirstName, People.Patronymic, People.Login
                        FROM tracks2mid tm
                        LEFT JOIN People ON (People.MID=tm.mid)
                        WHERE tm.trid = '".(int) $inputFieldData['track']."'
                        ORDER BY LastName, FirstName, Login";
                $res = sql($sql);                
                while($row = sqlget($res)) {
                    if (!$peopleFilter->is_filtered($row['MID'])) continue;                    
                    $ret[$row['MID']] = $row['LastName'].' '.$row['FirstName'].(strlen($row['Patronymic']) ? ' '.$row['Patronymic'] : '').' ('.$row['Login'].') ';
                                                            
                }                            
            break;
            
            case 'track':
                $sql = "SELECT trid, name FROM tracks";
                $res = sql($sql);
                $ret[0] = _('Выберите элемент');
                while($row = sqlget($res)) {
                    $ret[$row['trid']] = $row['name'];
                }
            break;
                
        }        
        
        return $ret;
    }
    
}




?>