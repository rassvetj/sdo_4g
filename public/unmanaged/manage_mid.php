<!-- ‚…ђ‘€џ 2.1
€§¬Ґ­Ґ­Ёп („Љ)
- ¤®Ў «Ґ­л ббл«ЄЁ Ї® § Є« ¤Є ¬ ­  ўлЇгбЄ­ЁЄ®ў Ё  ЎЁвгаЁҐ­в®ў
- ¤®о ў«Ґ­ ўлў®¤ д®в®Ја дЁ©
-->

<?
   require_once('1.php');
   require_once('courses.lib.php');
   require_once('tracks.lib.php');


   $s[user][assort]=(isset($s[user][assort])) ? $s[user][assort] : 1;
   $s[user][corder]=(isset($s[user][corder])) ? $s[user][corder] : 1;

   $assort=(isset($_GET['assort'])) ? intval($_GET['assort']) : "";

   if ($assort==$s[user][assort]) $s[user][corder]=($s[user][corder]==1) ? 2 : 1;
   if ($assort) $s[user][assort]=$assort;

  $s[user][tfull]=(isset($s[user][tfull])) ? $s[user][tfull] : 1;


  $tfull=(isset($_GET['all'])) ? intval($_GET['all']) : "";
  if ($tfull) $s[user][tfull]=$tfull;

  $tfull=$s[user][tfull];


function getAllCourses( $void ){
 // формирует массив названий курсов
 $r=sql("SELECT CID, Title FROM Courses ORDER BY Courses.Title","ERRGETTR");
  while ( $rr=sqlget($r) ){
    $courses[$rr[CID]]=$rr[Title];
  }
 sqlfree($r);
 return($courses);
}

function getAllGroups( $void ){
 // формирует массив названий курсов
 $r=sql("SELECT * FROM cgname","errGR75");
  while ( $rr=sqlget($r) ){
    $groups[$rr[cgid]]=$rr[name];
  }
 sqlfree($r);
 return($groups);
}

function selectFilter( $trid, $cid, $grid, $mask, $status, $level ){
  // $mask - маска фамилии
  //        маска имени
  //                 средний балл
  // $status -        тип (студент, выпускник, абит)

 // формируем список людей - MIDS по специальности

 // если укзана специальность и или семестр
  if( intval($trid) > 0 ){ // указана специальность
    $c.="  tracks2mid.trid=$trid AND People.MID=tracks2mid.mid";
  }
  if( intval($level) >= 0 ){ // если указана ступень обучения
//    if( isset ($c) ) $c.=" AND";
    $c.="  AND tracks2mid.level=$level AND People.MID=tracks2mid.mid";
  }
//  if( intval($cid) > 0 ){ // если указан курс
//    if( isset ($c) ) $c.=" AND";
//    $c.="  AND tracks2course.cid=$cid AND Students.CID";
//  }
//  if( intval($grid) > 0 ){ // если указана группа
//    if( isset ($c) ) $c.=" AND";
//    $c.=" AND Students.cgid=$grid ";
//  }
  if( isset( $c ) ) $c=" WHERE ".$c;
              //Students.cid as CID, Students, tracks2course

//              Students.CID as CID

  $fg="SELECT
              People.MID as MID,
              People.LastName as LastName,
              People.FirstName as FirstName,
              tracks2mid.level as level,
              tracks2mid.trid as trid
         FROM  People, tracks2mid
              $c "; //Students

  echo "$c<BR>";
  echo $fg;
  $res=sql($fg,"ERRfilt2"); // выбирает все MID
  while( $r=sqlget( $res) ){
      if ( strlen( $mask > 0) ){
        $F=isCorrect($mask,$r[LastName]);// СДЕСЬ СДЕЛАТЬ АНАЛИЗ МАСКИ С УЧЕТОМ РУССКИХ БУКВ
      }else
        $F=TRUE;
      if( $F ){
        $mids[$i][mid]=$r[MID];
        $mids[$i][LastName]=$r[LastName];
        $mids[$i][FirstName]=$r[FirstName];
        $mids[$i][cgid]=$r[cgid];
        $mids[$i][cid]=$r[CID];
        $mids[$i][level]=$r[level];
        $mids[$i][trid]=$r[trid];
        $i++;
      }
  }
  sqlfree($res);

 // если укзан специальность и или семестр

 /* if(count($courses)>0)
    $cs="AND Students.cid IN (".implode(",",$courses).")"; else $cs="";

    $fg="SELECT * FROM Students, People
             WHERE People.MID=Students.mid
                $gr
                $cs
                ORDER BY Students.mid"; // AND Students.cid=$course
    //echo $fg;
*/

  return( $mids );
}

