<?php
/**
*            -     -    
*/
require_once("Schedule.class.php");

class CLessonsListReport extends CReportData {
    
    function getReportData() {
        
        $inputData = $this->getInputData();
        $cid   = (int)$inputData['course'];        
        $result    = array();
        
        //типы занятий
        $sql = "SELECT * FROM exam_types";
        $res = sql($sql);
        $eventTypes = array();
        while ($row = sqlget($res)) {
            $eventTypes[$row['etid']] = $row['title'];
        }
               
        $sql = "SELECT title, metadata FROM organizations WHERE cid = '$cid'";
        $res = sql($sql);        
        while ($row = sqlget($res)) {
            $result[$row['title']] = array();
            $metadata = read_metadata (stripslashes($row['metadata']), 'item');
            foreach ($metadata as $meta){
                if ($meta['value']) {                    
                    $result[$row['title']][$meta['name']] = $meta['value'];
                    if ($meta['name'] == 'item_type') {
                        $result[$row['title']][$meta['name']] = $eventTypes[$meta['value']];
                    }
                }
            }
        }        
        
        $smarty = new Smarty_els();
        $smarty->assign('ret', $result);        
        
        $result = array();
        $result['lessonsList'] = $smarty->fetch('LessonsListReport.tpl');
        
        

        $this->data[] = $result;           
        
        //       !!              
        $this->data = parent::getReportData($this->data);
        
        return $this->data;
            
    }
    
    
    /**
    * Функция должна возвращать массив:
    */
    function getReportInputField($inputFieldName,$inputFieldData=false) {
        
        $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);
        
        $ret = array();
        
        switch($inputFieldName) {
                        
            case 'course':
                //все опубликованные специальности
                $sql = "SELECT CID, Title FROM Courses WHERE is_poll = 0";
                $res = sql($sql);
                $ret[0] = _('Выберите элемент');
                while($row = sqlget($res)) {
                    $ret[$row['CID']] = $row['Title'];
                }
            break;                
        }        
        
        return $ret;
    }
    
}




?>