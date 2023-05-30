<?php
/**
* Отчет по обучаемым - сводные - группы
*/
class CSpecialitySummaryReport extends CReportData {
    
    function getReportData() {
    
//                    LEFT JOIN tracks2mid ON (tracks2mid.trid=tracks.trid)
            $sql = "SELECT tracks.trid, tracks.name, tracks.totalcost, MAX(tracks2course.level) AS semestrs, 
                    COUNT(tracks2mid.trmid) AS students 
                    FROM tracks 
                    LEFT JOIN tracks2course ON (tracks.trid=tracks2course.trid)
                    LEFT JOIN tracks2mid ON (tracks2mid.trid=tracks.trid)
                    GROUP BY tracks.trid, tracks.name, tracks.totalcost ".$this->getSQLOrderString();

            $res = sql($sql);  
            $trackFilter = new CTrackFilter($GLOBALS['TRACK_FILTERS']);          
            if (sqlrows($res)) {             
                while($row = sqlget($res)) {
                    if (!$trackFilter->is_filtered($row['trid'])) continue;
                    $sql = "SELECT COUNT(tracks2mid.mid) AS students 
                            FROM tracks2mid INNER JOIN People ON (People.MID=tracks2mid.mid)
                            WHERE tracks2mid.trid='".(int) $row['trid']."'";
                    $res2 = sql($sql);
                    if (sqlrows($res2)) {
                        $row2=sqlget($res2); $row['students'] = $row2['students'];
                        if (defined('USE_BOLOGNA_SYSTEM') && USE_BOLOGNA_SYSTEM)
                            $row['credits'] = CCredits::countTrackCredits($row['trid']);
                    }
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