function isCorrect( $mask, $name ){
 // проверяет подходит ли имя под маску
  $f=strstr( $name, $mask );
  //echo $mask.":".$name."<BR>";
  return( $f );
}
/*Table: students lines=20
SID ( int ) [not_null primary_key unique_key auto_increment ]
MID ( int ) [not_null ]
CID ( int ) [not_null ]
cgid ( int ) [not_null ]
Registered ( int ) [not_null ]
*/



function writeAction( ){

  $s1="<FORM  NAME='todo' action=$PHP_SELF method='POST'>";
  $act="<SELECT NAME=c onChange=\"
     removeElem('m1');removeElem('m2');removeElem('m3');removeElem('m4');removeElem('m5');removeElem('m6');
     putElem('m'+(selectedIndex));

          \">
    <option value=-1>--"._("выбрать")."--</option>
    <option id=0 value='message'>"._("послать сообщение")."</option>
    <option id=2 value='inc2track'>"._("зачислить на специальность")."</option>
    <option id=3 value='nextlevel'>"._("перевести на след. семестр")."</option>
     </SELECT><BR>";



//    <option id=4 value='exclude'>отчислить с курса</option>
//    <option id=5 value='finish'>закончить обучение на курсе</option>
//    <option id=1 value='include'>зачислить на курс</option>

  $r=sql("SELECT * FROM TRACKS","ERRGETTR");
  while ( $rr = sqlget( $r ) ){
          $trlist[ $rr[trid] ]=$rr[name];
  }
  sqlfree( $r );

  $r=sql("SELECT CID, Title FROM Courses ORDER BY Courses.Title","ERRGETTR"); //WHERE Status>0
  while ( $rr = sqlget( $r ) ){
    $clist[ $rr[CID] ]=$rr[Title];
  }
  sqlfree( $r );

  $tmp="<textarea id='m1' class='hidden' width=50 height=3 name=message>"._("Укажите текст сообщения (пока не подключено)")."</textarea>";

  $tmp.="<SELECT NAME=TO_COURSE id='m2' class='hidden'>";
  $tmp.="<option value=''>-- "._("укажите курс")." --</option>";
  foreach( $clist as $i=>$el )
    $tmp.="<option value='$i'>$el</option>";
  $tmp.="</SELECT>";

  $tmp.="<SELECT NAME=FROM_COURSE id='m5' class='hidden'>";
  $tmp.="<option value=''>-- "._("укажите курс")." --</option>";
  foreach( $clist as $i=>$el )
    $tmp.="<option value='$i'>$el</option>";
  $tmp.="</SELECT>";

  $tmp.="<SELECT NAME=BY_COURSE id='m6' class='hidden'>";
  $tmp.="<option value=''>-- "._("укажите курс")." --</option>";
  foreach( $clist as $i=>$el )
    $tmp.="<option value='$i'>$el</option>";
  $tmp.="</SELECT>";

  $tmp.="<SELECT NAME=TO_TRACK id='m3' class='hidden'>";
  $tmp.="<option value=0>-- "._("укажите специальность")." --</option>";
  foreach($trlist as $i=>$el)
          $tmp.="<option value='$i'>$el</option>";
  $tmp.="</SELECT>";

  $tmp.="<SELECT NAME=TO_NEXT_LEVEL id='m4' class='hidden'>";
  $tmp.="<option value=0>-- "._("укажите специальность")." --</option>";
  foreach($trlist as $i=>$el)
          $tmp.="<option value='$i'>$el</option>";
  $tmp.="</SELECT>";

  $tmp.="<BR><input type=SUBMIT value=' "._("Применить")." '>
  </FORM>";


  return( $s1.$act.$tmp );
}

