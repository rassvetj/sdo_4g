<?php

/**
* todo: 
* 
* + putcomments
* This AU request message sends information that contains written
* "comments" made by the student to the CMI system.
* 
* + putobjectives
* This AU request message sends information that contains student
* performance (to specific objectives) to the CMI system.
* 
* + putpath
* This AU request message sends information to the CMI with regards to the
* path the student user navigated thru the AU to the CMI system.
* 
* + putperfomance
* This AU request message sends information that contains Learner
* performance information to the CMI system.
*/

require_once($GLOBALS['wwf'].'/lib/aicc/aicc.class.php');

$hacp_statuses = array(
    'passed' => 'passed',
    'completed' => 'completed',
    'failed' => 'failed',
    'incomplete' => 'incomplete',
    'browsed' => 'browsed',
    'not attempted' => 'not attempted',
    'p' => 'passed',
    'c' => 'completed',
    'f' => 'failed',
    'i' => 'incomplete',
    'b' => 'browsed',
    'n' => 'not attempted'
);

$hacp_exites = array(
    'logout' => 'logout',
    'time-out' => 'time-out',
    'suspend' => 'suspend',
    'l' => 'logout',
    't' => 'time-out',
    's' => 'suspend'
);

$ritzio_module_types = array(
    'learning' =>  1,
    'help'     =>  2,
    'group'    =>  3,
    'control'  =>  4
);

class CHACP_Tracking {
    
    var $uid;
    var $objId;

    var $subjectId;
    var $lessonId;
    var $time;
    var $version = null;
    
    function init() {
        $hacp_keys = array('command','version','session_id','aicc_data');
        $postVars = array_change_key_case($_POST, CASE_LOWER);        
        foreach($hacp_keys as $key) {
            $$key=$postVars[$key];
        }
        
        $allowed_commands = array('getparam','putparam','exitau','startau','putcomments','putobjectives','putpath','putinteractions','putperfomance');
        $command = strtolower($command);
        if (!in_array($command,$allowed_commands)) exit();

        $this->uid = $_SESSION['s']['mid'];
        $this->objId = $session_id;

        $this->subjectId = (int) $_GET['subject_id'];
        $this->lessonId = (int) $_GET['lesson_id'];
        $this->time = (int) $_GET['time'];

        if (isset($version)) {
            $this->version = $version;
        }
        
        $this->$command($session_id,$version,$aicc_data);
    }
    
    function getparam($session_id, $version, $aicc_data) {
        $_SESSION['s']['hacp']['trackID'] = $this->_start_track($session_id, $this->time);
        $response = new CHACP_Response();
        $response->init($this->uid, $this->objId);
        $response->send_params($version);
        
    }
    
    function _process_suspend_data($session_id, $data) {
        $data = explode('%r%',$data); $vars = array();
        foreach($data as $variable) {
            $parts = explode('=',$variable);
            if (count($parts) == 2) {
                $parts[0] = trim($parts[0]);
                $parts[1] = trim($parts[1]);
                $name = explode('_',$parts[0]);
                if ($parts[1][0]=="\"") {
                    $parts[1] = trim(substr($parts[1],1,-1));
                }
                switch(strtolower($name[0])) {
                    case 'module':
                        switch(strtolower($name[2])) {
                            case 'id':                                
                            case 'start':
                            case 'stop':
                            case 'score':
                            case 'scoremax':
                            case 'scoremin':
                            case 'title':
                            case 'type':
                                $vars[$name[0]][$name[1]][$name[2]] = $parts[1];
                                break;
                            
                        }
                        break;
                }
            }
        }
        
        if (isset($vars['module']) && is_array($vars['module']) && count($vars['module'])) {
            if (($_SESSION['s']['perm']==1) && $session_id) {
                $sql = "SELECT * FROM organizations WHERE module ='".(int) $session_id."'";
                $res = sql($sql);
                if ($row = sqlget($res)) {
                    $ModID = $row['oid'];
                    $CID   = $row['cid'];
                    
                    foreach($vars['module'] as $module) {
                                
                        $sql = "INSERT INTO scorm_tracklog
                                (
                                subject_id,
                                lesson_id,
                                mid, 
                                cid, 
                                ModID, 
                                McID, 
                                module,
                                start,
                                stop,
                                score,
                                score_max,
                                score_min,
                                title,
                                type
                                ) VALUES
                                (
                                '".(int) $this->subjectId."',
                                '".(int) $this->lessonId."',
                                '".(int) $_SESSION['s']['mid']."',
                                '".(int) $CID."',
                                '".(int) $ModID."',
                                '".(int) $session_id."',
                                ".$GLOBALS['adodb']->Quote($module['id']).",
                                ".$GLOBALS['adodb']->Quote($module['start']).",
                                ".$GLOBALS['adodb']->Quote($module['stop']).",
                                ".$GLOBALS['adodb']->Quote($module['score']).",
                                ".$GLOBALS['adodb']->Quote($module['scoremax']).",
                                ".$GLOBALS['adodb']->Quote($module['scoremin']).",
                                ".$GLOBALS['adodb']->Quote($module['title']).",
                                ".$GLOBALS['adodb']->Quote($GLOBALS['ritzio_module_types'][$module['type']])."
                                )";
                        $res = sql($sql);
                        return sqllast();
                        
                    }
                }
            }
        }
        
    }
    
