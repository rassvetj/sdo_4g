<?php
   require_once("1.php");
   require_once('courses.lib.php');
   require_once('schedule.lib.php');
   require_once('lib/classes/CCourseAdaptor.class.php');

   require_once($GLOBALS['wwf'].'/lib/sajax/SajaxWrapper.php');
   $sajax_javascript = CSajaxWrapper::init(array('search_people_unused', 'getParentGroupsOptions', 'getParentCoursesOptions')).$js;


   //if ($s['perm']!=3) login_error();
   $strHeader = (defined("LOCAL_WORDS_DEPARTMENT") && LOCAL_WORDS_DEPARTMENT) ? LOCAL_WORDS_DEPARTMENT : _("Кафедры");

function getColors(){
   $tmp[1][id]="white";                 $tmp[1][title]=_("белый");
   $tmp[2][id]="gray";                         $tmp[2][title]=_("серый");
   $tmp[3][id]="yellow";                 $tmp[3][title]=_("желтый");
   $tmp[4][id]="red";                         $tmp[4][title]=_("красный");
   $tmp[5][id]="lightblue";         $tmp[5][title]=_("голубой");
   $tmp[6][id]="blue";                         $tmp[6][title]=_("синий");
   $tmp[7][id]="cyan";                         $tmp[7][title]=_("фисташковый");
   $tmp[8][id]="magenta";                 $tmp[8][title]=_("фиолетовый");
   $tmp[9][id]="darkgray";                 $tmp[9][title]=_("темносерый");
   $tmp[10][id]="black";                 $tmp[10][title]=_("черный");
   $tmp[11][id]="green";                 $tmp[11][title]=_("зеленый");
   $tmp[12][id]="braun";                 $tmp[12][title]=_("коричневый");
   $tmp[13][id]="Olive";                 $tmp[13][title]=_("оливковый");
   $tmp[14][id]="Navy";                 $tmp[14][title]=_("небесный");
   $tmp[15][id]="Purple";                 $tmp[15][title]=_("пурпурный");
   $tmp[16][id]="Silver";                 $tmp[16][title]=_("серебрянный");
   $tmp[17][id]="Lime";                 $tmp[17][title]=_("лимонный");
   $tmp[18][id]="Fuchsia";                 $tmp[18][title]=_("малиновый");
   $tmp[19][id]="Maroon";                 $tmp[19][title]=_("бордовый");

   return( $tmp );
}

function getPalette( $color="white" ){
   $tmp.="<SELECT name='color'>";
   $tmp.="<option value=0>- "._("укажите")." -</option>";
   $cols = getColors();
   foreach( $cols as $col ){
      if( $color == $col[id] ) $sel="selected"; else $sel="";
      $tmp.="<option value=".$col[id]." $sel>".$col[title]."</option>";
   }
   $tmp.="</SELECT>";
  return( $tmp );
}

