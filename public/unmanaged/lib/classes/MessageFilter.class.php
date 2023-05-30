<?php
/**
* используются функции из positions.lib.php
*/

class CMessageFilter {

    var $_is_topic;
    var $_filtered = array();

    function init($is_topic=0) {
        $this->_is_topic = (int) $is_topic;
        $this->_get_basic_messages();
        $this->_get_boss_messages();
        $this->_get_curator_messages();
        $this->_get_study_administration_messages();
        $this->_get_department_messages();
        $this->_get_group_messages();
        $this->_get_course_messages();
        $this->_get_my_messages();
        if ($_SESSION['s']['perm'] == 2){
            $this->_get_teacher_messages();
        }
        $this->_filtered = array_unique($this->_filtered);
    }

    function _is_topic() {
        if ($is_topic) {
            return "AND is_topic=1";
        }
    }

    function _get_teacher_messages() {
        if ($_SESSION['s']['perm'] == 2) {
            $sql = "SELECT DISTINCT CID FROM Teachers WHERE MID = '".(int) $_SESSION['s']['mid']."'";
            $res = sql($sql);
            while($row = sqlget($res)) {
                $sql = "SELECT id FROM forummessages WHERE type IN (5,6,7) AND oid = '".(int) $row['CID']."'".$this->_is_topic();
                $res2 = sql($sql);
                while($row2 = sqlget($res2)) {
                    $this->_filtered[] = $row2['id'];
                }
            }
        }
    }

    function _get_my_messages() {
        $sql = "SELECT id FROM forummessages WHERE mid='".(int) $GLOBALS['s']['mid']."' ".$this->_is_topic();
        $res = sql($sql);
        while($row = sqlget($res)) $this->_filtered[] = $row['id'];
    }

    function _get_basic_messages() {
        $sql = "SELECT id
                FROM forummessages
                WHERE type=0 ".$this->_is_topic();
        $res = sql($sql);
        while($row = sqlget($res)) $this->_filtered[] = $row['id'];
    }

    function _get_boss_messages() {
        $soids = get_soids_by_person($GLOBALS['s']['mid']);
        if (is_array($soids) && count($soids)) {
            foreach($soids as $v) {
                $slaves = get_slaves($v);
                if (is_array($slaves) && count($slaves))
                    foreach($slaves as $slave)
                        $mids[] = $slave['MID'];
            }
        }
        if (is_array($mids) && count($mids)) {
            $sql = "SELECT id
                    FROM forummessages
                    WHERE mid IN ('".join("','",$mids)."') AND type=1 ".$this->_is_topic();
            $res = sql($sql);
            while($row = sqlget($res)) $this->_filtered[] = $row['id'];
        }
    }

    function _get_orgunit_slaves($orgunit_soid) {
        static $mids = array();
        if ($orgunit_soid>0) {
            $sql = "SELECT soid as soid, mid as mid, type as type FROM structure_of_organ WHERE owner_soid='".(int) $orgunit_soid."'";
            $res = sql($sql);
            while($row=sqlget($res)) {
                if ($row['mid'] > 0) $mids[] = $row['mid'];
                if ($row['type'] == 2) //$mids = array_merge($mids,$this->_get_orgunit_slaves($row['soid']));
                $this->_get_orgunit_slaves($row['soid']);
            }
        }
        return $mids;
    }

