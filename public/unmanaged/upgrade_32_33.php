<?
require_once("1.php");

$errors = array();

check_course_structure();
update_course_structure();
//add_modules_no_structure();
update_schedules();

sql("UPDATE tracks SET number_of_levels = 10");

sql("INSERT INTO `Courses` (`Title`, `Description`, `TypeDes`, `CD`, `cBegin`, `cEnd`, `Fee`, `valuta`, `Status`, `createby`, `createdate`, `longtime`, `did`, `type`) VALUES ('Пример ресурса','block=simple~name=description%END%type=fckeditor%END%title=%END%value=%END%sub=%END%~[~~]',0,'','2007-11-01','2008-11-01',0,0,'2','elearn@hypermethod.com','2007-11-01',120,0,1)");
sql("INSERT INTO `methodologist` (`mid`, `cid`) VALUES ({$_SESSION['s']['mid']}, 0)");

sql("INSERT INTO `Courses` (`Title`, `Description`, `TypeDes`, `CD`, `cBegin`, `cEnd`, `Fee`, `valuta`, `Status`, `createby`, `createdate`, `longtime`, `is_poll`) VALUES ('Аттестация','block=simple~name=description%END%type=fckeditor%END%title=%END%value=Пример описания курса%END%sub=%END%~[~~]',-1,'','2007-11-01','2008-11-01',0,0,'2','elearn@hypermethod.com','2006-11-01',120,1)");
sql("INSERT INTO `managers` (`id`, `mid`) VALUES (1, {$_SESSION['s']['mid']})");
sql("INSERT INTO `structure_of_organ` (`soid`, `soid_external`, `name`, `code`, `mid`, `info`, `owner_soid`, `agreem`, `type`, `own_results`, `enemy_results`, `display_results`, `threshold`) VALUES (1, '0', 'Организация', NULL, 0, '', 0, 0, 2, 1, 1, 0, 0)");

$msg = '';
if (count($errors)) {
	$msg = "<ul>Невозможно преобразовать следующие курсы:";
	foreach ($errors as $error) {
		$msg .= "<li>{$error}</li>";
	}
	$msg .= "</ul>";
}

$controller->setMessage("Преобразование выполнено успешно.{$msg}");
$controller->terminate();
exit();

function check_course_structure() {
	global $errors;
	$res = sql("SELECT CID, Title FROM Courses");
	while ($row = sqlget($res)) {
		$res1 = sql("SELECT oid, level FROM organizations WHERE prev_ref = '-1' AND CID = '{$row['CID']}'");
		if (sqlrows($res1) > 1) {
			$errors[$row['CID']] = $row['Title']; //_('неверно определен корневой элемент курса');
		} else {
			if ($row1 = sqlget($res1)) {
				if ($row1['level'] == 1) {
					sql("UPDATE organizations SET `level` = `level` - 1 WHERE CID = '{$row['CID']}'");
				}
			}
		}
	}
	return $errors;
}

function update_course_structure() {

	global $errors;
	$condition = '';
	if (count($errors)) {
		$cids = array_keys($errors);
		$condition = " WHERE CID NOT IN (" . implode(',', $cids) . ")";
	}

	$res = sql("SELECT CID FROM Courses {$condition}");
	while ($row = sqlget($res)) {
		$oids = _get_oids($row['CID']);
		for ($i=0; $i < count($oids); $i++) {
			$modules = $tests = $runs = array();
			$next_oid = $update = 0;

			$res2 = sql("SELECT test_id, run_id FROM mod_list WHERE ModID = '{$oids[$i]['mod_ref']}'");
			if ($row2 = sqlget($res2)) {
				if (!empty($row2['test_id'])){
					$tids = str_replace(';', ',', $row2['test_id']);
					$res3 = sql("SELECT tid, title FROM test WHERE tid IN ({$tids})");
					while ($row3 = sqlget($res3)) {
						$tests[$row3['tid']] = trim(str_replace('Материал:', '', $row3['title']));
					}
				}
				if (!empty($row2['run_id'])){
					$run_ids = str_replace(';', ',', $row2['run_id']);
					$res3 = sql("SELECT run_id, name FROM training_run WHERE run_id IN ({$run_ids})");
					while ($row3 = sqlget($res3)) {
						$runs[$row3['run_id']] = $row3['name'];
					}
				}
				$res3 = sql("SELECT Title, mod_l FROM mod_content WHERE ModID = '{$oids[$i]['mod_ref']}'");
				while ($row3 = sqlget($res3)) {
					switch (strpos($row3['mod_l'], 'COURSES/')) {
						case 0:
							$row3['mod_l'] = "/../" . $row3['mod_l'];
							break;
						case 1:
							$row3['mod_l'] = "/.." . $row3['mod_l'];
							break;
					}
					$modules[$row3['mod_l']] = $row3['Title'];
				}

				if ((count($modules) + count($tests) + count($runs)) == 1) {
					$update = true;
				}

				$prev_ref = $oids[$i]['oid'];
				$level = $oids[$i]['level'] + 1;

				foreach ($modules as $url => $title){
					$res3 = sql("INSERT INTO library (filename, mid, title, cid, is_active_version) VALUES ('{$url}', '{$_SESSION['s']['mid']}', ".$GLOBALS['adodb']->Quote($title).", '{$row['CID']}', '1')");
					$bid = sqllast();
					if ($update) {
						$res3 = sql("UPDATE organizations SET module = '{$bid}' WHERE oid = '{$oids[$i]['oid']}'");
					} else {
						$res3 = sql("INSERT INTO organizations (title, cid, level, prev_ref, vol1, vol2, module) VALUES ('{$title}', '{$row['CID']}', '{$level}', '{$prev_ref}', '0', '0', '{$bid}')");
						$prev_ref = sqllast();
					}
				}
				foreach ($tests as $tid => $title){
					if ($update) {
						$res3 = sql("UPDATE organizations SET vol1 = '{$tid}' WHERE oid = '{$oids[$i]['oid']}'");
					} else {
						$res3 = sql("INSERT INTO organizations (title, cid, level, prev_ref, vol1, vol2, module) VALUES ('{$title}', '{$row['CID']}', '{$level}', '{$prev_ref}', '{$tid}', '0', '0')");
						$prev_ref = sqllast();
					}
				}
				foreach ($runs as $run_id => $title){
					if ($update) {
						$res3 = sql("UPDATE organizations SET vol2 = '{$run_id}' WHERE oid = '{$oids[$i]['oid']}'");
					} else {
						$res3 = sql("INSERT INTO organizations (title, cid, level, prev_ref, vol1, vol2, module) VALUES ('{$title}', '{$row['CID']}', '{$level}', '{$prev_ref}', '0', '{$run_id}', '0')");
						$prev_ref = sqllast();
					}
				}
				if (!$update && (count($modules) + count($tests) + count($runs)) && isset($oids[$i + 1])) {
					sql("UPDATE organizations SET prev_ref = '{$prev_ref}' WHERE oid = '{$oids[$i + 1]['oid']}'");
				}
			}
		}
	}
	sql("UPDATE Courses SET tree = ''");
}

