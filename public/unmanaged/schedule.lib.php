<?php
if (!function_exists('intvals')) {
	#
	# выполнить для переменных, записанных через пробел, функцию intval
	#
	function intvals($s) {
	   foreach (explode(" ",$s) as $v)
		  $GLOBALS[$v]=intval($GLOBALS[$v]);
	}
}
intvals("sheid");

require_once("metadata.lib.php");
require_once($GLOBALS['wwf']."/lib/classes/Schedule.class.php");
require_once($GLOBALS['wwf']."/lib/classes/WeekSchedule.class.php");
//require_once("formula_calc.php");

if (!isset($s['user']['sd']))
     $s['user']['sd']=true;
if (isset($_GET['sd']))
    $s['user']['sd']=(boolean) $_GET['sd'];
$sd=($s['user']['sd']) ? 7 : 1;
if (!isset($s['user']['sf']))
    $s['user']['sf']=false;
if (isset($_GET['sf']))
    $s['user']['sf']=(boolean) $_GET['sf'];


$login=$s['login'];
$mid=intval($s['mid']);
$teacher=$teach;
$tm="template/schedule"; // путь к шаблонам
// получить начало и конец недели из $tweek (unixtime)
if (!isset($tweek)) {
        $tweek=time();
}
$tweek=intval($tweek);
if ($tweek < 60*60*24*365) {
        $tweek=time();
}
$day=$tweek; // сегодняшний день
while(date("w",$tweek)!=1 && $s['user']['sd']) {
        $tweek-=11*60*60;
}

if ($teach || $dean || $admin)
        $cids=$s[tkurs];
else
        $cids=$s[skurs];
// tweek теперь день начала недели
switch( $time_interval ) { // в зависимости от того каокй период времени следует отображать
case "level":

foreach( $cids as $cid ){
        $start=getStartTime( $cid, $mid ); //
        $end=$start+getLongTime( $cid );
        // неверно определяется начало и конец из за сдвигов!!! сделать апгрейд базы и переписать функцию определеня сдвига
        // ищем раннее начало самого раннего курса
        if( $start < $tbegin || $tbegin==0 )
        $tbegin=$start;
        //  ищем поздний конец самого позднего курса
        if( $end > $tend || $tend==0 )
        $tend=$end;
}
//      echo "<H1>".date("d.m.y",($tbegin))." - ".date("d.m.y",$tend)."</H1>";

//      $tweeklast=mktime(12,0,0,date("m",$tweek),date("d",$tweek)-1,date("Y",$tweek));
//      $tweeknext=mktime(12,0,0,date("m",$tweek),date("d",$tweek)+$sd,date("Y",$tweek));

break;
case "week":
$tbegin=mktime(0,0,0,date("m",$tweek),date("d",$tweek),date("Y",$tweek)); // дата начала текущей при выводе недели
//   $tend=mktime(23,59,59,date("m",$tweek),date("d",$tweek)+6,date("Y",$tweek)); // дата конца текущей при выводе недели
$tend=mktime(23,59,59,date("m",$tweek),date("d",$tweek)+6,date("Y",$tweek));

$tweeklast=mktime(12,0,0,date("m",$tweek),date("d",$tweek)-1,date("Y",$tweek));
//   $tweeknext=mktime(12,0,0,date("m",$tweek),date("d",$tweek)+7,date("Y",$tweek));
$tweeknext=mktime(12,0,0,date("m",$tweek),date("d",$tweek)+$sd,date("Y",$tweek));
break;
case "month":
default:
$tbegin=mktime(0,0,0,date("m",$tweek),date("d",$tweek),date("Y",$tweek)); // дата начала текущей при выводе недели
$tend=mktime(23,59,59,date("m",$tweek),date("d",$tweek)+6,date("Y",$tweek));

$tweeklast=mktime(12,0,0,date("m",$tweek),date("d",$tweek)-1,date("Y",$tweek));
$tweeknext=mktime(12,0,0,date("m",$tweek),date("d",$tweek)+$sd,date("Y",$tweek));
}


