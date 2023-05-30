<?php
if($GLOBALS['wwf']){
	$path = $GLOBALS['wwf'];	
} else {
	$path = realpath(__DIR__.'/../../');
}
//require_once($GLOBALS['wwf'].'/formula_calc.php');
require_once($path.'\formula_calc.php');


class Schedule {

        var $sheid;
        var $title;

        var $type_id;

        var $module_id = 0;
        var $test_id = 0;
        var $xml_file_path = "";

        var $db_id = 0;

        var $item_element = "";

        var $period = "-1";

        var $begin;

        var $end;

        var $rid;

        var $icon;

        var $description;

        var $teacher_mid;

        var $current;

        var $cid;

        var $CHID;

        var $penaltyFormula_id;

        var $penalty;

        var $teacher;

        var $connectId;
        

        function _process_relative_date_last($date) {

            if ($this->cid) {

                $sql = "SELECT cBegin, cEnd FROM Courses WHERE CID='".(int) $this->cid."'";

                $res = sql($sql);

                if (sqlrows($res)) {

                    $row = sqlget($res);

                    $begin = explode('-',$row['cBegin']);

                    if (is_array($begin) && (count($begin)==3)) {

                        $begin = mktime(0,0,0,$begin[1],$begin[2],$begin[0]);

                        return (int) ($begin + $date);

                    }

                }

            }

        }



        function _process_relative_date($date,$mid=0) {

            if ($mid == 0) $mid = $_SESSION['s']['mid'];

            if ($this->cid) {

                if (isset($GLOBALS['time_registered_cache'][$mid][$this->cid])) {
                    return (int) ($GLOBALS['time_registered_cache'][$mid][$this->cid] + $date);
                } else {

                    $sql = "SELECT time_registered as begin FROM Students WHERE CID='".(int) $this->cid."'

                    AND MID='".(int) $mid."'";

                    $res = sql($sql);

                    if (sqlrows($res)) {

                        $row = sqlget($res);

                        if (preg_match("/^(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})$/",$row['begin'],&$matches)
                        || preg_match("/^(\d{4})-(\d{2})-(\d{2})( (\d{2}):(\d{2}):(\d{2}))*$/", $row['begin'], &$matches)) {

                            $begin = mktime(0,0,0,$matches[2],$matches[3],$matches[1]);
                            $GLOBALS['time_registered_cache'][$mid][$this->cid] = $begin;

                            return (int) ($begin + $date);

                        }

                    }

                }

            }

        }



