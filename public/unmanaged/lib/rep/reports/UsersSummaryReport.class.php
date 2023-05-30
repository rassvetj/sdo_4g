<?php
/**
* Отчет по обучаемым - сводные - группы
*/

class CUsersSummaryReport extends CReportData {
    
    function getReportData() {

            $inputData = $this->getInputData();
            
            switch($inputData['role']) {
                
                case 0:
            $sql = "SELECT People.MID, People.Login, People.countlogin, People.last, CONCAT(CONCAT(CONCAT(CONCAT(People.LastName,' '),People.FirstName),' '), People.Patronymic) as FIO FROM People ".$this->getSQLOrderString();
                break;
                case 1:
            $sql = "SELECT DISTINCT People.MID, People.Login, People.countlogin, People.last, CONCAT(CONCAT(CONCAT(CONCAT(People.LastName,' '),People.FirstName),' '), People.Patronymic) as FIO
            FROM People INNER JOIN Students ON People.MID=Students.MID ".$this->getSQLOrderString();
                break;
                case 2:
            $sql = "SELECT DISTINCT People.MID, People.Login, People.countlogin, People.last, CONCAT(CONCAT(CONCAT(CONCAT(People.LastName,' '),People.FirstName),' '), People.Patronymic) as FIO
            FROM People INNER JOIN Teachers ON People.MID=Teachers.MID ".$this->getSQLOrderString();
                break;
                case 3:
            $sql = "SELECT DISTINCT People.MID, People.Login, People.countlogin, People.last, CONCAT(CONCAT(CONCAT(CONCAT(People.LastName,' '),People.FirstName),' '), People.Patronymic) as FIO
            FROM People INNER JOIN deans ON People.MID=deans.MID ".$this->getSQLOrderString();
                break;
                case 4:
            $sql = "SELECT DISTINCT People.MID, People.Login, People.countlogin, People.last, CONCAT(CONCAT(CONCAT(CONCAT(People.LastName,' '),People.FirstName),' '), People.Patronymic) as FIO
            FROM People INNER JOIN admins ON People.MID=admins.MID ".$this->getSQLOrderString();
                break;
                
            }
            
            
            $res = sql($sql);
            
            if (sqlrows($res)) {
                
                $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);
             
                while($row = sqlget($res)) {
                    
                    if (!$peopleFilter->is_filtered($row['MID'])) continue;
                    
                    if (isset($row['LAST'])) $row['last'] = $row['LAST'];
                
                    $timest = $row['last'];
                    if($timest){
                    $dsc = substr($timest, 6, 2)."-";
                    $dsc .= substr($timest, 4, 2)."-";
                    $dsc .= substr($timest, 0, 4)." ";
                    $dsc .= substr($timest, 8, 2).":";
                    $dsc .= substr($timest, 10, 2).":";
                    $dsc .= substr($timest, 12, 2);
                    }
                    else {
                        $dsc = "-";
                    }
                    $row['last'] = $dsc;
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
        
        switch($inputFieldName) {
            
            case 'role':
            
                $ret[0] = _("Все");
                $ret[] = _("Обучаемый");
                $ret[] = _("Преподаватель");
                $ret[] = _("Учебная администрация");
                $ret[] = _("Администратор");
            
            break;
            
            
        }
                
        return $ret;
    }
    
}


?>