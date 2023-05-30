<?php
/**
* Отчет по обучаемым - сводные - группы
*/

require_once($sitepath.'metadata.lib.php');

class CRegInfoPersonalReport extends CReportData {

    function getReportData() {

            $fields =
            array(
                _("ФИО") => array('field' => 'FIO'),
                _("Логин") => array('field' => 'Login'),
                'E-mail' => array('field' => 'EMail'),
            );

            $add_info = explode(';',REGISTRATION_FORM);

            foreach($add_info as $v) {
                $fields[get_reg_block_title($v)] = array('field' => $v);
            }

            $sql = "SELECT * FROM People ".$this->getSQLWhereString();

            $res = sql($sql);

            $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);

            if (sqlrows($res)) {

                while($row = sqlget($res)) {

                	if (!$peopleFilter->is_filtered($row['MID'])) continue;

                    foreach($add_info as $info) {
                        foreach (read_metadata($row['Information'],$info) as $val) {                            
                            $row[$info] .= ($val['title'] && strlen($row[$info])>0) ? '<br />' : '';
                            $row[$info] .= $val['title'] ? str_replace(' ', '&nbsp;', $val['title'].':') : '';
                            $row[$info] .= '&nbsp;'.$val['value'];
                        }                        
                    }

                    $row['FIO'] = $row['LastName'].' '.$row['FirstName'].' '.$row['Patronymic'];

                    $this->data[] = $row;

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
    function getReportInputField($inputFieldName,$inputFieldData=false,$where='') {

        $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);

        $ret = '';

        switch($inputFieldName) {

            case 'MID':

                $sql = "SELECT People.MID, People.LastName, People.FirstName, People.Patronymic, People.Login FROM People {$where} ORDER BY LastName";
                $res = sql($sql);
                while($row = sqlget($res)) {
                    if (!$peopleFilter->is_filtered($row['MID'])) continue;

                    $v = $row['MID'];
                    $ret[$v] = $row['LastName'].' '.$row['FirstName'].' '.$row['Patronymic'].' ('.$row['Login'].')';

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
    $html .= "<option value='-1'> "._("Все")."</option>";
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
        $people = CRegInfoPersonalReport::getReportInputField('MID',false,$where);
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