<?php
class CState {

	var $scope;
	var $scope_id;
	var $states = array();
	var $current_state;
	var $current_state_date;
	var $current_states;
	var $inactive_states = array();
	var $table;
	var $indicator;
	var $conditions = '';
	var $conditions_arr = array();
	var $sequence = true;

	function CState($scope){
		$this->scope = $scope;
		$this->setStates();
	}

	function setStates(){
		if ($this->scope_id) $where = "AND scope_id={$this->scope_id}";
		$sql = "SELECT state, title FROM states WHERE scope='{$this->scope}' {$where} ORDER BY state";
		$res = sql($sql);
		while ($row = sqlget($res)) {
			$this->states[$row['state']] = $row['title'];
		}
	}

	function createStates($states){
		if ($this->scope_id) $where = "AND scope_id={$this->scope_id}";
		if (count($states)){
			$sql = "DELETE FROM states WHERE scope='{$scope}' {$where}";
			$res = sql($sql);
			$titles = $this->getStatesTitles();
			$rows = array();
			foreach ($states as $state) {
				$rows[] = "('{$this->scope}', '{$this->scope_id}', '{$state}', '{$titles[$state]}')";
			}
			if (count($rows)) {
				$rows = implode(', ', $rows);
				$sql = "INSERT INTO states (scope, scope_id, state, title) VALUES {$rows}";
				$res = sql($sql);
			}
		}
	}

	function setConditions($conditions = array()){
		if (is_array($conditions)){
			$this->conditions_arr = $conditions;
			$this->conditions = array('1=1');
			foreach ($conditions as $key => $value) {
				$this->conditions[] = "{$key} = '{$value}'";
			}
			$this->conditions = 'WHERE ' . implode(' AND ', $this->conditions);
		}
	}

	function _setCurrent(){
		$this->current_states = $this->inactive_states = array();
		$sql = "SELECT state, modified, inactive FROM {$this->table} {$this->conditions} ORDER BY state";
		$res = sql($sql);
		while ($row = sqlget($res)) {
			$this->current_states[$row['state']] = date('d.m в H:i', strtotime($row['modified']));
			if ($row['inactive']) $this->inactive_states[] = $row['state'];
		}
		ksort($this->current_states);
		if ($this->sequence){
			if (count($this->current_states)) {
				$current_states_keys = array_keys($this->current_states);
				do {
					$this->current_state = array_pop($current_states_keys);
				} while ($this->current_state && in_array($this->current_state, $this->inactive_states));
				$arr = $this->current_states;
				$this->current_state_date = array_pop($arr);
			}
		} else {
			$this->current_state = 0; // нет текущего шага; все шаги равноправны
		}
	}

	function unsetStates($state){
		$sql = "DELETE FROM {$this->table} {$this->conditions}";
		if ($res = sql($sql)) {
			return true;
		}
		return false;
	}

	function setState($state){
		if (!array_key_exists($state, $this->states)) return false;

		$time = date('Y-m-d H:i');

		if ($this->sequence && !($this->_isNextState($state) || $this->_isCurrentState($state))) {
			return false;
		}

		$sql = "SELECT state FROM {$this->table} {$this->conditions} AND state='{$state}'";
		$res = sql($sql);
		if (sqlrows($res)) {
			$sql = "UPDATE {$this->table} SET state='{$state}', modified='{$time}' {$this->conditions}";
		} else {
			$fields = implode(',',array_keys($this->conditions_arr));
			$values = implode("','",$this->conditions_arr);
			$sql = "INSERT INTO {$this->table} ({$fields}, state, modified) VALUES('{$values}', '{$state}', '{$time}')";
		}
		if (!sql($sql)) {
			return false;
		}

		if ($this->sequence) {
			$state = $this->_getNextState($state);
			while (!$this->_isPossibleState($state)) {
				$fields = implode(',',array_keys($this->conditions_arr));
				$values = implode("','",$this->conditions_arr);
				$sql = "INSERT INTO {$this->table} ({$fields}, state, modified, inactive) VALUES('{$values}', '{$state}', '{$time}', 1)";
				if (!sql($sql)) {
					return false;
				}
				$state = $this->_getNextState($state);
			}
		}
		return true;
	}

	function _isCurrentState($state){
		if (!isset($this->current_states)) {
			$this->_setCurrent();
		}
		return ($state == $this->current_state);
	}

