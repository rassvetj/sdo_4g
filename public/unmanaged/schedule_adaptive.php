<?
require_once("1.php");
require_once('lib/classes/xml2array.class.php');
require_once('lib/classes/TimelineXMLParser.class.php');
require_once("schedule.lib.php");

define('SCHEDULE_TYPE_ABSOLUTE', 'absolute');
define('SCHEDULE_TYPE_RELATIVE', 'relative');

//ob_start();
if (isset($_POST['xml']) && !empty($_POST['xml'])) {
    $timeline_parser = new CTimelineXMLParser();
    $timeline_parser->init_string($_POST['xml']);
    $timeline_parser->parse();
    $timeline_parser->update_shedules();
}
if (isset($_GET['msg'])){
    $controller->setMessage(_("Параметры занятий сохранены"));
}
/*
if ($fp = fopen('timeline.log','w+')) {
    pr($_POST);
    $content = ob_get_contents();
    fwrite($fp,$content);
    fclose($fp);
}
ob_clean();
*/

if (!$s[login]) {
	exitmsg(_("Пожалуйста, авторизуйтесь"), "/?$sess");
}
if ($s[perm] < 2) {
	exitmsg(_("К этой странице могут обратится только: преподаватель, представитель учебной администрации, администратор"), "/?$sess");
}

$CID = isset($_GET['CID']) ? intval($_GET['CID']) : 0;

//список курсов с режимом прохождения "линейно"
$res = sql("SELECT CID FROM Courses WHERE CID IN ('".implode("','",$s['tkurs'])."') AND sequence=1");
$coursesnot2select = array();
while ($row = sqlget($res)) {
    $coursesnot2select[$row['CID']] = $row['CID'];
}

$arr_courselist = selCourses($s['tkurs'], $CID, $GLOBALS['controller']->enabled,true);

$GLOBALS['controller']->addFilter(_("Курс"), 'CID', $arr_courselist, $CID, true);

