<?php

/**
*  Usage example:
*  --------------------------------------------------------------
*  $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);
*  ...
*  if ($peopleFilter->is_filtered($mid))
*/

$GLOBALS['TRACK_FILTERS'] = array('CTrackFilter');

$GLOBALS['COURSE_FILTERS'] = array('CCourseFilter_Department');

$GLOBALS['SOID_FILTERS'] = false;
//$GLOBALS['SOID_FILTERS'] = array('CSoidFilter_Corporate');

$GLOBALS['PEOPLE_FILTERS'] = array('CPeopleFilter_Academic');
/*if (defined('APPLICATION_BRANCH') && (APPLICATION_BRANCH==APPLICATION_BRANCH_ACADEMIC))
    $GLOBALS['PEOPLE_FILTERS'] = array('CPeopleFilter_Academic');
else
    $GLOBALS['PEOPLE_FILTERS'] = array('CPeopleFilter_Corporate');
*/
class CSoidFilter extends CCoreFilter_Abstract {

    function CSoidFilter($filter_classes) {
        parent::CCoreFilter_Abstract($filter_classes);
    }

    function init() {
        $sql = "SELECT soid as soid FROM structure_of_organ";
        $res = sql($sql);
        while($row = sqlget($res)) $this->filtered[$row['soid']] = true;
    }

}

class CSoidFilter_Dummy extends CFilter_Abstract {

    function CSoidFilter_Dummy() {
        parent::CFilter_Abstract();
    }

    function init() {
        $sql = "SELECT soid as soid FROM structure_of_organ WHERE soid>0";
        $res = sql($sql);
        while($row = sqlget($res)) $this->filtered[$row['soid']] = true;
    }

}

class CPeopleFilter extends CCoreFilter_Abstract {

    function CPeopleFilter($filter_classes) {
        parent::CCoreFilter_Abstract($filter_classes);
    }

    function init() {
        $sql = "SELECT MID FROM People";
        $res = sql($sql);
        while($row = sqlget($res)) $this->filtered[$row['MID']] = true;
    }
}

class CCourseFilter extends CCoreFilter_Abstract {

    function CCourseFilter($filter_classes) {
        parent::CCoreFilter_Abstract($filter_classes);
    }

    function init() {
        $sql = "SELECT CID FROM Courses";
        $res = sql($sql);
        while($row = sqlget($res)) $this->filtered[$row['CID']] = true;
    }

}

class CTrackFilter extends CCoreFilter_Abstract {

    function CTrackFilter($filter_classes) {
        parent::CCoreFilter_Abstract($filter_classes);
    }

    function init() {
        $sql = "SELECT trid FROM tracks";
        $res = sql($sql);
        while($row = sqlget($res)) $this->filtered[$row['trid']] = true;
    }

}

// Фильтр-пустышка - для тестов
class CPeopleFilter_Dummy extends CFilter_Abstract {

    function CPeopleFilter_Dummy() {
        parent::CFilter_Abstract();
    }

    function init() {
        $sql = "SELECT MID FROM People WHERE MID<>1";
        $res = sql($sql);
        while($row = sqlget($res)) $this->filtered[$row['MID']] = true;
    }

}

/**
* Фильтр, связанный с учебной структурой и структурой организации
*                  | учебная структура | структура организации
* рук. должность                                  X                  видит своих подчиненных
* рук. должность            X                     X                  видит свои отделы и своиъ подчинённых
* должность                                       X                  ничего не видит
* должность                 X                     X                  видит свои отделы
*/
class CPeopleFilter_Corporate extends CFilter_Abstract {
    var $is_structured = false;
    var $is_hred = false;
    var $need_not_structured = false;
    var $processed_soids = array();

    function CPeopleFilter_Corporate() {
        parent::CFilter_Abstract();
    }

