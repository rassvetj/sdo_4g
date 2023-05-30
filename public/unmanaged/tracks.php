<?php
require_once("1.php");
require_once("lib/FCKeditor/fckeditor.php");
require_once('courses.lib.php');
require_once('tracks.lib.php');
if (!$dean) login_error();
?>
<script language='javascript'>
    function validateRegForm() {
        if(document.getElementById('name').value == ''){
            alert("Не заполнено название");
            return false;
        }
        else
            return true;
    }
</script>
<?php
//   "SELECT * FROM People, students WHERE People.MID=students.MID AND students.CID=$cid"
/*students  SID ( int ) [not_null primary_key unique_key auto_increment ]
MID ( int ) [not_null ]
CID ( int ) [not_null ]
cgid ( int ) [not_null ]
Registered ( int ) [not_null ]

*/

if ($trid) {
    if ($GLOBALS['controller']->checkPermission(TRACK_PERM_EDIT)) {
        if ($c == 'lock') {
            sql("UPDATE tracks SET locked = '1' WHERE trid = '".(int) $trid."'");
            refresh($GLOBALS['sitepath'].'tracks.php');
        }
        if ($c == 'unlock') {
            sql("UPDATE tracks SET locked = '0' WHERE trid = '".(int) $trid."'");
            refresh($GLOBALS['sitepath'].'tracks.php');
        }
    }
}