global $adodb;
switch ($c) {

case "":

        if (isset($_POST['hid_act'])) {
                if (is_array($_POST['sel_courses'])) {
                        $r = sql("DELETE FROM course2group");
                        $arrInserts = array();
                        //var_dump($_POST['sel_courses']);
                        foreach ($_POST['sel_courses'] as $did => $arrGids) {
                                foreach ($arrGids as $gid => $cid) {
                                        if ($cid) $arrInserts[] = "('{$cid}','{$gid}')";
                                }
                        }
                        if (isset($arrInserts) && is_array($arrInserts) && count($arrInserts)>0) {
                            $q = "INSERT INTO `course2group` (`cid`, `gid`) VALUES ".implode(",", $arrInserts);
                            $r = sql($q);
                        }
                }
        }

   echo show_tb();
   echo ph($strHeader);
$GLOBALS['controller']->captureFromOb(CONTENT);
$divs = get_structure_departments( );

set_structure_departments_levels( $divs );

//echo show_structure( $divs );

    if ($s['perm']>2){
    echo "
    <div style='padding-bottom: 5px;'>
        <div style='float: left;'><img src='{$GLOBALS['sitepath']}images/icons/small_star.gif'>&nbsp;</div>
        <div><a href='{$GLOBALS['sitepath']}departments.php?c=edit' style='text-decoration: none;'>"._("создать элемент учебной структуры")."</a></div>
    </div>";
    }

   echo "
    <table width=100% class=main cellspacing=0>
     <tr><th>"._("Название")."</th><th>"._("В должности")."</th><th>"._("Описание")."</th><th width='100px' align='center'>"._("Действия")."</th></tr>
    ";

    //проверим кастрированный мы уч. админ или нет
    if ($s['perm']>2){
        $GLOBALS['noperm2edit4dean'] = sqlvalue("SELECT mid FROM departments WHERE mid='{$GLOBALS['s']['mid']}' AND `application` = '".DEPARTMENT_APPLICATION."'");
    }
   
    if ($divs) {
    echo show_sublevel( $divs, 0 );
    }else {
        echo "<tr><td colspan='99' align='center'>"._("Учебная структура пуста")."</td></tr>";
    }
    
   
 /*
    $res=sql("SELECT * FROM departments","errGR73");
  while ($r=sqlget($res)) {
      if($r[color]>""){ $color=$r[color]; $pic=""; }else{ $color="white"; $pic="-";}
      echo "<tr>
            <td style='background:$color' width=30 align='center'>$pic</td><td><a href=$PHP_SELF?c=edit&did=$r[did]$sess>
                 $r[name]
            </a></td><td>".getpeoplename( $r[mid] )."</td>
            <td>$r[owner_did] ;
            $r[info]</td>
            <td  align='center'><a href=$PHP_SELF?c=delete&did=$r[did]$sess
            onclick=\"if (!confirm('Удалить?')) return false;\" >".getIcon("delete")."</a></tr>";
   }
   if (sqlrows($res)==0) echo "<tr><td colspan=5>Подразделений не создано</td></tr>";
   else sqlfree($res);
  */


   echo "</table>";
      
      if (isset($GLOBALS['noperm2edit4dean'])) {
          unset($GLOBALS['noperm2edit4dean']);
      }
$GLOBALS['controller']->captureStop(CONTENT);
/*$GLOBALS['controller']->captureFromOb('m080304');

        $arrGroups = array();
        $arrDepartments = array();
        $arrSelected = array();
*/
        /*$q = "  SELECT DISTINCT
                  Courses.Title AS course_title,
                  departments.did,
                  departments.name AS department_name,
                  departments.color AS department_color,
                  groupname.name AS group_name,
                  groupname.gid AS gid_notnull,
                  Courses.CID AS cid_notnull,
                  IF (course2group.cid IS NOT NULL, Courses.CID, '') AS course_selected
                FROM
                  course2group
                  RIGHT OUTER JOIN groupname ON (course2group.gid = groupname.gid)
                  RIGHT OUTER JOIN Courses ON (course2group.cid = Courses.CID)
                  INNER JOIN departments ON (Courses.did = departments.did)
                WHERE
                  groupname.cid='-1' AND
                  Courses.status > 0";*/
/*        $q = "  SELECT DISTINCT
                  Courses.Title AS course_title,
                  departments.did,
                  departments.name AS department_name,
                  departments.color AS department_color,
                  groupname.name AS group_name,
                  groupname.gid AS gid_notnull,
                  Courses.CID AS cid_notnull,
                  course2group.cid as cid_group
                 FROM
                  course2group
                  RIGHT OUTER JOIN groupname ON (course2group.gid = groupname.gid)
                  RIGHT OUTER JOIN Courses ON (course2group.cid = Courses.CID)
                  INNER JOIN departments ON (Courses.did = departments.did)
                WHERE
                  groupname.cid='-1' AND
                  Courses.status > 0";
        $r = sql($q);
        while ($a = sqlget($r)) {
                if($a['cid_group'] == "") {
                   $a['course_selected'] = "";
                }
                else {
                   $a['course_selected'] = $a['cid_group'];
                   $a['cid_group'] = $a['cid_notnull'];
                }
                $arrGroups[$a['gid_notnull']] = $a['group_name'];
                $arrDepartments[$a['did']]['name'] = $a['department_name'];
                $arrDepartments[$a['did']]['color'] = $a['department_color'];
                $arrDepartments[$a['did']]['courses'][$a['cid_notnull']] = $a['course_title'];
                if (!$arrSelected[$a['did']][$a['gid_notnull']]) $arrSelected[$a['did']][$a['gid_notnull']] = $a['course_selected'];
        }
*/?>
<form name="form_globaql_schedule" method="POST" action="">
<table width=100% class=main cellspacing=0>
<tr><th>
<?
/*        $intNumCols = (count($arrGroups)+1);
        if ($intNumCols == 1) {
*///                echo "<tr><td>Не задано соответствие \"{$strHeader} - группы - курсы\"</td></tr>";
/*        } else {
            if (!$GLOBALS['controller']->enabled)
            echo ph("{$strHeader}: "._("расписание"));

                echo "&nbsp;</th>";
        }
        $intWid = intval(100/$intNumCols);
        foreach ($arrGroups as $key => $val) {
                echo "<th class='th-center' width='{$intWid}%'>{$val}</th>";
        }
        echo "</tr>";
        $i = 0;
        $cnt = 0;
        $cntExists = 0;
        foreach ($arrDepartments as $did => $arrDep) {
                $strColor = ((++$i)%2) ? "#F7F5EA" : "#FFFFFF";
                echo "<tr bgcolor={$strColor}><td nowrap><a href='/departments.php?c=edit&did={$did}'>{$arrDep['name']}</a></td>";
                foreach ($arrSelected[$did] as $gid => $intSel) {
                        $strSelColor = ($intSel) ? $arrDep['color'] : $strColor;
                        echo "<td id='td_sel[{$did}][{$gid}]' bgcolor='{$strSelColor}'>";
*///                        if ($intSel && ($_POST['sel_type'])) {
//                                existsScheduleGroup();
//                        }
/*                        echo "<select name='sel_courses[{$did}][{$gid}]' style='width:100%;' onChange=\"javascript:document.getElementById('td_sel[{$did}][{$gid}]').bgColor = (this.value != 0) ? '{$arrDep['color']}' : '{$strColor}';invert({$did}, {$gid})\">";
                        echo "<option value='0'>- нет -</option>";
                        foreach ($arrDep['courses'] as $cid => $title) {
                                if ($intSel == $cid) {
                                        $strSelected = "selected";
                                        if (isset($_POST['ch_make_schedule']) && ($_POST['sel_type'])) {
                                                $arrTmp = autoSchedule($cid, $gid, $_POST['sel_type']);
                                                $cnt += $arrTmp['ok'];
                                                $cntExists += $arrTmp['already'];
                                        }
                                } else {
                                        $strSelected = "";
                                }
                                echo "<option value='{$cid}' {$strSelected}>{$title}</option>";
                        }
                        echo "</select>";
*///                        echo "<span id='sp_sel[{$did}][{$gid}]'><b>занятий: 0</b></span>";
/*                        echo "<span id='sp_sel[{$did}][{$gid}]'></span>";
                }
                echo "</td>\n</tr>
                ";
        }
        echo "</table>";
        echo "
        <script language='JavaScript'>
        for (i=1; i<document.all.length; i++) {
                spName = document.all[i].id;
                if (spName) {
                        if (spName.indexOf('sp_sel') != -1) {
                                tdName = spName.replace('sp', 'td');
                                if (tdName) document.all[i].style.color=(16777215-parseInt(document.getElementById(tdName).bgColor.replace('#', '0x')));
                        }
                }
        }

        function invert(did, gid)
        {
                document.getElementById('sp_sel['+did+']['+gid+']').style.color=(16777215-parseInt(document.getElementById('td_sel['+did+']['+gid+']').bgColor.replace('#', '0x')));
        }
        </script>
        ";
        if ($intNumCols > 1) {
*/?>
<table width="100%" border="0" cellspacing="1" cellpadding="0">
  <tr>
    <td>
    <input name="hid_act" type="hidden" id="hid_act" value="edit_global_schedule"><br>
<?
/*        echo okbutton();
*/?>
    </td>
  </tr>
</table>
<?
/*        }
*/?>
</form>
<?
/*$GLOBALS['controller']->captureStop('m080304');
*/

   $GLOBALS['controller']->setLink('m080305');
   $GLOBALS['controller']->setLink('m080307');

   echo show_tb();
break;

case "new":
   if (!empty($name)){
     $res=sql("INSERT INTO departments (name, application) values (".$adodb->Quote($name).",'".DEPARTMENT_APPLICATION."')","errFM185");
   }
   refresh("$PHP_SELF?$sess");
   sqlfree($res);
break;
case "delete":
   intvals("did");

   delete_department($did);

/*   if (!empty($did)) {
        $res=sql("DELETE FROM departments WHERE did='$did'","errFM185");
        sqlfree($res);
        sql("DELETE FROM departments_soids WHERE did='".(int) $did."'");
        sql("DELETE FROM departments_courses WHERE did='".(int) $did."'");
        sql("DELETE FROM departments_groups WHERE did='".(int) $did."'");
        sql("DELETE FROM departments_tracks WHERE did='".(int) $did."'");
   }
*/
   refresh("$PHP_SELF?$sess");
break;
case "edit":
   intvals("rid did");
   echo show_tb();
   echo ph("{$strHeader}: "._("редактирование свойств"));
   $GLOBALS['controller']->setHelpSection('edit');
   $GLOBALS['controller']->captureFromVar(CONTENT,'tmp',$tmp);
   $GLOBALS['controller']->setHeader(_("Учебная структура: редактирование свойств"));
   $tmp="SELECT * FROM departments WHERE did= '".intval($did)."'";
   $r=sql( $tmp );
   $res=sqlget( $r );
   $owner_did = $res['owner_did'];
   $not_in = $res['not_in'];
   sqlfree($r);

   $sql = "SELECT DISTINCT departments_groups.gid as soid, groupname.name
           FROM departments_groups
           INNER JOIN groupname ON (groupname.gid=departments_groups.gid)
           WHERE departments_groups.did='".(int) $did."'";
   $r = sql($sql);
   while($row = sqlget($r)) $orgunits[$row['name']] = $row['soid'];

   $color= $res[ color ];
   if($color=="" ) $color="white"; else $color=$res[ color ];
//   $color="white";
   $tmp="<script type=\"text/javascript\" language=\"JavaScript\" src=\"{$sitepath}js/roles.js\"></script>";
   $tmp.="<form action=$PHP_SELF method=post onSubmit=\"select_list_select_all('orgunits'); select_list_select_all('courses');\">
   <input type=hidden name=c value=\"post_edit\">
   <input type=hidden name=did value='$did'>";

   $divs=getDivs( $res[ did ], $res[ owner_did ] );
   $sajax_javascript .=
    "
    function show_user_select(html) {
        var elm = document.getElementById('people');
        if (elm) elm.innerHTML = '<select id=\"mid\" name=\"mid\" style=\"width: 100%\">'+html+'</select>';
    }

    function get_user_select(str) {

        var elm = document.getElementById('people');
        if (elm) elm.innerHTML = '<select style=\"width: 100%\"><option>"._("Загружаю данные...")."</option></select>';

        x_search_people_unused(str, ".(int) $res['mid'].", show_user_select);
    }

    function show_group_select(html) {
        var elm = document.getElementById('orgunits_all');
        if (elm) elm.innerHTML = ''+html+'';
    }

    function get_group_select(value) {

        var elm = document.getElementById('orgunits_all');
        if (elm) {
            elm.innerHTML = '<option>"._("Загружаю данные...")."</option>';
            x_getParentGroupsOptions(value, '$did', show_group_select);
        }
    }

    function show_course_select(html) {
        var elm = document.getElementById('courses_all');
        if (elm) elm.innerHTML = ''+html+'';
    }

    function get_course_select(value) {

        var elm = document.getElementById('courses_all');

        if (elm) {
            elm.innerHTML = '<option>"._("Загружаю данные...")."</option>';

            x_getParentCoursesOptions(value, '$did', show_course_select);
        }
    }
   ";
   $tmp .= "<script type=\"text/javascript\">
            <!--
            {$sajax_javascript}
            //-->
            </script>";
   $tmp.="<table width=100% class=main cellspacing=0>
   <tr>
   <td nowrap>"._("Название")."</td><td ><input type=text name=name value=\"".htmlspecialchars($res[ name ])."\"></td>
   </tr>
   <tr>
   <td>"._("Входит в")."</td><td > $divs&nbsp;&nbsp;" . $GLOBALS['tooltip']->display('departments_parent') ."</td>
   </tr>
   <tr>
   <td>"._("В должности")."</td><td >";
   $search = '';
   if (get_people_count()<ITEMS_TO_ALTERNATE_SELECT) $search = '*';
   $tmp .= "
   <input type=\"button\" value=\""._("Все")."\" style=\"width: 10%\" onClick=\"if (elm = document.getElementById('search_people')) elm.value='*'; get_user_select('*');\">
   <input type=\"text\" id=\"search_people\" value=\"{$search}\" style=\"width: 300\" onKeyUp=\"if (typeof(filter_timeout)!='undefined') clearTimeout(filter_timeout); filter_timeout = setTimeout('get_user_select(\''+this.value+'\');',1000);\">&nbsp;&nbsp;" . $GLOBALS['tooltip']->display('departments_person') ."<br>
   ";
   $tmp.="<div id=\"people\">";
   $tmp.="<SELECT id=\"mid\" name='mid'>";
   $tmp.=search_people_unused($search, $res['mid']);
   //$tmp.="<option value=0>- укажите -</option>".peopleSelect( "deans", $res[ mid ],"",true,true,$where);
   $tmp.="</SELECT>";// как задать только одного?";
   $tmp.="</div>";
   $tmp.="</td>
   </tr>
   <tr>
   <td>"._("Описание")."</td>
   <td ><textarea name='info' rows=5 cols=30>".$res[ info ]."</textarea></td>
   </tr>
   <script type=\"text/javascript\">
   <!--
   jQuery('select[name=\"owner_did\"]').change(function() {get_group_select(jQuery(this).get(0).value); get_course_select(jQuery(this).get(0).value);})
   //-->       
   </script>";

   //$tmp.="<tr>
   //<td >Цвет</td><td >";
   //$tmp.=getPalette( $color )."</td></tr>";

   $parentGroups = false;
   if ($owner_did) {
       $parentGroups = getParentGroups($owner_did);
   }


   $sql = "SELECT gid as soid,name FROM groupname WHERE cid='-1' ORDER BY name";
   $res = sql($sql);
   while($row = sqlget($res)) {
       if (is_array($parentGroups) && !isset($parentGroups[$row['soid']])) continue;
       $orgunits_all[$row['name']] = $row['soid'];
   }
   if (is_array($orgunits_all) && count($orgunits_all) && is_array($orgunits) && count($orgunits))
     $orgunits_all = array_diff($orgunits_all,$orgunits);

   /**
   * Оргединицы из структуры организации
   */
   $tmp.= "<tr><td nowrap>"._("Курирует группы")."</td><td width=100%><table width=100% border=0 cellspacing=0 cellpadding=0><tr><td width=50%>";
   $tmp.="<select name=\"orgunits_all\" id=\"orgunits_all\" size=5 multiple style=\"width:100%;\">";
   if (is_array($orgunits_all)) foreach($orgunits_all as $k=>$v) $tmp.="<option value=\"{$v}\"> {$k}</option>";
   $tmp.="</select>";
   $tmp.="</td><td nowrap>";
   $tmp.="<input type=\"button\" value=\">>\" onClick=\"select_list_move('orgunits_all','orgunits','select_list_cmp_by_text');\"><br>";
   $tmp.="<input type=\"button\" value=\"<<\" onClick=\"select_list_move('orgunits','orgunits_all','select_list_cmp_by_text')\">";
   $tmp.="</td><td width=50%>";
   $tmp.="<select name=\"orgunits[]\" id=\"orgunits\" size=5 multiple style=\"width:100%;\">";
   if (is_array($orgunits)) foreach($orgunits as $k=>$v) $tmp.="<option value=\"{$v}\"> {$k}</option>";
   $tmp.="</select>";
   $tmp.="</td><td>" . $GLOBALS['tooltip']->display('departments_groups') . "</td></tr>
   <tr><td colspan=4><input type=\"checkbox\" name=\"not_in\" value=\"1\"";
   if ($not_in) $tmp.="checked";
   $tmp.="> "._("всех пользователей вне групп")."</td></tr></table></td></tr>";

   /**
   * Связанные специальности
   */
   /*
   if (is_specialities_exists()) {

       $sql = "SELECT DISTINCT departments_tracks.track as track, tracks.name
               FROM departments_tracks
               INNER JOIN tracks ON (tracks.trid=departments_tracks.track)
               WHERE departments_tracks.did='".(int) $did."'";
       $res = sql($sql);
       while($row = sqlget($res)) {
           $speciality[$row['track']] = $row['name'];
       }

       $sql = "SELECT trid, name FROM tracks ORDER BY name";
       $res = sql($sql);
       while($row = sqlget($res)) {
           $speciality_all[$row['trid']] = $row['name'];
       }

       if (is_array($speciality_all) && count($speciality_all) && is_array($speciality) && count($speciality))
       $speciality_all = array_diff($speciality_all,$speciality);

       $tmp.= "<tr><td>Специальности:</td><td><table border=0 cellspacing=0 cellpadding=0><tr><td>";
       $tmp.="<select name=\"speciality_all\" id=\"speciality_all\" size=5 multiple style=\"width:200px;\">";
       if (is_array($speciality_all)) foreach($speciality_all as $k=>$v) $tmp.="<option value=\"{$k}\"> {$v}</option>";
       $tmp.="</select>";
       $tmp.="</td><td>";
       $tmp.="<input type=\"button\" value=\">>\" onClick=\"select_list_move('speciality_all','speciality','select_list_cmp_by_text');\"><br>";
       $tmp.="<input type=\"button\" value=\"<<\" onClick=\"select_list_move('speciality','speciality_all','select_list_cmp_by_text')\">";
       $tmp.="</td><td>";
       $tmp.="<select name=\"speciality[]\" id=\"speciality\" size=5 multiple style=\"width:200px;\">";
       if (is_array($speciality)) foreach($speciality as $k=>$v) $tmp.="<option value=\"{$k}\"> {$v}</option>";
       $tmp.="</select>";
       $tmp.="</td></tr></table></td></tr>";
   }
   */

   /**
   * Связанные курсы
   */
   $courses = array();
   $sql = "SELECT DISTINCT departments_courses.cid AS cid, Courses.Title AS Title
           FROM departments_courses
           INNER JOIN Courses ON (Courses.CID=departments_courses.cid)
           WHERE departments_courses.did='".(int) $did."'";
   $res = sql($sql);
   while($row = sqlget($res)) {
       $courses[$row['cid']] = $row['Title'];
   }

   $parentCourses = false;
   if ($owner_did) {
       $parentCourses = getParentCourses($owner_did);
   }

   $sql = "SELECT * FROM Courses WHERE is_poll = 0 ORDER BY Title";
   $res = sql($sql);
   while($row = sqlget($res)) {
       if (is_array($parentCourses) && !isset($parentCourses[$row['CID']])) continue;
       if (isset($row['type']) && $row['type']) {
           continue;
       }
       $courses_all[$row['CID']] = $row['Title'];
   }

   if (is_array($courses_all) && count($courses_all) && is_array($courses) && count($courses))
   $courses_all = array_diff($courses_all,$courses);

   $tmp.= "<tr><td nowrap>"._("Курирует курсы")."</td><td><table border=0 cellspacing=0 cellpadding=0><tr><td width=50%>";
   $tmp.="<select name=\"courses_all\" id=\"courses_all\" size=5 multiple style=\"width:100%;\">";
   if (is_array($courses_all)) foreach($courses_all as $k=>$v) $tmp.="<option value=\"{$k}\"> {$v}</option>";
   $tmp.="</select>";
   $tmp.="</td><td nowrap>";
   $tmp.="<input type=\"button\" value=\">>\" onClick=\"select_list_move('courses_all','courses','select_list_cmp_by_text');\"><br>";
   $tmp.="<input type=\"button\" value=\"<<\" onClick=\"select_list_move('courses','courses_all','select_list_cmp_by_text')\">";
   $tmp.="</td><td width=50%>";
   $tmp.="<select name=\"courses[]\" id=\"courses\" size=5 multiple style=\"width:100%;\">";
   if (is_array($courses)) foreach($courses as $k=>$v) $tmp.="<option value=\"{$k}\"> {$v}</option>";
   $tmp.="</select>";
   $tmp.="</td><td>" . $GLOBALS['tooltip']->display('departments_courses') . "</td></tr></table></td></tr>";
   $tmp.="</table><br>";
   $tmp.="
<table border=\"0\" cellspacing=\"5\" cellpadding=\"0\" width=\"100%\">
      <tr>
        <td align=\"right\" width=\"99%\">
        ".okbutton()."
        </td>
        <td align=\"right\" width=\"1%\">
        <div style='float: right;' class='button'><a href='{$GLOBALS['sitepath']}departments.php'>"._("Отмена")."</a></div><input type='button' value='отмена' style='display: none;'/><div class='clear-both'></div>
        </td>
      </tr>
</table>
   </form>";
   $tmp.="</form>";
   $GLOBALS['controller']->captureStop(CONTENT);
   //$GLOBALS['controller']->captureFromVar('m080302','tmp',$tmp);

   //$tmp.=getCoursesGant( "SELECT * FROM Courses WHERE did LIKE '".(int) $did."'", TRUE, FALSE );

//   $tmp.=getDepartmentCourses( $did );
   echo $tmp;
   $GLOBALS['controller']->captureStop(CONTENT);
   echo show_tb();
   return;

case "post_edit":

   intvals("did");
   
   if ($_POST['mid'] == $GLOBALS['s']['mid']) {
       $GLOBALS['controller']->setMessage(_('Нельзя задавать область ответственности самому себе'), JS_GO_URL, "$PHP_SELF?c=edit&did=$did");
       $GLOBALS['controller']->terminate();              
       exit();
   }   
    
   // ==============================
   /**
   * Проверка на зацикливание кафедр
   */
   if ($did && isDeparmentsCycle($did,$owner_did)) $owner_did = '';
   // ==============================
   $_query = "SELECT mid FROM departments WHERE did = {$did}";
   $_res = sql($_query);
   $_row = sqlget($_res);
   $mid_was_changed = ($_row['mid'] != $_POST['mid']) ? true : false;

   if ($did) {
       $rq = "  UPDATE departments
                    SET name=".$adodb->Quote($name).",
                    mid = '{$_POST['mid']}',
                    info = ".$adodb->Quote($info).",
                    color = '$color',
                    owner_did = '".intval($owner_did)."',
                    not_in = '".(int) $_POST['not_in']."',
                    application = '".DEPARTMENT_APPLICATION."'
                WHERE did= $did";
   }
   else {
        $rq = " INSERT INTO departments (name, mid, info, color, owner_did, not_in, application)
                VALUES (
                    ".$adodb->Quote($name).", 
                    '{$_POST['mid']}', 
                    ".$adodb->Quote($info).", 
                    '$color', 
                    '".intval($owner_did)."', 
                    '".(int) $_POST['not_in']."', 
                    '".DEPARTMENT_APPLICATION."'
                )";
   }
   
   $res = sql($rq,"errGR138");
   $did = $did ? $did : sqllast();
   sqlfree($res);

   /*
   sql("DELETE FROM departments_tracks WHERE did='".(int) $did."'");
   if (is_specialities_exists() && is_array($_POST['speciality'])) {
       foreach($_POST['speciality'] as $v) {
           $sql = "INSERT INTO departments_tracks (did,track) VALUES ('".(int) $did."','".(int) $v."')";
           sql($sql);
       }
   }
   */

   sql("DELETE FROM departments_courses WHERE did='".(int) $did."'");
   if (is_array($_POST['courses'])) {
       foreach($_POST['courses'] as $v) {
           $sql = "INSERT INTO departments_courses (did,cid) VALUES ('".(int) $did."','".(int) $v."')";
           sql($sql);
       }
   }

   if ($mid_was_changed) {
   	$query_ = "SELECT Courses.CID FROM Courses WHERE Courses.createby = '{$_POST['mid']}'";
   	$res_ = sql($query_);
   	if (sqlrows($res_)) {
   		while ($row_ = sqlget($res_)) {
   			if (!@in_array($row_['CID'], $_POST['courses'])) {
   				sql("INSERT INTO departments_courses (did,cid) VALUES ('{$did}','{$row_['CID']}')");
   			}
   		}
   	}
   }

   sql("DELETE FROM departments_groups WHERE did='".(int) $did."'");
   if (is_array($_POST['orgunits']) && count($_POST['orgunits']) && $did) {
       foreach($_POST['orgunits'] as $v) {
           $sql = "INSERT INTO departments_groups (did,gid) VALUES ('".(int) $did."','".(int) $v."')";
           sql($sql);
       }
   }
   refresh("$PHP_SELF?$sess");
   return;

}