    function init() {
        $this->_get_department($GLOBALS['s']['mid']);
        $this->_get_slaves($GLOBALS['s']['mid']);
        $this->_get_subordinates($GLOBALS['s']['mid']);
        $this->_get_people_not_in_structure();
        if (!$this->is_structured && !$this->is_hred) {
            $this->_get_all();
            return true;
        }
        $this->_get_students();
        $this->_get_claimants();
        $this->_get_graduated();
        $this->filtered[$GLOBALS['s']['mid']] = true;
        //$this->filtered = array_unique($this->filtered);
    }

    function _get_department_courses_people($did) {
    	
    }
    
    function _get_department($mid) {
        if ($mid) {
            $sql = "SELECT did, not_in FROM departments WHERE mid='".(int) $mid."' AND application = '".DEPARTMENT_APPLICATION."'";
            $res = sql($sql);
            while($row = sqlget($res)) {
                if ($row['not_in']) $this->need_not_structured = true;
                $this->is_hred = true;
            }
        }
    }

    /**
    * Добавляет в фильтр людей, которых нет в структуре, но есть в People
    * @return bool
    */
    function _get_people_not_in_structure() {
        if ($this->need_not_structured) {
            $sql = "SELECT DISTINCT People.MID
                    FROM People LEFT JOIN structure_of_organ ON (structure_of_organ.mid=People.MID)
                    WHERE structure_of_organ.mid IS NULL";
            $res = sql($sql);
            while($row = sqlget($res)) {
                $this->filtered[$row['MID']] = true;
            }
        }
    }

    function _get_graduated() {
       if ($GLOBALS['s']['perm']==2 && is_array($GLOBALS['s']['tkurs']) && count($GLOBALS['s']['tkurs'])) {
           $sql = "SELECT DISTINCT People.MID FROM
                   People INNER JOIN graduated ON (graduated.MID=People.MID)
                   WHERE graduated.CID IN ('".join("','",$GLOBALS['s']['tkurs'])."')";
           $res = sql($sql);
           while($row = sqlget($res)) {
               $this->filtered[$row['MID']] = true;
           }
       }
    }


    function _get_claimants() {
       if ($GLOBALS['s']['perm']==2 && is_array($GLOBALS['s']['tkurs']) && count($GLOBALS['s']['tkurs'])) {
           $sql = "SELECT DISTINCT People.MID FROM
                   People INNER JOIN claimants ON (claimants.MID=People.MID)
                   WHERE claimants.CID IN ('".join("','",$GLOBALS['s']['tkurs'])."')";
           $res = sql($sql);
           while($row = sqlget($res)) {
               $this->filtered[$row['MID']] = true;
           }
       }
    }

    function _get_students() {
       if ($GLOBALS['s']['perm']==2 && is_array($GLOBALS['s']['tkurs']) && count($GLOBALS['s']['tkurs'])) {
           $this->filtered = array();
           $sql = "SELECT DISTINCT People.MID FROM
                   People INNER JOIN Students ON (Students.MID=People.MID)
                   WHERE Students.CID IN ('".join("','",$GLOBALS['s']['tkurs'])."')";
           $res = sql($sql);
           while($row = sqlget($res)) {
               $this->filtered[$row['MID']] = true;
           }
       }
    }

    function _get_slaves_by_soid($soid,$not_mid) {
        if ($soid>0 && !in_array($soid, $this->processed_soids)) {
            $sql = "SELECT soid as soid, type as type, mid as mid
                    FROM structure_of_organ
                    WHERE owner_soid='".(int) $soid."' AND mid NOT IN ('".$not_mid."')";
            $res = sql($sql);
            while($row=sqlget($res)) {
                if ($row['type']==2) $this->_get_slaves_by_soid($row['soid'],$not_mid);
                if ($row['mid']>0) $this->filtered[$row['mid']] = true;
            }
            $this->processed_soids[] = $soid;
        }
    }

