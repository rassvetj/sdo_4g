<?  require_once('1.php');?>
<?  require_once('move2.lib.php');?>

<?


   $s[user][assort]=(isset($s[user][assort])) ? $s[user][assort] : 1;
   $s[user][corder]=(isset($s[user][corder])) ? $s[user][corder] : 1;

   $assort=(isset($_GET['assort'])) ? intval($_GET['assort']) : "";

   if ($assort==$s[user][assort]) $s[user][corder]=($s[user][corder]==1) ? 2 : 1;
   if ($assort) $s[user][assort]=$assort;

$s[user][tfull]=(isset($s[user][tfull])) ? $s[user][tfull] : 1;


$tfull=(isset($_GET['all'])) ? intval($_GET['all']) : "";
if ($tfull) $s[user][tfull]=$tfull;

$tfull=$s[user][tfull];



istest();

$CID=(isset($_GET['CID'])) ? $_GET['CID'] : $CID=0;
$CID=(isset($_POST['CID'])) ? $_POST['CID'] : $CID;

$pm=(isset($_POST['pm'])) ? $_POST['pm'] : array();
$pc=(isset($_POST['pc'])) ? $_POST['pc'] : array();

$go=(isset($_POST['c'])) ? $_POST['c'] : "";

if (!$dean && (!isset($s[tkurs]))) login_error();
if ($CID && (!isset($s[tkurs][$CID]))) login_error();

switch ($go) {
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
}


$curcid=$CID;

$html=show_tb(1);

$allheader=ph(_("Обучаемые"));
$hstud=ph(_("Обучаемые")." <FONT SIZE=0>>>[<a href=#in>"._("Претенденты")."</a>] >>[<a href=#out>"._("Прошедшие обучение")."</a>]</FONT>");
$habitur=ph("<a name=in>"._("Претенденты")."</a>");
$hgrad=ph("<a name=out>"._("Прошедшие обучение")."</a>");
$hot=ph("<a href='[PAGE]?[SESSID]all=[FULLOT]&CID=[CURCID]'>"._("Все обучаемые")."</a>");

$allcontent=loadtmpl("abitur-main.html");
$alltr=loadtmpl("abitur-tr.html");

$cselect="<option value=\"\">"._("Все курсы")."</option>";
$cselect.=selCourses($s['tkurs'],$CID);
/////
$gselect="<option value=\"\">"._("Все группы")."</option>";
//$gselect.=selGroups($s['tkurs'],$CID);
////
$cabitur=abiturList($CID);
$cgraduat=gradList($CID);
$cstudents=studList($CID);
$other="";
if($CID && $tfull==2) $other.=studListOther($CID);

$deangr=($dean) ? loadtmpl("abitur-gr.html") : "";

$html=str_replace("[ALL-CONTENT]",$allcontent,$html);
$html=str_replace("[DEANGR]",$deangr,$html);
$html=str_replace("[CUR-CID]",$CID,$html);
$html=str_replace("[HEADER]",$allheader,$html);
$html=str_replace("[SELECT-COURSES]",$cselect,$html);
$html=str_replace("[SELECT-GROUPS]",$gselect,$html);
$html=str_replace("[CURCID]",$curcid,$html);
$html=str_replace("[HGRAD]",$hgrad,$html);
$html=str_replace("[HABITUR]",$habitur,$html);
$html=str_replace("[HSTUD]",$hstud,$html);
$html=str_replace("[HOT]",$hot,$html);
$html=str_replace("[FULLOT]",(($tfull==2) ? "1" : "2"),$html);
$html=str_replace("[STUDENTS]",$cstudents,$html);
$html=str_replace("[ABITUR]",$cabitur,$html);
$html=str_replace("[GRADUATED]",$cgraduat,$html);
$html=str_replace("[OTHERSTUD]",$other,$html);
$html=str_replace("[CURCID]",$CID,$html);
$html=showSortImg($html,$s[user][assort]);

printtmpl($html);

?>