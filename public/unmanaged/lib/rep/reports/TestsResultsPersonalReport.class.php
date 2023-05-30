<?php
/**
* Отчет по обучаемым - сводные - группы
*/

class CTestsResultsPersonalReport extends CReportData {
    
    function getReportData() {
        
            $inputData = $this->getInputData();
            
            $sql = "SELECT scheduleID.toolParams, Courses.Title
                    FROM scheduleID 
                    INNER JOIN Courses ON (Courses.CID = schedule.CID)
                    INNER JOIN schedule ON (schedule.SHEID = scheduleID.SHEID)
                    WHERE " . ($inputData['CID'] ? "schedule.CID='".(int) $inputData['CID']."' AND " : "") . "scheduleID.MID='".(int) $inputData['MID']."'";
            $res = sql($sql);
            
            if (sqlrows($res)) {
                while ($row1 = sqlget($res)) {
                
                if (preg_match("!tests_testID=([0-9]+)!",$row1['toolParams'],$matches)) $tid = $matches[1];
                if (preg_match("!tests_testID = ([0-9]+)!",$row1['toolParams'],$matches)) $tid = $matches[1];
                 
                if ($tid > 0) {
                    $sql = "SELECT * FROM loguser WHERE mid='".(int) $inputData['MID']."' AND tid='".(int) $tid."' ".$this->getSQLOrderString(array('stop'));
                                        
                    $res = sql($sql);
                    
                    while($row = sqlget($res)) {
                        
                        $row['stop'] = date('d.m.Y',$row['stop']);
                        
                        if ($row['balmax']) {
                            $row['procent'] = round(($row['bal']*100) / ($row['balmax']-$row['balmin']));
                        }
                        else {
                            $row['procent'] = 0;
                        }
                        
                        $this->additionalData[_("Максимально возможный бал")] = $row['balmax'];
                        
                        $row['course'] = $row1['Title'];
                        
                        $this->data[] = $row;
                        
                    }
                    
                }
                }
                
            }
                        
            // ОБЯЗАТЕЛЬНО!! ВЫПОЛНИТЬ ФУНКЦИЮ ПРЕДКА
            $this->data = parent::getReportData($this->data);
            
            return $this->data;
            
    }
    
    
    /**
    * Функция должна возвращать массив:
    */
    function getReportInputField($inputFieldName,$inputData=false,$where='') {
        
        $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);
        $ret = '';
        
        switch($inputFieldName) {
            
            case 'MID':
                            
                $sql = "SELECT People.MID, People.LastName, People.FirstName, People.Login 
                        FROM People 
                        INNER JOIN Students ON (People.MID=Students.MID)
                        $where
                        ORDER BY LastName, FirstName, Login";
                $res = sql($sql);
                while($row = sqlget($res)) {
                    
                    if (!$peopleFilter->is_filtered($row['MID'])) continue;
                    $v = $row['MID'];
                    $ret[$v] = $row['LastName'].' '.$row['FirstName'].' ('.$row['Login'].') ';
                                                            
                }
                            
            break;
            
            case 'CID':
                $ret[0] = "Все";
                $courseFilter = new CCourseFilter($GLOBALS['COURSE_FILTERS']);
                        
                if (!isset($inputData['MID']) || !$inputData['MID']) {
                    
                    $sql = "SELECT * FROM People 
                            INNER JOIN Students ON (People.MID=Students.MID)
                            ORDER BY LastName";
                    $res = sql($sql);
                    if (sqlrows($res)) {
                
                        $row = sqlget($res);
                        $mid = $row['MID'];
                    
                    }
                    sqlfree($res);
                    
                } else $mid = $inputData['MID'];
                

                
                $sql = "SELECT Courses.CID, Courses.Title 
                        FROM Courses INNER JOIN Students ON (Courses.CID=Students.CID)
                        WHERE Students.MID='{$mid}'
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

    function getSajaxFunctions() {
        return array('process_filter_MID');
    }
    
    
}

function process_filter_MID($search, $current=0) {
    $html = '';
/*    if ($current>0) {
        $sql = "SELECT People.MID, People.LastName, People.FirstName, People.Login
                FROM People
                WHERE MID='".(int) $current."'";
        $res = sql($sql);
        if ($row = sqlget($res)) {
            $html .= "<option selected value=\"{$row['MID']}\"> ".htmlspecialchars($row['LastName'].' '.$row['FirstName'].' ('.$row['Login'].') ',ENT_QUOTES)."</option>";
        }
    }
*/
    if (!empty($search)) {
        $search = iconv('UTF-8','Windows-1251',unicode_urldecode($search));
        $search = trim($search);    
        $search = str_replace('*','%',$search);
        $where = "WHERE (People.LastName LIKE '%".addslashes($search)."%'
        OR People.FirstName LIKE '%".addslashes($search)."%'
        OR People.Login LIKE '%".addslashes($search)."%')";
        // AND People.MID NOT IN ('".(int) $current."')
        $people = CTestsResultsPersonalReport::getReportInputField('MID',false,$where);
        if (is_array($people) && count($people)) {
            foreach($people as $mid=>$name) {
                $html .= "<option value=\"$mid\"";
                if ($current == $mid) $html .= " selected ";
                $html .= "> ".htmlspecialchars($name,ENT_QUOTES)."</option>";
            }
        }
    }
    return $html;
}



?>