    function _get_slaves($mid) {
        $sql = "SELECT owner_soid as owner_soid FROM structure_of_organ WHERE mid='".(int) $mid."' AND type='1'";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $this->is_structured = true;
            if ($row['owner_soid']>0) $this->_get_slaves_by_soid($row['owner_soid'],$mid);
        }
    }

    function _get_subordinates($mid) {
        $sql = "SELECT DISTINCT People.MID
                FROM People
                INNER JOIN groupuser ON (groupuser.mid=People.MID)
                INNER JOIN departments_groups ON (departments_groups.gid=groupuser.gid)
                INNER JOIN departments ON (departments.did=departments_groups.did)
                WHERE departments.mid='".(int) $mid."' AND departments.application = '".DEPARTMENT_APPLICATION."'";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $this->is_structured = true;
            $this->filtered[$row['MID']] = true;
        }

        $sql = "SELECT DISTINCT People.MID
                FROM People
                INNER JOIN Students ON (Students.MID=People.MID)
                INNER JOIN departments_courses ON (departments_courses.cid=Students.CID)
                INNER JOIN departments ON (departments.did=departments_courses.did)
                WHERE departments.mid='".(int) $mid."' AND departments.application = '".DEPARTMENT_APPLICATION."'";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $this->is_structured = true;
            $this->filtered[$row['MID']] = true;
        }

        $sql = "SELECT DISTINCT People.MID
                FROM People
                INNER JOIN claimants ON (claimants.MID=People.MID)
                INNER JOIN departments_courses ON (departments_courses.cid=Students.CID)
                INNER JOIN departments ON (departments.did=departments_courses.did)
                WHERE departments.mid='".(int) $mid."' AND departments.application = '".DEPARTMENT_APPLICATION."'";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $this->is_structured = true;
            $this->filtered[$row['MID']] = true;
        }        
        return true;

/*
        $sql = "SELECT DISTINCT departments_soids.soid as soid
                FROM departments_soids
                INNER JOIN departments ON (departments.did=departments_soids.did)
                WHERE departments.mid='".(int) $mid."' AND departments.application = '".DEPARTMENT_APPLICATION."'";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $this->is_hred = true;
            if ($row['soid']>0) $this->_get_slaves_by_soid($row['soid'],$mid);
        }
*/

    }

    function _get_all() {
        $sql = "SELECT MID FROM People";
        $res = sql($sql);
        while($row = sqlget($res)) $this->filtered[$row['MID']] = true;
    }

}

/**
* Фильтр людей из курируемых групп (курируемые группы задаются в уч. структуре)
* (для академической версии els)
*/
class CPeopleFilter_Academic extends CFilter_Abstract {
    var $is_structured = false;
    var $need_not_structured = false;

    function CPeopleFilter_Academic() {
        parent::CFilter_Abstract();
    }

    function init() {
        $this->_get_department($GLOBALS['s']['mid']);
        $this->_get_slaves($GLOBALS['s']['mid']);
        $this->_get_people_not_in_structure();
        if (!$this->is_structured) {
            $this->_get_all();
            return true;
        }
        $this->_get_students();
        $this->_get_claimants();
        $this->_get_graduated();

        $this->filtered[$GLOBALS['s']['mid']] = true;
        //$this->filtered = array_unique($this->filtered);
    }

    function _get_department($mid) {
        if ($mid) {
            $sql = "SELECT did, not_in FROM departments WHERE mid='".(int) $mid."' AND application = '".DEPARTMENT_APPLICATION."'";
            $res = sql($sql);
            while($row = sqlget($res)) {
                if ($row['not_in']) $this->need_not_structured = true;
                $this->is_structured = true;
            }
        }
    }

    function _get_people_not_in_structure() {
        if ($this->need_not_structured) {
            $sql = "SELECT DISTINCT People.MID
                    FROM People LEFT JOIN groupuser ON (groupuser.mid=People.MID)
                    WHERE groupuser.mid IS NULL";
            $res = sql($sql);
            while($row = sqlget($res)) {
                $this->filtered[$row['MID']] = true;
            }
        }
    }

    function _get_graduated() {
       if ($GLOBALS['s']['perm']==2 && is_array($GLOBALS['s']['tkurs']) && count($GLOBALS['s']['tkurs'])) {
           $sql = "SELECT DISTINCT People.MID FROM
                   People INNER JOIN graduated ON (graduated.MID=People.MID)
                   WHERE graduated.CID IN ('".join("','",$GLOBALS['s']['tkurs'])."')";
           $res = sql($sql);
           while($row = sqlget($res)) {
               $this->filtered[$row['MID']] = true;
           }
       }
    }


