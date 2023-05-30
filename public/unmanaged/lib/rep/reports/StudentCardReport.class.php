<?php
/**
*            -     -    
*/

require_once($sitepath.'metadata.lib.php');

class CStudentCardReport extends CReportData {
    
    function getReportData() {
        //необходимое условие: 1курс - 1человек - 1экзамен        
        $inputData = $this->getInputData();
        $mid       = (int) $inputData['MID'];
        
        $result    = array();
        
        
        //факультет
        $result['department'] = 'ДУ';
        
       //Специальность
       //ФИО
       //меты
       $sql = "SELECT p.FirstName, p.LastName, p.Patronymic, p.EMail, p.Information, t.name, t.trid, t.id, tm.level
               FROM People p
               LEFT JOIN tracks2mid tm 
                 ON (tm.mid = p.mid)
               LEFT JOIN tracks t 
                 ON (t.trid = tm.trid)
               WHERE p.MID = '$mid'";
       $res = sql($sql);       
       while ($row = sqlget($res)){
           $result['LastName'] = $row['LastName'];         
           $result['FirstName'] = $row['FirstName'];
           $result['Patronymic'] = $row['Patronymic'];
           $result['email'] = $row['EMail'];
           $result['course'] = ceil($row['level']/2);
           $result['track'] .= ($result['track']) ? ', '.$row['id'].' '.$row['name'] : $row['id'].' '.$row['name'];           
           if ($result['trid']){
               $result['trid'][] = $row['trid'];
           }else{
               $result['trid'] = array($row['trid']);
           }            
           $metadataTypes = explode(';',REGISTRATION_FORM);
            if (is_array($metadataTypes) && count($metadataTypes)) {
                foreach($metadataTypes as $metadataType) {
                    $metadata = read_metadata (stripslashes($row['Information']), $metadataType);
                    foreach ($metadata as $meta){
                        if ($meta['value']) $result[$meta['name']] = $meta['value'];    
                    }                    
                }
            }
       }
       
       //результаты вступительных испытаний
       $sql = "SELECT cid FROM tracks2course WHERE trid IN ('".implode("','",$result['trid'])."')";
       $res = sql($sql);
       $cids4track = array();
       while ($row = sqlget($res)){
           $cids4track[$row['cid']] = $row['cid'];
       }
       $sql = "SELECT c.Title, cm.mark, cm.alias           
               FROM Students s
               LEFT JOIN Courses c
                 ON c.CID = s.CID
               LEFT JOIN courses_marks cm
                 ON (cm.cid = s.CID AND cm.mid = s.MID)
               WHERE s.MID = '$mid'
                 AND c.CID NOT IN ('".implode("','",$cids4track)."')";
       $res = sql($sql);       
       $introductory_tests = array();
       while ($row = sqlget($res)) {
           $introductory_tests[$row['Title']]['mark']  = $row['mark'];
           $introductory_tests[$row['Title']]['alias'] = $row['alias'];
       }
       
       $smarty = new Smarty_els();
       $smarty->assign('items', $introductory_tests);
       $result['introductory_tests'] = $smarty->fetch('StudentCardReport_introductoryTests.tpl');

             
       //Выполнение учебного плана
       $sql = "SELECT c.Title, tc.level, cm.mark, cm.alias, tm.started
               FROM tracks2course tc
               LEFT JOIN Courses c
                 ON (c.CID = tc.cid)
               LEFT JOIN courses_marks cm
                 ON (cm.cid = c.CID AND cm.mid = '$mid')
               LEFT JOIN tracks2mid tm
                 ON (tm.trid = tc.trid AND tm.mid = '$mid')
               WHERE tc.trid IN ('".implode("','",$result['trid'])."')
                 ORDER BY tc.level";
       $res = sql($sql);
       $curriculum = array();
       while ($row = sqlget($res)) {           
           $curriculum[$row['level']]['disciplines'][$row['Title']]['mark']      = is_numeric(substr($row['mark'],0,1)) ? $row['mark']." (".$row['alias'].")" : false;
           $curriculum[$row['level']]['disciplines'][$row['Title']]['text_mark'] = !is_numeric(substr($row['mark'],0,1)) ? $row['mark'] : false;
           $curriculum[$row['level']]['disciplines'][$row['Title']]['hours']     = '0'; //TODO
           $curriculum[$row['level']]['disciplines'][$row['Title']]['date']      = '__________'; //TODO
           $curriculum[$row['level']]['disciplines'][$row['Title']]['number']    = '__________'; //TODO
           
           $date = getdate($row['started'] + ($row['level'] - 1) * 15768000); // 15768000 - секунд в семестре ;)
           $curriculum[$row['level']]['course'] = round($row['level']/2);           
           $curriculum[$row['level']]['order']  = "__________";           //TODO
           $curriculum[$row['level']]['number'] = "__________";           //TODO
           $curriculum[$row['level']]['year']   = ($date['mon']>6)?$date['year']." / ".($date['year']+1):($date['year']-1)." / ".$date['year'];
       }
       
       $smarty = new Smarty_els();
       $smarty->assign('items', $curriculum);
       $result['curriculum'] = $smarty->fetch('StudentCardReport_Curriculum.tpl');
       
       //Факультативные дисциплины
       $facultative = array('discipline'=>array('hours'=>0,
                                                'mark'=>0,
                                                'text_mark'=>0,
                                                'date'=>0,
                                                'number'=>0)); //TODO
       $smarty = new Smarty_els();
       $smarty->assign('items', $facultative);
       $result['facultative'] = $smarty->fetch('StudentCardReport_facultative.tpl');
       
       //Практики
       $practice = array('discipline'=>array('weeks'=>0,
                                             'level'=>0,
                                             'text_mark'=>0,
                                             'date'=>0,
                                             'number'=>0)); //TODO
       $smarty = new Smarty_els();
       $smarty->assign('items', $practice);
       $result['practice'] = $smarty->fetch('StudentCardReport_practice.tpl');
       
       //Гос экзамены
       $examination = array('Экзамен 1'=>array('mark'=>0,
                                                'date'=>0,
                                                'number'=>0)); //TODO
       $smarty = new Smarty_els();
       $smarty->assign('items', $examination);
       $result['examination'] = $smarty->fetch('StudentCardReport_examination.tpl');
       

            $this->data[] = $result;
            
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
               
                $sql = "SELECT People.MID, People.LastName, People.FirstName, People.Patronymic, People.Login
                        FROM People 
                        INNER JOIN Students ON (People.MID=Students.MID)
                        {$where}
                        ORDER BY LastName, FirstName, Login";
                $res = sql($sql);
                while($row = sqlget($res)) {
                    if (!$peopleFilter->is_filtered($row['MID'])) continue;
                    $v = $row['MID'];
                    $ret[$v] = $row['LastName'].' '.$row['FirstName'].(strlen($row['Patronymic']) ? ' '.$row['Patronymic'] : '').' ('.$row['Login'].') ';
                                                            
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
        $search = iconv('UTF-8','Windows-1251',unicode_urldecode($search));
        $search = trim($search);    
        $search = str_replace('*','%',$search);
        $where = "WHERE (People.LastName LIKE '%".addslashes($search)."%'
        OR People.FirstName LIKE '%".addslashes($search)."%'
        OR People.Login LIKE '%".addslashes($search)."%')";
        $people = CStudentCardReport::getReportInputField('MID',false,$where);
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