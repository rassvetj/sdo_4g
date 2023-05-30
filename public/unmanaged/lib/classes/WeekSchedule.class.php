<?php
class WeekSchedule {
    var $begin_week;
    var $end_week;
    var $cids = array();
    var $_bad_sheids = array(); // которые исключаются
    var $_registered;
    var $_schedules = array();
    var $_courseTitles = array();
    var $_roomTitles = array();
    var $_periodTitles = array();

    function init_by_begin_week($begin_week) {
        //формат входного параметра YYYY-MM-DD
        $this->begin_week = $begin_week;
        $year_number = substr($begin_week, 0, 4);
        $month_number = ltrim(substr($begin_week, 5, 2),"0");
        $day_number = ltrim(substr($begin_week, 8, 2),"0");
        $begin_unixtime = mktime(0,0,0,$month_number,$day_number,$year_number);
        $end_unixtime = mktime(23,59,59,$month_number,$day_number+6,$year_number);
        $this->end_week = date("Y-m-d", $end_unixtime);
    }

    function set_bad_sheids($sheids) {
        $this->_bad_sheids = $sheids;
    }


    function set_course($cid) {
        $this->cid = (integer)$cid;
    }

    function get_groupname()
    {
       $schedule = new Schedule;
       $schedule->init($this->cid);
       return  $schedule->groupname;
    }

    function set_cids($cids) {
        if (is_array($cids) && count($cids)) {
            $res = sql("SELECT CID,is_poll FROM Courses WHERE CID IN ('".implode("','",$cids)."')");
            while ($row = sqlget($res)) {
                if (!(int)$row['is_poll']) {
                    $this->cids[] = $row['CID'];
                }
            }
        }
    }

    function get_begin_week() {
        $parts_of_date = explode("-", $this->begin_week);
        return $parts_of_date[2].".".$parts_of_date[1].".".$parts_of_date[0];
    }

    function get_end_week() {
        $parts_of_date = explode("-", $this->end_week);
        return $parts_of_date[2].".".$parts_of_date[1].".".$parts_of_date[0];
    }

    function initSchedules($sheids, $badSheids) {
        $schedules = array();
        if (is_array($sheids) && count($sheids)) {
            if (is_array($badSheids) && count($badSheids)) {
                $sheids = array_diff($sheids, $badSheids);
            }

            $toolParams = array();
            $sql = "SELECT toolParams as toolParams, SHEID
                    FROM scheduleID
                    WHERE SHEID IN ('".join("','", $sheids)."')
                    ";
            if (strtolower(dbdriver) == 'mysql') {
                $sql .= " GROUP BY SHEID";
            }
            $res = sql($sql);

            while($row = sqlget($res)) {
                $toolParams[$row['SHEID']] = $row['toolParams'];
            }

            $events = array();
            $sql = "SELECT * FROM EventTools";
            $res = sql($sql);

            while($row = sqlget($res)) {
                $events[$row['TypeID']] = $row;
            }

            $sql = "SELECT "
                    .$GLOBALS['adodb']->SQLDate("Y-m-d H:i:s", "begin")." as begin, "
                    .$GLOBALS['adodb']->SQLDate("Y-m-d H:i:s", "end")." as end, schedule.*
                    FROM schedule
                    WHERE SHEID IN ('".join("','", $sheids)."')";
            $res = sql($sql);

            $cids = array();
            while($row = sqlget($res)) {
                if (!isset($schedules[$row['SHEID']])) {
                    $row['toolParams'] = @$toolParams[$row['SHEID']];
                    $row['Icon'] = @$events[$row['typeID']]['Icon'];
                    $row['tools'] = @$events[$row['typeID']]['tools'];
                    $schedules[$row['SHEID']] = new Schedule();
                    $schedules[$row['SHEID']]->init($row['SHEID'], $row);
                    $cids[$row['CID']] = $row['CID'];
                }
            }

            if (count($cids)) {
                $sql = "SELECT CID, Title FROM Courses WHERE CID IN ('".join("','", $cids)."')";
                $res = sql($sql);

                while($row = sqlget($res)) {
                     $this->_courseTitles[$row['CID']] = $row['Title'];
                }
            }

        }
        return $schedules;
    }

    function get_periods() {
        $sql = "SELECT lid, name FROM periods";
        $res = sql($sql);

        while($row = sqlget($res)) {
            $this->_periodTitles[$row['lid']] = $row['name'];
        }
    }