/**
* Проверка на зацикливание кафедр
*/
function isDeparmentsCycle($depId, $ownerId) {

    if (empty($ownerId) || $ownerId == NULL) return false;
    $q = "SELECT owner_did FROM departments WHERE did=".(int) $ownerId;
    $res = sql($q);
    while ($r = sqlget($res)) {

        if ($r['owner_did'] == $depId) return true;
        if ($r['owner_did'] == NULL) return false;
        sqlfree($res);

        $q = "SELECT owner_did FROM departments WHERE did=".(int) $r['owner_did'];
        $res = sql($q);

    }
}

function getDivs( $self_did, $owner_did ){
  $tmp="SELECT * FROM departments WHERE `application` = '".DEPARTMENT_APPLICATION."'";
  $r=sql( $tmp );

 $divs="<SELECT name='owner_did'>";
 $divs.="<option value=0> - "._("укажите")." -</option>";
  while( $res=sqlget( $r ) ){
   if( $res[ did ] != $self_did ){
     if( $res[ did ] == $owner_did ) $sel=" selected "; else $sel="";
     $divs.="<option value=".$res[ did ] ." $sel>".$res[ name ]."</option>";
   }
  }
   $divs.="</SELECT>";// как задать только одного?";
  sqlfree($r);

//      $rq="ALTER TABLE departments ADD owner_did int";
//      $res=sql( $rq,"ERR upgrading $table");


 return( $divs );
}


