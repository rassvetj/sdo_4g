<?php
/**
* Отчет по обучаемым - сводные - группы
*/

class CTestCourseResultsPersonalReport extends CReportData {

    function getReportData() {

            $inputData = $this->getInputData();

            $sql = "SELECT toolParams
                    FROM scheduleID
                    WHERE MID='".(int) $inputData['MID']."'";

            $res = sql($sql);

            while ($row = sqlget($res)) {
                if (preg_match("!tests_testID=([0-9]+)!",$row['toolParams'],$matches)) $tid = $matches[1];
                if (preg_match("!tests_testID = ([0-9]+)!",$row['toolParams'],$matches)) $tid = $matches[1];

                if ($tid > 0) {
                    $tids[$tid] = $tid;
                }
            }
            $sql = "SELECT t1.*, test.title
                    FROM loguser t1
                    LEFT JOIN test ON (test.tid = t1.tid) 
                    LEFT JOIN loguser t2                    
                      ON (t1.stop < t2.stop 
                        AND t1.mid = t2.mid 
                        AND t1.tid = t2.tid
                        )
                    WHERE t1.tid IN('".implode("','",$tids)."') 
                      AND t1.mid = '".(int) $inputData['MID']."'
                      AND t2.stop is NULL";

            $res = sql($sql);

            while($row = sqlget($res)) {

                $row['stop'] = date('d.m.Y',$row['stop']);
                $row['test'] = $row['title'];

                if ($row['balmax']) {
                    $row['procent'] = round(($row['bal']*100) / ($row['balmax']-$row['balmin']));
                }else {
                    $row['procent'] = 0;
                }
                $row['procent'] = $row['procent'] ? $row['procent'] : '0 ';

                $this->data[] = $row;

            }

            // ОБЯЗАТЕЛЬНО!! ВЫПОЛНИТЬ ФУНКЦИЮ ПРЕДКА
            $this->data = parent::getReportData($this->data);

            return $this->data;

    }


    /**
    * Функция должна возвращать массив:
    */
    function getReportInputField($inputFieldName,$inputData=false,$where='') {

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
            
            case 'СID':
                
                $table = "Students";
                if (!isset($inputData['status'])) $inputData['status'] = $_REQUEST['status'];
                if ($inputData['status'] == 1) $table = "graduated";

                if (!isset($inputData['MID']) || !$inputData['MID']) {

                    $sql = "SELECT * FROM People
                            INNER JOIN $table ON (People.MID=$table.MID)
                            ORDER BY LastName";
                    $res = sql($sql);
                    if (sqlrows($res)) {

                        $row = sqlget($res);
                        $mid = $row['MID'];

                    }
                    sqlfree($res);

                } else $mid = $inputData['MID'];
                
                $sql = "SELECT 
                            $table.CID,
                            Courses.Title
                        FROM $table 
                        LEFT JOIN Courses ON (Courses.CID = $table.CID)                        
                        WHERE $table.CID>0 
                          AND $table.MID = '$mid'";
                $res = sql($sql);
                while ($row = sqlget($res)) {
                	$ret[$row['CID']] = $row['Title'];
                }
                break;

            case 'SHEID':

                $table = "Students";
                if (!isset($inputData['status'])) $inputData['status'] = $_REQUEST['status'];
                if ($inputData['status'] == 1) $table = "graduated";

                if (!isset($inputData['MID']) || !$inputData['MID']) {

                    $sql = "SELECT * FROM People
                            INNER JOIN $table ON (People.MID=$table.MID)
                            ORDER BY LastName";
                    $res = sql($sql);
                    if (sqlrows($res)) {

                        $row = sqlget($res);
                        $mid = $row['MID'];

                    }
                    sqlfree($res);

                } else $mid = $inputData['MID'];

                $sql = "SELECT scheduleID.SHEID, schedule.title FROM scheduleID
                        INNER JOIN schedule ON (scheduleID.SHEID=schedule.SHEID)
                        WHERE MID='".(int) $mid."' 
                            AND schedule.typeID=2
                            AND schedule.CID = '".$inputData['CID']."'
                        ORDER BY SSID";

                $res = sql($sql);
                while($row = sqlget($res)) {

                        $ret[$row['SHEID']] = $row['title'];

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
        // AND People.MID NOT IN ('".(int) $current."')
        $people = CTestCourseResultsPersonalReport::getReportInputField('MID',false,$where);
        if (is_array($people) && count($people)) {
            foreach($people as $mid=>$name) {
                $html .= "<option value=\"$mid\"";
                if ($current == $mid) $html .= " selected ";
                $html .= "> ".htmlspecialchars($name,ENT_QUOTES)."</option>";
            }
        }
    }
    return $html;
}



?>