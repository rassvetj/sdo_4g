<?php

class CResultsParser {
    var $_dom;
    var $_sessions = array();

    /**
     * @param array $dom xml2array
     */
    function init($dom) {
        if (is_array($dom) && count($dom)) {
            $this->_dom = $dom;
        }
    }

    function parse() {
        if (is_array($this->_dom) && count($this->_dom)) {
            $this->_parse(&$this->_dom);
        }
        return $this->_sessions;
    }

    function _parse($blocks) {
        static $session = array();
        static $action = array();
        static $result = array();
        static $question = array();

        if (count($blocks)) {
            foreach($blocks as $block) {
                switch($block['name']) {
                    case 'SESSION':
                        $session = array();
                        $session['start'] = $block['attrs']['START_TIME'];
                        $session['stop'] = $block['attrs']['END_TIME'];
                        $session['mid'] = $block['attrs']['USER_ID'];
                        $session['course_id'] = $block['attrs']['COURSE_ID'];
                        $session['module_id'] = $block['attrs']['MODULE_ID'];
                        $session['schedule_id'] = $block['attrs']['SCHEDULE_ID'];
                        $session['actions'] = array();

                        $this->_parse($block['children']);
                        array_push($this->_sessions, $session);
                        break;
                    case 'ACTION':
                        $action = array();
                        $action['type'] = $block['attrs']['TYPE'];
                        $action['id'] = $block['attrs']['DB_ID'];
                        $action['start'] = $block['attrs']['START_TIME'];
                        $action['stop'] = $block['attrs']['END_TIME'];
                        $action['score'] = $block['attrs']['SCORE'];
                        $action['results'] = array();

                        $result = array();
                        $result['id'] = $block['attrs']['DB_ID'];
                        $result['score'] = $block['attrs']['SCORE'];
                        $result['start'] = $block['attrs']['START_TIME'];
                        $result['stop'] = $block['attrs']['END_TIME'];
                        $result['questions'] = array();

                        $this->_parse($block['children']);

                        array_push($action['results'],$result);
                        array_push($session['actions'],$action);
                        break;
                    case 'RESULT':
                        $result = array();
                        $result['id'] = $block['attrs']['DB_ID'];
                        $result['score'] = $block['attrs']['SCORE'];
                        $result['start'] = $block['attrs']['START_TIME'];
                        $result['stop'] = $block['attrs']['END_TIME'];
                        $result['questions'] = array();

                        $this->_parse($block['children']);
                        array_push($action['results'],$result);
                        break;
                    case 'QUESTION':
                        $question = array();
                        $question['id'] = $block['attrs']['DB_ID'];
                        $question['type'] = $block['attrs']['TYPE'];
                        $question['start'] = $block['attrs']['START_TIME'];
                        $question['stop'] = $block['attrs']['END_TIME'];
                        $question['score'] = $block['attrs']['SCORE'];
                        $question['tryes'] = $block['attrs']['TRYES'];
                        $question['balmax'] = $block['attrs']['BALMAX'];
                        $question['balmin'] = $block['attrs']['BALMIN'];
                        $question['answers'] = array();

                        $this->_parse($block['children']);
                        array_push($result['questions'],$question);
                        break;
                    case 'ANSWER':
                        $answer = array();
                        $answer['id'] = $block['attrs']['DB_ID'];
                        $answer['value'] = $block['attrs']['VALUE'];
                        $answer['text'] = $block['tagData'];

                        $answer['number_of_question'] = $block['attrs']['NUMBER_OF_QUESTION'];
                        $answer['number_of_answer']   = $block['attrs']['NUMBER_OF_ANSWER'];
                        $answer['weight']             = $block['attrs']['WEIGHT'];

                        array_push($question['answers'],$answer);
                        break;
                    case 'COMMENT':
                        $comment = $block['tagData'];
                        $question['comment'] = $comment;
                        break;
                }
            }
        }

    }

}

class CResultProcessor {
    var $_sheids = array();
    var $_results;
    var $_startlimits = array();
    var $_automarks = array();

    function init(&$results) {
        $this->_results = &$results;
    }