function show_structure( $divs ){
if (is_array($divs)) {
  foreach( $divs as $div ){
    $sh="";
    for($i=0;$i<$div[ level ];$i++)
      $sh.="--";

    $tmp.=$sh.$div[ name ]."<BR>";
  }
}
  return( $tmp );
}


function show_sublevel( $divs, $did, $sh="" ){
        if (is_array($divs)) {
  foreach( $divs as $r ){
    if( $r[ owner_did ] == $did ){
     if( $did == 0 ){ $b="<B>"; $bb="</b>"; } else{ $b=""; $bb="";}
     if($r[color]>""){ $color=$r[color]; $pic=""; }else{ $color="white"; $pic="-";}
      $tmp.="<tr>
            <!--<td style='background:$color' width=30 align='center'>$pic</td>-->
            <td> $sh
                 $b $r[name] $bb
            </td>
            <td>".getpeoplename( $r[mid] )."</td>
            <td>$r[info]</td>
            <td  align='center'>";
      
      if ($GLOBALS['s']['mid']!=$r['mid']) {
          if (!$GLOBALS['noperm2edit4dean']) {
              $tmp.="
                    <a href=$PHP_SELF?c=edit&did=$r[did]$sess>".getIcon('edit', _('Редактировать элемент стструктуры'))."</a>
                    <a href=$PHP_SELF?c=delete&did=$r[did]$sess
                    onclick=\"if (!confirm('"._("Вы действительно желаете удалить элемент структуры?")."')) return false;\" >".getIcon("delete", _('Удалить элемент стструктуры'))."</a>";
          }
      }else {
          $tmp.="<img tooltip-url='{$GLOBALS['sitepath']}/help/context/tooltips/tooltipLoader.php?file=departments.tpl'
                      class='tooltip-link' 
                      src='{$GLOBALS['sitepath']}/template/smarty/skins/default/images/tooltip/tooltip.gif' 
                 />";
      }

      $tmp.='</td></tr>'.show_sublevel( $divs, $r[did], $sh.".." )."<P/>";
    }
  }
        }
  return( $tmp );
}

