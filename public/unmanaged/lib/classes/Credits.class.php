<?php

class CCredits {       
    
    function getTrackFreeCredits($track) {
        if ($track) {
            $sql = "SELECT credits_free FROM tracks WHERE trid='".(int) $track."'";
            $res = sql($sql);
            if (sqlrows($res)) {
                $row = sqlget($res);
                return $row['credits_free'];
            }
        }
    }
    
    function countTrackCredits($track) {
        if ($track > 0) {
            $sql = "SELECT Courses.CID, Courses.credits_student
                    FROM Courses 
                    INNER JOIN tracks2course ON (tracks2course.cid=Courses.CID)
                    WHERE 
                    tracks2course.trid = '".(int) $track."'";
            $res = sql($sql);
            while($row = sqlget($res)) {
                $totalcredits += (int) $row['credits_student'];
            }
        }
        return (int) $totalcredits;
    }
    
    function countTrackLevelCredits($track,$level) {
        if ($track > 0) {
            $sql = "SELECT Courses.CID, Courses.credits_student
                    FROM Courses 
                    INNER JOIN tracks2course ON (tracks2course.cid=Courses.CID)
                    WHERE 
                    tracks2course.trid = '".(int) $track."' AND
                    tracks2course.level = '".(int) $level."'";
            $res = sql($sql);
            while($row = sqlget($res)) {                
                $totalcredits += (int) $row['credits_student'];
            }
        }
        return (int) $totalcredits;
    }
    
    function countCreditsByMid($mid) {
        if ($mid>0) {
            $sql = "SELECT sum FROM money WHERE mid='".(int) $mid."'";
            $res = sql($sql);
            while($row = sqlget($res)) {
                $totalcredits += (int) $row['sum'];
            }            
        }
        return (int) $totalcredits;
    }
    
    function countMandatoryProgramCreditsByMid($mid) {
        if ($mid>0) {
            $sql = "SELECT DISTINCT Courses.CID, Courses.credits_student
                    FROM Courses
                    INNER JOIN tracks2course ON (tracks2course.cid=Courses.CID)
                    INNER JOIN tracks2mid ON (tracks2mid.trid=tracks2course.trid AND tracks2mid.level=tracks2course.level)
                    WHERE tracks2mid.mid='".(int) $mid."'";
            $res = sql($sql);
            while($row = sqlget($res)) {
                $totalcredits += (int) $row['credits_student'];
            }            
            
        }
        return (int) $totalcredits;
    }
    
    function countFreeProgramCreditsByMid($mid) {
        if ($mid>0) {
            $sql = "SELECT DISTINCT Courses.CID, Courses.credits_student
                    FROM Courses
                    INNER JOIN Students ON (Students.CID=Courses.CID)
                    LEFT JOIN tracks2course ON (tracks2course.cid=Courses.CID)
                    WHERE Students.MID = '".(int) $mid."' 
                    AND tracks2course.cid IS NULL
                    ";
            $res = sql($sql);
            while($row = sqlget($res)) {
                $totalcredits += (int) $row['credits_student'];
            }            
        }
        return (int) $totalcredits;
    }

    function countFreeProgramCredits($mid) {
        if ($mid > 0) {
            $sql = "SELECT tracks.credits_free
                    FROM tracks
                    INNER JOIN tracks2mid ON (tracks2mid.trid=tracks.trid)
                    WHERE 
                    tracks2mid.mid = '".(int) $mid."'";
            $res = sql($sql);
            while($row = sqlget($res)) {
                $totalcredits += (int) $row['credits_free'];
            }
        }
        return (int) $totalcredits;
    }
    
    function checkCourseRegistrationPossibility($course,$mid) {
        if ($course && $mid) {
            $sql = "SELECT sum FROM money WHERE mid='".(int) $mid."'";
            $res = sql($sql);
            if (sqlrows($res)) {
                while($row = sqlget($res)) {
                    $account += (int) $row['sum'];
                }
                $sql = "SELECT Courses.credits_student 
                        FROM Courses 
                        WHERE 
                        Courses.CID='".(int) $course."' AND
                        Courses.credits_student <= '".(int) $account."'";
                $res = sql($sql);
                if (sqlrows($res)==1) {
                    //$row = sqlget($res);
                    //if ((CCredits::countFreeProgramCredits($mid)-CCredits::countFreeProgramCreditsByMid($mid)) >= $row['credits_student'])
                        return true;
                }
            }
        }
        return false;
    }
    
    function decreaseCredits($mid,$value) {
        if ($mid && $value) {
            $sql = "UPDATE money 
                    SET sum=sum-".(int) $value."
                    WHERE mid='".(int) $mid."' AND sum>=".(int) $value."
                    LIMIT 1
                    ";
            sql($sql);
        }
    }
    
    function payCourse($mid,$cid) {
        if ($mid && $cid) {
            $sql = "SELECT credits_student FROM Courses WHERE CID='".(int) $cid."'";
            $res = sql($sql);
            if (sqlrows($res)) {
                $row = sqlget($res);
                CCredits::decreaseCredits($mid,$row['credits_student']);
            }
        }
    }
        
}

?>