<?php

require_once("1.php");
require_once("schedule.lib.php");
require_once("tracks.lib.php");
require_once("test.inc.php");

define("TYPE_GROUP", 1);

if (!$stud) login_error();
if (empty($courses)) login_error();

// SAJAX BEGIN
if ($s['perm']>=2) {
    require_once($GLOBALS['wwf'].'/lib/sajax/SajaxWrapper.php');
    $js =
    "
    function show_group_select(html) {
        var elm = document.getElementById('group');
        if (elm) elm.innerHTML = html;
    }

    function get_group_select(cid) {
        var elm = document.getElementById('group');
        if (elm) elm.innerHTML = '<select><option>"._("Загружаю данные...")."</option></select>';

        x_get_group_select(cid, 0, show_group_select);
    }
    ";
    $sajax_javascript = CSajaxWrapper::init(array('get_group_select')).$js;
}
// SAJAX END

//if (isset($_GET['CID'])) $s[user][vsortg] = -1;
if (isset($_POST['FILTER'])) $FILTER = $_POST['FILTER'];


$s[user][scourse]=(isset($s[user][scourse])) ? $s[user][scourse] : reset($courses);


$s[user][vsorts]=(isset($s[user][vsorts])) ? $s[user][vsorts] : 0;
$s[user][vsortf]=(isset($s[user][vsortf])) ? $s[user][vsortf] : 0;
$s[user][vsortg]=(isset($s[user][vsortg])) ? $s[user][vsortg] : -1;

$s[user][scourse]=(isset($_GET['CID'])) ? intval($_GET['CID']) : $s[user][scourse];
$s[user][vsorts]=(isset($_GET['sort'])) ? intval($_GET['sort']) : $s[user][vsorts];
$s[user][vsortf]=(isset($_GET['sortf'])) ? intval($_GET['sortf']) : $s[user][vsortf];
$s[user][vsortg]=(isset($_GET['gr'])) ? $_GET['gr'] : $s[user][vsortg];

if(!isset($CID)) $CID = 0;
if(!isset($gr)) $gr = "-1";
//else $gr = $_GET['gr'];
//if($CID==0)
//  $s[user][vsortg]=-1;

//echo "REf: ".$_SERVER['HTTP_REFERER']."<br>";
//echo "self: ".$_SERVER['PHP_SELF']."<br>";


//if(strpos($_SERVER['HTTP_REFERER'],$_SERVER['PHP_SELF'])===true) {
//  $CID=$s[user][scourse];
//  echo "from this script";

//}
//else {
//  $CID = 0;
// echo "from other script";
//}

if ( isset($ERASE_FOR_MID) ) {
  reset_mid_shedule( $ERASE_FOR_MID, $CID );
}


################################################################################
//
function isWeek( ){
}
################################################################################

################################################################################
//fn Return scheduled table if teacher is logined. $CID identificator of course,
//fn $filter - filter with value 0 or 1