    function putparam($session_id, $version, $aicc_data) {
        $data = $this->_parse_aicc_data($aicc_data);

        $score = $data['cmi.core.score.raw'];
        $scoremax = $data['cmi.core.score.max'];
        $scoremin = $data['cmi.core.score.min'];
        if (!$score && $data['cmi.core.score']) {
            $score = $data['cmi.core.score'];
            $scoremax = 100;
            $scoremin = 0;
        }

        if (($_SESSION['s']['perm']==1) && $_SESSION['s']['hacp']['trackID']) {
                        
            if (isset($data['cmi.suspend_data']) && !empty($data['cmi.suspend_data'])) {
                $this->_process_suspend_data($session_id, $data['cmi.suspend_data']);
            }
            
            $trackdata = $this->_get_trackdata($_SESSION['s']['hacp']['trackID']);
            if (is_array($trackdata) && count($trackdata)) {
                $data = array_merge($trackdata,$data);
            }


            // DEBUG BEGIN
            ob_start();

            print_r("-- PUT PARAM --");
            print_r($data);

            $content = ob_get_contents();
            $fp = fopen('hacp_track.log','a+');
            fwrite($fp,$content);
            fclose($fp);
            ob_clean();
            // DEBUG END

            $sql = "UPDATE scorm_tracklog 
                    SET 
                        stop=NOW(), 
                        trackdata='".serialize($data)."', 
                        score='".$score."',
                        scoremax='".$scoremax."', 
                        scoremin='".$scoremin."', 
                        status='".$data['cmi.core.lesson_status']."'
                    WHERE trackID='".(int) $_SESSION['s']['hacp']['trackID']."'";
            sql($sql);
        }

        $GLOBALS['hasp_track_status'] = $data['cmi.core.lesson_status'];
        $GLOBALS['hasp_track_score']  = $score;

        $response = new CHACP_Response();
        $response->init($this->uid, $this->objId);
        $response->send_ok($this->version);
    }
    
    function putcomments($session_id, $version, $aicc_data) {
        $response = new CHACP_Response();
        $response->init($this->uid, $this->objId);
        $response->send_ok($this->version);
    }

    function putobjectives($session_id, $version, $aicc_data) {
        $response = new CHACP_Response();
        $response->init($this->uid, $this->objId);
        $response->send_ok($this->version);
    }
    
    function putpath($session_id, $version, $aicc_data) {
        $response = new CHACP_Response();
        $response->init($this->uid, $this->objId);
        $response->send_ok($this->version);
    }        
    
    function _get_trackdata($trackID) {
        if ($trackID) {
            $sql = "SELECT trackdata FROM scorm_tracklog WHERE trackID='".(int) $trackID."'";
            $res = sql($sql);
            if (sqlrows($res)) {
                $row = sqlget($res);
                return unserialize($row['trackdata']);
            }
        }
        return array();
    }