        function init($sheid, $data = false) {

                global $adodb;

                $this->sheid = $sheid;

                //Устанавливаем id типа занятия

                if ($data && is_array($data)) {
                    $row = &$data;
                } else {
                    $query = "SELECT title, timetype, startday, stopday, CHID, `rid`, `CID`, `period`, `typeID`, `descript`,

                              `createID`, " . $adodb->SQLDate("Y-m-d H:i:s", "begin") . " as begin,

                              " . $adodb->SQLDate("Y-m-d H:i:s", "end") . " as end, teacher as teacher, connectId as connectId

                              FROM schedule

                              WHERE sheid = '$sheid'";

                    $result = sql($query,"errfn");

                    $row = sqlget($result);
                }

                $cid = $row['CID'];

                if ($row['period']) $this->period = $row['period'];

                if ($row['typeID']) $this->type_id = $row['typeID'];

                $this->title = $row['title'];

                $this->teacher = $row['teacher'];
                
                $this->connectId = $row['connectId'];

                $this->rid = $row['rid'];

                $this->CHID = $row['CHID'];

                if ($row['descript']) $this->description = $row['descript'];

//                if ($row['descript']) $this->description = htmlspecialchars($row['descript']);

                if ($row['createID']) $this->teacher_mid = $row['createID'];

                $this->cid = $row['CID'];



                $this->begin = $row['begin'];

                $this->end = $row['end'];

                if ($row['timetype']==1) {

                    $this->begin = date("Y-m-d 00:00:00",$this->_process_relative_date($row['startday']));

                    $this->end = date("Y-m-d 23:59:00",$this->_process_relative_date($row['stopday']));

                }



                //Определяем tools-ы для этого занятия

                if (!$data) {
                    $query = "SELECT * FROM EventTools WHERE TypeID = '".(int) $this->type_id . "'";

                    $result = sql($query);

                    $row = sqlget($result);
                }



                $this->icon = $row['Icon'];



                $tools = explode(",", $row["tools"]);

                foreach($tools as $key => $value) {

                        $tools[$key] = trim($value);

                }


                if (!$data) {
                    $query = "SELECT toolParams as toolParams FROM scheduleID WHERE SHEID = '".$this->sheid."' LIMIT 1";

                    $result = sql($query);

                    $row = sqlget($result);
                }

                $toolParams = explode(";", $row['toolParams']);

                foreach($toolParams as $key => $toolParam) {

                    if(in_array("module", $tools) && (strpos($toolParam, "module_moduleID=") !== false)) {

                        $tmp = explode("=", $toolParam);

                        $this->module_id = trim($tmp[1]);

                    }
                    if(in_array("tests", $tools) && (strpos($toolParam, "tests_testID=") !== false)) {
                        $tmp = explode("=", $toolParam);
                        $this->test_id = trim($tmp[1]);
                    }
                    if(strpos($toolParam, "penaltyFormula_id=") !== false) {

                        $tmp = explode("=", $toolParam);

                        $this->penaltyFormula_id = trim($tmp[1]);

                    }

                }



                //Если занятие связано с модулем устанавливаем id этого модуля

                /*

                if(in_array("module", $tools)) {

                        $query = "SELECT * FROM scheduleID WHERE SHEID = ".$this->sheid."LIMIT 1";

                        $result = sql($query);

                        $row = sqlget($result);

                        $toolParams = explode(";", $row['toolParams']);

                        foreach($toolParams as $key => $toolParam) {

                                if(strpos($toolParam, "module_moduleID=") !== false) {

                                        $tmp = explode("=", $toolParam);

                                        $this->module_id = trim($tmp[1]);

                                }

                        }

                }

                */



                //Проверяем существует ли xml файл курса

                $this->xml_file_path = $this->_check_for_existing_course_xml_file($cid);



                //Определяем db_id если он сушествует в таблице mod_content

                if($this->xml_file_path != "") {

                        $query = "SELECT * FROM mod_content WHERE ModID = '".(int) $this->module_id."'";

                        $result = sql($query);

                        if(sqlrows($result) > 0) {

                                $row = sqlget($result);

                                $tmp = explode("?", $row['mod_l']);

                                $tmp_1 = explode("&", $tmp[1]);

                                foreach($tmp_1 as $key => $value) {

                                        $tmp_2 = explode("=", $value);

                                        if($tmp_2[0] == "id") {

                                                $this->db_id = $tmp_2[1];

                                        }

                                }

                        }

                }



                if($this->db_id != 0) {



                        $xml = domxml_open_file($this->xml_file_path);

                        $xpath_context = xpath_new_context($xml);

                        $elements = xpath_eval($xpath_context, "//*[@DB_ID='".$this->db_id."']");

                        $nodes = $elements->nodeset;

                        $this->item_element = $nodes[0];

                }





        }



        function _check_for_existing_course_xml_file($cid) {

                if(is_file($_SERVER['DOCUMENT_ROOT']."/COURSES/course$cid/course.xml")) {

                        return $_SERVER['DOCUMENT_ROOT']."/COURSES/course$cid/course.xml";

                }

                else {

                        return "";

                }

        }



        function get_type_id() {

                 return $this->type_id;

        }



        function get_type() {

                $query = "SELECT * FROM EventTools WHERE TypeID = '".$this->type_id."'";

                $result = sql($query);

                $row = sqlget($result);

                return $row['TypeName'];

        }



        function get_icon() {

                return $this->icon;

        }



        function get_sheid() {

                return $this->sheid;

        }



        function get_module_id() {

                return $this->module_id;

        }

        function get_test_id() {
                return $this->test_id;
        }