	function _isNextState($state){
		if (!isset($this->current_states)) {
			$this->_setCurrent();
		}
		$rest = array_diff(array_keys($this->states), array_keys($this->current_states));
		sort($rest);
		return ($state == array_shift($rest)) ? true : false;
	}

	function _getNextState($state){
		$this->_setCurrent();
		$rest = array_diff(array_keys($this->states), array_keys($this->current_states));
		sort($rest);
		return (count($rest)) ? array_shift($rest) : false;
	}

	function _isFarNextState($state){
		if (!isset($this->current_states)) {
			$this->_setCurrent();
		}
		$rest = array_diff($this->states, $this->current_states);
		return (in_array($state, array_keys($rest)));
	}

	function getFinalState(){
		ksort($this->states);
		return array_pop(array_keys($this->states));
	}

	function setIndicator(){
		if ($this->sequence) {
			$this->indicator = new IndicatorSequence($this->states);
		} else {
			$this->indicator = new IndicatorNoSequence($this->states, $this->current_states);
		}
	}

	// interface
	function getStatesTitles(){}
}

define('STATE_POLLS_CREATED', 10);
define('STATE_POLLS_SELF_FILLED', 20);
define('STATE_POLLS_COLLEG_FILLED', 30);
define('STATE_POLLS_SUBORD_FILLED', 40);
define('STATE_POLLS_INTERVIEWED', 50);
define('STATE_POLLS_BOSS_FILLED', 60);
define('STATE_POLLS_FINISHED', 70);
define('STATE_POLLS_CANCELED', 999);

class CStatePolls extends CState {

	var $poll_info;

	function CStatePolls($pid = false, $tid = false){
		if ($tid) {
			$this->_setPollInfoByTid($tid);
		} else {
			$this->scope_id = (int)$pid;
		}
		$this->setSequence();
		parent::CState('polls');
		$this->table = 'polls_state';
	}

	function getStatesTitles(){
		return array(
			STATE_POLLS_CREATED => 'аттестация создана',
			STATE_POLLS_SELF_FILLED => 'анкета заполнена сотрудником',
			STATE_POLLS_COLLEG_FILLED => 'анкета заполнена коллегами',
			STATE_POLLS_SUBORD_FILLED => 'анкета заполнена подчиненными',
			STATE_POLLS_INTERVIEWED => 'преведено собеседование',
			STATE_POLLS_BOSS_FILLED => 'анкета заполнена руководителем',
			STATE_POLLS_FINISHED => 'аттестация завершена',
		);
	}

	// static
	function getPossibleStates(){
		return array(
			STATE_POLLS_SELF_FILLED => 'сотрудники заполняют свои анкеты',
			STATE_POLLS_COLLEG_FILLED => 'сотрудники заполняют анкеты по своим коллегам',
			STATE_POLLS_SUBORD_FILLED => 'сотрудники заполняют анкету по своему руководителю',
//			STATE_POLLS_INTERVIEWED => 'руководитель проводит собеседование с сотрудниками',
			STATE_POLLS_BOSS_FILLED => 'руководитель заполняет анкеты по своим сотрудникам',
			STATE_POLLS_FINISHED => 'руководитель подтверждает окончание аттестации',
		);
	}

	function setSequence(){
		if ($this->scope_id) {
			$sql = "SELECT sequence FROM polls WHERE id='{$this->scope_id}'";
			$res = sql($sql);
			if ($row = sqlget($res)) {
				$this->sequence = $row['sequence'];
			}
		}
	}

	function getIndicatorBrief($pid, $mid){
		$this->setConditions(array('mid' => $mid, 'pid' => $pid));
		$this->_setCurrent();
		if (count($this->current_states)){
			$this->setIndicator();
			$this->indicator->setCurrent(array_pop(array_keys($this->current_states)));
			if ($this->sequence) $this->indicator->setInactiveSteps($this->inactive_states);
			$this->indicator->setTemplate('brief');
			return $this->indicator->fetch();
		}
		return '';
	}

	function getIndicatorFull($pid, $mid){
		$this->setConditions(array('mid' => $mid, 'pid' => $pid));
		$this->_setCurrent();

		if (count($this->current_states)){

			$this->setIndicator();
			if ($this->sequence) {
				$comments = $this->_getCommentsSequence();
				$this->indicator->setCurrent($this->current_state);
				$this->indicator->setInactiveSteps($this->inactive_states);
			} else {
				$comments = $this->_getCommentsNoSequence();
			}
			$this->indicator->addComments($comments);
			$this->indicator->setTemplate('full');

			return $this->indicator->fetch();
		}
		return '';
	}

