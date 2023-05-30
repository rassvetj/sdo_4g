<?php

class Course {
      var $cid;
      var $department_id;
      var $name;

      function init($cid) {
               $this->cid = $cid;
               $query = "SELECT * FROM Courses WHERE CID = $cid";
               $result = sql($query);
               $row = sqlget($result);
               $this->department_id = $row['did'];
               $this->name = $row['Title'];
      }

      function get_sheids_array() {
               $query = "SELECT * FROM schedule WHERE CID = ".$this->cid;
               $result = sql($query);
               $return_array = array();
               while($row = sqlget($result)) {
                     $return_array[] = $row['SHEID'];
               }
               return $return_array;
      }

      function get_sheids_array_at_period($from, $to) {
               $query = "SELECT *
                         FROM schedule
                         WHERE GREATEST(UNIX_TIMESTAMP('$from'), UNIX_TIMESTAMP(begin)) <= LEAST(UNIX_TIMESTAMP('$to'), UNIX_TIMESTAMP(end))";
               $result = sql($query, "err3434");
               $return_array = array();
               while($row = sqlget($result)) {
                       $return_array[] = $row['SHEID'];
               }
               return $return_array;
      }

      function get_department() {
               if(!empty($this->department_id)) {
                   $query = "SELECT * FROM departments WHERE did = ".$this->department_id;
                   $result = sql($query);
                   $row = sqlget($result);
                   return $row['name'];
               }
               else {
                    return "";
               }
      }

      function get_name() {
              return $this->name;
      }
                
}

?>