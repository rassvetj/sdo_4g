<?php
/**
* Отчет по обучаемым - сводные - группы
*/

class CScormStudySummaryReport extends CReportData {
    
    function getReportData() {
        
            $inputData = $this->getInputData();
            
            $sql = "
                SELECT mod_content.Title, COUNT(scorm_tracklog.trackID) as runs,
                AVG(UNIX_TIMESTAMP(scorm_tracklog.stop)-UNIX_TIMESTAMP(scorm_tracklog.start)) as time
                FROM scorm_tracklog INNER JOIN mod_content ON (scorm_tracklog.McID=mod_content.McID AND 
                scorm_tracklog.ModID=mod_content.ModID)
                WHERE scorm_tracklog.cid='".(int) $inputData['CID']."'
                GROUP BY mod_content.Title
            ";
            
            $res = sql($sql);
            
            if (sqlrows($res)) {
             
                while($row = sqlget($res)) {
                    
                    $row['time'] = date('H:i:s',mktime(0,0,$row['time']));
                                        
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
                        
            case 'CID':
            
                $courseFilter = new CCourseFilter($GLOBALS['COURSE_FILTERS']);
                        
                $sql = "SELECT Courses.CID, Courses.Title 
                        FROM Courses INNER JOIN Students ON (Courses.CID=Students.CID)
                        ORDER BY Title";
                $res = sql($sql);
                
                while($row = sqlget($res)) {
                    
                    if (!$courseFilter->is_filtered($row['CID'])) continue;
                    $ret[$row['CID']] = $row['Title'];
                    
                }

            break;            
            
            
        }        
        
        return $ret;
    }
    
}


?>