function update_schedules(){
	global $errors;
	$toolParams = $toolParams_patched = $oids = array();
	$condition = '1=1';
	if (count($errors)) {
		$cids = array_keys($errors);
		$condition = "CID NOT IN (" . implode(',', $cids) . ")";
	}
	$res = sql("SELECT oid, mod_ref FROM organizations WHERE mod_ref AND {$condition}");
	while ($row = sqlget($res)) {
		$oids[$row['oid']] = $row['mod_ref'];
	}
	$res = sql("SELECT SSID, toolParams FROM scheduleID INNER JOIN `schedule` ON (`schedule`.SHEID = `scheduleID`.SHEID) WHERE toolParams LIKE '%module_moduleID={$row['mod_ref']}%' AND {$condition}");
	while ($row = sqlget($res)) {
		$toolParams[$row['SSID']] = $row['toolParams'];
	}
	foreach ($toolParams as $ssid => $toolParam) {
		foreach ($oids as $oid => $mod_id) {
			if (strpos($toolParam, "module_moduleID={$mod_id};") !== false) {
				$toolParams_patched[$ssid] = str_replace("module_moduleID={$mod_id};", "module_moduleID={$oid};", $toolParam);
			}
		}
	}
	foreach ($toolParams_patched as $ssid => $toolParam) {
		sql("UPDATE scheduleID SET toolParams = '{$toolParam}' WHERE SSID = '{$ssid}'");
	}
}

function add_modules_no_structure(){
	global $errors;
	$condition = '';
	if (count($errors)) {
		$cids = array_keys($errors);
		$condition = " WHERE CID NOT IN (" . implode(',', $cids) . ")";
	}

	$res = sql("SELECT CID FROM Courses {$condition}");
	while ($row = sqlget($res)) {
		$sql1 = "
		SELECT
		  `mod_list`.ModID,
		  `mod_list`.Title
		FROM
		  `mod_list`
		  LEFT OUTER JOIN `organizations` ON (`mod_list`.ModID = `organizations`.prev_ref)
		WHERE
		  (`organizations`.oid IS NULL)	AND CID={$row['CID']}
		";
		$res1 = sql($sql1);
		if (sqlrows($res1)){
			$oids = _get_oids($row['CID']);
			$last = array_pop($oids);
			sql("INSERT INTO organizations (title, cid, prev_ref, level) VALUES ('" . _('Модули вне структуры курса в 3.2') ."', '{$row['CID']}', '{$last}', '0')");
			while ($row1 = sqlget($res1)){

				// доделать..

				sql("INSERT INTO organizations (title, cid, prev_ref, level) VALUES ('" . _('Модули вне структуры курса в 3.2') ."', '{$row['CID']}', '{$last}', '0')");
			}
		}
	}
}
function _get_oids($cid){
	$base_prev_ref = '-1';
	$oids = array();
	do {
		$res1 = sql("SELECT oid, mod_ref, level, Title FROM organizations WHERE CID = '{$cid}' AND prev_ref = {$base_prev_ref}");
		if ($row1 = sqlget($res1)) {
			$oids[] = $row1;
			$base_prev_ref = $row1['oid'];
		} else {
			$base_prev_ref = false; // последний элемент в дереве
		}
	} while($base_prev_ref);
	return $oids;
}
?>