<?php
/**
* Отчет по обучаемым - сводные - группы
*/

class CAnswersStatsPersonalReport extends CReportData {

    function getReportData() {

            $inputData = $this->getInputData();

            $sql = "SELECT logseance.balmax, logseance.balmin, logseance.bal, logseance.otvet, list.qtype,
                    list.qdata FROM logseance INNER JOIN list ON (logseance.kod=list.kod)
                    WHERE stid='".(int) $inputData['stid']."' AND mid='".(int) $inputData['MID']."'";
            $res = sql($sql);

            while($row = sqlget($res)) {

                $row['bals'] = $row['balmin'].' - '.$row['balmax'];
                if ($row['balmax'])
                $row['procent'] = round(($row['bal']*100) / ($row['balmax']-$row['balmin']));
                else $row['procent'] = 0;

                $qdata = explode('~~',$row['qdata']);
                $row['q'] = $qdata[0];

                $otvet = unserialize($row['otvet']);

                if (is_array($otvet['main']))
                foreach($otvet['main'] as $k=>$v) {

                    $row['answ'] .= $v.' <br />';

                }

                switch($row['qtype']) {

                    case 1:
                    $row['qtype'] = _("один верный вариант");
                    break;
                    case 2:
                    $row['qtype'] = _("несколько верных вариантов");
                    break;
                    case 3:
                    $row['qtype'] = _("на соответствие");
                    break;
                    case 4:
                    $row['qtype'] = _("с прикрепленным файлом");
                    break;
                    case 5:
                    $row['qtype'] = _("заполнение формы");
                    break;
                    case 6:
                    $row['qtype'] = _("свободный ответ");
                    break;
                    case 7:
                    $row['qtype'] = _("выбор по карте на картинке");
                    break;
                    case 8:
                    $row['qtype'] = _("выбор из набора картинок");
                    break;
                    case 9:
                    $row['qtype'] = _("внешний объект");
                    break;
                    case 10:
                    $row['qtype'] = _("тренажер");
                    break;
                    default:
                    $row['qtype'] = _("неизвестно");
                }

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

                    $v = $row['MID'];
                    if ($peopleFilter->is_filtered($row['MID']))
                    $ret[$v] = $row['LastName'].' '.$row['FirstName'].' '.$row['Patronymic'].' ('.$row['Login'].') ';

                }

            break;

            case 'SHEID':

                if (!isset($inputData['MID']) || !$inputData['MID']) {

                    $table = "Students";
                    if (!isset($inputData['status'])) $inputData['status'] = $_REQUEST['status'];
                    if ($inputData['status'] == 1) $table = "graduated";

                    $sql = "SELECT * FROM People
                            INNER JOIN $table ON (People.MID=$table.MID)
                            ORDER BY LastName";
                    $res = sql($sql);
                    if (sqlrows($res)) {

                        while($row = sqlget($res)) {
                            if ($peopleFilter->is_filtered($row['MID'])) break;
                        }
                        $mid = $row['MID'];

                    }
                    sqlfree($res);

                } else $mid = $inputData['MID'];

                $sql = "SELECT scheduleID.SHEID, schedule.title FROM scheduleID
                        INNER JOIN schedule ON (scheduleID.SHEID=schedule.SHEID)
                        WHERE MID='".(int) $mid."' AND
                        typeID=2
                        ORDER BY SSID";

                $res = sql($sql);
                while($row = sqlget($res)) {

                        $ret[$row['SHEID']] = $row['title'];

                }

            break;

            case 'stid':

                $table = "Students";
                if (!isset($inputData['status'])) $inputData['status'] = $_REQUEST['status'];
                if ($inputData['status'] == 1) $table = "graduated";

                if (!isset($inputData['MID']) || !$inputData['MID']) {

                    $sql = "SELECT * FROM People
                            INNER JOIN $table ON (People.MID=$table.MID)
                            ORDER BY LastName LIMIT 1";
                    $res = sql($sql);
                    if (sqlrows($res)) {

                        while($row = sqlget($res)) {
                            if ($peopleFilter->is_filtered($row['MID'])) break;
                        }
                        $inputData['MID'] = $row['MID'];

                    }
                    sqlfree($res);

                } else $mid = $inputData['MID'];


                if (!isset($inputData['SHEID']) || !$inputData['SHEID']) {

                    $sql = "SELECT scheduleID.SHEID, schedule.title FROM scheduleID
                            INNER JOIN schedule ON (scheduleID.SHEID=schedule.SHEID)
                            WHERE MID='".(int) $mid."' AND
                            typeID=2
                            ORDER BY SSID LIMIT 1";

                    $res = sql($sql);

                    if (sqlrows($res)) {

                        $row = sqlget($res);
                        $inputData['SHEID'] = $row['SHEID'];

                    }
                    sqlfree($res);

                } else $sheid = $inputData['SHEID'];

            $sql = "SELECT toolParams
                    FROM scheduleID
                    WHERE SHEID='".(int) $inputData['SHEID']."' AND MID='".(int) $inputData['MID']."'";

            $res = sql($sql);

            if (sqlrows($res)) {

                $row = sqlget($res);

                sqlfree($res);

                if (preg_match("!tests_testID=([0-9]+)!",$row['toolParams'],$matches)) $tid = $matches[1];
                if (preg_match("!tests_testID = ([0-9]+)!",$row['toolParams'],$matches)) $tid = $matches[1];

                if ($tid>0) {

                    $sql = "SELECT stid,stop FROM loguser WHERE mid='".(int) $inputData['MID']."' AND tid='".(int) $tid."' ORDER BY stop";

                    $res = sql($sql);

                    $i=1;
                    while($row = sqlget($res)) {

                        $ret[$row['stid']] = $i++.': '.date('d.m.Y',$row['stop']);

                    }
                }

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
        $people = CAnswersStatsPersonalReport::getReportInputField('MID',false,$where);
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