    function _personExists($mid) {
        if ($mid) {
            $sql = "SELECT MID FROM People WHERE MID='".(int) $mid."'";
            $res = sql($sql);
            return sqlrows($res);
        }
    }

    function _testAttemptExists($mid, $tid, $start, $stop) {
        if ($mid && $tid && $start && $stop) {
            $sql = "SELECT * FROM loguser
                    WHERE
                        mid   = '".(int) $mid."' AND
                        tid   = '".(int) $tid."' AND
                        start = '".$this->_to_seconds($start)."' AND
                        stop  = '".$this->_to_seconds($stop)."'";
            $res = sql($sql);
            return sqlrows($res);
        }
    }

    function _testQuestionExists($mid, $kod, $start, $stop) {
        if ($mid && $kod && $start && $stop) {
            $sql = "SELECT * FROM logseance
                    WHERE
                        mid   = '".(int) $mid."' AND
                        kod   = '".(int) $kod."' AND
                        time = '".$this->_to_seconds($start)."'";
            $res = sql($sql);
            return sqlrows($res);
        }
    }

    function _moduleCreate($cid,$tid) {
        if ($cid && $tid) {
            $sql = "INSERT INTO mod_list
                    (Title, Pub, CID, test_id) VALUES
                    ('Задание #".(int) $tid."','1','".$cid."','".$tid."')";
            sql($sql);
            return sqllast();
        }
    }

    function _moduleExists($cid, $tid) {
        if ($cid && $tid) {
            $sql = "SELECT ModID FROM mod_list
                    WHERE CID='".(int) $cid."'
                    AND test_id='".(int) $tid."'
                    OR test_id LIKE '".(int) $tid.";%'
                    OR test_id LIKE '%;".(int) $tid."'
                    OR test_id LIKE '%;".(int) $tid.";%'";
            $res = sql($sql);
            if (sqlrows($res) && ($row = sqlget($res))) return $row['ModID'];
        }
    }

    function _scheduleCreate($cid, $module, $tid) {
        if ($cid && $module) {
            $sql = "INSERT INTO schedule
                    (title, descript, begin, end, createID, typeID, vedomost, CID, teacher) VALUES
                    ('Результаты #".(int) $tid."', 'Занятие создано автоматически для учета результатов offline-тестирования', '".date('Y-m-d 00:00:00')."','".date('Y-m-d 23:59:00')."','1','1','1','".(int) $cid."', '1')";
            $res = sql($sql);
            return sqllast();
        }
    }

    function _scheduleExists($cid, $module) {
        if ($cid && $module) {
            $sql = "SELECT schedule.SHEID
                    FROM schedule
                    INNER JOIN scheduleID ON (scheduleID.SHEID=schedule.SHEID)
                    WHERE schedule.CID='".(int) $cid."'
                    AND schedule.descript='offline'
                    AND scheduleID.toolParams LIKE '%module_moduleID=".(int) $module."%'";
            $res = sql($sql);
            if (sqlrows($res) && ($row = sqlget($res))) {
                return $row['SHEID'];
            }
        }
    }

    function _createSchedule($mid, $cid, $tid) {
        if ($mid && $cid && $tid) {
            if (!($ModID = $this->_moduleExists($cid, $tid))) {
                $ModID = $this->_moduleCreate($cid, $tid);
            }

            if ($ModID) {
                if (!($SHEID = $this->_scheduleExists($cid, $ModID))) {
                    $SHEID = $this->_scheduleCreate($cid, $ModID, $tid);
                }

                if ($SHEID) {
                    $sql = "SELECT SHEID FROM scheduleID WHERE MID='".(int) $mid."' AND SHEID='".(int) $SHEID."'";
                    $res = sql($sql);
                    if (!sqlrows($res)) {
                        $sql = "INSERT INTO scheduleID
                                (SHEID, MID, toolParams) VALUES
                                ('".(int) $SHEID."','".(int) $mid."','module_moduleID=".(int) $ModID.";')";
                        sql($sql);
                    }
                }

                return $SHEID;
            }
        }
    }

