<?php
/**
* Отчет по обучаемым - сводные - группы
*/

class CCourseStudySummaryReport extends CReportData {

    function getReportData() {

            $cid = (int) $this->inputData['CID'];
            $gid = (int) $this->inputData['gid'];
            $status = (int) $this->inputData['status'];
            $table = 'Students';
            if ($status == 1) $table = 'graduated';

            $fields =
            array(
                _("ФИО") => array('field' => 'fio'),
            );

/*            $events = array();
            $sql = "
            SELECT DISTINCT event
            FROM eventtools_weight
            WHERE cid='$cid' AND weight>0
            ";

            $res = sql($sql);
            while($row = sqlget($res)) {
            	$events[] = $row['event'];
            }
*/
            $marks = array();
            $sql = "
            SELECT mark, mid FROM courses_marks
            WHERE cid='$cid'
            ";
            $res = sql($sql);
            while($row = sqlget($res)) {
            	$marks[$row['mid']] = $row['mark'];
            }

//            if (is_array($events) && count($events)) {
                $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);
                $sql = "SELECT
                            schedule.SHEID, schedule.Title, scheduleID.V_STATUS,
                            CONCAT(CONCAT(CONCAT(CONCAT(People.LastName,' '),People.FirstName),' '),People.Patronymic) AS fio,
                            People.MID
                        FROM scheduleID
                        INNER JOIN schedule ON (scheduleID.SHEID=schedule.SHEID)
                        INNER JOIN People ON (scheduleID.MID=People.MID)
                        INNER JOIN $table ON (People.MID=$table.MID)
                        WHERE schedule.CID='$cid'
                        AND schedule.vedomost=1
                        ORDER BY schedule.Title
                        ";
                //        AND schedule.vedomost=1 AND schedule.typeID IN ('".join("','",$events)."')
                if ($gid) {
                    $sql = "SELECT
                                schedule.SHEID, schedule.Title, scheduleID.V_STATUS,
                                CONCAT(CONCAT(CONCAT(CONCAT(People.LastName,' '),People.FirstName),' '),People.Patronymic) AS fio,
                                People.MID
                            FROM schedule
                            INNER JOIN scheduleID ON (schedule.SHEID=scheduleID.SHEID)
                            INNER JOIN People ON (scheduleID.MID=People.MID)
                            INNER JOIN $table ON (People.MID=$table.MID)
                            INNER JOIN groupuser ON (People.MID=groupuser.mid)
                            WHERE schedule.CID='$cid'
                            AND groupuser.gid='$gid'
                            AND schedule.vedomost=1
                            ORDER BY schedule.Title
                            ";
                //            AND schedule.vedomost=1 AND schedule.typeID IN ('".join("','",$events)."')
                }
                $res = sql($sql);
                while($row = sqlget($res)) {
                    if (!$peopleFilter->is_filtered($row['MID'])) continue;
                    switch($row['V_STATUS']) {

                        case -1:
                            $row['V_STATUS'] = '-';
                        break;
                        case -2:
                            $row['V_STATUS'] = _("Б");
                        break;
                        case -3:
                            $row['V_STATUS'] = _("Н");
                        break;

                    }

                    $fields[$row['Title']] = array('field' => str_replace(' ','_',$row['Title']));

                    $data[$row['MID']]['fio'] = $row['fio'];
                    $data[$row['MID']]['schedules'][str_replace(' ','_',$row['Title'])] = $row['V_STATUS'];

                }
  //          }

            if (is_array($data) && count($data)) {
                foreach($data as $k=>$v) {

                    $v['schedules']['fio'] = $v['fio'];
                    if (!isset($marks[$k])) $marks[$k] = '-';
                    $v['schedules']['summary'] = $marks[$k];
                    $this->data[] = $v['schedules'];

                }
            }

            $fields[_("Итоговая оценка")] = array('field'=>'summary');

            // Нестандартная таблица
            $this->parseFields($fields);
            $this->parseFilterData($this->filterDataBackup);
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

            case 'status':
                $ret[0] = _("слушатели");
                $ret[1] = _("прошедшие обучение");
            break;

            case 'CID':

                $courseFilter = new CCourseFilter($GLOBALS['COURSE_FILTERS']);

                $sql = "SELECT CID, Title FROM Courses ORDER BY Title";
                $res = sql($sql);

                while($row = sqlget($res)) {

                    if (!$courseFilter->is_filtered($row['CID'])) continue;

                    $ret[$row['CID']] = $row['Title'];

                }

            break;

            case 'gid':

                $ret[0] = _("Все");
                $groupFilter = new CGroupFilter_Department();
                
                $sql = "SELECT * FROM groupname ORDER BY name";
                $res = sql($sql);

                //if (sqlrows($res) && isset($row)) $ret['-1'] = "---";

                while($row = sqlget($res)) {
                    if (!$groupFilter->is_filtered($row['gid'])) {
                        continue;
                    }
                    $ret[$row['gid']] = $row['name'];
                }

            break;


        }

        return $ret;
    }

}


?>