function teachVed( $CID , $filter="") {

  if (!$CID) return '';

  global $gr;
  global $s;
  $r = array();
  $GLOBALS['formulasGrMarks'] = parseFormulasGrMarks($CID);
  if ($CID){

      // Выставление результирующей оценки за курс
      if ($_POST['action'] == 'ved_post') {
         if (is_array($_POST['coursemark']) && count($_POST['coursemark']))
            foreach($_POST['coursemark'] as $k=>$v) {
                if ($_POST['course_mark_formula'] > 0) {
                    $v = getCourseMarkByFormula($k,$CID,$_POST['course_mark_formula']);
                }
                saveCourseMark($CID,$k,$v, $GLOBALS['markGr']);
            }
         refresh("{$sitepath}ved.php4?CID={$CID}&gr={$gr}&$owner_soid=".(int) $GLOBALS['owner_soid']);
         exit();
      }

      // $GLOBALS['controller']->setLink('m150103', array($CID)); // Ссылка перенесена в "адаптивный интерфейс"
      if ($GLOBALS['controller']->checkPermission(SHEDULE_PERM_EDIT_OWN)
          || $GLOBALS['controller']->checkPermission(SHEDULE_PERM_EDIT_OTHERS)) {
          $GLOBALS['controller']->setLink('m150102', array($CID));
          $GLOBALS['controller']->setLink('m150104', array($CID));
      }
      $GLOBALS['controller']->setLink('m150105', array($CID));
  }
  //fn Выбираем все расписания для которых есть ведомость по данному курсу и типы событий (экзамнены, зачеты, ...)
  //fn CID - идентификатор курса
  /*$sqlq1= "SELECT `SHEID`, `Title` , `Icon`, `begin`,`end`, timetype, startday, stopday
          FROM `schedule`,`EventTools`
          WHERE `vedomost` = 1
          AND `CID` = ".$CID."
          AND EventTools.TypeID=schedule.typeID ";*/

  $sqlql_if = (dbdriver=='mysql') ? "IF (schedule.startday IS NULL,0,schedule.startday) AS sday" : "schedule.startday AS sday";
  $sqlq1 = "SELECT DISTINCT scheduleID.SHEID,
                            schedule.Title,
                            schedule.createID,
                            EventTools.Icon,
                            schedule.begin,
                            schedule.end,
                            schedule.timetype,
                            schedule.startday,
                            schedule.stopday,
                            EventTools.TypeID,
                            {$sqlql_if}
            FROM scheduleID INNER JOIN schedule ON scheduleID.SHEID = schedule.SHEID INNER JOIN EventTools ON EventTools.TypeID = schedule.typeID
            WHERE schedule.vedomost = 1 AND
            schedule.CID = '".$CID."'
            ORDER BY sday, schedule.begin
            ";




  //fn Сортируем или по типам или по хронологии SHEID - auto_increment
//  if ($s[user][vsorts]) $sqlq1.=" ORDER BY EventTools.TypeID, scheduleID.SHEID ASC";
//  else $sqlq1.=" ORDER BY scheduleID.SHEID ASC";


  //fn Осуществляем запрос
  $res  = sql($sqlq1,"VQTer1");

  //fn Количетсво строк в результате
  $rcol = sqlrows($res);

  //fn Читаем результат запроса
  while ($row = sqlget($res)) {
    // Oracle fix ???
/*    $sql_tools = "SELECT toolParams FROM scheduleID WHERE SHEID='".$row['SHEID']."' LIMIT 1";
    $res_tools = sql($sql_tools);
    if (sqlrows($res_tools)) {
        $row_tools = sqlget($res_tools);
        $scheduleFormulas[$row['SHEID']] = getIntVal($row_tools['toolParams'],"formula_id=");
    }
*/
    $r[]= $row['SHEID'];//fn Массив идентификаторов расписаний
    $createIDs[$row['SHEID']] = $row['createID'];
    if( $row['timetype'] ) {
      $rt[]= $row['Title'];
      //$rtype[]="<FONT class=small>?</FONT>";
    }
    else {
      $rt[]= $row['Title'].": ".mydate($row['begin'])." - ".mydate($row['end']);
      $rtype[]="";
    }
    $ri[]= $row['Icon'];//fn Массив иконок для для расписаний
  }

  if (is_array($r) && count($r)) {
      $_sql = "SELECT toolParams as toolParams, SHEID
              FROM scheduleID
              WHERE SHEID IN ('".join("','", $r)."')";
      if (strtolower(dbdriver) == 'mysql') {
          $_sql .= " GROUP BY SHEID";
      }
      $_res = sql($_sql);

      while($_row = sqlget($_res)) {
          $scheduleFormulas[$_row['SHEID']] = getIntVal($_row['toolParams'],"formula_id=");
      }
  }

  $stype=($s[user][vsortf]) ? "DESC" : "ASC";//fn Выбираем сортировку для ФИО людей

  //fn Определяем выбрана ли группа или все

  $people_filter = false;
  $owner_soid = $GLOBALS['owner_soid'];
  if ($owner_soid) {
     require_once($GLOBALS['wwf'].'/lib/classes/Position.class.php');
     if ($people = CUnitPosition::getSlavesPeopleId(array($owner_soid))) {
     	foreach($people as $mid) {
     		$people_filter[$mid] = true;
     	}
     }
  }

  if (!$s[user][vsortg]) {
    $sqlq2="SELECT People.MID as MID, People.Patronymic, People.FirstName as fn, People.LastName as ln, Students.Registered as Registered, Students.time_registered
            FROM People, Students
            WHERE Students.CID='".$CID."'
            AND Students.MID=People.MID
            ORDER BY People.LastName, People.FirstName ".$stype;
  }
  else {

    //fn Определяем номер группы
    $gnum=substr($s[user][vsortg], 1, strlen($s[user][vsortg]));

    //fn Определяем пересекающиеся ли группы g - перескающиеся d - непересекающиеся
    if ($s[user][vsortg][0]=="g") {
    $sqlq2="SELECT People.MID as MID, People.Patronymic, People.FirstName as fn, People.LastName as ln, Students.Registered as Registered, Students.time_registered
            FROM People, Students, groupuser
            WHERE Students.CID='".$CID."'
            AND Students.MID=People.MID
            AND groupuser.mid=People.MID
            AND groupuser.gid='".$gnum."'
            ORDER BY People.LastName, People.FirstName ".$stype;
    }
    else {
      $sqlq2="SELECT People.MID as MID, People.Patronymic, People.FirstName as fn, People.LastName as ln, Students.Registered as Registered, Students.time_registered
              FROM People, Students, cgname
              WHERE Students.CID='".$CID."'
              AND Students.MID=People.MID
              AND  Students.cgid=cgname.cgid
              AND cgname.cgid='".$gnum."'
              ORDER BY People.LastName, People.FirstName ".$stype;
    }
  }

  //fn Осуществляем выборку студентов из данной группы или всех по данному курсу с указанной сортировкой
  $res  = sql($sqlq2,"VQTer2");

   if( count($filter) > 0 ) {
    foreach( $filter as $gid ) {
      $sql_f="SELECT People.MID as mid
              FROM People, Students, groupuser
              WHERE Students.CID='".$CID."'
              AND Students.MID=People.MID
              AND groupuser.mid=People.MID
              AND groupuser.gid=$gid";

      $res_g=sql( $sql_f, "Err-filter gr select");

      while( $rg=sqlget( $res_g )) {
        if ($people_filter && !isset($people_filter[$rg['mid']])) continue; // filter
      	if( isset( $mids[$rg['mid']] ) )
          $mids[$rg['mid']]++;
        else {
          $mids[$rg['mid']]=1;
        }
      }

      sqlfree( $res_g );

    }

    $kf=count( $filter ); // кол-во выбранных групп
    // собраны все группы и студни со всех групп. надо найти пересечение
  }


  //fn Считываем студентов из данной группы по данному курсу с указанной сортировкой
  while ($row = sqlget($res)) {
    if ($people_filter && !isset($people_filter[$row['MID']])) continue; // filter
  	$rmid[]= $row['MID'];//fn Массив идентификаторов студентов
    if($row['time_registered'])
      $date=date( "d.m.y", registered2time($row['time_registered']));
    else
      $date=_("[нет информации о дате регистрации на курсе]");

    $rname[]= "<span title='"._("Зарегистрирован с")." $date'>".$row['ln']." ".$row['fn']." " . $row['Patronymic'] . " " .$mids[$row['MID']]."</span>";
    //fn Забиваем массив имен студентов
    if(  (count( $filter ) < 0) || ($mids[$row['MID']] == $kf) )
      $rshow[]=1;
    else
      $rshow[]=0;
  }


  //fn Выбираем идентификатор и имя группы, для которых есть ведомость
  $q = "SELECT DISTINCT groupname.gid as gid, groupname.name as gname
        FROM scheduleID INNER JOIN schedule ON (scheduleID.SHEID = schedule.SHEID)
             INNER JOIN groupname ON (scheduleID.`gid` = groupname.gid)
        WHERE (schedule.isgroup = '1')";

  $res = sql($q);
  $intLastKey = count($rmid);//fn Номер последнего индекса в массиве идентификаторов студентов
  while ($row = sqlget($res)) {
    $rmid[] = $row['gid'];//fn Дополняем массив идентификаторов студентов
    $rname[] = "<span>".$row['gname']."</span>";//fn Доподняем массив имен студентов
    $rshow[] = 1;
    $rtype[$intLastKey++] = TYPE_GROUP;
  }

  //fn Проверяем не пустой ли у нас массив идентификаторов студентов
  if ( !empty($rmid) ) {
    //fn Выбираем всех этих студентов, чтобы знать из имена
    $sql="SELECT MID, last, countlogin
          FROM People
          WHERE  `People`.`MID` IN (".implode($rmid,", ").")
          ORDER BY `last`";
  //fn Совершаем выборку
  if ($res=@sql($sql,"VQTerLast"))
    while ($row = sqlget($res)) {
      //fn Определяем время последнего логина
      $temp1=time()-$row['last'];
      $temp=floor(doubleval($temp1)/(60*60*24));
      if ($row['last']>0)  $rlast[$row['MID']]=$temp;
      else $rlast[$row['MID']]="?";
        $rcountl[$row['MID']]=$row['countlogin'];
    }

    // формируем имена групп для отображения
    $sql="SELECT groupuser.mid as mid, groupname.name as gname
          FROM groupuser, groupname
          WHERE groupuser.mid IN (".implode($rmid,", ").")
          AND groupuser.gid=groupname.gid
          AND groupname.cid IN ('".$CID."', -1 )";
    if ($res=@sql($sql,"VQTerLast"))
      while ($row = sqlget($res)) {
        $rgroup[$row['mid']].=" ".$row['gname']."<BR>";
      }

    $sql="SELECT Students.MID as mid, cgname.name as gname
          FROM Students, cgname
          WHERE Students.MID IN (".implode($rmid,", ").")
          AND Students.cgid=cgname.cgid
          AND Students.CID='".$CID."'";

    if ($res=@sql($sql,"VQTerLast"))
      while ($row = sqlget($res)) {
        $dgroup[$row['mid']].=" ".$row['gname'];
      }


    if ( count($r)>0 ){//если есть хоть одно занятие
      $sqlq2="SELECT DISTINCT `scheduleID`.`MID` as MID ,
                 `scheduleID`.`V_STATUS` as mark,
                 `scheduleID`.`SHEID` as SHEID
              FROM `scheduleID`
                 INNER JOIN `schedule` ON (`scheduleID`.`SHEID` = `schedule`.`SHEID`)
                 INNER JOIN `People` ON (`scheduleID`.`MID` = `People`.`MID`)
              WHERE
                 `scheduleID`.`SHEID` IN (".implode($r,", ").") AND
                 `scheduleID`.MID IS NOT NULL";

      $res  = sql($sqlq2,"VQTer3");
      while ($row = sqlget($res)) {
        $rmark[$row['MID']][$row['SHEID']] =  $row['mark'];
      }

      $sqlq2="SELECT DISTINCT
                `scheduleID`.`gid` as gid ,
                `scheduleID`.`V_STATUS` as mark,
                `scheduleID`.`SHEID` as SHEID
              FROM `scheduleID`
                INNER JOIN `schedule` ON (`scheduleID`.`SHEID` = `schedule`.`SHEID`)
                INNER JOIN `groupname` ON (`scheduleID`.`gid` = `groupname`.`gid`)
              WHERE
                `scheduleID`.`SHEID` IN (".implode($r,", ").") AND
                `schedule`.isgroup = '1'";

      $res  = sql($sqlq2,"VQTer3");

      while ($row = sqlget($res)) {
        $rmarkGroups[$row['gid']][$row['SHEID']] =  $row['mark'];
      }

    }
  } // КОНЕЦ ЕСЛИ В ВЕДОМОСИ НЕТ НИ ОДНОГО СТУДЕНТА

  $rrow=count($rname);

  //fn $str.="<center>fed-nikitin</td>
  //fn       </center>";
  //echo $gr;

  if(($gr!=="0")&&($gr!=="-1")) {
    //echo "fed-niktin";
    //echo $fn_s;
    $fn_s = substr($gr,0,1);
   // echo $fn_s;

    switch ($fn_s) {
     case "d":

       $name_group=_("1-го типа");
     break;
     case "g":
       $name_group=_("2-го типа");
     break;
    }
//   $str.="<center><b>Отображаются лишь студенты ".$name_group."</b></center><br>";
  }


  if ($CID){
  $str.="<form action='' method='POST'>
         <input type=\"hidden\" name=\"action\" value=\"ved_post\">
         <input type=\"hidden\" name=\"CID\" value=\"{$CID}\">
         <table width=100% class=main cellspacing=0>";
  $vsortf=($s[user][vsortf]) ? "0" : "1";
  $vsortfalt=($s[user][vsortf]) ? _("По алфавиту") : _("В обратном порядке");




  $str.="<tr>
            <th nowrap width='100%'>
              <a href='[PATH]ved.php4?[SESSID]sortf=".$vsortf."&CID=".$CID."&gr=".$s[user][vsortg]."' title='".$vsortfalt."' >"._("ФИО")."</a>
               ";
//fn             <select name='ChGr' id='select' onchange=\"if (this.value=='-1')this.value=0; navigate('ved.php4?gr='+ChGr.value);\" class=\"selcselect2\">
//fn              [GRSEL]
//fn             </select>
//  $str.="       <input name=\"ch_more_info\" id=\"ch_more_info\" type=\"checkbox\" value=\"[ch_more_info_value]\" [ch_more_info_status] onClick=\"navigate('ved.php4?CID=".$CID."&gr=".$s[user][vsortg]."&ch_more_info='+document.getElementById('ch_more_info').value);\">&nbsp;доп. информация
$str .=  "</th>\n";

################################################################################
//
//  Определяем то значение индексы j (номер столбца)
//  которое выкинем из результата
//  $r - массив идентификаторов shedule's
//  $rmid - массив идентификаторов студентов
//
//   Пробегаем $r по j
//     Если в таблице scheduleID нет строки с mid из $rmid
//       то индекс битый, его игнорируем при рисовании, запоминем в массиве break_index .
//
################################################################################

  //fn echo "До цикла: ".count($break_index)."<br>";

  if(@$rmid) {
    foreach($r as $key=>$value) {
      if($gr==="0") break;
      $query = "SELECT * FROM scheduleID WHERE SHEID=$value AND MID IN (".implode($rmid,", ").")";
      $result = sql($query,"error query");
      if(sqlrows($result)==0) {
        $break_index[] = $key;
      }
    }
  }
  else {
    foreach($r as $key=>$value) {
     if($gr==="0") break;
     $break_index[] = $key;
    }
  }

  //fn echo "После цикла: ".count($break_index);


    //fn echo count($break_index);
    //fn echo "s[user][vsortg]: ".$s[user][vsortg];



  for ($j=0;$j<$rcol;$j++) {
      if(count($break_index)>0)
        if(in_array($j,@$break_index)) continue;

    $str.="<th align='center'>";

    //fn $ri[$j] Имя иконки соответс;

    if(is_quiz_by_sheid($r[$j])) {
       $temp = "show=stat&";
    }
    else {
       $temp = "";
    }

    $str.="  <a href='#' onclick='window.open(\"".$sitepath."redved.php4?".$temp."SHEID=".$r[$j]."&owner_soid={$owner_soid}\", \"_\", \"toolbar=0, status=0, menubar=0, scrollbars=1, resizable=1, width=600\");return false' title='".$rt[$j]."'>";
    $str.="    <img src='".$sitepath."images/events/".$ri[$j]."' widht=24 height=24 border=\"0\" hspace='10'>
             </a>";
    $str.="</th>\n";
  }

  $str.="<th  width='30px'>"._("Взвешенная оценка")."</th>
         <th width='30px' title=\""._("итоговая оценка")."\">"._("Итоговая оценка")."</th>
         <th  width='100px'>"._("Выполнено")."<br>("._("на&nbsp;оценку:всего").")</th>\n";
  $str.="</tr>\n";

    ////// расчет средней оценки
  $maxb=0;
  for ($i=0;$i<$rrow;$i++) {
    $finalmark=0;
    $allmark=0;
    $completes=0;

    for ($j=0;$j<$rcol;$j++) {
      if (isset($rmark[$rmid[$i]] [$r[$j]])) {
        $mark=$rmark[$rmid[$i]][$r[$j]];
      }
      elseif (isset($rmarkGroups[$rmid[$i]] [$r[$j]])) {
        $mark=$rmarkGroups[$rmid[$i]][$r[$j]];
      }
      else {
        $mark="";
      }

      if ($mark>0) {
        $finalmark+=$mark;
        $completes++;
      }

      $allmark++;
      if ($mark<0) {
        $mark = " - ";
      }
    }

    if ($completes>0)
      $mmark[$i]=sprintf("%1.1f",$finalmark/$completes); // вычисление средней оценки
    else
      $mmark[$i]="-";
         //тут надо вычислить по формуле в какую доп группу его запихать

    if ( $mmark[$i]> $maxb ) $maxb=$mmark[$i];

  }
  /// конец расчета средней оценки
  $sql_formula=sql("SELECT * FROM formula WHERE (CID='".$CID."' OR CID='0')","errFM50"); // фОРМИРУЕМ ВСЕ ФОРМУЛЫ ДЛЯ ЭТОГО КУРСА

  $kl=0;

  while ($rf=sqlget($sql_formula)) { //ВЫВОДИМ КАЖДУЮ ФОРМУЛУ В СПИСОК
    if ( $rf[type]== 3){ // т.е. для средней оценки
      $f[$kl]=$rf[formula];
      $kl++;
    }
  }
  // начало вывода мени группы
  for ($i = 0; $i < $rrow; $i++) {
     if ( !$rshow[$i] ) continue;
     if ( !$rtype[$i] ) {
        $str.="<tr>\n<td>".($i+1).".\n<a href='".$sitepath."userinfo.php?mid=".$rmid[$i]."' target='_blank'>";
        $str.=$rname[$i];
        $str.="</a>";
        if ($GLOBALS['controller']->enabled) $_GET['ch_more_info'] = true;
        if (isset($_GET['ch_more_info']) && $_GET['ch_more_info']) {
           $str.="<br>".getPhoto( $rmid[$i],0,60,80,1 )."<BR>";
           $stat="<BR><span align=right class='small'><span title='"._("кол-во входов")."'>"._("кол-во входов").":&nbsp;{$rcountl[$rmid[$i]]} </span></span><br>";
//                 (<span title='последний раз посетил курс {$rlast[$rmid[$i]]} дн. тому назад'>{$rlast[$rmid[$i]]}</span>)
        }
     }
     else {
        $str.="<tr>\n<td>".($i+1).".\n "._("Группа").":&nbsp;<b>".$rname[$i]."</b>";
        $stat = "";
     }

     if (isset($rgroup[$rmid[$i]]) && isset($_GET['ch_more_info']) && $_GET['ch_more_info'])
        $str.="<span class='small'>".$rgroup[$rmid[$i]]."</span><br>";

     if (isset($dgroup[$rmid[$i]]) && isset($_GET['ch_more_info']) && $_GET['ch_more_info'])
        $str.="<span class='small'><span class='cHilight'><b>".$dgroup[$rmid[$i]]."</span></span></b>";

     $text="";

     $str.="</td>\n";
     $finalmark=0;
     $allmark=0;
     $completes=0;
     $f=TRUE;
     for ($j=0;$j<$rcol;$j++) {
      ###############################################
      //  Здесь делаем проверку на пустоту колонки
      //  Выбираем из базы по заданию и смотрим
      //  если пусто то continue
      ################################################
        if(count($break_index)>0)
           if(in_array($j,@$break_index)) continue;

        if( $f ) $str.="<td align='center' class='tdr'>";
        else $str.="<td align='center'>";

        $f=!$f;
        $arrValidArray = ($rtype[$i]) ? $rmarkGroups : $rmark;
        $mark=$arrValidArray[$rmid[$i]][$r[$j]];

        if (isset($mark)) {
           if ($mark>0) {
              $finalmark+=$mark;
              $completes++;
           }
           $allmark++;
           if ($mark<0) {
              if ($mark == -2) $completes++;
              $intMark = (int)$mark;
              $qq = "SELECT * FROM alt_mark WHERE `int`='{$intMark}'";
             $rr = sql($qq);
          if ($a = sqlget($rr)) {
            $mark = " {$a['char']} ";
          }
          else {
            $mark = " . ";
          }
        }
      }
      else $mark="";

      if ($mark != '.') {
        $grMark = $GLOBALS['formulasGrMarks'][$scheduleFormulas[$r[$j]]][$mark];
      }
      $str.="<span title=\"{$grMark}\">".$mark."</span>";
      $str.="</td>\n";
      // присвоить группу человеку если она есть для сред оценки
    }

    //applyGroups( $rmid[ $i ], $CID );

    $proccompl=($allmark) ? floor(doubleval(($completes/$allmark)*100)) : "нет занятий";

    //$str.="<td width='30px' nowrap class='tdy' align='center'><b>".$mmark[$i]."</b></td>";
    $str.="<td width='30px' nowrap class='tdy' align=center>".getCourseRating($CID,$rmid[$i])."</td>";
    list($mark, $alias) = getCourseMark($CID,$rmid[$i]);
    $str.="<td width='30px' nowrap class='tdy' align=center title=\"\"><input size=1 type=\"text\" name=\"coursemark[{$rmid[$i]}]\" value=\"{$mark}\" title='{$alias}'></td>";

    $str.="<td width='100px' nowrap >".$completes." (".$allmark.":".shedule_count($rmid[$i], $CID ).") ";

    $data['progress']=$proccompl;
    $data['total']=$finalmark; //
    $data['level']=$mmark[$i];

    $applyFinishCourseFormula = applyFinishCourseFormula( $CID, $data );
    $str.="<b>".$applyFinishCourseFormula."</b>";//."prog=".$data['progress']." total=".$data['total']." lev=".$data['level'];
    if (!empty($applyFinishCourseFormula)) $mids2delFromCourse[] = $rmid[$i];
    $str.=makeProgress( $proccompl );
    $str.=$stat."</td></tr>\n";
  }

  if (empty($rrow)){
	$str .= "<tr><td align='center' colspan='99'>" . _('нет данных для отображения') . "</td></tr>";
  }

  if($CID!=0 && !$GLOBALS['controller']->enabled)
    $str.="<tr><td align='center'><a href=\"[PATH]groups.php?[SESSID]CID=[CURCID]\">"._("группы")."</a> ([GNUM]) ";
  else
    $str.="<tr><td>";

  ///////////// конец добавления формул
  $str.="</td>";
  for ($j=0;$j<$rcol;$j++) {

    if(count($break_index)>0)
      if(in_array($j,$break_index)) continue;

    $str.="<td align='center'>";
    if ((($createIDs[$r[$j]]==$GLOBALS['s']['mid']) && $GLOBALS['controller']->checkPermission(SHEDULE_PERM_EDIT_OWN))
        || $GLOBALS['controller']->checkPermission(SHEDULE_PERM_EDIT_OTHERS))
        $str.="<a href='[PATH]schedule.php4?[SESSID]c=modify&sheid=".$r[$j]."' title='"._("Редактировать занятие")."' class='wing' onclick=\"wopen('','schedit')\" target=\"schedit\">
            ".getIcon("edit",_("Редактировать занятие"))."</a><br>
            <a href='[PATH]schedule.php4?[SESSID]c=delete&sheid=".$r[$j]."&rp=ved&CID={$CID}&gr={$gr}'onClick='return confirm(\""._("Удалить это занятие?")."\")' title='"._("Удалить занятие")."' class='wing'>
            ".getIcon("delete",_("Удалить занятие"))."</a>";
    $str.="</td>";
  }

  if($CID!=0 && !$GLOBALS['controller']->enabled) {
    $str.="<td colspan=2 align='center'>
             <a href=\"[PATH]schedule.php4?[SESSID]c=add&addtoved=1&CID=[CURCID]\"
             onclick=\"wopen('','scheadd')\" target=\"scheadd\"><img border=0 width=26 src='{$sitepath}images/icons/add_shedule.gif' alt='"._("добавить занятие")."'></a> &nbsp; ";
    if (defined("LOCAL_ALLOW_SCHEDULE_GEN") && LOCAL_ALLOW_SCHEDULE_GEN) $str .=
             "<a href=\"[PATH]schedule.php4?[SESSID]c=gen_schedule&CID=[CURCID]\"
             onclick=\"wopen('','scheadd')\" target=\"scheadd\"><img border=0 src='{$sitepath}images/icons/gen_shedule.gif' alt='"._("сгенерировать занятия по данному курсу")."'></a> &nbsp; ";
    $str .=
             "<a onClick='if (!confirm(\""._("Вы действительно желаете удалить все занятия на данном курсе?")."\")) return false;' href=\"[PATH]schedule.php4?[SESSID]c=del_all_schedule&CID=[CURCID]\"
             ><img border=0 src='{$sitepath}images/icons/delete_all_shedules.gif' alt='"._("удалить все занятия на данном курсе")."'></a> &nbsp; ";
    if (isset($mids2delFromCourse) && is_array($mids2delFromCourse) && count($mids2delFromCourse)) {

        $mids2delFromCourseBase64 = base64_encode(serialize($mids2delFromCourse));
        $str .= "<a onClick='if (!confirm(\""._("Вы действительно желаете удалить с курса прошедших обучение людей")." (".count($mids2delFromCourse)." "._("чел").")?\")) return false;' href=\"{$sitepath}ved.php4?CID={$CID}&gr={$gr}&mids2del={$mids2delFromCourseBase64}\"><img border=0 src='{$sitepath}images/icons/del_from_course.gif' alt='"._("удалить прошедших обучение с курса")."'></a>";

    }
    $str .=  "</td></tr>";
  }
  else {
    $str.="<td colspan='4'></td></tr>";
  }

  $str.="</table>";
  $str.="<br>
  <table cellspacing=0 border=0 cellpadding=0 align=right>
  <tr><td>"._('Выполнить действие')."
  <select name=\"course_mark_formula\">
      <option value=\"0\">"._("сохранить итоговые оценки")." </option>";
  $course_mark_formulas = getCourseMarkFormulasArray($CID);
  if (is_array($course_mark_formulas) && count($course_mark_formulas))
      foreach($course_mark_formulas as $k=>$v) $str .= "<option value=\"$k\">"._("Выставить:")." {$v} </option>";
  $str .="</select>&nbsp;&nbsp;
  </td><td>
  ".okbutton()."</td></tr></table>";
  $str .= "</form>";
  }//if cid
  $probe = "";
  $str = str_replace("<tr><td></td><td></td></tr>",$probe,$str);
  return $str;
}
################################################################################

