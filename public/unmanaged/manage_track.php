<?
   require_once('1.php');
   require_once('courses.lib.php');
   require_once('tracks.lib.php');
   require_once('move2.lib.php');
   require_once('lib/classes/Credits.class.php');

   $s[user][assort]=(isset($s[user][assort])) ? $s[user][assort] : 1;
   $s[user][corder]=(isset($s[user][corder])) ? $s[user][corder] : 1;

   $assort=(isset($_GET['assort'])) ? intval($_GET['assort']) : "";

   if ($assort==$s[user][assort]) $s[user][corder]=($s[user][corder]==1) ? 2 : 1;
   if ($assort) $s[user][assort]=$assort;

  $s[user][tfull]=(isset($s[user][tfull])) ? $s[user][tfull] : 1;


  $tfull=(isset($_GET['all'])) ? intval($_GET['all']) : "";
  if ($tfull) $s[user][tfull]=$tfull;

  $tfull=$s[user][tfull];


function getCoursesList( $mid ){
  $l=getCourses( $mid );
//  echo "$l!! $mid <BR>";
  if ( count( $l ) > 0 ){
     $tmp="<span class='small'>";
     foreach( $l as $cc){
       $tmp.="<li>".$cc;
//       echo $cc."!! <BR>";
     }
     $tmp.="</span>";
  }
  return( $tmp );
}

function getCourses( $mid ){
 // формирует массив названий курсов
 $q="SELECT Courses.CID as CID, Courses.Title as Title
           FROM Courses, Students
           WHERE Courses.CID=Students.CID
             AND Students.MID=$mid
                  ORDER BY Courses.Title
 ";
                        //Courses.CID=Students.CID
            // AND
           //Students.cgid, Students.Registered
  $r=sql( $q, "ERRGETMIDCOURSES");
//  echo "<H1>$q</H1>";
  while ( $rr=sqlget( $r ) ){
    $cid=$rr[CID];
    $courses[ $cid ]=$rr[Title];

//    $c=$rr[Title];
//    echo $courses[ $cid ]."!".$cid."! $mid <BR>";
  }
 sqlfree($r);
 return( $courses );
}

function getCoursesCid( $mid ){
 // формирует массив названий курсов
 $q="SELECT Courses.CID as CID, Courses.Title as Title
           FROM Courses, Students
           WHERE Courses.CID=Students.CID
             AND Students.MID=$mid
                    ORDER BY Courses.Title
 ";
                        //Courses.CID=Students.CID
            // AND
           //Students.cgid, Students.Registered
  $r=sql( $q, "ERRGETMIDCOURSES");
//  echo "<H1>$q</H1>";
  while ( $rr=sqlget( $r ) ){
    $cid=$rr[CID];
    $courses[ $cid ]=$rr[CID];
  }
 sqlfree($r);
 return( $courses );
}

function getAllGroups( $void ){
   // формирует массив названий групп
   $r=sql("SELECT * FROM cgname","errGR75");
   while ( $rr=sqlget($r) ){
      $groups[$rr[cgid]]=$rr[name];
   }
   sqlfree($r);
   return($groups);
}

