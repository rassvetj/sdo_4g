<?php
/**
*            -     -
*/

require_once($sitepath.'metadata.lib.php');

class CStudyPlanPersonalReport extends CReportData {

    function getReportData() {

            $sql = "
                SELECT Courses.credits_student, Courses.Title, Courses.CID, Courses.cBegin, Courses.cEnd, Courses.Description
                FROM Students
                INNER JOIN Courses ON (Students.CID = Courses.CID)
                ".$this->getSQLWhereString()." ".$this->getSQLORderString(array('cBegin','cEnd'))."

            ";
            $res = sql($sql);

            if (sqlrows($res)) {

                while($row = sqlget($res)) {

                    $row['teachers'] = str_replace('<br>', ', <br>', substr(strip_tags(get_teachers_list((int) $row['CID']),'<br>'), 0, -4));
                    if (strstr($row['Description'],'~name=control')) {

                        if (($cond = getmetavalue(read_metadata($row['Description']),'control')) && !empty($cond))
                        $row['Condition'] = $cond;

                    } else $row['Condition'] = _('нет');

                    /**
                    * Подготовка данных для вычисления времени обучения
                    */
                    if (!isset($minDate)) $minDate = $row['cBegin'];
                    else if ($row['cBegin']<$minDate) $minDate = $row['cBegin'];
                    if (!isset($maxDate)) $maxDate = $row['cEnd'];
                    else if ($row['cEnd']>$maxDate) $maxDate = $row['cEnd'];


                    $row['cBegin'] = mydate($row['cBegin']);
                    $row['cEnd'] = mydate($row['cEnd']);

                    if (defined('USE_BOLOGNA_SYSTEM') && USE_BOLOGNA_SYSTEM) {
                        $sql2 = "SELECT cid FROM tracks2course WHERE cid='".(int) $row['CID']."'";
                        $res2 = sql($sql2);
                        if (sqlrows($res2)) $row['program'] = _('обязательная');
                        else $row['program'] = _('по выбору');
                    }

                    $this->data[] = $row;

                }

            }

            /**
            * Дополнительная информация - общее время обучения в днях
            */
            $days = (int) ((strtotime($maxDate) - strtotime($minDate)) / (60*60*24));
            $this->additionalData[_('Общее время обучения')] = "$days "._("дней");



            //       !!
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

            case 'MID':

//                $ret[-1] = _("Все");

                $sql = "SELECT People.MID, People.LastName, People.FirstName, People.Patronymic, People.Login
                        FROM People
                        INNER JOIN Students ON (People.MID=Students.MID)
                        {$where}
                        ORDER BY LastName, FirstName, Login";
                $res = sql($sql);
                while($row = sqlget($res)) {
                    if (!$peopleFilter->is_filtered($row['MID'])) continue;
                    $v = $row['MID'];
                    $ret[$v] = $row['LastName'].' '.$row['FirstName'].' '.$row['Patronymic'].' ('.$row['Login'].') ';

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
        $people = CStudyPlanPersonalReport::getReportInputField('MID',false,$where);
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