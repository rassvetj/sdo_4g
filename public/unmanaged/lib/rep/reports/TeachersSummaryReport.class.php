<?php
/**
* Отчет по обучаемым - сводные - группы
*/

require_once($sitepath.'metadata.lib.php');

class CTeachersSummaryReport extends CReportData {
    
    function getReportData() {
    
            $sql = "SELECT People.MID, People.Login, People.EMail, CONCAT(CONCAT(CONCAT(CONCAT(People.LastName,' '),People.FirstName),' '), People.Patronymic) AS FIO, COUNT(Teachers.CID) AS Courses 
                    FROM People INNER JOIN Teachers ON (People.MID=Teachers.MID) 
                    GROUP BY People.MID, People.Login, People.LastName, People.FirstName, People.Patronymic, People.EMail ".$this->getSQLOrderString();

            $res = sql($sql);
            
            if (sqlrows($res)) {
                
                $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);
             
                while($row = sqlget($res)) {
                    if (!$peopleFilter->is_filtered($row['MID'])) continue;
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

    }
    
}


?>