switch ($c) {
    case 'copy':
        intval('trid');
        copyTrack($trid);
        $GLOBALS['controller']->setMessage(_('Специальность успешно скопирована'), JS_GO_URL, $GLOBALS['sitepath'].'tracks.php');
        $GLOBALS['controller']->terminate();
        exit();
        break;

    case "":

        if (!isset($_SESSION['s']['track']['advanced'])) {
            $_SESSION['s']['track']['advanced'] = false;
        }

        require_once('Pager/examples/Pager_Wrapper.php');

        echo show_tb();
        echo ph(_("Учебные программы"));
        $GLOBALS['controller']->captureFromOb(CONTENT);
        // $res=sql("SELECT MID FROM Students GROUP BY MID");
        // $freestud=sqlrows($res);
        // $allstud=$freestud;

        $sql = "SELECT * FROM tracks ORDER BY name";
        $pagerOptions =
                array(
                    'mode'    => 'Sliding',
                    'delta'   => 5,
                    'perPage' => TRACKS_PER_PAGE,
                );

        if ($page = Pager_Wrapper_Adodb($GLOBALS['adodb'], $sql, $pagerOptions)) {
            while($row = sqlget($page['result'])) {
                if (!isset($row['trid'])) $row['trid'] = $row['TRID'];
                $trids[] = $row['trid'];
            }
        }

        if (is_array($trids) && count($trids)) {
            $sql = "SELECT * FROM tracks WHERE trid IN ('".join("','",$trids)."') ORDER BY name";
        }

        if ($page['links'])
            $links = "<br><table width=100% class=main cellspacing=0><tr><td align=center>".$page['links']."</td></tr></table><br>";

        echo $links;
        if ($GLOBALS['controller']->checkPermission(TRACK_PERM_EDIT)){
            $smarty = new Smarty_els();
            $smarty->assign('url', 'tracks.php?c=new_track');
            $smarty->assign('sitepath', $GLOBALS['sitepath']);
            $smarty->assign('caption',_("создать специальность"));
            echo $smarty->fetch('common/add_link.tpl');
        }
        echo "
    <table width=100% class=main cellspacing=0>
    <tr><th>"._("Название")."</th>";
        if ($GLOBALS['controller']->checkPermission(TRACK_PERM_EDIT))
            echo "<th width='100px' align='center'>" . _("Действия") . "</th>";
        echo "</tr>";

        $res=sql($sql);
        //$trackFilter = new CTrackFilter($GLOBALS['TRACK_FILTERS']);
        while ($r=sqlget($res)) {
            // if (!$trackFilter->is_filtered($r['trid'])) continue;
            echo "<tr><td>";
            if ( intval( $r[status]) > 0 ) echo "<B>";

            $title = ($r[id]) ? "{$r['id']}. {$r[name]}" : $r[name];
            echo "<a href={$PHP_SELF}?c=edit_courses&trid={$r[trid]}>{$title}</a>";
            if (  intval( $r[status]) > 0 ) echo "</B>";
            echo " <BR> "._("курсов").": ".getCoursesNum($r['trid'])."</td>";
            if ($GLOBALS['controller']->checkPermission(TRACK_PERM_EDIT)) {
                echo "<td  align='center' nowrap='nowrap'>";
                $icon = getIcon("struct", _("Редактировать состав курсов специальности"));
                if ($r['locked']) {
                    echo "<a href=\"{$PHP_SELF}?c=unlock&trid={$r[trid]}\" title=\""._('Разблокировать специальность')."\">".getIcon('unlock', _('Разблокировать специальность'))."</a>";
                } else {
                    echo "<a href=\"{$PHP_SELF}?c=lock&trid={$r[trid]}\" title=\""._('Заблокировать специальность')."\">".getIcon('lock', _('Заблокировать специальность'))."</a>";
                }
                echo "<a href={$PHP_SELF}?c=edit_courses&trid={$r[trid]} title='"._("Редактировать состав курсов специальности")."'>{$icon}</a>&nbsp;";
                echo "<a href={$PHP_SELF}?c=copy&trid={$r[trid]} title='"._("Скопировать специальность")."'>".getIcon('copy', _('Скопировать специальность'))."</a>&nbsp;";
                if (!$r['locked']) {
                    echo "<a href=\"javascript: void(0);\"  onClick=\"javascript: wopen('$PHP_SELF?c=edit&trid=$r[trid]$sess'); return true;\" title='"._("Редактировать специальность")."'>".getIcon("edit")."</a>
                <a href=$PHP_SELF?c=delete&trid=$r[trid]$sess title='"._("Удалить специальность")."' onclick=\"if (!confirm('"._("Удалить специальность?")."')) return false;\"
                ><img title='"._("Удалить специальность")."' alt='"._("Удалить специальность")."' src='".$sitepath."images/icons/delete.gif' hspace='3'></a>";
                }
            }
            echo "</tr>";
        }

        if (sqlrows($res)==0) echo "<tr><td colspan=2 align=center>"._("Нет ни одной специальности")."</td></tr>";

        echo "</table>";
        echo $links;
        if ($GLOBALS['controller']->checkPermission(TRACK_PERM_EDIT) && false) {
            echo "<form action=$GLOBALS[PHP_SELF] method=post>
   <input type=hidden name=c value='new_track'>
   <br/><br/>
   <table width=100% class=main cellspacing=0>
      <tr>
         <th colspan=2>"._("Добавить")."</th>
      </tr>
      <tr>
         <td nowrap>" . _('Название специальности') . ":</td>
         <td width='100%'>
         <input type=text name=name size=40 value=\"\">
         <!-- input type=checkbox name=TRACK value=1 >"._("специальность")." -->
         </td>
      </tr>
   </table>";

            echo "<br>
   <table cellspacing=\"0\"  cellpadding=\"0\" border=0 width=\"100%\">
   <tr>
      <td align=\"right\" valign=\"top\">";
            if ($GLOBALS['controller']->enabled) echo okbutton();
            else
                echo "<input type=\"image\" name=\"ok\"
      onmouseover=\"this.src='".$sitepath."images/send_.gif';\"
      onmouseout=\"this.src='".$sitepath."images/send.gif';\"
      src=\"".$sitepath."images/send.gif\" align=\"right\" alt=\"ok\" border=\"0\">";
            echo "</td>
   </tr>
   </table>
   </form>";
        }
        $GLOBALS['controller']->captureStop(CONTENT);
        echo show_tb();
        break;

    case "new_track":
        $GLOBALS['controller']->setHelpSection('new');
        $GLOBALS['controller']->setView('Document');
        $GLOBALS['controller']->setHeader('Создание специальности');

        echo show_tb();
        $GLOBALS['controller']->captureFromOb(CONTENT);

        $tmp="<form action=$PHP_SELF method=post onsubmit='javascript: return validateRegForm();'>
   <input type=hidden name=c value=\"post_addtrack\">
   <input type=hidden name=trid value=\"$trid\">";

        $tmp.="<table width=100% class=main cellspacing=0>
   <tr><td>"._("Название")." <span style=\"color:red\">*</span></td><td><input type=text size=60 name=name id=name></td></tr>
   <tr><td>"._("Статус")."</td><td><select name=status>
                          <option value='0' selected>"._("не опубликована")."</option>
                          <option value='1' >"._("опубликована")."</option>
                          </select></td></tr>
   <tr><td>"._("Код специальности")."</td><td><input type=text name=id></td></tr>
   <tr><td>"._("Количество семестров")."</td><td><input type=text name=\"number_of_levels\"></td></tr>
   <tr><td>"._("Год обучения")."</td><td><select name=\"year\">";
        //<tr><td>"._("Объем (часов)")."</td><td><input type=text name=volume></td></tr>
        for($i=2000;$i<=(date('Y') + 10);$i++) {
            $tmp .= "<option value=\"$i\"> ".$i."</option>";
        }
        $tmp .= "</select></td></tr>";

        ob_start();
        $oFCKeditor = new FCKeditor('description') ;
        $oFCKeditor->BasePath   = "{$sitepath}lib/FCKeditor/";
        $oFCKeditor->Value      = "";
        $oFCKeditor->Width      = 500;
        $oFCKeditor->Height     = 300;
        $fck_code = $oFCKeditor->Create() ;
        $fck_code = ob_get_contents();
        ob_end_clean();

        $tmp .= "<tr><td>"._("Описание")."</td><td>{$fck_code}</td></tr>";

        if (defined("USE_BOLOGNA_SYSTEM") && USE_BOLOGNA_SYSTEM)
            $tmp .=
                    "
       <tr><td>"._("Кредиты учебной программы по выбору")."</td><td><input type=text name=\"credits_free\" size=60></td></tr>
       <tr><td>"._("Кредиты обязательной учебной программы")."</td><td>[CREDITS]</td></tr>
       ";

        //$str1 = _('Обучение платное');
        //$str2 = _('Стоимость одного семестра');
/*	$tmp .= <<<E0D
		<tr><td>{$str1} </td><td>
		<input name='learn4money' value='1' type="checkbox" onChange="javascript: showHideTr('totalcost', this.checked);"></td></tr>
		<tr id='totalcost'><td>{$str2}: </td><td><input name='totalcost' type="text"></td></tr>
E0D;*/

        $tmp .="</table><br>";

        $tmp .= okbutton();
        $tmp .= "</form>";

        $GLOBALS['controller']->captureFromReturn(CONTENT, $tmp);
        $GLOBALS['controller']->terminate();
        exit();
        break;

    case "delete":
        intvals("trid");
        if ($GLOBALS['controller']->checkPermission(TRACK_PERM_EDIT) && !empty($trid) && !getField('tracks', 'locked', 'trid', $trid))
        {
            sql("DELETE FROM departments_tracks WHERE track='".(int) $trid."'");

            //$rq="UPDATE `tracks2course` SET trid='' WHERE trid=$trid";
            $rq = "DELETE FROM tracks2course WHERE trid='".(int) $trid."'";
            $res=sql($rq,"errGR138");
            sqlfree($res);

            $rq = "DELETE FROM tracks2mid WHERE trid='".(int) $trid."'";
            $res=sql($rq,"errGR138");
            sqlfree($res);

            $res=sql("DELETE FROM tracks WHERE trid='$trid'","errFM185");
            sqlfree($res);
        }

        refresh("$PHP_SELF?$sess");

        break;

    case "edit":

        $GLOBALS['controller']->setHelpSection('edit');
        $GLOBALS['controller']->setView('DocumentPopup');
        $GLOBALS['controller']->setHeader('Редактирование специальности');

        intvals("trid");
        echo show_tb();

        $res=sqlval("SELECT * FROM tracks WHERE trid=$trid","errGR87");
        if (!is_array($res)) exit(_("Такой программы не существует."));
        echo ph(_("Редактирование")." ".$res['name']);
        $GLOBALS['controller']->captureFromOb(CONTENT);
        $tmp="<form action=$PHP_SELF method=post onsubmit='javascript: return validateRegForm();'>
   <input type=hidden name=c value=\"post_edittrack\">
   <input type=hidden name=trid value=\"$trid\">";
        if( intval( $res['status']) == 1 ){
            $sel1="selected"; $sel0="";
        }else{
            $sel0="selected"; $sel1="";
        }
        $tmp.="<table width=100% class=main cellspacing=0>
   <tr><td>"._("Название")."<span style='color:red'>*</span></td><td><input type=text size=60 name=name id=name value=\"".htmlspecialchars($res['name'])."\"></td></tr>
   <tr><td>"._("Статус")."</td><td><select name=status>
                          <option value='0' $sel0>"._("не опубликована")."</option>
                          <option value='1' $sel1>"._("опубликована")."</option>
                          </select></td></tr>
   <tr><td>"._("Код специальности")."</td><td><input type=text name=id value=\"".htmlspecialchars($res['id'])."\"></td></tr>
   <tr><td>"._("Количество семестров")."</td><td><input type=text name=\"number_of_levels\" value=\"".htmlspecialchars($res['number_of_levels'])."\"></td></tr>
   <tr><td>"._("Год обучения")."</td><td><select name=\"year\">";
        //<tr><td>"._("Объем (часов)")."</td><td><input type=text name=volume value=\"".htmlspecialchars($res['volume'])."\"></td></tr>
        for($i=2000;$i<=(date('Y') + 10);$i++) {
            $tmp .= "<option value=\"$i\" ";
            if ($i == $res['year']) {
                $tmp .= "selected";
            }
            $tmp .= "> ".$i."</option>";
        }
        $tmp .= "</select></td></tr>";

        ob_start();
        $oFCKeditor = new FCKeditor('description') ;
        $oFCKeditor->BasePath   = "{$sitepath}lib/FCKeditor/";
        $oFCKeditor->Value      = $res['description'];
        $oFCKeditor->Width      = 500;
        $oFCKeditor->Height     = 300;
        $fck_code = $oFCKeditor->Create() ;
        $fck_code = ob_get_contents();
        ob_end_clean();

        $tmp .= "<tr><td>"._("Описание")."</td><td>{$fck_code}</td></tr>";

        if (defined("USE_BOLOGNA_SYSTEM") && USE_BOLOGNA_SYSTEM)
            $tmp .=
                    "
       <tr><td>"._("Кредиты учебной программы по выбору")."</td><td><input type=text name=\"credits_free\" size=60 value='".$res['credits_free']."'></td></tr>
       <tr><td>"._("Кредиты обязательной учебной программы")."</td><td>[CREDITS]</td></tr>
       ";

        $checked = $res['totalcost'] ? 'checked' : '';
        $display = $res['totalcost'] ? '' : 'style="display: none;"';
        //$str1 = _('Обучение платное');
        //$str2 = _('Стоимость одного семестра');
/*	$tmp .= <<<E0D
		<tr><td>{$str1} </td><td>
		<input name='learn4money' value='1' type="checkbox" $checked onChange="javascript: showHideTr('totalcost', this.checked);"></td></tr>
		<tr id='totalcost' $display><td>{$str2}: </td><td><input name='totalcost' type="text" value="{$res['totalcost']}"></td></tr>
E0D;*/

        $tmp .="</table><br>";

        $tmp .= okbutton();
        $tmp .= "</form>";

        $GLOBALS['controller']->captureFromReturn(CONTENT, $tmp);
        $GLOBALS['controller']->terminate();
        exit();
        break;

    case 'simple':
    case 'advanced':
        if ($c == 'advanced') {
            $_SESSION['s']['track']['advanced'] = true;
        }
        if ($c == 'simple') {
            $_SESSION['s']['track']['advanced'] = false;
        }
    case 'edit_courses':
        require_once($GLOBALS['wwf'].'/lib/classes/Control.class.php');
        require_once($GLOBALS['wwf'].'/lib/sajax/SajaxWrapper.php');
        $sajax_javascript = CSajaxWrapper::init(array('save_hours', 'save_controls'));

        $trackCourses = array();

        $GLOBALS['controller']->setHeader(_('Редактирование состава курсов специальности'));
        $GLOBALS['controller']->captureFromOb(CONTENT);
        $GLOBALS['controller']->setHelpSection('edit_courses');

        $trid = (int)$_REQUEST['trid'];
        $sql = "
		SELECT
		  `tracks`.id,
		  `tracks`.name,
		  `tracks`.number_of_levels,
		  tracks.locked,
		  `Courses`.CID,
		  `Courses`.Title,
		  `Courses`.credit,
		  `tracks2course`.level,
		  tracks2course.hours_lecture,
		  tracks2course.hours_samost,
		  tracks2course.hours_lab,
		  tracks2course.hours_practice,
		  tracks2course.hours_seminar,
		  tracks2course.hours_kurs,
		  tracks2course.type,
		  tracks2course.control
		FROM
		  `tracks`
		  LEFT OUTER JOIN `tracks2course` ON (`tracks`.trid = `tracks2course`.trid)
		  LEFT OUTER JOIN `Courses` ON (`tracks2course`.cid = `Courses`.CID)
		WHERE
		  (`tracks`.trid = {$trid})
	";
        if (!$_SESSION['s']['track']['advanced']) {
            $sql .= " ORDER BY Courses.Title";
        } else {
            $sql .= " ORDER BY tracks2course.type, Courses.Title";
        }
        $res = sql($sql);
        $total = array('courses' => 0, 'hours_lecture' => 0,'hours_samos' => 0, 'hours_lab' => 0,'hours_practice' => 0, 'hours_seminar' => 0,'hours_kurs' => 0,'hours_total' => 0, 'hours_room' => 0, 'credit' => 0);

        while ($row = sqlget($res)) {
            if(!isset($levels[$row['level']]) && $row['level'])
                $levels[$row['level']] = array('courses' => array(), 'l_courses' => 0, 'l_hours_lecture' => 0,'l_hours_samos' => 0, 'l_hours_lab' => 0,'l_hours_practice' => 0, 'l_hours_seminar' => 0,'l_hours_kurs' => 0,'l_hours_total' => 0, 'l_hours_room' => 0);

            if ($row['CID']) {
                $row['hours_room'] = $row['hours_lecture']+$row['hours_lab']+$row['hours_practice']+$row['hours_seminar'];
                $row['hours_total'] = $row['hours_samost'] + $row['hours_room'] + $row['hours_kurs'];
                $levels[$row['level']]['courses'][$row['CID']] = $row;
                $levels[$row['level']]['l_courses']++;
                $levels[$row['level']]['l_hours_lecture']+=$row['hours_lecture'];
                $levels[$row['level']]['l_hours_samost']+=$row['hours_samost'];
                $levels[$row['level']]['l_hours_lab']+=$row['hours_lab'];
                $levels[$row['level']]['l_hours_practice']+=$row['hours_practice'];
                $levels[$row['level']]['l_hours_seminar']+=$row['hours_seminar'];
                $levels[$row['level']]['l_hours_kurs']+=$row['hours_kurs'];
                $levels[$row['level']]['l_hours_total']+=$row['hours_total'];
                $levels[$row['level']]['l_hours_room']+=$row['hours_room'];
                $total['courses']++;
                $total['hours_lecture']+=$row['hours_lecture'];
                $total['hours_samost']+=$row['hours_samost'];
                $total['hours_lab']+=$row['hours_lab'];
                $total['hours_practice']+=$row['hours_practice'];
                $total['hours_seminar']+=$row['hours_seminar'];
                $total['hours_kurs']+=$row['hours_kurs'];
                $total['hours_total']+=$row['hours_total'];
                $total['hours_room']+=$row['hours_room'];
            }

            $track['name'] = ($row['id']) ? "{$row['id']}. {$row['name']}" : $row['name'];
            $track['number_of_levels'] = $row['number_of_levels'];
            $track['locked'] = $row['locked'];
        }

        if ((int)$track['number_of_levels'] <= 0){
            $GLOBALS['controller']->setMessage(_('Неверно задано количество семестров специальности'),JS_GO_URL,'tracks.php');
            $GLOBALS['controller']->terminate();
            exit();
        } else {
            $track['trid'] = $trid;
            foreach (range(1, $track['number_of_levels']) as $number) {
                if (!isset($levels[$number])) $levels[$number] = array();
            }
        }

        ksort($levels);
        foreach($levels as $number => $level)
            if($number > $track['number_of_levels'])
                unset($levels[$number]);

        $res = sql("SELECT DISTINCT `Courses`.CID, `Courses`.credit
		FROM `tracks`
		LEFT OUTER JOIN `tracks2course` ON (`tracks`.trid = `tracks2course`.trid)
		LEFT OUTER JOIN `Courses` ON (`tracks2course`.cid = `Courses`.CID)
		WHERE `tracks`.trid = {$trid}");
        while($row = sqlget($res))
            $total['credit'] += $row['credit'];

        $smarty = new Smarty_els();
        $smarty->assign('sajax_javascript',$sajax_javascript);
        $smarty->assign('CID',$CID);
        $smarty->assign('okbutton',okbutton());
        $smarty->assign('track',$track);
        $smarty->assign('levels',$levels);
        $smarty->assign('total',$total);
        $smarty->assign('types', getTrackCourseTypes());
        $smarty->assign('sitepath',$sitepath);
        $smarty->assign('controls', CControl::getList());
        $html = $smarty->fetch('track_levels.tpl');

        $GLOBALS['controller']->captureFromReturn(CONTENT, $html);
        $GLOBALS['controller']->terminate();
        exit();
        break;


    case 'edit_courses_level':
        $GLOBALS['controller']->setView('DocumentPopup');
        $GLOBALS['controller']->setHeader(_('Редактирование состава курсов семестра'));
        $GLOBALS['controller']->captureFromOb(CONTENT);

        $level = (int)$_REQUEST['level'];
        $trid = (int)$_REQUEST['trid'];

        require_once($GLOBALS['wwf'].'/lib/sajax/SajaxWrapper.php');

        $sajax_javascript = CSajaxWrapper::init(array('search_courses_unused'));

        $search = '';
        if (get_courses_count() < ITEMS_TO_ALTERNATE_SELECT) $search = '*';

        $smarty = new Smarty_els();
        $smarty->assign('search',$search);
        $smarty->assign('courses', search_courses_used($trid, $level));
        $smarty->assign('all_courses', search_courses_unused('', $trid, $level));

        $trackLevel = array();
        $sql = "SELECT * FROM tracks_levels WHERE trid = '$trid' AND level = '$level'";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $trackLevel = $row;
        }

        $smarty->assign('trackLevel', $trackLevel);
        $smarty->assign('sajax_javascript',$sajax_javascript);
        $smarty->assign('level',$level);
        $smarty->assign('trid',$trid);
        $smarty->assign('okbutton',okbutton());
        $smarty->assign('sitepath',$sitepath);
        $html = $smarty->fetch('track_level_courses.tpl');

        $GLOBALS['controller']->captureFromReturn(CONTENT, $html);
        $GLOBALS['controller']->terminate();
        exit();
        break;

    case 'edit_courses_level_assign':
        $GLOBALS['controller']->setView('DocumentPopup');
        $trid = (int)$_POST['trid'];
        $level = (int)$_POST['level'];

        $sql = "SELECT * FROM tracks_levels WHERE trid = '$trid' AND level = '$level'";
        $res = sql($sql);
        if (!sqlget($res)) {
            sql("INSERT INTO tracks_levels (trid, level) VALUES ('$trid', '$level')");
        }

        $sql = "UPDATE tracks_levels
            SET
                volume = ".(double )$volume.",
                date_begin = ".$GLOBALS['adodb']->DBDate(sprintf('%s-%s-%s', $_POST['begin']['Date_Year'], $_POST['begin']['Date_Month'], $_POST['begin']['Date_Day'])).",
                date_end = ".$GLOBALS['adodb']->DBDate(sprintf('%s-%s-%s', $_POST['end']['Date_Year'], $_POST['end']['Date_Month'], $_POST['end']['Date_Day']))."
            WHERE trid = '$trid' AND level = '$level'";
        sql($sql);
        $msg = _('Настройки успешно сохранены');

        if ($trid && $level){
            $sql = "SELECT cid FROM tracks2course WHERE trid = '$trid' AND level = '$level'";
            $res = sql($sql);
            $old = array();
            while($row = sqlget($res)) {
                $old[$row['cid']] = $row['cid'];
            }
            $new = array_diff($_POST['need_courses'], $old);
            $deleted = array_diff($old, $_POST['need_courses']);
            if(!count($_POST['need_courses']))
                $deleted = $old;
            if(count($deleted)){
                $del_cids = implode(', ', $deleted);
                $res = sql("DELETE FROM tracks2course WHERE trid='{$trid}' AND level = '{$level}' AND cid IN ({$del_cids})");
            }

            if (count($new)){
                $values = $assignedCourses = array();
                foreach ($new as $cid) {
                    if (isset($_POST['copy_courses']) && is_array($_POST['copy_courses']) && count($_POST['copy_courses'])) {
                        if (in_array($cid, $_POST['copy_courses'])) {
                            continue;
                        }
                    }
                    $assignedCourses[$cid] = $cid;
                    $values[] = "({$trid}, {$level}, {$cid})";
                }
                if (count($values)) {
                    $values = implode(', ', $values);
                    $sql = "INSERT INTO tracks2course (trid, level, cid) VALUES {$values}";
                    if (!$res = sql($sql)) $msg = _('Ошибка при изменении данных!');
                }
            }
        }
        $GLOBALS['controller']->setMessage($msg, JS_CLOSE_SELF_REFRESH_OPENER);
        $GLOBALS['controller']->terminate();
        exit();
        break;

    case 'blabla':

        $tmp="<form action=$PHP_SELF method=post>
   <input type=hidden name=c value=\"post_edittrack_courses\">";

        $tmp.=getTrackCourses( $trid, "", FALSE );
        //  getCoursesGant( $s_q_l, TRUE, FALSE );
        ////////  выбираем только те, кто входит в трэк    и делаем массив с индексом - id курса
        $res=sql("SELECT Courses.CID as cid, Courses.credits_student as credits FROM Courses, tracks2course
              WHERE tracks2course.trid=$trid
                AND Courses.cid=tracks2course.cid
                             ORDER BY Courses.Title
  ",
            "errGR159");  $check=array();
        while ($r=sqlget($res)){
            $check[$r[cid]]=1;
            $credits += $r['credits'];
        }
        $tmp = str_replace('[CREDITS]',$credits,$tmp);
        sqlfree( $res );
        $tmp.="<P><table width=100% class=main cellspacing=0 id=list1 >"; //    class='main'
        $tmp.="<tr><th>";
        //$tmp .="<span  style='cursor:hand' id=title_$trid onClick=\"putElem('list0');removeElem('list1');\">
        //".getIcon("-")."$t</span>";
        $tmp .= "
  </th><th>"._("даты")."</th><th>"._("стоим.")."</th><th>"._("название")."</th></tr>";

        $courseFilter = new CCourseFilter($GLOBALS['COURSE_FILTERS']);

        $res=sql("SELECT * FROM Courses ORDER BY Title");
        while ( $r = sqlget($res) ){
            if (!$courseFilter->is_filtered($r['CID'])) continue;
            $tmp.="<tr>";

            $tmp.="<td><INPUT type='checkbox' name='che[]' value=".$r['CID']." ".($check[$r['CID']]?"checked":"")."></td>";
            $bedate="<td>".mydate($r['cBegin'])."-<br>".mydate($r['cEnd'])."</td>";
            $tmp.=$bedate;
            $tmp.="<td>".($r['Fee']?$r['Fee']:"")."</td>";

            $tmp.="<td>".$r['Title']."</td>";

            $tmp.="</tr>";
        }
        $tmp.="</table>";

        $tmp.="</FORM>";
        sqlfree ( $res );
        break;

    case "post_edittrack":

        if (!$GLOBALS['controller']->checkPermission(TRACK_PERM_EDIT)) {
            refresh("$PHP_SELF");
            exit();
        }
        intvals("trid cid year");

        if(empty($name)){
            $GLOBALS['controller']->setView('DocumentBlank');
            $GLOBALS['controller']->setMessage("Вы не ввели название специальности",JS_GO_BACK);
            $GLOBALS['controller']->terminate();
            exit();
        }
        $pr=sqlval("SELECT * FROM tracks WHERE trid=$trid","errGR87");
        if (!is_array($pr)) exit(_("Такой программы не существует."));
        // перезаписывем учетные данные самой специальности
        global $adodb;
        $rq="UPDATE `tracks`
           SET name =".$adodb->Quote($name).",
               id  =".$adodb->Quote($id).",
               volume = ".$adodb->Quote($volume).",
               number_of_levels = ".$adodb->Quote($number_of_levels).",
               totalcost= ".$adodb->Quote( $learn4money ? $totalcost : 0 ).",
               type = ".$adodb->Quote($type).",
               status = ".$adodb->Quote($status).",
               owner = ".$adodb->Quote($owner).",
               description = ".$adodb->Quote($description).",
               credits_free = ".$adodb->Quote($credits_free).",
               year = '".(int) $year."'
          WHERE trid=$trid";
        $res=sql($rq,"errUP138");
        sqlfree($res);
        //$res = sql("DELETE FROM tracks_levels WHERE trid=".$trid." AND level > ".(int)$number_of_levels."");

        $GLOBALS['controller']->setView('DocumentBlank');
        $GLOBALS['controller']->setMessage(_('Настройки успешно сохранены'), JS_CLOSE_SELF_REFRESH_OPENER);
        $GLOBALS['controller']->terminate();
        exit();
        break;

    case "post_addtrack":

        global $adodb;
        if ($GLOBALS['controller']->checkPermission(TRACK_PERM_EDIT)) {

            if(empty($name)){
                $GLOBALS['controller']->setView('DocumentBlank');
                $GLOBALS['controller']->setMessage("Вы не ввели название специальности", JS_GO_BACK);
                $GLOBALS['controller']->terminate();
                exit();
            }

            $res=sql("INSERT INTO tracks
		    SET name =".$adodb->Quote($name).",
               id  =".$adodb->Quote($id).",
               volume = ".$adodb->Quote($volume).",
               number_of_levels = ".$adodb->Quote($number_of_levels).",
               totalcost= ".$adodb->Quote( $learn4money ? $totalcost : 0 ).",
               type = ".$adodb->Quote($type).",
               status = ".$adodb->Quote($status).",
               owner = ".$adodb->Quote($owner).",
               description = ".$adodb->Quote($description).",
               credits_free = ".$adodb->Quote($credits_free).",
               year = '".(int) $year."'","errFM185");
        }
        refresh("$PHP_SELF?$sess");
        sqlfree($res);
        break;

    case 'post_edittrack_courses':
        // echo "<H1>$status</H1>";
        // сбрасываем ссылки на курсы
        //   $rq="UPDATE `tracks2course` SET trid='' WHERE trid=$trid";

        $sql = "UPDATE tracks_levels
	       SET 
	       volume = ".(double )$volume.",
	       WHERE trid = '$trid' AND level = '$level'";
        sql($sql);

        $rq="DELETE FROM `tracks2course` WHERE trid=$trid";
        $res=sql($rq,"errGR138");
        sqlfree($res);

        // если выбраны курсы то подключаем их к программе
        if (is_array($che) && count($che)) {         // che - массив идентификатиоов курсов
            //       $rq="UPDATE `tracks2course` SET trid=$trid WHERE cid IN (".implode(", ",$che).")";
            $i=0;

            foreach( $che as $k => $cid){
                //         if ( ! isset($level[$k]) ) $level[$k]=1;
                //         $rq="INSERT INTO tracks2course SET trid=$trid, cid=$cid, level=$level[$k]";
                if ( ! isset($level[$cid]) ) $level[$cid]=1;  // если только включили курс в специальности - то помещаем на первый курс
                $rq="INSERT INTO tracks2course (trid, cid, level) values ($trid, $cid, {$level[$cid]})";
                $i++;
                // echo "RQ=$rq<BR>";
                $res=sql($rq,"errGR139");
                sqlfree($res);
            }
        }
        refresh("$PHP_SELF?c=edit&trid=$trid$sess");
}

return;

function save_hours($trid, $level, $cid, $name, $value) {
    $res = sql("UPDATE tracks2course SET ".substr($GLOBALS['adodb']->Quote($name),1,-1)."=".$GLOBALS['adodb']->Quote($value)." WHERE cid = '".(int) $cid."' AND trid = '".(int) $trid."' AND level='".(int) $level."'");
    if ($res) return true;
    return false;
}

function save_controls($trid, $level, $cid, $value) {
    $res = sql("UPDATE tracks2course SET control=".$GLOBALS['adodb']->Quote($value)." WHERE cid = '".(int) $cid."' AND trid = '".(int) $trid."' AND level='".(int) $level."'");
    if ($res) return true;
    return false;
}

?>