    function _getTestQuestion($kod) {
        if ($kod) {
            $sql = "SELECT * FROM list WHERE kod=".$GLOBALS['adodb']->Quote($kod)."";
            $res = sql($sql);
            if (sqlrows($res) && ($row = sqlget($res))) {
                return $row;
            }
        }
    }

    function _processTestQuestionAnswers(&$answers, $type) {
        if (is_array($answers) && count($answers)) {
            foreach($answers as $answer) {
                switch ($type) {
                    case 1:
                        $ret = $answer['id'];
                        break;
                    case 5:
                        $ret[] = $answer['value'];
                        break;
                    case 6:
                        $ret = $answer['text'];
                        break;
                    case 11: // todo: text!
                        $ret[$answer['number_of_question']] = $answer['number_of_answer'];
                        break;
                    default:
                        if (!isset($answer['value'])) $answer['value'] = 1;
                        $ret[$answer['id']] = $answer['value'];
                }
            }
        }
        return $ret;
    }

    function _processTestQuestionData(&$data, &$question) {
        $data['time']    = $this->_to_seconds($question['start']);
        $data['kod']     = $question['id'];
        $data['bal']     = $question['score'];
        $data['balmax']  = $question['balmax'];
        $data['balmin']  = $question['balmin'];
        if ($q = $this->_getTestQuestion($question['id'])) {
            switch ($question['type']) {
                case 'single':
                    $type = 1;
                    $params['otvet'] = $question['answers'][0]['id'];
                    //$params['otvet'] = $this->_processTestQuestionAnswers($question['answers'],1);
                    break;
                case 'multiple':
                    $type = 2;
                    $params = $this->_processTestQuestionAnswers($question['answers'],2);
                    break;
                case 'compare':
                    $type = 3;
                    $params = $this->_processTestQuestionAnswers($question['answers'],3);
                    break;
                case 'fill':
                    $type = 5;
                    $params['otvet'] = $this->_processTestQuestionAnswers($question['answers'],5);
                    break;
                case 'free':
                    $type = 6;
                    $params['otvet'] = $data['text'] = $this->_processTestQuestionAnswers($question['answers'],6);
                    break;
                case 'table': // todo: data['text']
                    $type = 11;
                    $params = $this->_processTestQuestionAnswers($question['answers'],11);
                    if (!empty($question['comment'])) {
                        $data['text'] = $question['comment'];
                    }
                    break;
            }
            if (isset($type)) {
                require_once("template_test/{$type}-v.php");
                $func = 'v_sql2php_'.$type;
                $vopros = $func($q);

                switch($type) {
                    case 3:
                        foreach($params as $k=>$v) {
                            $params[$k] = $vopros['variant2'][$k];
                        }
                        break;
                }

                $func = 'v_otvet_'.$type;
                $otvet = $func($vopros,$type,$data['number'],$attach,$params);

                if ($type == 11) {
                	if (is_array($question['answers'])) {
                		foreach($question['answers'] as $answer) {
                			$otvet['doklad']['weights'][$answer['number_of_answer'] + 1] = $answer['weight'];
                		}
                	}
                }

                $func = 'v_vopros_'.$type;
                $data['vopros'] = addslashes($func($vopros,$type,$data['number'],$attach));

                $data['good'] = $otvet['good'];
                $data['otvet'] = serialize($otvet['doklad']);

            }
        }

    }

