<?php
/**
*            -     -    
*/
require_once("Schedule.class.php");

class CCurriculumReport extends CReportData {
    
    function getReportData() {
        
        $inputData = $this->getInputData();
        $trackId   = (int)$inputData['track'];        
        $result    = array();
                
        //формы контроля
        $sql = "SELECT * FROM exam_types";
        $res = sql($sql);
        $allExamtypes = array();
        while ($row = sqlget($res)) {
            $allExamtypes[$row['etid']] = $row['title'];
        }
        
        //специальность        
        $sql = "SELECT t.name, t.id, t.volume
                FROM tracks t
                WHERE t.trid = '$trackId'";        
        $row = sqlget(sql($sql));
        
        $track = array();
        $track[1] = $row['id'];
        $track[2] = $row['name'];        
        $track[3] = $row['volume'];
        
        $hours = array(); $hours1Total = 0;
        
        $sql = "SELECT * FROM tracks_hours WHERE trid = '$trackId'";
        $res = sql($sql);
        
        while($row = sqlget($res)) {
            $hours[$row['cid']][$row['type']] = $row['value'];
        }
        
                
        //курсы на специальности        
        $sql = "SELECT tc.level, c.Title, c.hours, c.examtype, tc.cid
                FROM tracks2course tc
                LEFT JOIN Courses c ON c.CID = tc.cid
                WHERE tc.trid = '$trackId'";
        $res = sql($sql);
                
        $cids = $courses = $examTypes = $totalHours = array();        
        while ($row = sqlget($res)) {
            $row['hours'] = $hours[$row['cid']]['hours1'] + $hours[$row['cid']]['hours2'];
            $courses[$row['Title']]['level']      = (int)$row['level'];
            $courses[$row['Title']]['hours']      = (int)$row['hours'];
            $courses[$row['Title']]['hours1']     = (int) $hours[$row['cid']]['hours1'];
            $hours1Total += $hours[$row['cid']]['hours1'];
            $courses[$row['Title']]['hours2']     = (int) $hours[$row['cid']]['hours2'];
            $totalHours['courses']               += (int)$row['hours']; 
            $totalHours['levels'][$row['level']] += (int)$row['hours']; 
            
            if (is_array(unserialize($row['examtype']))) {
                foreach (unserialize($row['examtype']) as $value) {
                    $courses[$row['Title']]['examtype'][$value] = $allExamtypes[$value];
                    $examTypes[$value] = $allExamtypes[$value];
                }
            }
            $cids[$row['cid']] = $row['cid'];
        }                
        
        asort($examTypes);
        
        //типы занятий
        $sql = "SELECT e.TypeID, e.TypeName
                FROM EventTools e";
        $res = sql($sql);
        while ($row = sqlget($res)) {
            $etools[$row['TypeID']] = $row['TypeName'];
        }

        //часы занятий
        $sql = "SELECT o.metadata, c.Title, c.CID
                FROM organizations o
                LEFT JOIN Courses c ON c.CID = o.cid                
                WHERE o.cid IN ('".implode("','",$cids)."')";
        
        $res = sql($sql);
        $eventTypes = array();        
        while ($row = sqlget($res)) {            
            if ($row['metadata']) {
                $data = read_metadata(stripslashes($row['metadata']), "item" );
                
                $eventType  = ($data[0]['name'] == 'item_type')?     (int)$data[0]['value']: false;
                $eventHours = ($data[1]['name'] == 'item_duration')? (int)$data[1]['value']: false;
                $eventType = $etools[$eventType];
                
                if ($eventType) {
                    $courses[$row['Title']]['events'][$eventType] += $eventHours;
                    $eventTypes[$eventType]                        = $eventType;
                    $totalHours['events'][$eventType]             += $eventHours;
                }
            }
        }
        
        $totalHours['allevents'] = array_sum($totalHours['events']);
        
        //общее количество часов за курс по факту и разница с заявленным количеством
        foreach ($courses as $key=>$val) {
            $courses[$key]['eventsum']     = (is_array($val['events']))?array_sum($val['events']):0;
            $courses[$key]['hours_events'] = $courses[$key]['hours'] - $courses[$key]['eventsum'];
        }
        
        $smarty = new Smarty_els();
        $smarty->assign('courses', $courses);
        $smarty->assign('track', $track);
        $smarty->assign('eventTypes', $eventTypes);
        $smarty->assign('eventTypesCount', count($eventTypes));
        $smarty->assign('countCols', /*count($eventTypes) + */18 + count($examTypes));
        $smarty->assign('examTypes', $examTypes);
        $smarty->assign('examTypesCount', count($examTypes));
        $smarty->assign('hours1Total', $hours1Total);
        $smarty->assign('totalHours', $totalHours);
        
        $result = array();
        $result['curriculum'] = $smarty->fetch('CurriculumReport.tpl');
        
        

        $this->data[] = $result;           
        
        //       !!              
        $this->data = parent::getReportData($this->data);
        
        return $this->data;
            
    }
    
    
    /**
    * Функция должна возвращать массив:
    */
    function getReportInputField($inputFieldName,$inputFieldData=false) {
        
        $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);
        
        $ret = array();
        
        switch($inputFieldName) {
                        
            case 'track':
                //все опубликованные специальности
                $sql = "SELECT trid, name FROM tracks WHERE status>0";
                $res = sql($sql);
                $ret[0] = _('Выберите элемент');
                while($row = sqlget($res)) {
                    $ret[$row['trid']] = $row['name'];
                }
            break;                
        }        
        
        return $ret;
    }
    
}




?>