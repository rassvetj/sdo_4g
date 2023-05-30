<?php
/*  "SELECT * FROM People, students WHERE People.MID=students.MID AND students.CID=$cid"
students  SID ( int ) [not_null primary_key  unique_key auto_increment ]
MID ( int ) [not_null ]
CID ( int ) [not_null ]
cgid ( int ) [not_null ]
Registered ( int ) [not_null ]

*/

function inTable( $mode, $mid ){
  switch( $mode ){
    case 1:
      $query = "SELECT * FROM claimants WHERE MID=".$mid;
    break;
    case 2:
      $query = "SELECT * FROM Students WHERE MID=".$mid;
    break;
    case 3:
      $query = "SELECT * FROM graduated WHERE MID=".$mid;
    break;
  }
 $res=sql( $query, "abiturTogr01");
 while( $r=sqlget($res)){
   $t.=$r[CID].", ";
 }
 return( $t );
}

function last_level( $trid ){
  $tmp="SELECT level FROM tracks2course WHERE trid=$trid";
  $res=sql($tmp,"ERRsgetTrackLevel");
  $max=-1;
  while( $r=sqlget( $res ) ){
    if( $r[level] > $max ) $max=$r[level];
  }
  return( $max );
}

function getCurLevel( $trid, $mid ){
   $tmp="SELECT level FROM tracks2mid WHERE trid=$trid AND mid=$mid";

   $res=sqlval($tmp,"ERRsgetCurTrackLevel");
   if( $res )
     $ret=$res[level];
   else
     $ret=-1;
   return( $ret );
}

// started int, changed int, stoped int, status int, info TEXT,

function getTrackHistory(  $trid, $mid ){
  $tmp="SELECT * FROM tracks2mid WHERE trid=$trid AND level=$level";
  $res=sql( $tmp, "ERR-getHistory");
  while( $r=sqlget( $res )){
    $inf[start]=$r[started];
    $inf[change]=$r[chenged];
    $inf[stop]=$r[stoped];
    $inf[sign_mid]=$r[sign_mid];
    $inf[status]=$r[status];
  }
  return( $inf );
}
function startTrack( $trid, $mid ){
   $tmp="UPDATE tracks2mid
            SET started=".time().",
                changed=".time()."
            WHERE trid=$trid AND mid=$mid";
   $res=sql( $tmp, "ERR Start TrackLevel");
   return( $res );
}
function stopTrack( $trid, $mid ){
   $tmp="UPDATE tracks2mid
            SET stoped=".time()."
            WHERE trid=$trid AND mid=$mid";

   $res=sql( $tmp, "ERR Stop TrackLevel");
   return( $res );
}

function setStatus( $trid, $mid, $status ){
   $tmp="UPDATE tracks2mid
            SET status=$status,
                stoped=".time()."
            WHERE trid=$trid AND mid=$mid";
//   echo $tmp;
   $res=sql( $tmp, "ERRsetTrackStatusLevel");

   $sql = "SELECT level, changed FROM tracks2mid WHERE trid = '$trid' AND mid = '$mid'";
   $res = sql($sql);
   $row = sqlget($res);
   if ($row) {
       $level = $row['level'];
       $changed = $row['changed'];
   }

   if ($level > 0) {
       $sql = "SELECT DISTINCT cid FROM tracks2course WHERE trid='$trid' AND level='$level'";
       $res = sql($sql);
       while($row = sqlget($res)) {
           sql("DELETE FROM Students WHERE CID = '{$row['cid']}' AND MID = '$mid'");
           if ($status == 1) {
               sql("INSERT INTO Students (MID, CID, Registered, time_registered) VALUES ('$mid', '{$row['cid']}', '$changed', ".$GLOBALS['adodb']->DbTimestamp($changed).")");
           }
       }
   }

   return( $res );
}

function setLevel( $trid, $mid, $level ){

  $tmp="UPDATE tracks2mid
            SET level=$level,
                changed=".time()."
            WHERE trid=$trid AND mid=$mid";

   $res=sql( $tmp, "ERRsetTrackLevel");
   return( $res );
}