function selectFilter( $trid, $cid, $grid, $mask, $status, $level, $order="People.LastName" ){
   // $mask - маска фамилии
   //        маска имени
   //                 средний балл
   // $status -        тип (студент, выпускник, абит)
   // формируем список людей - MIDS по специальности
   // если укзана специальность и или семестр

   $arrJoin = array();
   $arrSelect = array('
         People.MID as MID,
         People.LastName as LastName,
         People.Patronymic as Patronymic,
         People.FirstName as FirstName');
   $arrWhere = (intval( $trid ) > 0) ? array('1=1') : array('1=0');
   if (intval( $trid ) > 0) {
      $arrSelect[] = "
         tracks2mid.level as level,
         tracks2mid.trid as trid,
         tracks2mid.started as started,
         tracks2mid.stoped as stoped,
         tracks2mid.status as status,
         tracks2mid.changed as changed";
      $arrJoin[] = "
        LEFT JOIN Students ON (People.`MID` = Students.`MID`)
        LEFT JOIN cgname ON (Students.cgid = cgname.cgid)
        LEFT JOIN groupuser ON groupuser.mid=People.MID
        LEFT JOIN groupname ON groupuser.gid=groupname.gid
        INNER JOIN tracks2mid ON (People.`MID` = tracks2mid.`mid`)
        INNER JOIN tracks ON (tracks2mid.trid = tracks.trid)";
      $arrWhere[] = "tracks.trid = {$trid}";
      if (intval( $level ) >= 0) {
         $arrWhere[] = "tracks2mid.level = ".(int) $level;
      }
   }
//   if( intval( $grid ) >= 0 ){ // если указана группа
//      $arrJoin[] = "
//         INNER JOIN Students ON (People.`MID` = Students.`MID`)";
//      $arrWhere[] = "Students.cgid={$grid}";
//   }
    if ($grid != -1) {
        switch ($grid[0]) {
            case "d":
            $cgid = (int)substr($grid, 1);
            break;
            case "g":
            $gid = (int)substr($grid, 1);
            break;
            default:
            break;
        }
        if ($cgid) {
            $arrWhere[] = "Students.cgid='{$cgid}'";
            if (!intval($trid)) $arrJoin[] = "INNER JOIN Students ON (People.`MID` = Students.`MID`)";
        }
        if ($gid) {
            $arrWhere[] = "groupuser.gid='{$gid}'";
            if (!intval($trid)) $arrJoin[] = "INNER JOIN groupuser ON (groupuser.mid=People.MID)";
        }
    }

   $strSelect = implode(",", $arrSelect);
   $strJoin = implode("", $arrJoin);
   $strWhere = implode(" AND ", $arrWhere);
   $fg = "
      SELECT DISTINCT {$strSelect}
      FROM People {$strJoin}
      WHERE
         {$strWhere}
      ORDER BY $order";
   $res=sql($fg,"ERRfilt2");// выбирает все MID
    $i = 0;
    while($r = sqlget( $res)) {
      $sql = "SELECT cgid FROM Students WHERE MID = ".$r['MID'];
      $result = sql($sql);
      $row = sqlget($result);
      //echo "cgid: ".$row[cgid]."<br>";

      $F=TRUE;
      if( $F ) {
         $mids[$i][mid]=$r[MID];
         $mids[$i][LastName]=$r[LastName];
         $mids[$i][FirstName]=$r[FirstName];
         $mids[$i][Patronymic]=$r[Patronymic];
//         $mids[$i][group]=$r['group_' . $grid[0]];
//         $mids[$i][cgid]=$row[cgid];
         $mids[$i][cid]=$r[CID];
         $mids[$i][level]=$r[level];
         $mids[$i][trid]=$r[trid];
         $mids[$i][started]=$r[started];
         $mids[$i][changed]=$r[changed];
         $mids[$i][status]=$r[status];
         $mids[$i][stoped]=$r[stoped];
         $mids[$i][sign]=$r[sign];
         $i++;
      }
   }
   sqlfree($res);
   return( $mids );
}

function getStatusTitle( $status ){
   if( $status == 0 ) return(_("остановлено"));
   if( $status == 1 ) return("");
}

function writeStudents( $trid, $cid, $grid, $mask, $status, $level, $order ){
    return '<br>';
   // выводит список отобранных фильтацией студентов
   $mids = selectFilter( $trid, $cid, $grid, $mask, $status, $level, $order );
   $allg=getAllGroups( $v );
   $width="width=40%";
   $tmp.=_("всего записей:")." ".count($mids)."<INPUT style='display:none' type=text name=ORDER value=$order><table width=100% class=main cellspacing=0>";
   $tmp.="<tr><th><input type='checkbox' id='checkAllCheckBox' onclick=\"checkAll(this);\"></th><th width=100%>"._("ФИО")."</th><!--th>"._("группа")."</th-->
   <th>"._("сем.")."</th><th>"._("принят")."</th><th>"._("переведен")."</th><!-- th>"._("статус")."</th --><th>"._("подпись")."</th><th>останов./<BR>"._("продол.")."</th><th>"._("баланс")."</th><th>"._("оценки за курсы")."</th></tr>";
   if(is_array($mids) && (count($mids) > 0) )
      //$ii = 0;
      $counter = 1;
      foreach( $mids as $i=>$st ) {
         $tmp.="<tr valign=top>";
         if( $st[mid] != $mids[$i-1][mid] ) {
            $money=getAccountState( $st[trid], $st[mid] );
            if( empty($money) || $money < 0 ) $money="0";
            if ( $st[stoped] ) $ds=date("d.m.y",$st[stoped]); else $ds="";
            if ( $st[changed] ) $dc=date("d.m.y",$st[changed]); else $dc="";
            //$name_check = "check_".$i;
            $tmp.="<td><input type='checkbox' name='pip[]' id='pip".(int) $counter."' value=$st[mid]></td>";
            $counter++;
            $tmp.="<td>".$st[LastName]." ".$st[FirstName]." " .$st[Patronymic]. "</td>
                   <td>".$st[level]."</td>
                   <td>".date("d.m.y",$st[started])."</td>
                   <td>".$dc."</td>
                   <td>".$st[sign]."</td>
                   <td>".$ds."</td>
                   <td>".$money."</td>
                   <td $width><a ";
            if ($GLOBALS['controller']->enabled) $tmp .= "target='_blank'";
            $tmp .= "href='plan.php?mid=".$st[mid]."&trid=".$st[trid]."'>".geticon("edit")."</a>".$courses."</td>";
         }
         $tmp.="</tr>";
        //$ii++;
      }
   //$tmp.="<tr><td colspan='11'><input type='checkbox' id='checkAllCheckBox' onclick=\"checkAll(this);\">&nbsp;Выделить всех</td></tr>";

   $tmp.="</TABLE>";
   $tmp.="
   <script language='javascript'>
    function checkAll(element) {
            var i=1;
            elm = document.getElementById('pip'+i);
            while (elm){
                elm.checked = element.checked;
                i++;
                elm = document.getElementById('pip'+i);
            }
    }
   </script>
   ";
   return( $tmp );
}


function writeFilter( $trid, $cid, $grid, $mask, $status, $level, $ORDER){
   $r=sql("SELECT * FROM tracks ORDER BY name","ERRGETTR");
   $trackFilter = new CTrackFilter($GLOBALS['TRACK_FILTERS']);
   while ( $rr=sqlget($r) ){
      if (!$trackFilter->is_filtered($rr['trid'])) continue;
      if ($trid ==$rr[trid]) $ss="selected "; else $ss="";
         $tr.="<option value=".$rr[trid]." $ss>".$rr[name]."</option>";
         $filter_tracks[$rr[trid]] = $rr[name];
   }
   $GLOBALS['controller']->addFilter(_("Специальность"), 'TRACKS',$filter_tracks,$trid,true);
   sqlfree( $r );
   $levels=getTrackLevelsCount( -1 );
   if ($level==0) $tr.='selected';
   $filter_level['-2'] = _("Претенденты");
   for($i=1;$i<=$levels;$i++){
       $filter_level[$i] = _("Семестр")." $i";
   }

   $filter_level_value = $level;
   if ($filter_level_value=='0') $filter_level_value='-2';
   if ($filter_level_value=='-1') $filter_level_value='0';
   $GLOBALS['controller']->addFilter(_("Семестр"),'LEVEL',$filter_level,$filter_level_value,false);

   $r=sql("SELECT CID, Title FROM Courses WHERE Status>0 ORDER BY Courses.Title","ERRGETTR");
   while ( $rr=sqlget($r) ){
      $filter_kurses[$rr[CID]] = $rr[Title];
   }

   sqlfree( $r );
   $r=sql("SELECT CID, Title FROM Courses WHERE Status=0 ORDER BY Courses.Title","ERRGETTR");
   while ( $rr=sqlget($r) ){
      $filter_kurses[$rr[CID]] = $rr[Title];
   }
   //$GLOBALS['controller']->addFilter('Курсы','COURSES',$filter_kurses,$cid,false,'-1');
   sqlfree( $r );

   $filter_groups = selGrved($CID,$grid,true);
   $GLOBALS['controller']->addFilter(_("Группа"),'GROUPS',$filter_groups,$grid, false);

   return($s1.$s.$tr.$gr.$ball.$name.$order.$view.$s2); //$cs.
}


function writeAction( $trid, $gid ){
    return '';

  global $PHP_SELF;

  $act="<input type=text style='display:none' name=TRACKS value=$trid >";
  $act .= "<input type=text style='display:none' name=GROUPS value=$gid >";

  $act.="<SELECT NAME=c onChange=\"
                  removeElem('m0');
                  removeElem('m1');
                  removeElem('m2');
                  removeElem('m3');
                  removeElem('m4');
                  removeElem('m5');
                  removeElem('m6');
                  removeElem('m7');
                  removeElem('m8');
                  removeElem('m9');

     putElem('m'+(selectedIndex));\">
    <option value=-1>--"._("выбрать")."--</option>
    <option value='message'>"._("послать сообщение")."</option>
    <option value='nextlevel'>"._("перевести на СЛЕД. семестр")."</option>
    <option value='prevlevel'>"._("вернуть на ПРЕД. семестр")."</option>
    <option value='finish'>"._("закончить обучение")."</option>
    <option value='break'>"._("отчислить")."</option>
    <option value='freeze'>"._("остановить обучение")."</option>
    <option value='continue'>"._("продолжить обучение")."</option>";
  if (defined('USE_BOLOGNA_SYSTEM') && !USE_BOLOGNA_SYSTEM) {
        $act .= "<option value='add_money'>"._("добавить денег")."</option>";
  } else {
        $act .= "<option value='add_money'>"._("добавить кредиты")."</option>";
  }

  $act .= "
    <option value='other'>зачислить на другую специальность</option>
    <!--<option value='erase'>"._("удалить навсегда")."</option>-->
     </SELECT><BR>";

 /*
  $act.="<SELECT NAME=c onChange=\"
            removeElem('m0');
                  removeElem('m1');
        removeElem('m2');
                  removeElem('m3');
                  removeElem('m4');
                  removeElem('m5');
                  removeElem('m6');
             removeElem('m7');
                  removeElem('m8');
                  removeElem('m9');
                  putElem('m'+(selectedIndex));\">
    <option value=-1>--выбрать--</option>
    <option id=0 value='message'>послать сообщение</option>
    <option id=1 value='nextlevel'>перевести на СЛЕД. семестр</option>
    <option id=2 value='prevlevel'>вернуть на ПРЕД. семестр</option>
    <option id=3 value='finish'>закончить обучение</option>
    <option id=4 value='break'>отчислить</option>
    <option id=5 value='freeze'>остановить обучение</option>
    <option id=6 value='continue'>продолжить обучение</option>
    <option id=7 value='add_money'>добавить денег</option>
    <option id=8 value='erase'>удалить навсегда</option>
     </SELECT><BR>";
                                                        //removeElem('m4');removeElem('m5');removeElem('m6');
//    <option id=4 value='exclude'>отчислить с курса</option>
//    <option id=5 value='finish'>закончить обучение на курсе</option>
//    <option id=1 value='include'>зачислить на курс</option>*/

  $r=sql("SELECT * FROM tracks","ERRGETTR");
  while ( $rr = sqlget( $r ) ){
          $trlist[ $rr[trid] ]=$rr[name];
  }
  sqlfree( $r );

  $r=sql("SELECT CID, Title FROM Courses ORDER BY Courses.Title","ERRGETTR"); //WHERE Status>0
  while ( $rr = sqlget( $r ) ){
    $clist[ $rr[CID] ]=$rr[Title];
  }
  sqlfree( $r );

  $tmp="<span style='display:none' id=m0></span>";
  $tmp.="<span style='display:none' id=m1><br>
         <textarea cols=50 rows=3 name=message>"._("текст сообщения")."</textarea><BR>
         <input type=SUBMIT value=' "._("Отослать")." '/>
        </span>";
  $tmp.="<span style='display:none' id=m2><input type=SUBMIT value=' "._("Применить!")." '></span>";
  $tmp.="<span style='display:none' id=m3><input type=SUBMIT value=' "._("Применить!")." '></span>";
  $tmp.="<span style='display:none' id=m4><input type=SUBMIT value=' "._("Применить!")." '></span>";
  $tmp.="<span style='display:none' id=m5><input type=SUBMIT value=' "._("Применить!")." '></span>";
  $tmp.="<span style='display:none' id=m6><input type=SUBMIT value=' "._("Применить!")." '></span>";
  $tmp.="<span style='display:none' id=m7><input type=SUBMIT value=' "._("Применить!")." '></span>";
  $tmp.="<span style='display:none' id=m8><br>
         <input type=text name=money value=0>
         <input type=SUBMIT value=' "._("Добавить")." '/>
        </span>";
  //$tmp.="<span style='display:none' id=m9><input type=SUBMIT value=' "._("Применить!")." '></span>";
  $tmp.="
  <span style='display:none' id=m9><br>
  <select name=\"track_id\">".
  getTracksOptions()
  ."</select>
  <input type=SUBMIT value=' "._("Применить!")." '>
  </span>";
  return( $s1.$act.$tmp );
}

$CID=(isset($_GET['CID'])) ? $_GET['CID'] : $CID=0;
$CID=(isset($_POST['CID'])) ? $_POST['CID'] : $CID;

$pm=(isset($_POST['pm'])) ? $_POST['pm'] : array();
$pc=(isset($_POST['pc'])) ? $_POST['pc'] : array();

$message=(isset($_POST['message'])) ? $_POST['message'] : "";


$go=(isset($_POST['c'])) ? $_POST['c'] : "";


if (!$dean && (!isset($s[tkurs]))) login_error();
if ($CID && (!isset($s[tkurs][$CID]))) login_error();


if ( ! isset ($trid) ) $trid=-1;
if ( ! isset ($cid) ) $cid=-1;
if ( ! isset ($grid) ) $grid=-1;
if ( ! isset ($level) ) $level=-1;
if ( ! isset ($status) ) $status=2;
if ( ! isset ($mask) ) $mask="";

if(($trid == -1) && isset($TRACKS))
    $trid = $TRACKS;

if (($GLOBALS['controller']->enabled)) {
    $ORDER='People.LastName';
    if (!isset($c)) {
        if ($LEVEL=='0') $LEVEL='-1';
        if ($LEVEL=='-2') $LEVEL='0';
        if (isset($TRACKS)) $c='select';
    }
}

//предотвращение повторной отправки POST
if ($_POST['post_flag'] == $_SESSION['post_flag'][md5($_SERVER['REQUEST_URI'])]) {
    $c = 'select';
}else {
    if (is_array($_SESSION['post_flag'])) {
        $_SESSION['post_flag'][md5($_SERVER['REQUEST_URI'])] = $_POST['post_flag'];
    }else {
        $_SESSION['post_flag'] = array(md5($_SERVER['REQUEST_URI'])=>$_POST['post_flag']);
    }
}

$track_courses = getTrackCoursesArr( $trid);
switch ($c) {
   case "erase":
      foreach($pip as $k=>$mid){
         erase( $mid ); // echo "<H1>$mid</H1>";
     }
   break;
   case "select":
      $trid      = $TRACKS;
      $cid       = $COURSES;
      $grid      = $GROUPS;
      $mask      = $FIRSTNAME;
      $status    = $STATUS;
      $level     = $LEVEL;
      $show_stud = 1;
   break;
   case "message":  // послать сообщение
      $show_stud=1;
      if (is_array( $pip ) && count($pip)){
         foreach($pip as $k=>$mid){
            mailToteach("elmes",$mid,null,$message);
         }
         $sss=implode(",",$pip);
         $sss=_("Отправлено")." ".count($pip)." "._("сообщений");
      }
      $trid=$TRACKS;
      $level = $LEVEL;
      $grid = $GROUPS;
      break;
   case "nextlevel":  // перевести на след ступень
      $show_stud=1;
      if (is_array($pip) && count( $pip )) {
         if (!$dummy = studentsToLevel($TRACKS, $pip, '+')) {
             $sss = _('Один или несколько слущателей не были переведены на след. семестр. Убедитесь в наличии достаточного количества средств.');
         }elseif (strlen((string)$dummy) > 1) {
             $sss = $dummy;
         }
         
/*         $max_level=last_level( $trid );
         foreach($pip as $k=>$mid) {
           levelOnTheTrack( $TRACKS, $mid, "+", $max_level );
         }
*/
      }
      else
         $sss=_("НЕ ВЫБРАНЫ УЧАЩИЕСЯ");
      //echo $sss;
//  $sss=implode(",",$pip)."->".$TRACK;
      $trid=$TRACKS;
      $level = $LEVEL;
      $grid = $GROUPS;
   break;
   case "prevlevel":  // перевести на след ступень
      $show_stud=1;
      if (is_array($pip) && count( $pip )) {
         studentsToLevel($TRACKS, $pip, '-');
/*         $max_level=last_level( $trid );
         foreach($pip as $k=>$mid) {
            levelOnTheTrack( $TRACKS, $mid, "-", $max_level );
         }
*/
      }
      else
         $sss=_("НЕ ВЫБРАНЫ УЧАЩИЕСЯ");
      //echo $sss;
//  $sss=implode(",",$pip)."->".$TRACK;
      $trid=$TRACKS;
      $level = $LEVEL;
      $grid = $GROUPS;
   break;
   case "break":  // отчислить с обучения
      $show_stud=1;
      if( is_array($track_courses)) {
         if (is_array($pip) && count($pip)){
            foreach($track_courses as $cid) {
            foreach($pip as $k=>$mid) {
                del($mid,$cid,false);
               //toab( $mid, $cid );
            }
            }
         }
        $query = "DELETE FROM tracks2mid WHERE mid='{$mid}' AND trid = '$trid'";
        sql($query,"abiturDel05");

//         $sss=implode(",",$pip)."->".$FROM_COURSE;
      }
      else
         $sss=_("ОШИБКА: УКАЖИТЕ КУРС");

      $trid=$TRACKS;
      $level = $LEVEL;
      $grid = $GROUPS;
   break;
   case "freeze":  // остановить обучение
      $show_stud=1;
      if (is_array($pip) && count( $pip )) {
         foreach($pip as $k=>$mid){
            setStatus( $TRACKS, $mid, 0 );
         }
      }
      else
         $sss=_("НЕ ВЫБРАНЫ УЧАЩИЕСЯ");
      //echo $sss;
      $trid=$TRACKS;
      $level = $LEVEL;
      $grid = $GROUPS;
   break;
   case "continue":  // продолжить обучение
      $show_stud=1;
      if (is_array($pip) && count( $pip )) {
         foreach($pip as $k=>$mid) {
            setStatus( $TRACKS, $mid, 1 );
      }
      }
      else
         $sss=_("НЕ ВЫБРАНЫ УЧАЩИЕСЯ");
      //echo $sss;
      $trid=$TRACKS;
      $level = $LEVEL;
      $grid = $GROUPS;
   break;
   case "finish":  // закончить обучение
      $show_stud=1;
      if (is_array($pip) && count( $pip )) {
         foreach($pip as $k=>$mid){
            stopTrack( $TRACKS, $mid );
         }
      }
      else
         $sss=_("НЕ ВЫБРАНЫ УЧАЩИЕСЯ");
      //echo $sss;
      $trid=$TRACKS;
      $level = $LEVEL;
      $grid = $GROUPS;
   break;
   case "inc2track":  // зачислить на специальность
      $show_stud=1;
      if (is_array($pip) && count( $pip )) {
         foreach($pip as $k=>$mid) {
            startTrack( $TRACKS, $mid );
         }
      }
      else {
         $sss=_("НЕ ВЫБРАНЫ УЧАЩИЕСЯ");
      }
      //echo $sss;
      $trid=$TRACKS;
      $level = $LEVEL;
      $grid = $GROUPS;
   break;
   case "add_money":  // зачислить на специальность
      $show_stud=1;
      if (is_array($pip) && count( $pip )) {
         foreach( $pip as $k=>$mid ) {
            $gg=add_money( $TRACKS, $mid, $money );
         }
      }
      else
         $sss=_("НЕ ВЫБРАНЫ УЧАЩИЕСЯ");
      //echo $sss;
      $trid=$TRACKS;
      $level = $LEVEL;
      $grid = $GROUPS;
   break;
   case 'other': // зачислить на другую специальность
      $show_stud = 1;
      $track_id = (int) $_POST['track_id'];
      if (is_array($pip) && count($pip) && ($track_id>0)) {
          foreach($pip as $mid) {
              registration2track($track_id,$mid,0,false);
          }
      }
   break;
   default:
      $show_stud = 0;
   break;
}

$curcid=$CID;
$html=show_tb(1);
$allheader=ph(_("ПЕРЕВОДЫ")." $sss");
//$allcontent=loadtmpl("manage_stud.htm");
//$alltr=loadtmpl("abitur-tr.html");
$gselect = writeFilter( $trid, $courses_view, $grid, $mask, $status, $level, $ORDER );
if ($GLOBALS['controller']->enabled) $gselect = '';
if( $show_stud ) {
  $action = writeAction( $trid, $grid );
  $studs = writeStudents( $trid, $courses_view, $grid, $mask, $status, $level, $ORDER );
}

$other="";
if($CID && $tfull==2) $other.=studListOther($CID);
$deangr=($dean) ? loadtmpl("abitur-gr.html") : "";

if ($GLOBALS['controller']->enabled) $allheader='';

$html=str_replace("[ALL-CONTENT]",$allcontent,$html);
$html=str_replace("[PHP_SELF]",$PHP_SELF,$html);
$html=str_replace("[HEADER]",$allheader,$html);
$html=str_replace("[SELECT-COURSES]",$cselect,$html);
$html=str_replace("[STUDS]",$studs,$html);
$html=str_replace("[ACTION]",$action,$html);
$html=str_replace("[SELECT-GROUPS]",$gselect,$html);
$html=showSortImg($html,$s[user][assort]);

// THRASH BEGIN

require_once($GLOBALS['wwf'].'/lib/classes/Pager.class.php');

$join = "";
if ($grid) {
    $join = "INNER JOIN groupuser ON groupuser.mid = People.MID";
}
$join .= " INNER JOIN tracks2mid ON tracks2mid.mid = People.MID";

$where = "tracks2mid.trid = '".(int) $trid."'";
if ($level >= 0) {
    $where .= " AND tracks2mid.level = '".(int) $level."'";
}

if ($grid) {
    $where .= " AND groupuser.gid = '".(int) substr($grid,1)."'";
}

$pagerOptions = array(
  'CDBPager_table'   => 'People',
  'CDBPager_idName'  => 'People.MID',
  'CDBPager_select'  => '
       People.MID as MID,
       People.LastName as LastName,
       People.FirstName as FirstName,
       People.Patronymic as Patronymic
       ',
  'CDBPager_join'    => $join,
  'CDBPager_where'   => $where,
  'CDBPager_group'   => '',
  'CDBPager_order'   => 'People.LastName, People.FirstName, People.Patronymic',
  'mode'    => 'Sliding',
  'delta'   => 5,
  'perPage' => 50
);

$pager = new CDBPager($pagerOptions);
$page = $pager->getData();

$people = array();
while($row = sqlget($page['result'])) {
    $people[$row['MID']] = $row;
}

if (count($people)) {
    $sql = "SELECT
            tracks2mid.mid,
            tracks2mid.level as level,
            tracks2mid.trid as trid,
            tracks2mid.started as started,
            tracks2mid.stoped as stoped,
            tracks2mid.status as status,
            tracks2mid.changed as changed
            FROM tracks2mid
            WHERE mid IN ('".join("','", array_keys($people))."') AND trid = '".(int) $trid."'";
    $res = sql($sql);

    while($row = sqlget($res)) {
        $people[$row['mid']]['level']   = $row['level'];
        $people[$row['mid']]['trid']    = $row['trid'];
        $people[$row['mid']]['started'] = $row['started'];
        $people[$row['mid']]['stoped']  = $row['stoped'];
        $people[$row['mid']]['status']  = $row['status'];
        $people[$row['mid']]['changed'] = $row['changed'];
    }

    $sql = "SELECT sum, mid FROM money WHERE mid IN ('".join("','", array_keys($people))."') AND trid = '".(int) $trid."'";
    $res = sql($sql);

    while($row = sqlget($res)) {
        $people[$row['mid']]['money'] = max($row['sum'],0);
    }
}

$specs = array();
$sql = "SELECT trid, name FROM tracks WHERE status > 0 ORDER BY name";
$res = sql($sql);

while($row = sqlget($res)) {
    $specs[$row['trid']] = $row['name'];
}

$smarty = new Smarty_els();
$smarty->assign('specs', $specs);
$smarty->assign('trid', $trid);
$smarty->assign('grid', $grid);
$smarty->assign('level', $level);
$smarty->assign('people', $people);
$smarty->assign('pager', $page['links']);
$smarty->assign('sitepath', $GLOBALS['sitepath']);
$smarty->assign('icon_edit', getIcon('edit'));
$smarty->assign('okbutton', okbutton());
$html .= $smarty->fetch('manage_track.tpl');

// THRASH END

if ($GLOBALS['controller']->enabled) {
    $html=words_parse($html,$words);
    $html=path_sess_parse($html);
    if (empty($studs)) $html='';
    $GLOBALS['controller']->setMessage($sss);
    $GLOBALS['controller']->captureFromReturn(CONTENT,$html);
}
printtmpl($html);
?>