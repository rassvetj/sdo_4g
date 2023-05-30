<?php
require_once('1.php');

istest();

require_once('courses.lib.php');
require_once('news.lib.php4');
require_once("metadata.lib.php");
require_once("lib/classes/Credits.class.php");
require_once("lib/classes/Chain.class.php");
require_once('lib/classes/CCourseAdaptor.class.php');
require_once('Pager/examples/Pager_Wrapper.php');

require_once 'Archive/Zip.php';
require_once 'HTTP.php';
require_once 'HTTP/Download.php';

if ((isset($delete) || isset($redCID) || isset($chCID)) && !isset($s[tkurs][$delete]) && $s[perm]<3) {
    $GLOBALS['controller']->setMessage(_('У вас не хватает прав на этот курс'));
    //exit("нет прав на этот курс");
}

$GLOBALS['is_perm_add'] = $GLOBALS['controller']->checkPermission(COURSE_PERM_MANAGE);
$GLOBALS['is_perm_edit'] = $GLOBALS['controller']->checkPermission(COURSE_PERM_MANAGE);
$GLOBALS['is_perm_del'] = $GLOBALS['controller']->checkPermission(COURSE_PERM_MANAGE);
if ($GLOBALS['is_perm_add']) $GLOBALS['controller']->setLink('m060101');

$redCID=(isset($_GET['redCID'])) ? intval($_GET['redCID']) : "";
$delete_id=(isset($_GET['delete'])) ? intval($_GET['delete']) : "";
$copy_id=(isset($_GET['copy'])) ? intval($_GET['copy']) : "";
$download_id=(isset($_GET['download'])) ? intval($_GET['download']) : "";
$export_id = (isset($_GET['export'])) ? (int) $_GET['export'] : 0;
$lock_id   = (int) $_GET['lock'];
$unlock_id = (int) $_GET['unlock'];
$Action=(isset($_POST['Action'])) ? $_POST['Action'] : "";
$Fee=(isset($_POST['Fee'])) ? $_POST['Fee'] : "";
if ($_POST['statusOptions']) {
    switch ($_POST['statusOptions']) {
        case 1:
            $is_module_need_check = 0;
            $sequence = 0;
        break;
        case 2:
            $is_module_need_check = 1;
            $sequence = 0;
        break;
        case 3:
            $is_module_need_check = 0;
            $sequence = 1;
        break;
    }
}
//$is_module_need_check=(isset($_POST['is_module_need_check'])) ? 1 : 0;

if ($redCID > 0) {
    $GLOBALS['controller']->setView('Document');
}

/**
 * Проверки на блокирование курса
 */
$is_locked = false;
if ($redCID && is_course_locked($redCID)) {
    $redCID = 0; $is_locked = true;
}
if ($delete_id && is_course_locked($delete_id)) {
    $delete_id = 0; $is_locked = true;
}
if ($is_locked) {
    $GLOBALS['controller']->setView('DocumentBlank');
    $GLOBALS['controller']->setMessage(_("Курс заблокирован. Данная операция невозможна"),JS_GO_URL,$sitepath.'courses.php4');
    $GLOBALS['controller']->terminate();
    exit();
}

$b_year=(isset($_POST['year'])) ? $_POST['year'] : date("Y");
$e_year=(isset($_POST['year2'])) ? $_POST['year2'] : date("Y");

$b_day=(isset($_POST['day'])) ? $_POST['day'] : date("d");
$e_day=(isset($_POST['day2'])) ? $_POST['day2'] : date("d");

$b_month=(isset($_POST['month'])) ? $_POST['month'] : date("m");
$e_month=(isset($_POST['month2'])) ? $_POST['month2'] : date("m");

$b_tmp = mktime(0, 0, 0, intval($b_month), intval($b_day), intval($b_year));
$e_tmp = mktime(0, 0, 0, intval($e_month), intval($e_day), intval($e_year));

$longtime = (isset($_POST['longtime'])) ? abs(intval($_POST['longtime'])) : 0;
$longtime_tmp = $longtime*24*3600;

$Title=(isset($_POST['Title'])) ? $_POST['Title'] : "";
$Description=(isset($_POST['Description'])) ? $_POST['Description'] : "";
$createby=(isset($_POST['createby'])) ? $_POST['createby'] : "";

$Status=(isset($_POST['Status'])) ? $_POST['Status'] : 0;
$TypeDes=(isset($_POST['TypeDes'])) ? $_POST['TypeDes'] : 0;
$chain = (int) $_POST['chain'];

$chCID=(isset($_POST['chCID'])) ? $_POST['chCID'] : "";

$s[user][csort]=(isset($s[user][csort])) ? $s[user][csort] : 1;
$s[user][corder]=(isset($s[user][corder])) ? $s[user][corder] : 1;
$csort=(isset($_GET['csort'])) ? intval($_GET['csort']) : "";

if ($csort==$s[user][csort])
   $s[user][corder]=($s[user][corder]==1) ? 2 : 1;
if ($csort)
   $s[user][csort]=$csort;

$createdate=$adodb->DBDate(date("Y-m-d"));
$Fee=intval($Fee);
//$Title=return_valid_value($Title);
//$Description=return_valid_value($Description);
$createby=return_valid_value($createby);
$cBegin=$adodb->DBDate($b_year."-".$b_month."-".$b_day);
$cEnd=$adodb->DBDate($e_year."-".$e_month."-".$e_day);

$error="";
$cool="";

if (isset($_GET['make']))
   $and_complete = $make;

if($copy_id) {
   copy_course($copy_id);
   $cool .= _("Курс успешно скопирован").'<br>';
}
if($download_id) {
   download_course($download_id);
}

if ($export_id) {
    export_course($export_id);
}

if ($lock_id && $GLOBALS['is_perm_edit']) {
    CCourseAdaptor::lock($lock_id);
}

if ($unlock_id && $GLOBALS['is_perm_edit']) {
    CCourseAdaptor::unlock($unlock_id);
}

function getSortType() {
   global $s;
   $ret="CID ";
   if (2==$s[user][csort])
      $ret="Title ";
   if (3==$s[user][csort])
      $ret="Fee ";
   if (4==$s[user][csort])
      $ret="Status ";
   if (5==$s[user][csort])
      $ret="TypeDes ";
   if (2==$s[user][corder])
      $ret.="DESC";
   else
      $ret.="ASC";
   $ret=" ORDER by ".$ret;
   return $ret;
}

function show_header() {
   $name=_("Направления, специальности и курсы");
   $static=show_info_block( 0, "[ALL-CONTENT]", "-~courses~-"  );// выводит информацию блоками
   $courses_header=loadtmpl("all-cHeader.html");
   $courses_header=str_replace("[W-PAGESTATIC]",$static,$courses_header);
   $courses_header=str_replace("[W-PAGENAME]",$name,$courses_header);
   return $courses_header;
}

function show_form_header() {
   $addcourse=loadtmpl("courses-addcourse.html");
   return $addcourse;
}

function get_bologna_system_fields($row) {
    $ret =
    "
    <tr>\n
        <td>"._("Кредиты преподавателя:")." </td>\n
        <td><input type=text size=3 value=\"{$row['credits_teacher']}\" name=\"credits_teacher\"></td>\n
    </tr>\n
    <tr>\n
        <td>"._("Кредиты обучаемого:")." </td>\n
        <td><input type=text size=3 value=\"{$row['credits_student']}\" name=\"credits_student\"></td>\n
    </tr>\n
    ";
    return $ret;
}

function course_reg_form() {
   $day1="";
   $month1="";
   $year1="";
   $longtime=100;
   $time2=time()+($longtime+20)*24*3600;
   $_POST = $_SESSION['course_edit'];
   unset($_SESSION['course_edit']);
   $date1 = array("dd" => $_POST['day'] ? $_POST['day'] : date("d", time()), "mm" => $_POST['month'] ? $_POST['month'] : date("m", time()), "yyyy" => $_POST['year'] ? $_POST['year'] : date("Y", time()));
   $date2 = array("dd" => $_POST['day2'] ? $_POST['day2'] : date("d", $time2), "mm" => $_POST['month2'] ? $_POST['month2'] : date("m", $time2), "yyyy" => $_POST['year2'] ? $_POST['year2'] : date("Y", $time2));

   for($j="1"; $j<32; $j++) {
      $day1.="<option value=".day($j)." ".(($j==$date1['dd'])?"selected":"").">".day($j)."</option>\n";
      $day2.="<option value=".day($j)." ".(($j==$date2['dd'])?"selected":"").">".day($j)."</option>\n";
   }
   for($j="1";$j<13;$j++) {
      $month1.="<option value=".day($j)." ".(($j==$date1['mm'])?"selected":"").">".month($j)."</option>\n";
      $month2.="<option value=".day($j)." ".(($j==$date2['mm'])?"selected":"").">".month($j)."</option>\n";
   }
   for($j=date("Y")-1;$j-8<date("Y");$j++) {
      $year1.="<option value=".$j." ".(($j==$date1['yyyy'])?"selected":"").">".$j."</option>\n";
      $year2.="<option value=".$j." ".(($j==$date2['yyyy'])?"selected":"").">".$j."</option>\n";
   }

   if ($GLOBALS['controller']->enabled) $form = loadtmpl("courses-editcourse-dummy.html");
   else $form = loadtmpl("courses-editcourse.html");
   
    $arrMeta = load_metadata(COURSES_DESCRIPTION);
    foreach ($arrMeta as $a) {
         $arrMetaNames[] = $a['name'];
    }
    $strDescMeta = set_metadata($_POST, $arrMetaNames, COURSES_DESCRIPTION);
   
   $desc=edit_metadata( read_metadata ( $strDescMeta, COURSES_DESCRIPTION ));
   for($j=0;$j<3;$j++) {    
       $n_status.="<option value=".$j." ".(($j==$_POST['Status']) ? 'selected' : '').">".get_course_status($j)."</option>\n";
   }
   
   
   $form=str_replace("[TITLE]",isset($_POST['Title']) ? $_POST['Title'] : '',$form);
   $form=str_replace("[STATUS]",$n_status,$form);  
   
   $toolTip = new ToolTip();
   $form=str_replace("[TT4RADIO1]",$toolTip->display('course_edit_radio1'),$form);
   $form=str_replace("[TT4RADIO2]",$toolTip->display('course_edit_radio2'),$form);
   $form=str_replace("[TT4RADIO3]",$toolTip->display('course_edit_radio3'),$form);

   $form=str_replace("[TT4DATE1]",$toolTip->display('course_edit_date1'),$form);
   $form=str_replace("[TT4DATE2]",$toolTip->display('course_edit_date2'),$form);

   $form=str_replace("[TT4GROUPS]",$toolTip->display('course_edit_groups'),$form);    

   $statusOptions = isset($_POST['statusOptions']) ? $_POST['statusOptions'] : 1;
   $form=str_replace("[RADIO{$statusOptions}]",'checked',$form);
   
   $providerOptions = '<option value="0"> '._('Нет').'</option>';
   $providers = getProviders(); 
   $provider = $_POST['provider'];
   if (is_array($providers) && count($providers)) {
       foreach($providers as $item) {
           $providerOptions .= '<option ';
           if ($provider == $item['id']) $providerOptions .= 'selected';
           $providerOptions .= ' value="'.$item['id'].'"> '.$item['title'].'</option>';
       }
   }
   $form=str_replace("[PROVIDERS]",$providerOptions,$form);

   $res = sql("SELECT * FROM rooms2course WHERE cid='".(int) $CID."'");
   $rooms2course = array();
   while($r = sqlget($res)) {
       $rooms2course[] = $r['rid'];
   }

   $res=sql("SELECT * FROM rooms WHERE status > 0 ORDER BY name","errGR73");
   $i=0;
   while ($r=sqlget($res)) {
      $rooms[$i][name]=$r[name];
      $rooms[$i][rid]=$r[rid];
      $rooms[$i][volume]=$r[volume];
      $i++;
   }

     $tmp="";
     $rooms2course = isset($_POST['rooms']) ? $_POST['rooms'] : array();
   if( count ( $rooms ) > 0 ){
     foreach( $rooms as $room )
      $tmp.="<input type='checkbox' ".(in_array($room['rid'],$rooms2course)?'checked':'')." name=\"rooms[]\" value=".$room[rid].">&nbsp;".$room[name]." (".$room[volume].")</input>&nbsp;&nbsp;" . (!$room_count++ ? $GLOBALS['tooltip']->display('course_edit_rooms') : '') . "<BR>";
   }else
      $tmp.="";
   $form=str_replace("[ROOMS]",$tmp,$form);
   
   $did = implode(";", $_POST['departments']);
   $groups = get_structure("courses_groups");
   set_structure_levels($groups);
   $form=str_replace("[GROUPS]","<select multiple style='width: 400px' name=\"departments[]\" size=5><option value=\"0\"> - "._('нет')." -</option>".show_sublevel($groups,0,'',$did)."</select>",$form);

   $form=str_replace("[CANCELBUTTON]",button(_("Отмена"), "", "cancel", "document.location.href=\"{$GLOBALS['sitepath']}courses.php4\";"),$form);
   
   $form=str_replace("[ACTION_NAME]",'change',$form);
   $form=str_replace("[CID]",0,$form);   
   $form=str_replace("[DESCRIPTION]",$desc,$form);   
   $form=str_replace("[DAY]",$day1,$form);
   $form=str_replace("[MONTH]",$month1,$form);
   $form=str_replace("[YEAR]",$year1,$form);
   $form=str_replace("[END_DAY]",$day2,$form);
   $form=str_replace("[END_MONTH]",$month2,$form);
   $form=str_replace("[END_YEAR]",$year2,$form);
   $form=str_replace("[LONGTIME]",isset($_POST['longtime']) ? $_POST['longtime'] : $longtime,$form);
   $form=str_replace("[OKBUTTON]",okbutton(),$form);
   $form=str_replace("[TYPE_DESCR]",get_course_type_selects(isset($_POST['TypeDes']) ? $_POST['TypeDes'] : $type,isset($_POST['chain']) ? $_POST['chain'] : $chain),$form);
   if (defined("USE_BOLOGNA_SYSTEM") && USE_BOLOGNA_SYSTEM)
       $form = str_replace('[BOLOGNA_SYSTEM]',get_bologna_system_fields(),$form);
   else $form = str_replace('[BOLOGNA_SYSTEM]','',$form);

   return $form;
}

