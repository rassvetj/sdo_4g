<?php
/**
* Отчет по обучаемым - сводные - группы
*/

class CSheduleMarksSummaryReport extends CReportData {

    function getReportData() {

            $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);

            $inputData = $this->getInputData();

            $status = (int) $inputData['status'];

            $sql = "SELECT `int`, `char` FROM alt_mark";
            $res = sql($sql);

            $altMarks = array();
            while($row = sqlget($res)) {
                $altMarks[$row['int']] = $row['char'];
            }

            if ($group = $this->getInputData(array('gid'))) {
                $grType = (int) $group['gid'][0];
                $grId = substr($group['gid'],2);
            }


            if ($inputData['begin']) {

                $date = explode('.',$inputData['begin']);
                if ((count($date)==3) && checkdate($date[1],$date[0],$date[2])) $begin = $date[2].'-'.$date[1].'-'.$date[0].' 00:00:00';

            }

            if ($inputData['end']) {

                $date = explode('.',$inputData['end']);
                if ((count($date)==3) && checkdate($date[1],$date[0],$date[2])) $end = $date[2].'-'.$date[1].'-'.$date[0].' 23:59:59';

            }

            if (isset($begin)) $where = " AND " . $GLOBALS['adodb']->SQLDate("Y-m-d H:i:s", "schedule.begin") . " >= '$begin' ";
            if (isset($end)) {
                $where .= " AND " . $GLOBALS['adodb']->SQLDate("Y-m-d H:i:s", "schedule.end") . " <= '$end' ";
            }

            $this->additionalData[_("ФИО преподавателя")] = substr(strip_tags(str_replace(array('&nbsp;','<br>'),array(' ',', '),get_teachers_list((int) $inputData['CID'])),'<br>'),0,-2);


            // Для всех студентов
            if (!$grType) {

                if ($status == 1) {
                    $sql = "SELECT
                                schedule.SHEID, schedule.Title, scheduleID.V_STATUS,
                                CONCAT(CONCAT(CONCAT(CONCAT(People.LastName,' '),People.FirstName), ' '),People.Patronymic) AS FIO,
                                People.MID
                            FROM scheduleID
                            INNER JOIN schedule ON (scheduleID.SHEID=schedule.SHEID)
                            INNER JOIN People ON (scheduleID.MID=People.MID)
                            INNER JOIN graduated ON (People.MID=graduated.MID)
                            WHERE schedule.CID='".(int) $inputData['CID']."'
                            $where
                            ORDER BY scheduleID.SHEID
                            ";
                } else {
                    $sql = "SELECT
                                schedule.SHEID, schedule.Title, scheduleID.V_STATUS,
                                CONCAT(CONCAT(CONCAT(CONCAT(People.LastName,' '),People.FirstName), ' '),People.Patronymic) AS FIO,
                                People.MID
                            FROM scheduleID
                            INNER JOIN schedule ON (scheduleID.SHEID=schedule.SHEID)
                            INNER JOIN People ON (scheduleID.MID=People.MID)
                            INNER JOIN Students ON (People.MID=Students.MID)
                            WHERE schedule.CID='".(int) $inputData['CID']."'
                            $where
                            ORDER BY scheduleID.SHEID
                            ";
                }


            } else {
            // Для студентов только из групп

            switch($grType) {
                case 1:
                $sql = "SELECT
                            schedule.SHEID, schedule.Title, scheduleID.V_STATUS,
                            CONCAT(CONCAT(CONCAT(CONCAT(People.LastName,' '),People.FirstName),' '),People.Patronymic) AS FIO,
                            People.MID
                        FROM schedule
                        INNER JOIN scheduleID ON (schedule.SHEID=scheduleID.SHEID)
                        INNER JOIN People ON (scheduleID.MID=People.MID)
                        INNER JOIN Students ON (People.MID=Students.MID)
                        WHERE schedule.CID='".(int) $inputData['CID']."'
                        AND Students.cgid='".(int) $grId."'
                        $where
                        ORDER BY scheduleID.SHEID
                        ";

                break;
                case 2:
                if ($status == 1) {
                    $sql = "SELECT
                                schedule.SHEID, schedule.Title, scheduleID.V_STATUS,
                                CONCAT(CONCAT(CONCAT(CONCAT(People.LastName,' '),People.FirstName),' '),People.Patronymic) AS FIO,
                                People.MID
                            FROM schedule
                            INNER JOIN scheduleID ON (schedule.SHEID=scheduleID.SHEID)
                            INNER JOIN People ON (scheduleID.MID=People.MID)
                            INNER JOIN graduated ON (People.MID=graduated.MID)
                            INNER JOIN groupuser ON (People.MID=groupuser.mid)
                            WHERE schedule.CID='".(int) $inputData['CID']."'
                            AND groupuser.gid='".(int) $grId."'
                            $where
                            ORDER BY scheduleID.SHEID
                            ";
                } else {
                    $sql = "SELECT
                                schedule.SHEID, schedule.Title, scheduleID.V_STATUS,
                                CONCAT(CONCAT(CONCAT(CONCAT(People.LastName,' '),People.FirstName),' '),People.Patronymic) AS FIO,
                                People.MID
                            FROM schedule
                            INNER JOIN scheduleID ON (schedule.SHEID=scheduleID.SHEID)
                            INNER JOIN People ON (scheduleID.MID=People.MID)
                            INNER JOIN Students ON (People.MID=Students.MID)
                            INNER JOIN groupuser ON (People.MID=groupuser.mid)
                            WHERE schedule.CID='".(int) $inputData['CID']."'
                            AND groupuser.gid='".(int) $grId."'
                            $where
                            ORDER BY scheduleID.SHEID
                            ";
                }

            }

            }

