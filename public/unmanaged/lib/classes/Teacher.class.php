<?php
require("People.class.php");

class Teacher extends People {
      function init($mid) {
               parent::init($mid);
      }
      function get_array_of_sheids($cid) {
               $query = "SELECT * FROM schedule WHERE createID = ".$this->mid." AND CID = $cid";
               $result = sql($query);
               $return_array = array();
               while($row = sqlget($result)) {
                     $return_array[] = $row['SHEID'];
               }
               return $return_array;
      }
      function get_mid() {
               return $this->mid;
      }
      function set_mid($mid) {
              $this->mid = $mid;
      }
      function get_array_of_cids() {
              $return_array = array();
              $query = "SELECT DISTINCT CID FROM Teachers WHERE MID = ".$this->mid;
              $result = sql($query);
              while($row = sqlget($result)) {
                    $return_array[] = $row['CID'];
              }
              return $return_array;
      }
}


?>