    function _get_claimants() {
       if ($GLOBALS['s']['perm']==2 && is_array($GLOBALS['s']['tkurs']) && count($GLOBALS['s']['tkurs'])) {
           $sql = "SELECT DISTINCT People.MID FROM
                   People INNER JOIN claimants ON (claimants.MID=People.MID)
                   WHERE claimants.CID IN ('".join("','",$GLOBALS['s']['tkurs'])."')";
           $res = sql($sql);
           while($row = sqlget($res)) {
               $this->filtered[$row['MID']] = true;
           }
       }
    }

    function _get_students() {
       if ($GLOBALS['s']['perm']==2 && is_array($GLOBALS['s']['tkurs']) && count($GLOBALS['s']['tkurs'])) {
           $this->filtered = array();
           $sql = "SELECT DISTINCT People.MID FROM
                   People INNER JOIN Students ON (Students.MID=People.MID)
                   WHERE Students.CID IN ('".join("','",$GLOBALS['s']['tkurs'])."')";
           $res = sql($sql);
           while($row = sqlget($res)) {
               $this->filtered[$row['MID']] = true;
           }
       }
    }

    function _get_slaves($mid) {
        $sql = "SELECT DISTINCT People.MID
                FROM People
                INNER JOIN groupuser ON (groupuser.mid=People.MID)
                INNER JOIN departments_groups ON (departments_groups.gid=groupuser.gid)
                INNER JOIN departments ON (departments.did=departments_groups.did)
                WHERE departments.mid='".(int) $mid."' AND departments.application = '".DEPARTMENT_APPLICATION."'";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $this->is_structured = true;
            $this->filtered[$row['MID']] = true;
        }
        
        $sql = "SELECT DISTINCT People.MID
                FROM People
                INNER JOIN Students ON (Students.MID=People.MID)
                INNER JOIN departments_courses ON (departments_courses.cid=Students.CID)
                INNER JOIN departments ON (departments.did=departments_courses.did)
                WHERE departments.mid='".(int) $mid."' AND departments.application = '".DEPARTMENT_APPLICATION."'";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $this->is_structured = true;
            $this->filtered[$row['MID']] = true;
        }
        
        $sql = "SELECT DISTINCT People.MID
                FROM People
                INNER JOIN claimants ON (claimants.MID=People.MID)
                INNER JOIN departments_courses ON (departments_courses.CID=claimants.CID)
                INNER JOIN departments ON (departments.did=departments_courses.did)
                WHERE departments.mid='".(int) $mid."' AND departments.application = '".DEPARTMENT_APPLICATION."'";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $this->is_structured = true;
            $this->filtered[$row['MID']] = true;
        }        
    }

    function _get_all() {
        $sql = "SELECT MID FROM People";
        $res = sql($sql);
        while($row = sqlget($res)) $this->filtered[$row['MID']] = true;
    }
}

class CSoidFilter_Corporate extends CFilter_Abstract {
    var $is_structured = false;
    var $is_hred = false;

    function CSoidFilter_Work() {
        parent::CFilter_Abstract();
    }

    function init() {
        $this->_get_slaves($GLOBALS['s']['mid']);
        $this->_get_subordinates($GLOBALS['s']['mid']);
        if (!$this->is_structured && !$this->is_hred) $this->_get_all();
        //$this->filtered = array_unique($this->filtered);
    }

    function _get_slaves_by_soid($soid) {
        if ($soid>0) {
            $this->filtered[$soid] = true;
            $sql = "SELECT soid as soid, type as type FROM structure_of_organ WHERE owner_soid='".(int) $soid."'";
            $res = sql($sql);
            while($row=sqlget($res)) {
                if ($row['type']==2) $this->_get_slaves_by_soid($row['soid']);
                $this->filtered[$row['soid']] = true;
            }
        }
    }