function writeStudents( $trid, $cid, $grid, $mask, $status, $level ){
  // выводит список отобранных фильтацией студентов
  echo "$trid!!!<BR>";
  $mids = selectFilter( $trid, $cid, $grid, $mask, $status, $level );
  $allc=getAllCourses( $v );
  $allg=getAllGroups( $v );
  $tmp.="<FORM>"._("всего записей:")." ".count($mids)."<TABLE>";
  $tmp.="<tr><th></th><th>"._("Фамилия")."</th><th>"._("Имя")."</th><th>"._("группа")."</th><th>"._("сем.")."</th><th>"._("курс")."</th></tr>";
  if( count($mids)>0 )
  foreach( $mids as $i=>$st ){
    if( $st[mid] != $mids[$i-1][mid] ){
       $tmp.="<tr><td><input type='checkbox' checked name='pip[]' value=$st[mid]></td>";
       $tmp.="<td>".$st[LastName]."</td>
           <td>".$st[FirstName]."</td>
           <td>".$allg[$st[cgid]]."</td>
           <td>".$st[level]."</td>";
//        $mids[$i][trid]=$r[trid];
    }else{
       $tmp.="<td></td><td></td><td></td>
           <td></td>
           <td></td>";
    }
    $tmp.="<td>".$allc[$st[cid]]." (".$st[cid].")</td>
         <tr>";
  }
  $tmp.="</TABLE><FORM>";
  return( $tmp );
}