function get_structure_departments( ){

  $tmp="SELECT * FROM departments WHERE `application` = '".DEPARTMENT_APPLICATION."'";
  $res=sql( $tmp );

  while( $r=sqlget( $res ) ){
     $divs[ $r[ did ] ][ did ]= $r[ did ];
     $divs[ $r[ did ] ][ owner_did ]= $r[ owner_did ];
     $divs[ $r[ did ] ][ name ]= $r[ name ];
     $divs[ $r[ did ] ][ color ]= $r[ color ];
     $divs[ $r[ did ] ][ mid ]= $r[ mid ];
     $divs[ $r[ did ] ][ info ]= $r[ info ];
  }
  sqlfree($r);
  return( $divs );
}

function get_structure_departments_level( $divs, $div, $i=0 ){
  // check infinite loop
  $i++;
  if( ( $divs[ $div ][ owner_did ] > 0 ) && ( $i < count ( $divs ) ) ){
     $level=get_structure_departments_level( $divs, $divs[ $div ][ owner_did ], $i ) + 1;
//     echo "level= $level !! ";
  }else
    $level = 0;
  return( $level );
}

function set_structure_departments_levels( &$divs ){
if (is_array($divs)) {
  foreach( $divs as $div ){
//    echo $div[ did ]."; ";
    $level = get_structure_departments_level( $divs, $div[ did ] );
    $divs[ $div[ did ] ] [ level ] = $level;
    $divs[ $div[ did ] ] [ org ] = $i++;
  }
}
}