    function _get_slaves($mid) {
        $sql = "SELECT owner_soid as owner_soid FROM structure_of_organ WHERE mid='".(int) $mid."' AND type='1'";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $this->is_structured = true;
            if ($row['owner_soid']>0) $this->_get_slaves_by_soid($row['owner_soid']);
        }
    }

    function _get_subordinates($mid) {
        $sql = "SELECT DISTINCT departments_soids.soid as soid
                FROM departments_soids
                INNER JOIN departments ON (departments.did=departments_soids.did)
                WHERE departments.mid='".(int) $mid."' AND departments.application = '".DEPARTMENT_APPLICATION."'";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $this->is_hred = true;
            $this->_get_slaves_by_soid($row['soid']);
        }
    }

    function _get_all() {
        $sql = "SELECT soid FROM structure_of_organ";
        $res = sql($sql);
        while($row = sqlget($res)) $this->filtered[$row['soid']] = true;
    }
}


class CCourseFilter_Department extends CFilter_Abstract {
    var $is_structured = false;

    function CCourseFilter_Department() {
        parent::CFilter_Abstract();
    }

    function init() {
//        if (is_specialities_exists()) {
//            $this->_get_other_courses($GLOBALS['s']['mid']);
//        }
//        if (!$this->is_structured) $this->_get_all();
        $this->_get_my_courses($GLOBALS['s']['mid']);
        if (!$this->is_structured && ($_SESSION['s']['perm']==3)) $this->_get_all();
        //$this->filtered = array_unique($this->filtered);
    }

    function _get_my_courses_student($mid) {
        $sql = "SELECT CID FROM Students WHERE MID='".(int) $mid."'";
        $res = sql($sql);
        while($row=sqlget($res))
            $this->filtered[$row['CID']] = true;
    }

    function _get_my_courses_teacher($mid) {
        $sql = "SELECT CID FROM Teachers WHERE MID='".(int) $mid."'";
        $res = sql($sql);
        while($row=sqlget($res)) {
            $this->filtered[$row['CID']] = true;
        }
    }

    function _get_my_courses($mid) {
        
		$cid = 0;
		if (USE_AT_INTEGRATION) {
        $cid = (int) getField('Courses','CID','is_poll','1');
		}
        
        if ($_SESSION['s']['perm']==1) {
            $this->_get_my_courses_student($mid);
            if (isset($this->filtered[$cid])) {
                unset($this->filtered[$cid]);
            }
            return;
        }

        // Курируемые курсы (эл уч структуры)
        $this->_get_other_courses($mid);

        if ($_SESSION['s']['perm']==2) {
            $this->_get_my_courses_teacher($mid);
            if (isset($this->filtered[$cid])) {
                unset($this->filtered[$cid]);
            }
            return;
        }

        if ($_SESSION['s']['perm']==4) {
            $this->_get_all();
            return;
        }

        // Созданные мной курсы
        $sql = "SELECT CID FROM Courses WHERE createby='".(int) $mid."' AND `type`='0'";
        $res = sql($sql);
        while($row=sqlget($res)) {
            $this->filtered[$row['CID']] = true;
        }
        
        if (isset($this->filtered[$cid])) {
            unset($this->filtered[$cid]);
        }
        
    }

    function _get_other_courses($mid) {
        $sql = "SELECT DISTINCT departments_courses.cid as CID
                FROM departments_courses
                INNER JOIN departments ON (departments.did=departments_courses.did)
                WHERE departments.mid='".(int) $mid."' AND departments.application = '".DEPARTMENT_APPLICATION."'";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $this->is_structured = true;
            $this->filtered[$row['CID']] = true;
        }
    }

    /*
    function _get_other_courses($mid) {
        $sql = "SELECT DISTINCT tracks2course.cid as CID
                FROM tracks2course
                INNER JOIN tracks ON (tracks.trid=tracks2course.trid)
                INNER JOIN departments_tracks ON (departments_tracks.track=tracks.trid)
                INNER JOIN departments ON (departments.did=departments_tracks.did)
                WHERE departments.mid='".(int) $mid."'";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $this->is_structured = true;
            $this->filtered[] = $row['CID'];
        }
    }
    */

    function _get_all() {		
        $sql = "SELECT CID FROM Courses WHERE `type`='0'";
		if (USE_AT_INTEGRATION) {
            $sql .= " AND is_poll <> '1'";
        }
        $res = sql($sql);
        while($row = sqlget($res)) $this->filtered[$row['CID']] = true;
    }
}