################################################################################
// выводиит прогресс обучения
function makeProgress( $proccompl ){

  $str.="<table width=100% class=main cellspacing=0><tr>";
  if ($proccompl && $proccompl!=_("нет занятий")) {
    $str.=" <td width='".$proccompl."%' class='tdr' align='right'>";
    if ($proccompl>50) $str.=$proccompl."%";
      $str.="</td>";
    }

  if ($proccompl<100) {
    $str.="<td align='left'>&nbsp;";
    if ($proccompl<=50) $str.=$proccompl."%";
    $str.="</td>";
  }
  $str.="</tr></table>";
  return $str;

}
#####################################################################

#####################################################################
// распределяет человека в группу в зависимости от его усевамости
function applyGroups( $mid, $cid ){

  $sql_formula=sql("SELECT * FROM formula WHERE type=3 AND (CID='".$cid."' OR CID='0')","errFM50"); // фОРМИРУЕМ ВСЕ ФОРМУЛЫ ДЛЯ ЭТОГО КУРСА
  $kl=0;

  while ($rf=sqlget($sql_formula)) { //ВЫВОДИМ КАЖДУЮ ФОРМУЛУ В СПИСОК
    $f[$kl]=$rf[formula];
    $fname[$kl]=$rf[name];
    $kl++;
  }

  sqlfree($sql_formula);
  if ( $kl ==0 ) return;



  $sqlq1="SELECT sheid
          FROM schedule
          WHERE vedomost=1
          AND CID=$cid";

  $res  = sql($sqlq1,"VQTer3");
  $r = array();
  $i=0;
  while ($row = sqlget($res)) {
    $r[$i]=$row['sheid'];
    $i++;
  }

  sqlfree($res);
  if ( count($r) == 0 ) return;

  $maxb=0;
  $mark=0;
  $finalmark=0;
  $allmark=0;
  $completes=0;

  $sqlq2="SELECT `scheduleID`.`V_STATUS` as mark,`scheduleID`.`SHEID` as SHEID
          FROM scheduleID, People
          WHERE scheduleID.MID=$mid
          AND scheduleID.SHEID IN (".implode($r,", ").")
          AND scheduleID.MID=People.MID";

  $res  = sql($sqlq2,"VQTer3");
  while ($row = sqlget($res)) { // по всем оценкам данного человека
    $mark=intval($row['mark']);
    if ($mark>0) {
      $finalmark+=$mark;
      $completes++;
    }
    else $mark=" - ";
    $allmark++;
  }

  sqlfree($res);

  if ($completes>0)
    $mark=sprintf("%1.1f",$finalmark/$completes); // вычисление средней оценки
  else $mark="-";
  //тут надо вычислить по формуле в какую доп группу его запихать
  if ( $mark > $maxb ) $maxb=$mark;

  /////// расчет средней оценки

  // начало вывода мени группы
  $text=""; $maxb=5;
  for($k=0;$k<$kl;$k++){ //ВЫВОДИМ КАЖДУЮ ФОРМУЛУ В СПИСОК
    $gr=viewFormula( $f[$k], $text, 0, $maxb, intval( $mark ) );

    if ($gr!=""){
      // получим id группы
      $sqlq2="SELECT `scheduleID`.`V_STATUS` as mark,`scheduleID`.`SHEID` as SHEID
              FROM scheduleID, People
              WHERE scheduleID.MID=$mid
              AND scheduleID.SHEID IN (".implode($r,", ").")
              AND scheduleID.MID=People.MID";

              // проверить если он по этой формуле был в какойто грппе откуда его надо удалить
              // если такой еще нет-то создадим
              // проверить если он по этой формуле был в какойто грппе откуда его надо удалить

      $sname="$fname[$k]:$gr";
      $gid = isGroup( $sname, $cid ); // есть ли такая группа на этом курсе?

      if ( $gid <= 0  ) {
        $gid=newGroup( $sname, $cid );
        addToGroup( $gid, $mid, $cid ); // добавляет
      }
      else {
        // если группа уже суествует
        // надо проверить - входит ли чел в группы по этой же формуле?
        // если да - то отовсюду исключить и только затем добавить
        excludeFromGroups( $mid, $cid, "$fname[$k]:([[:print:]]+)" );
        addToGroup( $gid, $mid, $cid ); // добавляет
      }
    }

  }

  return;

}
################################################################################