function course_change_form( $CID, $res) {
    global $cEnd, $cBegin;
   $_POST = $_SESSION['course_edit'];
   unset($_SESSION['course_edit']);
   
      if ($GLOBALS['controller']->enabled)
      $form=student_alias_parse(loadtmpl("courses-editcourse-dummy.html"));
      else
      $form=student_alias_parse(loadtmpl("courses-editcourse.html"));

      $did=isset($_POST['departments']) ? implode(";", $_POST['departments']) : $res['did'];
      $provider = $res['provider'];
//      $dep_name = getDepartmentName($did);
      $providers = getProviders();            

      /*$deps=getalldepartments();
      if( count( $deps ) > 0  ){
        $deps_list="<SELECT name=department><option value=0>- "._("нет")." -</options>";
        foreach($deps as $dep){
          if($dep[did]==$did) $cc=" selected "; else $cc="";
          $deps_list.="<option value=".$dep[did]." $cc>".$dep[name]."</option>";
        }
        $deps_list.="</SELECT>";
      }else
        $deps_list="";*/
      $b_date=isset($_POST) && count($_POST) ? $_POST['year']."-".$_POST['month']."-".$_POST['day'] : $res['cBegin'];
      $e_date=isset($_POST) && count($_POST) ? $_POST['year2']."-".$_POST['month2']."-".$_POST['day2'] : $res['cEnd'];
      $sequence = $res['sequence'];
      $is_module_need_check = $res['is_module_need_check'];

      $statusOptions = isset($_POST['statusOptions']) ? $_POST['statusOptions'] : $is_module_need_check + $sequence*2 + 1;

      $credits_teacher = $res['credits_teacher'];
      $credits_student = $res['credits_student'];


      $b_day="";
      $b_month="";
      $b_year="";

      $e_day="";
      $e_month="";
      $e_year="";

      $n_type="";
      $n_status="";

      $title=$res['Title'];
//      $description=  $res['Description'] ;
      //$description= get_description($CID, 1 , $res);
      if (isset($_POST) && count($_POST)) {
        $arrMeta = load_metadata(COURSES_DESCRIPTION);
        foreach ($arrMeta as $a) {
             $arrMetaNames[] = $a['name'];
        }
        $strDescMeta = set_metadata($_POST, $arrMetaNames, COURSES_DESCRIPTION);
        $description = edit_metadata( read_metadata ( $strDescMeta, COURSES_DESCRIPTION ));
      }
      else {
        $description = show_description($CID, 3);
      }
      $type= isset($_POST['TypeDes']) ? $_POST['TypeDes'] : $res['TypeDes'];
      $chain = isset($_POST['chain']) ? $_POST['chain'] : $res['chain'];
      $fee=$res['Fee'];
      $valuta=$res['valuta'];
      $status=isset($_POST['Status']) ? $_POST['Status'] : $res['Status'];
      $c_mail=$res['createby'];
      $longtime=isset($_POST['longtime']) ? $_POST['longtime'] : intval($res['longtime']);
      //$is_module_need_check = "<input type='checkbox' name='is_module_need_check'" . (intval($res['is_module_need_check']) ? " checked" : "") . "/>";

      $c_date=mydate($res['createdate']);




      for($j="1";$j<32;$j++)
            {
               $b_temp="";
               $e_temp="";

               if (substr($b_date,8,2)==$j)  $b_temp="selected";
               if (substr($e_date,8,2)==$j)  $e_temp="selected";

               $b_day.="<option value=".day($j)." ".$b_temp.">".day($j)."</option>\n";
               $e_day.="<option value=".day($j)." ".$e_temp.">".day($j)."</option>\n";

            }
      for($j="1";$j<13;$j++)
            {
               $b_temp="";
               $e_temp="";

               if (substr($b_date,5,2)==$j)  $b_temp="selected";
               if (substr($e_date,5,2)==$j)  $e_temp="selected";

               $b_month.="<option value=".day($j)." ".$b_temp.">".month($j)."</option>\n";
               $e_month.="<option value=".day($j)." ".$e_temp.">".month($j)."</option>\n";
            }

      for($j=date("Y")-1;$j-8<date("Y");$j++)
            {
               $b_temp="";
               $e_temp="";

               if (substr($b_date,0,4)==$j)  $b_temp="selected";
               if (substr($e_date,0,4)==$j)  $e_temp="selected";

               $b_year.="<option value=".$j." ".$b_temp.">".$j."</option>\n";
               $e_year.="<option value=".$j." ".$e_temp.">".$j."</option>\n";
            }

      for($j=0;$j<3;$j++)
            {
               $s_temp="";
               $t_temp="";

               if ($status==$j)  $s_temp="selected";
               if ($type==$j)  $t_temp="selected";

               $n_status.="<option value=".$j." ".$s_temp.">".get_course_status($j)."</option>\n";
               $n_type.="<option value=".$j." ".$t_temp.">".get_course_type($j)."</option>\n";
            }
////////////////////////////////////////////////////////////////
   $res = sql("SELECT * FROM rooms2course WHERE cid='".(int) $CID."'");
   $rooms2course = array();
   while($r = sqlget($res)) {
       $rooms2course[] = $r['rid'];
   }
   $rooms2course = isset($_POST['rooms']) ? $_POST['rooms'] : $rooms2course;
   
   $res=sql("SELECT * FROM rooms WHERE status > 0 ORDER BY name","errGR73");
   $i=0;
   while ($r=sqlget($res)) {
      $rooms[$i][name]=$r[name];
      $rooms[$i][rid]=$r[rid];
      $rooms[$i][volume]=$r[volume];
      $i++;
   }

     $tmp="";
   if( count ( $rooms ) > 0 ){
     foreach( $rooms as $room )
      $tmp.="<input type='checkbox' ".(in_array($room['rid'],$rooms2course)?'checked':'')." name=\"rooms[]\" value=".$room[rid].">&nbsp;".$room[name]." (".$room[volume].")</input>&nbsp;&nbsp;" . (!$room_count++ ? $GLOBALS['tooltip']->display('course_edit_rooms') : '') . "<BR>";
   }else
      $tmp.="";
   $form=str_replace("[ROOMS]",$tmp,$form);
   $teach_list="<a href='teachers.php4?CID=$CID'>".get_teachers_list($CID)."</a>";
   $stud_list="<a href='abitur.php4?CID=$CID'>".get_stud_list($CID)."</a>";


   $template_publ = "";
   /*
    if (file_exists($_SERVER['DOCUMENT_ROOT']."/COURSES/course{$CID}/course.xml")) {
    $template_publ = "          <tr>
            <td valign=middle>
              "._("Шаблон и стиль публикации")."
            </td>
            <td valign=middle>";
    $courses_templates = get_courses_templates_and_colors_as_array();
    $template_options = "<select name=template_publ><option value=0>---</option>";
    if (is_array($courses_templates) && count($courses_templates)) {
        foreach ($courses_templates as $key => $value) {
            $template_options .= "<optgroup label='{$value['title']}'>\n";
            if (is_array($value['colors']) && count($value['colors'])) {
                foreach ($value['colors'] as $keycolor => $valuecolor) {
                    $template_options .= "<option value='$key;$keycolor' style='font-weight: bold'>{$valuecolor}</option>\n";
                }
            }
            $template_options .= "</optgroup>";
        }
    }
    $template_options .= "</select>";
    $template_publ .= $template_options;
    $template_publ .= "</td></tr>";
   }
   */      

   $groups = get_structure("courses_groups");
   set_structure_levels($groups);
   $form=str_replace("[GROUPS]","<select multiple style='width: 400px' name=\"departments[]\" size=5><option value=\"0\"> - "._('нет')." -</option>".show_sublevel($groups,0,'',$did)."</select>",$form);
   $form=str_replace("[TEMPLATE_PUBL]",$template_publ,$form);
   $form=str_replace("[DEPARTMENTS]",$deps_list,$form);
   $form=str_replace("[TEACHERS]",$teach_list,$form);
   $form=str_replace("[STUDENTS]",$stud_list,$form);
   $form=str_replace("[DAY]",$b_day,$form);
   $form=str_replace("[MONTH]",$b_month,$form);
   $form=str_replace("[YEAR]",$b_year,$form);
   
   $form=str_replace("[END_DAY]",$e_day,$form);
   $form=str_replace("[END_MONTH]",$e_month,$form);
   $form=str_replace("[END_YEAR]",$e_year,$form);

   $form=str_replace("[ACTION_NAME]",($CID ? 'change' : 'reg'),$form);
   $form=str_replace("[TITLE]",isset($_POST['Title']) ? $_POST['Title'] : $title,$form);
   $form=str_replace("[DESCRIPTION]",$description,$form);
   $form=str_replace("[FEE]",$fee,$form);
   $form=str_replace("[CREATEBY]",$c_mail,$form);
   $form=str_replace("[CREATEDATE]",$c_date,$form);
   $form=str_replace("[LONGTIME]",$longtime,$form);

   $toolTip = new ToolTip();
   $form=str_replace("[TT4RADIO1]",$toolTip->display('course_edit_radio1'),$form);
   $form=str_replace("[TT4RADIO2]",$toolTip->display('course_edit_radio2'),$form);
   $form=str_replace("[TT4RADIO3]",$toolTip->display('course_edit_radio3'),$form);

   $form=str_replace("[TT4DATE1]",$toolTip->display('course_edit_date1'),$form);
   $form=str_replace("[TT4DATE2]",$toolTip->display('course_edit_date2'),$form);

   $form=str_replace("[TT4GROUPS]",$toolTip->display('course_edit_groups'),$form);

   //$form=str_replace("[IS_MODULE_NEED_CHECK]",$is_module_need_check,$form);
   //$form=str_replace('[COURSE_SEQUENCE]',($sequence ? 'checked' : ''),$form);
   $form=str_replace("[RADIO$statusOptions]",'checked',$form);

   $form=str_replace("[STATUS]",$n_status,$form);
   $form=str_replace("[TYPE_DESCR]",get_course_type_selects($type,$chain),$form);


   $form=str_replace("[CID]",$CID,$form);
   $form=str_replace("[OKBUTTON]",okbutton(),$form);
   $form=str_replace("[CANCELBUTTON]",button(_("Отмена"), "", "cancel", "document.location.href=\"{$GLOBALS['sitepath']}courses.php4\";"),$form);


   $provider = isset($_POST['provider']) ? $_POST['provider'] : $provider;
   $providerOptions = '<option value="0"> '._('Нет').'</option>';
   if (is_array($providers) && count($providers)) {
       foreach($providers as $item) {
           $providerOptions .= '<option ';
           if ($provider == $item['id']) $providerOptions .= 'selected';
           $providerOptions .= ' value="'.$item['id'].'"> '.$item['title'].'</option>';
       }
   }
   $form=str_replace("[PROVIDERS]",$providerOptions,$form);
   
   if (defined("USE_BOLOGNA_SYSTEM") && USE_BOLOGNA_SYSTEM)
       $form = str_replace('[BOLOGNA_SYSTEM]',get_bologna_system_fields(array('credits_teacher'=>$credits_teacher,'credits_student'=>$credits_student)),$form);
   else $form = str_replace('[BOLOGNA_SYSTEM]','',$form);

   $html="";
   foreach ($GLOBALS[valuta] as $k=>$v) {
      $html.="<option value=$k ".($k==$valuta?"selected":"").">$v[1]";
   }
   $form=str_replace("[VALUTA]",$html,$form);
   return $form;
}