	function _getCommentsSequence(){
		$comments = array();
		if (($mid = $this->conditions_arr['mid']) && ($pid = $this->conditions_arr['pid'])) {
			$comments_prev = array(
				STATE_POLLS_CREATED => "анкета создана {$this->current_states[STATE_POLLS_CREATED]}",
				STATE_POLLS_SELF_FILLED => "заполнена сотрудником {$this->current_states[STATE_POLLS_SELF_FILLED]}<br>
											<a href=\"test_log4at.php?pid={$pid}&mid={$mid}&poll_mid={$mid}\" target=\"_blank\">" . _('просмотреть анкету') . '</a>',
				STATE_POLLS_INTERVIEWED => 'собеседование назначено на 01.08',
				STATE_POLLS_BOSS_FILLED => "заполнена руководителем {$this->current_states[STATE_POLLS_BOSS_FILLED]}<br>
											<a href=\"test_log4at.php?pid={$pid}&mid={$_SESSION['s']['mid']}&poll_mid={$mid}\" target=\"_blank\">" . _('просмотреть анкету') . '</a>',
				STATE_POLLS_FINISHED => "результаты зафиксированы {$this->current_states[STATE_POLLS_FINISHED]}",
			);
			$comments_curr = array(
				STATE_POLLS_CREATED => "<a href=\"polls.php?action=change_state&state=" . STATE_POLLS_CANCELED . "&pid={$pid}&mid={$mid}\" onClick=\"javascript: return confirm('" . _('Вы действительно желаете освободить этого сотрудника от прохождения аттестации?') . "')\">" . _('освободить от аттестации') . '</a>',
				STATE_POLLS_SELF_FILLED => $comments_prev[STATE_POLLS_SELF_FILLED],
				STATE_POLLS_INTERVIEWED => "собеседование проведено на {$this->current_states[STATE_POLLS_INTERVIEWED]}",
				STATE_POLLS_BOSS_FILLED => $comments_prev[STATE_POLLS_BOSS_FILLED],
				STATE_POLLS_FINISHED => $comments_prev[STATE_POLLS_FINISHED],
			);
			$comments_next = array(
				STATE_POLLS_CREATED => '',
				STATE_POLLS_SELF_FILLED => '',
				STATE_POLLS_INTERVIEWED => '<a href="#">' . _('назначить собеседование') . '</a>',
				STATE_POLLS_BOSS_FILLED => ($sheid = $this->getShedule()) ? "<a href=\"schedule.php4?c=go&mode_frames=1&sheid={$sheid}\">" . _('заполнить анкету') . '</a>' : '',
				STATE_POLLS_FINISHED => "<a href=\"polls.php?action=change_state&state=" . STATE_POLLS_FINISHED . "&pid={$pid}&mid={$mid}\" onClick=\"javascript: return confirm('" . _('Вы действительно желаете зафиксировать результаты сотрудника в рамках данной аттестации? Дальнейшее изменение данных будет невозможно.') . "')\">" . _('зафиксировать результаты аттестации') . '</a>',
			);

			foreach ($this->states as $state => $title) {
				if ($state == $this->current_state) {
					$comments[$state] = $comments_curr[$state];
				} elseif ($state < $this->current_state) {
					$comments[$state] = $comments_prev[$state];
				} elseif ($this->_isNextState($state)) {
					$comments[$state] = $comments_next[$state];
				} else {
					$comments[$state] = '';
				}
			}
		}
		return $comments;
	}

