<?php
require_once('1.php');
require_once('lib/classes/CourseContent.class.php');
require_once('lib/classes/Module.class.php');

if (!$s[login]) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");

istest();
$urlAddon = '';

$tpo_type = 0;
$referer = in_array($_GET['referer'], array('search', 'test')) ? $_GET['referer'] : null;

if ($referer == 'test') {
    $res = sql("SELECT cid, module FROM organizations WHERE oid = '{$_GET['oid']}'");
    if ($row = sqlget($res)){
        $_GET['bid'] = $row['module'];
        $_GET['cid'] = $row['cid'];
    }
}

//проверим досткпен ли модуль в соответсвии с режимом прохождения курса
if (!isAvailableModule($_GET['oid'], $_SESSION['s']['mid'], $_GET['cid'])) {
   $GLOBALS['controller']->setView('DocumentBlank');
   $GLOBALS['controller']->setMessage(_('Элемент не доступен для изучения'),JS_GO_URL,'about:blank');
   $GLOBALS['controller']->terminate();
   exit();
}

$sequence = getField('Courses', 'sequence', 'CID', (int) $_GET['cid']);
if ($_GET['bid'] && isset($_GET['cid']) && $_GET['oid'] && !$referer) {

  	//CIrkutPersonalLogger::put($_GET['cid'],$_GET['oid']);
    CCourseContentCurrentItem::setCurrentItem($_SESSION['s']['mid'],$_GET['cid'],$_GET['oid']);
    if (($_SESSION['s']['perm'] <= 1) && $sequence) {
        CCourseContentSequenceHistoryItem::update($_SESSION['s']['mid'],$_GET['cid'],$_GET['oid']);
    }

	if ($_GET['tests'][0] == '_') {
	    $oids = explode('_',substr($_GET['tests'],1));
	    shuffle($oids);
	    $oids = join(',',$oids);
	    $GLOBALS['controller']->setView('DocumentBlank');
	    $GLOBALS['controller']->captureFromOb(CONTENT);
	    echo "
        <script type=\"text/javascript\">
        <!--
        top.start_test('$oids');
        //-->
        </script>";
        $GLOBALS['controller']->captureStop(CONTENT);
	    $GLOBALS['controller']->terminate();
	    exit();
	}

	if ($_GET['cid']) {
	   $urlAddon = 'aicc_sid='.(int) $_GET['bid'].'&aicc_url='.AICC_URL;
	}
}

$GLOBALS['controller']->setView('DocumentBlank');

if (isset($_GET['tid']) && $_GET['tid'] && isset($_GET['cid']) && isset($_GET['oid']) && !$referer) {
    //CIrkutPersonalLogger::put($_GET['cid'],$_GET['oid']);
    if ($_GET['oid']) {
        CCourseContentCurrentItem::setCurrentItem($_SESSION['s']['mid'],$_GET['cid'],$_GET['oid']);
        if (($_SESSION['s']['perm'] <= 1) && $sequence) {
            CCourseContentSequenceHistoryItem::update($_SESSION['s']['mid'],$_GET['cid'],$_GET['oid']);
        }
    }
    refresh($GLOBALS['sitepath'].'test_start.php?tid='.(int) $_GET['tid'].'&ModID='.(int) $_GET['oid'].'&cid='.(int) $_GET['cid']);
    exit();
}

if (isset($_GET['run']) && $_GET['run'] && isset($_GET['cid']) && isset($_GET['oid']) && !$referer) {
    //CIrkutPersonalLogger::put($_GET['cid'],$_GET['oid']);
    if ($_GET['oid']) {
        CCourseContentCurrentItem::setCurrentItem($_SESSION['s']['mid'],$_GET['cid'],$_GET['oid']);
        if (($_SESSION['s']['perm'] <= 1) && $sequence) {
            CCourseContentSequenceHistoryItem::update($_SESSION['s']['mid'],$_GET['cid'],$_GET['oid']);
        }
    }
    if ($params = CRunModuleItem::getParameters($_GET['run'])) {
        $str_runexe = "
        <script language=\"JavaScript\">
        if (parent.leftFrame) {
            parent.leftFrame.document.getElementById('Runner').Run('{$params['exe']}', 'open', '/{$params['params']}', '{$params['path_to']}', 5);
        }
        if (parent.topFrame) {
            parent.topFrame.document.getElementById('Runner').Run('{$params['exe']}', 'open', '/{$params['params']}', '{$params['path_to']}', 5);
        }
        </script>
        ";
        $GLOBALS['controller']->captureFromReturn(CONTENT, $str_runexe);
        $GLOBALS['controller']->setMessage(_('Запускается внешняя программа...'));
        $GLOBALS['controller']->terminate();
    }
    exit();
}