function writeFilter( $trid, $cid, $grid, $mask, $status, $level ){
  $s1="<form action=$PHP_SELF method=post>
   <input type=hidden name=c value=\"select\">";
  $tr="<SELECT NAME=TRACKS>";
  $tr.="<option value='-1'>"._("Все специальности")."</option>";
  $r=sql("SELECT * FROM TRACKS","ERRGETTR");
  while ( $rr=sqlget($r) ){
      if ($trid ==$rr[trid]) $ss="selected "; else $ss="";
          $tr.="<option value=".$rr[trid]." $ss>".$rr[name]."</option>";
  }
  sqlfree( $r );
  $tr.="</SELECT><BR>";

  $tr.="<SELECT NAME=LEVEL>";
  $levels=getTrackLevelsCount( -1 );
  $tr.="<option value='-1'>"._("все семестры")."</option>";
  $tr.="<option value='0'>"._("претенденты")."</option>";
  for($i=1;$i<=$levels;$i++){
      if ($level == $i) $ss="selected "; else $ss="";
          $tr.="<option value=$i $ss>"._("семестр")." $i</option>";
  }
  $tr.="</SELECT><BR>";

  $cs="<SELECT NAME=COURSES>";
  $cs.="<option value='-1'>"._("все курсы")."</option>";
  $r=sql("SELECT CID, Title FROM Courses WHERE Status>0 ORDER BY Courses.Title","ERRGETTR");
  while ( $rr=sqlget($r) ){
    if ($cid ==$rr[CID]) $ss="selected "; else $ss="";
    $cs.="<option value=".$rr[CID]." $ss>".$rr[Title]."</option>";
  }
  sqlfree( $r );
  $cs.="<option value='-1'>----</option>";
  $r=sql("SELECT CID, Title FROM Courses WHERE Status=0 ORDER BY Courses.Title","ERRGETTR");
  while ( $rr=sqlget($r) ){
    if ($cid ==$rr[CID]) $ss="selected "; else $ss="";
    $cs.="<option value=".$rr[CID]." $ss>".$rr[Title]."</option>";
  }
  sqlfree( $r );


  $cs.="</SELECT><BR>";
/////

  $gr="<SELECT NAME=GROUPS>";
  $gr.="<option value='-1'>"._("Все группы")."</option>";
  $r=sql("SELECT * FROM cgname","ERRGETTR");
  while ( $rr=sqlget($r) ){
     if ($grid ==$rr[cgid]) $ss="selected "; else $ss="";

    $gr.="<option value=".$rr[cgid]." $ss>".$rr[name]."</option>";
  }
  sqlfree( $r );
  $gr.="</SELECT><BR>";

/*  $st="<SELECT NAME=STATUS>";
  $sta[0]="Все ";  $sta[1]="Абитуриенты";   $sta[2]="Учащиеся";   $sta[3]="Выпускники";
  for($i=0;$i<4;$i++){
     if ($status == $i) $ss="selected "; else $ss="";
     $st.="<option value=$i>$sta[$i]</option>";
  }
  $st.="</SELECT><BR>";*/

  $ball="<!--INPUT value=0 name='ballmin' size=2 type='text'> < "._("ср.балл")." < <INPUT value=100 name='ballmax'  size=2 type='text'><BR-->";
 // $name="<INPUT NAME=FIRSTNAME value=$mask>Фамилия<BR><!--INPUT NAME=LASTNAME>Имя <BR-->";
  $s2="<input type=SUBMIT  value=' "._("Применить")." '>";
  //$s2.="<input type='image' name='ok'
  //                               onmouseover='this.src='[PATH]images/send_.gif';'
  //                               onmouseout='this.src='[PATH]images/send.gif';'
  //                               src='[PATH]images/send.gif' align='right' alt='ok' border='0'>";
   $s2.="</FORM>";
  return($s1.$s.$tr.$cs.$gr.$st.$ball.$name.$s2);
}


istest();

$CID=(isset($_GET['CID'])) ? $_GET['CID'] : $CID=0;
$CID=(isset($_POST['CID'])) ? $_POST['CID'] : $CID;

$pm=(isset($_POST['pm'])) ? $_POST['pm'] : array();
$pc=(isset($_POST['pc'])) ? $_POST['pc'] : array();

$go=(isset($_POST['c'])) ? $_POST['c'] : "";

if (!$dean && (!isset($s[tkurs]))) login_error();
if ($CID && (!isset($s[tkurs][$CID]))) login_error();

/*switch ($go) {
   case "stud" : moveStud($pm,$pc); break;
   case "abitur" : moveAbit($pm,$pc); break;
   case "grad" : moveGrad($pm,$pc); break;
   case "other" : moveToStud($pm,$pc); break;
}

if ($go) {
//   exit(location("abitur.php4?CID=$CID$sess"));
   //echo "abitur.php4?CID=$CID";
//   phpinfo();
   exit();
}*/
 if ( ! isset ($trid) ) $trid=-1;
 if ( ! isset ($$cid) ) $cid=-1;
 if ( ! isset ($grid) ) $grid=-1;
 if ( ! isset ($level) ) $level=-1;
 if ( ! isset ($status) ) $status=2;
 if ( ! isset ($mask) ) $mask="";

