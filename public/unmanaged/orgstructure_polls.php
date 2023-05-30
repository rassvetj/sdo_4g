<?php
require_once('1.php');
require_once('lib/classes/Position.class.php');
require_once('lib/classes/EventWeight.class.php');
require_once('lib/classes/State.class.php');
require_once('positions.lib.php');

if (!$_SESSION['s']['login']) exitmsg(_("Пожалуйста, авторизуйтесь"),$GLOBALS['sitepath']);

$GLOBALS['controller']->setView('DocumentPopup');
$GLOBALS['controller']->setHeader(_('Аттестация'));
$GLOBALS['controller']->enableNavigation();
$GLOBALS['controller']->view_root->disableBreadCrumbs();

$GLOBALS['controller']->captureFromOb(CONTENT);

$smarty = new Smarty_els();

$items = $soids = $events = false;


// обработка POSTа с обоих шагов
if (isset($_POST['action']) && ($_POST['action'] == 'assign') && ((is_array($_POST['assigned_mids']) && count($_POST['assigned_mids'])) || !empty($_POST['subject_soids']))) {

	require_once('lib/classes/CompetenceRole.class.php');
	require_once('move2.lib.php');
	require_once('lib/classes/Person.class.php');
	require_once('lib/classes/Competence.class.php');
	require_once('lib/classes/Formula.class.php');
	require_once('lib/classes/CCourseAdaptor.class.php');
	require_once('lib/classes/PollCourse.class.php');
	require_once('lib/classes/Poll.class.php');
	require_once('lib/classes/Question.class.php');
	require_once('lib/classes/PollQuestion.class.php');
	require_once('lib/classes/Task.class.php');
	require_once('lib/classes/CSchedule.class.php');
	require_once('lib/classes/Group.class.php');

	if (is_array($_POST['start'])){
		$dateBegin = mktime($_POST['start_time']['Time_Hour'], $_POST['start_time']['Time_Minute'], 0, $_POST['start']['Date_Month'], $_POST['start']['Date_Day'], $_POST['start']['Date_Year']);
		$dateEnd   = mktime($_POST['end_time']['Time_Hour'], $_POST['end_time']['Time_Minute'], 0, $_POST['end']['Date_Month'], $_POST['end']['Date_Day'], $_POST['end']['Date_Year']);
	} else {
		$dateBegin = $_POST['start'];
		$dateEnd   = $_POST['end'];
	}

	if (is_array($_POST['data']['states']['array'])) {
		$states = $_POST['data']['states']['array'];
	} else {
		$states = explode(',', $_POST['states']);
	}

	// Создание курса опросов если такого не существует
	$aAttributes['Title'] = _('Опросы');
	$aAttributes['TypeDes'] = -1; // назначаемый без согласования
	$aAttributes['chain'] = 0;
	$aAttributes['cBegin'] = date('Y-m-d',$dateBegin-24*60*60);
	$aAttributes['cEnd']   = date('Y-m-d',$dateEnd+24*60*60);
	$aAttributes['Status'] = 2;
	$aAttributes['createby'] = $_SESSION['s']['mid'];
	$aAttributes['createdate'] = date('Y-m-d');
	$aAttributes['is_poll'] = 1;

	//$pid = (int) $_POST['pid'];
	$pollName = trim(strip_tags($_POST['poll_name']));
	$pollDesc = trim(strip_tags($_POST['description']));
	$sequence = !empty($_POST['sequence']) ? 1 : 0;

	$events = CEventWeight::get_events();
	if (is_array($events) && count($events)) {
		foreach($events as $k => $event) {
			$tools = explode(',',$event['tools']);
			if (!in_array('tests',$tools)) {
				unset($events[$k]);
			}
		}
	}
	$event = array_shift($events);
	$event = $event['id'];

	if (!empty($_POST['subject_soids'])) {
		$subject_soids = explode(',', $_POST['subject_soids']);
		$sql = "SELECT soid, mid FROM structure_of_organ WHERE soid IN ({$_POST['subject_soids']}) AND mid";
		$res = sql($sql);
		$subject_soids = array();
		while ($row = sqlget($res)){
			$subject_soids[] = $row['soid'];
			$mids_self[$row['soid']] = $row['mid'];
		}
		foreach ($subject_soids as $subject_soid){ // todo: оптимизировать!
			$mids[$subject_soid] = array();
			if (in_array(STATE_POLLS_SELF_FILLED, $_POST['data']['states']['array'])){
				$mids[$subject_soid][] = $mids_self[$subject_soid];
			}
			if (in_array(STATE_POLLS_BOSS_FILLED, $_POST['data']['states']['array'])){
				if ($head = get_head_by_soid($subject_soid)){
					$mids[$subject_soid][] = $head['mid'];
				}
			}
			if (in_array(STATE_POLLS_COLLEG_FILLED, $_POST['data']['states']['array'])){
				if (is_array($colleagues = get_colleagues_by_soid($subject_soid))){
					$mids[$subject_soid] = array_merge(array_keys($colleagues), $mids[$subject_soid]);
				}
			}
			if (in_array(STATE_POLLS_SUBORD_FILLED, $_POST['data']['states']['array'])){
				if (is_array($subordinates = get_subordinates_by_soid($subject_soid))) {
					$mids[$subject_soid] = array_merge(array_keys($subordinates), $mids[$subject_soid]);
				}
			}
		}
	} else {
		$mids = $_POST['assigned_mids'];
	}


	// Если назначение опроса группе
	$groupMids = CGroup::getMidsArray($_POST['group']);

	$pollCourse = new CPollCourse($aAttributes);
	if (($cid = $pollCourse->create())
	&& is_array($mids) && count($mids)
	&& $event) {

		$mids2course = array(); $soids2poll = array(); $mids2poll = array();

		foreach($mids as $soid => $mids2vote) {
			$soids2poll[$soid] = $soid;

			if (is_array($groupMids) && count($groupMids)) {
				foreach($groupMids as $mid2vote) {
					if ($mid2vote>0) {
						$mids2course[$mid2vote] = $mid2vote;
						$mids2poll[$mid2vote][$soid] = $soid;
					}
				}
			}

			if (is_array($mids2vote) && count($mids2vote)) {
				foreach($mids2vote as $mid2vote) {
					if ($mid2vote>0) {
						$mids2course[$mid2vote] = $mid2vote;
						$mids2poll[$mid2vote][$soid] = $soid;
					}
				}
			}
		}

		$msg = _('Ошибка при создании аттестации');
		$createPollError = true;
		// Создание опроса

		$_persons = CPerson::get_persons_by_soids(array_keys($soids2poll)); // кэшируем пиплов, по которым создаются анкеты

		if (is_array($mids2poll) && count($mids2poll)) {
			$poll = new CPoll(array('name'=>$pollName, 'description' => $pollDesc, 'begin'=>date("Y-m-d H:i:s",$dateBegin), 'end' =>date("Y-m-d H:i:s",$dateEnd), 'sequence' => $sequence));
			if ($pid = $poll->create()) {

				array_unshift($states, STATE_POLLS_CREATED);
				if (count($states)) {
					$poll_state = new CStatePolls($pid);
					$poll_state->createStates($states);
				}

				if (count($kods = $poll->createQuestions($cid, $soids2poll))) {
					if (count($tests = $poll->createTasks($cid, $mids2poll, $kods, ''))) {
						CPollCourse::assignPeople($cid, $mids2course, false); // должно выполняться перед saveDependences
						if (count($sheids = $poll->createSchedules($cid, $mids2poll, $tests, $dateBegin, $dateEnd, $event, $poll->attributes['name']))) {
							$poll->saveDependences($pid, $mids2course, $kods, $tests, $sheids);
							$msg = _('Аттестация успешно создана');
							$createPollError = false;
						} else {
							$msg = _('Ошибка при создании занятий аттестации.');
						}
					} else {
						$msg = _('Ошибка при создании заданий аттестации.');
					}
				} else {
					$msg = _('Ошибка при создании вопросов аттестации. Проверьте правильность видов оценки (необходимые критерии, способы оценки)  и назначение видов оценок элементам структуры организации.');
				}
			}
		}

		if ($pid && $createPollError) {
			$poll->deleteResults($pid);
			$poll->delete($pid, true);
		}

	}

	$GLOBALS['controller']->setView('DocumentBlank');
	$GLOBALS['controller']->setMessage($msg, JS_GO_URL, $sitepath.'orgstructure_info.php');
	$GLOBALS['controller']->terminate();
	exit();
}


