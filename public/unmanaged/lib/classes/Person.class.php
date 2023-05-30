<?php

class CPerson extends CDBObject {
    var $table = 'People';

    function getPhoto($mid) {
        return getPhoto($mid);
    }
                
    /**
     * Enter description here...
     *
     * @return unixtimestamp
     */
    function getRegisteredTime($mid, $cid) {
        $time = 0;
        if ($mid && $cid) {
            $sql = "SELECT time_registered
                    FROM Students
                    WHERE CID='".(int) $cid."'
                    AND MID='".(int) $mid."'";
            $res = sql($sql);
            if (sqlrows($res) && ($row = sqlget($res))) {
                $registered = $row['time_registered'];
                if (preg_match("/^(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})$/", $registered, &$matches)
                || preg_match("/^(\d{4})-(\d{2})-(\d{2})( (\d{2}):(\d{2}):(\d{2}))*$/", $registered, &$matches)) {
                    $time = mktime($matches[4],$matches[5],$matches[6],$matches[2],$matches[3],$matches[1]);
                }
            }
        }
        return $time;
    }

    function getRegisteredDate($mid, $cid) {
        $time = 0;
        if ($mid && $cid) {
            $sql = "SELECT time_registered
                    FROM Students
                    WHERE CID='".(int) $cid."'
                    AND MID='".(int) $mid."'";
            $res = sql($sql);
            if (sqlrows($res) && ($row = sqlget($res))) {
                $registered = $row['time_registered'];
                if (preg_match("/^(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})$/", $registered, &$matches)
                || preg_match("/^(\d{4})-(\d{2})-(\d{2})( (\d{2}):(\d{2}):(\d{2}))*$/", $registered, &$matches)) {
                    $time = mktime(0,0,0,$matches[2],$matches[3],$matches[1]);
                }
            }
        }
        return $time;
    }

    function getNameLF() {
        if (is_array($this->attributes) && count($this->attributes)) {
            return $this->attributes['LastName'].' '.$this->attributes['FirstName'].' '.$this->attributes['Patronymic'];
        }
    }

    function getNameLFP() {
        if (is_array($this->attributes) && count($this->attributes)) {
            return $this->attributes['LastName'].' '.$this->attributes['FirstName'].' '.$this->attributes['Patronymic'];
        }
    }

    function get($mid, $mode='') {
        if ($mid) {
            $what = '*';

            switch($mode) {
                case 'LFP':
                    $what = 'LastName, FirstName, Patronymic, Login';
                break;
                case 'LF':
                    $what = 'LastName, FirstName, Patronymic';
                break;
            }

            $sql = "SELECT $what FROM People WHERE MID='".(int) $mid."'";
            $res = sql($sql);

            while($row = sqlget($res)) {
                return new CPerson($row);
            }
        }
        return false;
    }

    function get_positions($mid){
        $return = array();

        $sql = "SELECT t1.mid, t1.soid, t1.name as position, t2.name as unit
                FROM structure_of_organ t1
                LEFT JOIN structure_of_organ t2 ON (t2.soid = t1.owner_soid)
                WHERE t1.mid = '$mid'";
        $res = sql($sql);
        while ($row = sqlget($res)) {
            $arr['mid']      = $row['mid'];
            $arr['position'] = htmlspecialchars($row['position'], ENT_QUOTES);
            $arr['unit'] = htmlspecialchars($row['unit'], ENT_QUOTES);
            $return[$row['soid']] = $arr;
        }
        return $return;
    }
    
    function get_persons_by_soids($soids){
    	$return = array();
    	if (!is_array($soids) && !count($soids)) return $return;
    	$soids_str = implode(',', $soids);

    	$sql = "SELECT t1.mid, t1.soid, t1.name, t2.name as unit, People.LastName, People.FirstName, People.Patronymic
	            FROM structure_of_organ t1
	            LEFT JOIN structure_of_organ t2 ON (t2.soid = t1.owner_soid)
	            INNER JOIN People ON (t1.mid = People.MID)
	            WHERE t1.soid IN ({$soids_str})";
    	$res = sql($sql);
    	while ($row = sqlget($res)) {
    		$arr['mid']      = $row['mid'];
    		$arr['position'] = htmlspecialchars($row['name'], ENT_QUOTES);
    		$arr['unit'] = htmlspecialchars($row['unit'], ENT_QUOTES);
    		$arr['name'] = htmlspecialchars($row['LastName'].' '.$row['FirstName'].' '.$row['Patronymic'],ENT_QUOTES);
    		$return[$row['soid']] = $arr;
    	}
    	return $return;
    }

}

?>