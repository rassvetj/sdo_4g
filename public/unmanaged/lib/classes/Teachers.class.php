<?php

class Teachers {
      function get_mids_array($cid) {
               if($cid != "") {
                  $query = "SELECT * FROM Teachers LEFT JOIN People ON Teachers.MID = People.MID WHERE Teachers.CID = $cid";
               }
               else {
                  $query = "SELECT * FROM Teachers LEFT JOIN People ON Teachers.MID = People.MID";
               }
               $result = sql($query);
               $return_array = array();
               while($row = sqlget($result)) {
                     $return_array[] = $row['MID'];
               }
               return $return_array;
      }
/*
      function get_all_as_array() {
              $query = "SELECT DISTINCT Teachers.MID as mid
                        FROM Teachers LEFT JOIN People ON Teachers.MID = People.MID";
              $result = sql($query);
              $return_array = array();
              while($row = sqlget($result)) {
                    $teacher = new Teacher;
                    $teacher->init($row['mid']);
                    $return_array[$row['mid']] = array("first_name" => $teacher->get_first_name(),
                                                       "last_name" => $teacher->get_last_name());

              }
              return $return_array;
      }
*/
      function get_all_as_array($as_string = false) {
              $query = "SELECT DISTINCT Teachers.MID as mid
                        FROM Teachers INNER JOIN People ON Teachers.MID = People.MID ORDER BY People.LastName";
              $result = sql($query);
              $return_array = array();
              while($row = sqlget($result)) {
                    $teacher = new Teacher;
                    $teacher->init($row['mid']);
                    $return_array[$row['mid']] = ($as_string) ? $teacher->get_first_name().' '.$teacher->get_last_name() : array("first_name" => $teacher->get_first_name(),
                                                       "last_name" => $teacher->get_last_name(),
                                                       "rank" => $teacher->get_rank());
              }
              return $return_array;
      }
      
}

?>