function setTrack2mid( $trid, $mid, $level=1 ){

//  надо запомнить дату
  $cur_level = getCurLevel( $trid, $mid );
  if( $cur_level <= 0 ){
    $cur_level=1; // назначим на первый семестр
  }else
    $cur_level=$level;

  sql();

}

function OpenCourses($cids, $mid, $boolMail){

    if (is_array($cids) && count($cids)) {

        require_once($GLOBALS['wwf'].'/lib/classes/Chain.class.php');

        $info = array();
        $sql = "SELECT CID, TypeDes, chain FROM Courses WHERE CID IN ('".join("','",$cids)."')";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $info[$row['CID']] = $row;
        }

        foreach($cids as $cid) {
            if ($info[$cid]['TypeDes']<0) $info[$cid]['TypeDes'] = $info[$cid]['chain'];

            if ($info[$cid]['TypeDes'] == 0) {
                tost( $mid, $cid, $boolMail );
            } else {
                $sql = "SELECT * FROM claimants WHERE MID='".(int) $mid."' AND CID='".(int) $cid."' AND Teacher='0'";
                $res = sql($sql);
                if (!sqlrows($res)) {
                    $sql = "SELECT * FROM Students WHERE MID='".(int) $mid."' AND CID='".(int) $cid."'";
                    $res = sql($sql);
                    if (!sqlrows($res)) {
                        CChainLog::erase($cid,$mid);
                        sql("INSERT INTO claimants (MID,CID,Teacher) VALUES ('".(int) $mid."', '".(int) $cid."', 0)");
                        return sqllast();
                    }
                }
            }
        }
    }
}


function OpenCourse( $cid, $mid, $boolMail ){ //

    require_once($GLOBALS['wwf'].'/lib/classes/Chain.class.php');

    $sql = "SELECT TypeDes,chain FROM Courses WHERE CID='".(int) $cid."'";
    $res = sql($sql);
    if (sqlrows($res)) $row = sqlget($res);

    if ($row['TypeDes']<0) $row['TypeDes'] = $row['chain'];

    if ($row['TypeDes'] == 0) {
        tost( $mid, $cid, $boolMail );
    } else {
        $sql = "SELECT * FROM claimants WHERE MID='".(int) $mid."' AND CID='".(int) $cid."' AND Teacher='0'";
        $res = sql($sql);
        if (!sqlrows($res)) {
            $sql = "SELECT * FROM Students WHERE MID='".(int) $mid."' AND CID='".(int) $cid."'";
            $res = sql($sql);
            if (!sqlrows($res)) {
                CChainLog::erase($cid,$mid);
                sql("INSERT INTO claimants (MID,CID,Teacher) VALUES ('".(int) $mid."', '".(int) $cid."', 0)");
                return sqllast();
            }
        }
    }
}

function CloseCourses($cids, $mid, $boolMail = true) {
    if (count($cids) > 0) {
        CChainLog::eraseCourses($cids,$mid);

        $graduated = array();
        $sql = "SELECT DISTINCT CID FROM graduated WHERE MID = '".(int) $mid."' AND CID IN ('".join("','",$cids)."')";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $graduated[$row['CID']] = true;
        }

        foreach($cids as $cid) {
            if (!isset($graduated[$cid])) {
                $sql = "INSERT INTO graduated (MID, CID) VALUES (".$mid.", ".$cid.")";
                sql($sql);
                if ($boolMail) {
                    mailTostud("togr",$mid,$cid,"");
                }
            }
        }

        sql("DELETE FROM claimants WHERE MID = '".(int) $mid."' AND CID IN ('".join("','",$cids)."')");
        sql("DELETE FROM Students WHERE MID = '".(int) $mid."' AND CID IN ('".join("','",$cids)."')");
    }
}

function CloseCourse( $cid, $mid, $boolMail = true ){ //
    require_once($GLOBALS['wwf'].'/lib/classes/Chain.class.php');
    CChainLog::erase($cid,$mid);
    togr( $mid, $cid, $boolMail );
}


