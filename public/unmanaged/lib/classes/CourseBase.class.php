<?php

class CCourseBase extends CDBObject {

    function getListObjects() {
        $ret = array();
        $sql = "SELECT * FROM Courses WHERE type = '1' ORDER BY Title";
        $res = sql($sql);
        
        $courseFilter = new CCourseFilter($GLOBALS['COURSE_FILTERS']);
        $courseFilter->init();
        
        while($row = sqlget($res)) {
            if (!$courseFilter->is_filtered($row['CID'])) continue;
            $ret[$row['CID']] = new CCourseBase($row);
        }
        return $ret;
    }
    
    function getList() {
        $ret = array();
        $sql = "SELECT * FROM Courses WHERE type = '1' ORDER BY Title";
        $res = sql($sql);
        
        $courseFilter = new CCourseFilter($GLOBALS['COURSE_FILTERS']);
        $courseFilter->init();
        
        while($row = sqlget($res)) {
            if (!$courseFilter->is_filtered($row['CID'])) continue;
            $ret[$row['CID']] = $row['Title'];
        }
        return $ret;
    }
    
    function isReviewerExists($cid) {
        $sql = "SELECT mid FROM reviewers WHERE cid = '".(int) $cid."' LIMIT 1";
        $res = sql($sql);
        return sqlrows($res);        
    }
    
    function getReviewersMids($cid) {
        $ret = array();
        if ($cid) {
            $sql = "SELECT DISTINCT mid FROM reviewers WHERE cid = '".(int) $cid."'";
            $res = sql($sql);
            
            while($row = sqlget($res)) {
                $ret[$row['mid']] = $row['mid'];
            }
        }
        return $ret;
    }
    
}

?>