if ($CID) {
	$GLOBALS['controller']->setLink("m190201", array($CID));
	$GLOBALS['controller']->captureFromOb(CONTENT);


	$res = sql("SELECT
					Courses.longtime,
					Courses.cEnd,
					Courses.cBegin,
					Courses.sequence,
					departments.color
				FROM Courses
					LEFT JOIN departments
					ON Courses.did LIKE '%;departments.did;%'
				WHERE CID = '{$CID}'");
    $row = sqlget($res);
    if ($row['sequence']) {
        $GLOBALS['controller']->setMessage(_('Создание занятия на основе данного курса невозможно, необходимо изменить режим прохождения в свойствах курса.'));
        $GLOBALS['controller']->terminate();
        exit();
    }
    $longtime = $row['longtime'];
    $date_begin = $row['cBegin'];
    $date_end = $row['cEnd'];
	$date_begin_tms = mktime(0,0,0,substr($date_begin, 5, 2),substr($date_begin, 8, 2),substr($date_begin, 0, 4));
	$date_end_tms = mktime(0,0,0,substr($date_end, 5, 2),substr($date_end, 8, 2),substr($date_end, 0, 4));

	$date_begin = date("M d, Y H:i:s", $date_begin_tms);
	$date_end = date("M d, Y H:i:s", $date_end_tms);

	// только абсолютные занятия
	$schedules = $cond_sheids = array();
       
	$res = sql("SELECT SHEID, title, end, begin, cond_sheid, cond_mark, cond_progress, cond_avgbal, cond_sumbal, cond_operation, UNIX_TIMESTAMP(begin) AS ts_begin, UNIX_TIMESTAMP(end) AS ts_end FROM schedule WHERE CID = '{$CID}' AND CHID='0' AND timetype='0' ORDER BY `cond_sheid`");
    while ($row = sqlget($res)) {
        if (in_array(dbdriver, array('oci8'))) {
            $tmp = $row['ts_begin'];
        } else {
        	$tmp = mktime(substr($row['begin'], 11, 2), substr($row['begin'], 14, 2), substr($row['begin'], 17, 2), substr($row['begin'], 5, 2), substr($row['begin'], 8, 2), substr($row['begin'], 0, 4));
        }
        $tmp = date("M d, Y H:i:s", $tmp);
        $row['date_begin'] = $tmp;
        if (in_array(dbdriver, array('oci8'))) {
            $tmp = $row['ts_end'];
        } else {
            $tmp = mktime(substr($row['end'], 11, 2),
                          substr($row['end'], 14, 2),
                          (int)substr($row['end'], 17, 2),
                          substr($row['end'], 5, 2),
                          (substr($row['begin'], 8, 2)==substr($row['end'], 8, 2))?substr($row['end'], 8, 2)+1:substr($row['end'], 8, 2),
                          substr($row['end'], 0, 4));
        }
        $tmp = date("M d, Y H:i:s", $tmp);
    	$row['date_end'] = $tmp;

    	$row['cond_sheid'] = explode('#',$row['cond_sheid']);
    	$row['cond_mark']  = explode('#',$row['cond_mark']);
    	array_walk($row['cond_mark'], create_function('&$v,$k', 'if ($v[strlen($v)-1] == "-") $v = substr($v,0,-1);'));        
    	$schedules[($row['SHEID'])] = $row;
//        $schedules[] = $row;
    	$cond_sheids = array_merge($cond_sheids, $row['cond_sheid']);
    }

   //вызов рекурсивной функции для установления порядка массива
    $inarr = $rezult_array= array();
    sort_array_schedules('-1');
    //exit;
    $schedules=array_reverse($rezult_array);
    
    if (count($schedules)){
		$timelines[SCHEDULE_TYPE_ABSOLUTE]['date_begin'] = $date_begin;
		$timelines[SCHEDULE_TYPE_ABSOLUTE]['date_end'] = $date_end;
		$timelines[SCHEDULE_TYPE_ABSOLUTE]['color'] = '#717897';
		$timelines[SCHEDULE_TYPE_ABSOLUTE]['schedules'] = $schedules;
    }
	// только относительные занятия
	$date_begin_relative = date("M d, Y H:i:s", 0);
	$date_end_relative = date("M d, Y H:i:s", $longtime*24*60*60);

	$schedules_relative = array();
	$res = sql("SELECT SHEID, title, startday, stopday, cond_sheid, cond_mark, cond_progress, cond_avgbal, cond_sumbal, cond_operation FROM schedule WHERE CID = '{$CID}' AND CHID='0' AND timetype='1'");
    while ($row = sqlget($res)) {
        $row['startday'] -= 1;
    	$tmp = date("M d, Y H:i:s", $row['startday']);
    	$row['date_begin'] = $tmp;
    	$tmp = date("M d, Y H:i:s", $row['stopday']);
    	$row['date_end'] = $tmp;

    	$row['cond_sheid'] = explode('#',$row['cond_sheid']);
    	$row['cond_mark']  = explode('#',$row['cond_mark']);
    	array_walk($row['cond_mark'], create_function('&$v,$k', 'if ($v[strlen($v)-1] == "-") $v = substr($v,0,-1);'));

    	$schedules_relative[] = $row;
    }

    if (count($schedules_relative)){
		$timelines[SCHEDULE_TYPE_RELATIVE]['date_begin'] = $date_begin_relative;
		$timelines[SCHEDULE_TYPE_RELATIVE]['date_end'] = $date_end_relative;
		$timelines[SCHEDULE_TYPE_RELATIVE]['color'] = '#7E9771';
		$timelines[SCHEDULE_TYPE_RELATIVE]['schedules'] = $schedules_relative;
    }

	if (!count($timelines[SCHEDULE_TYPE_RELATIVE]['schedules']) && !count($timelines[SCHEDULE_TYPE_ABSOLUTE]['schedules'])) {
		$controller->setMessage(_("По данному курсу не создано ни одного занятия<br>Создать занятия можно на странице расписания."));
	} else {
	    $smarty_tpl = new Smarty_els();
	    $smarty_tpl->assign('sitepath',$sitepath);
	    $smarty_tpl->assign('tooltip_percent',addslashes($tooltip->display('schedule_adaptive_percent')));
	    $smarty_tpl->assign('tooltip_average',addslashes($tooltip->display('schedule_adaptive_average')));
	    $smarty_tpl->assign('tooltip_total',addslashes($tooltip->display('schedule_adaptive_total')));
	    $smarty_tpl->assign('timelines',$timelines);
	    $smarty_tpl->assign('CID', $CID);
		$timeline_code = $smarty_tpl->fetch("schedule_adaptive.tpl");
/*
        <input style="border:0" type="image" value="'._("Готово").'"
        onclick="t_%s.sendTo(\'%sschedule_adaptive.php\');showMessage(\''.APPLICATION_TITLE.'\',\''._("Параметры успешно сохранены").'\')" src="%s"/>
*/
		$html = '
		<h3>%s</h3>
		<div id="container_%s"></div>
		<br /><br /><br />
        <table border=0 cellpadding=0 cellspacing=0 class="auto-hscroll"><tr><td>

        <div class="button ok" ><a href="javascript:void(0);" onclick=\'t_%s.sendTo("%sschedule_adaptive.php");eLS.utils.showMessageBox("'._('Временная диаграмма').'","'._("Параметры успешно сохранены").'");\'>&nbsp;&nbsp;OK&nbsp;&nbsp;&nbsp;</a></div><input type="submit" name="ok" value="ok" class="submit" style="display: none;" /><input type=\'hidden\' name=\'ok\' value=\'&amp;nbsp;&amp;nbsp;&amp;nbsp;OK&amp;nbsp;&amp;nbsp;&amp;nbsp;\'><div class="clear-both"></div>

		</td></tr></table>
		<br /><br /><br />';
		$button = $controller->view_root->skin_url . "/images/b_done.gif";

/*		if (count($timelines[SCHEDULE_TYPE_RELATIVE]['schedules']) && count($timelines[SCHEDULE_TYPE_ABSOLUTE]['schedules'])) {
			$controller->captureFromReturn('m190201', sprintf($html, SCHEDULE_TYPE_ABSOLUTE, SCHEDULE_TYPE_ABSOLUTE, $sitepath, $button));
			$controller->captureFromReturn('m190202', sprintf($html, SCHEDULE_TYPE_RELATIVE, SCHEDULE_TYPE_RELATIVE, $sitepath, $button));
//			$controller->captureFromReturn(CONTENT, sprintf($html, SCHEDULE_TYPE_ABSOLUTE, SCHEDULE_TYPE_ABSOLUTE, $sitepath, $sitepath) . sprintf($html, SCHEDULE_TYPE_RELATIVE, SCHEDULE_TYPE_RELATIVE, $sitepath, $sitepath));
			$controller->setContentCommon($timeline_code);
		} else {
			$type = (count($timelines[SCHEDULE_TYPE_RELATIVE]['schedules']) > count($timelines[SCHEDULE_TYPE_ABSOLUTE]['schedules'])) ? SCHEDULE_TYPE_RELATIVE : SCHEDULE_TYPE_ABSOLUTE;
			$controller->captureFromReturn(CONTENT, $timeline_code . sprintf($html, $type, $type, $sitepath, $button));
		}
*/
		$html_absolute = (count($timelines[SCHEDULE_TYPE_ABSOLUTE]['schedules'])) ? sprintf($html, _("Абсолютные даты"), SCHEDULE_TYPE_ABSOLUTE, SCHEDULE_TYPE_ABSOLUTE, $sitepath, $button) : '';
		$html_relative = (count($timelines[SCHEDULE_TYPE_RELATIVE]['schedules'])) ? sprintf($html, _("Относительные даты"), SCHEDULE_TYPE_RELATIVE, SCHEDULE_TYPE_RELATIVE, $sitepath, $button) : '';

		$controller->captureFromReturn(CONTENT, $timeline_code . $html_absolute . $html_relative);

	}
}
$GLOBALS['controller']->terminate();


 /*
  *  	sort_array_schedules Рекурсивная функция сортировки массива для отображения связей
  *     @var $id int
  *     $schedules    - оригинальный массив
  *		$rezult_array - Результирующий массив
  *		$inarr        - Массив для проверки что бы не повторялись ИД
  */

function sort_array_schedules ($id) {
static $i=0;
	global $rezult_array, $inarr,$schedules;
	$i++;
	foreach ($schedules as $val) {
            foreach($val["cond_sheid"] as $idparent){
                if ($idparent==$id ){
                    if (in_array($val['SHEID'],$inarr)){
                        continue;
                    }
                    sort_array_schedules ($val['SHEID']);
                    $inarr[] = $val['SHEID'];
                    $rezult_array[] = $val;
                }
            }
	}
}
?>