################################################################################
// проверяет наличие на курсе указанной группы
function isGroup( $name, $cid ){

  $gid=-1;   //          groupname.name as gname
  $sql="SELECT * FROM groupname WHERE `CID`='".$cid."' ORDER BY gid";
  $res=sql( $sql, "ERRgr1" );
  while ( $row = sqlget($res) ) {
    if(  strcasecmp ( $row['name'], $name ) == 0  )
      $gid=$row['gid'];
  }
  sqlfree($res);
  return $gid;
}
################################################################################

################################################################################
//
function newGroup( $gname, $cid ){

  $res=sql("INSERT INTO groupname (cid, name) values ('$cid', '$gname')","errFM185");
  sqlfree($res);

  $res=sql("SELECT groupname.gid FROM groupname WHERE cid='$cid' AND name='$gname'","errFM185");

  if ( $row = sqlget($res) ) $gid = $row[gid];
    sqlfree($res);

  return $gid;

}
################################################################################

################################################################################
// добавляет студента в группу
function  addToGroup( $gid, $mid, $cid ) {

   if( $gid > 0) {
     $res=sql("SELECT * FROM groupuser WHERE cid='$cid' AND mid='$mid' AND gid='$gid'","errADDINTO GROUP");
     if ( ! ( $row = sqlget($res) ) ) {
       sqlfree($res);
       $res=sql("INSERT INTO groupuser (cid, mid, gid) values ('$cid', '$mid', '$gid')","errADDINTO GROUP");
       sqlfree($res);
     }
   }
}
################################################################################

