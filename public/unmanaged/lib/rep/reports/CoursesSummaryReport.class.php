<?php
/**
* Отчет по обучаемым - сводные - группы
*/

require_once($sitepath.'metadata.lib.php');

class CCoursesSummaryReport extends CReportData {
    
    function getReportData() {
    
            $sql = "SELECT * FROM Courses";            

            $res = sql($sql);
            
            if (sqlrows($res)) {
                
                $courseFilter = new CCourseFilter($GLOBALS['COURSE_FILTERS']);
             
                while($row = sqlget($res)) {
                    
                    if (!$courseFilter->is_filtered($row['CID'])) continue;
                    
                    $row['RegType'] = get_course_type($row['TypeDes'],$row['chain']);
                    $row['cBegin'] = date('d.m.Y',strtotime($row['cBegin']));
                    $row['cEnd'] = date('d.m.Y',strtotime($row['cEnd']));
                    $row['teachers'] = strip_tags(get_teachers_list($row['CID']),'<br>');

                    if (strstr($row['Description'],'~name=control')) {
                        if (($cond = getmetavalue(read_metadata($row['Description']),'control')) && !empty($cond))
                        $row['control'] = $cond;
                    } else $row['control'] = _('нет');
                    
                    $row['Status'] = get_course_status($row['Status']);
                    
                    $row['students'] = get_stud_list($row['CID']);
                    
                    $this->data[] = $row;
                
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

    }
    
}


?>