function getTrackCoursesArr( $trid, $level=0, $relation = "=" ){ //
   // взять все курсы по специальности заданной ступени
   if( ($level > 0) || ($level==-1) )
       $tmp="SELECT * FROM tracks2course WHERE trid=$trid AND level$relation$level";
   else
       $tmp="SELECT * FROM tracks2course WHERE trid=$trid";

   $res=sql($tmp,"ERRgetTrackCourses");

    while( $r=sqlget($res) ){
       $cids[]=$r[cid];
    }
   return( $cids );
}

function getTrackCoursesInfo( $trid, $level=0){
   $sql = "SELECT c.* 
           FROM Courses c
           LEFT JOIN tracks2course t ON (t.cid = c.CID)
           WHERE t.level = '$level' AND t.trid = '$trid'";
   $res = sql($sql);
   $courses = array();
   while ($row = sqlget($res)) {
       $courses[$row['CID']] = $row;
   }
   
   return $courses;
}

function studentsToLevel($trid, $mids, $action) {
    if (is_array($mids) && count($mids)) {
        $maxLevel = last_level($trid);
        $ret = true;

        // Узнаём текущий уровень
        $sql = "SELECT mid, level FROM tracks2mid WHERE trid='".(int) $trid."' AND mid IN ('".join("','",$mids)."')";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $levels[$row['mid']] = (int) $row['level'];
        }

        $money = getAccountsStates($trid, $mids);
        if (defined('USE_BOLOGNA_SYSTEM') && !USE_BOLOGNA_SYSTEM) $levelCost = getLevelCost($trid,-1);

        $message = array();
        foreach($levels as $mid=>$level) {
            require_once($GLOBALS['wwf'].'/lib/classes/Chain.class.php');

            if ($action == '+') {
                $nextLevel = (int) ($level+1);
            } else {
                $nextLevel = (int) ($level-1);
            }

            if (($nextLevel < 0) || ($nextLevel > $maxLevel)) continue;
           
	//проверка курсов на доступность 
            $coursesInfo = getTrackCoursesInfo($trid, $nextLevel);
            $errors = false;            
            foreach ($coursesInfo as $CID=>$info) {
                if ($info['Status'] != 2) {
                    $message[] = _("Курс `{$info['Title']}` {$nextLevel}ого семестра не опубликован");
                    $errors = true;
                }else {                    
                    list($y, $m, $d) = explode('-', $info['cEnd']);
                    
                    if (mktime(0, 0, 0, $m, $d, $y)<time()) {
                        $message[] = _("Курс `{$info['Title']}` {$nextLevel}ого семестра уже закончен");
                        $errors = true;
                    }
                }
            }
            if ($errors) {
                $message[] = getpeoplename($mid)._(" не переведён на {$nextLevel}й семестр");
                continue;
            }
            
            
            if (defined('USE_BOLOGNA_SYSTEM') && USE_BOLOGNA_SYSTEM) {
                $levelCost = CCredits::countTrackLevelCredits($trid, $nextLevel);
            }
            $sum = $money[$mid] - $levelCost;
            if ($sum >= 0) {
                $sql = "UPDATE money SET sum='$sum',  date=".time().", type='0' WHERE mid='$mid' AND trid='$trid'";
                $ok = sql($sql);
                if ($ok) {
                    if ($level > 0) {
                        $cids = getTrackCoursesArr($trid, $level);
                        CloseCourses($cids,$mid,false);
                        mailTostud( "togr_track", $mid, $cids, "", $trid );
                    }
                    $level = $nextLevel;
                    if ($level > 0) {
                        $cids=getTrackCoursesArr( $trid, $level );
                        if (count($cids)) {
                            if ($action == '+') {
                                mailTostud( "tost_track", $mid, $cids, "", $trid );  // при переводе - перегенерируем пароль
                            }

                            OpenCourses($cids,$mid,0);
                        }
                    }

                    if(($level <= $maxLevel) && ($level >= 0)) {
                        setLevel( $trid, $mid, $level );
                    }
                }
            }else {
            	$ret = false;
            }
        }

        if (count($message)) {
            $message = implode('<br />', $message);
            $ret = $message;
        }

    }
    return $ret;
}

