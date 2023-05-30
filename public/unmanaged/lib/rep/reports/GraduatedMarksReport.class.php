<?php
/**
* Отчет по обучаемым - сводные - группы
*/

class CGraduatedMarksReport extends CReportData {

    function getReportData() {

        $cid = (int) $this->inputData['CID'];
        $gid = (int) $this->inputData['gid'];

        $fields =
        array(
        'ФИО' => array('field' => 'fio'),
        );

        $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);
        $sql = "        SELECT
                            schedule.SHEID, schedule.Title, scheduleID.V_STATUS, 
                            CONCAT(CONCAT(People.LastName,' '),People.FirstName) AS fio,
                            People.MID
                        FROM scheduleID 
                        INNER JOIN schedule ON (scheduleID.SHEID=schedule.SHEID)
                        INNER JOIN People ON (scheduleID.MID=People.MID)
                        INNER JOIN graduated ON (People.MID=graduated.MID)
                        WHERE schedule.CID='$cid'
                        AND schedule.vedomost=1
                        ORDER BY schedule.Title
                        ";
        if ($gid) {
            $sql = "        SELECT
                                schedule.SHEID, schedule.Title, scheduleID.V_STATUS, 
                                CONCAT(CONCAT(People.LastName,' '),People.FirstName) AS fio,
                                People.MID
                            FROM schedule 
                            INNER JOIN scheduleID ON (schedule.SHEID=scheduleID.SHEID)
                            INNER JOIN People ON (scheduleID.MID=People.MID)
                            INNER JOIN graduated ON (People.MID=graduated.MID)
                            INNER JOIN groupuser ON (People.MID=groupuser.mid)
                            WHERE schedule.CID='$cid'
                            AND groupuser.gid='$gid'
                            AND schedule.vedomost=1
                            ORDER BY schedule.Title
                            ";                    
        }
        $res = sql($sql);
        while($row = sqlget($res)) {
            if (!$peopleFilter->is_filtered($row['MID'])) continue;
            switch($row['V_STATUS']) {

                case -1:
                    $row['V_STATUS'] = '-';
                    break;
                case -2:
                    $row['V_STATUS'] = 'Б';
                    break;
                case -3:
                    $row['V_STATUS'] = 'Н';
                    break;

            }
            $fields[$row['Title']] = array('field' => 'schedule_' . str_replace(' ','_',$row['Title']));

            $data[$row['MID']]['fio'] = $row['fio'];
            $data[$row['MID']]['schedule_' . str_replace(' ','_',$row['Title'])] = $row['V_STATUS'];

        }

        if (is_array($data) && count($data)) {
            foreach($data as $k=>$v) {
                $this->data[] = $v;
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

                $ret[0] = "Все";

                $sql = "SELECT * FROM groupname ORDER BY name";
                $res = sql($sql);
                $groupFilter = new CGroupFilter_Department();
                
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