            $res = sql($sql);

            if (sqlrows($res)) {

                $fields[_("ФИО")] = array('field' => 'FIO');
                while($row = sqlget($res)) {
                    if (!$peopleFilter->is_filtered($row['MID'])) continue;

                    if ($row['V_STATUS'] < -1) $row['V_STATUS'] = $altMarks[$row['V_STATUS']];
                    switch($row['V_STATUS']) {

                        case -1:
                            $row['V_STATUS'] = '.';
                        break;
/*                        case -2:
                            $row['V_STATUS'] = _("Б");
                        break;
                        case -3:
                            $row['V_STATUS'] = _("Н");
                        break;
*/
                    }
                    $data[$row['FIO']][str_replace(' ','_',$row['Title'])] = $row['V_STATUS'];

                    $fields[$row['Title']] = array('field' => str_replace(' ','_',$row['Title']));
                }

                /**
                * Изменяем глобальные вещи тк это изврат-отчет динамическая двумерная таблица
                */
                if (is_array($data) && count($data)) {


                    foreach($data as $k=>$v) {

                        $v['FIO'] = $k;
                        $this->data[] = $v;

                    }

                }

            }

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

                $sql = "SELECT CID, Title FROM Courses ORDER BY Title";
                $res = sql($sql);

                $courseFilter = new CCourseFilter($GLOBALS['COURSE_FILTERS']);

                while($row = sqlget($res)) {
                    if (!$courseFilter->is_filtered($row['CID'])) continue;

                    $ret[$row['CID']] = $row['Title'];

                }

            break;

            case 'gid':

                $ret[-1] = _("Не имеет значения");
                $groupFilter = new CGroupFilter_Department();
                
                $sql = "SELECT * FROM cgname ORDER BY cgid";
                $res = sql($sql);
                while($row = sqlget($res)) {
                    if (!$groupFilter->is_filtered($row['cgid'])) {
                        continue;
                    }
                    $v = '1_'.$row['cgid'];
                    $ret[$v] = $row['name'];

                }

                $sql = "SELECT * FROM groupname ORDER BY name";
                $res = sql($sql);

                //if (sqlrows($res) && isset($row)) $ret['-1'] = "---";

                while($row = sqlget($res)) {
                    if (!$groupFilter->is_filtered($row['gid'])) {
                        continue;
                    }
                    $v = '2_'.$row['gid'];
                    $ret[$v] = $row['name'];

                }

            break;


        }

        return $ret;

    }

}


?>