    function _get_curator_messages() {
        /*
        if (defined('APPLICATION_BRANCH') && (APPLICATION_BRANCH==APPLICATION_BRANCH_ACADEMIC)) {
            // Academic

            $sql = "SELECT DISTINCT People.MID
                    FROM People
                    INNER JOIN groupuser ON (groupuser.mid=People.MID)
                    INNER JOIN departments_groups ON (departments_groups.gid=groupuser.gid)
                    INNER JOIN departments ON (departments.did=departments_groups.did)
                    WHERE departments.mid='".(int) $GLOBALS['s']['mid']."' AND departments.application = '".DEPARTMENT_APPLICATION."'";
            $res = sql($sql);
            while($row = sqlget($res)) $mids[] = $row['MID'];

        } else {
            // Corporate
            $sql = "SELECT DISTINCT departments_soids.soid as soid
                    FROM departments_soids
                    INNER JOIN departments ON (departments.did=departments_soids.did)
                    WHERE departments.mid='".(int) $GLOBALS['s']['mid']."' AND departments.application = '".DEPARTMENT_APPLICATION."'";
            $res = sql($sql);
            $mids = array();
            while($row = sqlget($res)) {
                if ($row['soid']>0) $mids = array_merge($mids,$this->_get_orgunit_slaves($row['soid']));
            }

            $mids = array_unique($mids);

        }

        if (is_array($mids) && count($mids)) {
            $sql = "SELECT id
                    FROM forummessages
                    WHERE mid IN ('".join("','",$mids)."') AND type=2 ".$this->_is_topic();
            $res = sql($sql);
            while($row = sqlget($res)) $this->_filtered[] = $row['id'];
        }
        */
        $sql = "SELECT DISTINCT departments_courses.cid as cid
                FROM departments_courses
                INNER JOIN departments ON (departments.did=departments_courses.did)
                WHERE departments.mid='".(int) $GLOBALS['s']['mid']."' AND departments.application = '".DEPARTMENT_APPLICATION."'";
        $res = sql($sql);
        $cids = array();
        while($row = sqlget($res)) {
            $cids[] = $row['cid'];
        }

        if (is_array($cids) && count($cids)) {
            $sql = "SELECT forummessages.id
                    FROM `forummessages`
                    LEFT JOIN `forumthreads` ON (forumthreads.thread = forummessages.thread)
                    LEFT JOIN `forumcategories` ON (forumcategories.id = forumthreads.category)
                    WHERE forumcategories.cid IN ('".join("','",$cids)."') AND type = 2 ".$this->_is_topic();
            $res = sql($sql);
            while($row = sqlget($res)) $this->_filtered[] = $row['id'];
        }
    }

    function _get_study_administration_messages() {
        if ($GLOBALS['s']['perm'] == 3) {
        $sql = "SELECT * FROM deans WHERE MID='".(int) $GLOBALS['s']['mid']."'";
        $res = sql($sql);
        if (sqlrows($res)) {
                /*
            $sql = "SELECT id FROM forummessages
                    WHERE type=3 ".$this->_is_topic();
                */
                //Уч. админ должен видеть всё
                $sql = "SELECT id FROM forummessages
                    WHERE type >= 0 ".$this->_is_topic();
            $res = sql($sql);
            while($row = sqlget($res)) $this->_filtered[] = $row['id'];
        }
    }
    }

    function _get_department_messages() {
        $sql = "SELECT forummessages.id
                FROM forummessages
                INNER JOIN departments ON (departments.did=forummessages.oid)
                WHERE
                    departments.mid='".(int) $GLOBALS['s']['mid']."' AND
                    forummessages.type = 4 AND departments.application = '".DEPARTMENT_APPLICATION."' ".$this->_is_topic();
        $res = sql($sql);
        while($row = sqlget($res)) $this->_filtered[] = $row['id'];
    }

    function _get_groups() {
        $sql = "SELECT groupuser.gid
                FROM groupuser
                WHERE
                    groupuser.mid='".(int) $GLOBALS['s']['mid']."' AND
                    groupuser.cid>0";
        $res = sql($sql);
        while($row = sqlget($res)) {

        }
    }

    function _get_group_messages() {
        $sql = "SELECT DISTINCT groupuser.gid
                FROM groupuser
                WHERE
                    groupuser.mid='".(int) $GLOBALS['s']['mid']."' AND
                    groupuser.cid='-1'"; //под группами подразумеваются учебные группы, а не группы на курсе
        $res = sql($sql);
        while($row = sqlget($res)) {
            if ($row['gid']) {
                $sql = "SELECT forummessages.id
                        FROM forummessages
                        INNER JOIN groupuser ON (groupuser.mid=forummessages.mid)
                        WHERE groupuser.gid='".(int) $row['gid']."' AND forummessages.type=5
                        ".$this->_is_topic();
                $res2 = sql($sql);
                while($row2 = sqlget($res2)) {
                    $this->_filtered[] = $row2['id'];
                }

            }
        }
    }

    function _get_course_messages() {
        $sql = "SELECT forummessages.id
                FROM forummessages
                INNER JOIN Students ON (Students.CID=forummessages.oid)
                WHERE
                    Students.MID='".$GLOBALS['s']['mid']."' AND
                    forummessages.type=6 ".$this->_is_topic();
        $res = sql($sql);
        while($row = sqlget($res)) $this->_filtered[] = $row['id'];
    }

    function is_filtered($id) {
        if (is_array($this->_filtered) && count($this->_filtered))
            if (in_array($id,$this->_filtered)) return true;
    }
}
?>