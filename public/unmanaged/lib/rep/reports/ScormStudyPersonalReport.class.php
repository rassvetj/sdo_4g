<?php
/**
* Отчет по обучаемым - сводные - группы
*/

class CScormStudyPersonalReport extends CReportData {

    function getReportData() {

            $inputData = $this->getInputData();

            $sql = "
                SELECT mod_content.Title, COUNT(scorm_tracklog.trackID) as runs,
                SUM(UNIX_TIMESTAMP(scorm_tracklog.stop)-UNIX_TIMESTAMP(scorm_tracklog.start)) as time
                FROM scorm_tracklog INNER JOIN mod_content ON (scorm_tracklog.McID=mod_content.McID AND
                scorm_tracklog.ModID=mod_content.ModID)
                WHERE scorm_tracklog.mid='".(int) $inputData['MID']."'
                AND scorm_tracklog.cid='".(int) $inputData['CID']."'
                GROUP BY mod_content.Title
            ";

            $res = sql($sql);

            if (sqlrows($res)) {

                while($row = sqlget($res)) {

                    $row['time'] = date('H:i:s',mktime(0,0,$row['time']));

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
    function getReportInputField($inputFieldName,$inputFieldData=false,$where='') {

        $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);

        $ret = '';

        switch($inputFieldName) {

            case 'status':
                $ret[0] = _("слушатели");
                $ret[1] = _("прошедшие обучение");
            break;

            case 'MID':

                $table = "Students";
                if (!isset($inputData['status'])) $inputData['status'] = $_REQUEST['status'];
                if ($inputData['status'] == 1) $table = "graduated";

//                $ret[-1] = _("Все");

                $sql = "SELECT People.MID, People.LastName, People.FirstName, People.Patronymic, People.Login
                        FROM People
                        INNER JOIN $table ON (People.MID=$table.MID)
                        $where
                        ORDER BY LastName, FirstName, Login";
                $res = sql($sql);
                while($row = sqlget($res)) {
                    if (!$peopleFilter->is_filtered($row['MID'])) continue;
                    $v = $row['MID'];
                    $ret[$v] = $row['LastName'].' '.$row['FirstName'].' '.$row['Patronymic'].' ('.$row['Login'].') ';

                }

            break;

            case 'CID':

                $table = "Students";
                if (!isset($inputData['status'])) $inputData['status'] = $_REQUEST['status'];
                if ($inputData['status'] == 1) $table = "graduated";

                if (!$inputFieldData['MID']) {

                    $sql = "SELECT People.MID
                            FROM People
                            INNER JOIN $table ON (People.MID=$table.MID)
                            ORDER BY LastName LIMIT 1";
                    $res = sql($sql);
                    while($row = sqlget($res)) {

                        $inputFieldData['MID'] = $row['MID'];

                    }
                }

                $sql = "SELECT Courses.CID, Courses.Title
                        FROM Courses INNER JOIN $table ON (Courses.CID=$table.CID)
                        WHERE $table.MID='".(int) $inputFieldData['MID']."'
                        ORDER BY Title";
                $res = sql($sql);

                $courseFilter = new CCourseFilter($GLOBALS['COURSE_FILTERS']);

                while($row = sqlget($res)) {

                    if (!$courseFilter->is_filtered($row['CID'])) continue;

                    $ret[$row['CID']] = $row['Title'];

                }

            break;


        }

        return $ret;
    }

    function getSajaxFunctions() {
        return array('process_filter_MID');
    }

}

function process_filter_MID($search, $current=0) {
    $html = '';
/*    if ($current>0) {
        $sql = "SELECT People.MID, People.LastName, People.FirstName, People.Login
                FROM People
                WHERE MID='".(int) $current."'";
        $res = sql($sql);
        if ($row = sqlget($res)) {
            $html .= "<option selected value=\"{$row['MID']}\"> ".htmlspecialchars($row['LastName'].' '.$row['FirstName'].' ('.$row['Login'].') ',ENT_QUOTES)."</option>";
        }
    }
*/
    if (!empty($search)) {
        $search = iconv('UTF-8',$GLOBALS['controller']->lang_controller->lang_current->encoding,unicode_urldecode($search));
        $search = trim($search);
        $search = str_replace('*','%',$search);
        $where = "WHERE (People.LastName LIKE '%".addslashes($search)."%'
        OR People.FirstName LIKE '%".addslashes($search)."%'
        OR People.Login LIKE '%".addslashes($search)."%')";
        $people = CScormStudyPersonalReport::getReportInputField('MID',false,$where);
        if (is_array($people) && count($people)) {
            foreach($people as $mid=>$name) {
                $html .= "<option value=\"$mid\"";
                if ($current == $mid) {
                    $html .= " selected ";
                }
                $html .= "> ".htmlspecialchars($name,ENT_QUOTES)."</option>";
            }
        }
    }
    return $html;
}

?>