function all_tracks_list( $mode=1 ){
//   $tmp=ph("Специальности");
   $res=sql("SELECT * FROM tracks ORDER BY trid","errGR73");
   $k=0;
   while ( $r=sqlget($res) ) {
      if( $r['id'] !="" )
        $kod="( ".$r['id']." )";
      else
       $kod="";
      if( $r[status] || $mode ){
         $tmp.=getTrackCourses( $r['trid'], $r['name']." $kod ", TRUE, $r['description'] );
         $tmp.="<P/>";
      }
//      echo "$r[trid] $r[name] ";
//      echo " <BR> курсов ".getCoursesNum($r['trid'])."</td>
     $k++;
   }
   sqlfree( $res );
  $ph=ph(_("Специальности"));
//  $ph=ph("Специальности ($k)");

   global $s;
   if($s['perm'] == 3)
    return "";
   else
    return( $ph.$tmp);
//   return( $ph);
}

/*function isInTrackList( $tr, $cid ){
 if( count($tr)>0){
   foreach( $tr as $t){
     if( $t==$cid){
//       echo "$t<BR>";
//       echo cid2title($t);
       return(1);
     }
   }
 }
 return(0);
}
*/

function all_course_list( $title="Учебные курсы", $tracks="all" ) {
      global $coursestable;
      $clist=loadtmpl("courses-lowcourses.html");
      $line=loadtmpl("courses-1allline.html");
      if( $tracks!="all"){ // "notracks"
          $tr=getTracksIdList();
      }
      $result=@sql("SELECT * FROM ".$coursestable." WHERE Status>0 AND type = '0' ORDER BY Title");
      $k=0;

      while ($res=sqlget($result)) {
        if( !isInTrackList( $tr, $res[CID]) ) {
            $k++;
            $tmp=$line;
            $ds = (defined("LOCAL_FREE_REGISTRATION") && LOCAL_FREE_REGISTRATION) ? "<a href='reg.php4?Course=".$res[CID]."' ><img src='".$GLOBALS['controller']->view_root->skin_url . "/images/b_reg.gif' border=0 align='absmiddle'></a>" : "<br>";
            $tit=stripslashes($res['Title']);//.$ds;
            $tmp=str_replace("[REG-TO-COURSE]", $ds, $tmp);
            $tmp=str_replace("[cName]", $tit, $tmp);
            $bedate=mydate($res['cBegin'])."-<br>".mydate($res['cEnd']);
            $tmp=str_replace("[beDate]",$bedate,$tmp);
            $tmp=str_replace("[Fee]",$res['Fee'],$tmp);
            if ($res['Fee']==0)
               $tmp=str_replace("[VALUTA]","",$tmp);
            else {
               if (isset($GLOBALS[valuta][$res[valuta]]))
                  $tmp=str_replace("[VALUTA]",$GLOBALS[valuta][$res[valuta]][2],$tmp);
               else
                  $tmp=str_replace("[VALUTA]",$GLOBALS[valuta][0][2],$tmp);
            }
            if ($res['TypeDes']<0) $res['TypeDes'] = $res['chain'];
            $tmp=str_replace("[TypeDes]",get_course_type($res['TypeDes']),$tmp);
            $des=view_metadata_as_text( read_metadata ( stripslashes($res['Description']), COURSES_DESCRIPTION ), COURSES_DESCRIPTION);
            if (!empty($des)) $des = "<tr><td colspan=5>".$des."</td></tr>";
            $tmp=str_replace("[DESCRIPTION]",$des,$tmp); //stripslashes($res['Description'])
            $tmp=str_replace("[Teachers]",get_teachers_list($res['CID']),$tmp);
            $tmp=str_replace("[CID]",$res['CID'],$tmp);
            $all.=$tmp;

        }
        else {

        }
      }
      $clist=str_replace("[COURSES]",$all,$clist);
      if (!$GLOBALS['controller']->enabled) {
      $ttt="<table border=0 width=100% id='title_courses'><tr><th><span style='cursor:hand'
             onClick=\"removeElem('title_courses');putElem('courses');\">".getIcon("+").$title."</span></th></tr></table>";
      $clist="<table border='0' cellspacing='0' cellpadding='0' width=100% class=hidden2 id=courses ><tr><th><span style='cursor:hand'
             onClick=\"putElem('title_courses');removeElem('courses');\">".
             getIcon("-").$title."</span></th></tr><tr><td>".$clist."</td></tr></table>";
      }
      if( isset( $tmp )){
        if (!$GLOBALS['controller']->enabled)
        $ph=ph(_("Учебные курсы")." ($k)");
        $ret=$ph.$ttt.$clist;
      }
      else
        $ret=""; // если нет ни одного курса - не выводим таблицу ввобще
      return( $ret );
}

function dean_course_list() {
   global $coursestable;

   $courseFilter = new CCourseFilter($GLOBALS['COURSE_FILTERS']);

   $s_q_l="
    SELECT
      Courses.CID,
      Courses.Title,
      Courses.Description,
      Courses.TypeDes,
      Courses.CD,
      Courses.cBegin,
      Courses.cEnd,
      Courses.Fee,
      Courses.valuta,
      Courses.`Status`,
      Courses.createby,
      Courses.createdate,
      Courses.longtime,
      Courses.did,
      Courses.locked,
      Courses.chain,
      Courses.is_module_need_check
    FROM Courses
    WHERE 1=1 AND type = '0'";
   if (is_array($courseFilter->filtered) && count($courseFilter->filtered)) {
       $s_q_l .= " AND Courses.CID IN ('".join("','",array_keys($courseFilter->filtered))."') ";
   }
   $s_q_l .= " ORDER BY Courses.Title";
   //$s_q_l .= getSortType();

   $pagerOptions =
   array(
       'mode'    => 'Sliding',
       'delta'   => 5,
       'perPage' => COURSES_PER_PAGE,
       'urlVar' => 'activePageID',
   );

   if ($page = Pager_Wrapper_Adodb($GLOBALS['adodb'], $s_q_l, $pagerOptions)) {
       while($row = sqlget($page['result'])) {
           $cids[] = $row['CID'];
       }
   }

   if (is_array($cids) && count($cids)) {
       $s_q_l="
        SELECT
          Courses.CID,
          Courses.Title,
          Courses.Description,
          Courses.TypeDes,
          Courses.CD,
          Courses.cBegin,
          Courses.cEnd,
          Courses.Fee,
          Courses.valuta,
          Courses.`Status`,
          Courses.createby,
          Courses.createdate,
          Courses.longtime,
          Courses.did,
          Courses.locked,
          Courses.chain,
          Courses.is_module_need_check
        FROM
        ".$coursestable
       ." WHERE Courses.CID IN ('".join("','",$cids)."')
       ORDER BY Title";
   }


   if ($GLOBALS['controller']->enabled) {
   $clist = getCoursesGant( $s_q_l, TRUE, FALSE, false, true);
   } else
   $clist = getCoursesGant( $s_q_l, TRUE, TRUE, false );
   if ($page['links'])
   $links = "<br><table width=100% class=main cellspacing=0><tr><td align=center>".(string) $page['links']."</td></tr></table><br>";
   $ret = $links.$clist.$links;
   return $ret;
}

