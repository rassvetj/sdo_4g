<?php
//header("Content-Type: text/html; windows-1251");
header("Content-Type: text/xml; windows-1251");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0");
header("Pragma: no-cache");

define('_K_DIR_TEMPLATES','templates/');

include("../1.php");
include("../test.inc.php");
include("KTemplate.php");
require_once('lib/classes/Module.class.php');
$t=new KTemplate();

$q = "SELECT * FROM alt_mark";
$res = sql($q);
while ($arr = sqlget($res)) {
	$arrAltMark[$arr['int']] = $arr['char'];
}


//задать критерий для определения входного контроля
$gid=$_GET['gid'];
$date_b =$_GET['date_b'];
$date_e =$_GET['date_e'];

$date_b=date_loc2sql($date_b);
$date_e=date_loc2sql($date_e);

$arr_sheid=$_GET['arr_sheid'];

$t->file('data.htm','w');
$t->block('w',array('Body','Group','Student','Mark'));

$t->set('GROUPS');
$t->set('STATISTICA');

$q = (!isset($_GET['from_ved']))
             ? "SELECT DISTINCT ".$adodb->Concat("People.LastName","' '","People.FirstName")." AS stud_name,
                         People.MID AS stud_id,
                         People.Login AS stud_login,
                         groupname.name AS group_name, groupname.gid AS group_id
                   FROM People, Students, groupuser, groupname
                   WHERE groupname.gid='$gid'
                         AND Students.MID=People.MID
                         AND groupuser.mid=People.MID
                         AND groupname.gid=groupuser.gid
                         AND groupuser.gid='$gid'
                        ORDER BY stud_name"
                : "SELECT DISTINCT ".$adodb->Concat("People.LastName","' '","People.FirstName")." AS stud_name,
                         People.MID AS stud_id,
                         People.Login AS stud_login,
                         '' AS group_name, 0 AS group_id
                   FROM  `scheduleID`, `People`
                   WHERE `scheduleID`.`SHEID`='".$arr_sheid[0]."' AND
                         scheduleID.MID=People.MID
                   ORDER BY stud_name";


                /*? "SELECT CONCAT(People.LastName,' ',People.FirstName) AS stud_name,
                         People.MID AS stud_id,
                         People.Login AS stud_login,
                         groupname.name AS group_name, groupname.gid AS group_id
                   FROM People, Students, groupuser, groupname
                   WHERE groupname.gid='$gid'
                         AND Students.MID=People.MID
                         AND groupuser.mid=People.MID
                         AND groupname.gid=groupuser.gid
                         AND groupuser.gid='$gid'
                        ORDER BY People.LastName"
                : "SELECT CONCAT(People.LastName,' ',People.FirstName) AS stud_name,
                         People.MID AS stud_id,
                         People.Login AS stud_login,
                         '' AS group_name, 0 AS group_id
                   FROM  `scheduleID`, `People`
                   WHERE `scheduleID`.`SHEID`='".$arr_sheid[0]."' AND
                         scheduleID.MID=People.MID
                   ORDER BY People.LastName";*/
$r = sql($q, "err434");
if (isset($_GET['from_ved'])) {
   // ЗАЧЕМ ??? (c) Юрий 
   $date_b = '1970-01-01';
   $date_e = '2010-01-01';
}

