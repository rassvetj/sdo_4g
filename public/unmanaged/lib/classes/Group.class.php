<?php
class CGroup extends CDBObject {
    var $table = 'groupname'; 
    
    function getMidsArray($gid) {
        $ret = false;
        if ($gid) {
            $sql = "SELECT DISTINCT mid FROM groupuser WHERE gid='".(int) $gid."' ORDER BY mid";
            $res = sql($sql);
            
            while($row = sqlget($res)) {
                $ret[$row['mid']] = $row['mid'];
            }
        }
        return $ret;
    }
}
?>