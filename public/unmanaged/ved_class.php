<?php

class CVed {
      var $cid;
      var $gr;
      var $mid;
      var $week_id;

      function init($cid, $gr, $mid = 0, $week_id = 0) {
               //иницилизация объекта для выбранного курса и группы или для всех групп
               $this->cid = $cid;
               $this->gr = $gr;
               $this->mid = $mid;
               $this->week_id = $week_id;
      }

      function get_array_of_marks() {
               $return_array = array();
               if($this->mid != 0) {
                       $add_where_clause = " AND scheduleID.MID = '".$this->mid."'";
               }
               else {
                       $add_where_clause = "";
               }
               if($this->week_id != 0) {
               		$week = getWeek($this->cid, $this->week_id);
               		if(count($week))
               		{
               			$add_where_week = " schedule.begin >= '".date("Y-m-d", $week[0])."' AND schedule.end <= '".date("Y-m-d H:i", $week[1])."' AND ";
               		}
               }
               else {
               		$add_where_week = "";
               }
               
               if(substr($this->gr,0,1) == "d") {
                  //выбираем людей из непресекающейя группы
                  $gr_id = substr($this->gr, 1);
                  $query = "SELECT scheduleID.SHEID as SHEID, scheduleID.MID as MID, scheduleID.V_STATUS as V_STATUS
                            FROM scheduleID
                            LEFT JOIN schedule ON schedule.SHEID = scheduleID.SHEID
                            LEFT JOIN Students ON scheduleID.MID = Students.MID
                            WHERE $add_where_week schedule.CID = '".$this->cid."' AND
                                  Students.cgid = '$gr_id' AND
                                  schedule.vedomost = 1".$add_where_clause . " ORDER BY schedule.begin";
               }
               elseif(substr($this->gr, 0, 1) == "g") {
                 //выбираем людей из пересекающейся группы
                 $gr_id = substr($this->gr, 1);
                 $query = "SELECT scheduleID.SHEID as SHEID, scheduleID.MID as MID, scheduleID.V_STATUS as V_STATUS
                           FROM scheduleID
                           LEFT JOIN schedule ON schedule.SHEID = scheduleID.SHEID
                           LEFT JOIN groupuser ON  scheduleID.MID = groupuser.mid
                           WHERE $add_where_week schedule.CID = '".$this->cid."' AND
                           groupuser.gid = '$gr_id' AND
                           schedule.vedomost = 1".$add_where_clause . " ORDER BY schedule.begin";
               }
               else {
                     switch($this->gr) {
                            case "-1":
                                   //ничего не выбираем т.к. не выбрана группа
                                   return $return_array;
                            break;
                            case "0":
                                  //выбираем всех людей на группы не обращаем внимание
                                  $query = "SELECT scheduleID.SHEID as SHEID, scheduleID.MID as MID, scheduleID.V_STATUS as V_STATUS
                                            FROM scheduleID
                                            LEFT JOIN schedule ON schedule.SHEID = scheduleID.SHEID
                                            WHERE $add_where_week schedule.CID = '".$this->cid."' AND
                                            schedule.vedomost = 1".$add_where_clause . " ORDER BY schedule.begin";
                            break;
                     }
               }
               $result = sql($query, "err_select_sheids_and_mids");

               $alt_mark_query = "SELECT * FROM alt_mark";
               $alt_mark_result = sql($alt_mark_query);
               $alt_mark_array = array();
               while( $alt_mark_row = sqlget($alt_mark_result) ) {
                      $alt_mark_array[$alt_mark_row['int']] = $alt_mark_row['char'];
               }
               $alt_mark_array_keys = array_keys($alt_mark_array);
               while($row = sqlget($result)) {
               	
                      if( !empty($row['MID']) && !empty($row['SHEID']) ) {
                        if( in_array($row['V_STATUS'], $alt_mark_array_keys) ) {
                             $return_array[$row['MID']][$row['SHEID']]['key'] = $row['V_STATUS'];
                             $return_array[$row['MID']][$row['SHEID']]['char'] = $alt_mark_array[$row['V_STATUS']];
                        }
                        elseif($row['V_STATUS'] >= 0) {
                               $return_array[$row['MID']][$row['SHEID']]['key']  = $row['V_STATUS'];
                               $return_array[$row['MID']][$row['SHEID']]['char'] = $row['V_STATUS'];
                        }
                        else {
                               $return_array[$row['MID']][$row['SHEID']]['key']  = "-1";
                               $return_array[$row['MID']][$row['SHEID']]['char'] = "-";
                        }
                     }
               }
               return $return_array;
      }

      function get_cid() {
              return $this->cid;
      }

      function get_gr() {
              return $this->gr;
      }

      function get_count_groups_on_course() {
               $query = "SELECT COUNT(*) AS count FROM cgname";
               $result = sql($query);
               $row1 = sqlget($result);

               $query = "SELECT COUNT(*) AS count FROM groupname";
               $result = sql($query);
               $row2 = sqlget($result);

               return $row1['count'] + $row2['count'];
      }
}


?>