################################################################################
// проверяет - не входит л студент в группу созданную автоматом по данной формуле помещает в первый карман
function  excludeFromGroups($mid, $cid, $tname ) {

  $gid=-1;
  // выберем все группы с этим студентом на этом курсе
  $sql="SELECT * FROM groupuser WHERE CID=$cid AND MID=$mid";
  $res=sql( $sql, "ERRgr2"  );
  $i=0;
  while ( $row = sqlget($res) ) {
    $gids[$i]=$row['gid'];
    $i++;
  }
  sqlfree($res);
  //  теперь в перечне групп стунента найдем ту в которую он попал по формуле
  $k=$i;
  $flag=0;
  for ( $i=0; $i<$k ; $i++ ) { // для каждой группы студента на курсе проверим этойли формулой она создана? если да то вычеркиваем
    if ( isGeneratedName( $gids [$i], $tname ) ) {
      excludeFromGroup ( $mid , $gids[$i] );
    }
  }
  return $gid;
}
################################################################################

################################################################################
//
function isGeneratedName($gid, $tname ){

  $sql="SELECT * FROM groupname WHERE gid='".$gid."'";
  $res=sql( $sql, "ERRgr3"  );
  $flag = 0;
  if ( $row1 = sqlget($res) ) {
    if ( eregi( $tname, $row1['name'] ) ) {   // проверяем данное имя сгенерировано по этому шаблону?
      $flag = 1;
    }
  }
  sqlfree($res);
  return( $flag );
}
################################################################################

################################################################################
//
function excludeFromGroup( $mid, $gid ) {

  $res=sql("DELETE FROM groupuser WHERE gid='$gid' AND mid='$mid'","errFM185");
  sqlfree($res);
  // исключает студена из группы
}
################################################################################

################################################################################