    function _processTestQuestion($data, $question) {
        if ($data['mid'] && $data['cid'] && $data['tid']
        && $data['stid'] && $question['id'] && $question['tryes']
        && !$this->_testQuestionExists($data['mid'],$question['id'],$question['start'],$question['stop'])) {

            $this->_processTestQuestionData($data, $question);

            $sql = "INSERT INTO logseance
                    (".join(',',array_keys($data)).") VALUES
                    ('".join("','",array_values($data))."')";
            sql($sql);
            if ($question['type']=='free') { // TODO
                $sql = "INSERT INTO seance
                        (stid,mid,cid,tid,kod,text,time) VALUES
                        ('".$data['stid']."','".$data['mid']."','".$data['cid']."','"
                        .$data['tid']."','".$data['kod']."','".$data['text']."','".date("YmdHis",$data['time'])."')";
                sql($sql);

                $sql = "UPDATE loguser SET moder='1',needmoder='1' WHERE stid='".(int) $data['stid']."'";
                sql($sql);
            }
        }
    }

    function _processTestAttemptData(&$data, &$question) {
        $data['log']['akod'][]     = $question['id'];
        $data['log']['abalmax'][]  = $question['balmax'];
        $data['log']['abalmax2'][] = $question['balmax'];
        $data['log']['abalmin'][]  = $question['balmin'];
        $data['log']['abalmin2'][] = $question['balmin'];
        $data['log']['abal'][]     = $question['score'];
        $data['balmax2']           += (double) $question['balmax'];

        if ($question['type']=='free') {
            $data['moder']          = 1;
            $data['needmoder']      = 1;
        } else {
            $data['balmax']        += (double) $question['balmax'];
        }

        if ($question['tryes']>=1) {
            $data['log']['adone'][] = $question['id'];
            $data['questdone']++;
        } else {
            $data['status'] = 2;
        }

        if ($question['balmin']<$data['balmin']) {
            $data['balmin'] = $question['balmin'];
        }
        $data['balmin2'] = $data['balmin'];
    }

    /**
     * Избавляется от повторяюзихся ответов на один и тот же вопрос в тесте.
     * Оставляет последний ответ.
     *
     * @param array $questions
     * @return array
     */
    function _deleteDuplicatedQuestions($questions) {
        $ret = array();
        if (is_array($questions) && count($questions)) {
            foreach($questions as $question) {
                if (!isset($ret[$question['id']])) {
                    $ret[$question['id']] = $question;
                    continue;
                }
                if ($question['start'] > $ret[$question['id']]['start']) {
                    $ret[$question['id']] = $question;
                }
            }
        }
        return $ret;
    }

    function _processTestAttempt($mid, $cid, &$attempt, $schedule_id=0) {
        if ($mid && $cid && ($tid = $attempt['id'])) {

            if (!isset($this->_startlimits[$tid])) {
                $startlimit = (int) getField('test', 'startlimit', 'tid', $tid);
                if (!$startlimit) $startlimit = -1;
                $this->_startlimits[$tid]['startlimit'] = $startlimit;
            }

            if (!isset($this->_startlimits[$tid]['people'][$mid])) {
                $this->_startlimits[$tid]['people'][$mid] = $this->_startlimits[$tid]['startlimit'];
            }

            if ($this->_startlimits[$tid]['people'][$mid] == 0) {
                return true;
            }

            if ($this->_startlimits[$tid]['people'][$mid] > 0) {
                $this->_startlimits[$tid]['people'][$mid]--;
            }

            if (!$this->_testAttemptExists($mid, $tid, $attempt['start'], $attempt['stop'])) {

                if ($schedule_id) {
                    $this->_automarks[$schedule_id][$mid] = $tid;
                }

                $data['mid']       = $mid;
                $data['cid']       = $cid;
                $data['questall']  = count($attempt['questions']);
                $data['bal']       = $attempt['score'];
                $data['tid']       = $attempt['id'];
                $data['start']     = $this->_to_seconds($attempt['start']);
                $data['stop']      = $this->_to_seconds($attempt['stop']);
                $data['fulltime']  = (int) ($this->_to_seconds($attempt['stop'])-$this->_to_seconds($attempt['start']));
                $data['status']    = 1;
                $data['qty']       = 1;
                $data['sheid']     = $schedule_id;

                $sql = "INSERT INTO loguser
                        (".join(',',array_keys($data)).") VALUES
                        ('".join("','",array_values($data))."')";
                sql($sql);

                if ($stid=sqllast()) {

                    unset($data);

                    $data['questdone'] = $data['balmax'] = $data['balmax2'] = $data['balmin'] = 0;
                    $number = 0;
                    foreach($this->_deleteDuplicatedQuestions($attempt['questions']) as $question) {

                        $this->_processTestAttemptData($data, $question);

                        $this->_processTestQuestion(
                        array('mid'=>$mid,'cid'=>$cid,'tid'=>$tid,'stid'=>$stid,'number'=>$number,'sheid'=>$schedule_id), $question
                        );
                        ++$number;
                    }

                    $data['log'] = serialize($data['log']);

                    foreach($data as $k=>$v) {
                        $_sql[] = "$k='{$v}'";
                    }
                    $sql = "UPDATE loguser SET ".join(',',$_sql)." WHERE stid=".(int) $stid;
                    sql($sql);

                    if ($schedule_id) {
                        $this->_sheids[] = $schedule_id;
                    } else {
                        $this->_sheids[] = $this->_createSchedule($mid, $cid, $tid);
                    }
                }

            }
        }
    }

    function _processTest($mid, $cid, &$action, $schedule_id=0) {
        if (is_array($action['results']) && count($action['results'])) {
            for($i=0;$i<count($action['results']);$i++) {
                $this->_processTestAttempt($mid, $cid, $action['results'][$i], $schedule_id);
            }
        }
    }

    function _getModuleContentId($module_id) {
        if ($module_id) {
            $sql = "SELECT module FROM organizations WHERE oid = '".(int) $module_id."'";
            $res = sql($sql);
            if (sqlrows($res) && ($row = sqlget($res))) {
                return $row['module'];
            }
        }
    }

    function _processLessonTest($mid, $cid, $module_id, &$result, $schedule_id=0) {
        if ($mid && $cid && $module_id && $result) {
            if ($McID = $this->_getModuleContentId($module_id)) {
                $status = "брошен";
                if (is_array($result['questions']) && count($result['questions'])) {
                    $status = 'выполнен';
                    $i = 0;
                    foreach ($result['questions'] as $k=>$question) {
                        $track_data['cmi.interactions.'.$i.'.type']    = $question['type'];
                        $track_data['cmi.interactions.'.$i.'.id']      = $question['id'];
                        $track_data['cmi.interactions.'.$i.'.result']  = $question['score'];
                        $duration = (int) ($this->_to_seconds($question['stop'])-$this->_to_seconds($question['start']));
                        $track_data['cmi.interactions.'.$i.'.latency'] = (int) ($duration/60/60).':'.date('i:s',$duration);
                        $i++;
                    }
                }
                if (isset($track_data)) $track_data = serialize($track_data);
                $sql = "INSERT INTO scorm_tracklog
                        (mid,cid,ModID,McID,trackdata,stop,start,score,scoremax,status) VALUES
                        ('".$mid."','".$cid."','".$module_id."','".$McID."','".$track_data."','".date('Y-m-d H:i:s',$this->_to_seconds($result['stop']))."','".date('Y-m-d H:i:s',$this->_to_seconds($result['start']))."','".$result['score']."','".$result['score']."','".$status."')";
                sql($sql);

                if ($schedule_id) return $schedule_id;

                if (!($SHEID = $this->_scheduleExists($cid,$module_id))) {
                    $SHEID = $this->_scheduleCreate($cid, $module_id, $module_id);
                }
                if ($SHEID) {
                    $sql = "SELECT SHEID FROM scheduleID WHERE MID='".(int) $mid."' AND SHEID='".(int) $SHEID."'";
                    $res = sql($sql);
                    if (!sqlrows($res)) {
                        $sql = "INSERT INTO scheduleID
                                (SHEID, MID, toolParams) VALUES
                                ('".(int) $SHEID."','".(int) $mid."','module_moduleID=".(int) $module_id.";')";
                        sql($sql);
                    }
                }
            }
        }
        return $SHEID;
    }

    function _processLesson($mid, $cid, $module_id, &$action, $schedule_id=0) {
        if (is_array($action['results']) && count($action['results'])) {
            for($i=0;$i<count($action['results']);$i++) {
                $this->_sheids[] = $this->_processLessonTest($mid, $cid, $module_id, $action['results'][$i], $schedule_id);
            }
        }
    }

    function _processAction($mid, $cid, &$action, $module_id=0, $schedule_id=0) {
        if ($mid && $cid && is_array($action) && count($action)) {
            switch($action['type']) {
                case 'test':
                    $this->_processTest($mid, $cid, $action, $schedule_id);
                break;
                case 'module':
                    $this->_processLesson($mid, $cid, $module_id, $action, $schedule_id);
                break;
            }
        }
    }

    function _processActions($mid, $cid, &$actions, $module_id=0, $schedule_id=0) {
        if (is_array($actions) && count($actions)) {
            for($i=0;$i<count($actions);$i++) {
                $this->_processAction($mid, $cid, $actions[$i], $module_id, $schedule_id);
            }
        }
    }

    function _sessionExists($mid, $start, $stop) {
        global $adodb;
        if ($mid && $start && $stop) {
            $sql = "SELECT * FROM sessions
                    WHERE
                        mid='".(int) $mid."' AND
                        start=".$adodb->DBTimeStamp($this->_to_seconds($start))." AND
                        stop=".$adodb->DBTimeStamp($this->_to_seconds($stop));
            $res = sql($sql);
            return sqlrows($res);
        }
    }

    function _processSession($mid, $start, $stop) {
        global $adodb;
        if ($mid && $start && $stop && !$this->_sessionExists($mid, $start, $stop)) {
            $sql = "INSERT INTO sessions (mid, start, stop, ip, logout)
                    VALUES (
                        '".(int) $mid."',
                        ".$adodb->DBTimeStamp($this->_to_seconds($start)).",
                        ".$adodb->DBTimeStamp($this->_to_seconds($stop)).",
                        'offline',
                        '1')";
            sql($sql);
            return sqllast();
        }
    }

    function _to_seconds($msecs) {
        return (int) ($msecs/1000);
    }

    function _process(&$result) {
        if ($this->_personExists($result['mid'])) {
            if ($result['module_id'] == 'undefined')   $result['module_id']   = 0;
            if ($result['schedule_id'] == 'undefined') $result['schedule_id'] = 0;
            $this->_processActions($result['mid'],$result['course_id'],$result['actions'],$result['module_id'],$result['schedule_id']);
        }
    }

    function _processAutoMark($sheid, $mid, $tid) {
        if ($sheid && $mid && $tid) {
            if ($assign = CScheduleAssign::get($sheid, $mid)) {
                if (isset($assign->attributes['toolParams']['formula_id'])) {
                    $formula_id = (int) $assign->attributes['toolParams']['formula_id'];
                    $penalty_id = 0;
                    if (isset($assign->attributes['toolParams']['penaltyFormula_id'])) {
                        $penalty_id = (int) $assign->attributes['toolParams']['penaltyFormula_id'];
                    }
                    if (isset($assign->attributes['toolParams']['tests_testID'])) {
                        if ($assign->attributes['toolParams']['tests_testID'] == $tid) {
                            $loguser = array(); $procent = 0;
                            $sql = "SELECT bal, balmax2, balmin2, stop FROM loguser WHERE mid = '$mid' AND tid = '$tid' ORDER BY stid";
                            $res = sql($sql);

                            while($row = sqlget($res)) {
                                $row['procent'] = 0;
                                $diff = $row['balmax2'] - $row['balmin2'];
                                if ($diff) {
                                    $row['procent'] = round((($row['bal'] - $row['balmin2']) * 100)/ $diff);
                                    if ($row['procent'] >= $procent) {
                                        $procent = $row['procent'];
                                        $loguser = $row;
                                    }
                                }
                            }

                            if (count($loguser)) {
                                if ($formula = getField('formula', 'formula', 'id', $formula_id)) {
                                    $mark = viewFormula($formula, $text, $loguser['balmin2'], $loguser['balmax2'], $loguser['bal']);

                                    if ($penalty_id) {
                                        $days = getPenaltyDays($loguser['stop'], strtotime(getField('schedule', 'end', 'SHEID', $sheid)));
                                        $penaltyFormula = getPenaltyFormula($penalty_id);
                                        $penalty = viewPenaltyFormula($penaltyFormula, $days);
                                        if ($penalty) $mark = round($mark*$penalty,2);
                                    }

                                    sql("UPDATE scheduleID SET V_STATUS = '$mark' WHERE MID = '$mid' AND SHEID = '$sheid'");
                                }
                            }

                        }
                    }
                }
            }
        }
    }

    function process() {
        if (is_array($this->_results) && count($this->_results)) {
            foreach($this->_results as $timestamp => $result) {
                $this->_process($result);
            }
        }

        // set automarks
        if (is_array($this->_automarks) && count($this->_automarks)) {
            require_once ($GLOBALS['wwf'].'/formula_calc.php');
            require_once ($GLOBALS['wwf'].'/lib/classes/CSchedule.class.php');
            foreach($this->_automarks as $sheid => $mids) {
                foreach($mids as $mid => $tid) {
                    $this->_processAutoMark($sheid, $mid, $tid);
                }
            }
        }

        $this->_sheids = array_unique($this->_sheids);
        return $this->_sheids;
    }
}

class CResults {

