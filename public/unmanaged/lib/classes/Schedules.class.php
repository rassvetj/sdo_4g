<?php

class Schedules {
      function get_all() {
               $query = "SELECT * FROM schedule";
               $result = sql($query);
               $return_array = array();
               while($row = sqlget($result)) {
                       $return_array[] = $row['SHEID'];
               }
               return $return_array;
      }

        function get_all_createID_at_period($mid,$from,$to) {
                if($mid != 0)  $query = "SELECT * FROM schedule WHERE createID = '".$mid."' AND GREATEST(UNIX_TIMESTAMP('$from'), UNIX_TIMESTAMP(begin)) <= LEAST(UNIX_TIMESTAMP('$to'), UNIX_TIMESTAMP(end))";
                else  $query = "SELECT * FROM schedule WHERE GREATEST(UNIX_TIMESTAMP('$from'), UNIX_TIMESTAMP(begin)) <= LEAST(UNIX_TIMESTAMP('$to'), UNIX_TIMESTAMP(end))";
                $result = sql($query);
                $return_array = array();
                while($row = sqlget($result)) {
                        $return_array[] = $row['SHEID'];
                }
                return $return_array;
        }

        function get_all_types($only_id = false) {
                $query = "SELECT * FROM EventTools";
                $result = sql($query);
                $return_array = array();
                while($row = sqlget($result)) {
                        $return_array[$row['TypeID']] = ($only_id) ? null : $row['TypeName'];
                }
                return $return_array;

        }

        function get_all_periods() {
                $query = "SELECT * FROM periods ORDER BY starttime";
                $res = sql($query);
                $return = array();
                while($row = sqlget($res)) {
                        $return[$row['lid']] = $row;
                }
                return $return;
        }

        function get_all_students($cid, $only_cid = false) {
                require_once('Course.class.php');
                $course = new Course($cid);
                $query = "SELECT MID FROM Students WHERE CID='{$cid}'";
                $result = sql($query);
                $return_array = array();
                while($row = sqlget($result)) {
                        $return_array[$row['MID']] = ($only_cid) ? array() : get_people_military_info($row['MID'], SHOW_PLAIN);
                }
                return $return_array;
        }
}

?>