function levelOnTheTrack( $trid, $mid, $todo, $max_level=-1 ){
  //  надо запомнить дату   перевода
  $level = intval( getCurLevel( $trid, $mid ) );
  if( $level > 0 ){
     $cids=getTrackCoursesArr( $trid, $level );
     if( count( $cids ) > 0 ){
        foreach( $cids as $cid ){
           CloseCourse( $cid, $mid );
        }
     }
  }
  if( $todo=="+" ){
     if (defined('USE_BOLOGNA_SYSTEM') && USE_BOLOGNA_SYSTEM)
     $ok=discount( $trid, $mid, ($level+1));
     else
     $ok=discount( $trid, $mid, $level ) ;
     if( $ok )
        $level++;
     else { // недостаточно средств
     }

  }
  else {
      if (defined('USE_BOLOGNA_SYSTEM') && USE_BOLOGNA_SYSTEM) {
        $ok=discount( $trid, $mid, ($level-1));
        if ($ok) $level--;
      } else $level--;

  }

  if( $level > 0 ) {
     $cids=getTrackCoursesArr( $trid, $level );
     if( count( $cids ) > 0 ) {
        if($todo == "+")
           mailTostud( "tost_track", $mid, $cids, $more, $trid );  // при переводе - перегенерируем пароль
        foreach( $cids as $cid ){
           OpenCourse( $cid, $mid, 0 );
        }
     }
  }

  if( $max_level < 0) {
     $max_level=last_level( $trid );
  }



  if(($level <= $max_level)&&($level >= 0)) {
     setLevel( $trid, $mid, $level );
  }
  else {
  }

}

function getPeopleTracks( $mid ){
  $tracks = array();
  $tmp="SELECT *
               FROM tracks2mid
               WHERE mid=$mid";
  $res=sql( $tmp,"getPeopleTrack0001" );
  while( $r=sqlget( $res ) ){
    $tracks[]=$r[trid];
  }
//  echo $tmp.":".count($tracks);
  return( $tracks );
}


function registration2track( $trid, $mid, $teacher, $boolMsg = true ){
   global $studentstable;
   global $peopletable;
   global $optionstable;
   global $coursestable;
   global $teacherstable;
   global $claimtable;
   global $Login;
   global $Password;

   // Проверка на присутствие у юзера данной специальности
   if (in_array($trid,getPeopleTracks($mid))) {
       return false;
   }

   $time=time();

   if ($boolMsg) {
      $from=getField($optionstable,"value","name","dekanEMail");
      $fromname=getField($optionstable,"value","name","dekanName");
      $headers = "From: $fromname<$from>\n";
      $headers .="Content-type: text/html; Charset={$GLOBALS['controller']->lang_controller->lang_current->encoding}\n";
      $headers .= "X-Sender: <$from>\n";
      $to=getField( $peopletable,"EMail","MID",$mid);
      $query = "SELECT * FROM People WHERE MID = $mid";
      $result = sql($query);
      $row = sqlget($result);
      $LastName = $row['LastName'];
      $FirstName = $row['FirstName'];
      $r = sql("SELECT * FROM tracks WHERE trid='{$trid}'");
      if ($a = sqlget($r)) {
         $strTrackName = $a['name'];
         $subj=_("Спасибо за Вашу заявку на обучение по специальности.");
         $msg=_("Спасибо за Вашу заявку на обучение по специальности ").$strTrackName.".<br>\n
         "._("Ваша заявка будет рассмотрена в ближайшее время.
         После ее рассмотрения и в случае положительного результата Ваш аккаунт будет активирован.
         Для доступа используйте следующие данные:")."<br />
         "._("Логин:")." $Login<br />
         "._("Пароль:")." $Password<br /><br />
         \n "._("Желаем успехов!")."<br>\n
         <hr><br>\n".$fromname."<br>\n<a href=\"mailto:".$from."\">".$from."</a>";
         @mail($to, $subj, $msg, $headers);
         echo _("Ваша заявка на обучение по специальности")."\n   $strTrackName\n"._("отправлена! На данную специальность вы можете быть зачислены\nтолько с разрешения преподавателей или представителя учебной администрации.\nВы немедленно получите E-mail, как только вы будете зачислены.\nДо этого момента вы не будете являться учащимся специальности.");
         $subjx=_("Новая заявка на обучение.");
         $msgx=_("Я").", ".$LastName." ".$FirstName.student_alias_parse(", "._("прошу зарегистрировать меня в качестве")." [sTUDENT_ALIAS-ROD-ONE] "._("на специальность")." ").$strTrackName.".<br>\n"._("Для cвязи мой e-mail:")." <a href=\"mailto:".$to."\">".$to."</a><br>\n\n<hr><br>\n".$fromname."<br>\n<a href=\"mailto:".$from."\">".$from."</a>";
         @mail($from, $subjx, $msgx, $headers);
      }
   }
   if( !$teacher ) {
      $tmp="INSERT INTO tracks2mid (mid, trid, status, level, started, do_next_level)
            VALUES ($mid, $trid, 0, 0, $time, 0)";
      $res=sql( $tmp,"registrationTrack0001" );
   }
   return( $res );
}