function studVed( $CID, $trid=0, $level=0, $captured=false ) {
  //if (!$CID) return '';
  $GLOBALS['formulasGrMarks'] = parseFormulasGrMarks($CID);
  if($trid > 0) {

      $sql = "SELECT DISTINCT Courses.CID
              FROM Students
              INNER JOIN Courses ON (Courses.CID=Students.CID)
              WHERE Students.MID='".(int) $GLOBALS['s']['mid']."'";
      $res = sql($sql);
      while($row = sqlget($res)) $stud_courses[] = $row['CID'];

      $str = "";
      if (!$captured) $GLOBALS['controller']->captureFromVar(CONTENT, 'str', $str);

      $cids = getTrackCoursesArr($trid,(($level==0)?-1:$level), "=");
      if(count($cids) != 0) {
         $str.="<b> "._("Текущие курсы")." </b><br /><br />";
         $str.="<table width=100% class=main cellspacing=0>
         <tr>
            <th>"._("Курс")."</th>
            <th>"._("Контрольная точка")."</th>
            <th>"._("Дата сдачи")."</th>
            <th>"._("Оценка")."</th>
         </tr>";
         foreach( $cids as $cid ) {
               $spn=getrowSp( $cid );
               $str.="<tr><td valign=\"top\" ".($spn ? "rowspan=\"".$spn."\"" : '')."><b>";
               $str.=cid2title( $cid );
               $str.="</b><BR />";
               $tabl = (studCourseVed( $cid ));
               if($spn!=0){
               $count1  = $GLOBALS["rrr"];
                $proccompl=($count1/$spn)*100;

               $totalmark = getCourseMarkIfExists($cid,$GLOBALS['s']['mid']);
               if ($totalmark!='-') $str.=_("итоговая оценка").": {$totalmark}<br>";

               $str.=_("выполнено  учебного плана");
               $str.=makeProgress( intval($proccompl) );
               }
               $str.="</td>";
               $str.=$tabl;
         }
         $stud_courses = array_diff($stud_courses,$cids);
         if (is_array($stud_courses) && count($stud_courses)) {
             foreach( $stud_courses as $cid ) {
                   $spn=getrowSp( $cid );
                   $str.="<tr><td valign=\"top\" ".($spn ? "rowspan=\"".$spn."\"" : '')."><b>";
                   $str.=cid2title( $cid );
                   $str.="</b><BR />";
                   $tabl = (studCourseVed( $cid ));
                   if($spn!=0){
                   $count1  = $GLOBALS["rrr"];
                   $proccompl=($count1/$spn)*100;

                   $totalmark = getCourseMarkIfExists($cid,$GLOBALS['s']['mid']);
                   if ($totalmark!='-') $str.=_("итоговая оценка").": {$totalmark}<br>";

                   $str.=_("выполнено  учебного плана");
                   $str.=makeProgress( intval($proccompl) );
                   }
                   $str.="</td>";
                   $str.=$tabl;
             }
         }
         $str.="</table><br /><br />";
      }


      $cids = getTrackCoursesArr($trid,(($level==0)?-1:$level), "<");
      if(count($cids) != 0) {
         $str.="<b> "._("Прошедшие курсы")." </b><br /><br />";
         $str.="<table width=100% class=main cellspacing=0>
         <tr>
            <th>"._("Курс")."</th>
            <th>"._("Контрольная точка")."</th>
            <th>"._("Дата сдачи")."</th>
            <th>"._("Оценка")."</th>
         </tr>";
         foreach( $cids as $cid ) {
                $spn=getrowSp( $cid );
                $str.="<tr><td valign=\"top\" ".($spn ? "rowspan=\"".$spn."\"" : '')."><b>";
               $str.=cid2title( $cid );
               $str.="</b><BR />";
               $tabl = (studCourseVed( $cid ));
               if($spn!=0){
               $count1  = $GLOBALS["rrr"];
               $proccompl=($count1/$spn)*100;

               $totalmark = getCourseMarkIfExists($cid,$GLOBALS['s']['mid']);
               if ($totalmark!='-') $str.=_("итоговая оценка").": {$totalmark}<br>";

               $str.=_("выполнено  учебного плана");
               $str.=makeProgress( intval($proccompl) );
               }
               $str.="</td>";
               $str.=$tabl;
         }
         $str.="</table><br /><br />";
      }

      $cids = getTrackCoursesArr($trid,(($level==0)?-1:$level), ">");
      if(count($cids) != 0) {
         $str.="<b> "._("Будущие курсы")." </b><br /><br />";
         $str.="<table width=100% class=main cellspacing=0>
         <tr>
            <th>"._("Курс")."</th>
            <th>"._("Контрольная точка")."</th>
            <th>"._("Дата сдачи")."</th>
            <th>"._("Оценка")."</th>
         </tr>";
         foreach( $cids as $cid ) {
                $spn=getrowSp( $cid );
                $str.="<tr><td valign=\"top\" ".($spn ? "rowspan=\"".$spn."\"" : '')."><b>";
               $str.=cid2title( $cid );
               $str.="</b><BR />";
               $tabl = (studCourseVed( $cid ));
               if($spn!=0){
               $count1  = $GLOBALS["rrr"];
               $proccompl=($count1/$spn)*100;

               $totalmark = getCourseMarkIfExists($cid,$GLOBALS['s']['mid']);
               if ($totalmark!='-') $str.=_("итоговая оценка").": {$totalmark}<br>";

               $str.=_("выполнено  учебного плана");
               $str.=makeProgress( intval($proccompl) );
               }
               $str.="</td>";
               $str.=$tabl;
         }
         $str.="</table><br /><br />";
      }

      if (!$captured) $GLOBALS['controller']->captureStop(CONTENT);

  } else {
      $str.=studCourseVed_course($CID);
  }
  return($str);
}
function studCourseVed_course( $CID ) {

  if (empty($CID)) {
  	$GLOBALS['controller']->terminate();
	exit();
  }

  $r = array();
  global $s;

  $sqlq1="SELECT schedule.`SHEID`, `Title` , `Icon`, schedule.begin, schedule.cond_mark, schedule.cond_sheid,
          schedule.cond_progress, schedule.cond_avgbal, schedule.cond_sumbal, schedule.cond_operation
          FROM `schedule`, scheduleID, `EventTools`
          WHERE `vedomost` = 1
          AND `CID` = ".$CID."
          AND EventTools.TypeID=schedule.typeID
          AND schedule.SHEID = scheduleID.SHEID
          AND scheduleID.MID = '{$_SESSION['s']['mid']}'
          ORDER BY schedule.begin";

  $res  = sql( $sqlq1, "VQTer1" );
  $rcol = sqlrows($res);
  $sheids = array();
  while ($row = sqlget($res)) {
    if (!WeekSchedule::check_cond($row)) continue;
  	if (isset($sheids[$row['SHEID']])) continue;
  	$sheids[$row['SHEID']] = $row['SHEID'];
    $r[]= $row['SHEID'];
    $rt[]= $row['Title'];
    $tools=getField("scheduleID","toolParams","SHEID", $row['SHEID']);
    $temp_ex = getExecutedDate($tools, $s[mid]);
    if(trim($temp_ex) == "") {
            $ex = "-";
    }
    else {
            $ex = date("d.m.y H:i",getExecutedDate( $tools, $s[mid] ));
   }

    $rdate[$row['SHEID']] = $ex;
    $is_test[$row['SHEID']] = (strpos($tools, 'tests_testID') !== false);
    $ri[]= "<img src='images/events/".$row['Icon']."' align='absmiddle' hspace='3'>";
  }

  $str.="<table width=100% class=main cellspacing=0>
           <tr>
            <th>"._("Занятие")."</th>
            <th>"._("Дата сдачи")."</th>
            <th>"._("Оценка")."</th>
            <th>"._("Комментарий преподавателя")."</th>";

  if (empty($r))
//          return "[ нет контрольных точек ]";
          return $str. "</tr><tr><td colspan='4' align='center'>" . _('по данному курсу нет ни одного занятия на оценку') . "</td></tr></table>";

  $rmark=array();
  $sqlq2="SELECT `SHEID`, test_date, `V_STATUS` as mark, `scheduleID`.`SHEID` as SHEID, scheduleID.comments as comments
          FROM `scheduleID`
          WHERE `scheduleID`.`SHEID` IN (".implode($r,", ").")
          AND scheduleID.MID=".$s[mid]."
          ORDER BY `scheduleID`.`SHEID` ASC";
  $res  = sql($sqlq2,"VQTer3");
  while ($row = sqlget($res)) {
          $rmark[$row['SHEID']] =  $row['mark'];
          if ($rdate[$row['SHEID']] == '-') {
              $rdate[$row['SHEID']] = (strtotime($row['test_date']) <= 0) ? $rdate[$row['SHEID']] : date('d.m.y H:i',strtotime($row['test_date']));
          }
          $comments[$row['SHEID']] = $row['comments'];
    //echo "rdate: ".$rdate[$row['SHEID']]."<br>";
  }
  if (empty($rmark))
//          return "контрольные точки не назначались";
          return $str. "</tr><tr><td colspan='4' align='center'>" . _('по данному курсу нет ни одного занятия на оценку') . "</td></tr></table>";

  for ($j=0;$j<$rcol;$j++) {
    ############################################################################
    //
    // Вот здесь делаем проверку на то есть ли отметки по данному курсу
    // Здесь выводятся отметки
    //
    ############################################################################
    if (isset($rmark[$r[$j]])) {


      $mark=$rmark[$r[$j]];
      $str.="<tr>";
      $str.="<td width='50%'>";
      if ($is_test[$r[$j]]){
	      $str.= "<a href='test_test.php?CID={$CID}&sheid={$r[$j]}' title='" . _('Попытки тестирований') . "' target='_blank'>{$ri[$j]}</a></a>&nbsp;";
	      $str.= "<a href='test_test.php?CID={$CID}&sheid={$r[$j]}' title='" . _('Попытки тестирований') . "' target='_blank'>{$rt[$j]}</a>";
      } else {
      	  $str.= "{$ri[$j]}&nbsp;{$rt[$j]}";
      }
      $str.="</td>";
      $str.="<td align='center' nowrap>";
      $str.= $rdate[$r[$j]];
      $str.="</td>";
      $str.="<td nowrap align=center>";
      $intMark = (int)$mark;

      if (in_array($intMark,array(-3))) $missed++;
      if ($intMark=='-1') $incompleted++;

      if ($intMark <= -1) {
        $qq = "SELECT * FROM alt_mark WHERE `int`='{$intMark}'";
        $rr = sql($qq);
        if ($aa = sqlget($rr)) {
          $mark = $aa['char'];
        }
        else $mark = '.';

        if ($intMark==-2) $count++;
      }
      else {
          if ($intMark>0) {
              $count++;
          }
      }

      if ($mark != '.') {
          $formulaId = getFormulaIdBySheid($r[$j]);
          if ($formulaId>0) {
              $grMark = $GLOBALS['formulasGrMarks'][$formulaId][$mark];
              if (!empty($grMark)) $mark = $grMark;
          }
      }

      $str.=$mark;
      $str.="</td>\n";
      $str.="<td width='50%'>".nl2br($comments[$r[$j]])."</td>\n";
      $str.="</tr>\n";

    }
  }

  list($mark, $alias) = getCourseMark($CID,$s['mid']);
  if (!empty($mark)) {
        $str_mark = "<b>{$mark}</b>";
        if (!empty($alias)) $str_mark .= "&nbsp;({$alias})";
  } else {
        $str_mark = '-';
  }

  $str .= "<tr>
  				<td colspan='2' align='right'><b>" . _('Итоговая оценка по курсу') . ":&nbsp;</b></td>
  				<td colspan='2'>{$str_mark}</td>
  		   </tr>";
  $str .= "</table>";

  /*  не нужно

  $proccompl=($count/$rcol)*100;
  $str.="</table><BR />"._("выполнено  учебного плана")." <BR />";
  $str.=makeProgress( intval($proccompl) );

  $add = "<table width=100% class=main cellspacing=0>
          <tr><th>"._("ФИО")."</th><th align=center>"._("Кол-во пропусков")."</th><th align=center>"._("Кол-во несданных занятий")."</th><th align=center>"._("Итоговая оценка")."</th></tr>";
  $add .= "<tr>";
  $add .= "<td>".mid2name($s['mid'])."</td>";
  $add .= "<td align=center>".(int) $missed."</td>";
  $add .= "<td align=center>".(int) $incompleted."</td>";
  list($mark, $alias) = getCourseMark($CID,$s['mid']);
  if (!empty($mark)) {
        $str_mark = "><b>{$mark}</b>";
        if (!empty($alias)) $str_mark .= "<br>{$alias}";
  } else {
        $str_mark = '-';
  }
  $add .= "<td align=center{$str_mark}</td>";
  $add .= "</tr>";
  $add .= "</table><p>";
  $str = $add.$str;
*/

  return $str;
}