function writeCurInfo( $mid, $ctime ){
        global $s;
        $cids = $titles = array();
        if( $mid > 0) {
                $courseFilter = new CCourseFilter($GLOBALS['COURSE_FILTERS']);
                $tmstamp = time();
                $person = array();
                switch ($s['perm']) {
                    case "1":
                        $sql=
                        "SELECT
                             CID,
                             MID,
                             ({$tmstamp} - UNIX_TIMESTAMP(time_registered)) as seconds_from_registration,
                             time_registered as time_registered
                        FROM Students
                        WHERE MID = '".(int) $mid."'
                        ";
                        $res = sql($sql);

                        while($row = sqlget($res)) {
                            $person[$row['CID']] = $row;
                        }

                        if (count($person)) {
                            $res=sql(
                            "SELECT
                                 Courses.Title,
                                 Courses.is_poll,
                                 Courses.CID as CID,
                                 Courses.longtime,
                                 UNIX_TIMESTAMP(Courses.cBegin) as begin,
                                 UNIX_TIMESTAMP(Courses.cEnd) as end
                             FROM Courses
                             WHERE Courses.CID IN ('".join("','", array_keys($person))."')
                             AND Courses.Status > 1
                             AND UNIX_TIMESTAMP(Courses.cBegin) <= {$tmstamp}
                             AND UNIX_TIMESTAMP(Courses.cEnd) >= {$tmstamp}
                             ORDER BY Courses.Title");
                        }

                        //$res=sql("SELECT Courses.Title, Courses.is_poll, Courses.CID as CID, ({$tmstamp} - UNIX_TIMESTAMP(Students.time_registered)) as seconds_from_registration, Courses.longtime, UNIX_TIMESTAMP(Courses.cBegin) as begin, UNIX_TIMESTAMP(Courses.cEnd) as end, Students.time_registered as time_registered FROM Students, Courses WHERE MID=$mid AND Courses.CID=Students.CID AND Courses.Status > 1 AND UNIX_TIMESTAMP(Courses.cBegin) <= {$tmstamp} AND UNIX_TIMESTAMP(Courses.cEnd) >= {$tmstamp} ORDER BY Courses.Title");

                    break;
                    case "2":

                        $sql=
                        "SELECT
                             CID
                        FROM Teachers
                        WHERE MID = '".(int) $mid."'
                        ";
                        $res = sql($sql);

                        while($row = sqlget($res)) {
                            $person[$row['CID']] = $row;
                        }

                        if (count($person)) {
                            $res=sql(
                            "SELECT
                                 Courses.Title,
                                 Courses.is_poll,
                                 Courses.CID as CID,
                                 UNIX_TIMESTAMP(Courses.cBegin) as begin,
                                 UNIX_TIMESTAMP(Courses.cEnd) as end
                             FROM Courses
                             WHERE
                             Courses.CID IN ('".join("','", array_keys($person))."')
                             AND Courses.Status > 0
                             AND UNIX_TIMESTAMP(Courses.cBegin) <= {$tmstamp}
                             AND UNIX_TIMESTAMP(Courses.cEnd) >= {$tmstamp}
                             ORDER BY Courses.Title" ,"ERR get teach courses ");
                        }

                        //$res=sql("SELECT Courses.Title, Courses.is_poll, Courses.CID as CID, UNIX_TIMESTAMP(Courses.cBegin) as begin, UNIX_TIMESTAMP(Courses.cEnd) as end FROM Teachers, Courses WHERE MID=$mid AND Courses.CID=Teachers.CID AND Courses.Status > 0 AND UNIX_TIMESTAMP(Courses.cBegin) <= {$tmstamp} AND UNIX_TIMESTAMP(Courses.cEnd) >= {$tmstamp} ORDER BY Courses.Title" ,"ERR get teach courses ");

                    break;
                    default:
                        $res=sql(
                        "SELECT
                            Courses.Title,
                            Courses.is_poll,
                            Courses.CID as CID,
                            UNIX_TIMESTAMP(Courses.cBegin) as begin,
                            UNIX_TIMESTAMP(Courses.cEnd) as end
                        FROM Courses
                        WHERE UNIX_TIMESTAMP(Courses.cBegin) <= {$tmstamp}
                        AND UNIX_TIMESTAMP(Courses.cEnd) >= {$tmstamp}
                        ORDER BY Courses.Title" ,"ERR get adm courses ");
                    break;
                }
                while( $r=sqlget($res) ){
                        if (!$courseFilter->is_filtered($r['CID'])) continue;
                        if (!in_array($r['CID'],$cids)) {
                            if (isset($person[$r['CID']])) {
                                $r['seconds_from_registration'] = $person[$r['CID']]['seconds_from_registration'];
                                $r['time_registered']           = $person[$r['CID']]['time_registered'];
                            }
                            $cids[]=$r['CID'];
                            //$titles[$r['CID']] = htmlspecialchars($r['Title'],ENT_QUOTES);
                            $titles[$r['CID']] = $r['Title'];
                            $days_from_registration[$r['CID']] = sprintf(_("%d-й"), max(floor($r['seconds_from_registration']/86400 + 1), 1));
                            $days_till_end[$r['CID']] = ($r['longtime'] - $days_from_registration[$r['CID']]);
                            $is_poll[$r['CID']] = $r['is_poll'];
                            $fn_tmp = $r['time_registered'];
                            $arrPeriod[$r['CID']] = array('begin' => $r['begin'], 'end' => $r['end']);//getStartEndTimestamp( $r['CID'] );
                        }
                    }
                if( count ($cids) > 0 ) {
                    	if ($s['perm'] == 1) {
                			$tmp="<table width=100% class=main cellspacing=0><TR><TH>"._("дисциплина")."</TH><TH>"._("мой день занятий")."</TH><TH>"._("осталось дней")."</TH></TR>";
                    	} else {
                			$tmp="<table width=100% class=main cellspacing=0><TR><TH>"._("дисциплина")."</TH><TH>"._("день занятий")."</TH><TH>"._("осталось дней")."</TH></TR>";
                    	}
                        foreach( $cids as $cid ){
                                $curday = sprintf(_("%d-й"), intval(($ctime - $arrPeriod[$cid]['begin'])/(24*60*60)) + 1);
                                $days = (intval(($arrPeriod[$cid]['end'] - $ctime)/(24*60*60))+1);
                                if ($days < 0 || $curday < 0) {
                                        $days = "";
                                        $curday = "";
                                }
                                $title = $titles[$cid];//cid2title( $cid );
                                if ($s['perm'] == 1) {
                                	$curday = $days_from_registration[$cid];
                                	if ($is_poll[$cid]){
                                		$days = '-';
                                	} elseif ($days_till_end[$cid] > 0){
	                                	$days = $days_till_end[$cid];
	                                	$title = "<a href='{$GLOBALS['sitepath']}course_structure.php?CID={$cid}&page_id=m13{$cid}'>$title</a>";
                                	} else {
                                		$days = _('время обучения по курсу закончилось');
                                	}
                                } else {
                                	$title = "<a href='{$GLOBALS['sitepath']}course_structure.php?CID={$cid}&page_id=m13{$cid}'>$title</a>";
                                }

                               	$tmp.="<TR><TD>{$title}</TD><TD>$curday</TD><TD>$days</TD></TR>";
                        }
                        $tmp.="</TABLE>";
                }
        }
        return( $tmp );
}

function getSheItems( $cid ){
        $t=mktime(0,0,0,0,0,80);
        $d1=date( "Y-m-d H:i:s", mktime(0,0,0,0,0,01) );
        $d2=date( "Y-m-d H:i:s",time());
        $rq="SELECT * FROM  schedule
       WHERE CID=$cid
         AND ( end >= FROM_UNIXTIME( 0 ) AND begin <= FROM_UNIXTIME( $t ) )
                OR
              ( end >= '$d1' AND begin <= '$d2' )
       ";
        $res=sql($rq, "ERR");
        //  date yyyy-mm-dd hh:mm:ss:mm

        $st=date( "Y-m-d H:i:s", $t );

        $tmp="<B>$t:$st</B><table>";
        while( $r=sqlget($res) ){
                $sh[$i][title]=$r[title];
                $sh[$i][begin]=$r[begin];
                $sh[$i][end]=$r[end];
                $i++ ;
                $tmp.="<TR><TD>".$r[title]."</TD><TD>".$r[begin]."</TD><TD>".$r[end]."</TD></TR>";
        }
        $tmp.="</table>";
        sqlfree( $res );
        return( $tmp );
}

function getStartTime( $cid, $mid ){
        // дает время начала курса для человека
        $res=sql("SELECT * FROM Students WHERE CID=$cid AND MID=$mid","ERR get start time ");
        $r=sqlget($res);
        //  echo "REG=".date("d.m.y",$r['Registered'])."<BR>";
        return( intval($r['Registered']) );
}

function getStartEndTimestamp( $cid ){
        // дает время начала курса для человека
        $res=sql("SELECT UNIX_TIMESTAMP(cBegin) as begin, UNIX_TIMESTAMP(cEnd) as end FROM Courses WHERE CID=$cid","ERR get start time ");
        $r=sqlget($res);
        return $r;
}


function getLongTime( $cid ){
        // дает продолжительность курса
        $res=sql("
    SELECT UNIX_TIMESTAMP(cBegin) as begin, UNIX_TIMESTAMP(cEnd) as end
      FROM Courses
      WHERE CID=$cid","ERR get start time 1 ");
        $r=sqlget($res);

        return( intval($r['end'])-intval($r['begin'])  );
}

function writeCond( $s, $cond , $mark){
        if ($mark=="") $mark=1;
        $ss="<input type=\'hidden\' name=\'".$s."\' mark=\'$mark\' value=\'$cond\'>";
        return($ss);
}

function getCond( $desc,  &$s_cond, &$val, &$text  ){ // выбирает из поля description условие
if ( $desc !="" ){
        ereg("<input type=hidden name=([[:print:]]+) mark=([[:print:]]+) value=([[:print:]]+)>([[:print:]]*)",$desc,$Pockets);
        $s_cond=$Pockets[1];
        $sheid=$Pockets[3]; // условие из value
        $val=$Pockets[2];
        $text =$Pockets[4];
}
else
$sheid= -1; //"УСЛОВИЕ";


return $sheid;
}

function checkCond( $cond, $limit ){
        global $mid;
        // проверяет условие наличия оценки по занятию $sheid на выполнимость для студента $mid
        // возвращает оценку
        $sheid=$cond;
        $res= -1; // нет условия
        if ( intval($sheid) > 0 ){
                $sql="SELECT scheduleID.V_STATUS as mark,
                   scheduleID.SHEID as SSHEID,
                   schedule.sheid as sheid
              FROM scheduleID, schedule
              WHERE scheduleID.SHEID=$sheid
                     AND scheduleID.MID=$mid
                    AND schedule.sheid=$sheid";
                $res  = sql($sql,"wertw");
                $r = sqlget($res);
                $res=intval($r['mark']);
                //      echo "ВЫСТВНЛЕНО $res А НАДО НЕ МЕННЕЕ $limit";
                if ( $res < $limit ) $res=0;
        }
        return($res); // -1 нет условия 0- условие невыполняется >0 - выставленная оценка
}

function get_all( &$html, $shids, $teach, $tr, $htmldel, $htmlmod) {
        // формирует массив всех занятий за период $tbegin, $tend на каждую дату

        global $PHP_SELF, $tbegin, $tend, $rbegin, $rend, $s, $day, $sf, $tm;

        $tmp="";
        $tmp2="";
        //   $tbegin-=365*24*60*60;       //
        //            AND end<=FROM_UNIXTIME($tend)
        //            AND begin>=FROM_UNIXTIME($tbegin)

        $rq="SELECT schedule.SHEID as sheid,
             schedule.title, weekday(begin) as weekday,
             UNIX_TIMESTAMP(begin) as begin, UNIX_TIMESTAMP(end) as end,
             descript, schedule.startday as startday, schedule.stopday as stopday, schedule.timetype as timetype,
             Courses.Title as course, schedule.createID as createID,
             EventTools.Icon as icon, EventTools.TypeName as tn
      FROM  schedule, Teachers, Courses, EventTools
      WHERE
            Courses.Status>0
            AND EventTools.TypeID=schedule.typeID
            AND Courses.CID=schedule.CID
            AND ( ( end >= FROM_UNIXTIME( $tbegin ) AND begin <= FROM_UNIXTIME( $tend ) )
                   OR ";
        // анализируем на относительное время
        foreach ( $s[skurs] as $v )             // rbegin - массив свигов данной даты относительно начал курса
        $rq.="( schedule.stopday >= '".$rbegin[$v]."'  AND schedule.startday <= '".$rend[$v]."'  ) \n OR ";

        $rq=substr($rq, 0, -4);
        //                                                 $rbegin[$v]=$tbegin-Registered
        //fn$rq.=" ) GROUP BY schedule.SHEID";
        $rq.=" )";
        //            GROUP BY sheid ";
        //schedule.SHEID IN (".implode(",",$shids).") AND

        $res=sql($rq,"err get all");
        //    $ret=count()

        //      echo "<H1>".date("d.m.y",($tbegin))." - ".date("d.m.y",$tend)."</H1>";
        $j=0;
        while ( $r = sqlget($res) ) {
                $j++;
                //      echo $r[title]."<BR>";
                /////
                $s_cond=""; $desc="";
                $cond=getCond($r['descript'], $s_cond, $val, $desc );
                $ii =checkCond( $cond, $val ); // проверить условие для данного человека
                //      echo "ii=$ii<BR>";
                if ( $teach || $ii!=0 ){
                        if ( intval($teach)== 1 ){
                                if ( $ii == -1 ){ // т.е. условие есть
                                $desc=str_replace("\"","'",$r['descript']);
                                }else{
                                        $desc=str_replace("\"","'",$r['descript']." $desc<BR><I>"._("ЕСЛИ")." <B>$s_cond</B> "._("сдан на")." $val</I>");
                                }
                        }else{
                                if ( $ii > 0 ) // т.е. условие есть
                                $desc=str_replace("\"","'",$r['descript']." $desc<BR><I>"._("ЕСЛИ")." <B>$s_cond</B> "._("сдан на")." $val</I>");

                                if ($ii== -1)
                                $desc=str_replace("\"","'",$r['descript']);
                        }
                        if( $r['timetype'] ){
                                $desc.="<b> c ".intval($r['startday']/(24*60*60)+1)._("-го");
                                $desc.=" по ".intval($r['stopday']/(24*60*60)+1)._("-й день занятий")."</b>";
                        }

                        //      $week=$r[weekday]+1;
                        $date=date("d.m.y",$r[begin]);

                        $linfo=($r['descript'] && !$sf) ? gf("$tm-1hand_off.html") : gf("$tm-1hand_on.html");
                        $linfo=str_replace('[teach_notes]',$desc,$linfo);
                        if ($teach) {
                                $tmp=str_replace(array('[self]','[sheid]','[tweek]'),array($PHP_SELF,$r[sheid],$day),$htmldel);
                                $tmp2=str_replace(array('[self]','[sheid]','[asess]'),array($PHP_SELF,$r[sheid],$GLOBALS[asess]),$htmlmod);
                        }

                        if( $r[unix_begin] < 0 ) $r[unix_begin]=0;
                        $html[$date].=str_replace(
                        array('[title]','[time]','[t_info]','[url]','[sheid]','[delete]','[modify]','[course]','[reminder]','[u_info]','[icon]','[type]','[us_info]','[low_info]'),

                        array( $r[title], date("H:i",$r[unix_begin]).'-'.date("H:i",$r[unix_end] ),

                        "$desc",

                        "[PATH]schedule.php4?c=go&sheid=$r[sheid]$GLOBALS[asess]", $r[sheid], $tmp, $tmp2,$r['course'],"",
                        getName($r['createID']),$r['icon'],$r['tn'],"",$linfo
                        ),
                        $tr);
                }
        } // while
        return( $j );
} // function

function once( &$html, $shids, $teach, $tr, $htmldel, $htmlmod) {
        // формирует массив на каждый день неедли
        global $PHP_SELF, $tbegin, $tend, $s,$day, $sf, $tm;
        $tmp="";
        $tmp2="";
        $rq="SELECT schedule.SHEID as sheid,
               schedule.title,
               weekday(begin) as weekday,
               descript,
               Courses.Title as course,
               schedule.createID as createID,
               UNIX_TIMESTAMP(schedule.begin) as unix_begin,
               UNIX_TIMESTAMP(schedule.end) as unix_end,
               EventTools.Icon as icon,
               EventTools.TypeName as tn,
               schedule.isgroup,
               scheduleID.toolParams as toolParams
        FROM  schedule, Teachers, Courses, EventTools, scheduleID
        WHERE schedule.SHEID IN (".implode(",",$shids).")
        AND Courses.Status>0
        AND EventTools.TypeID=schedule.typeID
        AND Courses.CID=schedule.CID
        AND GREATEST(UNIX_TIMESTAMP(schedule.begin),$tbegin) <= LEAST(UNIX_TIMESTAMP(schedule.end), $tend)
        AND schedule.SHEID = scheduleID.SHEID";
        $res=sql($rq,"errOnce");
        while ( $r = sqlget($res) ) {
                for($week = 1; $week <= 7; $week++) {
                        $tbegin_day = $tbegin + 86400*($week - 1);
                        $tend_day = $tbegin + 86400*($week) - 1;
                        if(max($tbegin_day, $r['unix_begin']) > min($tend_day, $r['unix_end'])) {
                                continue;
                        }
                        //условие того, что
                        $s_cond = ""; $desc = "";
                        $strTarget = "";
                        if (strpos($r['toolParams'], "module") !== false) {
                                $strTarget = "";
                        }
                        if (strpos($r['toolParams'], "tests") !== false) {
                                if($s['perm'] == 2) {
                                        $strTarget = "target='_blank'";
                                }
                                else {
                                        $strTarget = "";
                                }
                        }

                        $cond = getCond($r['descript'], $s_cond, $val, $desc );
                        $ii = checkCond( $cond, $val ); // проверить условие для данного человека
                        if ( $teach || $ii != 0 ) {
                                if ( intval($teach) == 1 ){
                                        if ( $ii == -1 ){ // т.е. условие есть
                                        $desc=str_replace("\"","'",$r['descript']);
                                        }
                                        else {
                                                $desc=str_replace("\"","'",$r['descript']." $desc<BR><I>"._("ЕСЛИ")." <B>$s_cond</B> "._("сдан на")." $val</I>");
                                        }
                                }
                                else {
                                        if ( $ii > 0 ) // т.е. условие есть
                                        $desc=str_replace("\"","'",$r['descript']." $desc<BR><I>"._("ЕСЛИ")." <B>$s_cond</B> "._("сдан на")." $val</I>");
                                        if ($ii== -1)
                                        $desc=str_replace("\"","'",$r['descript']);
                                }
                                $linfo=($r['descript'] && !$sf) ? gf("$tm-1hand_off.html") : gf("$tm-1hand_on.html");
                                $linfo=str_replace('[teach_notes]',$desc,$linfo);
                                if (check_teachers_permissions(20, $s[mid])) {
                                        $tmp=str_replace(array('[self]','[sheid]','[tweek]'),array($PHP_SELF,$r[sheid],$day),$htmldel);
                                        $tmp2=str_replace(array('[self]','[sheid]','[asess]'),array($PHP_SELF,$r[sheid],$GLOBALS[asess]),$htmlmod);
                                }
                                if( $r[begin] < 0 ) $r[begin]=0;
                                $strIsGroup = ($r['isgroup']) ? _("групповое занятие") : "";
                                $boolShowFrames = ($teach) ? MODE_SHOW_NOFRAMES : MODE_SHOW_FRAMES;

                                $is_showed_print_button = is_showed_print_button($r['sheid']);


                                $html[$week].=str_replace(
                                array('[title]','[time]','[t_info]','[url]','[sheid]','[delete]','[modify]','[course]', '[isgroup]', '[reminder]','[u_info]','[icon]','[type]','[us_info]','[low_info]','[target]','[PRINT_BUTTON]'),
                                array( $r[title], date("H:i",$r[unix_begin]).'-'.date("H:i",$r[unix_end] ),
                                "$desc",
                                "[PATH]schedule.php4?c=go&mode_frames=".$boolShowFrames."&sheid=$r[sheid]$GLOBALS[asess]", $r[sheid], $tmp, $tmp2,$r['course'],$strIsGroup,"",
                                getName($r['createID']),$r['icon'],$r['tn'],"",$linfo,$strTarget,$is_showed_print_button?getIcon("print"):""
                                ),$tr);
                        }
                }//for
        } // while
        return( $ii );
} // function

function eday(&$html,$shids,$teach,$tr,$htmldel,$htmlmod) {
        global $PHP_SELF, $tbegin, $tend, $s,$day, $sf, $tm;
        $tmp="";
        $tmp2="";
        $rq="
      SELECT schedule.SHEID as sheid,
             schedule.title, weekday(begin) as weekday,
             weekday(end) as eweekday,
             UNIX_TIMESTAMP(begin) as begin, UNIX_TIMESTAMP(end) as end,
             descript, Courses.Title as course, schedule.createID as createID,
             EventTools.Icon as icon, EventTools.TypeName as tn
      FROM  schedule, Teachers, Courses, EventTools
      WHERE schedule.SHEID IN (".implode(",",$shids).")
            AND Courses.Status>0 AND
            EventTools.TypeID=schedule.typeID AND
            Courses.CID=schedule.CID  AND
            end>=FROM_UNIXTIME($tbegin) AND
            begin<=FROM_UNIXTIME($tend)
            ";//fn GROUP BY schedule.SHEID ";
        $res=sql($rq,"errEday");
        while ($r=sqlget($res)) {
                $desc=str_replace("\"","'",$r['descript']);
                if ($r['begin']<$tbegin)
                        $sweek=1;
                else
                        $sweek=$r[weekday]+1;
                if ($r['end']>$tend)
                        $eweek=7;
                else
                        $eweek=$r[eweekday]+1;
                $linfo=($r['descript'] && !$sf) ? gf("$tm-1hand_off.html") : gf("$tm-1hand_on.html");
                $linfo=str_replace('[teach_notes]',$desc,$linfo);
                if (check_teachers_permissions(20, $s[mid])) {
                        $tmp=str_replace(array('[self]','[sheid]','[tweek]'),array($PHP_SELF,$r[sheid],$day),$htmldel);
                        $tmp2=str_replace(array('[self]','[sheid]'),array($PHP_SELF,$r[sheid]),$htmlmod);
                }
                for($week=$sweek;$week<=$eweek;$week++) {
                        $boolShowFrames = ($teach) ? MODE_SHOW_NOFRAMES : MODE_SHOW_FRAMES;
                        $is_showed_print_button = is_showed_print_button($r['sheid']);
                        $html[$week].=str_replace(
                        array('[title]','[time]','[t_info]','[url]','[sheid]','[delete]','[modify]','[course]','[isgroup]','[reminder]','[u_info]','[icon]','[type]','[us_info]','[low_info]','[PRINT_BUTTON]'),
                        array($r[title],date("H:i",$r[unix_begin]).'-'.date("H:i",$r[unix_end])."<br>"._("ежедневно"),
                        "$desc",
                        "schedule.php4?c=go&mode_frames={$boolShowFrames}&sheid=$r[sheid]", $r[sheid], $tmp, $tmp2,$r['course'],$strIsGroup,"",getName($r['createID']),$r['icon'],$r['tn'],"",$linfo,
                        $is_showed_print_button?getIcon("print"):""),
                        $tr);
                }
        }
}


function eweek(&$html,$shids,$teach,$tr,$htmldel,$htmlmod,$ws) {
        global $PHP_SELF, $tbegin, $tend, $s,$day,$sf, $tm;
        $tmp="";
        $tmp2="";         //
        $d_week=60*60*24; // продолжительность дня
        $d_tbegin = $tbegin/$d_week;
        if ($ws) $stype=_("еженедельно");
        else $stype=_("через неделю");
        $rq="SELECT schedule.SHEID as sheid,
             schedule.title, weekday(begin) as weekday,
             UNIX_TIMESTAMP(begin) as begin, UNIX_TIMESTAMP(end) as end,
             descript, Courses.Title as course, schedule.createID as createID,
             EventTools.Icon as icon, EventTools.TypeName as tn
      FROM  schedule, Teachers, Courses, EventTools
      WHERE schedule.SHEID IN (".implode(",",$shids).")
            AND Courses.Status>0 AND
            EventTools.TypeID=schedule.typeID AND
            Courses.CID=schedule.CID  AND
            end>=FROM_UNIXTIME($tbegin) AND
            begin<=FROM_UNIXTIME($tend)
            ";//fn GROUP BY schedule.SHEID ";
        $res=sql($rq,"errEWeek1");
        while ( $r=sqlget($res) ) {
                $ttt = $d_tbegin +$r[weekday] - $r[begin]/$d_week;
                $ttt = $ttt/7;     // кол-во недель от начала отсчета
                //тут надо зять остаток от деления ttt на 2 от номера недели чтоб узнать четная она или нечетная
                // можно взять последний бит в двоичной системе
                $ss=substr(decbin($ttt), strlen(decbin($ttt))-1, 1);
                // ws  1 - если каждую неделю,  0 - если через неделю
                if(  $ws || (intval($ss)==0) ) // либо огда неделя нечетная по отношению к дате первого указанного занятия
                {  // если через неделю
                $desc=str_replace("\"","'",$r['descript']);
                $week=$r[weekday]+1;
                $linfo=($r['descript'] && !$sf) ? gf("$tm-1hand_off.html") : gf("$tm-1hand_on.html");
                $linfo=str_replace('[teach_notes]',$desc,$linfo);
                if (check_teachers_permissions(20, $s[mid])) {
                        $tmp=str_replace(array('[self]','[sheid]','[tweek]'),array($PHP_SELF,$r[sheid],$day),$htmldel);
                        $tmp2=str_replace(array('[self]','[sheid]'),array($PHP_SELF,$r[sheid]),$htmlmod);
                }
                $boolShowFrames = ($teach) ? MODE_SHOW_NOFRAMES : MODE_SHOW_FRAMES;
                $html[$week].=str_replace(
                array('[title]','[time]','[t_info]','[url]','[sheid]','[delete]','[modify]',
                '[course]','[isgroup]','[reminder]','[u_info]','[icon]','[type]','[us_info]','[low_info]','[PRINT_BUTTON]'),
                array($r[title],
                date("H:i",$r[unix_begin]).'-'.date("H:i",$r[unix_end])."<br>".$stype." [$ttt]",
                "$desc",
                "schedule.php4?c=go&mode_frames={$boolShowFrames}&sheid=$r[sheid]",
                $r[sheid], $tmp, $tmp2,$r['course'],$strIsGroup,"",
                getName($r['createID']),$r['icon'],$r['tn'],"",$linfo ),
                $tr);
                } // end if
        }//end while


}// end function

function emonth(&$html,$shids,$teach,$tr,$htmldel,$htmlmod) {
        global $PHP_SELF, $tbegin, $tend, $s,$day,$sd,$tweek,$sf, $tm;
        $tmp="";
        $tmp2="";


        $rq="SELECT schedule.SHEID as sheid,
             schedule.title, weekday(begin) as weekday,
             UNIX_TIMESTAMP(begin) as begin, UNIX_TIMESTAMP(end) as end,
             descript, Courses.Title as course, schedule.createID as createID,
             EventTools.Icon as icon, EventTools.TypeName as tn
      FROM  schedule, Teachers, Courses, EventTools
      WHERE schedule.SHEID IN (".implode(",",$shids).")
            AND Courses.Status>0 AND
            EventTools.TypeID=schedule.typeID AND
            Courses.CID=schedule.CID  AND
            end>=FROM_UNIXTIME($tbegin) AND
            begin<=FROM_UNIXTIME($tend)
            ";//fn GROUP BY schedule.SHEID ";
        $res=sql($rq,"errOnce");

        while ($r=sqlget($res)) {
                $week=0;
                $desc=str_replace("\"","'",$r['descript']);
                if (date("W",$day)==date("W",$r['begin'])) $week=$r[weekday]+1;
                elseif($r['end']>$tend) {
                        $dbegin=date("d",$r['begin']);
                        if (date("m",$tweek)=='02' && $dbegin>28) $dbegin=date("t",$tweek);
                        if ($dbegin>30) $dbegin=date("t",$tweek);
                        $dbegin;
                        $j=date("w",$tweek);
                        for ($i=$j; $i<$j+$sd; $i++) {
                                $td=mktime(12,0,0,date("m",$tweek),date("d",$tweek)+($i-1),date("Y",$tweek));
                                if (date("d",$td)==$dbegin) {
                                        $week=date("w",$td) ;
                                        if ($week==0) $week=7 ;
                                }
                        }

                }
                $linfo=($r['descript'] && !$sf) ? gf("$tm-1hand_off.html") : gf("$tm-1hand_on.html");
                $linfo=str_replace('[teach_notes]',$desc,$linfo);
                if (check_teachers_permissions(20,$s[mid])) {
                        $tmp=str_replace(array('[self]','[sheid]','[tweek]'),array($PHP_SELF,$r[sheid],$day),$htmldel);
                        $tmp2=str_replace(array('[self]','[sheid]'),array($PHP_SELF,$r[sheid]),$htmlmod);
                }
                $boolShowFrames = ($teach) ? MODE_SHOW_NOFRAMES : MODE_SHOW_FRAMES;
                $is_showed_print_button = is_showed_print_button($r['sheid']);
                $html[$week].=str_replace(
                array('[title]','[time]','[t_info]','[url]','[sheid]','[delete]','[modify]','[course]','[isgroup]','[reminder]','[u_info]','[icon]','[type]','[us_info]','[low_info]','[PRINT_BUTTON]'),
                array($r[title],date("H:i",$r[unix_begin]).'-'.date("H:i",$r[unix_end])."<br>"._("ежемесячно"),
                "$desc",
                "schedule.php4?c=go&mode_frames{$boolShowFrames}&sheid=$r[sheid]", $r[sheid], $tmp, $tmp2,$r['course'],$strIsGroup,"",getName($r['createID']),$r['icon'],$r['tn'],"",$linfo,$is_showed_print_button?getIcon("print"):""
                ),
                $tr);
        }

}


/**
 *
 *
 * @param int $marked 1-без оценок, 2-с оценками, 3-всёговно
 */
function schedule($eleIndex=0, $cid=0, $marked=3) {
        // выводит расписание в зависимости от режима и т.п.
        global $tm, $tbegin, $tend, $rbegin, $rend, $tweek, $mid, $nameweek, $day, $sess, $self, $sd;
        global $stud,$teach,$dean,$admin,$s,$cids;
        $sf=($s['user']['sf']) ? "1" : "0";
        if( $eleIndex==1 ) $sf=1; // полный показ
        if( $eleIndex==-1 ) $sf=1;
        $she_html=show_tb(1);
        if (empty($cids) && !$eleIndex) login_error();
        $table=gf("$tm-1table.html");
        if(check_teachers_permissions(20,$s[mid]) || ($s['perm']>=2)) {
                $tredit=gf("$tm-1tr-edit.html");
        }
        else {
                $tredit = "";
        }
        $main=gf("$tm-1main.html");
        $html=array();
        $html2="";
        $words=array();
        $she_words=loadwords("schedule-words.html");

        if ($GLOBALS['controller']->enabled) {
            $she_head=gf("$tm-static.html");
        } else
        $she_head=ph($she_words[0],gf("$tm-static.html"));

        $words["title"]=$she_words[1];
        $words["time"]=$she_words[2];
        $words["notes"]=$she_words[3];
        $words["startweek"]=$she_words[4];
        $words["edit"]=$she_words[5];
        $words["add"]=$she_words[6];
        $words["delete"]=$she_words[7];
        $words["course"]=$she_words[12];
        $words["lesstype"]=$she_words[13];
        $words["showfull"]=($sf) ? $she_words[11] : $she_words[10];
        $words["SF"]=($sf) ? 0 : 1;
        $words["SFICO"]=($sf) ? 6 : 4;

        switch( $eleIndex ){
                case 0:
                case -1:
                $main=str_replace("[SHE-HEADER]",$she_head,$main);
                break;
                case 1:
                $main=str_replace("[SHE-HEADER]","",$main);
                break;

        }

        if($eleIndex == 0) {
                $week_schedule = new WeekSchedule;
                switch($marked) {
                    case 1:
                        $bad_sheids = get_marked_sheids($GLOBALS['s']['mid']);
                    break;
                    case 2:
                        $bad_sheids = get_unmarked_sheids($GLOBALS['s']['mid']);
                    break;
                    default:
                        $bad_sheids = array();
                    break;
                }
                $week_schedule->set_bad_sheids($bad_sheids);

                $week_schedule->init_by_begin_week(date("Y-m-d", $tweek));


                if($s['perm'] >= 2)
                {
                    $is_edited = (check_teachers_permissions(20, $s['mid']) || ($s['perm']>=2));
                    if(isset($s["tkurs"]) && is_array($s["tkurs"])) {
                        if ($cid) {
                            if (in_array($cid,$s['tkurs'])) {
                                $week_schedule->set_cids(array($cid));
                            }
                        } else {
                            $week_schedule->set_cids($s["tkurs"]);
                        }
                    }
                }
                elseif($s['perm'] == 1)
                {
                    $is_edited = 0;
                    if(isset($s["skurs"]) && is_array($s["skurs"])) {
                        if ($cid) {
                            if (in_array($cid,$s['skurs'])) {
                                $week_schedule->set_cids(array($cid));
                            }
                        } else {
                            $week_schedule->set_cids($s["skurs"]);
                        }
                    }
                }

                $week_schedule_for_smarty = $week_schedule->get_as_array();
                $smarty_tpl = new Smarty_els;
                $smarty_tpl->assign("begin_day", $week_schedule->begin_week);
                $smarty_tpl->assign("end_day", $week_schedule->end_week);
                $smarty_tpl->assign("week_schedule", $week_schedule_for_smarty);

                $smarty_tpl->assign("tweek", $tweek);
                $smarty_tpl->assign("del_icon", getIcon("delete"));
                $smarty_tpl->assign("edit_icon", getIcon("edit"));
                $smarty_tpl->assign("open_icon", getIcon("open"));

                /**
                * Check permissions (options)
                */
                $add_permission = ($GLOBALS['controller']->checkPermission(SHEDULE_PERM_EDIT_OWN)
                             || $GLOBALS['controller']->checkPermission(SHEDULE_PERM_EDIT_OTHERS));

                $smarty_tpl->assign("add_permission", $add_permission);
                $smarty_tpl->assign("is_edited", $is_edited);
                $smarty_tpl->assign("with_notes", $sf);
                $smarty_tpl->assign("path_corner03", $GLOBALS['controller']->enabled ? $GLOBALS['controller']->view_root->skin_url . "/": $sitepath);
                $smarty_tpl->assign('cid',(int) $cid);
                $html2 = $smarty_tpl->fetch("week_schedule.tpl");
                if( $eleIndex == -1 ){
                        $main=str_replace(
                                array("[main]","[link1]","[link2]","[time1]","[time2]","[self]","[sess]","[sf]"),
                                array($html2,"","",date("d.m.Y",$tbegin),date("d.m.Y",$tend  ),$self,$sess,$sf),$main);
                }
                else {
                        $main=str_replace(
                                array("[main]","[link1]","[link2]","[time1]","[time2]","[self]","[sess]","[sf]"),
                                array($html2,"$PHP_SELF?tweek=".($GLOBALS[tweeklast])."&CID={$cid}&MARKED={$marked}","$PHP_SELF?tweek=".($GLOBALS[tweeknext])."&CID={$cid}&MARKED={$marked}",date("d.m.Y",$GLOBALS[tweeklast]),date("d.m.Y",$GLOBALS[tweeknext]),$self,$sess,$sf),$main);
                }
                $main=words_parse($main,$words);
                $she_html = str_replace("[ALL-CONTENT]",$main,$she_html);
                $she_html = str_replace("[PRINT_WEEK_SCHEDULE]", getIcon("print"), $she_html);
                $week_str = date("Y-m-d", $tweek);
                $she_html = str_replace("[week]", $week_str, $she_html);

                $GLOBALS['controller']->setLink('m190101', array($week_str));

                if ($GLOBALS['controller']->enabled) {
                    $she_html=words_parse($she_html,$words);
                    $she_html=path_sess_parse($she_html);
                    $GLOBALS['controller']->captureFromReturn(CONTENT,$she_html);
                }
                printtmpl($she_html);
        }
        else {
                $cur_unixtime = time();
                $cur_date = date("d.m.Y", $cur_unixtime);

                switch($marked) {
                    case 1:
                        $bad_sheids = get_marked_sheids($GLOBALS['s']['mid']);
                    break;
                    case 2:
                        $bad_sheids = get_unmarked_sheids($GLOBALS['s']['mid']);
                    break;
                    default:
                        $bad_sheids = array();
                    break;
                }

                $week_schedule = new WeekSchedule;
                $week_schedule->set_bad_sheids($bad_sheids);
                $week_schedule->init_by_begin_week(date("Y-m-d", $tweek));
                if($s['perm'] >= 2)
                {
                    $is_edited = check_teachers_permissions(20, $s['mid']);
                    if(isset($s["tkurs"]) && is_array($s["tkurs"]))
                        $week_schedule->set_cids($s["tkurs"]);
                }
                elseif($s['perm'] == 1)
                {
                    $is_edited = 0;
                    if(isset($s["skurs"]) && is_array($s["skurs"]))
                        $week_schedule->set_cids($s["skurs"]);
                }

                $week_schedule_for_smarty = $week_schedule->get_as_array_for_day($cur_date);

                $smarty_tpl = new Smarty_els;

                $add_permission = ($GLOBALS['controller']->checkPermission(SHEDULE_PERM_EDIT_OWN)
                             || $GLOBALS['controller']->checkPermission(SHEDULE_PERM_EDIT_OTHERS));

                $smarty_tpl->assign("add_permission", $add_permission);

                $smarty_tpl->assign("begin_day", $week_schedule->begin_week);
                $smarty_tpl->assign("end_day", $week_schedule->end_week);
                $smarty_tpl->assign("week_schedule", $week_schedule_for_smarty);
                $smarty_tpl->assign("tweek", $cur_unixtime);
                $smarty_tpl->assign("is_edited", $is_edited);
                $smarty_tpl->assign("open_icon", getIcon("open"));
                $smarty_tpl->assign("del_icon", getIcon("delete"));
                $smarty_tpl->assign("edit_icon", getIcon("edit"));
                $smarty_tpl->assign("with_notes", $sf);
                $smarty_tpl->assign("path_corner03", $GLOBALS['controller']->enabled ? $GLOBALS['controller']->view_root->skin_url . "/": $sitepath);
                return $smarty_tpl->fetch("week_schedule.tpl");
        }
}

function getparts($id,$names) {
        // формирует XML с параметрами текущего инструмента, записывая из в аттрибуты
        global $partparams;
        $s="<?xml version='1.0' encoding='ISO-8859-1'?>\n<eventType ID='$id'>\n";

        foreach ($names as $v) {
                $v=trim($v);
                $s.="<$v>\n";
                /*      if (!isset($partparams[$v]))
                exit("getparams(\"$id\",[array]) error: value \$partparams[$v] not set!");
                */        if (is_array($partparams[$v])) {
                foreach ($partparams[$v] as $vv) {
                        $s.="<attribute name='$vv'/>\n";
                }
                }
                $s.="</$v>\n";
        }

        $s.="</eventType>\n";
        return $s;
}

function schedule_go($sheid)  {
        global $mid,$teacher,$login,$javapass,$dean, $sitepath;
        global $s;
        global $adodb;
        $teacher = ($_SESSION['s']['perm']==2);
        $dean = ($_SESSION['s']['perm']==3);
        $current_unix_time = time();
        $current_datetime = date("Y-m-d H:i");
        $sheid=intval($sheid);
        if ($_SESSION['s']['perm']==3) {
                $sql="
         SELECT schedule.CID, scheduleID.toolParams, schedule.SHEID,
                schedule.title as schetitle, weekday(schedule.begin) as weekday,
                schedule.descript  as descript,
                schedule.typeID, EventTools.tools, EventTools.XSL,
                EventTools.TypeName, UNIX_TIMESTAMP(schedule.begin) as begin,
                UNIX_TIMESTAMP(end) as end, descript,
                Courses.Title as ctitle
         FROM   EventTools, Teachers, Courses, schedule
               INNER JOIN
                      scheduleID ON (schedule.SHEID = scheduleID.SHEID)
         WHERE
                schedule.SHEID=$sheid AND
                Courses.Status>0 AND
                Courses.CID=schedule.CID AND
                schedule.typeID=EventTools.TypeID";
        } elseif (($_SESSION['s']['perm']==2) && $s['old_mid']) {
                $sql="
         SELECT schedule.CID, scheduleID.toolParams, schedule.SHEID,
                schedule.title as schetitle, weekday(schedule.begin) as weekday,
                schedule.descript  as descript,
                schedule.typeID, EventTools.tools, EventTools.XSL,
                EventTools.TypeName, UNIX_TIMESTAMP(schedule.begin) as begin,
                UNIX_TIMESTAMP(schedule.end) as end, descript,
                Courses.Title as ctitle
         FROM   EventTools, Teachers, Courses, schedule
         INNER JOIN
                scheduleID ON (schedule.SHEID = scheduleID.SHEID)
         WHERE
                schedule.SHEID=$sheid AND
                Teachers.mid=".(int) $s['old_mid']." AND
                Teachers.CID=schedule.CID AND
                Courses.Status>0 AND
                Courses.CID=schedule.CID AND
                schedule.typeID=EventTools.TypeID AND
                scheduleID.mid IS NOT NULL
      ";
        }
// если преподаватель проходит занятие от имени слушателя - тоже разрешать в люботе время
        elseif ($_SESSION['s']['perm']==2) {
                $sql="
         SELECT schedule.CID, scheduleID.toolParams, schedule.SHEID,
                schedule.title as schetitle, weekday(schedule.begin) as weekday,
                schedule.descript  as descript,
                schedule.typeID, EventTools.tools, EventTools.XSL,
                EventTools.TypeName, UNIX_TIMESTAMP(schedule.begin) as begin,
                UNIX_TIMESTAMP(schedule.end) as end, descript,
                Courses.Title as ctitle
         FROM   EventTools, Teachers, Courses, schedule
               INNER JOIN
                      scheduleID ON (schedule.SHEID = scheduleID.SHEID)
         WHERE
                schedule.SHEID=$sheid AND
                Teachers.mid=$mid AND
                Teachers.CID=schedule.CID AND
                Courses.Status>0 AND
                Courses.CID=schedule.CID AND
                schedule.typeID=EventTools.TypeID AND
                              scheduleID.mid IS NOT NULL
      ";
        }else {
                $time_registered = get_time_registered($mid,getField('schedule','CID','SHEID',$sheid));
                $sql="
         SELECT schedule.CID, scheduleID.toolParams, schedule.SHEID,
                schedule.title as schetitle, weekday(schedule.begin) as weekday,
                schedule.descript  as descript,
                schedule.typeID, EventTools.tools, EventTools.XSL,
                EventTools.TypeName,
                Courses.Title as ctitle
         FROM   EventTools, Courses, schedule
               INNER JOIN
                      scheduleID ON (schedule.SHEID = scheduleID.SHEID)
         WHERE
                schedule.sheid=$sheid AND
                scheduleID.mid=$mid AND
                scheduleID.sheid=$sheid AND
                Courses.CID=schedule.CID AND
                Courses.Status>0 AND
                schedule.typeID=EventTools.TypeID AND ((schedule.timetype = 0 AND
                " . $adodb->SQLDate("Y-m-d H:i:s", "begin") . " <= '$current_datetime' AND
                " . $adodb->SQLDate("Y-m-d H:i:s", "end") . " >= '$current_datetime') OR
                (schedule.timetype = 1 AND schedule.startday<='{$time_registered}' AND schedule.stopday>='{$time_registered}'))";

                //$query = "SELECT DISTINCT scheduleID.SHEID as SHEID
                //                  FROM scheduleID INNER JOIN schedule ON scheduleID.SHEID = schedule.SHEID
                //                  WHERE GREATEST(UNIX_TIMESTAMP(schedule.begin),$begin_day_unixtime) < LEAST(UNIX_TIMESTAMP(schedule.end),$end_day_unixtime) ORDER BY schedule.begin";
        }
        $res=sql($sql,"errSL602");
        if (sqlrows($res)==0) {

            $GLOBALS['controller']->setView('DocumentBlank');

            $sh = new Schedule;
            $sh->init($sheid);
            $current_cid = $sh->cid;

            $smarty_tpl = new Smarty_els;
            $smarty_tpl->assign("sitepath", "{$sitepath}");
            // $smarty_tpl->assign("location", "{$sitepath}index.php");
            $smarty_tpl->assign("location", "javascript:window.close();");

            if($dean)
            {
                $msg = _("Такого занятия нет в БД");
            }
            elseif($teacher)
            {
                if(!in_array($current_cid, $s['tkurs']))
                    $msg = _("Данное занятие не принадлежит к списку Ваших курсов");
                else
                    $msg = _("Такого занятия нет в БД");
            }
            else
            {
                // Если препод запустил задание из ведомости
                $kursArr = ($s['perm']==2) ? $s['tkurs'] : $s['skurs'];
                if(!in_array($current_cid, $kursArr /*$s['skurs']*/))
                    $msg = _("Данное занятие не принадлежит к списку Ваших курсов");
                else
                    $msg = _("Занятие назначено на другое время");
            }

            // Возвращаем mid на своё место если препод запустил занятие из ведомости
            if (($s['perm'] == 2) && isset($s['old_mid'])) $s['mid'] = (int) $s['old_mid'];

            $smarty_tpl->assign("message", $msg);

            $GLOBALS['controller']->setMessage($msg, JS_GO_URL, 'schedule.php4');
            $GLOBALS['controller']->terminate();
            if (!$GLOBALS['controller']->enabled) {
                exit($smarty_tpl->fetch("schedule_error.tpl"));
            } else {
                exit();
            }
        }

        $r=sqlget($res);
        if (debug) pr($r);
        $cid=$r["CID"];
        $scourse=$r["ctitle"];
        $typeid=$r['typeID'];
        $tools=$r['tools'];
        $tool_par=$r['toolParams'];
        $fxsl="xml2/EventXSL/$r[XSL]";
        $filexsl=gf($fxsl);
        $fxml="xml2/EventXML/eventType$typeid.xml";
        $ex=explode(";",$tool_par);
        $parts=explode(",",$r['tools']);
        if (count($parts)==0)
        exit("урок пустой");
        $filexml=getparts($typeid,$parts);
        foreach ($ex as $v) {
                $tmp=explode("=",$v);
                $tmp1=trim($tmp[0]);
                $arg[$tmp1]=trim($tmp[1]);
        }

        $tmp=explode(" ",$arg['externalURLs_urls']);
        $urls="";
        foreach ($tmp as $v) {
                if (!empty($v)) $urls.="<li><a style='color:white' href='{$GLOBALS['protocol']}://".$v."'>".$v."</a></li>";
        }

        if (debug) pr ($arg);

        $html=array();
        $host=$_SERVER["HTTP_HOST"];
        $moreparam="&mode_frames=1&jsclose=1".$sess."&sheid=".$sheid.'&cid='.$cid;

        //pr(session_id());

//      без xslt тоже работает
/*        $filexsl=iconv("ISO-8859-1","UTF-8",$filexsl);
        $arguments = array('/_xml' => $filexml, '/_xsl' => $filexsl);
        $xh=xslt_create();

        $out=xslt_process($xh, 'arg:/_xml', 'arg:/_xsl', NULL, $arguments);


        $out=iconv("UTF-8","ISO-8859-1",$out);

        $out=preg_replace("!content=\"text/html; charset=UTF-8\"!i","content=\"text/html; charset={$GLOBALS['controller']->lang_controller->lang_current->encoding}\"",$out,1);
        xslt_free($xh);
*/


        //if (debug) echo "<hr size=1 noshade><xmp>$out</xmp><hr size=1 noshade>";
        ////


        $pass=substr(md5(microtime()),0,20);

        $out=str_replace("[chatApplet_in_login]",$s[login],$out);
        $out=str_replace("[chatApplet_in_pass]",$pass,$out);
        //$out=str_replace("[chatApplet_in_room]",$sheid,$out);
        $out=str_replace("[chatApplet_in_serverPort]",kclientport,$out);
        $out=str_replace("[Server]",$_SERVER["HTTP_HOST"],$out);
        $out=str_replace("[SHE_TITLE]",$r["schetitle"],$out);
        $out=str_replace("[URLServer]","{$GLOBALS['protocol']}://$host/",$out);
        $out=str_replace("[URLServlets]","{$GLOBALS['protocol']}://$host/servlet/",$out);
        $out=str_replace("[SHE_CID]",$cid,$out);
        $out=str_replace("[SHE_COURSE]",$scourse,$out);
        $out=str_replace("[liveCamPro_file]","{$GLOBALS['protocol']}://$host/COURSES/course".$cid."/webcam_room_".$cid."/web.jpg",$out);
        $out=str_replace("[SHE_ID]",$sheid,$out);
        $out=str_replace("[kpaint_in_login]",$login,$out);
        $out=str_replace("[externalResourses]",$urls,$out);
        $out=str_replace("[TEACHNOTES]",$r[descript],$out);
        $out=str_replace("[more_param]",$moreparam,$out);
        $out=str_replace("[FSESSID]",$sessf,$out);
        $out=str_replace("[Coursename]",$r["ctitle"],$out);
        $out=str_replace("[starttime]",date("d-m-Y H:i",$r["begin"]),$out);
        $out=str_replace("[endtime]",date("d-m-Y H:i",$r["end"]),$out);
        $out=str_replace("[fname]",$r["fname"],$out);
        $out=str_replace("[sname]",$r["sname"],$out);
        $out=str_replace("[HOST]",$host,$out);
        $out=str_replace("[KCLIENT_PORT]",kclientport,$out);



        foreach ($arg as $k=>$v) {
                $out=str_replace("[$k]",$v,$out);
        }
        if (debug)
                echo "<xmp>".preg_replace("!\[[a-z_0-9]{3,30}\]!i","<font color=white><b>\\0</b></font>",$out)."</xmp><hr size=1 noshade>";
        $out=preg_replace("!\[[a-z_0-9]{3,30}\]!i","",$out);

        // если нет экзотики - прячем промежуточное окно
        if (!count(array_diff($parts, array('module', 'tests')))) {
            $out = str_replace(array("<BODY", "<a "), array("<BODY style='color:white'", "<a style='color:white'"), $out);
            if ((isset($_GET['mode_frames']))&&($_GET['mode_frames'])) {
                    $strOpenFrames = "<input type=hidden name='mode_frames' value=".MODE_SHOW_FRAMES."></form>";
                    $strNewWin = "";
                    $strAction = "location.href=document.links[0].href;";
                    $strSkipPage = "
                               <script>
                                       if (window.document.forms[0]) {
                                            window.document.forms[0].target='_top';
                                               window.document.forms[0].submit();

                                       } else {
                                               if (window.document.links[0]) {

                                                               {$strAction}
                                               } else {

                                                       history.back();
                                               }
                                       }
                               </script></body>";
                    $strHideContent = "<body style='color:white'";
                    $strHideSpans = "<span style='color:white'";
                    $strHideLinks = "<a style='color:white'";

                    if (strpos($tools, "chatApplet") === false) {
                            $out = str_replace("<BODY", $strHideContent, $out);
                            $out = str_replace("</BODY>", $strSkipPage, $out);
                            $out = str_replace("<span", $strHideSpans, $out);
                            $out = str_replace("<a ", $strHideLinks, $out);
                            $out = str_replace("</form>", $strOpenFrames, $out);
                    }
                    $boolInFrame = true;
                    session_register("boolInFrame");
                    $_SESSION['boolInFrame'] = true;

                    $closeSheduleWindow = false;
                    session_register("closeSheduleWindow");
                    $_SESSION['closeSheduleWindow'] = false;

            }
            else {
                    $strHideLinks = "<a style='color:white'";
                    $strHideSpans = "<span style='color:white'";
                    $out = str_replace("<a ", $strHideLinks, $out);
                    $out = str_replace("<span", $strHideSpans, $out);
                    $out .= "<script language='javascript'>
                                             var s = document.forms;
                                             if(s.length != 0) {
                                                     window.document.forms[0].submit();                                         }
                                             else {
                                                     document.location.href = document.links[0].href;
                                             }
                                     </script>";
            }
        }
        echo $out;

        $sql = "UPDATE scheduleID SET test_date=".$GLOBALS['adodb']->DBTimeStamp(time())." WHERE SHEID='".(int) $sheid."' AND MID='".$s['mid']."'";
        sql($sql);

        if (trim($tools)=='offline') {
            $GLOBALS['controller']->setMessage(_('Данное занятие не может быть запущено'),$sitepath);
            $GLOBALS['controller']->terminate();
            exit();
        }

        /**
        * Вызывает в текущем окне сразу необходимый event!!! Если убрать это, то будет старая система с ссылками
        */
        $tools = explode(',',$tools);

        foreach($tools as $v) {
            switch($v) {
            	case 'webinar':
            		$links['webinar'] = $GLOBALS['sitepath'].'webinar/index/?pointId='.$sheid;
            		$link = $links['webinar'];
            		break;
            	case 'connectpro':
                    $links['connectpro'] = "cp.php?id=$sheid";
                    $link = $links['connectpro'];            		
            		break;
                case 'run':
                    $links['run'] = "show_material.php?run_id={$arg['run_id']}&cid={$cid}";
                    $link = $links['run'];
                    break;
                case "module":
                    //$links['module'] = "teachers/edit_mod.php4?make=editMod&ModID={$arg['module_moduleID']}&CID={$cid}&PID=&new_win=1&mode_frames=1";
                    //$links['module'] = "show_material.php?bid={$arg['module_moduleID']}&cid={$cid}";
                    $links['module'] = "course_structure.php?oid={$arg['module_moduleID']}";
                    $link = $links['module'];
                break;
                case "tests":
                    $links['test'] = "test_start.php?tid={$arg['tests_testID']}{$moreparam}";
                    $link = $links['test'];
                break;
                case "chat":
                    $links['chat'] = "ajaxchat.php?sheid=$sheid";
                    $link = $links['chat'];
                    // $link = "chat_chat.php?uid={$s['mid']}&rid={$r['createIDID']}&sheid={$sheid}";
                break;
                case "video_live":
                    $links['video_live'] = "video_live.php";
                    $link = $links['video_live'];
                break;
            }
        }

        unset($_SESSION['s']['location']);
        if (count($links)>1) {
            if (array_key_exists('module',$links)) {
                $_SESSION['s']['location'] = $sheid;
            }
            $html = prepare_shedule_template($links);
            ob_clean();
            echo $html;
            die();
        }
        header("location: {$link}");
}

function add_shedule_item( $mids, $sheid, $tool_params = ""){
        /*    if( isarray($mids)){
        if ( count( $mids ) ) {
        $rq="INSERT INTO scheduleID (sheid, mid) VALUES ";
        foreach ($mids as $mid) {
        $rq.="\n($sheid, $mid ),";
        }
        $rq=substr($rq, 0, strlen($rq)-1);

        $res=sql($rq,"err61__add");
        }
        }else*/{
        $rq="INSERT INTO scheduleID (sheid, mid, toolParams, isgroup) VALUES ($sheid, $mids, '{$tool_params}','0')";
        //fn echo $rq."<br><br>";
        $res=sql($rq,"err61__add");
        //      echo $res.$rq."<BR>";
        }
        return( $res );
}

function clear_shedule( $mid, $cid ){
        //  отчищает расписание
        $sheids=get_shedule( $mid, $cid );
        if(count($sheids)>0){
                $ss=implode(",", $sheids );
                $rq="DELETE FROM scheduleID
                WHERE scheduleID.SHEID IN ($ss) AND MID='".(int) $mid."'";     //schedule.CID=$cid AND
                $res=sql( $rq, "err-select shedule delete");
                //      echo "<LI>$rq";
        }
        //    else
        //      echo "ПО КУРСУ $cid ДЛЯ $mid НЕТ ЗАНЯТИЙ";
        return( $res );
}

function get_shedule( $mid, $cid ){

        // возвращает занятия студента по курсу
        $rq="SELECT * FROM   schedule, scheduleID
           WHERE schedule.CID=$cid AND scheduleID.SHEID=schedule.SHEID AND scheduleID.MID=$mid";
        //    $rq="SELECT * FROM   schedule, scheduleid
        //           WHERE schedule.CID=$cid AND scheduleid.SHEID=schedule.SHEID AND scheduleid.MID=$mid";
        $res=sql( $rq, "err-select shedule 3");
        $i=0;
        while( $r=sqlget($res) ){
                $ret[$i++]=$r[SHEID];
        }
        //    echo "занятий по курсу $cid:$i<BR>";
        return( $ret );// count($res) );
}

function shedule_count( $mid, $cid ){

        // возвращает кол-во занятий студента по курсу
        $rq="SELECT * FROM   schedule, scheduleID
           WHERE schedule.CID=$cid AND scheduleID.SHEID=schedule.SHEID AND scheduleID.MID=$mid";
        //    $rq="SELECT * FROM   schedule, scheduleid
        //           WHERE schedule.CID=$cid AND scheduleid.SHEID=schedule.SHEID AND scheduleid.MID=$mid";
        $res=sql( $rq, "err-select shedule 2");
        $i=0;
        while( $r=sqlget($res) ){
                $i++;
        }
        return( $i);// count($res) );
}

function reset_mid_shedule( $mid, $cid ){
        // сбросить расписание
        // добавить все, что -
        // относительные занятия
        // абсолютные, те что для всех новых
        //    echo "ПЕРЕНАЗНАЧЕНИЕ ЗАНЯТИЙ CID=$cid MID=$mid";

        clear_shedule(  $mid, $cid );

        $rq="SELECT * FROM schedule WHERE schedule.CID=$cid";
        $res=sql( $rq, "err-select shedule 1");

        while( $r=sqlget($res)){ // для всех занятий на курсе
        $sheid=$r[SHEID];
        $query = "SELECT toolParams FROM scheduleID
                 WHERE SHEID = $sheid AND toolParams LIKE '%sAddToAllnew=1%'";
        $result = sql($query);
        if(sqlrows($result)) {
                $row = sqlget($result);
                $tool_params = $row['toolParams'];
                //fn echo "tool_params: ".$tool_params."  ";
                if( add_shedule_item( $mid, $sheid, $tool_params ) )  // добавляет в sheduleid записи о состоявшихся занятиях
                $i++;
        }
        }
        sqlfree( $res );
        return( $i );
}
/////////////////////////////////////  ПАРЫ /////////////////////////////////

function getallperiods( ){ // возвращает все пары, времена их начала окончания в массиве
$rq="SELECT * FROM periods ORDER BY starttime";
$res=sql( $rq, "err-select shedule 1");

while( $r=sqlget($res)){ // для всех занятий на курсе
$periods[$i][name]=$r[name];
$periods[$i][lid]=$r[lid];
$periods[$i][starttime]=$r[starttime];
$periods[$i][stoptime]=$r[stoptime];
$i++;
}
sqlfree( $res );
return( $periods );
}

// пример вызова $num=getlessonperiod( getallperiods(), 640);
function min2hours( $time , $boolSingle = false){
        // время в минутах
        $H= intval($time/60);
        $I= $time-$H*60;
        if($H<10) $H="0".$H;
        if($I<10) $I="0".$I;
        if (!$boolSingle) return( $H.":".$I );
        else {
                switch ($boolSingle) {
                        case "H":
                        return $H;
                        case "I":
                        return $I;
                }
        }
}
function getlessonperiod( $periods, $time ){ // возвращает номер пары
// time - кол-во минут от начала дня

foreach( $periods as $i=>$period){
        if( $time<$period[stoptime] AND $time>$period[starttime] )
        $num=$i;
}

return( $num );
}

function getlessontime( $period, &$starttime, &$stoptime ){ // возвращает время и окончания пары
$rq="SELECT * FROM periods";
$res=sql( $rq, "err-select shedule 1");
while( $r=sqlget($res)){ // для всех занятий на курсе
$period[name]      = $r[name];
$period[starttime] = $r[starttime];
$period[stoptime]  = $r[stoptime];
}
return( $time );
}

function autoSchedule($cid, $gid, $type)
{
        $cntSchedules = 0;
        $q = "
                SELECT
                  People.`MID`
                FROM
                  People
                  INNER JOIN groupuser ON (People.`MID` = groupuser.`mid`)
                WHERE
                  (groupuser.gid = '{$gid}')
        ";
        $r = sql($q);
        $arrPeople = array();
        while ($a = sqlget($r)) {
                $arrPeople[] = $a['MID'];
        }
        $q = "
                SELECT DISTINCT
                  mod_list.ModID,
                  mod_list.Title AS module_title,
                  Courses.CID
                FROM
                  test
                  INNER JOIN Courses ON (test.cid = Courses.CID)
                  INNER JOIN organizations ON (test.cid = organizations.cid)
                  INNER JOIN mod_list ON (organizations.mod_ref = mod_list.ModID) AND (test.cid = mod_list.CID)
                WHERE
                  Courses.CID = '{$cid}'
        ";
        $r = sql($q);
        $strDateBegin = (date("Y-m-d")."00:00:00");
        $strDateEnd = (date("Y-m-d")."23:59:00");
        while ($a = sqlget($r)) {
                if (existsScheduleGroup($a['ModID'], $gid, $type)) {
                        $cntExists++;
                        continue;
                }
                $qq = "
                        INSERT
                        INTO `schedule` (title, typeID, vedomost, CID, createID, begin, end, isgroup) values
                        (
                          '{$a['module_title']}',
                          '$type',
                          '1',
                          '{$cid}',
                          '{$_SESSION['s']['mid']}',
                          '{$strDateBegin}',
                          '{$strDateEnd}',
                          '1')
                ";
                $rr = sql($qq);
                $intSheid = sqllast();
                $qq = "
                        INSERT
                        INTO scheduleID (SHEID, gid, isgroup, toolParams) values (

                          '{$intSheid}',
                          '{$gid}',
                          '1',
                          'module_moduleID=0;)
                ";
                if ($rr = sql($qq)) $cntSchedules++;
                foreach ($arrPeople as $mid) {
                        if (existsScheduleMid($a['ModID'], $mid, $type)) {
                                continue;
                        }
                        $qq = "
                                INSERT
                                INTO scheduleID (SHEID, mid, isgroup, toolParams) values (

                                  '{$intSheid}',
                                  '{$mid}',
                                  '0',
                                  'module_moduleID={$a['ModID']};')
                        ";
                        $rr = sql($qq);
                }
        }
        $return['ok'] = $cntSchedules;
        $return['already'] = $cntExists;
        return $return;
}

function existsScheduleGroup($modID, $gid, $typeID)
{
        $q = "
                SELECT *
                FROM
                  schedule
                  INNER JOIN scheduleID scheduleID1 ON (schedule.SHEID = scheduleID1.SHEID)
                  INNER JOIN scheduleID scheduleID2 ON (schedule.SHEID = scheduleID2.SHEID)
                WHERE
                  (scheduleID2.toolParams LIKE '%module_moduleID={$modID}%') AND
                  (scheduleID2.isgroup = '0') AND
                  (schedule.typeID = '{$typeID}') AND
                  (scheduleID1.isgroup = '1') AND
                  (scheduleID1.gid = '{$gid}')
        ";
        $r = sql($q);
        return sqlrows($r);
}

function countScheduleGroup($did, $gid)
{
        $q = "
                SELECT *
                FROM
                  schedule
                  INNER JOIN scheduleID scheduleID1 ON (schedule.SHEID = scheduleID1.SHEID)
                  INNER JOIN scheduleID scheduleID2 ON (schedule.SHEID = scheduleID2.SHEID)
                WHERE
                  (scheduleID2.toolParams LIKE '%module_moduleID={$modID}%') AND
                  (scheduleID2.isgroup = '0') AND
                  (schedule.typeID = '{$typeID}') AND
                  (scheduleID1.isgroup = '1') AND
                  (scheduleID1.gid = '{$gid}')
        ";
        $r = sql($q);
        return sqlrows($r);
}

function existsScheduleMid($modID, $mid, $typeID)
{
        $q = "
                SELECT *
                FROM
                  schedule
                  INNER JOIN scheduleID ON (schedule.SHEID = scheduleID.SHEID)
                WHERE
                  (schedule.typeID = '{$typeID}') AND
                  (scheduleID.isgroup = '0') AND
                  (scheduleID.toolParams LIKE '%module_moduleID={$modID}%') AND
                  (scheduleID.`MID` = '{$mid}')
        ";
        $r = sql($q);
        return sqlrows($r);
}

function is_quiz_by_sheid($sheid) {
        $query = "SELECT typeID FROM schedule WHERE SHEID='$sheid'";
        $result = sql($query);
        $row = sqlget($result);
        $type_id = $row['typeID'];

        $query = "SELECT tools FROM EventTools WHERE TypeID='$type_id'";
        $result = sql($query);
        $row = sqlget($result);
        $tools = $row['tools'];
        if(strpos($tools, "collaborator") !== false) {
                return true;
        }
        else {
                return false;
        }
}

function is_quiz_by_type_id($type_id) {
        $query = "SELECT tools FROM EventTools WHERE TypeID='$type_id'";
        $result = sql($query);
        $row = sqlget($result);
        $tools = $row['tools'];
        if(strpos($tools, "collaborator") !== false) {
                return true;
        }
        else {
                return false;
        }
}
/*
class Schedule {
        var $sheid;
        var $type_id;
        var $module_id = 0;
        var $xml_file_path = "";
        var $db_id = 0;
        var $item_element = "";
        var $period = "-1";
        var $begin;
        var $end;
        var $rid;
        var $cid;
        var $icon;

        function init($sheid) {
                $this->sheid = $sheid;

                //Устанавливаем id типа занятия
                $query = "SELECT * FROM schedule WHERE sheid = $sheid";
                $result = sql($query,"errfn");
                $row = sqlget($result);
                $cid = $row['CID'];
                $this->cid = $cid;
                $this->period = $row['period'];
                $this->type_id = $row['typeID'];
                $this->begin = $row['begin'];
                $this->end = $row['end'];
                $this->rid = $row['rid'];

                //Определяем tools-ы для этого занятия
                $query = "SELECT * FROM EventTools WHERE TypeID = ".$this->type_id;
                $result = sql($query);
                $row = sqlget($result);

                $this->icon = $row['Icon'];


                $tools = explode(",", $row["tools"]);
                foreach($tools as $key => $value) {
                        $tools[$key] = trim($value);
                }

                //Если занятие связано с модулем устанавливаем id этого модуля
                if(in_array("module", $tools)) {

                        $query = "SELECT * FROM scheduleID WHERE SHEID = ".$this->sheid;
                        $result = sql($query);
                        $row = sqlget($result);
                        $toolParams = explode(";", $row['toolParams']);
                        foreach($toolParams as $key => $toolParam) {
                                if(strpos($toolParam, "module_moduleID=") !== false) {
                                        $tmp = explode("=", $toolParam);
                                        $this->module_id = trim($tmp[1]);
                                }
                        }
                }

                //Проверяем существует ли xml файл курса
                $this->xml_file_path = $this->_check_for_existing_course_xml_file($cid);

                //Определяем db_id если он сушествует в таблице mod_content
                if($this->xml_file_path != "") {
                        $query = "SELECT * FROM mod_content WHERE ModID = ".$this->module_id;
                        $result = sql($query);
                        if(sqlrows($result) > 0) {
                                $row = sqlget($result);
                                $tmp = explode("?", $row['mod_l']);
                                $tmp_1 = explode("&", $tmp[1]);
                                foreach($tmp_1 as $key => $value) {
                                        $tmp_2 = explode("=", $value);
                                        if($tmp_2[0] == "id") {
                                                $this->db_id = $tmp_2[1];
                                        }
                                }
                        }
                }

                if($this->db_id != 0) {

                        $xml = domxml_open_file($this->xml_file_path);
                        $xpath_context = xpath_new_context($xml);
                        $elements = xpath_eval($xpath_context, "//*[@DB_ID='".$this->db_id."']");
                        $nodes = $elements->nodeset;
                        $this->item_element = $nodes[0];
                }


        }

        function _check_for_existing_course_xml_file($cid) {
                if(is_file($_SERVER['DOCUMENT_ROOT']."/COURSES/course$cid/course.xml")) {
                        return $_SERVER['DOCUMENT_ROOT']."/COURSES/course$cid/course.xml";
                }
                else {
                        return "";
                }
        }

        function get_type() {
                $query = "SELECT * FROM EventTools WHERE TypeID = ".$this->type_id;
                $result = sql($query);
                $row = sqlget($result);
                return $row['TypeName'];
        }

        function get_icon() {
                return $this->icon;
        }

        function get_sheid() {
                return $this->sheid;
        }

        function get_course_name() {
                $query = "SELECT * FROM Courses WHERE CID = ".$this->cid;
                $result = sql($query);
                $row = sqlget($result);
                return $row['Title'];
        }

        function get_notes() {
                global $sf;
                $query = "SELECT * FROM schedule WHERE sheid = ".$this->sheid;
                $result = sql($query);
                $row = sqlget($result);
                return $row['descript'];
        }

        function get_module_id() {
                return $this->module_id;
        }

        function get_subject() {
                if($this->item_element != "") {
                        $item_element = $this->item_element;
                        $item_element_childrens = $item_element->child_nodes();
                        if(is_array($item_element_childrens))
                        foreach($item_element_childrens as $key => $item_element_children) {
                                if($item_element_children->tagname == "subject") {
                                        $item_element_children_attribute_title = $item_element_children->get_attribute("title");
                                        return  iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding,$item_element_children_attribute_title);
                                }
                        }
                }
        }

        function get_targets() {
                if($this->item_element != "") {
                        $item_element = $this->item_element;
                        $item_element_childrens = $item_element->child_nodes();
                        if(is_array($item_element_childrens))
                        foreach($item_element_childrens as $key => $item_element_children) {
                                if($item_element_children->tagname == "targets") {
                                        $targets_element_childrens = $item_element_children->child_nodes();
                                        $return_array = array();
                                        if(is_array($targets_element_childrens))
                                        foreach($targets_element_childrens as $target_element) {
                                                if($target_element->tagname == "target") {
                                                        $return_array[] = iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding, $target_element->get_attribute("title"));
                                                }
                                        }
                                        return $return_array;
                                }
                        }
                }
        }

        function get_studiedproblems() {
                if($this->item_element == "") return " ";
                $item_element = $this->item_element;
                $item_element_childrens = $item_element->child_nodes();
                if(is_array($item_element_childrens))
                foreach($item_element_childrens as $key => $item_element_children) {
                        if($item_element_children->tagname == "studiedproblems") {
                                $studiedproblems_element = $item_element_children;
                                $studiedproblems_element_childrens = $studiedproblems_element->child_nodes();
                                $return_value = array();
                                $i = 0;
                                if(is_array($studiedproblems_element_childrens))
                                foreach($studiedproblems_element_childrens as $key => $studiedproblem_element) {
                                        if($studiedproblem_element->tagname == "studiedproblem") {
                                                $return_value[$i]['title'] = iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding,$studiedproblem_element->get_attribute("title"));
                                                $studiedproblem_element_childrens = $studiedproblem_element->child_nodes();
                                                if(is_array($studiedproblem_element_childrens))
                                                foreach($studiedproblem_element_childrens as $studiedproblem_element_child) {
                                                        if($studiedproblem_element_child->tagname == "text") {
                                                                $return_value[$i]['texts'][] = iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding,$studiedproblem_element_child->get_attribute("title"));
                                                        }
                                                }
                                                $i++;
                                        }
                                }
                                return $return_value;
                        }
                }
        }

        function get_time() {
                if($this->period != "-1") {
                        $query = "SELECT * FROM periods WHERE lid = ".$this->period;
                        $result = sql($query);
                        if(sqlrows($result) > 0) {
                                $return_array['begin'] = substr($this->begin,11,5);
                                $return_array['end'] = substr($this->end,11,5);
                                return $return_array;
                        }
                        else {
                                return "";
                        }
                }
                else {
                        return "";
                }
        }

        function get_date() {
                if($this->period != "-1") {
                        $year = substr($this->begin,0,4);
                        $month = substr($this->begin, 5,2);
                        $day = substr($this->begin,8,2);
                        return $day.".".$month.".".$year;
                }
                else {
                        return "";
                }
        }

        function get_period() {
                if($this->period != "-1") {
                        $query = "SELECT * FROM periods WHERE lid = ".$this->period;
                        $result = sql($query);
                        $row = sqlget($result);
                        return $row['name'];
                }
                else {
                        return "";
                }
        }

        function get_room() {
                if( ($this->rid != 0) && ($this->rid != "-1") ) {
                         $query = "SELECT * FROM rooms WHERE rid = ".$this->rid;
                         $result = sql($query);
                         if( sqlrows($result) > 0 ) {
                                 $row = sqlget($result);
                                 return $row['name'];
                         }
                         else {
                                 return "";
                         }
                }
                return "";
        }

        function get_name() {
                $query = "SELECT * FROM schedule WHERE SHEID = ".$this->sheid;
                $result = sql($query);
                $row = sqlget($result);
                return $row['title'];
        }

        function get_teacher() {
                $query = "SELECT * FROM schedule WHERE SHEID = ".$this->sheid;
                $result = sql($query);
                $row = sqlget($result);

                $query = "SELECT * FROM People WHERE MID = ".$row['createID'];
                $result = sql($query);
                $row = sqlget($result);

                $meta = read_metadata($row['Information'],"military_state");

                return $row['LastName'];
        }

}  */

function getRooms($CID,$capacity=true){
      $rq="SELECT *
        FROM rooms
        LEFT JOIN rooms2course ON (rooms2course.rid = rooms.rid)
        WHERE ".($CID?"cid = '$CID' AND":"")." status>0 ORDER BY name";
   $res=sql($rq,"room21");

   while ($r=sqlget($res)) {
      $rooms[$r['rid']]['rid']=$r['rid'];
      $rooms[$r['rid']]['name']=$r['name'];
      if ($capacity) $rooms[$r['rid']]['name'] .= " ("._("макс.")." ".$r['volume']." "._("чел.").")";
  }
  return( $rooms );
}

function is_showed_print_button($sheid) {
        $schedule = new Schedule;
        $schedule->init($sheid);
        if( ($schedule->module_id == 0) || ($schedule->xml_file_path == "") ) {
                return false;
        }
        else {
                return true;
        }
}

/**
*
* @return string frameset template
* @param array $modules
*/
function prepare_shedule_template($modules) {
    foreach($modules as $k=>$v) {
        switch($k) {
            case 'video_live':
                $modules[$k] .= '?view=blank';
            break;
            case 'chat':
                $modules[$k] .= '&view=blank';
            break;
        }
    }

    $names = array_keys($modules);
    $values = array_values($modules);

    switch (count($modules)) {
        case 2:
        if ($names[0] == 'video_live') {
            $values[0] .= '&fullscreen=true';
        }
        $template =
            "
            <frameset frameborder=1 border=1 framespacing=0>
                <frameset rows=\"50,60%,*\">
                    <frame name=\"topFrame\" src=\"course_structure_top.php?oid=0\" noresize=yes frameborder=no scrolling=no>
                    <frame name=\"$names[0]\" src=\"{$values[0]}\" frameborder=no>
                    <frame name=\"$names[1]\" src=\"{$values[1]}\" frameborder=no>
                </frameset>
            <noframes><body>
            </body></noframes>
            </frameset>
            ";

        break;
        case 3:
        $template =
            "
            <frameset frameborder=1 border=1 framespacing=0 cols=\"50%,*\">
	            <frame name=\"{$names[0]}\" src=\"{$values[0]}\">
	            <frameset rows=\"50%,*\">
		            <frame name=\"$names[1]\" src=\"{$values[1]}\">
		            <frame name=\"$names[2]\" src=\"{$values[2]}\">
	            </frameset>
            <noframes><body>
            </body></noframes>
            </frameset>
            ";
        $template =
            "
            <frameset frameborder=1 border=1 framespacing=0 rows=\"60%,*\">
	            <frame name=\"{$names[0]}\" src=\"{$values[0]}\">
	            <frameset cols=\"40%,*\">
		            <frame name=\"$names[1]\" src=\"{$values[1]}\">
		            <frame name=\"$names[2]\" src=\"{$values[2]}\">
	            </frameset>
            <noframes><body>
            </body></noframes>
            </frameset>
            ";
        break;
        case 4:

        break;
    }
    return $template;
}

function get_unmarked_sheids($mid) {
    if ($mid) {
        $sql = "SELECT DISTINCT SHEID FROM scheduleID WHERE MID='".(int) $mid."' AND V_STATUS='-1'";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $ret[] = $row['SHEID'];
        }
    }
    return $ret;
}


function get_marked_sheids($mid) {
    $ret = array();
    if ($mid) {
        $sql = "SELECT DISTINCT SHEID FROM scheduleID WHERE MID='".(int) $mid."' AND V_STATUS<>'-1'";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $ret[] = $row['SHEID'];
        }
    }
    return $ret;
}

function getPartOfToolParams ($toolParams, $search = 'module_moduleID') {
    $module = explode(';',$toolParams);
	foreach ($module as $v) {
	    $dummy = explode('=',$v);
	    if ($dummy[0] == $search) {
	        $module = $dummy[1];
	        return $module;
	    }
	}
}

function checkSchedule4SynhCourse ($cid/*, $crntStudy*/) {
    //проверка корректности дат занятий для курса с синхронным режимом прохождения
    $res = sql($sql = "SELECT o.*
                       FROM organizations o
                       LEFT JOIN Courses c ON (c.CID = o.cid)
                       WHERE c.is_module_need_check = 1 AND c.CID = '$cid'");
    $orgs = array();
    while ($row = sqlget($res)) {
        $orgs[$row['prev_ref']] = $row['oid'];
    }

    $res = sql($sql = "SELECT s.SHEID, s.begin, s.end, s.startday, s.stopday, sid.toolParams
                       FROM `schedule` s
                       LEFT JOIN scheduleID sid ON (sid.SHEID = s.SHEID)
                       WHERE s.CID = '$cid' AND sid.toolParams LIKE '%module_moduleID%'");
    $scheduleDates = array();
    while ($row = sqlget($res)) {
        $scheduleDates[getPartOfToolParams($row['toolParams'])] = array('begin'=>strtotime($row['begin']),
                                                                        //'end'=>strtotime($row['end']),
                                                                        'startday'=>$row['startday'],
                                                                        //'stopday'=>$row['stopday'],
                                                                        'SHEID'=>$row['SHEID']
                                                                        );
    }
    //добавим ткущую дату
    //$scheduleDates[$form['module']] = $crntStudy;
    /*
                                      array('begin'=>$timestamp1,
                                            //'end'=>$timestamp2,
                                            'startday'=>$startday,
                                            //'stopday'=>$stopday,
                                            'SHEID'=>$sheid
                                             );
    */

    if (count($orgs) && count($scheduleDates)) {
        $curOrg = $orgs['-1'];
        $curDateAbs = $curDateRel = 0;
        while (!empty($curOrg)) {
            if (isset($scheduleDates[$curOrg]) &&
                ($scheduleDates[$curOrg]['begin']   < $curDateAbs ||
                $scheduleDates[$curOrg]['startday'] < $curDateRel))
                {
                    return $alertMsg = _('Время проведения одного или нескольких занятий,
                                           не соответствует положению модулей (на которых эти занятия основаны)
                                           в дереве курса. Для получения более подробной информации ознакомтесь
                                           с описанием синхронного режима прохождения курса в соответствующем
                                           разделе документации.');
                }
            $curDateAbs = $scheduleDates[$curOrg]['begin']?$scheduleDates[$curOrg]['begin']:$curDateAbs;
            $curDateRel = $scheduleDates[$curOrg]['startday']?$scheduleDates[$curOrg]['startday']:$curDateRel;
            $curOrg = $orgs[$curOrg];
        }

    }
}

/*
############################################################
############################################################
############################################################
############################################################
*/
/*
class WeekSchedule {
        var $begin_week;
        var $end_week;

        function init_by_begin_week($begin_week) {
                //формат входного параметра YYYY-MM-DD
                $this->begin_week = $begin_week;
                $year_number = substr($begin_week, 0, 4);
                $month_number = ltrim(substr($begin_week, 5, 2),"0");
                $day_number = ltrim(substr($begin_week, 8, 2),"0");
                $begin_unixtime = mktime(0,0,0,$month_number,$day_number,$year_number);
                $end_unixtime = mktime(0,0,0,$month_number,$day_number+7,$year_number);
                $this->end_week = date("Y-m-d", $end_unixtime);
        }

        function get_as_array() {
                $begin_day = $this->begin_week;
                $i = 1;
                while($begin_day < $this->end_week) {
                        $end_day = date("Y-m-d", mktime(0,0,0,substr($begin_day,5,2),ltrim(substr($begin_day,8,2),"0") + 1,substr($begin_day,0,4)));
                        $sheids_array = $this->_get_sheids_array($begin_day, $end_day);
                        $return_array[$i]['day_name'] = $this->get_day_name_by_number($i);
                        $return_array[$i]['date'] = substr($begin_day,8,2).".".substr($begin_day,5,2).".".substr($begin_day,0,4);
                        $return_array[$i]['tweek'] = mktime(0,0,0,ltrim(substr($begin_day,5,2),"0"),ltrim(substr($begin_day,8,2),"0"),substr($begin_day,0,4));
                        if( is_array($sheids_array) ) {
                                $return_array[$i]['count_studies'] = count($sheids_array);
                                foreach($sheids_array as $sheid) {
                                        $schedule = new Schedule;
                                        $schedule->init($sheid);
                                        $return_array[$i]['studies'][] = array(
                                                                                                "sheid" => $schedule->get_sheid(),
                                                                                                "name" => $schedule->get_name(),
                                                                                                "period" => $schedule->get_period(),
                                                                                                "teacher" => $schedule->get_teacher(),
                                                                                                "room" => $schedule->get_room(),
                                                                                                "module_id" => $schedule->get_module_id(),
                                                                                                "course_name" => $schedule->get_course_name(),
                                                                                                "notes" => $schedule->get_notes(),
                                                                                                "icon" => $schedule->get_icon());

                                }
                        }
                        $begin_day = $end_day;
                        $i++;
                }
                return $return_array;
        }

        function get_as_array_for_day($date) {
                $week_schedule_array = $this->get_as_array();
                if(is_array($week_schedule_array)) {
                        foreach($week_schedule_array as $key => $value) {
                                if($value['date'] == $date) {
                                        $return_array = array();
                                        $return_array[] = $value;
                                        return $return_array;
                                }
                        }
                }
        }

        function _get_sheids_array($begin_day, $end_day) {
                $begin_day_unixtime = mktime(0,0,0,substr($begin_day,5,2),substr($begin_day,8,2),substr($begin_day,0,4));
                $end_day_unixtime = mktime(0,0,0,substr($end_day,5,2),substr($end_day,8,2),substr($end_day,0,4));
                $query = "SELECT DISTINCT scheduleID.SHEID as SHEID, schedule.begin, schedule.end FROM scheduleID INNER JOIN schedule ON scheduleID.SHEID = schedule.SHEID
                                  WHERE GREATEST(UNIX_TIMESTAMP(schedule.begin),$begin_day_unixtime) < LEAST(UNIX_TIMESTAMP(schedule.end),$end_day_unixtime) ORDER BY schedule.begin";
                echo $query."<hr />";
                $result = sql($query,"eerrnfsdf");
                $return_array = array();
                while( $row = sqlget($result) ) {
                        $return_array[] = $row['SHEID'];
                }
                return $return_array;
        }

        function get_day_name_by_number($number) {
                switch ($number) {
                        case "1":
                                return "понедельник";
                        break;
                        case "2":
                                return "вторник";
                        break;
                        case "3":
                                return "среда";
                        break;
                        case "4":
                                return "четверг";
                        break;
                        case "5":
                                return "пятница";
                        break;
                        case "6":
                                return "суббота";
                        break;
                        case "7":
                                return "воскресенье";
                        break;
                        default:
                                return "";
                }
        }
}*/

?>