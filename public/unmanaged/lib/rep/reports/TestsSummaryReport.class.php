<?php
/**
* Отчет по обучаемым - сводные - группы
*/

require_once($sitepath.'metadata.lib.php');

class CTestsSummaryReport extends CReportData {
    
    function getReportData() {
    
            $inputData = $this->getInputData();
            
            $sql = "SELECT test.tid, test.title, COUNT(loguser.stid) as runs, 
            AVG(loguser.stop-loguser.start) as avgtime,
            AVG((loguser.bal*100) / CASE WHEN loguser.balmax2>0 THEN (loguser.balmax2-loguser.balmin2) ELSE 1 END) AS avgprocent,
            AVG(bal) as avgbal
            FROM test LEFT JOIN loguser ON (test.tid=loguser.tid)
            WHERE test.cid='".$inputData['CID']."'
            GROUP BY test.tid, test.title
            ";
            
            $res = sql($sql);
            
            if (sqlrows($res)) {
             
                while($row = sqlget($res)) {                                     
                    
                    $sql = "SELECT test.data FROM test WHERE test.tid = '".(int) $row['tid']."'";
                    $res2 = sql($sql);
                    $row['questions'] = 0;
                    if (sqlrows($res2)) {
                        
                        $row2 = sqlget($res2);
                    
                        $tmp = explode('~~',$row2['data']);
                        foreach($tmp as $k=>$v) {
                            if (strpos($v,"%")!==false || strpos($v,"_")!==false) {
                                $query[]=" kod LIKE '".ad($v)."'";
                                unset($tmp[$k]);
                            }
                        }
                        if (is_array($query) && count($query)) {
                            
                            $sql = "SELECT COUNT(kod) AS count FROM list WHERE ".implode(" OR ",$query);
                            $res = sql($sql);
                            if (sqlrows($res)) $row_tmp = sqlget($res);
                            $row['questions'] = $row_tmp['count'];
                            
                        }
                    }
                    if ($tmp[0]) $row['questions'] += count($tmp);
                    
//                    pr($row['avgtime']);
//                    pr($row['avgtime']/60);
                    $row['avgprocent'] = round($row['avgprocent']);
                    $row['avgbal'] = round($row['avgbal']);
                    $row['avgtime'] = date('H:i:s',mktime(0,0,$row['avgtime']));
                    
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
            
                $sql = "SELECT CID, Title FROM Courses ORDER BY Title";
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