function dean_course_list2() {
   global $coursestable;

   $courseFilter = new CCourseFilter($GLOBALS['COURSE_FILTERS']);

   $s_q_l="
    SELECT
      Courses.CID,
      Courses.Title,
      Courses.Description,
      Courses.TypeDes,
      Courses.CD,
      Courses.cBegin,
      Courses.cEnd,
      Courses.Fee,
      Courses.valuta,
      Courses.`Status`,
      Courses.createby,
      Courses.createdate,
      Courses.longtime,
      Courses.did,
      Courses.locked,
      Courses.chain,
      Courses.is_module_need_check
    FROM Courses
    WHERE Status < 2";
   if (is_array($courseFilter->filtered) && count($courseFilter->filtered)) {
       $s_q_l .= " AND Courses.CID IN ('".join("','",array_keys($courseFilter->filtered))."') ";
   }
   $s_q_l .= " ORDER BY Courses.Title";
   //$s_q_l .= getSortType();

   $pagerOptions =
   array(
       'mode'    => 'Sliding',
       'delta'   => 5,
       'perPage' => COURSES_PER_PAGE,
       'urlVar' => 'inactivePageID',
   );

   if ($page = Pager_Wrapper_Adodb($GLOBALS['adodb'], $s_q_l, $pagerOptions)) {
       while($row = sqlget($page['result'])) {
           $cids[] = $row['CID'];
       }
   }

   if (is_array($cids) && count($cids)) {
       $s_q_l="
        SELECT
          Courses.CID,
          Courses.Title,
          Courses.Description,
          Courses.TypeDes,
          Courses.CD,
          Courses.cBegin,
          Courses.cEnd,
          Courses.Fee,
          Courses.valuta,
          Courses.`Status`,
          Courses.createby,
          Courses.createdate,
          Courses.longtime,
          Courses.did,
          Courses.locked,
          Courses.chain,
          Courses.is_module_need_check
        FROM
        ".$coursestable
       ." WHERE Courses.CID IN ('".join("','",$cids)."')
       ORDER BY Title";
   }


   if ($GLOBALS['controller']->enabled) {
   $clist = getCoursesGant( $s_q_l, FALSE, TRUE, false );
   } else
   $clist = getCoursesGant( $s_q_l, TRUE, TRUE, false );
   if ($page['links'])
   $links = "<br><table width=100% class=main cellspacing=0><tr><td align=center>".$page['links']."</td></tr></table><br>";
   $ret = $links.$clist.$links;
   return $ret;
}

function dean_course_list3() {

    $available_statuses = ($GLOBALS['s']['perm'] == 2) ? array(1,2) : array(0,1,2);
    foreach($available_statuses as $v) $statuses[$v.'#'] = get_course_status($v);
    $GLOBALS['controller']->addFilter(_("Название"), 'Title', false, $_REQUEST['Title']);
    $GLOBALS['controller']->addFilter(_('Статус'),'filterStatus',$statuses,$GLOBALS['filterStatus'],false,'-1',true);

    $groups = get_structure();
    set_structure_levels($groups);
    list($rubrics, $current) = get_sublevels($groups,0,'',(int) $_REQUEST['department']);
    $GLOBALS['controller']->addFilter(_('Рубрики'),'department',$rubrics,$current,false,'-1',true);
    $GLOBALS['controller']->addFilter(_('Провайдер'),'provider',getProvidersList(),$_REQUEST['provider']);
    //    $GLOBALS['controller']->addFilter(_('Рубрики'),'group','div',"<select style='width: 400px' name=\"department\" size=5><option value=\"0\" selected >"._('все')."</option>".show_sublevel($groups,0,'',(int) $_REQUEST['department'])."</select>");

    $courseFilter = new CCourseFilter($GLOBALS['COURSE_FILTERS']);
    if ($GLOBALS['s']['perm'] == 2) {
        $cids = $GLOBALS['s']['tkurs'];
    } else {
        $cids = $courseFilter->filtered;
    }
    if (is_array($cids) && count($cids) && $_REQUEST['department'] > 0) {
        intval($_REQUEST['department']);
        $deps = CCourseAdaptor::getDepartmentChildren($_REQUEST['department']);
        $deps[$_REQUEST['department']] = $_REQUEST['department'];
        if (count($deps)) {
            foreach($deps as $depId => $dep) {
                $deps[$depId] = "did LIKE '%;".$dep.";%'";
            }

            $sql = "SELECT CID
                    FROM Courses
                    WHERE (".join(' OR ', $deps).")
                    AND CID IN ('".join("','",array_keys($cids))."')";
        $cids = array();
        $res = sql($sql);
        while($row = sqlget($res)) {
            $cids[$row['CID']] = $row['CID'];
        }
    }
    }

    $sql = "SELECT * FROM Courses WHERE 1=1 AND type = '0' ";
    if (is_array($cids) && count($cids)) {
       $sql .= " AND CID IN ('".join("','",array_keys($cids))."') ";
    } else {
        $sql .= " AND CID = 0 ";
    }
    if (isset($GLOBALS['filterStatus']) && ($GLOBALS['filterStatus'] != -1)) {
        $sql .= " AND Status = '".(int) $GLOBALS['filterStatus']."' ";
    }
    
    if (isset($_REQUEST['provider']) && ($_REQUEST['provider'] > 0)) {
        intval($_REQUEST['provider']);
        $sql .= " AND provider = '".$_REQUEST['provider']."'";  
    }


    if (isset($_REQUEST['department']) && ($_REQUEST['department'] > 0)) {
        intval($_REQUEST['department']);
        $deps = CCourseAdaptor::getDepartmentChildren($_REQUEST['department']);
        $deps[$_REQUEST['department']] = $_REQUEST['department'];
        if (count($deps)) {
            foreach($deps as $depId => $dep) {
                $deps[$depId] = "did LIKE '%;".$dep.";%'";
            }

            $sql .= " AND (".join(" OR ", $deps).") ";
        }
    }

    if (isset($_REQUEST['Title']) && (strlen($_REQUEST['Title']))) {
        $sql .= " AND LOWER(Title) LIKE LOWER('%".substr($GLOBALS['adodb']->Quote($_REQUEST['Title']),1,-1)."%')";
    }

    $sql .= " ORDER BY Title";

    $pagerOptions =
    array(
        'mode'    => 'Sliding',
        'delta'   => 5,
        'perPage' => COURSES_PER_PAGE,
        'urlVar' => 'pageID',
    );

    $cids = array();
    if ($page = Pager_Wrapper_Adodb($GLOBALS['adodb'], $sql, $pagerOptions)) {
        while($row = sqlget($page['result'])) {
                $cids[$row['CID']] = $row['CID'];
        }
    }

    if (is_array($cids) && count($cids)) {

        $sql = "SELECT * FROM Courses WHERE Courses.CID IN ('".join("','",$cids)."')
                ORDER BY Title";
    }

    $smarty = new Smarty_els();

    $res = sql($sql);
    $courses = array();
    $i=0;
    while($row = sqlget($res)) {
        if (!$courseFilter->is_filtered($row['CID'])) continue;
        $row['StatusName'] = get_course_status($row['Status']);
        //if ($row['Status'] == 2) {
            $begin        = strtotime($row['cBegin']);
            $end          = strtotime($row['cEnd']);
            $row['active'] = false;
            if (($begin <= time()) && ($end >= time())) $row['active'] = true;
            if ($i==0) {
                $cBegin = $begin;
                $cEnd = $end;
            }
            if ($begin < $cBegin) {
                $cBegin = $begin;
            }
            if ($end > $cEnd) {
                $cEnd = $end;
            }
            $i++;
        //}
        $courses[$row['CID']] = $row;
    }

    if (is_array($courses) && count($courses)) {
        foreach($courses as $k => $row) {
            //if ($row['Status']<=1) continue;
            $begin = strtotime($row['cBegin']);
            $end = strtotime($row['cEnd']);
            $div = $cEnd - $cBegin;
            if ($div == 0) $div = 1;
            $prod   = sprintf("%1.1f",($end - $begin)*100 / ($div));
            $before = sprintf("%1.1f",($begin - $cBegin)*100 / ($div));
            $after  = sprintf("%1.1f",($cEnd - $end)*100 / ($div));

//            $row['timeline'] = "
//                <table width=100% cellspacing=0 cellpadding=0>
//                <tr><td width='".$before."%'></td>
//                <td width='".$prod."%' style='border:solid;'>".date ("d.m.y",$begin);
//            if (intval($after) > 5) {
//                $row['timeline'] .= "</td><td width='".$after."%' class='smallfont' > - ".date("d.m.y",$end);
//            } else {
//                $row['timeline'] .= " - ".date ("d.m.y",$end)."</td><td width='".$after."%'>";
//            }
//            $row['timeline'] .= "</td></tr></table>";
//            $courses[$k]['timeline'] = $row['timeline'];
            $courses[$k]['timeline'] = date("d.m.Y",$begin) . "&nbsp;&ndash;&nbsp;" . date("d.m.Y",$end);
        }
    }

    $url = 'course_structure.php';
    $smarty->assign('url', $url);
    $smarty->assign('sitepath', $GLOBALS['sitepath']);
    $smarty->assign('perm_manage',$GLOBALS['controller']->checkPermission(COURSE_PERM_MANAGE));
    if (($_SESSION['s']['perm'] == 3) && USE_CMS_INTEGRATION) {
        $smarty->assign('perm_manage_content', 1);
    } else {
    $smarty->assign('perm_manage_content',$GLOBALS['controller']->checkPermission(COURSE_PERM_MANAGE_CONTENT));
    }
    $smarty->assign('perm_manage_groups', ($_SESSION['s']['perm'] == 2));
    $smarty->assign('links',(string) $page['links']);
    $smarty->assign('courses', $courses);
    $smarty->assign('icon_groups',getIcon("people",_("Группы на курсе")));
    $smarty->assign('icon_view',getIcon("look",_("Открыть курс")));
    $smarty->assign('icon_struct',getIcon("struct",_("Редактировать программу курса")));
    $smarty->assign('icon_edit',getIcon("edit",_("Редактировать курс")));
    $smarty->assign('icon_delete',getIcon("delete",_("Удалить курс")));
    $smarty->assign('icon_copy',getIcon("copy",_("Копировать курс")));
    $smarty->assign('icon_import',getIcon("import_course",_("Импортировать курс")));
    $smarty->assign('icon_save',getIcon("save_course",_("Подготовить локальную копию курса")));
    $smarty->assign('icon_unlock',getIcon("unlock",_("Заблокировать курс")));
    $smarty->assign('icon_lock',getIcon("lock",_("Разблокировать курс")));
    $smarty->assign('add_url',$GLOBALS['sitepath']."courses.php4?action=add");
    $smarty->assign('add_caption',_("создать курс"));
    return $smarty->fetch('courses.tpl');

}