function delFromTrack ($mid, $trid=false) {
    $trid = (int) $trid;
    $mid = (int) $mid;

    $row = sqlget(sql("SELECT * FROM tracks2mid WHERE mid='$mid'" . (($trid)?" AND trid = '$trid'":'')));

    if (count($row)) {
        if ($row['level']) {
            //TODO: делаем что-то для удаления человека проучившегося хотябы один семестр
        }

        sql("DELETE FROM tracks2mid WHERE mid='$mid'" . (($trid)?" AND trid = '$trid'":''));
    }
}

function setStatusOnTrack( $trid, $mid, $status ){

}

function getStatusOnTrack( $trid, $mid ){

}


function getFinished( $trid, $level=0 ){  // если 0- то все
   // ищет всех уч-ся по специальности в заданном семестре кто прошел обучение
                                    // где храниться что чел прошел обучение?
   $cids=getTrackCoursesArr( $trid, $level );
   if( count($cids)>0 ){

     foreach( $cids as $cid ){
        isGraduated( $cid, $mid );
        // выбрать всех  кто закончил курс и учится по спец в данном семестре и закончил курс
       $tmp="SELECT * FROM tracks2course WHERE trid=$trid AND level=$level";

         $q = "SELECT MID, CID
                   FROM graduated, track2mid
                   WHERE graduated.CID=$cid
                     AND graduated.MID=track2mid.mid
                     AND track2mid.level=$level";
         sql( $query,"abiturTogr02" );
         // проверить - все ли курсы семестра сданы?

     }
   }
}

// что если?!
// удалили специальность\
// удалили курс
// удалили студента ?

function serviceTrack( $trid, $level=0 ){
 // всех закончивших обучение по семестру переводит на след семестр
  $levels=getLevelsCount( $trid );
//  for( $i=1; $i<=$levels; $i++){
  foreach( $levels as $i ){
    $studs=getFinished( $trid, $i );
    if( count($studs) > 0 ){
      foreach( $studs as $stud ){

        nextLevelOnTheTrack( $trid, $stud ); // переводит на следующий семестр
      }
    }
  }
}



function showSuds( $studs ){
    if( count($studs) > 0 ){
      foreach( $studs as $stud ){
        $info=getInfo( $stud );
        echo "<LI>".$info[fName];
      }
    }

}

function add_money( $trid, $mid, $summ ){
 // добавляет на счет учащегося сумму
 global $s;
 $m=getAccountState( $trid, $mid );
 if( $m === false ){
   $q="INSERT INTO money (mid, trid, sum, date, type, sign) values (
   $mid, $trid, $summ, ".time().", '0', '".$s[mid]."')";
 }else{
   $q="UPDATE money SET sum=".($summ+$m).",  date=".time().", type='0' , sign='".$s[mid]."' WHERE mid=$mid AND trid=$trid";
 }
// echo $q;
 $res=sql( $q, "err-add money");
 return( $res );
}

function getLevelCost( $trid, $level ){
 // totalcost=                       возвраает стоимость семестра обучения
  $q="SELECT * FROM tracks WHERE trid=$trid";
  $res=sqlval( $q, "ERR get money");
  return( $res[totalcost] );
}

