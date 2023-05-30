<?php

class People {
      var $mid;
      var $last_name;
      var $first_name;

      function init($mid) {
              $query = "SELECT * FROM People WHERE MID = $mid";
              $result = sql($query);
              $row = sqlget($result);
              $this->mid = $mid;
              $this->last_name = $row['LastName'];
              $this->first_name = $row['FirstName'];

      }

      function get_last_name() {
              return $this->last_name;
      }

      function get_first_name() {
              return $this->first_name;
      }

      function get_rank() {
              return get_rank($this->mid);
      }

}
?>