	function _getCommentsNoSequence(){
		$comments = array();
		if (($mid = $this->conditions_arr['mid']) && ($pid = $this->conditions_arr['pid'])) {
			$my_potential_state = $this->getState(false, $mid);
			$comments_prev = array(
				STATE_POLLS_CREATED => "<a href=\"polls.php?action=change_state&state=" . STATE_POLLS_CANCELED . "&pid={$pid}&mid={$mid}\" onClick=\"javascript: return confirm('" . _('Вы действительно желаете освободить этого сотрудника от прохождения аттестации?') . "')\">" . _('освободить от аттестации') . '</a>',
				STATE_POLLS_SELF_FILLED => "заполнена сотрудником {$this->current_states[STATE_POLLS_SELF_FILLED]}<br>
											<a href=\"test_log4at.php?pid={$pid}&mid={$mid}&poll_mid={$mid}\" target=\"_blank\">" . _('просмотреть анкету') . '</a>',
				STATE_POLLS_INTERVIEWED => 'собеседование назначено на 01.08',
				STATE_POLLS_BOSS_FILLED => "заполнена руководителем {$this->current_states[STATE_POLLS_BOSS_FILLED]}<br>
											<a href=\"test_log4at.php?pid={$pid}&mid={$_SESSION['s']['mid']}&poll_mid={$mid}\" target=\"_blank\">" . _('просмотреть анкету') . '</a>',
				STATE_POLLS_FINISHED => "результаты зафиксированы {$this->current_states[STATE_POLLS_FINISHED]}",
			);
			$sheid = $this->getShedule();
			$comments_next = array(
				STATE_POLLS_CREATED => "<a href=\"polls.php?action=change_state&state=" . STATE_POLLS_CANCELED . "&pid={$pid}&mid={$mid}\" onClick=\"javascript: return confirm('" . _('Вы действительно желаете освободить этого сотрудника от прохождения аттестации?') . "')\">" . _('освободить от аттестации') . '</a>',
				STATE_POLLS_SELF_FILLED => ($sheid && ($my_potential_state == STATE_POLLS_SELF_FILLED)) ? "<a href=\"schedule.php4?c=go&mode_frames=1&sheid={$sheid}\">" . _('заполнить анкету') . '</a>' : '',
				STATE_POLLS_INTERVIEWED => '<a href="#">' . _('назначить собеседование') . '</a>',
				STATE_POLLS_BOSS_FILLED => ($sheid && ($my_potential_state == STATE_POLLS_BOSS_FILLED)) ? "<a href=\"schedule.php4?c=go&mode_frames=1&sheid={$sheid}\">" . _('заполнить анкету') . '</a>' : '',
				STATE_POLLS_FINISHED => "<a href=\"polls.php?action=change_state&state=" . STATE_POLLS_FINISHED . "&pid={$pid}&mid={$mid}\" onClick=\"javascript: return confirm('" . _('Вы действительно желаете зафиксировать результаты сотрудника в рамках данной аттестации? Дальнейшее изменение данных будет невозможно.') . "')\">" . _('зафиксировать результаты аттестации') . '</a>',
			);
			foreach ($this->states as $state => $title) {
				if (in_array($state, array_keys($this->current_states))) {
					$comments[$state] = $comments_prev[$state];
				} else {
					$comments[$state] = $comments_next[$state];
				}
			}
		}
		return $comments;
	}

	function _setPollInfoByMid($mid){
	    $this->poll_info['subject']['mid'] = $mid; // чья это анкета
	    if ($this->poll_info['subject']['soid'] = get_soid_by_mid($this->poll_info['subject']['mid'])) {
		    $this->poll_info['subject']['head'] = get_head_by_soid($this->poll_info['subject']['soid']);
		    $this->poll_info['subject']['colleagues'] = get_colleagues_by_soid($this->poll_info['subject']['soid']);
		    $this->poll_info['subject']['subordinates'] = get_subordinates_by_soid($this->poll_info['subject']['soid']);
	    }
	    $this->poll_info['respondent']['mid'] = $_SESSION['s']['mid'];
	    if ($this->poll_info['respondent']['soid'] = get_soid_by_mid($this->poll_info['respondent']['mid'])) {
		    $this->poll_info['respondent']['subordinates'] = get_subordinates_by_soid($this->poll_info['respondent']['soid']);
	    }
	}

