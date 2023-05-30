<?php
class CPoll extends CDBObject {
    
    var $table = 'polls';
            
    function get($id) {
        return parent::get(array('name'=>'id','value'=>$id),'polls', 'CPoll');
    }    
    
    function deleteResults($pid, $mid=0) {
        if ($pid) {
            $where = '';
            if ($mid>0) $where = " AND mid='".(int) $mid."' ";
            $sql = "SELECT DISTINCT kod FROM polls_people WHERE poll = '".(int) $pid."' $where";
            $res = sql($sql);
            
            while($row = sqlget($res)) {
                $kods[] = $row['kod'];
            }
            
            if (is_array($kods) && count($kods)) {
                if ($mid>0) {
                    $sql = "DELETE FROM logseance WHERE kod IN ('".join("','",$kods)."')";
                    sql($sql);
                } else {
                    $sql = "SELECT DISTINCT stid FROM logseance WHERE kod IN ('".join("','",$kods)."')";
                    $res = sql($sql);
                    
                    while($row = sqlget($res)) {
                        $stids[] = $row['stid'];
                    }
                    
                    if (is_array($stids) && count($stids)) {
                        sql("DELETE FROM loguser WHERE stid IN ('".join("','",$stids)."')");
                        sql("DELETE FROM logseance WHERE stid IN ('".join("','",$stids)."')");
                    }
                }
            }
            
        }
    }

    function update($id) {
        if ($id) {
            if ($poll = CPoll::get($id)) {
                if (!empty($poll->attributes['data'])) {
                    $data = unserialize($poll->attributes['data']);
                    if (is_array($data['schedules']) && count($data['schedules'])) {
                        $sql = "UPDATE schedule
                                SET 
                                    begin = ".$GLOBALS['adodb']->Quote($this->attributes['begin']).", 
                                    end = ".$GLOBALS['adodb']->Quote($this->attributes['end'])." 
                                WHERE SHEID IN ('".join("','",$data['schedules'])."')";
                        sql($sql);

                        // Корректировка дат у курса
                        $pollCourse = new CPollCourse(
                            array(
                                'cBegin' => date('Y-m-d',($this->unixDate($this->attributes['begin']))-24*60*60),
                                'cEnd'   => date('Y-m-d',($this->unixDate($this->attributes['end']))+24*60*60),
                            ));
                        $pollCourse->create();
                    }
                }
            }
        }
        return parent::update(array('name'=>'id','value'=>$id));
    }    
    
    function delete($pid, $delete = false) {
        
        $sql = "SELECT DISTINCT kod FROM polls_people WHERE poll = '".(int) $pid."'";
        $res = sql($sql);
        
        while($row = sqlget($res)) {
            $kods[] = $row['kod'];            
        }
        
        if (is_array($kods) && count($kods)) {
            sql("DELETE FROM list WHERE kod IN ('".join("','",$kods)."')");
        }
        
        //sql("DELETE FROM polls_people WHERE poll = '".(int) $pid."'");
        
        $sql = "SELECT `data` FROM polls WHERE id='".(int) $pid."'";
        $res = sql($sql);
        
        while($row = sqlget($res)) {
            if (!empty($row['data'])) {
                $data = unserialize($row['data']);
                if(is_array($data['tests']) && count($data['tests'])) {
                    sql("DELETE FROM test WHERE tid IN ('".join("','",$data['tests'])."')");
                }
                if(is_array($data['schedules']) && count($data['schedules'])) {
                    sql("DELETE FROM scheduleID WHERE SHEID IN ('".join("','",$data['schedules'])."')");
                    sql("DELETE FROM schedule WHERE SHEID IN ('".join("','",$data['schedules'])."')");
                }
            }
            
            // удаление людей если у них больше нет занятий на курсе опросов
        }
        
        $this->attributes = array();
        $this->attributes['deleted'] = '1';
        parent::update(array('name'=>'id','value'=>$pid));

        if ($delete) {
            sql("DELETE FROM polls_people WHERE poll = '".(int) $pid."'");
            sql("DELETE FROM polls WHERE id = '".(int) $pid."'");
        }
        //parent::delete(array('name' => 'id', 'value' => $pid));
    }        
    
    function _saveKods($pid, $kods) {
        if (is_array($kods) && count($kods)) {
            $soids2mids = CPosition::getMidsBySoids(array_keys($kods));
            foreach($kods as $soid=>$roles) {
                $mid = $soids2mids[$soid];
                if (is_array($roles) && count($roles) && $mid) {
                    foreach($roles as $role=>$kod) {
                        $sql = "INSERT INTO polls_people (poll, mid, soid, role, kod) VALUES ('$pid','$mid','$soid','$role','$kod')";
                        sql($sql);
                    }
                }
            }
        }        
    }
    
