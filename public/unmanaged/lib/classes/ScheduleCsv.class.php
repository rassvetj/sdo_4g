<?php

class ScheduleCsv {

        function get_as_array() {
        		$query = "SELECT DATE_ADD(begin, INTERVAL 1 DAY) FROM schedule WHERE SUBSTRING(end, 0, -9) = SUBSTRING(begin, 0, -9) ORDER BY begin DESC LIMIT 1";
        		$res = sql($query);
        		if(sqlrows($res)>0)
        		{
        			$row = sqlget($res);        			
        			$end = $row['DATE_ADD(begin, INTERVAL 1 DAY)'];
        		}
        		
        		$query = "SELECT begin, WEEKDAY(begin) FROM schedule WHERE SUBSTRING(end, 0, -9) = SUBSTRING(begin, 0, -9) ORDER BY begin ASC LIMIT 1";
        		$res = sql($query);
        		if(sqlrows($res)>0)
        		{
        			$row = sqlget($res);
        			$begin = $row['begin'];
        			$j = (int)$row['WEEKDAY(begin)'] + 1;
        		}        		

                $begin_day = $begin;
                
                $i = 0;
                while($begin_day < $end) {
                        $end_day = date("Y-m-d", mktime(0,0,0,substr($begin_day,5,2),ltrim(substr($begin_day,8,2),"0") + 1,substr($begin_day,0,4)));
                        $sheids_array = $this->_get_sheids_array($begin_day, $end_day);
                        $return_array[$i]['day_name'] = $this->get_day_name_by_number($j);
                        $return_array[$i]['date'] = substr($begin_day,8,2).".".substr($begin_day,5,2).".".substr($begin_day,0,4);
                        $return_array[$i]['tweek'] = mktime(0,0,0,ltrim(substr($begin_day,5,2),"0"),ltrim(substr($begin_day,8,2),"0"),substr($begin_day,0,4));
                        if( is_array($sheids_array) ) {
                                $return_array[$i]['count_studies'] = count($sheids_array);
                                foreach($sheids_array as $sheid) {
                                        $schedule = new Schedule;
                                        $schedule->init($sheid);
                                        $return_array[$i]['studies'][] = array(
                                                                                                "sheid" => $schedule->get_sheid(),
                                                                                                "name" => $schedule->get_name(),
                                                                                                "period" => $schedule->get_period(),
                                                                                                "teacher" => $schedule->get_teacher(),
                                                                                                "room" => $schedule->get_room(),
                                                                                                "module_id" => $schedule->get_module_id());

                                }
                        }
                        $begin_day = $end_day;
                        $i++;
                        $j++;
                        if($j==8)
                        	$j==1;
                }
                return $return_array;
        }

        function _get_sheids_array($begin_day, $end_day) {
                $begin_day_unixtime = mktime(0,0,0,substr($begin_day,5,2),substr($begin_day,8,2),substr($begin_day,0,4));
                $end_day_unixtime = mktime(0,0,0,substr($end_day,5,2),substr($end_day,8,2),substr($end_day,0,4));
                $query = "SELECT DISTINCT scheduleID.SHEID as SHEID FROM scheduleID INNER JOIN schedule ON scheduleID.SHEID = schedule.SHEID
                                  WHERE GREATEST(UNIX_TIMESTAMP(schedule.begin),$begin_day_unixtime) < LEAST(UNIX_TIMESTAMP(schedule.end),$end_day_unixtime) ORDER BY schedule.begin";
                $result = sql($query,"eerrnfsdf");
                $return_array = array();
                while( $row = sqlget($result) ) {
                        $return_array[] = $row['SHEID'];
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
}

?>