################################################################################

################################################################################

function getExecutedDate( $toolsparam, $mid ){

  $tid = getTestId( $toolsparam );
  if( $tid > 0 ) {
    $res=sql("SELECT *
              FROM loguser
              WHERE loguser.tid=$tid
              AND mid=$mid
              ORDER BY start","err_get_executed_time");
    while( $r=sqlget( $res) ) {
      foreach( $r as $rr ) $start=$r['start'];
    }
  }
  return( $start );
}
################################################################################

################################################################################
//
function getrowSp( $CID){
  global $s;

  $sqlq2="SELECT schedule.`SHEID`, `Title` , `Icon`, schedule.begin
          FROM `schedule`, scheduleID, `EventTools`
          WHERE `vedomost` = 1
          AND `CID` = '".(int) $CID."'
          AND EventTools.TypeID=schedule.typeID
          AND schedule.SHEID = scheduleID.SHEID
          AND scheduleID.MID = '{$_SESSION['s']['mid']}'
          ORDER BY schedule.begin";
  $res1  = sql( $sqlq2, "VQTer1" );
  $roSp = sqlrows($res1);
  return $roSp;
}



function studCourseVed( $CID ) {
  $r = array();
  global $s;

  $sqlq1="SELECT schedule.`SHEID`, `Title` , `Icon`, schedule.begin
          FROM `schedule`, scheduleID, `EventTools`
          WHERE `vedomost` = 1
          AND `CID` = '".(int) $CID."'
          AND EventTools.TypeID=schedule.typeID
          AND schedule.SHEID = scheduleID.SHEID
          AND scheduleID.MID = '{$_SESSION['s']['mid']}'
          ORDER BY schedule.begin";

  $res  = sql( $sqlq1, "VQTer1" );
  $rcol = sqlrows($res);
  while ($row = sqlget($res)) {
    $r[]= $row['SHEID'];
    $rt[]= $row['Title'];
    $tools=getField("scheduleID","toolParams","SHEID", $row['SHEID']);
    $temp_ex = getExecutedDate($tools, $s[mid]);
    if(trim($temp_ex) == "") {
            $ex = "-";
    }
    else {
              $ex = date("d.m.y H:i",getExecutedDate( $tools, $s[mid] ));
   }

    $rdate[] = $ex;
    $ri[]= "<img src='images/events/".$row['Icon']."' align='absmiddle' hspace='3'>";
  }
  if (empty($r))
//          return "[ нет контрольных точек ]";
          return "<td>-</td><td>-</td><td>-</td></tr>";
  $rmark=array();
  $sqlq2="SELECT `SHEID`, test_date, `V_STATUS` as mark, `scheduleID`.`SHEID` as SHEID
          FROM `scheduleID`
          WHERE `scheduleID`.`SHEID` IN (".implode($r,", ").")
          AND scheduleID.MID=".$s[mid]."
          ORDER BY `scheduleID`.`SHEID` ASC";
  $res  = sql($sqlq2,"VQTer3");
  while ($row = sqlget($res)) {
          $rmark[$row['SHEID']] =  $row['mark'];
    $rdate[$row['SHEID']] = ($row['test_date'] == '0000-00-00 00:00:00')?"":$row['test_date'];
    //echo "rdate: ".$rdate[$row['SHEID']]."<br>";
  }
  if (empty($rmark))
  return "<td>-</td><td>-</td><td>-</td></tr>";
//          return "контрольные точки не назначались";
          /*
  $str.="<table width=100% class=main cellspacing=0>
            <tr>
            <th>Контрольная точка</th>
            <th>Дата сдачи</th>
            <th>Оценка</th>";*/
  for ($j=0;$j<$rcol;$j++) {
    ############################################################################
    //
    // Вот здесь делаем проверку на то есть ли отметки по данному курсу
    // Здесь выводятся отметки
    //
    ############################################################################
    if (isset($rmark[$r[$j]])) {


      $mark=$rmark[$r[$j]];
      if($j>0){
        $str.="<tr>";
      }
      $str.="<td>";
      $str.=$ri[$j].$rt[$j];
      $str.="</td>";
      $str.="<td align='center'>";
      $str.= $rdate[$j];
      $str.="</td>";
      $str.="<td>";
      $intMark = (int)$mark;
      if ($intMark <= -1) {
        $qq = "SELECT * FROM alt_mark WHERE `int`='{$intMark}'";
        $rr = sql($qq);
        if ($aa = sqlget($rr)) {
          $mark = $aa['char'];
        }
        else $mark = '-';
      }
      else {
          if ($intMark>0) {
              $count++;
          }
      }

      if ($mark != '-') {
          $formulaId = getFormulaIdBySheid($r[$j]);
          if ($formulaId>0) {
              $grMark = $GLOBALS['formulasGrMarks'][$formulaId][$mark];
              if (!empty($grMark)) $mark = $grMark;
          }
      }
      $str.=$mark;
      $str.="</td>\n";
      $str.="</tr>";
    }
  }

  $GLOBALS["rrr"] = $count;
  return $str;
}
################################################################################

################################################################################

//  Begining script. Input point
//
//    ...........Functions describe above.............
//
//       studCourseVed( $CID )
//       getExecutedDate( $toolsparam, $mid )
//       studVed( $CID, $trid=0, $level=0 )
//       excludeFromGroup( $mid, $gid )
//       isGeneratedName($gid, $tname )
//       excludeFromGroups($mid, $cid, $tname )
//       addToGroup( $gid, $mid, $cid )
//       newGroup( $gname, $cid )
//       isGroup( $name, $cid )
//       applyGroups( $mid, $cid )
//       makeProgress( $proccompl )
//       teachVed( $CID , $filter="")
//       isWeek( )

$html = show_tb(1);

/**
* Удаление прошедших обучение с курса
*/
if (isset($_GET['mids2del']) && !empty($_GET['mids2del']) && $_GET['CID']) {

    $mids2del = unserialize(base64_decode($_GET['mids2del']));

    if (is_array($mids2del) && count($mids2del)) {

        foreach($mids2del as $mid2del) {

            $sql = "SELECT SHEID FROM schedule WHERE CID='".(int) $_GET['CID']."'";
            $res = sql($sql);

            if (sqlrows($res)) {

                while ($row = sqlget($res)) {

                $sql = "DELETE FROM scheduleID WHERE SHEID='".(int) $row['SHEID']."' AND MID='".(int) $mid2del."'";

                sql($sql);

                }

            }

            $sql = "DELETE FROM Students WHERE MID='".(int) $mid2del."' AND CID='".(int) $_GET['CID']."'";
            sql($sql);

        }

    }

}

$allheader = ph("<FONT SIZE=+1>"._("Успеваемость")."</FONT>");
if ($teach) {
  $allcontent = loadtmpl("ved-main.html");
}
elseif($stud)
  $allcontent = loadtmpl("ved-main_stud.html");

if ($s[user][vsorts]) {
  $strsort = "<option value='0'>"._("По хронологии")."</option>
              <option value='1' selected>"._("По типам")."</option>";
}
else {
  $strsort = "<option value='0' selected>"._("По хронологии")."</option>
              <option value='1' >"._("По типам")."</option>";
}


$html = str_replace("[ALL-CONTENT]",$allcontent,$html);
$html = str_replace("[HEADER]",$allheader,$html);

// CID - курс ID

if((!isset($CID))||($CID == 0)) { // если первый раз - то найти семестр и становить для вывода а если его нет, то все курсы
  $tracks = getPeopleTracks( $s[mid] );
  $trid = $tracks[0];
  if( $trid > 0 )
    $level = getCurLevel( $trid, $s[mid] );

}

//echo "trid: ".$trid."<br>";
//echo "level: ".$level."<br>";
//echo "CID: ".$CID."<br>";
/*
if(  $CID <= 0) {
  if($CID < 0)
      $level = -$CID; // семестр
  else
      $level = -1;
  $tracks=getPeopleTracks( $s[mid] );
  $trid=$tracks[0];
  if( $trid > 0 )
      $max_level=last_level( $trid );
}*/

  $cselect = "<option value=\"0\" ";
  if($CID == 0) $cselect .= "selected";
  $cselect .= ">--"._("выберите курс")."--</option>";

  $str = selCourses($courses, $CID , $GLOBALS['controller']->enabled);

  $extra_html = '';
  if ($s['perm'] >= 2) {
      $extra_html = "onChange=\"get_group_select(this.value);\"";
  }
  $GLOBALS['controller']->addFilter(_("Курс"), 'CID', $str, $CID, true, 0, true, $extra_html);
  $cselect .= $str;

  $owner_soid = (int) $_REQUEST['owner_soid'];
  //$str = selGrved($CID,$gr, $GLOBALS['controller']->enabled);
  if ($s['perm'] >= 2) {
      $GLOBALS['controller']->addFilter(_("Группа"), 'group', 'div', get_group_select($CID, $gr));

      if (defined('ALLOW_SWITCH_2_AT') && ALLOW_SWITCH_2_AT) {
          $smarty = new Smarty_els();
          $defaultValue = 0;
          if ($owner_soid > 0) {
              $defaultValue = getField('structure_of_organ','owner_soid','soid',$owner_soid);
          }
          $smarty->assign('list_name','owner_soid');
          $smarty->assign('container_name','container_owner_soid');
          $smarty->assign('list_extra'," style=\"width: 300px;\" ");
          $smarty->assign('list_default_value',(int) $defaultValue);
          $smarty->assign('list_selected', (int) $owner_soid);
          $smarty->assign('url',$GLOBALS['sitepath'].'structure.php');
          $GLOBALS['controller']->addFilter(_('Подразделение'), 'owner_soid', 'div', $smarty->fetch('control_treeselect.tpl'), $owner_soid);
      }

  }

  $GLOBALS['controller']->addFilterJavaScript($sajax_javascript);

  if ($teach) {
    $str = teachVed( $CID, $FILTER );
    $html=str_replace("[MAINT]",$str, $html);
    if ($GLOBALS['controller']->enabled) {
        $str=str_replace("[CURCID]",$CID,$str);
        $str=str_replace("[GNUM]",gGroupNum($CID),$str);
    }
    $GLOBALS['controller']->captureFromReturn(CONTENT, $str);
  }
  elseif($stud) {
    $str = studVed( $CID, $trid, $level, true);
    $html=str_replace("[MAINT]",$str, $html);
    $GLOBALS['controller']->captureFromReturn(CONTENT, $str);
    /**
    * Среднии оценки по типам занятий пока скроем. Слушателю это не нужно.

    if ($CID) {
        //$sql = "SELECT EventTools.TypeName as name, EventTools.TypeID as id
        //        FROM EventTools ORDER BY EventTools.TypeName";
        $sql = "SELECT EventTools.TypeName as name, EventTools.TypeID as id, eventtools_weight.weight
                FROM EventTools
                LEFT JOIN eventtools_weight
                ON (eventtools_weight.event=EventTools.TypeID AND eventtools_weight.cid='".(int) $CID."')
                WHERE eventtools_weight.event IS NULL OR
                (eventtools_weight.event IS NOT NULL AND eventtools_weight.weight<>-1)
                ORDER BY EventTools.TypeName";

        $res = sql($sql);
        $tab = "<table width=100% class=main cellspacing=0>";
        $tab .= "<tr><th>"._("Тип занятия")."</th><th>"._("Средняя оценка")."</th></tr>";
        while($row = sqlget($res)) {
            $sql = "SELECT AVG(scheduleID.V_STATUS) as mark
                    FROM schedule
                    LEFT JOIN scheduleID ON (scheduleID.SHEID=schedule.SHEID)
                    WHERE scheduleID.MID='".(int) $s['mid']."' AND
                    schedule.CID='".(int) $CID."' AND
                    scheduleID.V_STATUS>=0 AND
                    schedule.typeID='".(int) $row['id']."'";
            $res2 = sql($sql);
            if (sqlrows($res2) && ($row2 = sqlget($res2))) $row['mark'] = $row2['mark'];
            $tab .= "<tr><td>{$row['name']}</td><td>".round($row['mark'])."</td></tr>";
        }
        $tab .= "</table>";
        $GLOBALS['controller']->captureFromReturn('m150107',$tab);
    }
*/
  }

  $strChMoreInfoStatus = (isset($_GET['ch_more_info']) && $_GET['ch_more_info']) ? "checked" : "";
  $strChMoreInfoValue = (isset($_GET['ch_more_info']) && $_GET['ch_more_info']) ? "0" : "1";
  $html = str_replace("[ch_more_info_value]", $strChMoreInfoValue, $html);
  $html = str_replace("[ch_more_info_status]", $strChMoreInfoStatus, $html);

  $html=str_replace("[SELECT-COURSES]",$cselect."123",$html);
  $html=str_replace("[CURCID]",$CID,$html);

  //fn echo "gr: ".$gr;

  $html=str_replace("[GRID]",$gr,$html);

  $ab=gAbNum($CID);

  if( $ab > 0 ) {
    if($teach) {
      $html=str_replace("[ANUM]","",$html);
    }
    else {
      $html=str_replace("[ANUM]",getIcon("people",_("записалось на курс").": $ab "._("чел.")).$ab,$html);
    }
  }
  else
    $html=str_replace("[ANUM]","",$html);

  $cw=gModerNum($CID);
  $html=str_replace("[MNUM]",_("Проверка преподавателем")." ($cw)",$html);

/*
  if( $cw > 0 )
    $html=str_replace("[MNUM]",getIcon("tocheck","$cw работ на проверку").$cw,$html);
  else
    $html=str_replace("[MNUM]","",$html);
*/
  $html=str_replace("[GNUM]",gGroupNum($CID),$html);
  $html=str_replace("[SORT]",$strsort,$html);

  $strlook="<span id='look_icon' style='cursor:hand' onclick=\"putElem('filter');removeElem('select');\">".getIcon("look",_("выбрать по критериям"))."</span>";
  $html=str_replace("[LOOK]",$strlook,$html);

  $tmp="<span class=hidden id='filter'><FORM ACTION='ved.php4' METHOD=POST><input type=hidden name=CID value=".$CID.">";
  $tmp.=showSelectGroups( $CID, $FILTER );
  //fn $tmp.=stGroupshowSelec( $CID, $FILTER );
  $tmp.="<INPUT type=button name='look' value='"._("Закрыть")."' onclick=\"removeElem('filter');putElem('select')\"><INPUT type=submit name='look' value='"._("Искать")."'></FORM></span>";

  $strWarning = (($gr == '0') || ($gr == '-1') || ($CID == '0') ) ? "" : "<span style='font-family: Verdana; font-size: 10px;'><b>"._("Внимание!")."</b> "._("Отображаются только те занятия, которые назначены")." [sTUDENT_ALIAS-DAT-ONE] "._("из выбранной группы")."</span>";

  $html=str_replace("[GRFILTER]", $tmp ,$html);
  $html=str_replace("[GRSEL]",$str,$html);
  $html=student_alias_parse(str_replace("[GRTEXT]",$strWarning,$html));
  printtmpl($html);
?>