    function parseXML($filename, &$errorFileNames) {
        $results = array();
        if (file_exists($filename) && is_file($filename)) {
            $xml = file_get_contents($filename);
            if ($xml) {
                $objXML = new xml2Array();
                $arrDOM = $objXML->parse($xml,'Windows-1251');
                unset($objXML);

                if (is_array($arrDOM)) {
                    $parser = new CResultsParser();
                    $parser->init($arrDOM);
                    $results = $parser->parse();
                } else {
                    $parts = pathinfo($filename);
                    $errorFileNames[] = @$parts['basename'];
                }
            }
        }

        return $results;
    }

    function parsePath($path, &$errorFileNames) {
        static $results = array();
        if (!empty($path) && is_dir($path)) {
            if ($dir = opendir($path)) {
                while (($file=readdir($dir)) !== false) {
                    if (($file=='.') || ($file=='..')) continue;
                    if (is_dir($path.$file)) {
                        CResults::parsePath($path.$file.'/',$errorFileNames);
                    }
                    if (is_file($path.$file) && (strtolower(substr($file,-4))) == '.xml') {
                        foreach(CResults::parseXML($path.$file, $errorFileNames) as $result) {
                            if (isset($result['start'])) {
                                $results[$result['start']] = $result;
                            }
                        }
                    }

                }
                closedir($dir);
            }
        }

        if (is_array($results) && count($results)) {
            ksort($results);
        }

        return $results;
    }