    function get_rooms() {
        $sql = "SELECT rid, name FROM rooms";
        $res = sql($sql);

        while($row = sqlget($res)) {
            $this->_roomTitles[$row['rid']] = $row['name'];
        }
    }

    function get_as_array() {
        $begin_day = $this->begin_week;
        $i = 1;
        $sheids = $this->_get_all_sheids_array($this->begin_week,$this->end_week);

        $perm_edit_own           = $GLOBALS['controller']->checkPermission(SHEDULE_PERM_EDIT_OWN);
        $perm_edit_others        = $GLOBALS['controller']->checkPermission(SHEDULE_PERM_EDIT_OTHERS);
        $perm_edit_others_people = $GLOBALS['controller']->checkPermission(SHEDULE_PERM_EDIT_OTHERS_PEOPLE);

        $this->get_rooms();
        $this->get_periods();

        while($begin_day <= $this->end_week) {
            $end_day = date("Y-m-d", mktime(0,0,0,substr($begin_day,5,2),ltrim(substr($begin_day,8,2),"0") + 1,substr($begin_day,0,4)));
            $sheids_array = $sheids[$begin_day];
            //$sheids_array = $this->_get_sheids_array($begin_day, $end_day);
            $week_day = date('w',strtotime($begin_day));
            if ($week_day==0) $week_day = 7;
            $return_array[$i]['day_name'] = $this->get_day_name_by_number($week_day);
            $return_array[$i]['date'] = substr($begin_day,8,2).".".substr($begin_day,5,2).".".substr($begin_day,0,4);
            $return_array[$i]['tweek'] = mktime(0,0,0,ltrim(substr($begin_day,5,2),"0"),ltrim(substr($begin_day,8,2),"0"),substr($begin_day,0,4));
            $return_array[$i]['tweekUTC'] = $return_array[$i]['tweek'] + date('Z');
            if( is_array($sheids_array) ) {
                $schedules = &$this->initSchedules($sheids_array, $this->_bad_sheids);
                foreach($sheids_array as $sheid) {
                    if (!isset($schedules[$sheid])) continue;
                    if (in_array($sheid, $this->_bad_sheids)) continue;

                    if (!isset($this->_schedules[$sheid])) {
                        $schedule = new Schedule();
                        $schedule->init($sheid);
                        //$schedule = &$schedules[$sheid];
                        $this->_schedules[$sheid] = $schedule;
                    } else {
                        $schedule = $this->_schedules[$sheid];
                    }
                    $schedule->set_current($begin_day);
                    if (!$schedule->check_rebuild()) continue;
                    //$schedule = new Schedule;
                    //$schedule->init($sheid);
                    ////$schedule->set_current($begin_day);
                    ////if (!$schedule->check_rebuild()) continue;
                    if(count($this->cids)>0)
                    {
                        if(in_array($schedule->get_cid(), $this->cids))
                        {
                            $shed = array(
                            "sheid" => $schedule->get_sheid(),
                            "name" => $schedule->get_title(),
                            /*"period" => $schedule->get_period(),*/
                            'period' => $this->_periodTitles[ $schedule->get_period_id()],
                            'period_id' => $schedule->get_period_id(),
                            "time" => $schedule->get_time(),
                            "description" => $schedule->get_description(),
                            "teacher" => $schedule->get_teacher(),
                            "teacher_mid" => $schedule->teacher,
                            /*'edit_permission' => $schedule->get_edit_permission(),*/
                            'edit_permission' => ((($schedule->get_teacher_mid()==$_SESSION['s']['mid'])
                                                 && $perm_edit_own)
                                                 || $perm_edit_others
                                                 || $perm_edit_others_people),
                            /*"room" => $schedule->get_room(),*/
                            'room' => $this->_roomTitles[$schedule->get_room_id()],
                            'room_id' => $schedule->get_room_id(),
                            "module_id" => $schedule->get_module_id(),
                            "test_id" => $schedule->get_test_id(),
                            "icon" => $schedule->get_icon(),
                            /*"course_name" => cid2title($schedule->get_cid()),*/
                            'course_name' => $this->_courseTitles[$schedule->get_cid()],
                            'cid' => $schedule->get_cid(),
                            'penalty' => $schedule->get_penalty($begin_day),
                            'studyType' => $schedule->get_studyType(),
                            'connectId' => $schedule->connectId
                            );
                            $return_array[$i]['studies'][$shed['time']['begin'].$shed['course_name'].$shed['sheid']] = $shed;
                        }
                    }
                    else
                    {
                        $shed = array(
                        "sheid" => $schedule->get_sheid(),
                        "name" => $schedule->get_title(),
                        /*"period" => $schedule->get_period(),*/
                        'period' => $this->_periodTitles[ $schedule->get_period_id()],
                        'period_id' => $schedule->get_period_id(),
                        "time" => $schedule->get_time(),
                        "description" => $schedule->get_description(),
                        "teacher" => $schedule->get_teacher(),
                        /*'edit_permission' => $schedule->get_edit_permission(),*/
                        'edit_permission' => ((($schedule->get_teacher_mid()==$_SESSION['s']['mid'])
                                             && $perm_edit_own)
                                             || $perm_edit_others
                                             || $perm_edit_others_people),
                        /*"room" => $schedule->get_room(),*/
                        'room' => $this->_roomTitles[$schedule->get_room_id()],
                        'room_id' => $schedule->get_room_id(),
                        "module_id" => $schedule->get_module_id(),
                        "test_id" => $schedule->get_test_id(),
                        "icon" => $schedule->get_icon(),
                        /*"course_name" => cid2title($schedule->get_cid()),*/
                        'course_name' => $this->_courseTitles[$schedule->get_cid()],
                        'cid' => $schedule->get_cid(),
                        'penalty' => $schedule->get_penalty($begin_day),
                        'studyType' => $schedule->get_studyType(),
                        'connectId' => $schedule->connectId
                        );
                        $return_array[$i]['studies'][$shed['time']['begin'].$shed['course_name'].$shed['sheid']] = $shed;

                    }

                }
                $return_array[$i]['count_studies'] = count($return_array[$i]['studies']);
                //@ksort($return_array[$i]['studies']);
            }
            $begin_day = $end_day;
            $i++;
        }
        return $return_array;
    }