if (isset($_SESSION['s']['orgstructure']['current']) && $_SESSION['s']['orgstructure']['current']) {
	$_id = $_SESSION['s']['orgstructure']['current'];
}
if (isset($_SESSION['s']['orgstructure']['checked']) && is_array($_SESSION['s']['orgstructure']['checked'])) {
	array_walk($_SESSION['s']['orgstructure']['checked'],'intval');

	$slaves = array();
	//$peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);
	if (!empty($_SESSION['s']['orgstructure']['filters']['specialization'])){
		$filter['specialization'] = $_SESSION['s']['orgstructure']['filters']['specialization'];
		$slaves = CUnitPosition::getSlavesIdAllFiltered($_SESSION['s']['orgstructure']['checked'], $filter);
		$layout = 'plain';
	} else {
		$slaves = CUnitPosition::getSlavesIdAll($_SESSION['s']['orgstructure']['checked'], $filter);
		$slaves = array_merge($slaves, $_SESSION['s']['orgstructure']['checked']);
		$slaves = array_unique($slaves);
		$layout = 'full';
	}

	$items = array();

	// шаг 2 - персонально каждому сотруднику
	if (isset($_POST['action']) && ($_POST['action'] == 'assign-detailed')) {

		$step1['poll_name'] = strip_tags($_POST['poll_name']);
		$step1['description'] = strip_tags($_POST['description']);
		$step1['start'] = mktime(0,0,0, $_POST['start']['Date_Month'], $_POST['start']['Date_Day'], $_POST['start']['Date_Year']);
		$step1['end'] = mktime(0,0,0, $_POST['end']['Date_Month'], $_POST['end']['Date_Day'], $_POST['end']['Date_Year']);
		$step1['states'] = implode(',', $_POST['data']['states']['array']);
		$step1['sequence'] = !empty($_POST['sequence']) ? 1 : 0;
		$smarty->assign('step1', $step1);

		$smarty->assign('allow_self', in_array(STATE_POLLS_SELF_FILLED, $_POST['data']['states']['array']));
		$smarty->assign('allow_boss', in_array(STATE_POLLS_BOSS_FILLED, $_POST['data']['states']['array']));
		$smarty->assign('allow_colleg', in_array(STATE_POLLS_COLLEG_FILLED, $_POST['data']['states']['array']));
		$smarty->assign('allow_subord', in_array(STATE_POLLS_SUBORD_FILLED, $_POST['data']['states']['array']));

		// если не выбрана специализация
		if ($layout == 'full') {
			$sql = "SELECT t1.mid, t1.soid, t1.name, t1.type, t2.soid as owner_soid, t2.name as owner_name
			        FROM structure_of_organ t1
			        LEFT JOIN structure_of_organ t2 ON (t2.soid = t1.owner_soid)
			        WHERE t1.soid IN ('".join("','",$slaves)."')
			        ORDER BY t2.name
			        ";
			$res = sql($sql);

			$positions = $mids = array();
			while($row = sqlget($res)) {

				$positions[] = CPosition::getPosition($row);
				if ($row['mid'] > 0) {
					$mids[$row['mid']] = $row['mid'];
				}

				if ($row['type'] == 2) {
					$positions = array_merge($positions, getSlavesAll(array($row['soid'])));
				}
			}

			if (count($mids)) {
				$sql = "SELECT MID, LastName, FirstName, Patronymic FROM People WHERE MID IN ('".join("','",$mids)."')";
				$res = sql($sql);

				while($row = sqlget($res)) {
					$mids[$row['MID']] = $row['LastName'].' '.$row['FirstName'].' '.$row['Patronymic'];
				}
			}

			if (count($positions)) {
				foreach($positions as $item) {
					if ($item->attributes['mid'] > 0) {
						//$soids[$item->attributes['soid']] = $item;
						$item->attributes['person']       = $mids[$item->attributes['mid']];

						$item->attributes['boss']         = get_head_by_soid($item->attributes['soid']);
						$item->attributes['collegues']    = get_colleagues_by_soid($item->attributes['soid']);
						$item->attributes['subordinates'] = get_subordinates_by_soid($item->attributes['soid']);

						$item->attributes['i'] = $soids[$item->attributes['owner_soid']];
						$items[$item->attributes['owner_soid']][$item->attributes['soid']] = $item;
					}
				}
			}

			$smarty->assign('items', $items);
			echo $smarty->fetch('orgstructure_polls_detailed.tpl');

		}
		// шаг 2, если выбрана специализация
		else {
			if (is_array($slaves) && count($slaves)){
				$slaves = implode(',', $slaves);
				$sql = "
					SELECT
					  `structure_of_organ`.soid,
					  `structure_of_organ`.`mid`,
					  `structure_of_organ`.name,
					  People.LastName,
					  People.FirstName,
					  structure_of_organ1.name
					FROM
					  `structure_of_organ`
					  INNER JOIN People ON (`structure_of_organ`.`mid` = People.`MID`)
					  INNER JOIN `structure_of_organ` structure_of_organ1 ON (`structure_of_organ`.owner_soid = structure_of_organ1.soid)
					WHERE
					  (`structure_of_organ`.soid IN ({$slaves}))
	    		";
				$res = sql($sql);
				while ($row = sqlget($res)){
					$item = new CObject();
					$item->attributes['mid'] = $row['mid'];
					$item->attributes['soid'] = $row['soid'];
					$item->attributes['person'] = "{$row['LastName']} {$row['FirstName']}";

					$item->attributes['boss']         = get_head_by_soid($row['soid']);
					$item->attributes['collegues']    = get_colleagues_by_soid($row['soid']);
					$item->attributes['subordinates'] = get_subordinates_by_soid($row['soid']);

					$item->attributes['i'] = 0;
					$items[0][$row['soid']] = $item;
				}
			}
			$smarty->assign('items', $items);
			echo $smarty->fetch('orgstructure_polls_detailed.tpl');
		}
	}
	// шаг 1 - всем хором
	else {

		$smarty->assign('events', $events);
		$smarty->assign('date_end', time() + 1296000); // 15 дней
		$smarty->assign('okbutton',okbutton());
		$smarty->assign('sitepath',$sitepath);
		$smarty->assign('layout', $layout);

		$aliases = CStatePolls::getPossibleStates();
		$smarty->assign('all_states', $aliases);
		$smarty->assign('states', array());


		$smarty->assign('count', count($slaves));
		$smarty->assign('items', implode(',', $slaves));
		echo $smarty->fetch('orgstructure_polls.tpl');
	}
}

$GLOBALS['controller']->captureStop(CONTENT);
$GLOBALS['controller']->terminate();

function getSlavesAll($soids) {
	$ret = array();
	if (is_array($soids) && count($soids)) {
		foreach($soids as $soid) {
			$sql = "SELECT t1.mid, t1.soid, t1.name, t1.type, t2.soid as owner_soid, t2.name as owner_name
                    FROM structure_of_organ t1
                    LEFT JOIN structure_of_organ t2 ON (t2.soid = t1.owner_soid)
                    WHERE t1.owner_soid = ".(int) $soid.'
                    ORDER BY t2.name';
			$res = sql($sql);

			while($row = sqlget($res)) {
				$ret[] = CPosition::getPosition($row);
				if ($row['type']==2) {
					$ret = array_merge($ret, getSlavesAll(array($row['soid'])));
				}
			}
		}
	}
	return $ret;
}

?>