    function parseResults($path, &$errorFileNames) {

        $results = CResults::parsePath($path, $errorFileNames);
        CResults::deldir($path);

        $processor = new CResultProcessor();
        $processor->init($results);
        return $processor->process();

    }

    /**
     * Распаковка архива с результатами
     *
     * @param string $filename
     * @param string $dest
     * @return string path to extract
     */
    function unpack($filename, $dest) {
        if (file_exists($filename)) {
            $unique_name = md5($filename.filesize($filename).filemtime($filename));
            if (!empty($unique_name)) {
                $dest .= '/'.$unique_name.'/';
                CResults::mkdirs($dest);
                if (strtolower(substr($filename,-4))=='.zip') {
                    if ($zip = new Archive_Zip($filename)) {
                        if ($zip->extract(array('add_path'=>$dest))) {
                            return $dest;
                        }
                    }
                }
            }
        }
        return;
    }

    /**
     * Рекурсивное создание каталогов
     *
     * @param string $dir
     * @param int $mode
     * @param bool $recursive
     * @return bool
     */
    function mkdirs($dir, $mode = 0777, $recursive = true) {
        if( is_null($dir) || $dir === "" ) {
            return FALSE;
        }
        if( is_dir($dir) || $dir === "/" ) {
            return TRUE;
        }
        if(CResults::mkdirs(dirname($dir), $mode, $recursive)) {
            $oldumask = umask(0);
            $ret = mkdir($dir, $mode);
            umask($oldumask);
            return $ret;
        }
        return FALSE;
    }

    function deldir($dir){
        $d = dir($dir);
        while($entry = $d->read()) {
            if ($entry != "." && $entry != "..") {
                if (is_dir($dir."/".$entry))
                CResults::deldir($dir."/".$entry);
                else
                @unlink ($dir."/".$entry);
            }
        }
        $d->close();
        @rmdir($dir);
    }

}

?>