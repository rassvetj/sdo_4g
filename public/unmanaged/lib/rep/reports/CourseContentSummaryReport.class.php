<?php
/**
* Отчет по обучаемым - сводные - группы
*/

require_once($sitepath.'metadata.lib.php');

class CCourseContentSummaryReport extends CReportData {
    
    function getReportData() {
    
            $sql = "SELECT DISTINCT 
                        Courses.Title          AS title,
                        Courses.type           AS type,
                        COUNT(oTests.vol1)     AS tests,
                        COUNT(oRuns.vol2)      AS runs,
                        COUNT(oModules.module) AS modules
                    FROM organizations o
                    LEFT JOIN Courses ON (o.cid = Courses.CID) 
                    LEFT JOIN organizations oTests   ON (o.oid = oTests.oid AND oTests.vol1 > 0)
                    LEFT JOIN organizations oRuns    ON (o.oid = oRuns.oid AND oRuns.vol2 > 0)
                    LEFT JOIN organizations oModules ON (o.oid = oModules.oid AND oModules.module > 0)
                    WHERE Courses.Title IS NOT NULL
                    GROUP BY Courses.Title
                    ORDER BY Courses.Title ASC"; 

            $res = sql($sql);
            
            if (sqlrows($res)) {
                
                //$courseFilter = new CCourseFilter($GLOBALS['COURSE_FILTERS']);
             
                while($row = sqlget($res)) {
                    //if (!$courseFilter->is_filtered($row['CID'])) continue;
                    $row['type'] = ($row['type']) ? _('ресурс') : _('курс');
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