function discount( $trid, $mid, $level ){
         if (!$m=getAccountState( $trid, $mid )) $m = 0;
         if (defined('USE_BOLOGNA_SYSTEM') && USE_BOLOGNA_SYSTEM) $lc = CCredits::countTrackLevelCredits($trid,($level));
         else $lc=getLevelCost($trid, $level);
         $summ=$m-$lc;
         if( $summ >= 0 ) {
                   $q="UPDATE money SET sum=$summ,  date=".time().", type='0' WHERE mid=$mid AND trid=$trid";
                   $res=sql( $q, "err-add money");
         }
         return( $res );
}

function getAccountsStates($trid, $mids) {
    $ret = array();
    if (is_array($mids) && count($mids)) {
        $sql = "SELECT mid, sum FROM money WHERE trid = '".(int) $trid."' AND mid IN ('".join("','",$mids)."')";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $ret[$row['mid']] = $row['sum'];
        }
    }
    return $ret;
}

function getAccountState( $trid, $mid ){
 $q="SELECT * FROM money WHERE mid=$mid AND trid=$trid";
 $res=sqlval( $q, "ERR get money");
 //echo "<H1>$trid+$mid:".$res[sum]."</H1>";
 if( $res ) return( max($res[sum], 0) );
 else return false;
}

function getTracksOptions()
{
        $q = "SELECT * FROM tracks WHERE status=1";
        $r = sql($q);
        while ($a = sqlget($r)) {
                $str .= "<option value='{$a['trid']}'>{$a['name']}</option>";
        }
        return $str;
}

function get_courses_count() {
	$sql = "SELECT * FROM Courses";
	$res = sql($sql);
	return sqlrows($res);
}
function search_courses_used($trid, $level) {
    intval('trid level');
	$used = '';
	$sql = "
		SELECT
		  `Courses`.CID,
		  `Courses`.Title
		FROM
		  `tracks`
		  INNER JOIN `tracks2course` ON (`tracks`.trid = `tracks2course`.trid)
		  INNER JOIN `Courses` ON (`tracks2course`.cid = `Courses`.CID)
		WHERE
		  (`tracks`.trid = {$trid}) AND
		  (`tracks2course`.level = {$level}) AND
		  `Courses`.type = '0'
	";
	$res = sql($sql);
	while ($row = sqlget($res)) {
           $used .= "<option value=\"{$row['CID']}\"> ".htmlspecialchars($row['Title'], ENT_QUOTES)."</option>";
	}
	return $used;
}
function search_courses_unused($search = '', $trid = 0, $level = 0) {
    intval('trid level');
	$unused = '';
	$search = iconv('UTF-8',$GLOBALS['controller']->lang_controller->lang_current->encoding,unicode_urldecode($search));
    $search = trim($search);
    $search = str_replace('*','%',$search);
	$search = $GLOBALS['adodb']->quote("%{$search}%");
	$search_upper = strtoupper($search);

    $used = array();
    if ($trid && $level) {
        $sql = "
		SELECT
		  `Courses`.CID,
		  `Courses`.Title
		FROM
		  `tracks`
		  INNER JOIN `tracks2course` ON (`tracks`.trid = `tracks2course`.trid)
		  INNER JOIN `Courses` ON (`tracks2course`.cid = `Courses`.CID)
		WHERE
		  (`tracks`.trid = {$trid}) AND
		  (`tracks2course`.level = {$level}) AND
		  `Courses`.type = '0'
        ";
        $res = sql($sql);
        while ($row = sqlget($res)) {
            $used[$row['CID']] = $row['CID'];
        }
    }

	$sql = "SELECT * FROM Courses WHERE ((Title LIKE {$search}) OR (Title LIKE {$search_upper})) AND
            `Courses`.type = '0' ORDER BY Title";
	$res = sql($sql);
	while ($row = sqlget($res)) {
        if (isset($used[$row['CID']])) continue; 
           $unused .= "<option value=\"{$row['CID']}\"> ".htmlspecialchars($row['Title'], ENT_QUOTES)."</option>";
	}
	return $unused;
}
?>