switch ( $c ) {

case "select":

  $trid=$TRACKS;
  $cid=$COURSES;
  $grid=$GROUPS;
  $mask=$FIRSTNAME;
  $status=$STATUS;
  $level=$LEVEL;
  $show_stud=1;

  //$sss=$TRACKS.":".$COURSES.":".$GROUPS.":".$mask;
  break;
case "message":  // послать сообщение
  $show_stud=1;
  if (is_array( $pip ) && count($pip)){
    foreach($pip as $k=>$mid){
       // echo "<H1>$mid</H1>";
    }
    $sss=implode(",",$pip);
  }

  break;
case "nextlevel":  // перевести на след ступень
  $show_stud=1;
  if (is_array($pip) && count($pip)){
    foreach($pip as $k=>$mid){
//      $cur_level=getLevel($TO_NEXT_TRACK, $mid);
//      setTrack4Stud( $TO_NEXT_TRACK, $mid, $cur_level ); // зачисляет на первый ступень специальности
      nextLevelOnTheTrack( $TO_TRACK, $mid );
    }
   }
  $sss=implode(",",$pip)."->".$TO_NEXT_TRACK;

  break;
case "exclude":  // отчислить с обучения
  $show_stud=1;
  if( isset($FROM_COURSE) && ( FROM_COURSE>=0) ){
    if (is_array($pip) && count($pip)){
    foreach($pip as $k=>$mid){
       toab( $mid, $FROM_COURSE );
    }
   }
   $sss=implode(",",$pip)."->".$FROM_COURSE;
  }else
     $sss=_("ОШИБКА: УКАЖИТЕ КУРС");

  break;
case "include":  // зачислить на курс
  $show_stud=1;
  if( isset($TO_COURSE)  && ( TO_COURSE>=0) ){
   if (is_array($pip) && count($pip)){
    foreach($pip as $k=>$mid){
      tost( $mid, $TO_COURSE);
    }
   }
  $sss=implode(",",$pip)."->".$TO_COURSE;
  }else
     $sss=_("ОШИБКА: УКАЖИТЕ КУРС");


  break;

case "finish":  // закончить обучение
  $show_stud=1;
  if( isset($BY_COURSE)  && ( BY_COURSE>=0) ){
    if (is_array($pip) && count($pip)){
    foreach($pip as $k=>$mid){
      togr( $mid, $BY_COURSE );
    }
   }
  $sss=implode(",",$pip)."->".$BY_COURSE;
  }else
     $sss=_("ОШИБКА: УКАЖИТЕ КУРС");


  break;
case "inc2track":  // зачислить на специальность
  $show_stud=1;
  if( isset($TO_TO_TRACK)  && ( TO_TRACK>=0) ){
  if (is_array($pip) && count($pip)){
    foreach($pip as $k=>$mid){
      //tost( $mid, $TO_TRACK);
      setTrack4Stud( $TO_TRACK, $mid, 1 ); // зачисляет на первый ступень специальности

    }
   }
  $sss=implode(",",$pip)."->".$TO_TRACK;
  }else
     $sss=_("ОШИБКА: УКАЖИТЕ СПЕЦИАЛЬНОСТЬ");

  break;
default:
  $show_stud=0;
 break;

}

$curcid=$CID;

$html=show_tb(1);

$allheader=ph(_("управление учащимися:")."$c:$sss");
//$hstud=ph("Студенты <FONT SIZE=0>>>[<a href=#in>Абитуриенты</a>] >>[<a href=#out>Выпускники</a>]</FONT>");
//$habitur=ph("<a name=in>Абитуриенты</a>");
//$hgrad=ph("<a name=out>Выпускники</a>");
//$hot=ph("<a href='[PAGE]?[SESSID]all=[FULLOT]&CID=[CURCID]'>Все студенты</a>");

$allcontent=loadtmpl("manage_stud.htm");
$alltr=loadtmpl("abitur-tr.html");

$gselect = writeFilter( $trid, $cid, $grid, $mask, $status, $level );
if( $show_stud ){
 $studs = writeStudents( $trid, $cid, $grid, $mask, $status, $level );
 $action = writeAction();
}

// $cabitur=abiturList($CID);
//$cgraduat=gradList($CID);
//$cstudents=studList($CID);

$other="";
if($CID && $tfull==2) $other.=studListOther($CID);

$deangr=($dean) ? loadtmpl("abitur-gr.html") : "";