function delete_course($id) {
   global $coursestable;
   global $scheduletable;
   global $studentstable;
   global $teacherstable;
   global $scoursestable;
   global $claimtable;
   $remove=array();
   if(is_dir("COURSES/course".$id)) {
      empty_dir("COURSES/course".$id);
      $remove['dirs']=@rmdir("COURSES/course".$id);
   }
   $remove['coursestable']=@sql("DELETE FROM ".$coursestable." WHERE CID='".$id."'");
   $remove['scheduletable']=@sql("DELETE FROM ".$scheduletable." WHERE CID='".$id."'");
   $remove['studentstable']=@sql("DELETE FROM ".$studentstable." WHERE CID='".$id."'");
   $remove['teacherstable']=@sql("DELETE FROM ".$teacherstable." WHERE CID='".$id."'");
   $remove['scoursestable']=@sql("DELETE FROM ".$scoursestable." WHERE CID='".$id."'");
   $remove['tracks2course']=@sql("DELETE FROM `tracks2course` WHERE cid='".$id."'");
   $remove['library']      =@sql("DELETE FROM library WHERE cid = '".(int) $id."'");
   $remove['claimtable']   =@sql("DELETE FROM ".$claimtable." WHERE CID='".$id."'");
   return $remove;
}

function create_dirs($id)
   {
      $create=array();
      $ret=0;

      if (!is_dir($_SERVER['DOCUMENT_ROOT'] . "/COURSES/course".$id)) if (!@mkdir($_SERVER['DOCUMENT_ROOT'] . "/COURSES/course".$id, 0700)) $ret=1;
      if (!@chmod($_SERVER['DOCUMENT_ROOT'] . "/COURSES/course".$id,0775)) $ret=2;
      if (!is_dir($_SERVER['DOCUMENT_ROOT'] . "/COURSES/course".$id."/TESTS")) if (!@mkdir($_SERVER['DOCUMENT_ROOT'] . "/COURSES/course".$id."/TESTS", 0700)) $ret=1;
      if (!@chmod($_SERVER['DOCUMENT_ROOT'] . "/COURSES/course".$id."/TESTS",0775)) $ret=2;
      if (!is_dir($_SERVER['DOCUMENT_ROOT'] . "/COURSES/course".$id."/webcam_room_".$id)) if (!@mkdir($_SERVER['DOCUMENT_ROOT'] . "/COURSES/course".$id."/webcam_room_".$id, 0700)) $ret=1;
      if (!@chmod($_SERVER['DOCUMENT_ROOT'] . "/COURSES/course".$id."/webcam_room_".$id,0775)) $ret=2;
      if (!is_dir($_SERVER['DOCUMENT_ROOT'] . "/COURSES/course".$id."/mods")) if (!@mkdir($_SERVER['DOCUMENT_ROOT'] . "/COURSES/course".$id."/mods", 0700)) $ret=1;
      if (!@chmod($_SERVER['DOCUMENT_ROOT'] . "/COURSES/course".$id."/mods",0775)) $ret=2;
      if (!is_dir($_SERVER['DOCUMENT_ROOT'] . "/COURSES/course".$id."/TESTS_ANW")) if (!@mkdir($_SERVER['DOCUMENT_ROOT'] . "/COURSES/course".$id."/TESTS_ANW", 0700)) $ret=1;
      if (!@chmod($_SERVER['DOCUMENT_ROOT'] . "/COURSES/course".$id."/TESTS_ANW",0775)) $ret=2;
      if ($ret == 2) $ret = 0;

   return $ret;
   }

function create_java_folder($id,$d)
   {
      global $servletpath;

      $LoginServlet=$servletpath."fsdCreateDir";

      $html=loadtmpl("courses-java.html");
      $html=str_replace("[LoginServlet]",$LoginServlet,$html);
      $html=str_replace("[ID]",$id,$html);
      $html=str_replace("[DO]",$d,$html);

      return $html;
   }

   if ($delete_id) {
           $remove_status=delete_course($delete_id);
           $cJavaFolder=create_java_folder($delete_id,"delete");
           $cool .= _("Курс удален").'<br>';
   }


   $cJavaFolder="";





//var_dump($HTTP_POST_VARS);
//if (!isset($Status)) $Status=0;

   if($Action=="reg")
   {

//регистрация
       $_SESSION['course_edit'] = $_POST;
          if (isset($_POST['ch_add_teacher'])) {
                  if (isset($_POST['ch_add_teacher']) && ($strLogin=validateEmail($createby))) {
                                  $r = sql("SELECT * FROM People WHERE Login='{$strLogin}'");
                                  if (sqlrows($r)) {
                                          $strLogin .= "_".randString(3);
                                          $r = sql("SELECT * FROM People WHERE Login='{$strLogin}'");
                                          if (sqlrows($r)) {
                                                  $error .= _("Невозможно создать преподавателя: попробуйте другой")." e-mail<br>\n";
                                          }
                                  }
                  } else {
                                  $error.=_("Неверный")." e-mail.<br>\n";
                  }
          }
      if ( empty($Title)) {
          $error.=_("Вы не ввели название курса.")." <br>\n";
      }
      if ($b_tmp > $e_tmp) {
          $error.=_("Некорректный ввод диапазона курса.")." <br>\n";
      }
      if ($longtime_tmp > $e_tmp-$b_tmp+86400) {
          $error.=_("Некорректный ввод длительности курса.")." <br>\n";
      }
      if (!strlen($error))
      {
         global $adodb;
         $result=sql("select Title from Courses where `type` = '0' AND Title=".$adodb->Quote($Title));
         if (sqlrows($result)<0) {
            $error.="$Title "._("уже зарегистрирован. Выберите другое название.")."\n";
         }
         else
         {
             unset($_SESSION['course_edit']);
                 $arrMeta = load_metadata(COURSES_DESCRIPTION);
                 foreach ($arrMeta as $a) {
                         $arrMetaNames[] = $a['name'];
                 }
                 $strDescMeta = set_metadata($_POST, $arrMetaNames, COURSES_DESCRIPTION);

            if ($TypeDes==0) $TypeDes = $chain;
            $query = "INSERT INTO $coursestable (Title,Description,cBegin,cEnd,TypeDes,Status,Fee,createby,createdate,longtime,credits_teacher, credits_student, chain, CD)
                      VALUES (".$adodb->Quote($Title).",".$adodb->Quote($strDescMeta).",".$cBegin.",".$cEnd.",".$adodb->Quote($TypeDes).",".$adodb->Quote($Status).",".$adodb->Quote($Fee).",".$adodb->Quote($s['mid']).",".$createdate.",".$adodb->Quote($longtime).",".$adodb->Quote((int)$credits_teacher).",".$adodb->Quote((int)$credits_student).",".$adodb->Quote($chain).", '')";
            sql($query,"errCR601");
            $newCID=sqllast();
            /**
            * Занесения курса в курируемые
            */
            if ($newCID) {
                $sql = "SELECT did FROM departments WHERE mid='".(int) $GLOBALS['s']['mid']."'";
                $tmp_res = sql($sql);
                if (sqlrows($tmp_res) && ($tmp_row = sqlget($tmp_res))) {
                    $sql = "INSERT INTO departments_courses (did,cid) VALUES ('".(int) $tmp_row['did']."','".(int) $newCID."')";
                    sql($sql);
                }

                sql("INSERT INTO organizations (title, cid, prev_ref, level) VALUES ('"._("&lt;пустой элемент&gt;")."','$newCID','-1', '0')");

            }

            /**
             * Назначение весов занятий
             */
            if ($newCID) {
                $sql = "SELECT TypeID FROM EventTools";
                $res = sql($sql);
                $i=1;
                while($row = sqlget($res)) {
                    $weight = (int) (100/sqlrows($res));
                    if ($i==sqlrows($res)) {
                        $weight = 100 - ($weight*(sqlrows($res)-1));
                    }
                    $sql = "INSERT INTO eventtools_weight (event, cid, weight)
                            VALUES ('".(int) $row['TypeID']."','".(int) $newCID."','".(int) $weight."')";
                    sql($sql);
                    $i++;
                }
            }

                        if (isset($_POST['ch_add_teacher'])) {
                    // inserts teacher
                    $strPassword = randString(7);
                                $r = sql("INSERT INTO People (Login, Password, EMail) values ('{$strLogin}', PASSWORD('{$strPassword}'), '{$createby}')");
                                $idPeople = sqllast();
                                $r = sql("INSERT INTO Teachers (MID, CID) values ('{$idPeople}', '{$newCID}')");
                    mailToteach("forced", $idPeople, $newCID);
                        }

            if ($dean)
            {
               //$newCID=getField($coursestable,"CID","Title",$Title);
               $status['create']=create_dirs($newCID);
               $cJavaFolder=create_java_folder($newCID,"create");
               if (!$status['create']) {
                   $cool.=_("Курс")." ".$Title." "._("добавлен успешно.");
               }
               else
               {
                  $error.=_("Произошла ошибка при добавлении курса")." ".$Title."<br>";
                  if(1==$status['create']) $error.=_("Не были созданы необходимые каталоги.");
                  else $error.=_("Не были присвоены необходимые права.");
               }
            }
            else
            {
               $addparam['email']=$createby;
               $addparam['description']=$Description;
               $addparam['title']=$Title;
               $addparam['lf']=$createby;
               mailToelearn("fromcourse","",$newCID,$addparam);
               mailToother("fromcourse",$newCID,$addparam);
               $cool.=_("Спасибо за регистрацию курса")." ".$Title." "._("на сервере. Через некоторое время вам будет выслан ответ.");
            }
         }
      }
   }
//   echo "<H1>$Action</H1>";
   if( $Action=="change" && $dean)