    function saveDependences($pid, $mids, $kods, $tests, $schedules) {
        if ($pid) {
            $data = array();
            CPoll::_saveKods($pid, $kods);
            
            /// ???
            $data['tests']     = $tests;
            $data['schedules'] = $schedules;
            $data['mids']      = $mids;
            
            $sql = "UPDATE polls SET data = '".serialize($data)."' WHERE id = '".(int) $pid."'";
            sql($sql);
        }        
    }
        
    function createSchedule($mid, $cid, $test, $begin, $end, $type, $title='') {
        if ($mid && $cid && $test) {
            $person = CPerson::get($mid,'LF');
            $schedule = new CSchedule(
                array(
                    'title'    => sprintf(_("Опрос для %s"),$person->getNameLF()).$title,
                    'descript' => '',
                    'begin'    => date("Y-m-d H:i:s", $begin),
                    'end'      => date("Y-m-d H:i:s", $end),
                    'createID' => $_SESSION['s']['mid'],
                    'typeID'   => $type,
                    'CID'      => $cid,
                    'timetype' => '0',
                    'CHID'	   => '0'
                ));
            if ($sheid = $schedule->create()) {
                $scheduleAssign = new CScheduleAssign(
                    array(
                        'SHEID'         => $sheid,
                        'MID'           => $mid,
                        'toolParams'    => 'tests_testID='.$test.';',                        
                    ));
                if ($scheduleAssign->create()) {                    
                    return $sheid;
                }
            }
        }
    }
    
    function createSchedules($cid, $mids, $tests, $begin, $end, $type, $title='') {
        $ret = array();
        if ($cid
            && is_array($mids) && count($mids)
            && is_array($tests) && count($tests)) {
                foreach($mids as $mid=>$soids) {
                    if ($tests[$mid]) {
                        $ret[$mid] = CPoll::createSchedule($mid, $cid, $tests[$mid], $begin, $end, $type, $title);
                    }
                }
            }
        return $ret;
    }
    
    function createTask($mid, $cid, $kods, $title='') {
        if ($mid && $cid && count($kods)) {
            
            if ($person = CPerson::get($mid,'LF')) {
                $task = new CTask(
                    array(
                        'cid'        => $cid,
                        'cidowner'   => $cid,
                        'title'      => sprintf(_("Опрос для %s"),$person->getNameLF()).$title,
                        'datatype'   => '1',
                        'data'       => join("~\x03~",$kods),
                        'status'     => '1',
                        'endres'     => '0',
                        'last'       => time(),
                        'created_by' => $_SESSION['s']['mid'],
                        'startlimit' => '0',
                        'mode'       => '1',
                    ));
                return $task->create();
            }
        }
    }
        
    function createTasks($cid, $mids2poll, $soid2kods, $title='') {
        $ret = array();
        if ($cid && is_array($mids2poll) && count($mids2poll)
        && is_array($soid2kods) && count($soid2kods)) {
            foreach($mids2poll as $mid => $soids) {
                if (is_array($soids) && count($soids)) {
                    $kods = array();
                    foreach($soids as $soid) {
                        if (is_array($soid2kods) && count($soid2kods)) {
                            $kods = array_merge($kods, array_values($soid2kods[$soid]));
                        }
                    }
                    $ret[$mid] = CPoll::createTask($mid, $cid, $kods, $title);
                }
            }
        }
        return $ret;
    }
    
    function createQuestion($cid, $soid) {
        $soidPollQuestion = new CSoidPollQuestion(array('cid'=>$cid));
        return $soidPollQuestion->create($soid);
    }
    
    function createQuestions($cid, $soids) {
        $ret = array();
        if (is_array($soids) && count($soids) && $cid) {
            foreach($soids as $soid) {                
                $ret[$soid] = CPoll::createQuestion($cid, $soid);
            }
        }
        return $ret;
    }
    
    function getSoids($pid) {
        $ret = array();
        if ($pid) {
            $sql = "SELECT DISTINCT soid FROM polls_people WHERE poll='".(int) $pid."'";
            $res = sql($sql);
            
            while($row = sqlget($res)) {
                $ret[$row['soid']] = $row['soid'];
            }
        }
        return $ret;
    }

    function getMids($pid) {
        $ret = array();
        if ($pid) {
            $sql = "SELECT `data` FROM polls WHERE poll='".(int) $pid."'";
            $res = sql($sql);
            
            while($row = sqlget($res)) {
                $array = unserialize($row['data']);
                $ret = $array['mids'];
            }
        }
        return $ret;
    }
    
}

