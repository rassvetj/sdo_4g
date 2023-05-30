<?php
/**
* Отчет по обучаемым - сводные - группы
*/

class CCoursePlanReport extends CReportData {
    
    function getReportData() {
        
            $inputData = $this->getInputData();
            
            /**
            * Работа с группой
            */
            if ($inputData['gid'] && ($inputData['gid']!='-1')) {
                
                if ($group = $inputData['gid']) {
                    
                    $grType = (int) $group[0];
                    $grId = substr($group,2);

                    switch($grType) {
                    case 1:
                    $sql = "SELECT DISTINCT
                                scheduleID.SHEID
                            FROM
                                Students
                            INNER JOIN People ON (People.`MID` = Students.`mid`) 
                            INNER JOIN scheduleID ON (People.MID=scheduleID.MID)
                            WHERE Students.cgid='".$grId."' ";
                                
                    break;
                    case 2:
                    $sql = "SELECT DISTINCT
                                scheduleID.SHEID
                            FROM
                                Students
                            INNER JOIN People ON (Students.`MID` = People.`mid`)
                            INNER JOIN groupuser ON (People.MID = groupuser.mid)
                            INNER JOIN scheduleID ON (People.MID=scheduleID.MID)
                            WHERE groupuser.gid='".$grId."' ";
                    break;
                
                    }
                    
                    $res = sql($sql);
                                           
                    while($row = sqlget($res)) {
                        
                        $allow[] = (int) $row['SHEID'];                        
                        
                    }
                    
                    if (is_array($allow) && count($allow))
                    $allowSQL = "AND schedule.SHEID IN (".join(',',$allow).")";
                    
                }
                
            }
            
            if ($inputData['begin']) {

                $date = explode('.',$inputData['begin']);
                if ((count($date)==3) && checkdate($date[1],$date[0],$date[2])) $begin = $date[2].'-'.$date[1].'-'.$date[0].' 00:00:00';
                                
            }

            if ($inputData['end']) {

                $date = explode('.',$inputData['end']);
                if ((count($date)==3) && checkdate($date[1],$date[0],$date[2])) $end = $date[2].'-'.$date[1].'-'.$date[0].' 23:59:59';
                                
            }
            
            if (isset($begin)) {
                $begin = strtotime($begin);
                $where = "AND UNIX_TIMESTAMP(schedule.begin) >= '$begin' ";
            }
            if (isset($end)) {
                $end = strtotime($end);
                $where .= "AND UNIX_TIMESTAMP(schedule.end) <= '$end' ";
            }
    
            $sql = "SELECT schedule.SHEID, schedule.begin, schedule.title, schedule.cond_sheid, schedule.cond_mark, schedule.period,
                    schedule.CHID, CONCAT(CONCAT(CONCAT(CONCAT(People.LastName,' '),People.FirstName), ' '), People.Patronymic) as teacher
                    FROM schedule LEFT JOIN People ON (schedule.createID=People.MID)
                    INNER JOIN scheduleID ON (schedule.SHEID=scheduleID.SHEID)
                    WHERE schedule.CID='".(int) $inputData['CID']."'
                    $where
                    $allowSQL
                    GROUP BY schedule.SHEID, schedule.title, schedule.begin, schedule.cond_sheid,
                    schedule.period, schedule.cond_mark, schedule.CHID, People.LastName, People.FirstName, People.Patronymic
                    ".$this->getSQLOrderString(array('begin'));
                    
            $res = sql($sql);
            
            if (sqlrows($res)) {
             
                while($row = sqlget($res)) {
                    
                    /**
                    * Кол-во обучаемых
                    */
                    $sql2 = "SELECT SSID FROM scheduleID WHERE SHEID='".(int) $row['SHEID']."' AND MID<>'-1'";
                    $res2 = sql($sql2);
                    $row['students'] = sqlrows($res2);
                
                    $row['begin'] = date("d.m.Y",strtotime($row['begin']));
                    
                    /**
                    * Условие
                    */
                    $row['cond'] = _('нет');
                    if (!empty($row['cond_sheid']) && ($row['cond_sheid']!='-1')) {
                        $conds = false;
                        
                        $cond_sheids = explode('#',$row['cond_sheid']);
                        $cond_marks = explode('#',$row['cond_mark']);
                        
                        if (is_array($cond_sheids) && is_array($cond_marks) && (count($cond_sheids)==count($cond_marks))) {
                            reset($cond_sheids);
                            while(list($k,$v) = each($cond_sheids)) {
                                                        
                                $sql = "SELECT title FROM schedule WHERE SHEID='".(int) $v."'";
                                $res2 = sql($sql);
                                
                                if (sqlrows($res2)) {
                                    
                                    $row2 = sqlget($res2);
                                    
                                    $mark = explode('-',$cond_marks[$k]);
                                    if ($mark[1]) $mark = $cond_marks[$k];
                                    else $mark = $mark[0];
                                    
                                    $conds[] = $row2['title'].' на оценку '.$mark;
                                    
                                }
                                
                                sqlfree($res2);

                            }
                            if (is_array($conds) && count($conds)) $row['cond'] = join(', ',$conds);
                        }
                                                
                    }
                    
                    if ($row['period']>0) {
                        
                        $sql = "SELECT starttime, stoptime FROM periods WHERE lid='".(int) $row['period']."'";
                        $res2 = sql($sql);
                        if (sqlrows($res2)) {
                            
                            $row2 = sqlget($res2);                                                

                            $startimeH= intval($row2['starttime']/60);
                            $startimeI= $row2['starttime']-$startimeH*60;
                            if($startimeH<10) $startimeH="0$startimeH";
                            if($startimeI<10) $startimeI="0$startimeI";
                            
                            $stoptimeH= intval($row2['stoptime']/60);
                            $stoptimeI= $row2['stoptime']-$stoptimeH*60;
                            if($stoptimeH<10) $stoptimeH="0$stoptimeH";
                            if($stoptimeI<10) $stoptimeI="0$stoptimeI";
                        
                        }
                        
                        sqlfree($res2);
                        
                        $row['per'] = "$startimeH:$startimeI - $stoptimeH:$stoptimeI";
                        
                    } else
                    switch($row['CHID']) {
                        
                        case 0:
                        $row['per'] = _("Единожды");
                        break;
                        case 1:
                        $row['per'] = _("Ежедневно");
                        break;
                        case 2:
                        $row['per'] = _("Еженедельно");
                        break;
                        case 3:
                        $row['per'] = _("Ежемесячно");
                        break;
                        case 4:
                        $row['per'] = _("Через неделю");
                        break;
                        case 5:
                        $row['per'] = _("Без даты");
                        break;
                        
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
            
            case 'gid':
            
                $ret[-1] = _("Все");
                $groupFilter = new CGroupFilter_Department();
                
                $sql = "SELECT * FROM cgname ORDER BY cgid";
                $res = sql($sql);
                while($row = sqlget($res)) {
                    if (!$groupFilter->is_filtered($row['cgid'])) {
                        continue;
                    }    
                    $v = '1_'.$row['cgid'];
                    $ret[$v] = $row['name'];
                                                            
                }
                
                $sql = "SELECT * FROM groupname ORDER BY name";
                $res = sql($sql);

                //if (sqlrows($res) && isset($row)) $ret['-1'] = "---";

                while($row = sqlget($res)) {
                    if (!$groupFilter->is_filtered($row['gid'])) {
                        continue;
                    }
                    $v = '2_'.$row['gid'];
                    $ret[$v] = $row['name'];
                    
                }            
            
            break;
                        
        }
        
        return $ret;

    }
    
}


?>