$html=str_replace("[ALL-CONTENT]",$allcontent,$html);
$html=str_replace("[PHP_SELF]",$PHP_SELF,$html);
//$html=str_replace("[DEANGR]",$deangr,$html);
//$html=str_replace("[CUR-CID]",$CID,$html);
$html=str_replace("[HEADER]",$allheader,$html);
$html=str_replace("[SELECT-COURSES]",$cselect,$html);
$html=str_replace("[STUDS]",$studs,$html);
$html=str_replace("[ACTION]",$action,$html);
$html=str_replace("[SELECT-GROUPS]",$gselect,$html);

$html=showSortImg($html,$s[user][assort]);

printtmpl($html);

function getStudList(){
}
   function getSort($add) {
   global $s;

      $ret="Courses.Title ";
      if (2==$s[user][assort] && $add=="Students.SID") $ret="Students.cgid ";
      if (3==$s[user][assort]) $ret=" lname ";

//      $ret.=$add;

      if (2==$s[user][corder]) $ret.=" DESC";
         else $ret.=" ASC";

      $ret=" ORDER BY ".$ret;
      return $ret;
   }


function abiturList($CID) {
      global $alltr,$s;
      $sel="";
      $n=1;
      $all=array();

         $sql="SELECT  claimants.CID as cid, People.MID as mid, People.FirstName as fname, People.LastName as lname, Courses.Title as title FROM claimants, People, Courses WHERE Courses.CID=claimants.CID AND claimants.MID=People.MID AND claimants.Teacher='0'";
         if ($CID) $sql.=" AND claimants.CID='".$CID."'";
            else $sql.=" AND claimants.CID IN (".implode(",",$s[tkurs]).")";
         //$sql.=" ORDER BY Courses.CID, claimants.SID ASC";
         $sql.=getSort("claimants.SID");
        // echo $sql;
         $res=sql($sql,"abiturEr01");
         while ($row=sqlget($res)) {
               $all['num']=$n;
               $all['name']="<a href=\"[PATH]reg.php4?[SESSID]showMID=".$row['mid']."\">".$row['lname']." ".$row['fname']."</a>";
               $all['ctitle'] =$row['title'];
               $all['move']   ="<input type=\"checkbox\" name=\"abtost['".$n."']\" value=\"tost\">";
               $all['remove'] ="<input type=\"checkbox\" name=\"abdelete['".$n."']\" value=\"delete\">";
               $all['hidden'] ="<input type=\"hidden\" name=\"pm['".$n."']\" value=\"".$row['mid']."\">";
               $all['hidden'].="<input type=\"hidden\" name=\"pc['".$n."']\" value=\"".$row['cid']."\">";
               $sel.=words_parse($alltr,$all,"AB-");
               $n++;
            }

      return $sel;
   }