    function get_as_array_for_day($date) {
        $partsDate = explode('.',$date);
        $this->begin_week = $this->end_week = $partsDate[2].'-'.$partsDate[1].'-'.$partsDate[0];
        $week_schedule_array = $this->get_as_array();
        if(is_array($week_schedule_array)) {
            foreach($week_schedule_array as $key => $value) {
                if($value['date'] == $date) {
                    $return_array = array();
                    $return_array[] = $value;
                    return $return_array;
                }
            }
        }
    }

    function _get_relative_dates_array_all($begin_unixtime, $end_unixtime) {
        $sql = "SELECT DISTINCT CID, time_registered FROM Students WHERE MID='".(int) $GLOBALS['s']['mid']."'";
        $res = sql($sql);
        while($row = sqlget($res)) {
            if ($row['CID'] &&
            (preg_match("/^(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})$/",$row['time_registered'],&$matches)
            || preg_match("/^(\d{4})-(\d{2})-(\d{2})( (\d{2}):(\d{2}):(\d{2}))*$/",$row['time_registered'],&$matches))) {
                $begin = mktime(0,0,0,$matches[2],$matches[3],$matches[1]);
                $this->_registered = $begin;
                $diff1 = (int) ($begin_unixtime - $begin);
                if ($diff1 < 0) $diff1 = 0;
                $diff2 = (int) ($end_unixtime - $begin + ((60*60*23)+(60*59)+59));
                //$diff2 = (int) ($diff1 + ((60*60*23)+(60*59)+59));
                if ($diff2>=0)
                $ret[] = array('begin'=>$diff1, 'end'=>$diff2, 'cid'=>$row['CID']);
            }
        }
        return $ret;
    }