class CPollResult {
    var $pid;
    var $mid;
    var $soid;
    var $role;
    var $result = array();
    
    function CPollResult($pid, $mid, $soid, $role) {
        $this->pid             = (int) $pid;
        $this->mid             = (int) $mid;
        $this->soid            = (int) $soid;
        $this->role            = (int) $role;
        $this->result['min']   = 0;
        $this->result['max']   = 0;
        $this->result['sum']   = 0;
        $this->result['count'] = 0;
        $this->result['avg']   = 0;        
    }
    
    function process($kod) {
        if ($kod) {
            $sql = "SELECT * FROM logseance WHERE kod = ".$GLOBALS['adodb']->Quote($kod)." ORDER BY stid";
            $res = sql($sql);
            while($row = sqlget($res)) {                
                $info = unserialize($row['otvet']);
                $this->result['min'] = @min($info['weights']);
                $this->result['max'] = @max($info['weights']);
                if (is_array($info['variant1']) && count($info['variant1'])) {
                    foreach($info['variant1'] as $k=>$variant) {
                        $rows[$row['mid']]['info'][$variant] = $info['weights'][$info['otv'][$k]+1];
                    }
                }
                $rows[$row['mid']]['text'] = $row['text'];
                $rows[$row['mid']]['bal'] = $row['bal'];
            }
            if (is_array($rows) && count($rows)) {
                foreach($rows as $row) {
                    $this->result['texts'][] = $row['text'];
                    $this->result['sum'] += $row['bal'];
                    $this->result['count']++;
                    if (is_array($row['info']) && count($row['info'])) {
                        foreach($row['info'] as $variant => $value) {
                            if (!isset($this->result['info'][$variant]['sum'])) {
                                $this->result['info'][$variant]['sum'] = 0;
                                $this->result['info'][$variant]['count'] = 0;
                            }
                            $this->result['info'][$variant]['sum'] += $value;
                            $this->result['info'][$variant]['count']++;
                        }
                    }
                }
            }
        }
        
        if ($this->result['count']) {
            $this->result['avg'] = $this->result['sum'] / $this->result['count'];
        }
        if (is_array($this->result['info']) && count($this->result['info'])) {
            foreach($this->result['info'] as $k=>$v) {
                if ($v['count']) {
                    $this->result['info'][$k]['avg'] = $v['sum'] / $v['count'];
                }
            }
        }
        return $this->result;
    }
}

class CPollResults {
    var $pid;
    var $results = array();

    function CPollResults($pid) {
        $this->pid = (int) $pid;
    }
    
    function process($mids=false) {
        if ($this->pid) {
            $where = '';
            if (is_array($mids) && count($mids)) {
                $where = " AND mid IN ('".join("','",$mids)."') ";
            }
            $sql = "SELECT * FROM polls_people WHERE poll='".$this->pid."' $where";
            $res = sql($sql);
            
            while($row = sqlget($res)) {
                $result = new CPollResult($this->pid, $row['mid'], $row['soid'], $row['role']);
                $result->process($row['kod']);
                $this->results[] = $result;                
            }
            
        }
        return $this->results;
    }
}

class CPolls {
    function get() {
        $ret = false;
        $sql = "SELECT * FROM polls ORDER BY name";
        $res = sql($sql);
        
        while($row = sqlget($res)) {
            $ret[] = new CPoll($row);
        }
        return $ret;
    }
    
    function getResults($mid, $last = false) {
        $ret = false;
        if ($mid) {
            if ($polls = CPolls::getByMid($mid)) {
                foreach($polls as $k=>$poll) {
                    $results = new CPollResults($poll->attributes['id']);
                    $poll->attributes['results'] = $results->process(array($mid));
                    $ret[] = $poll;
                    if ($last) break;
                }
            }
        }
        return $ret;
    }
    
    function getByMid($mid) {
        $ret = false;
        if ($mid) {
            $sql = "SELECT polls.*
                    FROM polls_people 
                    INNER JOIN polls ON (polls.id = polls_people.poll)
                    WHERE polls_people.mid = '".(int) $mid."'
                    ORDER BY polls.begin DESC";
            $res = sql($sql);
            
            while($row = sqlget($res)) {
                $rows[$row['id']] = $row;
            }
            if (is_array($rows) && count($rows)) {
                foreach($rows as $row)
                $ret[] = new CPoll($row);
            }
        }
        return $ret;
    }
}
?>