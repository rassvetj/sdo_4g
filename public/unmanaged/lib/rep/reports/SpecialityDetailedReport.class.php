<?php
/**
* Отчет по обучаемым - сводные - группы
*/

class CSpecialityDetailedReport extends CReportData {

    function _getTeachers() {
        $ret = array();
        $sql = "SELECT People.LastName, People.FirstName, Teachers.CID
                FROM People
                INNER JOIN Teachers ON (Teachers.MID = People.MID)";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $ret[$row['CID']][] = htmlspecialchars($row['LastName'].' '.$row['FirstName'],ENT_QUOTES);
        }
        return $ret;
    }

    function getReportData() {
        require_once("metadata.lib.php");

        $inputData = $this->getInputData();

        $teachers = $this->_getTeachers();

        $sql = "SELECT tracks2course.level, Courses.credits_student, Courses.Title as CourseTitle,
                    COUNT(tracks2mid.trmid) AS students, Courses.CID
                    FROM tracks2course 
                    INNER JOIN Courses ON (tracks2course.cid=Courses.CID) 
                    LEFT JOIN tracks2mid ON (tracks2course.trid=tracks2mid.trid AND
                    tracks2course.level=tracks2mid.level)
                    WHERE tracks2course.trid='".(int) $inputData['trid']."' 
                    GROUP BY 
                    tracks2course.trid, 
                    tracks2course.level, 
                    tracks2course.cid, 
                    Courses.Title, 
                    Courses.credits_student,
                    Courses.CID ".$this->getSQLOrderString()."
                    ";

        $res = sql($sql);

        if (sqlrows($res)) {

            $courseFilter = new CCourseFilter($GLOBALS['COURSE_FILTERS']);

            while($row = sqlget($res)) {
                if (!$courseFilter->is_filtered($row['CID'])) continue;
                if (is_array($teachers[$row['CID']]) && count($teachers[$row['CID']])) {
                    $row['teacher'] = join('<br>',$teachers[$row['CID']]);
                }

                /* Рекомендуемое время изучения */
                $row['recommend_time'] = floatval(0);
                $meta = read_metadata(stripslashes(getField('Courses','Description','CID',$row['CID'])), COURSES_DESCRIPTION);
                if (is_array($meta)) {
                    foreach($meta as $item) {
                        if($item['name'] == "courseRec") {
                            $row['recommend_time'] = floatval(str_replace(",", ".", $item['value']));
                        }
                    }
                }
                $row['recommend_time'] *= 3600;
                $row['recommend_time'] = sprintf("%d:%d:%d",(int)($row['recommend_time']/3600),($row['recommend_time']/60%60),($row['recommend_time']%60));
                $this->data[] = $row;

            }

        }

        // ОБЯЗАТЕЛЬНО!! ВЫПОЛНИТЬ ФУНКЦИЮ ПРЕДКА
        $this->data = parent::getReportData($this->data);

        return $this->data;

    }


    /**
    * Функция должна возвращать массив:
    */
    function getReportInputField($inputFieldName,$inputFieldData=false) {

        $ret = '';

        switch($inputFieldName) {

            case 'trid':
                $trackFilter = new CTrackFilter($GLOBALS['TRACK_FILTERS']);

                $sql = "SELECT trid,name FROM tracks ORDER BY name";
                $res = sql($sql);
                while($row = sqlget($res)) {
                    if (!$trackFilter->is_filtered($row['trid'])) continue;
                    $ret[$row['trid']] = $row['name'];

                }

                break;

        }


        return $ret;


    }

}


?>