function studListOther($CID) {
      global $alltr,$s;
      $alltr=loadtmpl("abitur-ottr.html");
      $sel="";
      $n=1;
      $all=array();

         $sql="SELECT Students.CID as cid,  People.MID as mid, People.FirstName as fname,
                      People.LastName as lname, Courses.Title as title, cgname.name as cgr
                  FROM Students, People, Courses LEFT JOIN cgname ON cgname.cgid=Students.cgid
                  WHERE Courses.CID=Students.CID AND Students.MID=People.MID";

         $sql.=" AND Students.CID<>'".$CID."'";
//            else $sql.=" AND Students.CID IN (".implode(",",$s[tkurs]).")";
//         $sql.=" ORDER BY Courses.CID, Students.SID ASC";
         $sql.=getSort("Students.SID");

         $res=sql($sql,"abiturEr02");
         while ($row=sqlget($res)) {
               $all['num']=$n;
               $all['name']="<a href=\"[PATH]reg.php4?[SESSID]showMID=".$row['mid']."\">".$row['lname']." ".$row['fname']."</a>";
               $all['ctitle'] =$row['title'];
               $all['cgr'] =$row['cgr'];
               $all['move']   ="<input type=\"checkbox\" name=\"tost['".$n."']\" value=\"tost\">";
               $all['remove'] ="";
               $all['hidden'] ="<input type=\"hidden\" name=\"pm['".$n."']\" value=\"".$row['mid']."\">";
               $all['hidden'].="<input type=\"hidden\" name=\"pc['".$n."']\" value=\"".$CID."\">";
               $sel.=words_parse($alltr,$all,"AB-");
               $n++;
            }

         $sql="SELECT  claimants.CID as cid, People.MID as mid, People.FirstName as fname, People.LastName as lname, Courses.Title as title FROM claimants, People, Courses WHERE Courses.CID=claimants.CID AND claimants.MID=People.MID AND claimants.Teacher='0'";
         $sql.=" AND claimants.CID<>'".$CID."'";
//            else $sql.=" AND claimants.CID IN (".implode(",",$s[tkurs]).")";*/
         //$sql.=" ORDER BY Courses.CID, claimants.SID ASC";
         $sql.=getSort("claimants.SID");
        // echo $sql;
         $res=sql($sql,"abiturEr01");
         while ($row=sqlget($res)) {
               $all['num']=$n;
               $all['name']="<a href=\"[PATH]reg.php4?[SESSID]showMID=".$row['mid']."\">".$row['lname']." ".$row['fname']."</a>";
               $all['ctitle'] =$row['title'];
               $all['move']   ="<input type=\"checkbox\" name=\"tost['".$n."']\" value=\"tost\">";
               $all['remove'] ="";
               $all['hidden'] ="<input type=\"hidden\" name=\"pm['".$n."']\" value=\"".$row['mid']."\">";
               $all['hidden'].="<input type=\"hidden\" name=\"pc['".$n."']\" value=\"".$CID."\">";
               $sel.=words_parse($alltr,$all,"AB-");
               $n++;
            }


      return $sel;
   }

//   function studSearchList($cid ) {
     // поиск по условиям


 //  }


function studList($CID) {
   // выводит спислк сутдентов
      global $alltr,$s;
      $alltr=loadtmpl("abitur-sttr.html");
      $sel="";
      $n=1;
      $all=array();

         $sql="SELECT Students.CID as cid,  People.MID as mid, People.FirstName as fname, People.LastName as lname, Courses.Title as title, cgname.name as cgr FROM Students, People, Courses LEFT JOIN cgname ON cgname.cgid=Students.cgid WHERE Courses.CID=Students.CID AND Students.MID=People.MID";
         if ($CID)
           $sql.=" AND Students.CID='".$CID."'";
         else
           $sql.=" AND Students.CID IN (".implode(",",$s[tkurs]).")";
       if ($cgid) $sql.=" AND Students.cgid='".$cgid."'";

//         $sql.=" ORDER BY Courses.CID, Students.SID ASC";
         $sql.=getSort("Students.SID");

         $res=sql($sql,"abiturEr02");
         $ii=1;
         while ($row=sqlget($res)) {
               $all['num']=$n;
                $ii=intval($row['mid']);
               $img=sqlval("SELECT * FROM filefoto WHERE mid=$ii ","errRE428");

               {

                  $all['name']=$mid."<a href=\"[PATH]reg.php4?[SESSID]showMID=".$row['mid']."\">".$row['lname']." ".$row['fname']."</a>";
                }

               $all['ctitle'] =$row['title'];
               $all['cgr'] =$row['cgr'];
               $all['move']   ="<input type=\"checkbox\" name=\"sttoab['".$n."']\" value=\"toab\">";
               $all['remove'] ="<input type=\"checkbox\" name=\"sttogr['".$n."']\" value=\"togr\">";
               $all['hidden'] ="<input type=\"hidden\" name=\"pm['".$n."']\" value=\"".$row['mid']."\">";
               $all['hidden'].="<input type=\"hidden\" name=\"pc['".$n."']\" value=\"".$row['cid']."\">";
               $sel.=words_parse($alltr,$all,"AB-");
               $n++;
            }

      return $sel;
   }