	function _setPollInfoByTid($tid){
	    $sql = "SELECT poll_mid FROM test WHERE tid='{$tid}'";
	    $res = sql($sql);
	    if ($row = sqlget($res)) {
		    $this->poll_info['subject']['mid'] = $row['poll_mid']; // чья это анкета
		    if ($this->poll_info['subject']['soid'] = get_soid_by_mid($this->poll_info['subject']['mid'])) {
			    $this->poll_info['subject']['head'] = get_head_by_soid($this->poll_info['subject']['soid']);
			    $this->poll_info['subject']['colleagues'] = get_colleagues_by_soid($this->poll_info['subject']['soid']);
			    $this->poll_info['subject']['subordinates'] = get_subordinates_by_soid($this->poll_info['subject']['soid']);
		    }
	    }
	    $this->poll_info['respondent']['mid'] = $_SESSION['s']['mid'];
	    if ($this->poll_info['respondent']['soid'] = get_soid_by_mid($this->poll_info['respondent']['mid'])) {
		    $this->poll_info['respondent']['subordinates'] = get_subordinates_by_soid($this->poll_info['respondent']['soid']);
	    }

		$tid2poll = array();
        $sql = "SELECT id, `name`, `data` FROM polls";
        $res = sql($sql);
		while ($row = sqlget($res)) {
			$data = unserialize($row['data']);
			if (is_array($data['tests'])){
				foreach ($data['tests'] as $test_id) {
					$tid2poll[$test_id] = $row;
				}
			}
		}

    	if (isset($tid2poll[$tid])) {
    		$this->poll_info['pid'] = $tid2poll[$tid]['id'];
    		$this->scope_id = $this->poll_info['pid'];
    	}
	}

	function getCurrentState(){
		if (!isset($this->current_states)) {
			$this->_setCurrent();
		}
		$titles = CStatePolls::getStatesTitles();
		return array('state' => $this->current_state, 'title' => $titles[$this->current_state], 'modified' => $this->current_state_date);
	}

	function getState($tid = false, $mid = false){ // кто я по отношению к этому челу
		if (!isset($this->poll_info)){
			if ($tid) {
				$this->_setPollInfoByTid($tid);
			} elseif ($mid) {
				$this->_setPollInfoByMid($mid);
			}
		}
		if ($_SESSION['s']['mid'] == $this->poll_info['subject']['mid']) return STATE_POLLS_SELF_FILLED;
		elseif ($_SESSION['s']['mid'] == $this->poll_info['subject']['head']['mid']) return STATE_POLLS_BOSS_FILLED;
		elseif (in_array($_SESSION['s']['mid'], array_keys($this->poll_info['subject']['colleagues']))) return STATE_POLLS_COLLEG_FILLED; // todo: один коллега заполнил - это еще не значит что можно менять статус
		elseif (in_array($_SESSION['s']['mid'], array_keys($this->poll_info['subject']['subordinates']))) return STATE_POLLS_SUBORD_FILLED;
		return false;
	}

	function getShedule(){
		if ($this->conditions_arr['pid'] && $this->conditions_arr['mid']){
	        $sql = "SELECT id, `name`, `data` FROM polls WHERE id='{$this->conditions_arr['pid']}'";
	        $res = sql($sql);
			if ($row = sqlget($res)) {
				$data = unserialize($row['data']);
				if (count($tids = $data['tests'])) {
					$tids = implode(',', $tids);
					$sheids = implode(',', $data['schedules']);
					$sql = "SELECT tid FROM test WHERE poll_mid='{$this->conditions_arr['mid']}' AND tid IN ({$tids})";
			        $res = sql($sql);
					if ($row = sqlget($res)) {
						$tid = $row['tid'];
						$sql = "SELECT SHEID FROM scheduleID WHERE toolParams LIKE '%tests_testId={$tid}%' AND SHEID IN ({$sheids}) AND MID='{$_SESSION['s']['mid']}'";
				        $res = sql($sql);
						if ($row = sqlget($res)) {
							return $row['SHEID'];
						}
					}
				}
			}
		}
		return false;
	}

	function _isPossibleState($state){
		if (!isset($this->poll_info)) {
			if ($mid = $this->conditions_arr['mid']) {
				$this->_setPollInfoByMid($mid);
			}
		}
		switch ($state) {
			case false:
			case STATE_POLLS_SELF_FILLED:
			case STATE_POLLS_BOSS_FILLED:
			case STATE_POLLS_INTERVIEWED:
			case STATE_POLLS_FINISHED:
				return true;
				break;
			case STATE_POLLS_COLLEG_FILLED:
				return (is_array($this->poll_info['subject']['colleagues']) && count($this->poll_info['subject']['colleagues']));
			case STATE_POLLS_SUBORD_FILLED:
				return (is_array($this->poll_info['subject']['subordinates']) && count($this->poll_info['subject']['subordinates']));
		}
	}
}

?>