function org_structure_departments_levels( &$divs ){
if (is_array($divs)) {
  foreach( $divs as $div ){
//    echo $div[ did ]."; ";
    $level = get_structure_departments_level( $divs, $div[ did ] );
    $divs[ $div[ did ] ] [ level ] = $level;
  }
}
}

function delete_department($did) {
    if ($did) {
        $sql = "SELECT did FROM departments WHERE owner_did='".(int) $did."'";
        $res = sql($sql);
        while($row = sqlget($res)) {
            if ($row['did']) {
                delete_department($row['did']);
            }
        }

        $sql = "SELECT chain FROM chain_item WHERE `type` = '2' AND item = '".(int) $did."'";
        $res = sql($sql);

        while($row = sqlget($res)) {
            if ($row['chain']) {
                $place = 0;
                sql("DELETE FROM chain_item WHERE `type` = '2' AND item = ".(int) $did);
                $sql = "SELECT id as id, place FROM chain_item WHERE chain = '".(int) $row['chain']."' ORDER BY place";
                $res2 = sql($sql);

                while($row2 = sqlget($res2)) {
                    sql("UPDATE chain_item SET place = '".$place."' WHERE id = ".(int) $row2['id']);
                    $place++;
                }
            }
        }

        sql("DELETE FROM departments WHERE did='".(int) $did."'");
        sql("DELETE FROM departments_soids WHERE did='".(int) $did."'");
        sql("DELETE FROM departments_courses WHERE did='".(int) $did."'");
        sql("DELETE FROM departments_groups WHERE did='".(int) $did."'");
        sql("DELETE FROM departments_tracks WHERE did='".(int) $did."'");
    }
}

