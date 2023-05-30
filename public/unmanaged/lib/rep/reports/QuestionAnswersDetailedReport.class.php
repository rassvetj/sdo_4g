<?php
/**
* Отчет по обучаемым - сводные - группы
*/

require_once($sitepath.'metadata.lib.php');

class CQuestionAnswersDetailedReport extends CReportData {
    
    function getReportData() {
    
            $inputData = $this->getInputData();
            
            $sql = "SELECT otvet FROM logseance WHERE kod='".$inputData['kod']."'";
            
            $res = sql($sql);
                        
            while($row = sqlget($res)) {
                
                $otvet = unserialize($row['otvet']);

                if (is_array($otvet) && count($otvet) && is_array($otvet['main']))
                foreach($otvet['main'] as $k=>$v) {
                    
                    $data[$v]['good'] = $otvet['good'][$k];
                    $data[$v]['count']++;
                    
                }                
                
            }
            
            if (is_array($data) && count($data)) {
                
                foreach($data as $k=>$v) {
                    
                    $rs['text'] = $k;
                    
                    $rs['true'] = _("нет");
                    if ($v['good']) $rs['true'] = _("да");
                    
                    $rs['count'] = $v['count'];
                    
                    $this->data[] = $rs;
                    
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

        $courseFilter = new CCourseFilter($GLOBALS['COURSE_FILTERS']);

        switch($inputFieldName) {
                        
            case 'CID':                
            
                $sql = "SELECT CID, Title FROM Courses ORDER BY Title";
                $res = sql($sql);
                
                while($row = sqlget($res)) {
                    
                    if (!$courseFilter->is_filtered($row['CID'])) continue;
                    
                    $ret[$row['CID']] = $row['Title'];
                    
                }

            break;
            
            case 'tid':
            
                if (!$inputFieldData['CID']) {

                    $sql = "SELECT CID, Title FROM Courses ORDER BY Title";
                    $res = sql($sql);
                
                    if (sqlrows($res)) {
                        
                        while($row = sqlget($res)) {
                            if ($courseFilter->is_filtered($row['CID']))
                                $inputFieldData['CID'] = $row['CID'];
                        }
                    }
                    
                }
                
                $sql = "SELECT title,tid FROM test WHERE cid='".(int) $inputFieldData['CID']."' ORDER BY title";
                $res = sql($sql);
                
                while($row = sqlget($res)) {
                    
                    $ret[$row['tid']] = $row['title'];
                    
                }
                
            
            break;

            case 'kod':
            
                if (!$inputFieldData['CID']) {

                    $sql = "SELECT CID, Title FROM Courses ORDER BY Title LIMIT 1";
                    $res = sql($sql);
                
                    if (sqlrows($res)) {
                        
                        $row = sqlget($res);
                        $inputFieldData['CID'] = $row['CID'];
                        
                    }
                    
                    $sql = "SELECT tid FROM test WHERE cid='".(int) $inputFieldData['CID']."' ORDER BY title LIMIT 1";
                    $res = sql($sql);
                
                    if (sqlrows($res)) {
                        
                        $row = sqlget($res);
                        if (isset($row['TID'])) $row['tid'] = $row['TID'];
                        $inputFieldData['tid'] = $row['tid'];
                                        
                    }
                    
                    
                }
                                
                if (!$inputFieldData['tid']) {
                    
                    $sql = "SELECT tid FROM test WHERE cid='".(int) $inputFieldData['CID']."' ORDER BY title LIMIT 1";
                    $res = sql($sql);
                
                    if (sqlrows($res)) {
                        
                        $row = sqlget($res);
                        if (isset($row['TID'])) $row['tid'] = $row['TID'];
                        $inputFieldData['tid'] = $row['tid'];
                                        
                    }
                    
                    
                }
                                
                $sql = "SELECT data FROM test WHERE tid='".(int) $inputFieldData['tid']."' AND cid='".(int) $inputFieldData['CID']."'";
                $res = sql($sql);
                                
                if (sqlrows($res)) {
                    
                    $row = sqlget($res);
                    
                    $q = explode('~~',$row['data']);
                    foreach($q as $k=>$v) {
                        if (strpos($v,"%")!==false || strpos($v,"_")!==false) {
                            $query[]=" kod LIKE '".ad($v)."'";
                            unset($tmp[$k]);
                        }
                    }

                    if (is_array($query) && count($query)) {
                            
                        $maskkods = ' OR '.implode(" OR ",$query);                        
                            
                    }
                    
                    $kods = "'".join("','",$q)."'";
                    
                }
                
                if ($kods) {
                
                $sql = "SELECT kod, qdata FROM list WHERE kod IN ($kods) {$maskkods}";
                $res = sql($sql);
                                
                while($row = sqlget($res)) {
                    
                    $qdata = explode('~~',$row['qdata']);
                    $ret[$row['kod']] = $qdata[0];
                    
                }
                
                }
                
            
            break;
            
        }

        return $ret;
    }
    
}


?>