        function get_subject() {

                if($this->item_element != "") {

                        $item_element = $this->item_element;

                        $item_element_childrens = $item_element->child_nodes();

                        if(is_array($item_element_childrens))

                        foreach($item_element_childrens as $key => $item_element_children) {

                                if($item_element_children->tagname == "subject") {

                                        $item_element_children_attribute_title = $item_element_children->get_attribute("title");
                                        return  iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding,$item_element_children_attribute_title);
                                }

                        }

                }

        }



        function get_targets() {

                if($this->item_element != "") {

                        $item_element = $this->item_element;

                        $item_element_childrens = $item_element->child_nodes();

                        if(is_array($item_element_childrens))

                        foreach($item_element_childrens as $key => $item_element_children) {

                                if($item_element_children->tagname == "targets") {

                                        $targets_element_childrens = $item_element_children->child_nodes();

                                        $return_array = array();

                                        if(is_array($targets_element_childrens))

                                        foreach($targets_element_childrens as $target_element) {

                                                if($target_element->tagname == "target") {
                                                        $return_array[] = iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding, $target_element->get_attribute("title"));
                                                }

                                        }

                                        return $return_array;

                                }

                        }

                }

        }



        function get_studiedproblems() {

                if($this->item_element == "") return " ";

                $item_element = $this->item_element;

                $item_element_childrens = $item_element->child_nodes();

                if(is_array($item_element_childrens))

                foreach($item_element_childrens as $key => $item_element_children) {

                        if($item_element_children->tagname == "studiedproblems") {

                                $studiedproblems_element = $item_element_children;

                                $studiedproblems_element_childrens = $studiedproblems_element->child_nodes();

                                $return_value = array();

                                $i = 0;

                                if(is_array($studiedproblems_element_childrens))

                                foreach($studiedproblems_element_childrens as $key => $studiedproblem_element) {

                                        if($studiedproblem_element->tagname == "studiedproblem") {
                                                $return_value[$i]['title'] = iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding,$studiedproblem_element->get_attribute("title"));
                                                $studiedproblem_element_childrens = $studiedproblem_element->child_nodes();

                                                if(is_array($studiedproblem_element_childrens))

                                                foreach($studiedproblem_element_childrens as $studiedproblem_element_child) {

                                                        if($studiedproblem_element_child->tagname == "text") {
                                                                $return_value[$i]['texts'][] = iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding,$studiedproblem_element_child->get_attribute("title"));
                                                        }

                                                }

                                                $i++;

                                        }

                                }

                                return $return_value;

                        }

                }

        }



        function get_time() {
                if($this->period != "-1") {
                        $query = "SELECT * FROM periods WHERE lid = '".$this->period."'";
                        $result = sql($query);
                        if(sqlrows($result) > 0) {
                                $return_array['begin'] = substr($this->begin,11,5);
                                $return_array['end'] = substr($this->end,11,5);
                                return $return_array;
                        }
                        else {
                                return "";
                        }
                }
                elseif (isset($this->current)) {
                        if ($this->current == substr($this->begin,0,10) || ($this->CHID>0)) {
                            $return_array['begin'] = substr($this->begin,11,5);
                        } else {
                            $return_array['begin'] = '00:00';
                        }
                        if ($this->current == substr($this->end,0,10) || ($this->CHID>0)) {
                            $return_array['end'] = substr($this->end,11,5);
                        } else {
                            $return_array['end'] = '23:59';
                        }
                        return $return_array;
                }
                else {
                        return "";
                }

        }



        function get_date() {

                if($this->period != "-1") {

                        $year = substr($this->begin,0,4);

                        $month = substr($this->begin, 5,2);

                        $day = substr($this->begin,8,2);

                        return $day.".".$month.".".$year;

                }

                else {

                        return "";

                }

        }



        function get_begin_date() {

               $year = substr($this->begin,0,4);

               $month = substr($this->begin, 5,2);

               $day = substr($this->begin,8,2);

               return $day.".".$month.".".$year;

        }



        function get_end_date() {

               $year = substr($this->end,0,4);

               $month = substr($this->end, 5,2);

               $day = substr($this->end,8,2);

               return $day.".".$month.".".$year;

        }





        function get_period() {

                if($this->period != "-1") {

                        $query = "SELECT * FROM periods WHERE lid = '".$this->period."'";

                        $result = sql($query);

                        $row = sqlget($result);

                        return $row['name'];

                }

                else {

                        return "";

                }

        }

        function get_period_id() {
            return $this->period;
        }

        function get_count_of_hours() {

                 if($this->period != "-1") {

                    $query = "SELECT * FROM periods WHERE lid = '".$this->period."'";

                    $result = sql($query, "err_select_period_sh");

                    $row = sqlget($result);

                    return $row['count_hours'];

                 }

        }



        function get_room() {

                if( ($this->rid != 0) && ($this->rid != "-1") ) {

                         $query = "SELECT * FROM rooms WHERE rid = '".$this->rid."'";

                         $result = sql($query);

                         if( sqlrows($result) > 0 ) {

                                 $row = sqlget($result);

                                 return $row['name'];

                         }

                         else {

                                 return "";

                         }

                }

                return "";

        }

        function get_room_id() {
            return $this->rid;
        }

        function get_name() {

                $query = "SELECT * FROM schedule WHERE SHEID = '".$this->sheid."'";

                $result = sql($query);

                $row = sqlget($result);

                return $row['title'];

        }

        function get_title() {
            return $this->title;
        }



        function get_teacher() {

                $ret = '';

                if ($this->teacher) {
                    $query = "SELECT * FROM People WHERE MID = '".$this->teacher."'";

                    $result = sql($query);

                    $row = sqlget($result);

                    $ret = $row['LastName'];
                    if (strlen(trim($row['FirstName']))) {
                        $ret .= ' '.trim($row['FirstName']);
                    }

                    if (strlen(trim($row['Patronymic']))) {
                        $ret .= ' '.trim($row['Patronymic']);
                    }
                }

                return $ret;

        }



        function get_teacher_mid() {

                 return $this->teacher_mid;

        }



        function get_edit_permission() {

            return ((($this->get_teacher_mid()==$_SESSION['s']['mid'])

                && $GLOBALS['controller']->checkPermission(SHEDULE_PERM_EDIT_OWN))
                || ($GLOBALS['controller']->checkPermission(SHEDULE_PERM_EDIT_OTHERS))
                || $GLOBALS['controller']->checkPermission(SHEDULE_PERM_EDIT_OTHERS_PEOPLE));
        }



        function get_description() {

                 return nl2br($this->description);

        }



        function get_cid() {

                 return $this->cid;

        }



        function set_current($date) {

            $this->current = $date;

        }



        /**

        * Проверка на попадание текущей даты в периодичность занятия

        */

        function check_rebuild() {

            if (in_array($this->CHID,array(0,1))) return true;



            $unixCurrent = explode('-',$this->current);



            $unixBeginDate = explode(' ',$this->begin);

            $unixBeginDate = explode('-',$unixBeginDate[0]);

            $unixBeginDate = mktime(0,0,0,$unixBeginDate[1],$unixBeginDate[2],$unixBeginDate[0]);



            $unixEndDate = explode(' ',$this->end);

            $unixEndDate = explode('-',$unixEndDate[0]);

            $unixEndDate = mktime(0,0,0,$unixEndDate[1],$unixEndDate[2],$unixEndDate[0]);



            if (is_array($unixCurrent) && (count($unixCurrent)==3)) {

                $unixCurrent = mktime(0,0,0,$unixCurrent[1],$unixCurrent[2],$unixCurrent[0]);



                switch($this->CHID) {

                    case 2: // еженедельно

                        $add = 60*60*24*7;



                        if (!isset($GLOBALS['check_rebuild_2'][$this->sheid])) {

                            while ($unixBeginDate <= $unixEndDate) {

                                $GLOBALS['check_rebuild_2'][$this->sheid][] = $unixBeginDate;

                                $unixBeginDate += $add;

                            }

                        }



						if (in_array($unixCurrent,$GLOBALS['check_rebuild_2'][$this->sheid])) {
							return true;
						} else {
							foreach ($GLOBALS['check_rebuild_2'][$this->sheid] as $time) {
								if ($this->_check_summertime($time, $unixCurrent)) return true;
							}
						}



                        /*

                        if (($unixCurrent - $unixBeginDate)<($unixEndDate-$unixCurrent)) {

                            while ($unixBeginDate <= $unixCurrent) {

                                if ($unixBeginDate == $unixCurrent) return true;

                                $unixBeginDate += $add;

                            }

                        } else {

                            while ($unixEndDate >= $unixCurrent) {

                                if ($unixEndDate == $unixCurrent) return true;

                                $unixEndDate -= $add;

                            }

                        }

                        */

                    break;

                    case 3: // ежемесячно

                        if (!isset($GLOBALS['check_rebuild_3'][$this->sheid])) {

                            while ($unixBeginDate <= $unixEndDate) {

                                $GLOBALS['check_rebuild_3'][$this->sheid][] = $unixBeginDate;

                                $unixBeginDate = strtotime('+1 month',$unixBeginDate);

                            }

                        }



                        if (in_array($unixCurrent,$GLOBALS['check_rebuild_3'][$this->sheid])) return true;



                        /*

                        if (($unixCurrent - $unixBeginDate)<($unixEndDate-$unixCurrent)) {

                            while ($unixBeginDate <= $unixCurrent) {

                                if ($unixBeginDate == $unixCurrent) return true;

                                $unixBeginDate = strtotime('+1 month',$unixBeginDate);

                            }

                        } else {

                            while ($unixEndDate >= $unixCurrent) {

                                if ($unixEndDate == $unixCurrent) return true;

                                $unixEndDate = strtotime('-1 month',$unixEndDate);

                            }

                        }

                        */

                    break;

                    case 4: // через неделю

                    /*
                    $add = 60*60*24*7;

                    $unixEndDate = $unixBeginDate + $add;

                    while ($unixBeginDate <= $unixEndDate) {

                        if ($unixBeginDate == $unixCurrent) return true;

                        $unixBeginDate += $add;

                    }
                    */

                    if (!isset($GLOBALS['check_rebuild_4'][$this->sheid])) {

                        while ($unixBeginDate <= $unixEndDate) {

                            $GLOBALS['check_rebuild_4'][$this->sheid][] = $unixBeginDate;

                            $unixBeginDate = strtotime('+2 week',$unixBeginDate);

                        }

                    }

                    if (is_array($GLOBALS['check_rebuild_4'][$this->sheid])) {
					if (in_array($unixCurrent,$GLOBALS['check_rebuild_4'][$this->sheid])) {
						return true;
					} else {
						foreach ($GLOBALS['check_rebuild_4'][$this->sheid] as $time) {
							if ($this->_check_summertime($time, $unixCurrent)) return true;
						}
					}
                        }
                    break;

                }



            }



        }

        function _check_summertime($time, $time_current){
			if ($time == $time_current + 3600) return true;
			if ($time == $time_current - 3600) return true;
        }



        function get_comments($sheid, $mid) {

            if ($sheid && $mid) {

                $sql ="SELECT comments FROM scheduleID WHERE SHEID='".(int) $sheid."' AND MID='".(int) $mid."'";

                $res = sql($sql);

                if (sqlrows($res) && ($row = sqlget($res))) return $row['comments'];

            }

        }



        function get_penalty($date) {

            if ($this->penaltyFormula_id) {

                $row['formula'] = getPenaltyFormula($this->penaltyFormula_id);

                if (!empty($row['formula'])) {

//                    $days = (int) ((strtotime($this->end)-strtotime($date))/60/60/24);

                    $days = getPenaltyDays(strtotime($date), strtotime($this->end));

                    $ret = viewPenaltyFormula($row['formula'],$days);

                    if ($ret) $ret = (int) (100-round($ret*100));

                    if ($ret < 0) $ret = 0;

                    $this->penalty = $ret;

                    return $ret;

                }

            }

        }

        function get_studyType() {

            return sqlvalue("SELECT EventTools.tools
                             FROM EventTools
                             LEFT JOIN schedule ON (schedule.typeID = EventTools.TypeID)
                             WHERE schedule.SHEID = '{$this->sheid}'");
        }



}



?>