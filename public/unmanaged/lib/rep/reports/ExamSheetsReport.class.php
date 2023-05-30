<?php
/**
*            -     -    
*/

require_once($sitepath.'metadata.lib.php');

class CExamSheetsReport extends CReportData {
    
    function getReportData() {
        //необходимое условие: 1курс - 1человек - 1экзамен        
        $inputData = $this->getInputData();
        $course    = (int) $inputData['course'];
        $mid       = (int) $inputData['MID'];
        $result    = array();
        
        //группа
        //дисциплина
        //ФИО
        $sql = "SELECT gn.name, c.Title, p.LastName, p.FirstName, p.Patronymic
                FROM groupuser gu
                LEFT JOIN groupname gn ON (gu.gid = gn.gid)
                LEFT JOIN Students s ON (s.MID = gu.mid)
                LEFT JOIN Courses c ON (s.CID = c.CID)
                LEFT JOIN People p ON (p.mid = gu.mid)
                WHERE gu.mid = '$mid'
                  AND s.CID = '$course'";
        $res = sql($sql);
        while ($row = sqlget($res)){
            $result['group']      = $row['name'];
            $result['discipline'] = $row['Title'];
            $result['fio']        = $row['LastName'].' '.substr($row['FirstName'],0,1).'. '.substr($row['Patronymic'],0,1).'.';
        }
        
        
        $test = $sheid = '';
        $sql = "SELECT s2.toolParams, s1.SHEID
                FROM schedule s1
                LEFT JOIN scheduleID s2 ON (s1.SHEID = s2.SHEID)
                WHERE s1.typeID = 4
                  AND s1.CID = '$course'
                  AND s2.MID = '$mid'";
        $res = sql($sql);
        $row = sqlget($res);
        if (preg_match("/tests_testID=(.*?);/",$row['toolParams'],$match)){
            $test = $match[1];
        }
        $sheid = $row['SHEID'];
        
        //рецензия
        //оценка
        //дата здачи
        //экзаменатор
        $sql = "SELECT s.time, s.bal, s.comments, p.FirstName, p.LastName, p.Patronymic
                FROM seance s
                LEFT JOIN loguser l ON (l.stid = s.stid)
                lEFT JOIN People p ON (l.moderby = p.MID)
                WHERE s.mid   = '$mid'
                  AND s.cid   = '$course'
                  AND s.tid   = '$test'
                  AND l.sheid = '$sheid'";
        $res = sql($sql);
        $row = sqlget($res);
        if (is_array($row) && count($row)){
            $row['examiner'] = $row['FirstName'].' '.$row['Patronymic'].' '.$row['LastName'];
            unset($row['FirstName']);
            unset($row['Patronymic']);
            unset($row['LastName']);
            
            foreach ($row as $k=>$v){
                if ($k=='time'){
                    $year    = substr($v,0,4);
                    $month   = substr($v,4,2);
                    $day     = substr($v,6,2);
                    $hours   = substr($v,8,2);
                    $minutes = substr($v,10,2);
                    $seconds = substr($v,11,2);
                    $v = "$day.$month.$year";
                }
                $result[$k] = $v;
            }
        }
        //семестр
        //учебный год
        //курс
        //общее количество часов
        $sql = "SELECT tm.started, tm.level, t.volume
                FROM tracks2mid tm
                LEFT JOIN tracks2course tc ON (tm.trid = tc.trid)
                LEFT JOIN tracks t ON (t.trid = tm.trid)
                WHERE tm.mid = '$mid'
                  AND tc.cid = '$course'";
        $res = sql($sql);
        $row = sqlget($res);
        
        $date = getdate($row['started']);
        $result['course'] = (int)($row['level']/2+1);
        $result['year']   = ($date['mon']>6)?$date['year']." / ".++$date['year']:--$date['year']." / ".$date['year'];
        $result['term']   = ($row['level']%2)?_('второй'):_('первый');
        $result['hours']  = $row['volume'];

                
        //форма контроля
        //$result['checkup'] = "Экзамен";
        //фаультет
        $result['department'] = "ДО";
        /*
            $sql = "
                SELECT Courses.credits_student, Courses.Title, Courses.CID, Courses.cBegin, Courses.cEnd, Courses.Description 
                FROM Students
                INNER JOIN Courses ON (Students.CID = Courses.CID)
                ".$this->getSQLWhereString()." ".$this->getSQLORderString(array('cBegin','cEnd'))."
                
            ";
            $res = sql($sql);
            
            if (sqlrows($res)) {
             
                while($row = sqlget($res)) {
                                        
                    $row['teachers'] = strip_tags(get_teachers_list((int) $row['CID']),'<br>');
                    if (strstr($row['Description'],'~name=control')) {
                        
                        if (($cond = getmetavalue(read_metadata($row['Description']),'control')) && !empty($cond))
                        $row['Condition'] = $cond;
                                                
                    } else $row['Condition'] = _('нет');
                    
                    /**
                    * Подготовка данных для вычисления времени обучения
                    //
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
            //
            $days = (int) ((strtotime($maxDate) - strtotime($minDate)) / (60*60*24));
            $this->additionalData[_('Общее время обучения')] = "$days "._("дней");
            */

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
            
            case 'course':
                $sql = "SELECT DISTINCT s.CID, c.Title
                        FROM Students s
                        LEFT JOIN Courses c ON (c.CID = s.CID)
                        WHERE s.MID = '".(int) $inputFieldData['MID']."'";
                $res = sql($sql);
                while ($row = sqlget($res)){
                    $ret[$row['CID']] = $row['Title'];
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
        $people = CExamSheetsReport::getReportInputField('MID',false,$where);
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