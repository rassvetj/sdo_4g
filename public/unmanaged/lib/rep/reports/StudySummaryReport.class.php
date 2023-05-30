<?php
/**
* Отчет по обучаемым - сводные - группы
*/

class CStudySummaryReport extends CReportData {

    function getReportData() {

            if ($group = $this->getInputData(array('gid'))) {
                $grType = (int) $group['gid'][0];
                $grId = substr($group['gid'],2);
            }

            $status = $this->getInputData(array('status'));
            $status = (int) $status['status'];

            $table = "Students";
            if ($status == 1) $table = "graduated";

                $sql = "
                SELECT DISTINCT
                    People.MID AS MID,
                    CONCAT(CONCAT(CONCAT(CONCAT(People.LastName,' '),People.FirstName),' '),People.Patronymic) AS FIO,
                    scheduleID.V_STATUS as v_status,
                    scheduleID.SSID
                FROM
                    $table
                INNER JOIN People ON (People.MID=$table.MID)
                LEFT JOIN scheduleID ON (scheduleID.MID = People.MID)
                INNER JOIN schedule ON (schedule.SHEID=scheduleID.SHEID AND schedule.CID=$table.CID)
                WHERE schedule.vedomost='1'
                ";

            switch($grType) {
                case 1:
                $sql = "SELECT DISTINCT
                            People.MID AS MID,
                            CONCAT(CONCAT(CONCAT(CONCAT(People.LastName,' '),People.FirstName),' '),People.Patronymic) AS FIO,
                            scheduleID.V_STATUS as v_status,
                            scheduleID.SSID
                        FROM
                            Students
                        INNER JOIN People ON (Students.`MID` = People.`MID`)
                        LEFT JOIN scheduleID ON (Students.`MID` = scheduleID.`MID`)
                        INNER JOIN schedule ON (schedule.SHEID=scheduleID.SHEID AND schedule.CID=Students.CID)
                        WHERE Students.cgid='".$grId."' AND schedule.vedomost='1'";

                break;
                case 2:
                $sql = "SELECT DISTINCT
                            People.MID as MID,
                            CONCAT(CONCAT(CONCAT(CONCAT(People.LastName,' '),People.FirstName),' '),People.Patronymic) AS FIO,
                            scheduleID.V_STATUS as v_status,
                            scheduleID.SSID
                        FROM
                            $table
                        INNER JOIN People ON ($table.`MID` = People.`MID`)
                        INNER JOIN groupuser ON (People.MID = groupuser.mid)
                        LEFT JOIN scheduleID ON (People.`MID` = scheduleID.`MID`)
                        INNER JOIN schedule ON (schedule.SHEID=scheduleID.SHEID AND schedule.CID=$table.CID)
                        WHERE groupuser.gid='".$grId."' AND schedule.vedomost='1'
                        ";
                break;

            }

            $res = sql($sql);

            if (sqlrows($res)) {

                $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);

                while($row = sqlget($res)) {

                    if (!$peopleFilter->is_filtered($row['MID'])) continue;

                    $data[$row['MID']]['FIO'] = $row['FIO'];
                    if (($row['v_status'] >= 0) || ($row['v_status'] == -2)) $data[$row['MID']]['completedClasses']++;
                    if ($row['v_status'] == -1) $data[$row['MID']]['uncompletedClasses']++;
                    //if ($row['v_status'] == -2) $data[$row['MID']]['ill']++;
                    if ($row['v_status'] == -3) $data[$row['MID']]['absent']++;
                    $data[$row['MID']]['count']++;

                }

                if (isset($data) && is_array($data) && count($data)) {

                    reset($data);
                    while(list($k,$v) = each($data)) {

                        $v['MID'] = $k;
                        if ($v['count']) $v['procent'] = round(($v['completedClasses'] * 100) / $v['count']);
                        $v['completedClasses'] = (int) $v['completedClasses'];
                        $v['uncompletedClasses'] = (int) $v['uncompletedClasses'];
                        $v['ill'] = (int) $v['ill'];
                        $v['absent'] = (int) $v['absent'];
                        $this->data[] = $v;

                    }

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

            case 'status':
                $ret[0] = _("слушатели");
                $ret[1] = _("прошедшие обучение");
            break;

            case 'gid':

                $ret[0] = _("Все");
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