//изменение данных
    {
        $_SESSION['course_edit'] = $_POST;
        $reg = false;
        if (!$chCID) {
            $reg = true;            
            if ( empty($Title)) {
                $error.=_("Вы не ввели название курса.")." <br>\n";
            } else {  

                $result=sql("select Title from Courses where `type` = '0' AND Title=".$adodb->Quote($Title));
                if (sqlrows($result)<0) {
                    $error.="$Title "._("уже зарегистрирован. Выберите другое название.")."\n";
                } else {
                    
                    sql("INSERT INTO Courses (Title,createby,createdate,Status) 
                         VALUES (".$adodb->Quote($Title).",".$adodb->Quote($s['mid']).",".$createdate.",0)");
                    $chCID = sqllast();

                    if ($chCID) {
                        $sql = "SELECT did FROM departments WHERE mid='".(int) $GLOBALS['s']['mid']."'";
                        $tmp_res = sql($sql);
                        if (sqlrows($tmp_res) && ($tmp_row = sqlget($tmp_res))) {
                            $sql = "INSERT INTO departments_courses (did,cid) VALUES ('".(int) $tmp_row['did']."','".(int) $chCID."')";
                            sql($sql);
                        }
                        sql("INSERT INTO organizations (title, cid, prev_ref, level) VALUES ('"._("&lt;пустой элемент&gt;")."','$chCID','-1', '0')");
                        
                        $sql = "SELECT TypeID FROM EventTools";
                        $res = sql($sql);
                        $i=1;
                        while($row = sqlget($res)) {
                            $weight = (int) (100/sqlrows($res));
                            if ($i==sqlrows($res)) {
                                $weight = 100 - ($weight*(sqlrows($res)-1));
                            }
                            $sql = "INSERT INTO eventtools_weight (event, cid, weight)
                                    VALUES ('".(int) $row['TypeID']."','".(int) $chCID."','".(int) $weight."')";
                            sql($sql);
                            $i++;
                        }
                                        
                    }
                    
                }
            }
        }
        $courses_templates = get_courses_templates_and_colors_as_array();
        if ($_POST['template_publ']) {
            $tmp = explode(";", $_POST['template_publ']);
            if(count($tmp) == 2) {
                $strDir = $_SERVER['DOCUMENT_ROOT']."/template/interface/{$tmp[0]}";
                copyDir($strDir, $_SERVER['DOCUMENT_ROOT']."/COURSES/course{$chCID}/");
                $strDir = $_SERVER['DOCUMENT_ROOT']."/template/interface/{$tmp[0]}/Styles/{$tmp[1]}";
                @copyDir($strDir, $_SERVER['DOCUMENT_ROOT']."/COURSES/course{$chCID}/");
            }
        }
       $addparam['email']=$createby;
       $addparam['lf']=$createby;
/*
      if ((getField($coursestable,"Status","CID",$chCID)==0) AND ($Status>0))
         {/*  $to=getField($coursestable,"createby","CID",$redCID);
            $from=getField($optionstable,"value","name","dekanEMail");
            $fromname=getField($optionstable,"value","name","dekanName");
            $headers = "From: $fromname<$from>\n";
            $headers .="Content-type: text/html; Charset={$GLOBALS['controller']->lang_controller->lang_current->encoding}\n";
            $headers .="X-Sender: <$from>\n";
            @mail("$to", "Курс $Title зарегистрирован по Вашей заявке", "Теперь Вы можете на нем зарегистрироваться", $headers);
            */
//            mailToother("regcourse",$chCID,$addparam);
//         }
      if ((getField($coursestable,"Status","CID",$chCID)==0) AND ($Status>0)) mailToother("regcourse",$chCID,$addparam);
      if ((getField($coursestable,"Status","CID",$chCID)>0) AND ($Status==0)) mailToother("delcourse",$chCID,$addparam);

      if ($_POST['chain']) {
          $emptyChainItems = is_brokenChain($chCID,$_POST['chain']);
          if ($emptyChainItems) {
              $error.=_("Цепочка согласования не может быть применена, так как одна или несколько должностей вакантны");
          }
      }
      if ( empty($Title) && !$reg) {
          $error.=_("Вы не ввели название курса.")." <br>\n";
      }
      if ($b_tmp > $e_tmp) {
          $error.=_("Некорректный ввод диапазона курса.")." <br>\n";
      }
      if ($longtime_tmp > $e_tmp-$b_tmp+86400) {
          $error.=_("Некорректный ввод длительности курса.")." <br>\n";
      }
      if (!strlen($error))
      {
        $status['create']=create_dirs($chCID);
        if (!$status['create']) $cool.="";
        else
        {
          $error.=_("Произошла ошибка при добавлении курса")." ".$Title."<br>";
          if(1==$status['create']) $error.=_("Не были созданы необходимые каталоги.");
          else $error.=_("Не были присвоены необходимые права.");
        }

         unset($_SESSION['course_edit']);
         $cJavaFolder=create_java_folder($chCID,"create");
         $department = $_POST['departments'];

         if (is_array($department) && count($department)) {
             foreach($department as $dep) {
                 if ($dep == 0) {
                     $department = array();
                 }
             }
         }

         //valuta='".(intval($val)%count($valuta))."',
         global $adodb;
         if ($TypeDes==0) $TypeDes=$chain;

         if (getField('Courses','sequence','CID', $chCID) != (int) $_POST['sequence']) {
             // обнулить sequence и current
             sql("DELETE FROM sequence_current WHERE cid = '".(int) $chCID."'");
             sql("DELETE FROM sequence_history WHERE cid = '".(int) $chCID."'");
         }

         $query = "
         UPDATE $coursestable set
           Title=".$adodb->Quote($Title).",
           cBegin=$cBegin,
           cEnd=$cEnd,
           TypeDes=$TypeDes,
           Status=$Status,
           Fee='$Fee',
           valuta='".intval($val)."',
           longtime='$longtime',
           did='".((is_array($department) && count($department)) ? ";".implode(";",$department).";" : "")."',
           credits_teacher='".($credits_teacher?$credits_teacher:0)."',
           credits_student='".($credits_student?$credits_student:0)."',
           chain='{$chain}',
           is_module_need_check='{$is_module_need_check}',
           sequence = '{$sequence}',
           provider = '".(int) $_POST['provider']."'
         where CID='$chCID'";
//           createby=".$adodb->Quote($s['mid']) . ",
//         var_dump($_POST['department']);exit();
         if (@sql($query)) {
            if ($reg) {
                $cool.=_("Курс")." ".$Title." "._("добавлен успешно.");             
            } else {
                $cool.=_("Данные курса были изменены.")."\n";
            }
         }

         // Сохранение метаданных
         $postedNames = array();
         $metadata = load_metadata(COURSES_DESCRIPTION);
         if (is_array($metadata) && count($metadata)) {
             foreach ($metadata as $value) {
                 if (!empty($value['name'])) {
                     $postedNames[] = $value['name'];
                 }
             }
         }
         $meta = set_metadata($_POST, $postedNames, COURSES_DESCRIPTION);
         $sql = "UPDATE Courses SET Description =".$GLOBALS['adodb']->Quote($meta)." WHERE CID= ".(int) $chCID;
         sql($sql);

         /**
          * Переводит всех претендентов в студенты
          */
         if ($chain==0) {
             require_once($GLOBALS['wwf'].'/lib/classes/CCourseAdaptor.class.php');
             CCourseAdaptor::process_claimants2students($chCID);
         }

         sql("DELETE FROM rooms2course WHERE cid='".(int) $chCID."'");
         if (is_array($_POST['rooms']) && count($_POST['rooms'])) {
             foreach($_POST['rooms'] as $v)
                if ($v>0) {
                    sql("INSERT INTO rooms2course (rid,cid) VALUES ('".(int) $v."','".(int) $chCID."')");
                }
         }

         $GLOBALS['controller']->setView('DocumentBlank');
         $GLOBALS['controller']->setMessage($cool,JS_GO_URL,$GLOBALS['sitepath']."courses.php4");
         $GLOBALS['controller']->terminate();
         exit();
      } else {
          if ($reg && $chCID) {
              sql("DELETE FROM Courses WHERE CID = '".(int) $chCID."'");
              sql("DELETE FROM departments_courses WHERE cid = '".(int) $chCID."'");
              sql("DELETE FROM organizations WHERE cid = '".(int) $chCID."'");
              sql("DELETE FROM eventtools_weight WHERE cid = '".(int) $chCID."'");
          }
      }

   }


   $courses_header=show_header();

//   global $new_course_registration;


//   if( sqlget (sql_query(35)) > 0 )
   $new_course_registration=($s['perm'] >=3) ? 1 : 0;
//   else
//    $new_course_registration=0;

   if(($new_course_registration==1) && ($redCID<=0)){
     //$reg_form=course_reg_form();
     $form_header=show_form_header();
   }

   $wait=_("Подождите пока будут созданы/удалены каталоги в сервлетной области");

// $cJavaFolder=create_java_folder($cJid,$cJdo);


   if ($redCID && ($s['perm'] >=3))

   {
      $result=sql("
      SELECT
          Courses.CID,
          Courses.Title,
          Courses.Description,
          Courses.TypeDes,
          Courses.CD,
          Courses.cBegin,
          Courses.cEnd,
          Courses.Fee,
          Courses.valuta,
          Courses.`Status`,
          Courses.createby,
          Courses.createdate,
          Courses.longtime,
          Courses.did,
          Courses.credits_teacher,
          Courses.credits_student ,
          Courses.chain,
          Courses.is_module_need_check,
          Courses.sequence,
          Courses.provider
      FROM
      $coursestable WHERE CID=".$redCID);

      $GLOBALS['controller']->setHelpSection('editcourse');

      if (sqlrows($result)>0)
         {
            $GLOBALS['controller']->setHeader(_('Редактирование свойств курса'));
            $row=sqlget($result);
            $reg_form=course_change_form($redCID,$row);
            $GLOBALS['controller']->setCurTab('m060102');
         }
   }

    if (isset($_GET['action']) && $_GET['action'] == "add") {
        $GLOBALS['controller']->setHeader(_('Добавить курс'));
        $GLOBALS['controller']->setHelpSection('editcourse');
        $course_list=course_reg_form();
        
        /*if ($s['perm'] >= 3) {
            $GLOBALS['controller']->setHeader('Редактирование свойств курса');
            $smarty = new Smarty_els();
            $longtime = 120;
            $time2 = time() + ($longtime + 20) * 24 * 3600;
            $smarty->assign("longtime", $longtime);
            $smarty->assign("date1", array("d" => date("d"), "m" => date("m"), "y" => date("Y")));
            $smarty->assign("date2", array("d" => date("d", $time2), "m" => date("m", $time2), "y" => date("Y", $time2)));
            
            $course_list = $smarty->fetch("courses_add.tpl");
        }*/
    }
    elseif (!isset($_GET['redCID'])) {
       if ($s['perm'] >=2) {
            $GLOBALS['controller']->setPermissionTemporary('1301');
            $GLOBALS['controller']->setPermissionTemporary('1302');
            $course_list = dean_course_list3();
            //$course_list2 = dean_course_list2();
       } else {
    // у слушателя и гостя courses.php4 нигде не используется, но может быть вызвана только напрямую через GET
    //           $course_list = all_course_list(_("Перечень курсов"), "notracks");
           //$course_list2 = false;
       }
    }

   $html=loadtmpl("courses-main.html");

   $cJavaFolder="";
   ///////////////////

    $tmp=all_tracks_list( $dean );

    $html=str_replace("[TRACKS]",'',$html);
////////////////////////////////////////////////////////////////

   if ($GLOBALS['controller']->enabled) {
       $courses_header='';
       if (!empty($error) || !empty($cool)) {
           $GLOBALS['controller']->setView('DocumentBlank');
           $link = 'courses.php4?pageID='.(int) $_GET['pageID'];
           if (isset($_POST['Action']) && ($_POST['Action'] == 'change')) {
               $link .= '&redCID='.(int) $_POST['chCID'];
           }
           if ($reg) {
               $link = 'courses.php4?pageID='.(int) $_GET['pageID'].'&action=add';              
           }
           $GLOBALS['controller']->setMessage(strip_tags(str_replace("\n","",($error)).'<br>'.str_replace("\n","",($cool))),JS_GO_URL,$link, false, false, $newCID);
           $GLOBALS['controller']->terminate();
           exit();
       }
   }
   $html=str_replace("[HEADER]",$courses_header,$html);
   $html=str_replace("[FORM_HEADER]",$form_header,$html);
   $html=str_replace("[REG_FORM]",$reg_form,$html);
   $html=str_replace("[COURSE_LIST]",$course_list,$html);

   $html=str_replace("[ERROR]",$error,$html);
   $html=str_replace("[COOL]",$cool,$html);
   $html=str_replace("[WAIT]",$wait,$html);
   $html=str_replace("[CJAVA]",$cJavaFolder,$html);
   $html=str_replace("[ACTION]","courses.php4",$html);
   $html=showSortImg($html,$s[user][csort]);
   if (!$GLOBALS['controller']->enabled) {
        if ($s[perm]==3) $html.="<P><a href=courses1c.php?$sess>"._("Импорт курсов из .CSV файла")."</a>";
   }

   $mhtml=show_tb(1);

   $mhtml=str_replace("[ALL-CONTENT]",$html,$mhtml);

   if ($GLOBALS['controller']->enabled) {

        if ($GLOBALS['s']['perm']>=3) {
        $reg_form=words_parse($reg_form,$words);
        $reg_form=path_sess_parse($reg_form);
        $reg_form=str_replace("[ERROR]",$error,$reg_form);
        $reg_form=str_replace("[COOL]",$cool,$reg_form);
        $reg_form=str_replace("[WAIT]",$wait,$reg_form);
        $reg_form=str_replace("[CJAVA]",$cJavaFolder,$reg_form);
        $reg_form=str_replace("[ACTION]","courses.php4",$reg_form);

        if (!$redCID && $GLOBALS['is_perm_add'])
        $GLOBALS['controller']->captureFromReturn(CONTENT,$reg_form);

        if ($redCID && $GLOBALS['is_perm_edit'])
        $GLOBALS['controller']->captureFromReturn(CONTENT,$reg_form);

        }

        if ($GLOBALS['s']['perm']>=3) {
        $course_list=words_parse($course_list,$words);
        $course_list=path_sess_parse($course_list);
        $course_list=showSortImg($course_list,$s[user][csort]);
        $course_list=str_replace("[ERROR]",$error,$course_list);
        $course_list=str_replace("[COOL]",$cool,$course_list);
        $course_list=str_replace("[WAIT]",$wait,$course_list);
        $course_list=str_replace("[CJAVA]",$cJavaFolder,$course_list);
        $course_list=str_replace("[ACTION]","courses.php4",$course_list);
        $GLOBALS['controller']->captureFromReturn(CONTENT,$course_list);
        }

        if ($GLOBALS['s']['perm']>=3) {
        $course_list2=words_parse($course_list2,$words);
        $course_list2=path_sess_parse($course_list2);
        $course_list2=showSortImg($course_list2,$s[user][csort]);
        $course_list2=str_replace("[ERROR]",$error,$course_list2);
        $course_list2=str_replace("[COOL]",$cool,$course_list2);
        $course_list2=str_replace("[WAIT]",$wait,$course_list2);
        $course_list2=str_replace("[CJAVA]",$cJavaFolder,$course_list2);
        $course_list2=str_replace("[ACTION]","courses.php4",$course_list2);
        //$GLOBALS['controller']->captureFromReturn('m060104',$course_list2);
        }

        if ($GLOBALS['s']['perm']<3) {
            $GLOBALS['controller']->captureFromReturn(CONTENT,$course_list);
//            $GLOBALS['controller']->captureFromReturn('m010301',$tmp);
//            $GLOBALS['controller']->captureFromReturn('m010302',$course_list);
        }

//        if ($admin) {
//            $GLOBALS['controller']->captureFromReturn(CONTENT,$tmp.$course_list);

//        }
   }
   printtmpl($mhtml);


function copy_course($id) {

         require_once($GLOBALS['wwf'].'/lib/classes/CourseContent.class.php');

         //Select the course for copy
         $query_select = "SELECT * FROM Courses WHERE CID = $id";
         $result_select = sql($query_select,"err select course");
         $course_for_copy = sqlget($result_select);
         //Insert new course
         $query_insert = "INSERT INTO Courses (";
         foreach($course_for_copy as $key => $value) {
                 if (($key != 'CID') && ($key != 'tree'))
                    $query_insert .= $key.",";

         }
         $query_insert = trim($query_insert, ",").")";
         $query_insert .= " VALUES (";
         foreach($course_for_copy as $key => $value ) {
                 if($key == 'Title') {
                         $query_insert .= "'"._("Копия")." $value',";
                 }
                 elseif(($key != 'CID') && ($key != 'tree'))
                                 $query_insert .= "".$GLOBALS['adodb']->Quote($value).",";

         }
         $query_insert = trim($query_insert, ",");
         $query_insert .= ")";
         $result_insert = sql($query_insert, "err insert course");

         $new_course_id = sqllast($result_insert);

         //Select all tests corresponding course and copy
         $query_select = "SELECT * FROM test WHERE cid = $id";
         $result_select = sql($query_select, "err select test");
         $tests = array();
         while($test_for_copy = sqlget($result_select)) {
               $query_insert = "INSERT INTO test (";
               foreach($test_for_copy as $key => $value) {
                    if($key != 'tid')
                       $query_insert .= $key.",";
               }
               $query_insert = trim($query_insert, ",").")";
               $query_insert .= " VALUES (";
               foreach($test_for_copy as $key => $value ) {
                    if($key == 'tid') {

                    }
                    elseif($key == 'cid')
                        $query_insert .= "'$new_course_id',";
                    elseif($key =='cidowner')
                        $query_insert .= "'$new_course_id',";
                    elseif($key == 'data')
                        $query_insert .= "REPLACE('".$test_for_copy['data']."', '".$id."-', '".$new_course_id."-'),";
                    else
                        $query_insert .= "".$GLOBALS['adodb']->Quote($value).",";
               }
               $query_insert = trim($query_insert, ",").")";
               //echo $query_insert."<br><br>";
               $res = sql($query_insert,"err insert test: $query");
               if ($res) {
                   $tests[$test_for_copy['tid']] = sqllast();
               }

         }

         //copy all question
         $query_select = "SELECT * FROM list WHERE kod like '".$id."-%'";
         $result_select = sql($query_select);
         while($question_for_copy = sqlget($result_select)) {
               $query_insert = "INSERT INTO list (";
               foreach($question_for_copy as $key => $value) {
                    //if($key != 'kod')
                    $query_insert .= $key.",";
               }
               $query_insert = trim($query_insert, ",").")";
               $query_insert .= " VALUES (";
               foreach($question_for_copy as $key => $value ) {
                    if($key == 'kod') {
                       $query_insert .= "REPLACE('".$question_for_copy['kod']."','$id-','$new_course_id-'),";
                    }
                    else
                       $query_insert .= "".$GLOBALS['adodb']->Quote($value).",";
               }
               $query_insert = trim($query_insert, ",").")";
               $res = sql($query_insert,"err insert list: $query");
         }

         // copy all files
         $query_select = "SELECT * FROM file WHERE kod like '".$id."-%'";
         $result_select = sql($query_select);
         while($question_for_copy = sqlget($result_select)) {
               $query_insert = "INSERT INTO file (";
               foreach($question_for_copy as $key => $value) {
                    //if($key != 'kod')
                    $query_insert .= $key.",";
               }
               $query_insert = trim($query_insert, ",").")";
               $query_insert .= " VALUES (";
               foreach($question_for_copy as $key => $value ) {
                    if($key == 'kod') {
                       $query_insert .= "REPLACE('".$question_for_copy['kod']."','$id-','$new_course_id-'),";
                    }
                    else
                       $query_insert .= "".$GLOBALS['adodb']->Quote($value).",";
               }
               $query_insert = trim($query_insert, ",").")";
               $res = sql($query_insert);
         }

         // copy all runs
         $runs = array();
         $query_select = "SELECT * FROM training_run WHERE cid = '".$id."'";
         $result_select = sql($query_select);
         while($question_for_copy = sqlget($result_select)) {
               $query_insert = "INSERT INTO training_run (";
               foreach($question_for_copy as $key => $value) {
                    if($key != 'run_id')
                    $query_insert .= $key.",";
               }
               $query_insert = trim($query_insert, ",").")";
               $query_insert .= " VALUES (";
               foreach($question_for_copy as $key => $value ) {
                    if ($key == 'cid') {
                        $value = $new_course_id;
                    }
                    if ($key != 'run_id')
                    $query_insert .= "".$GLOBALS['adodb']->Quote($value).",";
               }
               $query_insert = trim($query_insert, ",").")";
               $res = sql($query_insert,"err insert list: $query");
               if ($res) {
                   $runs[$question_for_copy['run_id']] = sqllast();
               }
         }

         // copy all modules
         $modules = array();
         $query_select = "SELECT * FROM library WHERE cid = '".$id."' ORDER BY parent";
         $result_select = sql($query_select);
         while($question_for_copy = sqlget($result_select)) {
               $query_insert = "INSERT INTO library (";
               foreach($question_for_copy as $key => $value) {
                    if($key != 'bid')
                    $query_insert .= $key.",";
               }
               $query_insert = trim($query_insert, ",").")";
               $query_insert .= " VALUES (";
               foreach($question_for_copy as $key => $value ) {
                    if ($key == 'cid') {
                        $value = $new_course_id;
                    }
                    if ($key == 'filename') {
                        $fileNameTemplate = "/^\/..\/COURSES\/course$id\//i";
                        $newFileName      = "/../COURSES/course$new_course_id/";
                        if (preg_match($fileNameTemplate, $value)) {
                            $value = preg_replace($fileNameTemplate, $newFileName, $value);
                        }
                    }
                    if ($key == 'parent') {
                        if (isset($modules[$value])) {
                            $value = $modules[$value];
                        }
                    }
                    if ($key != 'bid')
                    $query_insert .= "".$GLOBALS['adodb']->Quote($value).",";
               }
               $query_insert = trim($query_insert, ",").")";

               $res = sql($query_insert,"err insert list: $query");
               if ($res) {
                   $modules[$question_for_copy['bid']] = sqllast();
               }
         }

         if (count($modules)) {
             $query_select = "SELECT * FROM library WHERE parent IN (".join(",", array_keys($modules)).") ORDER BY parent";
             $result_select = sql($query_select);
             while($question_for_copy = sqlget($result_select)) {
                   $query_insert = "INSERT INTO library (";
                   foreach($question_for_copy as $key => $value) {
                        if($key != 'bid')
                        $query_insert .= $key.",";
                   }
                   $query_insert = trim($query_insert, ",").")";
                   $query_insert .= " VALUES (";
                   foreach($question_for_copy as $key => $value ) {
                        if ($key == 'cid') {
                            $value = $new_course_id;
                        }
                        if ($key == 'filename') {
                            $fileNameTemplate = "/^\/..\/COURSES\/course$id\//i";
                            $newFileName      = "/../COURSES/course$new_course_id/";
                            if (preg_match($fileNameTemplate, $value)) {
                                $value = preg_replace($fileNameTemplate, $newFileName, $value);
                            }
                        }
                        if ($key == 'parent') {
                            if (isset($modules[$value])) {
                                $value = $modules[$value];
                            }
                        }
                        if ($key != 'bid')
                        $query_insert .= "".$GLOBALS['adodb']->Quote($value).",";
                   }
                   $query_insert = trim($query_insert, ",").")";
                   $res = sql($query_insert,"err insert list: $query");
                   if ($res) {
                       $modules[$question_for_copy['bid']] = sqllast();
                   }
             }
         }

         //Copy mod_list
/*         $query_select = "SELECT * FROM mod_list WHERE CID = $id";
         $result_select = sql($query_select);
         while($mod_for_copy = sqlget($result_select)) {
               $query_insert = "INSERT INTO mod_list (";
               foreach($mod_for_copy as $key => $value) {
                    if($key != 'ModID')
                    $query_insert .= $key.",";
               }
               $query_insert = trim($query_insert, ",").")";
               $query_insert .= " VALUES (";
               $ModID_for_copy = 0;
               foreach($mod_for_copy as $key => $value ) {
                    if($key != 'ModID') {
                       if($key == 'CID') {
                          $query_insert .= "$new_course_id,";
                       }
                       else
                          $query_insert .= "".$GLOBALS['adodb']->Quote($value).",";
                    } else {
                        $ModID_for_copy = (int) $value;
                    }
               }
               $query_insert = trim($query_insert, ",").")";
               $result_insert = sql($query_insert,"err insert mod_list: $query");
               $mod_last_id = sqllast($result_insert);
               $mods[$ModID_for_copy] = $mod_last_id;
               $q_mod_content_select = "SELECT * FROM mod_content WHERE ModID = ".$mod_for_copy['ModID'];
               $r_mod_content_result = sql($q_mod_content_select, "err mod_content select");
               while($mod_content_for_copy = sqlget($r_mod_content_result)) {

               $do_q = false;
               $q_mod_content_insert = "INSERT INTO mod_content (";

               if(count($mod_content_for_copy) && is_array($mod_content_for_copy))
               foreach($mod_content_for_copy as $key => $value ) {
                       if($key == "McID") {
                           $do_q = true;
                       }
                       else {
                             $q_mod_content_insert .= $key.",";
                       }
               }
               $q_mod_content_insert = trim($q_mod_content_insert, ",").") VALUES (";

               if(count($mod_content_for_copy) && is_array($mod_content_for_copy))
               foreach($mod_content_for_copy as $key => $value ) {
                       if($key == "McID") {
                       }
                       elseif($key == "ModID") {
                             $q_mod_content_insert .= $mod_last_id.",";
                       }
                       elseif($key == "mod_l") {
                             $q_mod_content_insert .= "'".str_replace("course$id", "course$new_course_id", $value)."',";

                       }
                       else {
                             $q_mod_content_insert .= "".$GLOBALS['adodb']->Quote($value).",";
                       }
               }
               $q_mod_content_insert = trim($q_mod_content_insert, ",").")";

               //if($do_q)
               sql($q_mod_content_insert, "err insert mod_content $q_mod_content_insert");
               }
         }
*/

            /**
            * Занесения курса в курируемые
            */

            $sql = "SELECT did FROM departments WHERE mid='".(int) $GLOBALS['s']['mid']."'";
            $tmp_res = sql($sql);
            if (sqlrows($tmp_res) && ($tmp_row = sqlget($tmp_res))) {
                $sql = "INSERT INTO departments_courses (did,cid) VALUES ('".(int) $tmp_row['did']."','".(int) $new_course_id."')";
                sql($sql);
            }


         // Copy organization

         $organization = CCourseContent::getChildren($id);
         $i=0;
         if (is_array($organization) && count($organization)) {
             foreach ($organization as $item) {
                 $organization_for_copy = $item->attributes;

                 $query_insert = "INSERT INTO organizations (";
                 foreach($organization_for_copy as $key => $value) {
                     if($key != 'oid') {
                         $query_insert .= $key.",";
                     }

                 }
                 $query_insert = trim($query_insert, ",").")";
                 $query_insert .= " VALUES (";
                 foreach($organization_for_copy as $key => $value ) {
                     if (($key == 'vol1') && isset($tests[$value])) {
                         $value = $tests[$value];
                     }
                     if (($key == 'vol2') && isset($runs[$value])) {
                         $value = $runs[$value];
                     }
                     if (($key == 'module') && isset($modules[$value])) {
                         $value = $modules[$value];
                     }
                     if ($key == 'mod_ref') {
                          $value = $mods[$value];
                      }
                      if($key != 'oid') {
                         if($key == 'cid') {
                            $query_insert .= "$new_course_id,";
                         }
                         elseif($key == "prev_ref") {
                             if($i == 0) {
                                $query_insert .= "'-1',";
                             }
                             else {
                                $query_insert .= "'".$last_org_id."',";
                             }
                         }
                         else
                            $query_insert .= "".$GLOBALS['adodb']->Quote($value).",";
                      }
                 }
                 $query_insert = trim($query_insert, ",").")";
                 $result_insert = sql($query_insert,"err insert organizations: $query");
                 $last_org_id = sqllast($result_insert);
                 $i++;
             }
         }


/*         $query_select = "SELECT * FROM organizations WHERE cid = $id ORDER BY prev_ref";
         $result_select = sql($query_select);
         $i = 0;
         while($organization_for_copy = sqlget($result_select)) {
               $query_insert = "INSERT INTO organizations (";
               foreach($organization_for_copy as $key => $value) {
                    if($key != 'oid') {
                       $query_insert .= $key.",";
                    }

               }
               $query_insert = trim($query_insert, ",").")";
               $query_insert .= " VALUES (";
               foreach($organization_for_copy as $key => $value ) {
                   if (($key == 'vol1') && isset($tests[$value])) {
                       $value = $tests[$value];
                   }
                   if (($key == 'vol2') && isset($runs[$value])) {
                       $value = $runs[$value];
                   }
                   if (($key == 'module') && isset($modules[$value])) {
                       $value = $modules[$value];
                   }
                   if ($key == 'mod_ref') {
                        $value = $mods[$value];
                    }
                    if($key != 'oid') {
                       if($key == 'cid') {
                          $query_insert .= "$new_course_id,";
                       }
                       elseif($key == "prev_ref") {
                           if($i == 0) {
                              $query_insert .= "'-1',";
                           }
                           else {
                              $query_insert .= "'".$last_org_id."',";
                           }
                       }
                       else
                          $query_insert .= "".$GLOBALS['adodb']->Quote($value).",";
                    }
               }
               $query_insert = trim($query_insert, ",").")";
               //echo $query_insert."<br><br>";
               $result_insert = sql($query_insert,"err insert organizations: $query");
               $last_org_id = sqllast($result_insert);
               $i++;
         }
*/
         //copy directory with course
         if(is_dir("./COURSES/course$id")) {
            mkdir("./COURSES/course$new_course_id/",0775);
            chmod("./COURSES/course$new_course_id/",0775);
            copyDir("./COURSES/course$id", "./COURSES/course$new_course_id/");
         }

         header("location: courses.php4");
}

function show_sublevel( $divs, $did, $sh="",$current ){
    $current1 = explode(';', $current);
    $current1 = is_array($current1) ? $current1 : array();
        if (is_array($divs)) {
  foreach( $divs as $r ){
    if($r[owner_did] == $did ){
     //if( $did == 0 ){ $b=""; $bb="</b>"; } else{ $b=""; $bb="";}
     if($r[color]>""){ $color=$r[color]; $pic=""; }else{ $color="white"; $pic="-";}
      $tmp.="<option value=\"{$r[did]}\"";
      if (in_array($r['did'], $current1)) {
          $tmp.=" selected ";
      }
      $tmp.=">$sh $b $r[name] $bb</option>";


      $tmp.=show_sublevel( $divs, $r[did], $sh."..", $current);
    }
  }
        }
  return( $tmp );
}

function get_sublevels( $divs, $did, $sh="",$current ){
    static $sublevels_arr;
    static $sublevels_current;
    if (!isset($sublevels_arr)){
        $sublevels_arr = array();
    }
    if (!isset($sublevels_current)){
        $sublevels_current = (int) $current;
    }
    $current1 = explode(';', $current);
    $current1 = is_array($current1) ? $current1 : array();
    if (is_array($divs)) {
        foreach( $divs as $r ){
            if($r['owner_did'] == $did ){
                $sublevels_arr[$r['did']] = "{$sh} {$r['name']}";
                if (in_array($r['did'], $current1)) {
                    $sublevels_current = $r['did'];
                }
                get_sublevels($divs, $r['did'], $sh."..", $current);
            }
        }
    }
    return array($sublevels_arr, $sublevels_current);
}

function get_structure($table = "courses_groups"){

  $tmp="SELECT * FROM {$table}";
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

function get_structure_level( $divs, $div, $i=0 ){
  // check infinite loop
  $i++;
  if( ( $divs[ $div ][ owner_did ] > 0 ) && ( $i < count ( $divs ) ) ){
     $level=get_structure_level( $divs, $divs[ $div ][ owner_did ], $i ) + 1;
//     echo "level= $level !! ";
  }else
    $level = 0;
  return( $level );
}

function set_structure_levels( &$divs ){
if (is_array($divs)) {
  foreach( $divs as $div ){
//    echo $div[ did ]."; ";
    $level = get_structure_level( $divs, $div[ did ] );
    $divs[ $div[ did ] ] [ level ] = $level;
    $divs[ $div[ did ] ] [ org ] = $i++;
  }
}
}

function export_course($cid) {
    if ($cid) {
        require_once($GLOBALS['wwf'].'/lib/classes/CourseContent.class.php');
        require_once($GLOBALS['wwf'].'/lib/classes/CourseStructureExport.class.php');

        $xml = CCourseStructureExport::export($cid);
        header("Content-type: text/xml");
        header("Content-Disposition: attachment; filename=imsmanifest.xml" );
        header("Expires: 0");
        header("Cache-Control: no-cache");
        header("Pragma: no-cache");
        echo iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,'UTF-8',$xml);
        exit();
    }
}

function is_brokenChain ($cid,$chain) {
    require_once("lib/classes/Chain.class.php");
    $items = CChainItems::get_as_array($chain);
    if (is_array($items) && count($items)) {
        $emptyItems = array();
        foreach ($items as $item) {
            if (!CChainItems::get_subject($cid,0,$item)){
                return true;
            }
        }
    }
    return false;
}

?>