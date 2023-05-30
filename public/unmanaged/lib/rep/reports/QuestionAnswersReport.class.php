<?php
/**
* Отчет по обучаемым - сводные - группы
*/

require_once($sitepath.'metadata.lib.php');

class CQuestionAnswersReport extends CReportData {
    
    function getReportData() {
    
            $inputData = $this->getInputData();
        
            $sql = "SELECT logseance.*, list.qdata
                    FROM logseance 
                    INNER JOIN list ON (logseance.kod=list.kod)
                    INNER JOIN loguser ON (logseance.stid=loguser.stid)
                    WHERE loguser.cid='".(int) $inputData['CID']."'
                    ";
                    
            $res = sql($sql);
            
            if (sqlrows($res)) {
             
                while($row = sqlget($res)) {
                    
                    // ORACLE FIX
                    if (isset($row['BAL'])) $row['bal'] = $row['BAL'];
                    if (isset($row['BALMAX'])) $row['balmax'] = $row['BALMAX'];
                                        
                    $qdata = explode('~~',$row['qdata']);
                    $row['q'] = $qdata[0];
                    $data[$row['q']]['q'] = $row['q'];
                    $data[$row['q']]['count']++;
                    if ($row['bal']==$row['balmax'])
                    $data[$row['q']]['true']++;
                    else
                    $data[$row['q']]['false']++;
                
                }   
                
                foreach($data as $v) {
                    
                    $v['count'] = (int) $v['count'];
                    $v['true'] = (int) $v['true'];
                    $v['false'] = (int) $v['false'];
                    
                    if ($v['count'])
                    $v['procent'] = round(($v['true']*100)/$v['count']);
                    else $v['procent'] = 0;
                    
                    $this->data[] = $v;
                    
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