function gradList($CID) {
      global $alltr,$s;
      $sel="";
      $n=1;
      $all=array();

         $sql="SELECT graduated.CID as cid, People.MID as mid, People.FirstName as fname, People.LastName as lname, Courses.Title as title
                FROM graduated, People, Courses
                WHERE Courses.CID=graduated.CID AND graduated.MID=People.MID";

         if ($CID) $sql.=" AND graduated.CID='".$CID."'";
         else $sql.=" AND graduated.CID IN (".implode(",",$s[tkurs]).")";
//         $sql.=" ORDER BY Courses.CID, graduated.SID ASC";
         $sql.=getSort("graduated.SID");
         $res=sql($sql,"abiturEr03");
         while ($row=sqlget($res)) {
               $all['num']=$n;
               $all['name']=$mid."<a href=\"[PATH]reg.php4?[SESSID]showMID=".$row['mid']."\">".$row['lname']." ".$row['fname']."</a>";
               $all['ctitle'] =$row['title'];
               $all['move']   ="<input type=\"checkbox\" name=\"grtost['".$n."']\" value=\"tost\">";
               $all['remove'] ="<input type=\"checkbox\" name=\"grdelete['".$n."']\" value=\"delete\">";
               $all['hidden'] ="<input type=\"hidden\" name=\"pm['".$n."']\" value=\"".$row['mid']."\">";
               $all['hidden'].="<input type=\"hidden\" name=\"pc['".$n."']\" value=\"".$row['cid']."\">";
               $sel.=words_parse($alltr,$all,"AB-");
               $n++;
         }

      return $sel;
   }

function moveStud($pm,$pc) {
//   pr($_POST);

      $sablist=(isset($_POST['sttoab'])) ? $_POST['sttoab'] : array();
      $sgrlist=(isset($_POST['sttogr'])) ? $_POST['sttogr'] : array();

      foreach ($sablist as $k=>$val) {
         if (isset($pm[$k]) && isset($pc[$k])) toab($pm[$k],$pc[$k]);
      }
      foreach ($sgrlist as $k=>$val) {
         if (isset($pm[$k]) && isset($pc[$k])) togr($pm[$k],$pc[$k]);
      }
   }

function moveAbit($pm,$pc) {
      $sstlist=(isset($_POST['abtost'])) ? $_POST['abtost'] : array();
      $sdelist=(isset($_POST['abdelete'])) ? $_POST['abdelete'] : array();

      foreach ($sstlist as $k=>$val) {
         if (isset($pm[$k]) && isset($pc[$k])) tost($pm[$k],$pc[$k]);
      }
      foreach ($sdelist as $k=>$val) {
         if (isset($pm[$k]) && isset($pc[$k])) del($pm[$k],$pc[$k]);
      }
   }

function moveGrad($pm,$pc) {
      $sstlist=(isset($_POST['grtost'])) ? $_POST['grtost'] : array();
      $sdelist=(isset($_POST['grdelete'])) ? $_POST['grdelete'] : array();
      foreach ($sstlist as $k=>$val) {
         if (isset($pm[$k]) && isset($pc[$k])) tost($pm[$k],$pc[$k]);
         }
      foreach ($sdelist as $k=>$val) {
         if (isset($pm[$k]) && isset($pc[$k])) del($pm[$k],$pc[$k]);
         }

   }

function moveToStud( $pm, $pc ) {
      $sstlist=(isset($_POST['tost'])) ? $_POST['tost'] : array();
      foreach ($sstlist as $k=>$val) {
         if (isset($pm[$k]) && isset($pc[$k])) tost($pm[$k],$pc[$k]);
         }
   }


?>