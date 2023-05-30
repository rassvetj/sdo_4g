<?php

class CPosition extends CDBObject {

    var $table = 'structure_of_organ';

	function deleteItem($soid) {
	    if ($soid > 0) {
	        $sql = "SELECT soid, type FROM structure_of_organ WHERE owner_soid='".(int) $soid."'";
	        $res = sql($sql);
	        while($row = sqlget($res)) {
	            if ($row['type'] == 2) CPosition::deleteItem($row['soid']);

	            sql("DELETE FROM structure_of_organ WHERE soid = '".(int) $row['soid']."'");
	            sql("DELETE FROM str_of_organ2competence WHERE soid = '".(int) $row['soid']."'");
	            sql("DELETE FROM departments_soids WHERE soid = '".(int) $row['soid']."'");
	            sql("DELETE FROM structure_of_organ_roles WHERE soid = '".(int) $row['soid']."'");

	        }

	        sql("DELETE FROM structure_of_organ WHERE soid = '".(int) $soid."'");
	        sql("DELETE FROM str_of_organ2competence WHERE soid = '".(int) $soid."'");
	        sql("DELETE FROM departments_soids WHERE soid = '".(int) $soid."'");
	        sql("DELETE FROM structure_of_organ_roles WHERE soid = '".(int) $soid."'");

	    }
	}

    function getPerson($soid) {
        if ($soid) {
            $sql = "SELECT People.*
                    FROM People
                    INNER JOIN structure_of_organ ON (structure_of_organ.mid = People.MID)
                    WHERE structure_of_organ.soid = '".(int) $soid."'";
            $res = sql($sql);

            while($row = sqlget($res)) {
                return new CPerson($row);
            }
        }
        return false;
    }

    function getSoidsByMid($mid) {
    	$return = array();
        if ($mid) {
            $sql = "SELECT structure_of_organ.soid
                    FROM structure_of_organ
                    INNER JOIN People ON (structure_of_organ.mid = People.MID)
                    WHERE People.mid = '".(int) $mid."'";
            $res = sql($sql);

            while($row = sqlget($res)) {
                $return[] = $row['soid'];
            }
        }
        return $return;
    }

    function getPersonName($soid,$mode='LF') {
        if ($soid) {
            $sql = "SELECT People.LastName, People.FirstName, People.Patronymic, People.Login
                    FROM People
                    INNER JOIN structure_of_organ ON (structure_of_organ.mid = People.MID)
                    WHERE structure_of_organ.soid = '".(int) $soid."'";
            $res = sql($sql);

            while($row = sqlget($res)) {
                $person = new CPerson($row);
                $method = 'getName'.$mode;
                return $person->$method();
            }
        }
        return false;
    }

    function get($soid) {
        $ret = false;
        if ($soid) {
            $sql = "SELECT * FROM structure_of_organ WHERE soid='".(int) $soid."'";
            $res = sql($sql);

            while($row = sqlget($res)) {
                $ret = new CPosition($row);
            }
        }
        return $ret;
    }

    function get_roles($soid) {
        $ret = false;
        if ($soid) {
            $sql = "SELECT role FROM structure_of_organ_roles WHERE soid='".(int) $soid."'";
            $res = sql($sql);

            while($row = sqlget($res)) {
                $ret[] = $row['role'];
            }
        }
        return $ret;
    }

    function getMidsBySoids($soids) {
        $rows = array();
        if (is_array($soids) && count($soids)) {
            $sql = "SELECT soid, mid FROM structure_of_organ WHERE soid IN ('".join("','",$soids)."')";
            $res = sql($sql);

            while($row = sqlget($res)) {
                $rows[$row['soid']] = $row['mid'];
            }
        }
        return $rows;
    }

    function getPosition($row) {
        switch($row['type']) {
            case 1:
                return new CHeadPosition($row);
            break;
            case 2:
                return new CUnitPosition($row);
            break;
            default:
                return new CSlavePosition($row);
        }
    }

}

class CSlavePosition extends CPosition {

}

class CHeadPosition extends CSlavePosition {

}

class CUnitPosition extends CPosition {
    function getSlaves($soid) {
        $ret = false;
        if ($soid) {
            $sql  = "SELECT * FROM structure_of_organ WHERE owner_soid='".(int) $soid."' ORDER BY type DESC, name";
            $res = sql($sql);

            while($row = sqlget($res)) {

                $ret[] = CPosition::getPosition($row);
            }
        }
        return $ret;
    }

    function getSlavesIdAll($soids) {
        $ret = array();
        if (is_array($soids) && count($soids)) {
            foreach($soids as $soid) {
                $sql = "SELECT soid, type FROM structure_of_organ WHERE owner_soid = ".(int) $soid.' ORDER BY type DESC, name';
                $res = sql($sql);

                while($row = sqlget($res)) {
                    $ret[] = $row['soid'];
                    if ($row['type']==2) {
                        $ret = array_merge($ret,CUnitPosition::getSlavesIdAll(array($row['soid'])));
                    }
                }
            }
        }
        return $ret;
    }

    function getSlavesAll($soids) {
        $ret = array();
        if (is_array($soids) && count($soids)) {
            foreach($soids as $soid) {
                $sql = "SELECT * FROM structure_of_organ WHERE owner_soid = ".(int) $soid.' ORDER BY type DESC, name';
                $res = sql($sql);

                while($row = sqlget($res)) {
                    $ret[] = CPosition::getPosition($row);
                    if ($row['type']==2) {
                        $ret = array_merge($ret,CUnitPosition::getSlavesAll(array($row['soid'])));
                    }
                }
            }
        }
        return $ret;
    }

    function getSlavesPeopleId($soids) {
        $ret = array();
        if (is_array($soids) && count($soids)) {
            foreach($soids as $soid) {
                $sql = "SELECT soid, type, mid FROM structure_of_organ WHERE owner_soid = ".(int) $soid.' ORDER BY type DESC, name';
                $res = sql($sql);

                while($row = sqlget($res)) {
                    if ($row['type']==2) {
                        $ret = array_merge($ret,CUnitPosition::getSlavesPeopleId(array($row['soid'])));
                    } else {
                    	if ($row['mid'] > 0) {
                            $ret[] = $row['mid'];
                    	}
                    }
                }
            }
        }
        return $ret;
    }

    function getMasterId($soids){ // на вхооде и на выходе массивы - на случай совмещения
        $ret = array();
        if (is_array($soids) && count($soids)) {
            foreach($soids as $soid) {
                $sql = "SELECT owner_soid FROM structure_of_organ WHERE soid = ".(int) $soid.' ORDER BY name';
                $res = sql($sql);
                if (($row = sqlget($res))) {
                    $ret[] = $row['owner_soid'];
                }
            }
        }
        return $ret;
    }

    function getMastersIdAll($soids){
        $ret = array();
        if (is_array($soids) && count($soids)) {
            foreach($soids as $soid) {
                $sql = "SELECT owner_soid FROM structure_of_organ WHERE soid = ".(int) $soid.' ORDER BY name';
                $res = sql($sql);
                if (($row = sqlget($res)) && !empty($row['owner_soid'])) {
                    $ret[] = $row['owner_soid'];
                    $ret = array_merge($ret,CUnitPosition::getMastersIdAll(array($row['owner_soid'])));
                }
            }
        }
        return $ret;
    }
}

?>