    function putinteractions($session_id, $version, $aicc_data) {
        $data = explode("\n",$aicc_data);
        $columns = CAicc::_get_aicc_columns($data[0]);
        $regexp = CAicc::_forge_cols_regexp($columns->columns);

        if (($_SESSION['s']['perm']==1) && $session_id) {
            $trackdata = $this->_get_trackdata($_SESSION['s']['hacp']['trackID']);
        }        
        
        $n=0;
        for ($i=1;$i<count($data);$i++) {
            if (preg_match($regexp,$data[$i],$matches)) {
                for ($j=0;$j<count($columns->columns);$j++) {
                    if (strtolower($columns->columns[$j])=='type_interaction') $columns->columns[$j] = 'type';
                    if (strtolower($columns->columns[$j])=='interaction_id') $columns->columns[$j] = 'id';
                    $trackdata['cmi.interactions.'.(int)$n.'.'.$columns->columns[$j]] = substr($matches[$j+1],1,-1);
                }
                $n++;
            }
        }
        if (($_SESSION['s']['perm']==1) && $session_id) {
            $sql = "UPDATE scorm_tracklog 
                    SET trackdata='".serialize($trackdata)."' 
                    WHERE trackID='".$_SESSION['s']['hacp']['trackID']."'";
            sql($sql);
        }        
        $response = new CHACP_Response();
        $response->init($this->uid, $this->objId);
        $response->send_ok($this->version);
    }

    function putperfomance($session_id, $version, $aicc_data) {
        $response = new CHACP_Response();
        $response->init($this->uid, $this->objId);
        $response->send_ok($this->version);
    }
    
    function startau($session_id, $version, $aicc_data) {
        $this->getparam($session_id, $version, $aicc_data);
    }
    
    function exitau($session_id, $version, $aicc_data) {
        if (($_SESSION['s']['perm'] == 1) && $_SESSION['s']['hacp']['trackID']) {
            $sql = "UPDATE scorm_tracklog SET stop = NOW(), lesson_id = '".(int) $this->lessonId."' WHERE trackID = '".(int) $_SESSION['s']['hacp']['trackID']."'";
            sql($sql);
        }
        $response = new CHACP_Response();
        $response->init($this->uid, $this->objId);
        $response->send_ok($this->version);
        unset($_SESSION['s']['hacp']['trackID']);
    }
        
    function _start_track($session_id, $time) {
        if (($_SESSION['s']['perm']==1) && $session_id && $time) {
            $sql = "SELECT * FROM organizations WHERE module ='".(int) $session_id."'";
            $res = sql($sql);
            if (sqlrows($res)) {
                $row = sqlget($res);
                $ModID = $row['oid'];
                $CID = $row['cid'];

                $sql = "
                SELECT trackID FROM scorm_tracklog
                WHERE
                    mid = '".(int) $_SESSION['s']['mid']."'
                    AND cid = '".(int) $CID."'
                    AND ModID = '".(int) $ModID."'
                    AND McID = '".(int) $session_id."'
                    AND start = ".$GLOBALS['adodb']->DBTimeStamp($time)."
                    AND lesson_id = '".(int) $this->lessonId."'";
                $res = sql($sql);
                if ($row = sqlget($res)) {
                    return $row['trackID'];
                }

                $sql = "INSERT INTO scorm_tracklog
                        (mid, cid, ModID, McID, start, stop, trackdata, lesson_id) VALUES
                        ('".(int) $_SESSION['s']['mid']."','".(int) $CID."','".(int) $ModID."','".(int) $session_id."', ".$GLOBALS['adodb']->DBTimeStamp($time).", NOW(), '', $this->lessonId)";
                $res = sql($sql);
                return sqllast();                
            }
        }
    }
    