class CTrackFilter_Department extends CFilter_Abstract {
    var $is_structured = false;

    function CTrackFilter_Department() {
        parent::CFilter_Abstract();
    }

    function init() {
        if (is_specialities_exists()) {
            $this->_get_my_tracks($GLOBALS['s']['mid']);
            $this->_get_other_tracks($GLOBALS['s']['mid']);
        }
        if (!$this->is_structured) $this->_get_all();
        //$this->filtered = array_unique($this->filtered);
    }

    function _get_my_tracks($mid) {
        $sql = "SELECT DISTINCT trid FROM tracks2mid WHERE mid='".(int) $mid."'";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $this->filtered[$row['trid']] = true;
        }
    }

    function _get_other_tracks($mid) {
        $sql = "SELECT DISTINCT departments_tracks.track as trid
                FROM departments_tracks
                INNER JOIN departments ON (departments.did=departments_tracks.did)
                WHERE departments.mid='".(int) $mid."' AND departments.application = '".DEPARTMENT_APPLICATION."'";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $this->is_structured = true;
            $this->filtered[$row['trid']] = true;
        }
    }

    function _get_all() {
        $sql = "SELECT trid FROM tracks";
        $res = sql($sql);
        while($row = sqlget($res)) $this->filtered[$row['trid']] = true;
    }
}

// Прототип для фильтра
class CFilter_Abstract {
    var $filtered = array();

    function CFilter_Abstract() {
        $this->init();
    }

    function init() {
    }

    function execute(&$data) {
        if (is_array($data) && count($data)) {
            reset($data);
            while(list($k,$v) = each($data)) {
                if (!$this->filtered[$k]) unset($data[$k]);
            }
        }
    }
}

class CCoreFilter_Abstract {
    var $filter_classes;
    var $filtered = array();

    function CCoreFilter_Abstract($filter_classes) {
        $this->filter_classes = $filter_classes;
        $this->init();
        if (is_array($filter_classes) && count($filter_classes)) {
            foreach($filter_classes as $filter_class)
                if (class_exists($filter_class)) {
                    $filter = new $filter_class();
                    if (method_exists($filter,'execute'))
                        $filter->execute($this->filtered);
                }
        }
    }

    function init() {
    }

    function is_filtered($item) {
        if ($this->filtered[$item]) return true;
        return false;
    }
}

class CGroupFilter_Department extends CFilter_Abstract {    

    function CTrackFilter_Department() {
        parent::CFilter_Abstract();
    }

    function init() {
        if ($this->_inDepartment($GLOBALS['s']['mid'])) {
            $this->_get_my_groups($GLOBALS['s']['mid']);
        }else {
            $this->_get_all_groups($GLOBALS['s']['mid']);
        }        
    }

    function _inDepartment($mid) {
        return sqlvalue("SELECT did FROM `departments` WHERE mid='$mid'");
    }
    
    function _get_my_groups($mid) {
        $sql = "SELECT 
                    departments_groups.gid,
                    groupname.name
                FROM `departments`
                LEFT JOIN `departments_groups` ON (departments_groups.did = departments.did)
                LEFT JOIN `groupname` ON (departments_groups.gid = groupname.gid)
                WHERE departments.mid='".(int) $mid."' AND groupname.cid='-1'";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $this->filtered[$row['gid']] = $row['name'];
        }
    }

    function _get_all_groups($mid) {
        $sql = "SELECT 
                    gid, 
                    name
                FROM `groupname`
                WHERE cid='-1'";
        $res = sql($sql);
        while($row = sqlget($res)) {            
            $this->filtered[$row['gid']] = $row['name'];
        }
    }

    function is_filtered($item) {
        if ($this->filtered[$item]) return true;
        return false;
    }   
}

?>