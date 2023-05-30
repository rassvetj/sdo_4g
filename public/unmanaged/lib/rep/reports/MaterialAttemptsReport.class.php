<?php
/**
* Отчет по обучаемым - сводные - группы
*/

class CMaterialAttemptsReport extends CReportData {

    function getReportData() {

            $inputData = $this->getInputData();

            if ($inputData['begin']) {

                $date = explode('.',$inputData['begin']);
                if ((count($date)==3) && checkdate($date[1],$date[0],$date[2])) {
                    $begin = mktime(0, 0, 0, $date[1], $date[0], $date[2]);
                }

            }

            if ($inputData['end']) {

                $date = explode('.',$inputData['end']);
                if ((count($date)==3) && checkdate($date[1],$date[0],$date[2])) {
                    $end = mktime(23, 59, 59, $date[1], $date[0], $date[2]);
                }

            }

            $where = '';
            if (isset($begin)) {
                $where = "AND mod_attempts.start >= ".$GLOBALS['adodb']->DBDate($begin);
            }
            if (isset($end)) {
                $where .= " AND mod_attempts.start <= ".$GLOBALS['adodb']->DBDate($end + 60*60*24); // включительно!
            }

            $people = array();
            $sql = "SELECT
                        People.MID,
                        People.LastName,
                        People.FirstName,
                        People.Patronymic,
                        library.title             AS material,
                        library.bid,
                        COUNT(mod_attempts.start) AS attempts
                    FROM mod_attempts
                    INNER JOIN People ON (People.MID = mod_attempts.mid)
                    INNER JOIN library ON (mod_attempts.modID = library.bid)
                    WHERE 1=1 $where
                    GROUP BY People.MID, library.bid, People.LastName, People.FirstName, People.Patronymic, library.title";
            $res = sql($sql);
            $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);

            while($row = sqlget($res)) {
                if (!$peopleFilter->is_filtered($row['MID'])) continue;
                    $row['fio'] = htmlspecialchars($row['LastName'].' '.$row['FirstName'].' '.$row['Patronymic'], ENT_QUOTES);

                    $this->data[] = $row;
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

        return $ret;
    }

}


?>