    function _parse_aicc_data($string) {
        $data = array();
        if (!empty($string)) {
            $lines=explode("\n", $string);
            for($i=0;$i<count($lines);$i++) {
                $line=trim($lines[$i]);
                if (empty($line) || substr($line,0,1)==";" || substr($line,0,1)=="#"){
                    continue;
                }
                if (substr($line,0,1)=="[") {
                    $block=substr($line,1,-1);
                    continue;
                }
                if (empty($block))
                    continue;

                if (strtolower($block) == 'core_lesson') {
                    $block = 'suspend_data';
                }
                
                if (strtolower($block) == 'objectives_status') {
                    if (substr_count($line,"=") == 1) {
                        $line = explode("=",$line);
                        if (substr_count($line[0],".") == 1) {
                            $name = explode(".",$line[0]);
                            switch(strtolower($name[0])) {
                                case 'j_id':
                                    $data[strtolower("cmi.objectives.".(int) $name[1].".id")] = $line[1];
                                break;
                                case 'j_score':
                                    $values = explode(',',$line[1]);
                                    if ((count($values) > 1) && ($values[1] >= $values[0]) && is_numeric($values[1])) {
                                        $value = trim($values[1]);
                                        $data["cmi.objectives.".(int) $name[1].".score.max"] = $value;
                                        if ((count($values) == 3) && ($values[2] <= $values[0]) && is_numeric($values[2])) {
                                            $value = trim($values[2]);
                                            $data["cmi.objectives.".(int) $name[1].".score.min"] = $value;
                                        }
                                    }
                                    
                                    $data["cmi.objectives.".(int) $name[1].".score.raw"] = trim($values[0]);
                                break;
                                case 'j_status':
                                    $data[strtolower("cmi.objectives.".(int) $name[1].".status")] = $GLOBALS['hacp_statuses'][strtolower($line[1])];
                                break;
                            }
                        }                                                
                    }
                    continue;
                }
                                   
                if (substr_count($line, "=")!=1) {
                    $data[strtolower("cmi.".$block)]=$line;
                } else if (substr_count($line, "=")==1) {
                    $line=explode("=", $line);
                    switch(strtolower("cmi.".$block.".".$line[0])) {
                        case 'cmi.core.lesson_status':
                            $values = explode(',',$line[1]);
                            $value = '';
                            if (count($values)>1) {
                                $value = trim(strtolower($values[1]));
                                if (isset($GLOBALS['hacp_exites'][strtolower($value)])) {
                                    $value = $GLOBALS['hacp_exites'][strtolower($value)];
                                }
                            }
                            if (!empty($value) || isset($GLOBALS['hacp_exites'][$value])) {
                                $data['cmi.core.exit'] = $value;
                            }
                            $value = trim(strtolower($values[0]));
                            if (isset($GLOBALS['hacp_statuses'][strtolower($value)])) {
                                $value = $GLOBALS['hacp_statuses'][strtolower($value)];
                            }
                            $data[strtolower("cmi.".$block.".".$line[0])]=$value;
                            break;
                        case 'cmi.core.score':
                            $values = explode(',',$line[1]);
                            if ((count($values) > 1) && ($values[1] >= $values[0]) && is_numeric($values[1])) {
                                $value = trim($values[1]);
                                $data['cmi.core.score.max'] = $value;
                                if ((count($values) == 3) && ($values[2] <= $values[0]) && is_numeric($values[2])) {
                                    $value = trim($values[2]);
                                    $data['cmi.core.score.min'] = $value;
                                }
                            }
                            
                            $data['cmi.core.score.raw'] = trim($values[0]);
                            
                            break;
                        default:
                            $data[strtolower("cmi.".$block.".".$line[0])]=$line[1];                            
                    }
                }
            }
        }
        return $data;
    }
        
}

class CHACP_Response {
    
    var $uid;
    var $objId;
    
    function init($uid, $objId) {
        $this->uid = $uid;
        $this->objId = $objId;
        
    }
    
    function _get_student_name($uid) {
        if ($uid) {
            $sql = "SELECT Login, LastName, FirstName FROM People WHERE MID='".(int) $uid."'";
            $res = sql($sql);
            if (sqlrows($res)) {
                $row = sqlget($res);
                return "{$row['LastName']} {$row['FirstName']} ({$row['Login']})";
            }
        }
    }
    
    function _get_obj_params($objId) {
        if ($objId) {
            $sql= "SELECT scorm_params FROM library WHERE bid = '".(int) $objId."'";
            $res = sql($sql);
            if (sqlrows($res)) {
                $row = sqlget($res);
                $params = unserialize($row['scorm_params']);
                return $params;
            }
        }
    }

    function _get_obj_version($objId) {
        if ($objId) {
            $sql= "SELECT content FROM library WHERE bid = '".(int) $objId."'";
            $res = sql($sql);
            if (sqlrows($res)) {
                $row = sqlget($res);
                if (strlen($row['content'])) {
                    $parts = explode('_', $row['content']);
                    if (count($parts) == 2) {
                        return $parts[1];
                    }
                }
            }
        }
        return '4.0';
    }
        