function search_people_unused($search, $current) {
    $html = '';
    $html .= "<option value=0>- "._("укажите")." -</option>";
    if ($current>0) {
        $sql = "SELECT People.MID, People.LastName, People.FirstName, People.Patronymic, People.Login
                FROM People
                WHERE People.MID = '".(int) $current."'";
        $res = sql($sql);
        if ($row = sqlget($res)) {
            $html .= "<option selected value='".(int) $row['MID']."'> ".htmlspecialchars($row['LastName'].' '.$row['FirstName'].' '.$row['Patronymic'].' ('.$row['Login'].')',ENT_QUOTES)."</option>";
            $html .= "<option value=0> ------</option>";
        }
    }
    if (!empty($search)) {
        $search = iconv('UTF-8',$GLOBALS['controller']->lang_controller->lang_current->encoding,unicode_urldecode($search));
        $search = trim($search);
        $search = str_replace('*','%',$search);
        $where = "AND (People.LastName LIKE '%".addslashes($search)."%'
        OR People.FirstName LIKE '%".addslashes($search)."%'
        OR People.Login LIKE '%".addslashes($search)."%') AND People.MID NOT IN ('".(int) $current."')";
        $html .= peopleSelect( "deans", $current,"",true,true,$where);
    }
    return $html;
}