if (isset($_GET['bid']) && $_GET['bid']) {

    if (1 || check_access_level($_GET['bid'],$GLOBALS['s']['user']['meta']['access_level'])) {

        $bid = (int) $_GET['bid'];
        $sql = "SELECT * FROM library WHERE (bid='".$bid."' OR parent='".$bid."') AND is_active_version='1'";
        $res = sql($sql);

        if (!sqlrows($res)) {
            $sql = "SELECT * FROM library WHERE (bid='".$bid."' OR parent='".$bid."') ORDER BY bid DESC";
            $res = sql($sql);
        }

        if (sqlrows($res)) {

            $row = sqlget($res);
            if ($row['is_active_version'] != '1') {
                sql("UPDATE library SET is_active_version = '1' WHERE bid = '{$row['bid']}'");
            }
            $filename = $row['filename'];

            if (empty($filename)) {
                $GLOBALS['controller']->setMessage(_("Учебный материал не найден"),JS_GO_URL,'javascript:window.close();');
                $GLOBALS['controller']->terminate();
                exit();
            }

            if ($_GET['cid']) {
                if (strstr($filename,'?') === false) {
                    $urlAddon = '?'.$urlAddon;
                } else {
                    $urlAddon = '&'.$urlAddon;
                }
            }
            $url = "{$sitepath}library{$filename}".$urlAddon;

            $addon = '';
            if (strchr($filename,'?')) {
                if ($parts = explode('?',$filename)) {
                    $filename = $parts[0];
                }
            }
            if (!file_exists($GLOBALS['wwf'].'/library'.$filename)) {
/*                if (($_GET['cid']) && (strstr($filename, '-T.') !== false) && ($_SESSION['s']['perm'] <= 1)) {
                    sql("INSERT INTO
                            scorm_tracklog
                            (mid, cid, ModID, McID, status, trackdata, stop, start, score, scoremax, scoremin)
                        VALUES
                            ('".(int) $_SESSION['s']['mid']."','".(int) $_GET['cid']."','".(int) $_GET['oid']."','".(int) $_GET['bid']."','', '', NOW(), NOW(), '0', '0', '0')");
                    $GLOBALS['controller']->setView('DocumentBlank');
                    $GLOBALS['controller']->captureFromOb(CONTENT);
                    echo "
                    <script type=\"text/javascript\">
                    <!--
                    top.open_test_page();
                    //-->
                    </script>";
                    $GLOBALS['controller']->captureStop(CONTENT);
                    $GLOBALS['controller']->terminate();
                    exit();
                }
*/
                $GLOBALS['controller']->setView('DocumentBlank');
                $GLOBALS['controller']->setMessage(_("Файл не найден"));
                $GLOBALS['controller']->terminate();
                exit();
            }

            //Фиксируем статистику доступа к материалу
            saveStat($_SESSION['s']['mid'], 'module', $_GET['bid']);

            if (strtolower(substr($filename, -3)) == 'oms') {
                header("Content-type: application/unknown");
                header("Content-disposition: attachment; filename=\"".basename($filename)."\";");
                echo file_get_contents($GLOBALS['wwf'].COURSES_DIR_PREFIX.'/library'.$filename);
                exit();
            }

            if (strtolower(substr($filename, -3)) == 'mht') {
                header("Content-type: message/rfc822");
                header("Content-disposition: inline; filename=\"".basename($filename)."\";");
            	echo file_get_contents($GLOBALS['wwf'].COURSES_DIR_PREFIX.'/library'.$filename);
            	exit();
            }

            if ($_GET['bid'] && isset($_GET['cid']) && isset($_GET['oid'])) {
                if ($_GET['oid']) {
                	$sql = "SELECT cid, root_ref FROM organizations WHERE oid = '".(int) $_GET['oid']."'";
                	$res = sql($sql);

                	while($row = sqlget($res)) {
                		$cid_true = (int) $row['root_ref'];
                		if (!$cid_true) $cid_true = (int) $row['cid'];
                	}
                } else {
                    $cid_true = (int) $row['cid'];
                }
                
                if (preg_match('/COURSES\/course([0-9]+)\//i', $url, &$matches)) {
                    $cid_true = $matches[1];
                }                

                // scorm api launch
                $GLOBALS['controller']->captureFromOb(CONTENT);
?>
<html>
    <head>
    <TITLE><?=APPLICATION_TITLE?></TITLE>
    <script language="Javascript" type="text/javascript" src="<?=$sitepath.'scorm_api.php?oid='.(int) $_GET['oid'].'&bid='.$_GET['bid'].'&cid='.$_GET['cid']?>"></script>
    <script language="Javascript" type="text/javascript" src="<?=$sitepath.'lib/scorm/request.js'?>"></script>
    <script language="Javascript" type="text/javascript">
    <!--
    if (typeof parent.eLearning_server_metadata != 'undefined') {
        parent.eLearning_server_metadata.coursexml = '<?=$sitepath.'COURSES/course'.$cid_true.'/course.xml' ?>';
    }
    //-->
    </script>
    </head>
    <body style="margin: 0px; padding: 0px;">
    <iframe name="main" frameborder="no" width=100% height=100% src="<?=$url?>"></iframe>
    </body>
</html>
<?php
                exit();
                $GLOBALS['controller']->captureStop(CONTENT);

            } else {
                //header("Location: $url");
                //exit();
                //всё что ниже до exit(); только для того, чтобы открыть материал без навигации, иначе подойдут и две строчки выше                
                if (defined('ENABLE_EAUTHOR_COURSE_NAVIGATION') && ENABLE_EAUTHOR_COURSE_NAVIGATION) {
                    $use_external_navigation = 'true';
                }
                else {
                    $use_external_navigation = 'false';
                }
                $scriptDisableClick = '';
                $block_content_copy      = 'false';
                $condition = ($GLOBALS['s']['perm']==1) && DISABLE_COPY_MATERIAL;
                if ($condition){
                    $scriptDisableClick =  '<SCRIPT src="' . $sitepath . 'js/disableclick.js" language="JScript" type="text/javascript"></script>';
                    $block_content_copy = 'true';
                }

                $cid_true = $CID;
                
                if (preg_match('/COURSES\/course([0-9]+)\//i', $url, &$matches)) {
                	$cid_true = $matches[1];
                }
                
                ?>
<html>
    <head>
    <TITLE><?=APPLICATION_TITLE?></TITLE>
    <?php echo $scriptDisableClick; ?>
    <script type="text/javascript">
<!--
var eLearning_server_metadata = {
    version_string: "3000",
    revision: 23423,
    course_options: {
        use_internal_navigation: <?=$use_external_navigation?>,
        block_content_copy: <?=$block_content_copy?>,
        metadata_page: '<?=$sitepath.'teachers/'.$strParamsLink.$strParamsMain?>',
        glossary_url: '<?=$GLOBALS['sitepath'].'glossary_get.php?cid='.$CID?>'
    },
    permission: "<?=$_SESSION['s']['perm'] ?>",
    coursexml: "<?=$GLOBALS['sitepath'].'COURSES/course'.$cid_true.'/course.xml' ?>"
}
//-->
</script>
    </head>
    <body style="margin: 0px; padding: 0px;">
    <iframe name="main" frameborder="no" width=100% height=100% src="<?=$url?>"></iframe>
    </body>
</html>
<?php
                exit();
            }

        } else $GLOBALS['controller']->setMessage(_("Нет текущий версии учебного материала"),JS_GO_URL,'javascript:window.close();');

    } else $GLOBALS['controller']->setMessage(_("У вас нет соответствующего уровня доступа"),JS_GO_URL,'javascript:window.close();');

} else $GLOBALS['controller']->setMessage(_("Не указан учебный материал"),JS_GO_URL,'javascript:window.close();');