$t->set('STUDENTS');
$sum_bal = 0;
$sum_percent = 0;
$count = 0;
while($a = sqlget($r)) {
    
      $tids = array();

      $t->set('stud_login',$a['stud_login']);
      // Oracle fix
      $t->set('stud_name',isset($a['STUD_NAME']) ? $a['STUD_NAME'] : $a['stud_name']);
      $t->set('MARKS');

      foreach($arr_sheid as $sheid) {
              $qq = "SELECT V_STATUS, scheduleID.toolParams
                     FROM scheduleID INNER JOIN schedule ON (schedule.SHEID = scheduleID.SHEID)
                     WHERE scheduleID.MID='".$a['stud_id']."'
                           AND schedule.SHEID=".$sheid."
                     ORDER BY end DESC
                     ";
// ЗАЧЕМ ЭТО НАДО БЫЛО??? (c) Юрий
//                           AND begin >= '$date_b'
//                           AND end < '$date_e'

              $rr = sql($qq, "errfn");

                     if($aa = sqlget($rr)) {
                     	if (isset($arrAltMark[$aa['V_STATUS']])){
                     		$ball = $arrAltMark[$aa['V_STATUS']];
                     	}
                        elseif($aa['V_STATUS'] == "-1")
                           $ball = "";
                        else
                           $ball = $aa['V_STATUS'];
                        $toolParams = explode(";", $aa['toolParams']);
                        foreach($toolParams as $key => $value) {
                                if(strpos($value, "tests_testID") !== false) {
                                   $temp_arr = explode("=", $value);
                                   $tid = $temp_arr[1];
                                   $tids = array($tid);
                                }
                                
                                if(strpos($value, "module_moduleID") !== false) {
                                   $temp_arr = explode("=", $value);
                                   if ($module = $temp_arr[1]) {
                                       if(is_array($tests = CModule::getTests(array($module))) && count($tests)) {                                   
                                           $tids = $tests[$module];
                                       }
                                   }
                                   
                                }
                        }
                     }
                     else {
                        $ball = "";
                     }
      }
      
      if (!is_array($tids)) $tids = array();
      
      foreach($tids as $tid) {
      if ($tid <= 0) continue;
      
      $qq = "SELECT loguser.stid as stid, loguser.balmax2, loguser.bal, loguser.`status`, stop as date
             FROM People INNER JOIN loguser ON (People.`MID` = loguser.`mid`)
             WHERE People.`MID` = ".$a['stud_id'];
      if(empty($tid)) {
         $qq .= " AND 1>1";
      }
      else {
         $qq .= " AND loguser.tid = '".(int) $tid."'";
      }
      $qq .= " ORDER BY loguser.stop DESC";


      $rr  = sql($qq, "err2343fd");
      if($aa = sqlget($rr)) {
         $est = "0";
         $date = date("H:i d.m.Y", $aa['date']);
         if ($aa['status'] == 5) {

         }
         if ($aa['balmax2'] != 0 ) {
               $bal_max_by_stid = get_maxbal_by_stid($aa[stid]);
               $bal_min_by_stid = get_minbal_by_stid($aa[stid]);
               if ($val = ($bal_max_by_stid-$bal_min_by_stid)) {
                   $est = (int) ((($aa['bal']-$bal_min_by_stid)*100)/$val);
                   //$est=sprintf("%1.0f",$aa['bal']*100/$bal_max_by_stid); 
               } else $est = 0;
         }
      }
      else {
           $est = "";
               $date = "";
      }
      $t->set("ball", $ball);
      $t->set("percent", $est);
      $t->set("date", $date);
      $t->set("count_ball", round($aa['bal'], 2));
      $t->parse('MARKS','Mark',true);
      // здесь еще добавить еще два запроса на  два разных типа занятий
      $t->parse('STUDENTS','Student',true);

      if($ball !== "") {
         $sum_bal += $ball;
      }

      $sum_percent += $est;
      $count++;

      } // foreach

}

if($count == 0) {
        $aver_percent = 0;
        $aver_bal = 0;
}
else {
        $aver_percent = substr($sum_percent/$count, 0, 5);
        $aver_bal = round($sum_bal/$count,2);
}


$t->set("aver_percent", $aver_percent);
$t->set("aver_bal", $aver_bal);
$t->parse('GROUPS','Group',true);

//$GLOBALS['controller']->setView('DocumentPrint');
//$GLOBALS['controller']->captureFromOb(CONTENT);
//echo "<pre>";
echo $t->subst('Body');
//echo "</pre>";
//$GLOBALS['controller']->captureStop(CONTENT);
//$GLOBALS['controller']->terminate();

function date_loc2sql($str) {
         list($day,$month, $year) =split("[/.-]",$str);
         return "$year-$month-$day";
}

function date_sql2loc($str) {
         list($year,$month,$day) =split("[/.-]",$str);
         return "$day.$month.$year";
}
?>