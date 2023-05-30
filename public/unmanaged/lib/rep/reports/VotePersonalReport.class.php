<?php
/**
* Отчет по обучаемым - сводные - группы
*/

class CVotePersonalReport extends CReportData {
    
    function getReportData() {
        
            $GLOBALS['brtag'] = "~\x03~";
            $inputData = $this->getInputData();
            
            $sql = "
            SELECT polls FROM People WHERE MID='".(int) $inputData['MID']."'
            ";
            
            $res = sql($sql);
            while($row = sqlget($res)) {
                if (!empty($row['polls'])) {
                    $polls = unserialize($row['polls']);
                    if (is_array($polls) && count($polls)) {
                        foreach($polls as $poll) {
                            $parts = explode('#',$poll);
                            if ($parts[1]==$inputData['tid']) {
                                $sheids[$parts[0]] = $parts[0];
                            }
                        }
                    }
                }
            }
            
            $sql = "SELECT tid, data FROM test WHERE tid IN ('".(int) $inputData['tid']."')";
            $res = sql($sql);
            while($row = sqlget($res)) {
            	if (!empty($row['data'])) {
            	    $parts = explode($GLOBALS['brtag'],$row['data']);
            	    if (is_array($parts) && count($parts)) {
            	        $sql = "SELECT * FROM list WHERE kod IN ('".join("','",$parts)."')";
            	        $res = sql($sql);
            	        while($row = sqlget($res)) {
            	        	require_once('template_test/'.intval($row['qtype']).'-v.php');
            	        	$func = 'v_sql2php_'.intval($row['qtype']);
            	        	if (function_exists($func)) {
            	        	    $questions[$row['kod']]['info'] = $func($row);
            	        	    $questions[$row['kod']]['type'] = $row['qtype'];
            	        	}
            	        }
            	    }
            	}
            }                        
            
            if (is_array($sheids) && count($sheids)) {
                $sql = "
                SELECT loguser.stid, loguser.tid, loguser.log
                FROM loguser
                INNER JOIN logseance ON (logseance.stid = loguser.stid)
                WHERE loguser.tid='".(int) $inputData['tid']."' 
                AND logseance.sheid IN ('".join("','",array_values($sheids))."')
                ";
                $res = sql($sql);
                while($row = sqlget($res)) {
                    if (!empty($row['log'])) {
                        $results[$row['stid']] = unserialize($row['log']);
                    }
                }
            }
            if (is_array($results) && count($results)) {
                foreach($results as $result) {
                    if (is_array($result['akod']) && count($result['akod'])) {
                        foreach($result['akod'] as $i=>$kod) {
                            if (isset($questions[$kod]) && is_array($result['aotv'][$i]) && count($result['aotv'][$i])) {
                                switch($questions[$kod]['type']) {
                                    case 1:
                                        $questions[$kod]['info']['count'][$result['aotv'][$i][0]]++;
                                    break;
                                    case 2:
                                        foreach($result['aotv'][$i] as $otv=>$true) {
                                            if ($true) {
                                                $questions[$kod]['info']['count'][$otv+1]++;                                                
                                            }
                                        }
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            
            if (is_array($questions) && count($questions)) {
                foreach($questions as $question) {
                    $q['question'] = $question['info']['vopros'];
                    foreach($question['info']['variant'] as $i=>$variant) {
                        $q['answer'] = $variant;
                        $q['count'] = (int) $question['info']['count'][$i];
                        $this->data[] = $q;
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
    function getReportInputField($inputFieldName,$inputFieldData=false) {
        
        $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);
        
        $ret = '';    
                
        switch($inputFieldName) {
            
            case 'MID':
                            
                $sql = "SELECT * 
                        FROM People 
                        INNER JOIN Students ON (People.MID=Students.MID)
                        ORDER BY LastName";
                $res = sql($sql);
                while($row = sqlget($res)) {
                    if (!$peopleFilter->is_filtered($row['MID'])) continue;
                    $v = $row['MID'];
                    $ret[$v] = $row['LastName'].' '.$row['FirstName'];
                                                            
                }
                            
            break;
            
            case 'tid':
            
                if (!$inputFieldData['MID']) {
                    
                    $sql = "SELECT People.MID
                            FROM People 
                            INNER JOIN Students ON (People.MID=Students.MID)
                            ORDER BY LastName LIMIT 1";
                    $res = sql($sql);
                    while($row = sqlget($res)) {
                        if (!$peopleFilter->is_filtered($row['MID'])) continue;
                        $inputFieldData['MID'] = $row['MID'];                    
                    }
                }
                
                $sql = "SELECT polls FROM People WHERE MID='".(int) $inputFieldData['MID']."'";
                $res = sql($sql);
                while($row = sqlget($res)) {
                	if (!empty($row['polls'])) {
                	    $votes = unserialize($row['polls']);
                	    if (is_array($votes) && count($votes)) {
                	        foreach($votes as $vote) {
                	            $parts = explode('#',$vote);
                	            $tids[$parts[1]] = $parts[1];
                	        }
                	    }
                	}
                }
                
                if (is_array($tids) && count($tids)) {
                    $sql = "SELECT tid,title FROM test WHERE tid IN ('".join("','",array_values($tids))."') ORDER BY title";
                    $res = sql($sql);
                    while($row = sqlget($res)) {
                    	$ret[$row['tid']] = $row['title'];
                    }
                }
            

            break;            
            
            
        }        
        
        return $ret;
    }
    
}


?>