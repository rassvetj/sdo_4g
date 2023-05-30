<?php
/**
* Отчет по обучаемым - сводные - группы
*/

class CTeachersLoadingReport extends CReportData {
    
    function getReportData() {        
                
            $inputData = $this->getInputData();

            if ($inputData['begin']) {

                $date = explode('.',$inputData['begin']);
                if ((count($date)==3) && checkdate($date[1],$date[0],$date[2])) $begin = $date[2].'-'.$date[1].'-'.$date[0].' 00:00:00';
                                
            }

            if ($inputData['end']) {

                $date = explode('.',$inputData['end']);
                if ((count($date)==3) && checkdate($date[1],$date[0],$date[2])) $end = $date[2].'-'.$date[1].'-'.$date[0].' 23:59:59';
                                
            }

            if (isset($begin)) $where = "schedule.begin >= ".$GLOBALS['adodb']->DBTimestamp(strtotime($begin));
            if (isset($end)) {
                if (isset($begin)) $where .= ' AND ';
                $where .= "schedule.end <= ".$GLOBALS['adodb']->DBTimestamp(strtotime($end));
            }
            
            if ($where) $where = 'WHERE '.$where;
                
            /**
            * Массив типов занятий
            */
            $sql = "SELECT TypeID, TypeName FROM EventTools ORDER BY TypeName";
            $res = sql($sql);
            
            $fields[_("ФИО")] = array('field' => 'FIO');
            while ($row = sqlget($res)) {
                
                $fields[$row['TypeName']] = array('field' => str_replace(' ','_',$row['TypeName']), 'type' => 'integer');
                $types[$row['TypeID']] = $row['TypeName'];
                
            }
            $fields[_("Суммарная нагрузка")] = array('field' => 'sum');
            
            $sql = "SELECT DISTINCT 
                        schedule.SHEID,
                        schedule.typeID,
                        CONCAT(CONCAT(CONCAT(CONCAT(People.LastName,' '),People.FirstName),' '), People.Patronymic) as FIO,  
                        People.MID,
                        periods.count_hours                        
                    FROM People INNER JOIN Teachers ON (People.MID=Teachers.MID)
                    LEFT JOIN schedule ON (People.MID=schedule.teacher)
                    LEFT JOIN periods ON (schedule.period = periods.lid)
                    $where";
                    
            $res = sql($sql);
            
            $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);
            
            while($row = sqlget($res)) {
                
                if (!$peopleFilter->is_filtered($row['MID'])) continue;
                
                $data[$row['MID']]['FIO'] = $row['FIO'];
                $data[$row['MID']][str_replace(' ','_',$types[$row['typeID']])] += $row['count_hours'];
                $data[$row['MID']]['sum'] += $row['count_hours'];
                
            }
            
            if (isset($data) && is_array($data) && count($data)) {
                
                foreach($data as $k=>$v) {
                    
                    $this->data[] = $v;
                    
                }
                
            }
                        
            // Нестандартная таблица
            $this->parseFields($fields);
            $this->parseFilterData($this->filterDataBackup);
            //pr($fields);
            //pr($this->filterDataBackup);
            // ОБЯЗАТЕЛЬНО!! ВЫПОЛНИТЬ ФУНКЦИЮ ПРЕДКА
            $this->data = parent::getReportData($this->data);
            
            return $this->data;
            
    }
    
    
    /**
    * Функция должна возвращать массив:
    */
    function getReportInputField($inputFieldName,$inputFieldData=false) {
        

    }
    
}


?>