        function _get_relative_dates_array($unixtime) {
        $sql = "SELECT DISTINCT CID, time_registered FROM Students WHERE MID='".(int) $GLOBALS['s']['mid']."'";
        $res = sql($sql);
        while($row = sqlget($res)) {
            if ($row['CID'] && (preg_match("/^(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})$/",$row['time_registered'],&$matches)
            || preg_match("/^(\d{4})-(\d{2})-(\d{2})( (\d{2}):(\d{2}):(\d{2}))*$/", $row['time_registered'], &$matches))) {

                $begin = mktime(0,0,0,$matches[2],$matches[3],$matches[1]);
                $this->_registered = $begin;
                $diff1 = (int) ($unixtime-$begin);
                $diff2 = (int) ($diff1 + ((60*60*23)+(60*59)+59));
                if ($diff1>=0)
                $ret[] = array('begin'=>$diff1, 'end'=>$diff2, 'cid'=>$row['CID']);
            }
        }
/*
        $sql = "SELECT CID, cBegin, cEnd FROM Courses";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $begin = explode('-',$row['cBegin']);
            $end = explode('-',$row['cEnd']);
            if (is_array($begin) && (count($begin)==3) &&
                is_array($end) && (count($end)==3)) {
                    $begin = mktime(0,0,0,$begin[1],$begin[2],$begin[0]);
                    $end = mktime(0,0,0,$end[1],$end[2],$end[0]);
                    $diff1 = (int) ($unixtime-$begin);
                    $diff2 = (int) ($diff1 + ((60*60*23)+(60*59)+59));
                    if ($diff1>=0)
                    $ret[] = array('begin'=>$diff1, 'end'=>$diff2, 'cid'=>$row['CID']);
                }
        }
*/
        return $ret;
    }