$GLOBALS['controller']->terminate();

function check_access_level($id,$access_level) {
    if ($access_level>0)
        $sql_access_level = " need_access_level>='".(int) $access_level."' OR ";
    $sql = "SELECT * FROM library WHERE bid='".(int) $id."' AND ({$sql_access_level} need_access_level='0')";
    $res = sql($sql);
    if (sqlrows($res)==1) return true;
    return false;
}

function isAvailableModule($oid, $mid, $cid) {
    //линейный режим прохождения курса
    $sequence = getField('Courses', 'sequence', 'CID', (int) $cid);
    if ($oid && $cid && $sequence) {
        $sequence = new CCourseContentSequence($cid,$mid);
        if (!$cid) $sequence->disable();
        if ($_SESSION['s']['perm'] > 1) $sequence->disable();
        if (!$sequence->isItemAllowed($oid)) {
            //модуль недоступен для изучения
            return false;
        }
    }

    //синхронный с расписанием режим прохождения курса
    $is_module_need_check = getField('Courses', 'is_module_need_check', 'CID', (int) $cid);
    if ($cid && $mid && $GLOBALS['s']['perm']<2 && $is_module_need_check) {
        //структура курса
        $prev_refs = array();
        $res = sql($sql = "SELECT oid, vol1, module, prev_ref
                           FROM organizations
                           WHERE cid='$cid'");
        while ($row = sqlget($res)) {
            $prev_refs[$row['prev_ref']] = $row['oid'];
        }

        //добавим инфу о занятиях
        $studies         = array();
        $studies2modules = array();
        $marks           = array();

        $res = sql($sql = "SELECT
                                s.SHEID,
                                s.begin,
                                s.title,
                                s.cond_sheid,
                                s.cond_mark,
                                s.cond_progress,
                                s.cond_sumbal,
                                s.cond_avgbal,
                                s.cond_operation,
                                sID.V_STATUS,
                                sID.toolParams
                           FROM schedule s
                           LEFT JOIN scheduleID sID ON (s.SHEID = sID.SHEID)
                           LEFT JOIN EventTools e ON (e.TypeID = s.typeID)
                           WHERE
                                sID.toolParams LIKE '%module_moduleID%' AND
                                (e.tools = 'tests' OR e.tools = 'module') AND
                                sID.MID = '$mid' AND
                                s.CID = '$cid'
                                ORDER BY s.begin DESC");
        while ($row = sqlget($res)) {
            $dummy = explode(';',$row['toolParams']);
            foreach ($dummy as $val) {
                $_dummy = explode('=',$val);
                if ($_dummy[0] == 'module_moduleID') {
                    $studies[$row['SHEID']] = $row;
                    unset($studies[$row['SHEID']]['SHEID']);
                    $marks[$row['SHEID']] = $row['V_STATUS'];
                    $studies2modules[$_dummy[1]] = $row['SHEID'];
                }
            }
        }

        //анализируем занятия
        if (is_array($studies) && count($studies)) {
            //$crnt = array_search('-1',$prev_refs);
            $crnt = '-1';
            do {

                $crnt = $prev_refs[$crnt];
                //если есть занятие для текущего модуля
                if ($crntStudy = $studies[$studies2modules[$crnt]]) {

                    //прооверим дату начла занятия
                    if (strtotime($crntStudy['begin'])>time()) {
                        $crnt = false;
                        break;
                    }

                    //проверим условия
                    $conditions = array('cond_progress' => $crntStudy['cond_progress'],
                        'cond_avgbal'   => $crntStudy['cond_avgbal'],
                        'cond_sumbal'   => $crntStudy['cond_sumbal']
                    );

                    foreach ($conditions as $key=>$condition) {
                        if ($condition) {
                            $interval  = explode('-',trim($condition));
                            if(!$interval[1]) unset($interval[1]);
                            $dummy     = array_count_values($marks);
                            switch ($key) {
                                case 'cond_progress':
                                    $crntValue = 100 - ($dummy['-1']*100)/count($marks);
                                    break;
                                case 'cond_avgbal':
                                    $crntValue = (array_sum($marks) + $dummy['-1'])/count($marks);
                                    break;
                                case 'cond_sumbal':
                                    $crntValue = array_sum($marks) + $dummy['-1'];
                                    break;
                            }

                            if ((isset($interval[1]) && !($interval[0]>$crntValue && $crntValue>$interval[1])) ||
                                    (!isset($interval[1]) && $crntValue<$interval[0]) ) {
                                $crnt = false;
                                break 2;
                            }
                        }
                    }
                    //проверим связи с другими занятиями
                    if ($crntStudy['cond_sheid'] != '-1' && $crntStudy['cond_marks'] != '-' ) {
                        
                        $sheids = explode('#',$crntStudy['cond_sheid']);
                        $sheidMarks = explode('#',$crntStudy['cond_mark']);
                        if (is_array($sheids) && count($sheids) && is_array($sheidMarks) && count($sheidMarks)) {
                            foreach ($sheids as $sheid) {
                                list($key, $condition) = each($sheidMarks);
                                $interval  = explode('-',trim($condition));
                                if(!$interval[1]) unset($interval[1]);
                                $crntValue = $studies[$sheid]['V_STATUS'];
                                if ((isset($interval[1]) && !($interval[0]>$crntValue && $crntValue>$interval[1])) ||
                                        (!isset($interval[1]) && $crntValue<$interval[0]) ) {
                                    $crnt = false;
                                    break 2;
                                }
                            }
                        }
                    }
                }

            } while ($crnt != $oid);

            return (bool) $crnt;
        }else {
            //если занятий нет то все модули доступны
            return true;
        }
    }
    //Если ни один из реждимов непременим
    return true;
}

/**
 * Запись лога доступа к модулям
 */
function saveStat($mid, $moduleType, $id) {
    if ($mid > 0 && $moduleType && $id > 0) {
        switch ($moduleType) {
            case 'module':
                sql("INSERT INTO `mod_attempts` (modID, mid, start) VALUES($id, $mid, ".$GLOBALS['adodb']->DBDate(time()).")");                
                break;
        }
    }
}
?>