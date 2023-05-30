<?
//require_once('phplib.php4');

include_once("1.php");
include_once("_partparams.php");
require_once('formula_calc.php');

if (ereg('[0-9a-zA-Z]',$c)){
    $GLOBALS['controller']->setHelpSection($c);
}

if (!$stud)
     login_error();
istest();
/*if (!isset($s['user']['sd']))
     $s['user']['sd']=true;
if (isset($_GET['sd']))
    $s['user']['sd']=(boolean) $_GET['sd'];
$sd=($s['user']['sd']) ? 7 : 1;
if (!isset($s['user']['sf']))
    $s['user']['sf']=false;
if (isset($_GET['sf']))
    $s['user']['sf']=(boolean) $_GET['sf'];
*/
include_once("schedule.lib.php");
$query = "SELECT * FROM People WHERE mid=$mid";
//$query = "SELECT * FROM People WHERE mid=$mid AND login='".ad($s['login'])."'";
$res = sql($query, "errLogin234");

if (sqlrows($res)==0)
    exit("autorized false");
$pass=sqlres($res,0,'Password');
$javapass=sqlres($res,0,'javapassword');
sqlfree($res);
unset($_SESSION['s']['old_mid']);
if((isset($_GET['ch_sid']))&&($_GET['ch_sid'] == 1)) {
    $dean = "";
    $teacher = "";
    $mid = $_GET['mid'];
    $s[old_mid] = $s[mid];
    $s[mid] = $mid;
}



switch ($c)  {
   case "go":       
      schedule_go($sheid);
   break;
   case "delete":
   case "add":
   case "add2":
   case "add_submit":
   case "gen_schedule":
   case "gen_make":

      include("schedule_add.php4");
      break;

   case "modify":       
   case 'modify_people':
   case "modify_submit":
   case 'modify_people_submit':
      include("schedule_modify.php4");
      break;
   case "del_all_schedule":
      $r = sql("SELECT * FROM schedule WHERE CID={$_GET['CID']}");
      while ($a = sqlget($r)) {
         $rr = sql("DELETE FROM scheduleID WHERE SHEID='{$a['SHEID']}'");
      }
      $rr = sql("DELETE FROM schedule WHERE CID={$_GET['CID']}");
      header("Location:" . $sitepath . "ved.php4?CID=" . $_GET['CID']);
   break;
   default:
      $GLOBALS['controller']->setHeader(_("Расписание по неделям"));
      $filter_kurses = selCourses(array_merge($s['tkurs'],$s['skurs']),$CID,true);  
      $GLOBALS['controller']->addFilter(_("Курс"),'CID',$filter_kurses,$CID,false);
      if (!isset($MARKED)) {
          /*switch($s['perm']) {
              case 1:
                  $MARKED = 1;
              break;
              default:*/
                  $MARKED = 3;
              /*break;
          }*/
      }
      if ($s['perm'] == 1){
      	$GLOBALS['controller']->addFilter(_("Статус занятия"),'MARKED',array(1=>_("оценка не выставлена"),_("оценка выставлена"),STR_OPTIONS_ALL),$MARKED,false,0,false);            
      }
      schedule(0,$CID, $MARKED);
}
?>