    function _get_all_sheids_array($begin_day, $end_day) {
        global $s, $adodb;
        $sheids = array();
        $begin_day_unixtime = mktime(0,0,0,substr($begin_day,5,2),substr($begin_day,8,2),substr($begin_day,0,4));
        $end_day_unixtime = mktime(0,0,0,substr($end_day,5,2),substr($end_day,8,2),substr($end_day,0,4)) + 23*60*60 + 59*60 +59;
        $poll_cid = sqlvalue("SELECT CID FROM Courses WHERE is_poll = 1");
        	
        $query = "SELECT schedule.SHEID, schedule.CID, schedule.cond_progress, schedule.cond_avgbal, schedule.cond_sumbal,
                  schedule.cond_operation, schedule.begin, schedule.cond_sheid, schedule.cond_mark, schedule.timetype,
                  schedule.startday, schedule.stopday, schedule.end
                  FROM schedule WHERE ";
        if($poll_cid)
        	$query .= "schedule.CID != '$poll_cid' AND ";
        $query .= "(schedule.timetype=0 AND GREATEST(UNIX_TIMESTAMP(schedule.begin),UNIX_TIMESTAMP(" . $adodb->DBTimeStamp($begin_day_unixtime) . ")) < LEAST(UNIX_TIMESTAMP(schedule.end),UNIX_TIMESTAMP(" . $adodb->DBTimeStamp($end_day_unixtime) . "))";
        $relative_dates = $this->_get_relative_dates_array_all($begin_day_unixtime, $end_day_unixtime);
        if (is_array($relative_dates) && count($relative_dates)) {
            foreach($relative_dates as $v) {
                $query .= " OR (schedule.timetype=1 AND schedule.CID='{$v['cid']}'
                            AND ((schedule.startday>='{$v['begin']}' AND schedule.startday<='{$v['end']}')";
                $query .= " OR (schedule.stopday>='{$v['begin']}' AND schedule.stopday<='{$v['end']}')";
                $query .= " OR (schedule.startday<'{$v['begin']}' AND schedule.stopday>'{$v['end']}')";
                $query .= ")) ";
            }
        }
        $query .= ")";
        if (is_array($this->cids) && count($this->cids)) {
            $query .= " AND schedule.CID IN ('".join("','", $this->cids)."')";
        }elseif ($s['perm']<3) {
            //если вам не назначенно курсов и роль ниже уч. админа тогда занятия показывать не будем
            $query .= " AND schedule.CID = 0";
        }
        $query .= " ORDER BY schedule.begin, schedule.title";

        //scheduleID.SHEID as SHEID, scheduleID.MID as MID_,

        $result = sql($query,"eerrnfsdf");
        $return_array = $conditions = array();
        while( $row = sqlget($result) ) {

            //if((($s['mid'] == $row['MID_']) || ($s['perm'] > 1)) && !in_array($row['SHEID'], $sheids) && $this->check_cond($row)) {
            if (!in_array($row['SHEID'], $sheids) /*&& $this->check_cond($row)*/) {

                $conditions[$row['SHEID']] = $row;
                //$conditions[$row['SHEID']]['MID_'] =

                $sheids[] = $row['SHEID'];
                switch($row['timetype']) {
                    case 1:
                        $begin = date('Y-m-d',$this->_registered + $row['startday']);
                        $end = date('Y-m-d',$this->_registered + $row['stopday']);
                    break;
                    default:
                        $begin = substr($row['begin'],0,10);
                        $end = substr($row['end'],0,10);
                    break;
                }
                while($begin<=$end) {
                    $end_day = date("Y-m-d", mktime(0,0,0,substr($begin,5,2),ltrim(substr($begin,8,2),"0") + 1,substr($begin,0,4)));
                    if (!@in_array($row['SHEID'],$return_array[$begin])) {
                        $return_array[$begin][] = $row['SHEID'];
                    }
                    $begin = $end_day;
                }
            }
        }

        if ($_SESSION['s']['perm'] <= 1) {
            $allowed = array();
            if (is_array($sheids) && count($sheids)) {
                $sql = "SELECT SHEID FROM scheduleID WHERE SHEID IN ('".join("','", $sheids)."') AND MID = '".(int) $_SESSION['s']['mid']."'";
                $res = sql($sql);

                while($row = sqlget($res)) {
                    if (!isset($allowed[$row['SHEID']])) {
                        if (!$this->check_cond($conditions[$row['SHEID']])) {
                            continue;
                        }
                    }
                    $allowed[$row['SHEID']] = $row['SHEID'];
                }

                if (is_array($return_array)) {
                    foreach($return_array as $date => $day) {
                        if (is_array($day)) {
                            foreach($day as $key => $sheid) {
                                if (!isset($allowed[$sheid])) {
                                    unset($return_array[$date][$key]);
                                }
                            }
                        }
                    }
                }
            }
        }

        return $return_array;
    }

    function _get_sheids_array($begin_day, $end_day) {
        global $s;
        global $adodb;
        $begin_day_unixtime = mktime(0,0,0,substr($begin_day,5,2),substr($begin_day,8,2),substr($begin_day,0,4));
        $end_day_unixtime = mktime(0,0,0,substr($end_day,5,2),substr($end_day,8,2),substr($end_day,0,4));
        $query = "SELECT DISTINCT scheduleID.SHEID as SHEID, scheduleID.MID as MID_, schedule.CID, schedule.cond_progress,
                  schedule.cond_avgbal, schedule.cond_sumbal, schedule.cond_operation, schedule.begin, schedule.cond_sheid,
                  schedule.cond_mark, schedule.timetype, schedule.startday, schedule.stopday, schedule.end
                  FROM scheduleID
                  INNER JOIN schedule ON scheduleID.SHEID = schedule.SHEID
                  WHERE (schedule.timetype=0 AND GREATEST(UNIX_TIMESTAMP(schedule.begin),UNIX_TIMESTAMP(" . $adodb->DBTimeStamp($begin_day_unixtime) . ")) < LEAST(UNIX_TIMESTAMP(schedule.end),UNIX_TIMESTAMP(" . $adodb->DBTimeStamp($end_day_unixtime) . "))";
        $relative_dates = $this->_get_relative_dates_array($begin_day_unixtime);
        if (is_array($relative_dates) && count($relative_dates)) {
            foreach($relative_dates as $v) {
                $query .= " OR (schedule.timetype=1 AND schedule.CID='{$v['cid']}'
                            AND ((schedule.startday>='{$v['begin']}' AND schedule.startday<='{$v['end']}')";
                $query .= " OR (schedule.stopday>='{$v['begin']}' AND schedule.stopday<='{$v['end']}')";
                $query .= " OR (schedule.startday<'{$v['begin']}' AND schedule.stopday>'{$v['end']}')";
                $query .= ")) ";
            }
        }
        $query .= ") ORDER BY schedule.begin";
        //                                  WHERE GREATEST(UNIX_TIMESTAMP(schedule.begin),$begin_day_unixtime) < LEAST(UNIX_TIMESTAMP(schedule.end),$end_day_unixtime) ORDER BY schedule.begin";
        $result = sql($query,"eerrnfsdf");
        $return_array = array();
        while( $row = sqlget($result) ) {
            if((($s['mid'] == $row['MID_']) || ($s['perm'] > 1)) && !in_array($row['SHEID'], $return_array) && $this->check_cond($row)) {
                $return_array[] = $row['SHEID'];
            }
        }
        return $return_array;
    }

    function get_day_name_by_number($number) {
        switch ($number) {
            case "1":
            return _("понедельник");
            break;
            case "2":
            return _("вторник");
            break;
            case "3":
            return _("среда");
            break;
            case "4":
            return _("четверг");
            break;
            case "5":
            return _("пятница");
            break;
            case "6":
            return _("суббота");
            break;
            case "7":
            return _("воскресенье");
            break;
            default:
            return "";
        }
    }

    function check_cond($arrSheid){
        if (($_SESSION['s']['perm'] > 1) /*|| empty($arrSheid['cond_mark']) || ($arrSheid['cond_sheid'] == -1)*/) {
            return true;
        } else {
            if ($arrSheid['cond_sheid'] && ($arrSheid['cond_sheid'] != '-1')) {
                $cond_sheids = explode('#',$arrSheid['cond_sheid']);
                $cond_marks = explode('#',$arrSheid['cond_mark']);
                if (is_array($cond_sheids) && is_array($cond_marks) && (count($cond_sheids)==count($cond_marks))) {
                    reset($cond_sheids);
                    while(list($k,$v) = each($cond_sheids)) {
                        $arrRange = explode("-", $cond_marks[$k]);
                        $strCondition = "AND V_STATUS>='" . (integer)trim($arrRange[0]) . "'";
                        $strCondition .= ((integer)($top = trim($arrRange[1]))) ? " AND V_STATUS<='{$top}'" : "";
                        $strQuery[] = " (SHEID='{$v}' {$strCondition}) ";
                    }
                    if (is_array($strQuery) && count($strQuery)) {
                        $sql = "SELECT DISTINCT SHEID FROM scheduleID WHERE mid='{$_SESSION['s']['mid']}' AND (".join('OR',$strQuery).")";
                        $res = sql($sql);
                        $cond_sheid = (sqlrows($res)==count($cond_sheids));
                        sqlfree($res);
                    }

                }

            }
/*
            if ($arrSheid['cond_sheid'] && ($arrSheid['cond_sheid'] != '-1')) {
                $arrRange = explode("-", $arrSheid['cond_mark']);
                $strCondition = "AND V_STATUS>='" . (integer)trim($arrRange[0]) . "'";
                $strCondition .= ((integer)($top = trim($arrRange[1]))) ? " AND V_STATUS<='{$top}'" : "";
                $strQuery = "SELECT * FROM scheduleID WHERE mid='{$_SESSION['s']['mid']}' AND SHEID='{$arrSheid['cond_sheid']}' {$strCondition}";
                $res = sql($strQuery);
                $cond_sheid = sqlrows($res);
            }
*/
            /**
            * Проверка остальных условий progress, avgbal, sumbal
            */
            if ($arrSheid['cond_progress'] || $arrSheid['cond_avgbal'] || $arrSheid['cond_sumbal']) {



/*                $sql = "SELECT schedule.SHEID, scheduleID.V_STATUS FROM scheduleID
                        INNER JOIN schedule ON (schedule.SHEID=scheduleID.SHEID)
                        WHERE scheduleID.MID='".$arrSheid['MID_']."' AND schedule.CID='".(int) $arrSheid['CID']."' AND vedomost = '1'";
*/
                $sql = "SELECT schedule.SHEID, scheduleID.V_STATUS FROM scheduleID
                        INNER JOIN schedule ON (schedule.SHEID=scheduleID.SHEID)
                        WHERE scheduleID.MID='".$_SESSION['s']['mid']."' AND schedule.CID='".(int) $arrSheid['CID']."' AND vedomost = '1'";
                $res = sql($sql);
                while($row = sqlget($res)) {
                    if ($row['V_STATUS'] > 0) {
                        $shedules_completed++;
                        $shedules_sumbal += $row['V_STATUS'];
                    }
                    $shedules_total++;
                }

                if ($shedules_total)
                    $shedules_progress = floor(doubleval(($shedules_completed/$shedules_total)*100));
                if ($shedules_completed)
                    $shedules_avgbal = $shedules_sumbal/$shedules_completed;

/*
                $cond_progress = ($shedules_progress >= $arrSheid['cond_progress']);
                $cond_avgbal = ($shedules_avgbal >= $arrSheid['cond_avgbal']);
                $cond_sumbal = ($shedules_sumbal >= $arrSheid['cond_sumbal']);
*/
                $cond_progress = checkInterval($shedules_progress, $arrSheid['cond_progress']);
                $cond_avgbal   = checkInterval($shedules_avgbal, $arrSheid['cond_avgbal']);
                $cond_sumbal   = checkInterval($shedules_sumbal, $arrSheid['cond_sumbal']);

            }

            return checkCondition(array($cond_sheid, $cond_progress, $cond_avgbal, $cond_sumbal), $arrSheid['cond_operation']);
        }
    }
}

?>