function getParentGroups($did)
{
    $ret = array();
    $sql = "SELECT gid FROM departments_groups WHERE did = '".(int) $did."'";
    $res = sql($sql);
    while($row = sqlget($res)) {
        $ret[$row['gid']] = $row['gid'];
    }
    return $ret;

}

function getParentCourses($did)
{
    $ret = array();
    $sql = "SELECT cid FROM departments_courses WHERE did = '".(int) $did."'";
    $res = sql($sql);
    while($row = sqlget($res)) {
        $ret[$row['cid']] = $row['cid'];
    }
    return $ret;

}

function getParentGroupsOptions($did, $current = 0) {
    $ret = '';
    $gids = getParentGroups($current);

    $sql = "SELECT gid, name FROM groupname ORDER BY name";
    if ($did)
    $sql = "SELECT t1.gid, t1.name  FROM groupname t1 INNER JOIN  departments_groups t2 ON (t2.gid = t1.gid) WHERE t2.did = '".(int) $did."' ORDER BY t1.name";
    $res = sql($sql);
    while($row = sqlget($res)) {
        if (isset($gids[$row['gid']])) continue;
        $ret .= "<option value=\"{$row['gid']}\"> ".htmlspecialchars($row['name'])."</option>";
    }
    return $ret;

}

function getParentCoursesOptions($did, $current = 0)
{
    $ret = '';
    $cids = getParentCourses($current);
    $sql = "SELECT CID, Title FROM Courses WHERE is_poll = '0' ORDER BY Title";
    if ($did)
    $sql = "SELECT t1.CID, t1.Title FROM Courses t1 INNER JOIN departments_courses t2 ON (t2.cid = t1.CID) WHERE t2.did = '".(int) $did."' ORDER BY Title";
    $res = sql($sql);
    while($row = sqlget($res)) {
        if (isset($cids[$row['CID']])) continue;
        $ret .= "<option value=\"{$row['CID']}\"> ".htmlspecialchars($row['Title'])."</option>";
    }
    return $ret;

}



?>