    function send_params($version) {
        ob_start();
        echo "error=0\r\n";
        echo "error_txt=Successful\r\n";
        if (!$version) {
            $version = $this->_get_obj_version($this->objId);
        }
        echo "version=".$version."\r\n";
        //echo "version=2.0\n";
        echo "aicc_data=";
        
        $data["core"]["student_id"]= $this->uid;
        $data["core"]["student_name"] = $this->_get_student_name($this->uid);
        $data["core"]["time"]="00:00:00";
        
//        $data["core_vendor"]=;

        $params = $this->_get_obj_params($this->objId);
        $data["student_data"]["mastery_score"]     = $params['masteryscore'];
        $data["student_data"]["max_time_allowed"]  = $params['maxtimeallowed'];
        $data["student_data"]["time_limit_action"] = $params['timelimitaction'];
        
        $data["student_preferences"]["audio"]="-1";
        $data["student_preferences"]["text"]="1";
        
        $data["core_lesson"]="";
        $data["core"]["output_file"]="";
        $data["core"]["credit"]="c";
        $data["core"]["lesson_location"]="";
        $data["core"]["lesson_status"]="n,a";
        $data["core"]["path"]="";
        $data["core"]["score"]="";        
        
        if ($this->uid && $this->objId) {
            $sql = "SELECT trackdata FROM scorm_tracklog 
                    WHERE mid='".(int) $this->uid."' AND McID='".(int) $this->objId."'
                    ORDER BY trackID ASC";
            $res = sql($sql);
            while($row = sqlget($res)) {
                $trackdata = unserialize($row['trackdata']);
                if (is_array($trackdata) && count($trackdata)) {
                    $score = $objective_score = array();
                    foreach($trackdata as $k=>$v) {
                        $arr = explode('.',$k);
                        array_shift($arr);
                        if (count($arr)==1) {
                            if (strtolower($arr[0]) == 'suspend_data') {
                                $arr[0] = 'core_lesson';
                            }
                            $data[$arr[0]]=$v;
                        } else if (count($arr)==2) {
                            $data[$arr[0]][$arr[1]]=$v;
                        } else if (count($arr)==3) {
                            switch($k) {
                                case 'cmi.core.score.raw':
                                    $score[0] = $v;
                                    break;
                                case 'cmi.core.score.max':
                                    $score[1] = $v;
                                    break;
                                case 'cmi.core.score.min':
                                    $score[2] = $v;
                                    break;
                                default:
                                    if (strtolower($arr[0]) == 'objectives') {
                                        switch(strtolower($arr[2])) {
                                            case 'id':
                                                $data['objective_status']['j_id.'.(int) $arr[1]] = $v;
                                                break;
                                            case 'status':
                                                $data['objective_status']['j_status.'.(int) $arr[1]] = $v;
                                                break;
                                        }
                                    }
                                    break;
                            }
                        } else if (count($arr) == 4) {
                            if (strtolower($arr[0]) == 'objectives') {
                                switch($arr[2]) {
                                    case 'score':
                                        switch(strtolower($arr[3])) {
                                            case 'raw':
                                                $objective_score[$arr[1]][0] = $v;
                                                break;
                                            case 'max':
                                                $objective_score[$arr[1]][1] = $v;
                                                break;
                                            case 'min':
                                                $objective_score[$arr[1]][2] = $v;
                                                break;
                                        }
                                    break;
                                }
                            }
                        }                        
                    }
                    if (count($score) > 0)   {
                        $data['core']['score'] = join(',',$score);
                    }
                    if (count($objective_score) > 0) {
                        foreach($objective_score as $id => $scores) {
                            if (count($scores) > 0) {
                                $data['objective_status']['j_score.'.(int) $id] = join(',',$scores);
                            }
                        }
                    }
                }
            }        
        }
        
        foreach($data as $block => $blockData) {
            echo '['.$block."]\r\n";
            if (!is_array($blockData)) {
                echo $blockData."\r\n";
                continue;
            }
            
            foreach ($blockData as $key=>$value)
                echo "$key=$value\r\n";
        }
        
        $content = ob_get_contents();
        if (defined('HACP_DEBUG') && HACP_DEBUG) {
            $sql = "INSERT INTO hacp_debug
                    (message, date, direction)
                    VALUES (".$GLOBALS['adodb']->Quote(serialize($content)).",".$GLOBALS['adodb']->DBTimestamp(date("Y-m-d H:i:s")).", '1')";
            sql($sql);
        }

        ob_start();

        print_r("-- GET PARAM --\n");
        print_r($content);

        $content = ob_get_contents();
        $fp = fopen('hacp_track.log','a+');
        fwrite($fp,$content);
        fclose($fp);
        ob_clean();
        
        ob_end_flush();
    }
    
    function send_ok($version = null) {
        echo "error=0\r\n";
        echo "error_txt=Successful\r\n";
        if (null === $version) {
            $version = $this->_get_obj_version($this->objId);
        }
        echo "version=".$version."\r\n";
    }
    
}


?>