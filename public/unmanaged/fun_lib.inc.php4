<?
define("ORDER_BY_LNAME", 1);
define("ORDER_BY_POSITION", 2);
define("ORDER_BY_RANK", 3);

define("ORDER_ASC", 1);
define("ORDER_DESC", 0);

define("PERMISSIONS_STUDENT", 'student');
define("PERMISSIONS_TEACHER", 'teacher');
define("PERMISSIONS_DEAN", 'dean');

if (isset($dean)) unset($dean);
if (isset($admin)) unset($admin);
if (isset($teach)) unset($teach);
if (isset($stud)) unset($stud);

$s=(isset($_SESSION['s'])) ? $_SESSION['s'] : "";

// $s - сессия - 3 уровень доступа декана
$dean=(login_chek($s,$access['d'])) ? 1 : 0;

// $s - сессия - 4 уровень доступа декана
$admin=(login_chek($s,$access['a'])) ? 1 : 0;

// $s - сессия - 2 уровень доступа декана
$teach=(login_chek($s,$access['t'])) ? 1 : 0;

// $s - сессия - 1 уровень доступа декана
$stud=(login_chek($s,$access['s'])) ? 1 : 0;

if (isset($courses)) unset($courses);

if ($teach) $courses=$s['tkurs'];
elseif ($stud) $courses=$s['skurs'];


class log_elarn_error
{
    var $errors;

    function add_error($error,$num)
    {
        $this->errors[$error]=$num;
    }
    function get_error()
    {
        return ($this->errors);
    }
    function is_errors()
    {
        $ret=1;
        if (empty($this->errors)) $ret=0;
        return $ret;
    }
}

function student_alias_parse($text) {

    global $_STUDENT_ALIAS;
    $replace_what = array(  "[sTUDENT_ALIAS-IMEN-ONE]",
    "[sTUDENT_ALIAS-ROD-ONE]",
    "[sTUDENT_ALIAS-VIN-ONE]",
    "[sTUDENT_ALIAS-DAT-ONE]",
    "[sTUDENT_ALIAS-TVOR-ONE]",
    "[sTUDENT_ALIAS-PREDL-ONE]",
    "[sTUDENT_ALIAS-IMEN-MORE]",
    "[sTUDENT_ALIAS-ROD-MORE]",
    "[sTUDENT_ALIAS-VIN-MORE]",
    "[sTUDENT_ALIAS-DAT-MORE]",
    "[sTUDENT_ALIAS-TVOR-MORE]",
    "[sTUDENT_ALIAS-PREDL-MORE]",
    "[STUDENT_ALIAS-IMEN-ONE]",
    "[STUDENT_ALIAS-ROD-ONE]",
    "[STUDENT_ALIAS-VIN-ONE]",
    "[STUDENT_ALIAS-DAT-ONE]",
    "[STUDENT_ALIAS-TVOR-ONE]",
    "[STUDENT_ALIAS-PREDL-ONE]",
    "[STUDENT_ALIAS-IMEN-MORE]",
    "[STUDENT_ALIAS-ROD-MORE]",
    "[STUDENT_ALIAS-VIN-MORE]",
    "[STUDENT_ALIAS-DAT-MORE]",
    "[STUDENT_ALIAS-TVOR-MORE]",
    "[STUDENT_ALIAS-PREDL-MORE]");

    $replace_to = array(  _($_STUDENT_ALIAS['IMEN']['ONE']),
    _($_STUDENT_ALIAS['ROD']['ONE']),
    _($_STUDENT_ALIAS['VIN']['ONE']),
    _($_STUDENT_ALIAS['DAT']['ONE']),
    _($_STUDENT_ALIAS['TVOR']['ONE']),
    _($_STUDENT_ALIAS['PREDL']['ONE']),
    _($_STUDENT_ALIAS['IMEN']['MORE']),
    _($_STUDENT_ALIAS['ROD']['MORE']),
    _($_STUDENT_ALIAS['VIN']['MORE']),
    _($_STUDENT_ALIAS['DAT']['MORE']),
    _($_STUDENT_ALIAS['TVOR']['MORE']),
    _($_STUDENT_ALIAS['PREDL']['MORE']),
    _(first_char_to_upper($_STUDENT_ALIAS['IMEN']['ONE'])),
    _(first_char_to_upper($_STUDENT_ALIAS['ROD']['ONE'])),
    _(first_char_to_upper($_STUDENT_ALIAS['VIN']['ONE'])),
    _(first_char_to_upper($_STUDENT_ALIAS['DAT']['ONE'])),
    _(first_char_to_upper($_STUDENT_ALIAS['TVOR']['ONE'])),
    _(first_char_to_upper($_STUDENT_ALIAS['PREDL']['ONE'])),
    _(first_char_to_upper($_STUDENT_ALIAS['IMEN']['MORE'])),
    _(first_char_to_upper($_STUDENT_ALIAS['ROD']['MORE'])),
    _(first_char_to_upper($_STUDENT_ALIAS['VIN']['MORE'])),
    _(first_char_to_upper($_STUDENT_ALIAS['DAT']['MORE'])),
    _(first_char_to_upper($_STUDENT_ALIAS['TVOR']['MORE'])),
    _(first_char_to_upper($_STUDENT_ALIAS['PREDL']['MORE'])));

    return str_replace($replace_what, $replace_to, $text);
}

function first_char_to_upper($str) {
    $ascii_code = ord(substr($str, 0, 1));
    if(($ascii_code > 223)&&($ascii_code <256))
    $return_value = chr($ascii_code - 32).substr($str, 1, strlen($str)-1);
    else
    $return_value = strtoupper(substr($str, 0,1)).substr($str, 1, strlen($str)-1);
    return $return_value;
}

function getField($tableName, $fieldName, $idname, $id)
{
    if (strtolower($tableName) == 'options') {
        if (isset($GLOBALS['options']) && isset($GLOBALS['options'][$id])) {
            return $GLOBALS['options'][$id];
        }
    }

    $sql = "SELECT $fieldName FROM  $tableName WHERE $idname = '$id'";
$Result = sql($sql);
if(!$Result) return FALSE;
if(sqlrows($Result)==0) return FALSE;
$return_result = sqlres($Result,0,0);
return $return_result;

}

function mydatetype( $db_date )
{ // 0- абсолютная дата 1 - относительная
if( intval(substr( $db_date,0,4 ))<=1990 ) $type=1; else $type=0;
//   if( substr( $old, strlen($old)-1, 1 )!="0" )
return $type;
}
function mydate($old) // выводит дату из строковой константы
{                     // формат yyyy-mm-dd hh:ss:mm
if( intval(substr($old,5,2))!=0 )
$new=substr($old,8,2).".".substr($old,5,2).".".substr($old,0,4);
else
$new=_("на")." ".substr($old,8,2)." "._("день");

return $new;
}
function randString ($pass_len = 7)
{
    $allchars = 'abcdefghijknmopqrstuvwxyzABCDEFGHJKLNMPQRSTUVWXYZ23456789';
    $string = '';
    mt_srand ((double) microtime() * 1000000);
    for ($i = 0; $i < $pass_len; $i++) {
        $string .= $allchars{mt_rand(0,strlen($allchars)-1)};
    }
    return $string;
}
function validateEmail($email)
{
    if (eregi("(^[_a-z0-9-]+(\.[_a-z0-9-]+)*)@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", $email, $arrMatches)) {
        return $arrMatches[1];
    } else return false;
}



function return_valid_value($val)
{
    $val=htmlspecialchars($val);
    $val=str_replace("#","",$val);
    $val=addslashes($val);
    return $val;
}


function empty_dir($d)
{
    if (!isset($d)) $d=realpath("./")."/";
    if ($d[strlen($d)-1]!="/") $d.="/";
    if (isset($files)) unset($files);
    if ($di=@dir($d)) {
        while ($name=$di->read()) {
            if ($name=="." || $name=="..") continue;
            if (@is_dir($d.$name)) $files["1 $name"]=$name;
            else $files["2 $name"]=$name;
            $ftype[$name]=@filetype($d.$name);
        } //while
        $di->close();
    }
    if (isset($files))
    {
        if(count($files)!=0)
        {
            ksort($files);
            foreach ($files as $k=>$v) {
                $name=$d.$v;
                switch($ftype[$v]) {
                    case "file":
                    @unlink($d.urlencode($v));

                    break;
                    case "dir":
                    //          unlink($d.urlencode($v));
                    empty_dir($d.urlencode($v));
                    @rmdir($d.urlencode($v));
                    break;
                }       // swith --
            }       //foreach --
        }       //if count $files = 0
    } //if set $files

}   // empty_dir($d)


function create_course_list($who)
{
    $teacher=0;
    $ret="";
    $error->teach_courses=0;
    $error->stud_courses=0;

    if (is_teacher($who)) $teacher=1;
    if ($teacher) $ret=get_teacher_courses($who);
    else $ret=get_student_courses($who);

    // if($error->teach_courses || $error->stud_courses) print_error($error);

    return $ret;
}


function is_student($who)
{
    $student=0;
    $sql="SELECT * FROM Students WHERE MID='".$who."'";
    $sql_result=sql($sql);
    if (sqlrows($sql_result)>0) $student=1;
    return $student;
}

function is_teacher($who)
{
    global $teacherstable;
    $teacher=0;
    $sql="SELECT PID FROM ".$teacherstable." WHERE MID='".$who."'";
    $sql_result=sql($sql);
    if (sqlrows($sql_result)>0) $teacher=1;
    return $teacher;
}

function is_dean($who)
{
    $dean=0;
    $sql="SELECT * FROM deans WHERE MID='".$who."'";
    $sql_result=sql($sql);
    if (sqlrows($sql_result)>0) $dean=1;
    return $dean;
}

function is_admin($who)
{
    global $teacherstable;
    $admin=0;
    $sql="SELECT * FROM admins WHERE MID='".$who."'";
    $sql_result=sql($sql);
    if (sqlrows($sql_result)>0) $admin=1;
    return $admin;
}

function get_teacher_courses($who)
{
    global $teacherstable,$coursestable,$sitepath;
    $i=1;
    $ret="";
    $sql="SELECT ".$coursestable.".Title,".$teacherstable.".CID FROM ".$teacherstable.",".$coursestable." WHERE ".$coursestable.".Status!=0 AND ".$teacherstable.".MID='".$who."' AND ".$teacherstable.".CID=".$coursestable.".CID "; //ORDER by ".$coursestable.".CID DESC
    $sql_result=sql($sql);
    if (sqlrows($sql_result)<0) return 1;
    while($res=sqlget($sql_result))
    $ret.=$i++.". <a href=\"".$sitepath."teachers/manage_course.php4?CID=".$res['CID']."\">".$res['Title']."</a><br>\n";
    return $ret;

}

function get_student_courses($who)
{
    $i=1;
    global $studentstable,$coursestable,$sitepath;
    $ret="";
    //  2002 10 01

    $sql="SELECT Title,".$studentstable.".CID FROM ".$studentstable.",".$coursestable." WHERE ".$coursestable.".Status=2 AND ".$studentstable.".MID='".$who."' AND ".$studentstable.".CID=".$coursestable.".CID"; // ORDER by ".$coursestable.".CID

    // end

    $sql_result=sql($sql);
    if (sqlrows($sql_result)<0) return 1;
    while($res=sqlget($sql_result))
    //    echo $i++.". ".$res['Title']."<br>";
    $ret.=$i++.". <a href=\"".$sitepath."teachers/manage_course.php4?CID=".$res['CID']."\">".$res['Title']."</a><br>\n";
    return $ret;
}

function print_error($error)
{
    if($error->stud_courses) echo "Students record not found";
    if($error->teach_courses) echo "Teachers record not found";
    echo "Some Error";
}

function get_course_title($cid)
{
    global $coursestable;
    $sql="SELECT Title FROM ".$coursestable." WHERE CID='".$cid."'";
    $sql_result=sql($sql);
    if (sqlrows($sql_result)<0) return "";
    $res=sqlget($sql_result);
    return $res['Title'];

}

function get_test_title($tid)
{
    $tid = intval($tid);
    $sql="SELECT title FROM test WHERE tid = {$tid}";
    $sql_result=sql($sql);
    if (sqlrows($sql_result)<0) return "";
    $res=sqlget($sql_result);
    return $res['title'];

}

function get_people_title($mid) {
    $mid = intval($mid);
    $res=sql("SELECT People.mid, People.firstname, People.lastname, People.login FROM People WHERE mid = {$mid}");
    if (sqlrows($res)<0) return "";
    $row=sqlget($res);
    return "{$row[lastname]} {$row[firstname]} ({$row[login]})";
}

function get_pid($who,$cid) {

    global $teacherstable;
    $sql = "SELECT PID FROM ".$teacherstable." WHERE MID='".$who."' AND CID='".$cid."'";
    if (sqlrows($sql_result = sql($sql)) == 0) {
        $sql_sub = "SELECT * FROM deans WHERE MID = '$who'";
        if(sqlrows($sql_sub_result = sql($sql_sub)) == 0) {
            return 0;
        }

        return 0;
    }
    //if (sqlrows($sql_result) = 0) return 0;
    $res = sqlget($sql_result);
    return $res['PID'];
}

function get_sid($who,$cid) {
    global $studentstable;
    global $coursestable;

    // 2002 10 01

    $sql="SELECT ".$studentstable.".SID as SID FROM ".$studentstable.",".$coursestable." WHERE ".$studentstable.".MID='".$who."' AND ".$studentstable.".CID='".$cid."' AND ".$coursestable.".CID=".$studentstable.".CID AND ".$coursestable.".Status='2'";

    //end

    if (!$sql_result=@sql($sql)) return 0;
    if (sqlrows($sql_result)<0) return 0;
    $res=sqlget($sql_result);
    return $res['SID'];

}


function forum_id_string()
{
    global $mod_list_table;
    $str="0";
    $sql="SELECT forum_id FROM ".$mod_list_table." WHERE forum_id!=NULL AND Pub='0'";
    // echo $sql;
    if (!$sql_result=sql($sql)) return $str;
    if (sqlrows($sql_result)<0) return $str;
    $res=sqlgetrow($sql_result);
    $str=$res[0];
    while ($res=sqlgetrow($sql_result)) $str.=",".$res[0];
    $str=(empty($str)) ? "0" : $str;
    return $str;
}

// 2002 10 03

function add_stud_tasks($MID,$CID)
{
    global $scheduletable;
    global $vedtable;

    $str=1;

    $sql="SELECT scheduleID.toolParams, scheduleID.SSID FROM scheduleID";
    $res = sql($sql);
    while ($row = sqlget($res)) {
        $toolparams[$row['SSID']] = $row['toolParams'];
    }

    $sql="SELECT DISTINCT scheduleID.SHEID, scheduleID.SSID
          FROM scheduleID
          INNER JOIN schedule ON (schedule.SHEID=scheduleID.SHEID)
          WHERE schedule.CID=".$CID." AND toolParams LIKE '%sAddToAllnew=1%'";

    //fn if (!$sql_result=@sql($sql)) return $str;
    $sql_result = sql($sql);
    if(sqlrows($sql_result) == 0) return $str;
    //if (sqlrows($sql_result)<0) return $str;

    while($res=sqlget($sql_result)) {
        $sql="INSERT INTO ".$vedtable." (SHEID,MID,V_STATUS,toolParams) VALUES (".$res['SHEID'].",".$MID.",'-1','{$toolparams[$res['SSID']]}')";
        sql($sql);
    }
    return $str;
}

function loadtmpl($in)
{

    global $templdir;
    $ret = '';

    $template_file = $templdir . $in;
    $GLOBALS['controller']->substituteTemplate($template_file);
    if (!@is_file($template_file)) {
        return "<h1>Template error</h1>";
    }

    if (get_magic_quotes_gpc()) $ret = stripslashes(implode("",file($template_file)));
    if (get_magic_quotes_runtime()) $ret = stripslashes(implode("",file($template_file)));

    if (!$ret) $str = implode("",file($template_file));

    $str = ($ret)?$ret:$str;
    //замена тултипов
    require_once('lib/classes/ToolTip.class.php');
    while (($tag1 = strpos($str, '<tooltip>')+9) && $tag2 = strpos($str,'</tooltip>')) {
        $tooltip = substr($str,$tag1,$tag2-$tag1);
        $toolTip = new ToolTip();
        $str = str_replace("<tooltip>$tooltip</tooltip>",$toolTip->display($tooltip),$str);
    }

    if ($ret) return $str;

    return parse_t_blocks($str);

}

$ldq = preg_quote('{?');
$rdq = preg_quote('?}');
$cmd = preg_quote('t');

function parse_t_blocks($str) {

    global $ldq, $rdq, $cmd;

    if (preg_match_all(
            "/{$ldq}\s*({$cmd})\s*([^?\}\{]*){$rdq}([^\{\}]*){$ldq}\/\\1{$rdq}/",
            $str,
            $matches
    )){
        foreach ($matches[0] as $key => $match) {
            $str = str_replace($match, _($matches[3][$key]), $str);
        }
    }
    return $str;
}

function loadwords($in)
{
    global $templdir;

    if (!@is_file($templdir.$in)) return "<h1>Words error</h1>";

    $words=file($templdir.$in);

    while(list($key,$val)=each($words))
    {
        if (get_magic_quotes_gpc() || get_magic_quotes_runtime()) {
            $words[$key]=stripslashes(_($val));
        } else {
            $words[$key] = parse_t_blocks($val);
        }
    }

    return  $words;
}

function get_news( $num="", $where="" ) {
    global $newstable;
    global $dean;
    global $adodb;

    $SQL = "SELECT nID, `date`, Title, author, message, lang, `show`, standalone, UNIX_TIMESTAMP(`date`) as date_timestamp FROM ".$newstable;
    if (!$dean) $SQL.=" WHERE `show`> '0'";
    else $SQL.=" WHERE `show`< '2'";
    if ($where) $SQL.=" AND nID = '".$where."'";
    if (defined('ALLOW_SWITCH_2_SIS')) $SQL .= ' AND !application';
    $SQL.= " ORDER BY ".nsort_type()." DESC";
    $result = sql($SQL);
    return $result;
}

function nsort_type()
{
    global $s;

    if (!isset($s['user']['nsort'])) return "nID";
    if (!$s['user']['nsort']) return "nID";
    return "date";
}


function create_new_html($top,$m)
{
    global $s;
    global $default_groups;
    global $strRedirectNoFrames;
    global $_TOP_MENU;
    global $_LEFT_MENU;

    $html=loadtmpl("all-html.html");

    $allheader=loadtmpl("all-header.html");

    $allheader = str_replace("[REDIRECT_NO_FRAMES]", $strRedirectNoFrames, $allheader);

    $strJs = "return;";
    if (is_array($_TOP_MENU)) {
        $strJs = "";
        for ($i = 0; $i < count($_TOP_MENU); $i++) {
            $item = $_TOP_MENU[$i];
            if (is_array($item['dropdown']) && count($item['dropdown'])) {
                $strSalt = randString();
                $strJs .= <<<EOD
                                                \nif (window.mm_menu_{$strSalt}_0) return;
                                                color01 = getColorFromCss('.th5');
                                                color02 = getColorFromCss('.th1');
                                                color03 = getColorFromCss('.color4');
                                                color04 = getColorFromCss('.color5');
                                                window.mm_menu_{$strSalt}_0 = new Menu("root",126,18,"",12,color03,color04,color01,color02,"left","middle",3,0,500,-5,7,true,false,true,0,true,true);
                                                [SUBITEMS]
EOD;
                $strSubItem = "";
                foreach ($item['dropdown'] as $subitem) {
                    $strSubItem .= "
                                                                   mm_menu_{$strSalt}_0.addMenuItem('{$subitem['title']}',\"location='{$subitem['url']}'\");";
                }
                $strJs = str_replace("[SUBITEMS]", $strSubItem, $strJs);
                $_TOP_MENU[$i]['salt'] = $strSalt;
}
        }
        if (isset($strSalt)) $strJs .= "\nmm_menu_{$strSalt}_0.writeMenus();\n";
    }
    $allheader = str_replace("[MM_LOAD_MENUS]", $strJs, $allheader);

    $topmenu = "";
    $menu = "";
    $allmenu = "";
    $words=array();

    $allwords=loadwords("all-words.html");

    if (!is_array($allwords)) return "<h1>Words error</h1>";

    $words['title']=$allwords[0];


    $words['MAINWIDTH']=$allwords[9];

    if ($top) {
        $topmenu = loadtmpl("all-topmenu.html");
        //              $toplinks = (defined("LOCAL_FREE_REGISTRATION") && LOCAL_FREE_REGISTRATION) ? loadtmpl("all-toplinks.html") : "";
        if (isset($_TOP_MENU)) {
            $toplinks = loadtmpl("all-toplinks.html");
            $strTopMenu = "";
            if (is_array($_TOP_MENU) && count($_TOP_MENU)) {
                $arrLinks = array();
                foreach ($_TOP_MENU as $key => $item) {
                    $strJs = (isset($item['salt']) && isset($item['dropdown'])) ? "onMouseOver=\"MM_showMenu(window.mm_menu_{$item['salt']}_0,0,17,null,'link{$key}')\" onMouseOut='MM_startTimeout();'" : "";
                    $arrLinks[] = "<td nowrap><a href='{$item['url']}' class='menu1' id='link{$key}' {$strJs}>{$item['title']}</a></td>";
                }
                $strQlue = "<td width='12' align='center' nowrap>|</td>";
                $strTopMenu = implode($strQlue, $arrLinks);
            }
            $toplinks = str_replace("[TOP_MENU]", $strTopMenu, $toplinks);
        } else {
            $toplinks = loadtmpl("all-toplinks-default.html");
        }

        $words['toplinks']=$toplinks;
        $topactions = (defined("LOCAL_FREE_REGISTRATION") && LOCAL_FREE_REGISTRATION) ? loadtmpl("all-topactions.html") : "";
        $words['topactions']=$topactions;
        $words['logotext'] = (defined("LOCAL_LOGO_TEXT")) ? LOCAL_LOGO_TEXT : "";
        $words['slogan'] = (defined("LOCAL_SLOGAN")) ? LOCAL_SLOGAN : "eLearning Server";
        $words['home']=$allwords[1];
        $words['reg']=$allwords[2];
        $words['course']=$allwords[3];
        $words['lib']=$allwords[4];
        $words['serv']=$allwords[5];
        $words['help']=$allwords[6];
        $words['hello']= (isset($_SESSION['s']['mid'])&&($_SESSION['s']['mid'] !== 0) && (!isset($_GET['exit']))) ? $allwords[7] . "&nbsp;<a href='[PATH]reg.php4' class='menu_top'>[W-uNAME]</a>" : "";
        $words['MAINWIDTH']=$allwords[8];
    }

    if ($m) {
        if ($m == "dean") {
            $strMenu = '
                <table width="85%" border="0" cellspacing="0" cellpadding="0" valign="top"  align="center">
                      <tr><td heigth="10"> &nbsp;</td></tr>
                      ';

            $arrAllowedActions = get_permissions($_SESSION['s']['mid'], PERMISSIONS_DEAN);

            $strExtraWhere = (count($arrAllowedActions)) ? "acid IN (".implode(", ",$arrAllowedActions).")" : "0 > 1";
            $q = "SELECT * FROM actions WHERE {$strExtraWhere} ORDER BY actions.sequence";
            $r = sql($q);

            while ($a = sqlget($r)) {
                $a['name'] = (ord($a['name'])<128) ? ((defined($a['name'])) ? strtolower_custom(constant($a['name'])) : _("кафедры")) : $a['name'];
                $strExit = (strpos($a['name'], _("выход")) !== false) ? "&exit=true" : "";
                $strTmp = $allmenu=loadtmpl("dean-lMenu.html");
                $strTmp = str_replace("[URL]",$a['url'],$strTmp);
                $strTmp = str_replace("[EXIT]",$strExit,$strTmp);
                $strTmp = str_replace("[TITLE]",$a['title'],$strTmp);
                $strTmp = str_replace("[ACID]",$a['acid'],$strTmp);
                $strTmp = str_replace("[NAME]",$a['name'],$strTmp);

                $strMenu .= $strTmp;
            }
            $strMenu .= "</table><br><br>";
            $menu = $strMenu;
            //echo "strMenu: ".$strMenu;

        } else {

            if(!isset($_LEFT_MENU)) {
                $_LEFT_MENU = array("STUDENT" => array( array("url"  => "[PATH]index.php?[SESSID]",
                "name" => _("В НАЧАЛО"),
                "icon" => "[PATH]images/menu/home.gif"),
                array("url"  => "[PATH]schedule.php4?[SESSID]",
                "name" => _("РАСПИСАНИЕ"),
                "icon" => "[PATH]images/menu/shed.gif"),
                array("url"  => "[PATH]ved.php4?[SESSID]",
                "name" => _("РЕЗУЛЬТАТЫ"),
                "icon" => "[PATH]images/menu/progress.gif"),
                array("url"  => "[PATH]test_test.php?[SESSID]",
                "name" => _("ЗАДАНИЯ"),
                "icon" => "[PATH]images/menu/task.gif"),
                array("url"  => "[PATH]guestbook.php4?[SESSID]",
                "name" => _("ОБЪЯВЛЕНИЯ"),
                "icon" => "[PATH]images/menu/message.gif"),
                array("url"  => "[PATH]forum.php4?[SESSID]",
                "name" => _("ВОПРОСЫ-ОТВЕТЫ"),
                "icon" => "[PATH]images/menu/quest.gif"),
                'chat' => array("url"  => "[PATH]chat.php4?[SESSID]",
                "name" => _("ОБСУЖДЕНИЯ"),
                "icon" => "[PATH]images/menu/chat.gif"),
                array("url"  => "[PATH]index.php?[SESSID]exit=true",
                "name" => _("ВЫХОД"),
                "icon" => "[PATH]images/menu/exit.gif")),
                "TEACHER" => array( array("url"  => "[PATH]index.php?[SESSID]",
                "name" => _("В НАЧАЛО"),
                "icon" => "[PATH]images/menu/home.gif"),
                array("url"  => "[PATH]schedule.php4?[SESSID]",
                "name" => _("РАСПИСАНИЕ"),
                "icon" => "[PATH]images/menu/shed.gif"),
                array("url"  => "[PATH]ved.php4?[SESSID]",
                "name" => _("РЕЗУЛЬТАТЫ"),
                "icon" => "[PATH]images/menu/progress.gif"),
                array("url"  => "[PATH]test_test.php?[SESSID]",
                "name" => _("ЗАДАНИЯ"),
                "icon" => "[PATH]images/menu/task.gif"),
                array("url"  => "[PATH]guestbook.php4?[SESSID]",
                "name" => _("ОБЪЯВЛЕНИЯ"),
                "icon" => "[PATH]images/menu/message.gif"),
                array("url"  => "[PATH]forum.php4?[SESSID]",
                "name" => _("ВОПРОСЫ-ОТВЕТЫ"),
                "icon" => "[PATH]images/menu/quest.gif"),
                'chat' => array("url"  => "[PATH]chat.php4?[SESSID]",
                "name" => _("ОБСУЖДЕНИЯ"),
                "icon" => "[PATH]images/menu/chat.gif"),
                array("url"  => "[PATH]index.php?[SESSID]exit=true",
                "name" => _("ВЫХОД"),
                "icon" => "[PATH]images/menu/exit.gif")));
            if (dbdriver != "mysql") {
                unset($_LEFT_MENU['STUDENT']['chat']);
                unset($_LEFT_MENU['TEACHER']['chat']);
            }

            }
            $menu = loadtmpl($m."-lMenu.html");


            $mneu = "";
            if($m == "st")
            $menu .= get_left_menu($_LEFT_MENU['STUDENT']);
            if($m == "teach")
            $menu .= get_left_menu($_LEFT_MENU['TEACHER']);
        }

        $words['MAINWIDTH']=$allwords[8];
    }

    if ($menu!="<h1>Template error</h1>" && !empty($menu)) $allmenu=loadtmpl("all-menu.html");
    else $menu="";


    $html=str_replace("[ALL-HEADER]",$allheader,$html);
    $html=str_replace("[TOP-MENU]",$topmenu,$html);
    $html=str_replace("[ALL-MENU]",$allmenu,$html);
    $html=str_replace("[MENU]",$menu,$html);
    $html=words_parse($html,$words);

    return $html;
}

function get_left_menu($menu) {

    $return_value  = "<TABLE border=\"0\" align=\"center\" cellpadding=\"3\" cellspacing=\"0\">";
    foreach($menu as $key => $value) {
        $return_value .= "
                  <tr class='menu_left'>
                   <td align=\"center\" nowrap><img src=\"".$value['icon']."\" border=0></td>
                   <td align='left' valign=\"middle\">
                    <span id=links class=[SHED_VIEW]>
                     <a href=\"".$value['url']."\">".$value['name']."</a>
                    </span>
                   </td>
                  </tr>";
    }
    $return_value .= "<tr><td colspan='2'><br></td></tr>";
    $return_value .= "</table>";
    return $return_value;
}

function change_level($chl,$ml,$cl)
{
    //    global $s;
    //    if ($s['me']) return $cl;
    $canChange = false;
    switch ($chl) {
        case 1:
            $canChange = is_student($GLOBALS['s']['mid']);
            break;
        case 2:
            $canChange = is_teacher($GLOBALS['s']['mid']);
            break;
        default:
            $canChange = true;
    }
    if ($canChange) {
        checkForFrames($chl);
        if (session_is_registered("boolInFrame")) session_unregister("boolInFrame");
        if (session_is_registered("closeSheduleWindow")) session_unregister("closeSheduleWindow");
        unset($_SESSION['s']['old_mid']);
        if ($ml>=$chl && $chl>0) {
            if ($GLOBALS['controller']->enabled) {
                $GLOBALS['controller']->persistent_vars->set('profile_current', $_GET['profile']);
            }
            return $chl;
        }
    }
    hack_log(1,"change level");
    return $ml;

}


function show_cur_html($perm) {
    $html = create_new_html("top","st");
    if(2==$perm) {
        $html=create_new_html("top", "teach");
    }
    elseif (3==$perm) {
        $html=create_new_html("top","dean");
    }
    elseif (4==$perm) {
        $html=create_new_html("top","admin");
    }
    return $html;
}


function path_sess_parse($html)
{
    global $sess;
    global $sessf;
    global $sitepath;
    global $self;

    $html=str_replace("[SESSID]",$sess,$html);
    $html=str_replace("[FSESSID]",$sessf,$html);
    $html=str_replace("[PATH]",$sitepath,$html);
    $html=str_replace("[PAGE]",$self,$html);
    return $html;
}

function words_parse($html,$words,$k="W-") {
    if (is_array($words)) {
        while(list($key,$val)=each($words)) {
            $val=str_replace(array("\r\n","\n"),"",$val);
            if (($key == "uNAME") && !strlen($val)) {
                $val = "["._("имя")."&nbsp;"._("не")."&nbsp;"._("задано")."]";
            }
            $html=str_replace("[".$k.$key."]",$val,$html);
        }
    }
    return $html;
}

/*
function news_parse($html,$words)
{
if (is_array($words))
{
while(list($key,$val)=each($words))
{
$html=str_replace("[N-".$key."]",$val,$html);
}
}
return $html;
}
*/
function get_announce($num,$CIDs)
{
    //    global $guestbook;
    $guestbook="posts3";


    if (is_array($CIDs))
    {
        reset($CIDs);

        list($key,$val)=each($CIDs);
        $SQL = "SELECT postid, name, cid, email, text, UNIX_TIMESTAMP(posted) as posted FROM $guestbook WHERE CID='".$val."'";
        while (list($key,$val)=each($CIDs)) $SQL.=" OR CID='".$val."'";
        $SQL.="ORDER BY posted DESC LIMIT ".$num;
        $result=sql($SQL);

    }elseif("admin"==$CIDs){
        $SQL = "SELECT postid, name, cid, email, text, UNIX_TIMESTAMP(posted) as posted FROM $guestbook WHERE CID='0'";
        $result=sql($SQL);
    }
    else exit("error cids");

    return $result;
}

function tosql($str)
{
    $str=str_replace("#","",$str);
    if (!get_magic_quotes_gpc() && !get_magic_quotes_runtime())
    {
        $str=addslashes($str);
    }
    return $str;
}

function cCourse_links($perm)
{
    global $s;

    $links="";
    $i=1;
    $sql="";
    $line=loadtmpl("st-links.html");

    if (1==$perm && is_array($s['skurs'])){

        reset($s['skurs']);

        list($key,$val)=each($s['skurs']);
        $sql="SELECT CID, Title FROM Courses WHERE CID='".$val."'";
        while (list($key,$val)=each($s['skurs'])) $sql.=" OR CID='".$val."'";

    }elseif (2==$perm && is_array($s['tkurs'])){

        reset($s['tkurs']);
        list($key,$val)=each($s['tkurs']);
        $sql="SELECT CID, Title FROM Courses WHERE CID='".$val."'";
        while (list($key,$val)=each($s['tkurs'])) $sql.=" OR CID='".$val."'";

    }else return loadtmpl("st-nolinks.html");
    $sql .= " ORDER BY Title";
    if (!$result=sql($sql)) return $links;
    if (sqlrows($result)<1) return $links;

    while ($row=sqlget($result))
    {


        $tmp=$line;
        $tmp=str_replace("[ID]",$i,$tmp);
        $tmp=str_replace("[TITLE]",$row['Title'],$tmp);
        $tmp=str_replace("[CID]",$row['CID'],$tmp);
        $links.=$tmp;
        $i++;
    }

    return $links;
}

function login_chek($s,$mod)
{
    // нет в сессии MID пользователя
    if (!isset($s['mid'])) return false;

    // нет в сессии уровня доступа пользователя
    if (!isset($s['perm'])) return false;

    // уровня доступа пользователя не хватает для просмотра данной страницы
    if ($mod>$s['perm']) return false;

    return true;
}

function login_error()
{
    global $sitepath;
    header("location:".$sitepath);
    exit();
}


function show_tb($type=0)
{

    global $s;
    static $tb;
    if (is_array($tb))  {
        $_SESSION['s']=$s;
//      return $tb[1];
        $GLOBALS['controller']->terminate();
        exit();
    }
    $html=create_new_html("top","all");
    if (isset($s['perm'])) {
        $html=show_cur_html($s['perm']);
        $words['ucLINKS']=cCourse_links($s['perm']);
        if (isset($s['user']['fname'])) $words['uNAME']=$s['user']['fname'];
        $html=words_parse($html,$words);
    }

    $html=path_sess_parse($html);
    $tb=explode("[ALL-CONTENT]",$html);
    if ($type)  {
        return $html;
    }
//  return "";
    return $tb[0];
}

function day($i)
{
    if (1==strlen($i)) $i="0".$i;
    return $i;
}

function month($i)
{
    $months=loadwords("all-months.html");
    return $months[$i-1];
}

function hack_log($i,$s)
{
    //    echo "Hack detected: ".$s;
}

function select_day($b_date="")
{
    $b_day="";
    for($j="1";$j<32;$j++)
    {
        $b_temp="";

        if ($b_date==$j)  $b_temp="selected";

        $b_day.="<option value=".day($j)." ".$b_temp.">".day($j)."</option>\n";

    }
    return $b_day;

}

function select_month($b_date="")
{
    $b_month="";
    for($j="1";$j<13;$j++)
    {
        $b_temp="";

        if ($b_date==$j)  $b_temp="selected";

        $dummyMonth = strpos(month($j), 'ь')||strpos(month($j), 'й')?str_replace(array('ь','й'),'я',month($j)):month($j).'а';

        $b_month.="<option value=".day($j)." ".$b_temp.">".$dummyMonth."</option>\n";
    }
    return $b_month;
}

function select_year($b_date="")
{
    $b_year="";
    for($j=date("Y")-1;$j-8<date("Y");$j++)
    {
        $b_temp="";

        if ($b_date==$j)  $b_temp="selected";

        $b_year.="<option value=".$j." ".$b_temp.">".$j."</option>\n";
    }
    return $b_year;
}

function select_author($author) {
    $authors = array("", "-~home~-","-~reg~-","-~courses~-",
    "-~lib~-","-~about~-","-~help~-","-~faq~-");
    $return_value = "";
    foreach($authors as $key => $value ) {
        if($author == $value) {
            $return_value .= "\n
                    <option value='$value' selected>".get_page_name_by_id($value)."\n";
        }
        else {
            $return_value .= "\n
                    <option value='$value'>".get_page_name_by_id($value)."\n";
        }
    }

    return $return_value;
}

function select_show($show) {
    if($show == 1) {
        return "<input type='checkbox' name='show' value='1' checked>";
    }
    else {
        return "<input type='checkbox' name='show' value='1'>";
    }
}

function get_page_name_by_id($str = "") {
    switch ($str) {
        case "":
        return _("какой-то страницы");
        break;
        case "-~home~-":
        return _("стартовой страницы");
        break;
        case "-~reg~-":
        return _("страницы регистрации");
        break;
        case "-~courses~-":
        return _("страницы курсов");
        break;
        case "-~lib~-":
        return _("страницы библиотеки");
        break;
        case "-~about~-":
        return _("страницы о сервере");
        break;
        case "-~help~-":
        return _("страницы помощи");
        break;
        case "-~faq~-":
        return "FAQ";
        break;
    }
}

function v_text($str, $boolAllowHTML = false)
{
    if (!$boolAllowHTML) $str=strip_tags($str);
    //if (!$boolAllowHTML) $str=htmlspecialchars($str);
    //if (!$boolAllowHTML) $str = htmlentities($str,ENT_QUOTES);
    $str=nl2br($str);
    if (!get_magic_quotes_gpc()) {
        $str=addslashes($str);
    }
    return $str;
}

function v_year($str)
{
    return intval($str);
}
function v_month($str)
{
    $str=intval($str);
    $str=day($str);
    return $str;
}
function v_day($str)
{
    $str=intval($str);
    $str=day($str);
    return $str;
}
function v_hours($str)
{
    $str=intval($str);
    $str=day($str);
    return $str;
}
function v_min($str)
{
    $str=intval($str);
    $str=day($str);
    return $str;
}

function printtmpl($html) {
    global $s,$words;

    $html=words_parse($html,$words);

    $html=path_sess_parse($html);

//  $_SESSION['s']=$s;
    echo $html;
//  s_timeprint();
    $GLOBALS['controller']->terminate();
}

function getName($mid) {
    $res=sql("SELECT LastName, FirstName, Login FROM People WHERE MID='$mid'","err_p1");
    return sqlres($res,0,'LastName')." ".sqlres($res,0,'FirstName')." (".sqlres($res,0,'Login').")";
}
function helptitle($title,$a,$lnk="")
{
    return "<a href=\"".$lnk."\" title=\"".$title."\" onclick=\"return ".(($lnk) ? "true": "false")."\">".$a."</a>";
}

function getpeoplename( $mid ){
    if( $mid > 0 ){
        $rq="SELECT People.MID as MID, People.Login as login, People.LastName as lname, People.FirstName as fname
                 FROM People
                 WHERE MID=$mid";
        $res=sqlval( $rq,"admEr02");
        $name=$res[lname]." ".$res[fname];
        if (empty($res[lname]) && empty($res[fname])) $name = $res[login];
    }
    return( $name );
}

function add_people_to_group_by_formula($sheid, $mid, $formulagr_id = "") {

    $query = "SELECT * FROM schedule WHERE SHEID=$sheid";
    $result = sql($query);
    $row = sqlget($result);
    $type_sheid = $row['typeID'];

    //apply groups by V_STATUS
    $query = "SELECT * FROM scheduleID WHERE SHEID = $sheid AND MID=$mid";
    $result = sql($query);
    $row_sheid = sqlget($result);

    if($row_sheid['V_STATUS'] != -1) {
        if($formulagr_id == "") {
            $tool_params = explode(";",$row['params']);

            foreach($tool_params as $key => $value) {
                if(strpos($value, "formula_group_id") !== false) {
                    $tmp = explode("=", $value);
                    $formulagr_id = intval($tmp[1]);
                }
            }
        }
        if(isset($formulagr_id) && $formulagr_id) {

            $result = sql("SELECT * FROM formula WHERE id = $formulagr_id");

            $formula = sqlget($result);
            $formula_array = explode(";", $formula['formula']);
            foreach($formula_array as $key => $value) {
                if(trim($value) == "") {
                    continue;
                }
                $tmp = explode(":", $value);
                $interval = $tmp[0];
                $part_of_grname = $tmp[1];
                $grname = $formula['name'].":".$row['title'].":".$part_of_grname;
                $tmp = explode("-", $interval);
                $min_value = $tmp[0];
                $max_value = $tmp[1];
                if ($max_value == NULL) $max_value = $min_value;
                if(($row_sheid['V_STATUS'] >= $min_value)&&($row_sheid['V_STATUS'] <= $max_value)) {
                    $in_group[$grname] = 1;
                }
                else {
                    $in_group[$grname] = 0;
                }
            }
            if(is_array($in_group))
            foreach($in_group as $key => $value) {
                //Проверяем суедствует ли такая группа если не существует то добавляем
                $query = "SELECT * FROM groupname WHERE name = '$key'";
                $result = sql($query);
                if(sqlrows($result) == 0) {
                    $query = "INSERT INTO groupname (cid, name) VALUES (".$row['CID'].", '$key')";
                    $result = sql($query);
                    $gid = sqllast($result);
                }
                else {
                    $row_gid = sqlget($result);
                    $gid = $row_gid['gid'];
                }
                //Проверяем человека существует ли он в этой группе
                $query = "SELECT * FROM groupuser WHERE mid = ".$mid." AND cid=".$row['CID']." AND gid = $gid";
                $result = sql($query);
                if(sqlrows($result) == 0) {
                    if($value == 0) {
                    }
                    else {
                        $query = "INSERT INTO groupuser (mid, cid, gid) VALUES (".$mid.", ".$row['CID'].", ".$gid.")";
                        $result = sql($query);
                    }
                }
                else {
                    if($value == 0) {
                        $query = "DELETE FROM groupuser WHERE mid = ".$mid." AND cid = ".$row['CID']." AND gid = ".$gid;
                        $result = sql($query);
                    }
                    else {
                    }
                }
            }
        }
    }
}
/*
function peopleSelect( $table="", $mid=0 ) {
// table=teachers к примеру, mid - кто должен быть селектирован сразу

$html="";
if( $table!=""){
$cond=" WHERE People.MID=$table.MID ";
$from=", $table ";
}
$rq="SELECT People.MID as MID, People.Login as login, People.LastName as lname, People.FirstName as fname
FROM People $from
$cond
ORDER BY login";
//      echo $rq;
$res=sql( $rq,"admEr02");
if (!sqlrows($res)) return "<option>" . _('не найдено') . "</option>";
while ($row=sqlget($res)) {
if( $row['MID'] == $mid ) $sel=" SELECTED "; else $sel="";
$html.="<option value=".$row['MID']." $sel>".$row['login'].": ".$row['lname']." ".$row['fname']."</option>";
}
return $html;
}

*/
function peopleSelect($table="", $mid=0, $exclTable="", $boolShowAdmin = true, $disableFilter=false, $where='') {
    $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);

    $html="";
    $sqlExtraWhere = (!$boolShowAdmin) ? " AND People.`MID` != '1' " : " ";
    $tables = (!strlen($table)) ? array("admins", "deans", "Teachers", "Students", 'reviewers', 'developers', 'methodologist') : array($table);
    $arrMids = array();
    if($exclTable != "") {
        $r = sql("SELECT * FROM {$exclTable}", "err343223");
        while($a = sqlget($r)) {
            if ($disableFilter || $peopleFilter->is_filtered($a['MID']))
            $arrMids[] = $a['MID'];
        }
    }
    $strMids = implode(",", $arrMids);
    if (strlen($strMids)) $sqlExtraWhere .= " AND People.`MID` NOT IN ({$strMids}) ";
    foreach ($tables as $table) {
        $sql = "
               SELECT DISTINCT
                People.MID as MID, People.Login as login, People.LastName as lname, People.FirstName as fname, People.Patronymic
               FROM
                People
                INNER JOIN {$table} ON (People.`MID` = {$table}.`MID`)
               WHERE 1=1
                {$sqlExtraWhere}
                {$where}
               ORDER BY
               lname, fname, login";
        $res=sql($sql,"admEr02");
        while ($row=sqlget($res)) {
            $strSel = ($row['MID'] == $mid) ? "selected" : "";
            if ($disableFilter || $peopleFilter->is_filtered($row['MID']))
            $rows[]="<option value=".$row['MID']." $strSel>".$row['lname']." ".$row['fname']." ".$row['Patronymic']." (".$row['login'].")</option>\n";
        }
    }
    if (!count($rows)) return "<option>" . _("нет пользователей") . "</option>";
    $rows = array_unique($rows);
    foreach ($rows as $row) {
        $html.=$row;
    }
    return $html;
}

function getMids($table = "People", $array = array()) {
    $arrReturn = $array;
    $sql = "SELECT DISTINCT MID FROM {$table}";
    $res = sql($sql);
    while ($row = sqlget($res)) {
        if(!in_array($row['MID'], $arrReturn)) {
            $arrReturn[] = $row['MID'];
        }
    }
    return $arrReturn;
}

function peopleSelect_2($tables = array(), $mid = 0) {
    $html = "";
    $mids = array();
    foreach ($tables as $table) {
        $mids = getMids($table, $mids);
    }
    $where = "People.MID IN ( " . (count($mids) ? implode(", ", $mids) : "0") .")\n";
    $sql = "SELECT DISTINCT
            People.MID as MID, People.Login as login, People.LastName as lname, People.FirstName as fname, People.Patronymic
            FROM People
            WHERE {$where}
            ORDER BY `People`.LastName";

    $res = sql($sql);
    if (!sqlrows($res)) {
        return "<option>--"._("нет пользователей")."--</option>";
    }
    while ($row = sqlget($res)) {
        $strSel = ($row['MID'] == $mid) ? "selected" : "";
        $html .= "<option value=".$row['MID']." $strSel>".$row['lname']." ".$row['fname']." " .$row['Patronymic']. " (".$row['login'].")</option>\n";
    }

    $html .= "<option>--- </option>";
    $sql = "SELECT DISTINCT
            People.MID as MID, People.Login as login, People.LastName as lname, People.FirstName as fname, People.Patronymic
            FROM People
            WHERE People.MID NOT IN ('".join("','",$mids)."')
            ORDER BY `People`.LastName";
    $res = sql($sql);
    while ($row = sqlget($res)) {
        $strSel = ($row['MID'] == $mid) ? "selected" : "";
        $html .= "<option value=".$row['MID']." $strSel>".$row['lname']." ".$row['fname']." " .$row['Patronymic']. " (".$row['login'].")</option>\n";
    }


    return $html;
}

function selCourses($aCur,$selCur,$quiet = false,$filter=false,$type = 0) {
    //fn $sel="<option value=\"0\" ";
    //echo $selCur;
    //fn if($selCur==0) $sel.= "selected";
    //fn $sel.=">--выберите курс--</option>";
    $return = array();
    if (is_array($aCur)){
        $res=sql("SELECT * FROM Courses WHERE `CID` IN ('".implode("','",$aCur)."') AND `type` = '".(int) $type."' ORDER by Title ASC","funEr01");
        if ($filter) $courseFilter = new CCourseFilter($GLOBALS['COURSE_FILTERS']);
        while ($row=sqlget($res)) {

            if ($filter && !$courseFilter->is_filtered($row['CID'])) continue;
            $short = (strlen($row['Title']) > 50) ? substr($row['Title'], 0, 50) . '...' : $row['Title'];
            $sel.="<option value=\"".$row['CID']."\" ";
            if (($selCur==$row['CID'])&&($selCur!=0)) $sel.="selected" ;
            $sel.=" title='".$row['Title']."'>".$short."</option>";
            $return["{$row['CID']}"] = $row['Title'];
        }
    }
    return ($quiet) ? $return : $sel;
}

function selGroups($selCur) {
    $sel="";
    $res=sql("SELECT * FROM groupname WHERE cid='-1' ORDER by name ASC","funEr01");
    while ($row=sqlget($res)) {

        $sel.="<option value=\"".$row['gid']."\" ";
        if ($selCur==$row['gid']) $sel.="selected" ;
        $sel.=">".$row['name']."</option>";
    }
    return $sel;
/*
    $res=sql("SELECT * FROM cgname ORDER by cgid ASC","funEr01");
    while ($row=sqlget($res)) {

        $sel.="<option value=\"".$row['cgid']."\" ";
        if ($selCur==$row['cgid']) $sel.="selected" ;
        $sel.=">".$row['name']."</option>";
    }
    return $sel;
*/
}

function selGrRestricted($cid, $cgid = 0, $prompt = 1) {
    $sel="";
    $strSelected = (!$cgid) ? "selected" : "";
    if ($prompt) $sel.="<option value=\"0\" {$strSelected}>--- "._("выберите группу")." ---</option>\n";
    $res=sql("SELECT * FROM cgname ORDER BY cgid","funEr01");
    while ($row=sqlget($res)) {
        $strSelected = ($row['cgid'] == $cgid) ? "selected" : "";
        $sel.="<option value=\"".$row['cgid']."\" {$strSelected}>".$row['name']."</option>\n";
    }
    return $sel;
}


function selAutoGroups($selCur, $prompt = 1) {
    $sel="";
    $res=sql("SELECT * FROM groupname ORDER by gid ASC","funEr01");
    if ($prompt) $sel.="<option value=\"0\" {$strSelected}>--- "._("выберите группу")." ---</option>\n";
    while ($row=sqlget($res)) {
        $sel.="<option value=\"".$row['gid']."\" ";
        if ($selCur==$row['gid']) $sel.="selected" ;
        $sel.=">".$row['name']."</option>";
    }
    return $sel;
}

function selPmGroups($selCur, $type = "dean") {
    $sel="";
    $res=sql("SELECT * FROM permission_groups WHERE type = '$type' ORDER by pmid ASC","funEr01");
    while ($row=sqlget($res)) {
        $sel.="<option value=\"".$row['pmid']."\" ";
        if ($selCur==$row['pmid']) $sel.="selected" ;
        $sel.=">".$row['name']."</option>";
    }
    return $sel;
}



function getCommonGroup()
{
    $q = "
                        SELECT
                          `groupuser`.gid,
                          COUNT(DISTINCT People.`MID`) AS num_stud
                        FROM
                          People
                          INNER JOIN groupuser ON (People.`MID` = groupuser.`mid`)
                          INNER JOIN Students ON (People.`MID` = Students.`MID`)
                        GROUP BY
                          `groupuser`.gid
                        ORDER BY
                          num_stud DESC
                   ";
    $r = sql($q);
    if ($a = sqlget($r)){
        return $a['gid'];
    } else {
        return 0;
    }

}

function getDeansOptions() {
    global $optionstable;
    $sel=array();
    $sel['email']=getField("OPTIONS","value","name","dekanEMail");
    $sel['name']=getField("OPTIONS","value","name","dekanName");
    return $sel;
}

function getPass( $mid ){
    $res=sql("SELECT Password FROM People WHERE `MID`='".$mid."'","funEr02");
    $pl=sqlget($res);
    sqlfree( $res );
    return( $pl['Password'] );
}

function getRegNums( $mid ){
    // возвращает число регистрация на курсах человека в качестве студента и преподавателя
    //                 $sql="SELECT * from People, Students  WHERE Students.MID=People.MID"; break;
    //                 $sql="SELECT * from graduated, People  WHERE graduated.MID=People.MID"; break;
    //                 $sql="select * from People, claimants  WHERE claimants.MID=People.MID AND claimants.Teacher=0"; break;
    $ret=0;
    $sql="SELECT * FROM People WHERE MID='{$mid}'";
    $res = sql($sql);
    return( sqlrows($res) );
}

function getPLE( $mid, $newpass ) {
    // пишет в базу новый случайный пароль
    // возвращает структуру с паролем, мылом, логином чела
    // генерировать пароль надо когда
    // - впервые записывается кудато  (т.е. count(mid)==0)
    // - забыл пароль  newpass=1
    // - меняет пароль newpass=2
    //
    global $teach;
    $pl=array();
    if( $newpass==0 ){
        //$num=sqlval("SELECT COUNT(*) FROM People WHERE mid=$mid","errFL867");
        $num=getRegNums( $mid );
        if($num == 0) {
            $newpass=1; // если раньше не было человека то сгенерировать пароль
        }
    }

    if ( $newpass ) {
        if($newpass==1) {
            $Password=randString(7);
        }
        else {
            $Password=$newpass;
        }
        $res=sql("UPDATE `People` SET Password=PASSWORD('".$Password."') WHERE `MID`='".$mid."'","funEr02P");
    }
    else {
        $Password=_("не изменился, используйте старый пароль")." (not updated)";
    }
    $res=sql("SELECT Login, Email FROM People WHERE `MID`='".$mid."'","funEr02");
    //  if( $pl['Password']==""){
    //    $Password=randString(7);
    //     echo "<H1>$Password</H1>";
    //  }
    if ( sqlrows( $res ) > 0 ) {
        $pl=sqlget($res);
        $pl['Password']=$Password;
    }
    return $pl;
}

function getStCol($gid, $table = "Students") {
    global $freestud;
    //   $sql="SELECT COUNT(*) FROM Students WHERE cgid=".$cgid." AND CID=".$cid." ORDER BY cgid";
    //   $sql="SELECT MID FROM Students WHERE cgid=".$cgid." ORDER BY cgid";

    switch ($table) {
        case "Students":
        $sql = "
                                SELECT
                                  COUNT(DISTINCT People.`MID`) AS num_students_group
                                FROM
                                  People
                                  INNER JOIN Students ON (Students.`MID` = People.`MID`)
                            WHERE Students.cgid=".$gid."
                     ";
        break;
        case "groupuser":
        $sql = "
                                SELECT
                                  COUNT(DISTINCT People.`MID`) AS num_students_group
                                FROM
                                  People
                                  INNER JOIN Students ON (Students.`MID` = People.`MID`)
                                  INNER JOIN groupuser ON (groupuser.`mid` = People.`MID`)
                            WHERE groupuser.gid='{$gid}' AND groupuser.cid=-1
                     ";
        break;
    }

    //   $res=sql($sql);
    //   $groupuser=sqlrows($res);
    $res = sql($sql);
    $row = sqlget($res);
    $groupuser = $row['num_students_group'];

    //   if (isset($freestud)) $freestud=$freestud-$groupuser;
    return $groupuser;
}

function showSortImg($html,$num) {
    global $s;
    $imgname="[SORTIMG".$num."]";
    $imgpath="<img src='[PATH]images/sort_".((2==$s[user][corder]) ? "up" : "down").".gif' border=0>";
    $html=str_replace($imgname,$imgpath,$html);
    for($i=1;$i<10;$i++) $html=str_replace("[SORTIMG".$i."]","",$html);
    return $html;
}


function selGr($cid) {
    $sel="";
    $sel.="<option value=\"1\" selected>"._("--- отметить всех ---")."</option>\n";
    $sel.="<option value=\"0\">"._("--- снять всех ---")."</option>\n";
    //$res=sql("SELECT * FROM cgname ORDER BY cgid","funEr01");
    //while ($row=sqlget($res)) $sel.="<option value=\"d".$row['cgid']."\">".$row['name']."</option>\n";
    $res=sql("SELECT * FROM groupname WHERE `cid`='".$cid."' ORDER BY gid", "funEr01");
    if (sqlrows($res)) {
        $sel.="<option value='-1'>---</option>";
    }
    while ($row=sqlget($res)) $sel.="<option value=\"g".$row['gid']."\">".$row['name']."</option>\n";
    //$res=sql("SELECT * FROM groupname WHERE `cid`='-1' ORDER BY gid", "funEr01");
    $res = sql("SELECT DISTINCT groupname.*
            FROM groupname
            INNER JOIN groupuser ON (groupuser.gid=groupname.gid)
            INNER JOIN Students ON (groupuser.mid=Students.MID)
            WHERE groupname.cid='-1' AND Students.CID='".(int) $cid."'");
    if (sqlrows($res)) {
        $sel.="<option value='-1'>---</option>";
    }
    while ($row=sqlget($res)) $sel.="<option value=\"g".$row['gid']."\">".$row['name']."</option>\n";
    return $sel;
}

function selGrved( $cid, $gr, $quiet = false ) {
    $return = array();
    $sel = "";
    $sel="<option value=-1 ";
    if($gr==-1) $sel.="selected";
    $sel.=">".STR_OPTIONS_ALL."</option>";
//    $sel.="<option value=\"0\" ";
//    if ($gr=="0") $sel.="selected" ;
//    $sel.=">--- все ---</option>";
    $gr = (string)$gr;
    //     $sel.="<option value=-1>--- FILTER ---</option>";
    // деканские группы

    /*
    $res=sql("SELECT * FROM cgname ORDER BY name","funEr01");
    while ($row=sqlget($res)) {
        $sel.="<option value=\"d".$row['cgid']."\" ";
        if ($gr=="d".$row['cgid']) $sel.="selected" ;
        $sel.=">".$row['name']."</option>";
        $return["d".$row['cgid']] = $row['name'];
    }
    $sel.="<option value='-1'>---</option>";
    */

    /*       $sel.="<option value=\"0\" ";
    if ($gr=="0") $sel.="selected" ;
    $sel.=">--- ALL ---</option>";
    */
    // деканские Но пересекающиеся

    $groupFilter = new CGroupFilter_Department();
    if ($GLOBALS['s']['perm']>=3) {
        if (APPLICATION_BRANCH=='academic') {
            $res=sql("
            SELECT groupname.name, groupname.gid FROM groupname
            INNER JOIN departments_groups ON (groupname.gid = departments_groups.gid)
            INNER JOIN departments ON (departments_groups.did = departments.did)
            WHERE `CID`='-1' AND departments.mid={$GLOBALS['s']['mid']}
            AND departments.application = '".DEPARTMENT_APPLICATION."' ORDER BY groupname.name","funEr01");
            while ($row=sqlget($res)) {
                if (!$groupFilter->is_filtered($row['gid'])) {
                        continue;
                }
                $is_hred = true;
                $sel.="<option value=\"g".$row['gid']."\" ";
                if ($gr=="g".$row['gid']) $sel.="selected" ;
                $sel.=">".$row['name']."</option>";
                $return["g".$row['gid']] = $row['name'];
            }

            if (!$is_hred) {
                $sql = "SELECT groupname.name, groupname.gid
                        FROM groupname
                        WHERE cid='-1'
                        ORDER BY groupname.name";
                $res = sql($sql);
                while ($row=sqlget($res)) {
                    if (!$groupFilter->is_filtered($row['gid'])) {
                        continue;
                    }
                    $sel.="<option value=\"g".$row['gid']."\" ";
                    if ($gr=="g".$row['gid']) $sel.="selected" ;
                    $sel.=">".$row['name']."</option>";
                    $return["g".$row['gid']] = $row['name'];
                }
            }
        } else {
            $sql = "SELECT groupname.name, groupname.gid
                    FROM groupname
                    WHERE cid='-1'
                    ORDER BY groupname.name";
            $res = sql($sql);
            while ($row=sqlget($res)) {
                if (!$groupFilter->is_filtered($row['gid'])) {
                        continue;
                }
                $sel.="<option value=\"g".$row['gid']."\" ";
                if ($gr=="g".$row['gid']) $sel.="selected" ;
                $sel.=">".$row['name']."</option>";
                $return["g".$row['gid']] = $row['name'];
            }
        }
    }

    if ($GLOBALS['s']['perm']==2) {
        // преподавательские группы
        $res=sql("SELECT * FROM groupname WHERE cid='".$cid."' ORDER BY name","funEr01");
        while ($row=sqlget($res)) {
            if (!$groupFilter->is_filtered($row['gid'])) {
                        continue;
                }
            $sel.="<option value=\"g".$row['gid']."\" ";
            if ($gr=="g".$row['gid']) $sel.="selected";
            $sel.=">".$row['name']."</option>";
            $return["g".$row['gid']] = $row['name'];
        }
        $sel.="<option value='-1'>---</option>";

        $sql = "SELECT DISTINCT groupname.*
                FROM groupname
                INNER JOIN groupuser ON (groupuser.gid=groupname.gid)
                INNER JOIN Students ON (groupuser.mid=Students.MID)
                WHERE groupname.cid='-1' AND Students.CID='".(int) $cid."'";
        $res = sql($sql);
        while ($row=sqlget($res)) {
            if (!$groupFilter->is_filtered($row['gid'])) {
                        continue;
                }
            $sel.="<option value=\"g".$row['gid']."\" ";
            if ($gr=="g".$row['gid']) $sel.="selected";
            $sel.=">".$row['name']."</option>";
            $return["g".$row['gid']] = $row['name'];
        }
    }

    if ($GLOBALS['s']['perm']==1) {
        $res=sql("SELECT DISTINCT groupname.*
                  FROM groupname
                  INNER JOIN groupuser ON (groupuser.gid=groupname.gid)
                  WHERE groupuser.mid='".$GLOBALS['s']['mid']."'
                  ORDER BY groupname.name");
        while ($row=sqlget($res)) {
            if (!$groupFilter->is_filtered($row['gid'])) {
                        continue;
                }
            $sel.="<option value=\"g".$row['gid']."\" ";
            if ($gr=="g".$row['gid']) $sel.="selected";
            $sel.=">".$row['name']."</option>";
            $return["g".$row['gid']] = $row['name'];
        }
    }

    return ($quiet) ? $return : $sel;

}

function grArray($cid) {
    $sel="";
    $res=sql("SELECT * FROM cgname","funEr01");
    while ($row=sqlget($res)) {
        $resin=sql("SELECT MID FROM Students WHERE cgid='".$row['cgid']."' AND CID='".$cid."'","funEr01");
        $sel.=" var d".$row['cgid']." = new Array(".sqlrows($resin).");\n";
        $i=1;
        while ($rowin=sqlget($resin)) {
            $sel.="d".$row['cgid']."[".$i."] = ".$rowin['MID'].";\n";
            $i++;
        }
    }

    $res=sql("SELECT * FROM groupname WHERE `CID`='".$cid."' OR cid='-1' ORDER BY gid","funEr01");
    while ($row=sqlget($res)) {
        $resin=sql("SELECT mid FROM groupuser WHERE gid='".$row['gid']."' ","funEr01");
        $sel.=" var g".$row['gid']." = new Array(".sqlrows($resin).");\n";
        $i=1;
        while ($rowin=sqlget($resin)) {
            $sel.="g".$row['gid']."[".$i."] = ".$rowin['mid'].";\n";
            $i++;
        }
    }

    return $sel;
}

function MIDgroupArray($mid,$cid) {
    $sql="SELECT groupuser.mid as mid, groupname.gid as gid, groupname.name as gname FROM groupuser, groupname WHERE groupuser.mid='".$mid."' AND groupuser.gid=groupname.gid AND (groupname.cid='".$cid."' OR groupname.cid = '-1')";
    if ($res=@sql($sql,"VQTerLast"))
    while ($row = sqlget($res)) {
        $rgroup[$row['gid']]=$row['gname'];
    }
    //$rgroup['dean']=sqlvalue("SELECT name FROM cgname, Students WHERE cgname.cgid=Students.cgid AND MID='$mid' AND CID='$cid'","err1");
    return $rgroup;
}

// function retrun value of all claimants on the course
function gAbNum($CID) {
    $sql="SELECT COUNT(DISTINCT claimants.MID) FROM claimants,
                 People WHERE claimants.CID='".$CID."' AND
                 claimants.Teacher='0' AND
                 People.MID=claimants.MID";
    $res=sqlvalue($sql,1,1);
    return $res;
}

// function retrun value of all STYDENTS on the course
function gStudNum($CID) {
    $sql="SELECT COUNT(*) FROM Students,
                 People WHERE Students.CID='".$CID."' AND
                 People.MID=Students.MID";
    $res=sqlvalue($sql,1,1);
    return $res;
}

function gGroupNum($CID) {
    $sql="SELECT COUNT(*) FROM groupname WHERE CID='".$CID."'";
    $res=sqlvalue($sql,1,1);
    return $res;
}


// function retrun value of all non moder answers on the course
function gModerNum($CID) {
    $cnt1=sqlvalue("SELECT COUNT(*) FROM seance WHERE cid = ".$CID." AND bal IS NULL","err1");
    //fn $cnt2=sqlvalue("SELECT COUNT(*) FROM seance WHERE cid = ".$CID." AND bal IS NOT NULL","err1");
    //fn return $cnt1+$cnt2;
    return $cnt1;
}

function showSelectGroups( $cid, $filter="" ) {
    //

    /*         $res=sql("SELECT * FROM cgname
    ","funEr01");
    while ($row=sqlget($res)) {
    $tmp.="<input type=checkbox name=".$row['cgid']." value=".$row['cgid'].">";
    $tmp.=$row['name'];
    $tmp.="</input>";
    }
    */

    // деканские Но пересекающиеся
    /*         if( $filter!=""){
    $tmp.="FILTER:";
    foreach( $filter as $cond) $tmp.=$cond.", ";

    }  */

    $res=sql("SELECT * FROM groupname
                     WHERE groupname.cid=$cid OR groupname.cid=-1
                     ORDER BY groupname.name","funEr__01");
    //         $i=0;
    while ($row=sqlget($res)) {
        if( isset( $filter[ $row['gid'] ] ) ) $ch="checked"; else $ch="";
        $tmp.="<input type=checkbox name=FILTER[".$row['gid']."] value=".$row['gid']." ".$ch.">";
        if( $row[cid]==-1)
        $tmp.="<B>".$row['name']."</B>";
        else
        $tmp.=$row['name'];
        $tmp.="</input>";
    }

    return $tmp;

}

function showGroups( $mid ){

    //  SELECT * FROM  WHERE mid=$mid

    //   $sel="";
    // деканские группы
    $res=sql("SELECT * FROM cgname, Students
                            WHERE Students.mid=$mid AND Students.cgid=cgname.cgid
                            ","funEr01");
    while ($row=sqlget($res)) {
        $tmp.=$row['name']."<BR>";
        break;
    }


    // деканские Но пересекающиеся

    $res=sql("SELECT * FROM groupuser, groupname
                     WHERE groupuser.mid=$mid AND groupuser.gid=groupname.gid
                     ORDER BY groupname.name","funEr01");

    //         $res=sql("SELECT * FROM groupname WHERE `CID`='-1' AND mid=$mid ORDER BY name","funEr01");
    while ($row=sqlget($res)) {
        $tmp.=$row['name']."<BR>";
    }

    return $tmp;

}

function getCountG($gid) {
    //   $sql="SELECT COUNT(*) FROM groupuser WHERE gid=$gid ORDER BY gid";
    $sql = "
                SELECT
                  COUNT(DISTINCT `People`.`MID`) AS num_users
                FROM
                  People
                  INNER JOIN groupuser ON (People.`MID` = groupuser.`mid`)
                    INNER JOIN Students ON (People.`MID` = Students.`MID`)
                WHERE
                  (groupuser.gid = {$gid})

   ";
    $res=sqlvalue($sql,1,1);
    return $res;
}

function writeGroups( $cid, $dean=0 ){
    // dean=1 тогда не даем редактировать и удалять деканские
    if (!$cid) return '';
    $tmp="<table width=100% class=main cellspacing=0>
   <tr>
         <th>"._("Название")."</th>
         <th width='30%'>"._("Количество обучаемых")."</th>
         <th width='10%'>"._("Действия")."</th>
   </tr>
   ";
    if( $cid > 0 ){
        $res=sql("SELECT * FROM groupname WHERE cid=$cid ORDER BY name","errGR73");
        while ($r=sqlget($res)) {
            $tmp.="<tr><td>$r[name]</td><td align=center>".getCountG($r[gid])."</td>
               <td width='100px' align='center'>
               <a href=\"$PHP_SELF?c=editgr&cid=$cid&gid=$r[gid]&hide_ghosts=1$sess\">".getIcon('edit',_("Редактировать состав группы"))."</a>
               <a href=$PHP_SELF?c=delete&cid=$cid&gid=$r[gid]$sess onclick=\"if (!confirm('"._("Вы действительно желаете удалить группу?")."')) return false;\"  class=\"wing\">".getIcon("delete",_("Удалить группу"))."</a></td>
            </tr>";
        }
	   if (sqlrows($res)==0) $tmp .= "<tr><td colspan=99 align=center>"._("нет данных для отображения")."</td></tr>";
    }
    $tmp.="</table>";
    /*$tmp.="<br><table width=100% class=main cellspacing=0>";
    $tmp.="<tr><th>"._("группы учебной администрации")."</th><th>"._("Кол-во обучаемых")."</th></tr>";
    $res=sql("SELECT * FROM groupname WHERE cid=-1 ORDER BY name","errGR73");
    if (!sqlrows($res)) {
        $tmp.="<tr><td colspan='2'>"._("не создано ни одной группы")."</td></tr>";
    }
    while ($r=sqlget($res)) {
        $strColspan = (!$dean) ? " colspan=1" : "";
        $tmp.="<tr><td {$strColspan}>$r[name]</td><td align=center>".getCountG($r[gid])."
               ";
        if( $dean && (!isset($cid))){
            $tmp.="</td><td width='100px' align='center'>
            <a href=$PHP_SELF?c=editgr&cid=$cid&autogroups=1&gid=$r[gid]$sess>" . getIcon("delete",_("редактировать")) . "</a>
            <a href=$PHP_SELF?c=delete&cid=$cid&gid=$r[gid]$sess onclick=\"if (!confirm('"._("Удалить группу?")."')) return false;\"  class=\"wing\">".
            getIcon("delete",_("удалить"))."</a>";
        }
        $tmp.="</td></tr>";
    }
    // деканские группы
    if(0&& $cid > 0 ){
    $tmp.="<tr><th colspan='2'>"._("группы учебной администрации (непересекающиеся)")."</th></tr>";
        $res=sql("SELECT * FROM cgname ORDER BY name","errGR73");
        if (!sqlrows($res)) {
            $tmp.="<tr><td colspan='2'>"._("не создано ни одной группы")."</td></tr>";
        }
        while ($r=sqlget($res)) {
            $tmp.="<tr><td colspan=2><b><a href=$PHP_SELF?c=showdgr&cgid=$r[cgid]$sess>$r[name]</a></b> (".getStCol($r[cgid]).") </td>
                  <!--td align='center'>"._("деканат")."</td-->
            </tr>";
        }
    }
    $tmp.="</table>";
*/
    return( $tmp );
}
function deleteGroup( $gid ){
    $sql = "
                   DELETE
                   FROM
                     `groupuser`
                   WHERE `groupuser`.`gid`='{$gid}'
   ";

    $res=sql($sql,"errFM185");
    sqlfree($res);

    $res=sql("DELETE FROM groupname WHERE gid='$gid'","errFM185");
    sqlfree($res);

}

function writeGroupListWide( $cid, $gid, $dean=0 ){

    global $s;

    $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);

    intvals("cid gid");

    $boolEditable = (($s['perm'] > 2) || ($cid > 0));

    $gr=sqlval("SELECT * FROM groupname WHERE gid=$gid","errGR87");
    if (!is_array($gr)) exit(_("Такой группы не существует."));
    if (($gr[cid]!=$cid) && ($gr[cid]>0)) exit("HackDetect: "._("доступ к чужому курсу"));

    if (!$GLOBALS['controller']->enabled)
    //$tmp.="
   //&lt;&lt; <a href=$PHP_SELF?$sess>"._("вернуться к списку групп")."</a>";
   $tmp.="<P>
   "._("Текущий курс:")." <b>".cid2title($cid)."</b><br>
   "._("Редактируемая группа:")." <b>$gr[name]</b><P>";

    $strInputAuto = (isset($_GET['autogroups'])) ? "<input type=hidden name=autogroups value=\"1\">" : "";

    if ($boolEditable) {
        $strCheckedGhosts = (isset($_GET['hide_ghosts']) ? "checked" : "");
        $tmp.= "
           <form id='form_ghosts' name='form_ghosts' method='GET' action='{$_SERVER['PHP_SELF']}'>
           <input type=hidden name=c value=\"editgr\">
           <input type=hidden name=gid value=\"$gid\">
       {$strInputAuto}
           <input type='checkbox' name='hide_ghosts' value='1' $strCheckedGhosts onClick=\"document.getElementById('form_ghosts').submit()\">"._("отображать только входящих в группу")."
                </form>
    ";
    }

    $tmp.="<form action='' method=post>
   <input type=hidden name=c value=\"post_editgr\">
   <input type=hidden name=gid value=\"$gid\">
   <input type=hidden name=cid value=\"$cid\">";

    if ($cid == -1) $tmp .= "<input type=hidden name=autogroups value=\"1\">";

    $boolOrderDir = (!isset($_GET['dir'])) ? ORDER_DESC : !$_GET['dir'];
    $sqlOrderDir = ($boolOrderDir) ? "DESC" : "";
    $strImageArrowDir = ($boolOrderDir) ? "up" : "down";

    switch ($_GET['assort']) {
        case ORDER_BY_LNAME:
        $sqlOrder = " People.LastName";
        $strImageArrowLname = "<img src='/images/sort_{$strImageArrowDir}.gif' border=0>";
        break;
        case ORDER_BY_POSITION:
        $sqlOrder = " People.Position";
        $strImageArrowPosition = "<img src='/images/sort_{$strImageArrowDir}.gif' border=0>";
        break;
        case ORDER_BY_RANK:
        $sqlOrder = " People.rnid";
        $strImageArrowRank = "<img src='/images/sort_{$strImageArrowDir}.gif' border=0>";
        break;
        default:
        $sqlOrder = " People.MID";
        $strImageArrowLname = "";
        $strImageArrowDepartment = "";
        $strImageArrowPosition = "";
        $strImageArrowCategory = "";
        $strImageArrowPlace = "";
        break;
    }
    $strHideGhosts = (isset($_GET['hide_ghosts'])) ? "&hide_ghosts={$_GET['hide_ghosts']}" : "";
    $strAutoGroups = (isset($_GET['autogroups'])) ? "&autogroups={$_GET['autogroups']}" : "";
    $tmp.="
   <table width=100% class=main cellspacing=0>
   <tr>
   <th nowrap><a class=cpass href={$_SERVER['PHP_SELF']}?c={$_GET['c']}{$strAutoGroups}&gid={$_GET['gid']}{$strHideGhosts}&assort=".ORDER_BY_LNAME."&dir={$boolOrderDir}>"._("ФИО")."</a>{$strImageArrowLname}</th>
        <th nowrap><a class=cpass href=#>"._("Логин")."</a></th>";
    if (defined("LOCAL_REGINFO_CIVIL") && !LOCAL_REGINFO_CIVIL) {
        $tmp.= "
                           <th nowrap><a class=cpass href={$_SERVER['PHP_SELF']}?c={$_GET['c']}{$strAutoGroups}&gid={$_GET['gid']}{$strHideGhosts}&assort=".ORDER_BY_RANK."&dir={$boolOrderDir}>"._("звание")."</a>{$strImageArrowRank}</th>
                           <th nowrap><a class=cpass href={$_SERVER['PHP_SELF']}?c={$_GET['c']}{$strAutoGroups}&gid={$_GET['gid']}{$strHideGhosts}&assort=".ORDER_BY_POSITION."&dir={$boolOrderDir}>"._("должность")."</a>{$strImageArrowPosition}</th>
                           </tr>";
    } else {
        $tmp.= "
                     <th nowrap><a class=cpass href='#'>E-mail</a></th>";
    }
    //   $res=sql("SELECT * FROM groupuser WHERE gid=$gid AND cid=$cid","errGR159");
    $sql = "
                SELECT DISTINCT
                  MID as distinct_mid
                FROM
                  groupuser
                WHERE
                  gid=$gid AND cid=$cid
        ";
    $res=sql($sql,"errGR159");
    $check=array();
    while ($r=sqlget($res)) {
        $check[$r['distinct_mid']]=1;
    }

    if( $cid > 0 )  $cid_stud="Students.CID=$cid AND ";
    else $cid_stud="";

    /*   $res=sql("SELECT People.FirstName, People.LastName, People.Login, People.email,
    People.mid as mid
    FROM Students
    LEFT JOIN People ON Students.MID=People.MID
    WHERE $cid_stud NOT ISNULL(People.MID)
    ORDER BY LastName","errGR105");
    */
    $sqlWhere = ($_GET['autogroups']) ? "1=1" : "`Students`.`cid`={$cid}";
    if (!$boolEditable) {
        $sqlWhere .= " AND groupuser.`gid` = {$gid}";
        $sqlExtraJoin = "INNER JOIN groupuser ON (People.`MID` = groupuser.`mid`)";
    }

    $sql = "
                SELECT DISTINCT
                  People.`MID` as distinct_mid,
                  People.LastName,People.Patronymic,People.FirstName,People.Login,People.Position,People.EMail,
                  rank.Title as rank
                FROM
                  People
                  INNER JOIN Students ON (People.`MID` = Students.`mid`)
                  LEFT OUTER JOIN rank ON (rank.rnid = People.rnid)
                    {$sqlExtraJoin}
                   WHERE
                     {$sqlWhere}
                   ORDER BY
                     {$sqlOrder} {$sqlOrderDir}
   ";
    $res=sql($sql,"errGR105");

    if ($boolEditable) $tmp.=_("Отметьте входящих в эту группу")." ("._("всего")." ".sqlrows($res).")<P>";

    while ($r=sqlget($res)) {
        if (!$peopleFilter->is_filtered($r['distinct_mid'])) continue;
        $strChecked = ($check[$r['distinct_mid']]) ? "checked" : "";
        $strEditAbility = ($boolEditable) ? "<input type=checkbox name='che[]' value='{$r['distinct_mid']}' {$strChecked}>" : "";
        if (!$_GET['hide_ghosts'] || $check[$r['distinct_mid']]) {
            $tmp.="<tr><td nowrap>".$strEditAbility."$r[LastName] $r[FirstName] $r[Patronymic]</td>
                     <td>{$r['Login']}</td>";
            if (defined("LOCAL_REGINFO_CIVIL") && !LOCAL_REGINFO_CIVIL) {
                $tmp .= "<td>{$r['rank']}</td>
                     <td>{$r['Position']}</td>";
            } else {
                $tmp .= "<td>{$r['EMail']}</td>";
            }
        }
    }
    if ($boolEditable) {$tmp.="
   <P>
   <tr>
      <td colspan=100 align=\"right\" valign=\"top\">";
      if ($GLOBALS['controller']->enabled) $tmp .= okbutton();
      else
      $tmp.="<input type=\"image\" name=\"ok\"
      onmouseover=\"this.src='".$sitepath."images/send_.gif';\"
      onmouseout=\"this.src='".$sitepath."images/send.gif';\"
      src=\"".$sitepath."images/send.gif\" align=\"right\" alt=\"ok\" border=\"0\">";
      $tmp .= "</td>
   </tr>
   </table>
   </form>";
    }
    return( $tmp );
}

function writeGroupList( $cid, $gid, $dean=0 ){

    global $s;

    intvals("cid gid");

    $boolEditable = (($s['perm'] > 2) || (($cid > 0) && !isset($_GET['autogroups'])));

    $gr=sqlval("SELECT * FROM groupname WHERE gid=$gid","errGR87");
    if (!is_array($gr)) exit(_("Такой группы не существует."));
    if (($gr[cid]!=$cid) && ($gr[cid]>0)) exit("HackDetect: "._("доступ к чужому курсу"));

    //$tmp.="
   //&lt;&lt; <a href=$PHP_SELF?$sess>"._("вернуться к списку групп")."</a>";
   $tmp .= "<P>
   "._("Текущий курс:")." <b>".cid2title($cid)."</b><br>
   "._("Редактируемая группа:")." <b>$gr[name]</b><P>";
    $strInputAuto = (isset($_GET['autogroups'])) ? "<input type=hidden name=autogroups value=\"1\">" : "";
    if ($boolEditable) {
        $strCheckedGhosts = (isset($_GET['hide_ghosts']) ? "checked" : "");
        $tmp.= "
           <form id='form_ghosts' name='form_ghosts' method='GET' action='{$_SERVER['PHP_SELF']}'>
           <input type=hidden name=c value=\"editgr\">
       {$strInputAuto}
           <input type=hidden name=gid value=\"$gid\">
           <input type='checkbox' name='hide_ghosts' value='1' $strCheckedGhosts onClick=\"document.getElementById('form_ghosts').submit()\">"._("отображать только входящих в группу")."
                </form>
    ";
    }

    $tmp.="<form action='' method=post>
   <input type=hidden name=c value=\"post_editgr\">
   <input type=hidden name=gid value=\"$gid\">
   <input type=hidden name=cid value=\"$cid\">";

    if ($cid == -1) $tmp .= "<input type=hidden name=autogroups value=\"1\">";

    $tmp.="
   <table width=100% class=main cellspacing=0>
   <tr><th>"._("ФИО")."</th><th>"._("логин")."</th></tr>";

    //   $res=sql("SELECT * FROM groupuser WHERE gid=$gid AND cid=$cid","errGR159");
    $sql = "
                SELECT DISTINCT
                  MID as distinct_mid
                FROM
                  groupuser
                WHERE
                  gid=$gid AND cid=$cid
        ";
    $res=sql($sql,"errGR159");
    $check=array();
    while ($r=sqlget($res)) {
        $check[$r['distinct_mid']]=1;
    }

    if( $cid > 0 )  $cid_stud="Students.CID=$cid AND ";
    else $cid_stud="";

    /*   $res=sql("SELECT People.FirstName, People.LastName, People.Login, People.email,
    People.mid as mid
    FROM Students
    LEFT JOIN People ON Students.MID=People.MID
    WHERE $cid_stud NOT ISNULL(People.MID)
    ORDER BY LastName","errGR105");
    */
    $sqlWhere = ($_GET['autogroups']) ? "1=1" : "`Students`.`cid`={$cid}";
    if (!$boolEditable) {
        $sqlWhere .= " AND groupuser.`gid` = {$gid}";
        $sqlExtraJoin = "INNER JOIN groupuser ON (People.`MID` = groupuser.`mid`)";
    }
    $sql = "
                SELECT DISTINCT
                  People.`MID` as distinct_mid,
                  People.LastName,
                  People.FirstName,
                  People.Patronymic,
                  People.EMail,
                  People.Login
                FROM
                  People
                  INNER JOIN Students ON (People.`MID` = Students.`mid`)
                    {$sqlExtraJoin}
                   WHERE
                     {$sqlWhere}
                   ORDER BY
                     People.LastName
   ";
    $res=sql($sql,"errGR105");
    if ($boolEditable) $tmp.=_("Отметьте, входящих в эту группу")." ("._("всего")." ".sqlrows($res).")<P>";

    while ($r=sqlget($res)) {
        $strChecked = ($check[$r['distinct_mid']]) ? "checked" : "";
        $strEditAbility = ($boolEditable) ? "<input type=checkbox name='che[]' value='{$r['distinct_mid']}' {$strChecked}>" : "";
        if (!$_GET['hide_ghosts'] || $check[$r['distinct_mid']]) {
            $tmp.="<tr><td>".$strEditAbility."$r[LastName] $r[FirstName] $r[Patronymic]</td><td>$r[Login]</td>".
            "</tr>";
        }
    }
    if ($boolEditable) $tmp.="
   <P>
   <tr>
      <td colspan=100 align=\"right\" valign=\"top\">".okbutton()."</td>
   </tr>";
   $tmp.="
   </table>
   </form>";
    return( $tmp );
}

/*function applyFilter( $mid, $filter ){
// выдает для студента входит ли он в группы  перечень групп в  filter
$fl=1;
foreach( $grs as $gr ){
$grst=getStudFromGr( $rg );

if( ! in( $stud, $grst ){
$fl=0;
break;
}
}
return( $fl );
} */

function getPhoto( $showMID, $nophoto=0, $foto_image_maxx=100, $foto_image_maxy=150, $without_change = 0 ){
    global $s;
    global $sitepath;
    $mid=0;
    $isme=0;
    $tmp="<a href='$PHP_SELF?upload1=foto$sess'>";
    if (isset($showMID)) {
        $mid=$showMID;
        if ($s[login] && $s[mid]===$showMID) $isme=1;
        if($without_change == 1) {
            $isme = 0;
        }
    }
    elseif (isset($s['mid'])) {
        $mid=$s['mid'];
        $isme=1;
    }

    //if( $mid > 0 ){
        //$res=sql("SELECT mid,fx,fy FROM filefoto WHERE mid=$mid","errRE484");
        //Zend_Registry::get('config');

        $getpath = getPath($_SERVER['DOCUMENT_ROOT'].'/../upload/photo/', $mid);
        $maxFilesCount = 800;
        $glob = glob($getpath . $mid .'.*');

        $file = $glob[0];

        if(count($file) > 0){
	        foreach($glob as $value){
	            $plus = floor($userId / $maxFilesCount) . '/' . basename($value);
	        }
	        return "<img name='foto' src=\"{$sitepath}upload/photo/" . $plus . "\" border=\"1px\">";
        }else{
            return "<img name='foto' src=\"{$sitepath}images/people/nophoto.gif\" border=\"1px\">";
        }
/*        if (sqlrows($res)<1 ) {
            if( $nophoto) {
                if ($isme) $html.=$tmp;
                $html.="<img name='foto' src=\"{$sitepath}images/people/nophoto.gif\" border=\"1px\">";
                if ($isme) {
                    if (!$GLOBALS['controller']->enabled)
                    $html.=" "._("заменить")." >></a>";
                    else $html .= "</a>";
                    $GLOBALS['controller']->setLink('m010205');
                }
            }
        }
        else {
            $v=sqlget($res);
            $html="";
            $url="{$sitepath}reg.php4?getimg=$mid$asess";
            if ($v[fx]>0 && $v[fy]>0) {
                if ($foto_image_maxx) $dx=doubleval($v[fx])/doubleval($foto_image_maxx);
                if ($foto_image_maxy) $dy=doubleval($v[fy])/doubleval($foto_image_maxy);
                if ($dx<1 && $dy<1) $dd=1; else $dd=max($dx,$dy);
                $imx=round(doubleval($v[fx])/doubleval($dd));
                $imy=round(doubleval($v[fy])/doubleval($dd));
                $html="<img src='$url' width=$imx height=$imy alt='"._("фото")."' border=0>";
            }
            else {
                $html="<img src='$url' width=$foto_image_maxx height=$foto_image_maxy alt='"._("фото")."' border=2>";
            }
            if ($isme) {
                if (!$GLOBALS['controller']->enabled)
                $html.="$tmp$html "._("заменить")." >></a>";
                else $html .= "</a>";
                $GLOBALS['controller']->setLink('m010205');
                $GLOBALS['controller']->setLink('m010206');
            }
        }
  //  }
    return( $html );*/
}

function getPath($filePath, $id){

        if(!is_dir($filePath)){
            return false;
        }
        $maxFilesCount = 800;
        $path = floor($id / $maxFilesCount);
        if(!is_dir($filePath . DIRECTORY_SEPARATOR . $path)){
            mkdir($filePath . DIRECTORY_SEPARATOR . $path, 0664);
        }
        return  $filePath . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR;
    }



function strtolower_custom($str){
    if (!strpos($_SERVER["SERVER_SOFTWARE"], "Win")){
        setlocale (LC_CTYPE, "ru_RU.KOI8-R");
        $str = convert_cyr_string($str,"w","k");
        $str = strtolower($str);
        $str = convert_cyr_string($str,"k","w");
    } else {
        $str = strtolower($str);
    }
    return $str;
}

function copyDir($strDirSource, $strDirDest, $strtolower = true) {
    $ret = array();
    if ($handle = @opendir($strDirSource)) {
        while (false !== (@$file = readdir($handle))) {
            $strLowerFile = ($strtolower) ? strtolower($file) : $file;
            if (is_dir($strDirSource."/".$file)) {
                if (($file != "..") && ($file != ".")) {
                    @mkdir($strDirDest.$strLowerFile,0775);
                    chmod($strDirDest.$strLowerFile,0775);
                    $ret = array_merge($ret, copyDir($strDirSource."/".$file, $strDirDest.$strLowerFile."/", $strtolower));
                }
            }
            else {
                @copy($strDirSource."/".$file, $strDirDest."{$strLowerFile}");
                $ret[] = $strDirDest.$strLowerFile;
            }
        }
        @closedir($handle);
    }
    return $ret;
}

//Функция добавляет учителя в дефолтовые группы,
//т.е. в группы в которых должны быть все преподаватели, если их там еще нет
function add_teacher_to_default_groups($mid) {

    CRole::add_mid_to_role($mid,CRole::get_default_role('teacher'));
    return;

    $query = "SELECT * FROM permission_groups WHERE `type`='teacher' AND `default` = '1'";
    $result = sql($query);
    while($row = sqlget($result)) {
        $pmid = $row['pmid'];
        $sub_q = "SELECT * FROM permission2mid WHERE pmid = '$pmid' AND mid = '$mid'";
        $sub_res = sql($sub_q);
        if(sqlrows($sub_res) == 0) {
            $sub_q = "INSERT INTO permission2mid (pmid, mid) VALUES ('$pmid', '$mid')";
            $sub_res = sql($sub_q);
        }
    }
}

//проверяет можно ли преподавателю с данным mid делать action с acid
//возвращает true/false
function check_students_permissions($acid, $mid) {
    return check_permissions($acid, $mid, PERMISSIONS_STUDENT);
}
function check_teachers_permissions($acid, $mid) {
    return check_permissions($acid, $mid, PERMISSIONS_TEACHER);
}

function check_permissions($acid, $mid, $status) {
    if ($GLOBALS['controller']->enabled) return true;
    global $s;

    switch ($status)
    {
        case "student":
            $perm = 1;
            break;
        case "teacher":
            $perm = 2;
            break;
        case "dean":
            $perm = 3;
            break;
        default:
            $perm = 4;
    }

    if($s['perm'] > $perm) {
        return true;
    } elseif($s['perm'] < $perm) {
        return false;
    }

    $pmid = get_pmid($mid, $status);
    $query = "
            SELECT
              permission2act.pmid
            FROM
              permission2act
              INNER JOIN actions ON (permission2act.acid = actions.acid)
            WHERE
              actions.acid = '{$acid}' AND
              permission2act.pmid = '{$pmid}'
        ";
    $result = sql($query);
    return sqlrows($result);
}

function check_permissions_group($acid, $pmid) {
    global $s;
    $query = "
            SELECT *
            FROM
              permission2act
            WHERE
              permission2act.acid = '{$acid}' AND
              permission2act.pmid = '{$pmid}'
        ";
    $result = sql($query);
    return sqlrows($result);
}

function get_permissions($mid, $status) {

    $pmid = get_pmid($mid, $status);
    $query = "
            SELECT
              actions.acid as acid
            FROM
              permission2act
              INNER JOIN actions ON (permission2act.acid = actions.acid)
            WHERE
              permission2act.pmid = '{$pmid}'
        ";
    $result = sql($query);
    $return = array();
    while ($arr = sqlget($result)) {
        $return[] = $arr['acid'];
    }
    return $return;
}

function get_permissions_all($status) {

    $query = "
            SELECT
              permission_groups.pmid, permission_groups.name, permission_groups.default as default_group
            FROM
              permission_groups
            WHERE
              permission_groups.type = '{$status}'
            ORDER BY
              default_group DESC
        ";
    $result = sql($query);
    $return = array();
    while ($arr = sqlget($result)) {
        $return[$arr['pmid']] = $arr['name'];
    }
    return $return;
}

function get_pgroup_name($mid, $status) {

    global $default_groups;

    if ($status == 'teacher') $ret = _("Преподаватель");
    if ($status == 'dean') $ret = _("Учебная администрация");

    $query = "
            SELECT
              permission_groups.`name`
            FROM
              permission2mid
              INNER JOIN permission_groups ON (permission2mid.pmid = permission_groups.pmid)
            WHERE
              permission_groups.`type` = '{$status}' AND
              permission2mid.mid = '{$mid}'
              ";
    $result = sql($query);
    return ($arr = sqlget($result)) ? $arr['name'] : $ret;

}

function get_pmid($mid, $status) {

    global $default_groups;

    $query = "
            SELECT
              permission2mid.`pmid`
            FROM
              permission2mid
              INNER JOIN permission_groups ON (permission2mid.pmid = permission_groups.pmid)
            WHERE
              permission_groups.`type` = '{$status}' AND
              permission2mid.mid = '{$mid}'
              ";
    $result = sql($query);
    return ($arr = sqlget($result)) ? $arr['pmid'] : $default_groups[$status];

}

function get_actions($status) {
    $arrReturn = array();
    $q = "SELECT * FROM actions WHERE type='{$status}' ORDER BY sequence";
    $r = sql($q);
    while($a = sqlget($r)) {
        $arrReturn[$a['acid']] = $a['name'];
    }
    return $arrReturn;
}

function month_name($number) {
         switch($number) {
                 case "1":
                       return _("Январь");
                 break;
                 case "2":
                       return _("Февраль");
                 break;
                 case "3":
                       return _("Март");
                 break;
                 case "4":
                       return _("Апрель");
                 break;
                 case "5":
                       return _("Май");
                 break;
                 case "6":
                       return _("Июнь");
                 break;
                 case "7":
                       return _("Июль");
                 break;
                 case "8":
                       return _("Август");
                 break;
                 case "9":
                       return _("Сентябрь");
                 break;
                 case "10":
                       return _("Октябрь");
                 break;
                 case "11":
                       return _("Ноябрь");
                 break;
                 case "12":
                       return _("Декабрь");
                 break;
         }
}

function get_rank($mid) {
         $query = "SELECT * FROM People WHERE MID = '$mid'";
         $result = sql($query);
         $row = sqlget($result);
         $Information = $row['Information'];
         if(!empty($Information)) {
             $reg_block = explode(";", REGISTRATION_FORM);
             if(is_array($reg_block)) {
                foreach($reg_block as $key => $value) {
                        if($value == "military_state") {
                           $meta = read_metadata($Information, "military_state");
                           if(is_array($meta)) {
                              foreach($meta as $item) {
                                      if($item['name'] == "rank") {
                                         return $item['value'];
                                      }

                              }
                           }
                        }
                }
             }
         }
         else {
             return "";
         }
}

function get_blank($mid) {

    if ($mid_manager = get_manager($mid)) {
        $return_array["h_name"] = getpeoplename($mid_manager);
        $return_array["h_rank"] = get_rank($mid_manager);
        $return_array["h_office"] = get_position($mid_manager);
    }

    $return_array["current_year"] = date("Y", time());
    $return_array["f_name"] = getpeoplename($mid);
    $return_array["f_rank"] = get_rank($mid);

    return $return_array;
}

function get_manager($mid) {
  $strSql = "
    SELECT
          departments_manager.mid as mid_manager
    FROM
      departments departments_subordinate
      INNER JOIN departments departments_manager ON (departments_subordinate.owner_did = departments_manager.did)
    WHERE
      (departments_subordinate.`mid` = '{$mid}') AND
      (departments_manager.mid)
  ";
  $res=sql( $strSql );
  return ($arr = sqlget($res)) ? $arr['mid_manager'] : false;
}

function getWeek($CID = 0, $current_week = 0)
{
        $array = array();
        if($CID!=0)
                $where = "WHERE CID=$CID";
        else
                $where = "";

        $res = sql("SELECT cBegin FROM courses $where ORDER by cBegin ASC LIMIT 1", "selWeeks1");
        $row = sqlget($res);
        $date_begin = $row['cBegin'];

        $res = sql("SELECT cEnd FROM courses $where ORDER by cEnd DESC LIMIT 1", "selWeeks2");
        $row = sqlget($res);
        $date_end = $row['cEnd'];

        $date_begin_tms = mktime(0,0,0,substr($date_begin, 5, 2),substr($date_begin, 8, 2),substr($date_begin, 0, 4));
        $date_end_tms = mktime(0,0,0,substr($date_end, 5, 2),substr($date_end, 8, 2),substr($date_end, 0, 4));
        //echo $CID." ".$current_week;

        if($current_week == 0)
        {
                $array[0] = $date_begin_tms;
                $array[1] = $date_end_tms;
                return $array;
        }

        $date_begin_dayweek = (int) date("w", $date_begin_tms);
        if($date_begin_dayweek==0)
                $date_begin_dayweek = 7;

        $begin = $date_begin_tms - ($date_begin_dayweek-1)*24*60*60;

        $i = 1;
        while($begin <= $date_end_tms)
        {
                if($current_week==$i)
                        $array[0] = $begin;

                $begin += 7*24*60*60;

                if($current_week==$i)
                        $array[1] = $begin - 1;

                $i++;
        }

        return $array;
}

function selWeeks($CID = 0, $current_week = 0)
{
    $current_week = $_GET['week'];
        global $tweek;
        if($CID!=0)
                $where = "WHERE CID=$CID";
        else
                $where = "";

        $res = sql("SELECT cBegin FROM courses $where ORDER by cBegin ASC LIMIT 1", "selWeeks1");
        $row = sqlget($res);
        $date_begin = $row['cBegin'];

        $res = sql("SELECT cEnd FROM courses $where ORDER by cEnd DESC LIMIT 1", "selWeeks2");
        $row = sqlget($res);
        $date_end = $row['cEnd'];

        $date_begin_tms = mktime(0,0,0,substr($date_begin, 5, 2),substr($date_begin, 8, 2),substr($date_begin, 0, 4));
        $date_end_tms = mktime(0,0,0,substr($date_end, 5, 2),substr($date_end, 8, 2),substr($date_end, 0, 4));

        if (($date_begin_tms == -1) || ($date_end_tms == -1)) {
            return $str = "<option value=0>---</option>";
        }


        $date_begin_dayweek = (int) date("w", $date_begin_tms);
        if($date_begin_dayweek==0)
                $date_begin_dayweek = 7;

        $begin = $date_begin_tms - ($date_begin_dayweek-1)*24*60*60;

        $i = 1;
        $array = array();
        while($begin <= $date_end_tms)
        {
                 if(($tweek >= $begin) && ($tweek <= ($begin + 1*24*60*60)))
                    $GLOBALS['current_week_id'] = $i;
                if(time() >= $begin && time() < ($begin + 7*24*60*60)) {
                        if($current_week==0) $current_week = $i;
                        $actual_week = $i;
                }
                $array[$i] = (string)$i. ": ". date("d.m.Y ", $begin);
                $begin += 6*24*60*60;
                $array[$i] .= "- ". date("d.m.Y", $begin);
                $begin += 1*24*60*60;
                $i++;
        }

        $str = "<option value=0>---</option>";
        foreach ($array as $key => $val)
        {
                $str .= "<option value=$key ";
                $strSelected = ($key==$current_week) ? "selected" : "";
                $strStar = ($key==$actual_week) ? " *" : "";
                $str .= $strSelected . ">$val {$strStar}</option>";
        }
        $GLOBALS['week'] = $current_week;
        return $str;
}

function delete_student($mid) {
    $query = "DELETE FROM Students WHERE MID='{$mid}'";
    sql($query,"abiturDel01");
    $query = "DELETE FROM claimants WHERE MID='{$mid}'";
    sql($query,"abiturDel03");
    $query = "DELETE FROM groupuser WHERE MID='{$mid}'";
    sql($query,"abiturDel03");
    $query = "DELETE FROM logseance WHERE mid='{$mid}'";
    sql($query,"abiturDel03");
    $query = "DELETE FROM logseance WHERE MID='{$mid}'";
    sql($query,"abiturDel03");
    $query = "DELETE FROM money WHERE mid='{$mid}'";
    sql($query,"abiturDel03");
    $query = "DELETE FROM scheduleID WHERE MID='{$mid}'";
    sql($query,"abiturDel03");
    $query = "DELETE FROM seance WHERE MID='{$mid}'";
    sql($query,"abiturDel03");
    $query = "DELETE FROM teachNotes WHERE MID='{$mid}'";
    sql($query,"abiturDel03");
    $query = "DELETE FROM testcount WHERE mid='{$mid}'";
    sql($query,"abiturDel03");
    $query = "DELETE FROM tracks2mid WHERE MID='{$mid}'";
    sql($query,"abiturDel05");
    $query = "DELETE FROM People WHERE MID='{$mid}'";
    sql($query,"abiturDel04");
}

function assign_person2course($mid,$cid,$mail=1) {
    $sql = "SELECT TypeDes, chain FROM Courses WHERE CID='".(int) $cid."'";
    $res = sql($sql);
    if (sqlrows($res)) $row = sqlget($res);
    //if ($row['TypeDes']<0) $row['TypeDes'] = $row['chain'];
    $row['TypeDes'] = ($row['TypeDes']!=0) ? $row['chain'] : 0;
    if ($row['TypeDes']<=0) {
        return tost($mid,$cid,$mail,0,1);
    } else {
        $sql = "SELECT * FROM claimants WHERE MID='".(int) $mid."' AND CID='".(int) $cid."' AND Teacher='0'";
        $res = sql($sql);
        if (!sqlrows($res)) {
            $sql = "SELECT * FROM Students WHERE MID='".(int) $mid."' AND CID='".(int) $cid."'";
            $res = sql($sql);
            if (!sqlrows($res)) {
                sql("INSERT INTO claimants (MID,CID,Teacher) VALUES ('".(int) $mid."', '".(int) $cid."', 0)");
                return sqllast();
            }
        }
    }
}

function delete_person_from_course($mid,$cid) {
    $typeDes = getField('Courses','TypeDes','CID',$cid);
    if ($typeDes!=0) $typeDes = getField('Courses','chain','CID',$cid);
    if ($typeDes <= 0) {
        sql("DELETE FROM claimants WHERE MID='".(int) $mid."' AND CID='".(int) $cid."'");
    }
    sql("DELETE FROM Students WHERE MID='".(int) $mid."' AND CID='".(int) $cid."'");
    require_once($GLOBALS['wwf'].'/lib/classes/Chain.class.php');
    CChainLog::erase($cid,$mid);
}



/**
* Возвращает кол-во ролей типа type
*/
function count_user_roles_by_type($mid,$type) {
    $sql = "SELECT DISTINCT permission2mid.mid, permission2mid.pmid
            FROM permission2mid INNER JOIN permission_groups ON (permission_groups.pmid=permission2mid.pmid)
            WHERE permission2mid.mid='{$mid}' AND
            permission_groups.type='{$type}' AND
            permission_groups.default = '0'";
    $res = sql($sql);
    return sqlrows($res);
}

function is_last_admin($mid) {
    $sql = "SELECT * FROM admins WHERE MID<>'{$mid}'";
    $res = sql($sql);
    if (sqlrows($res)) return false;
    else return true;
}

// todo: нуно оптимизировать - слишком медленно
function getCourseRating($cid,$mid) {
    if ($cid && $mid) {
        $sum = array();
        foreach ($weights = CEventWeight::get_shedules_weights($cid, $mid) as $key => $schedule) {
            if ($schedule['weight_cid'] == -1) continue;
            $weight = ((!empty($schedule['weight_cid'])) ? $schedule['weight_cid'] : $schedule['weight_base']);
            $sum_type[$schedule['type_id']] += $schedule['V_STATUS'] * $weight;
            $sum_type_recount[$schedule['type_id']] += $schedule['V_STATUS'];
            $cnt[$schedule['type_id']]++;
        }

        // проверка на сотню
        $recount = false;
        if (is_array($weights_used) && ($count = count($weights_used))) {
            if (array_sum($weights_used)!=100) {
                $w = floor(100/count($weights_used));
                $i=1;
                foreach($weights_used as $key=>$weight) {
                    $weights_used[$key] = $w;
                    if ($i==count($weights_used)) $weights_used[$key] = (int) (100-($w*($count-1)));
                    $i++;
                }
                $sum_type = $sum_type_recount;
                $recount = true;
            }
        }
        if (is_array($sum_type) && count($sum_type))
        foreach ($sum_type as $type => $value) {
            if ($recount) $value *= $weights_used[$type];
            $sum_all += $value/$cnt[$type];
        }
        return ($sum_all) ? round($sum_all/100,2) : '';
    }
    return '';
}

function getCourseMark($cid,$mid) {
    if ($cid && $mid) {
        $sql = "SELECT mark, alias FROM courses_marks WHERE cid='".(int) $cid."' AND mid='".(int) $mid."'";
        $res = sql($sql);
        if (sqlrows($res)) {
            $row = sqlget($res);
            return array($row['mark'], $row['alias']);
        }
    }
}

function getCourseMarkIfExists($cid,$mid) {
    if ($cid && $mid) {
        $sql = "SELECT mark FROM courses_marks WHERE cid='".(int) $cid."' AND mid='".(int) $mid."'";
        $res = sql($sql);
        if (sqlrows($res)) {
            $row = sqlget($res);
            return $row['mark'];
        }
    }
    return '-';
}

function saveCourseMark($cid,$mid,$mark,$alias = false) {
    if ($cid) {
        $sql = "SELECT *
                FROM courses_marks
                WHERE cid='".(int) $cid."' AND mid='".(int) $mid."'";
        $res = sql($sql);
        if (sqlrows($res)) {
            $sql = "UPDATE courses_marks SET mark=".$GLOBALS['adodb']->Quote($mark).", alias=".$GLOBALS['adodb']->Quote($alias)."
                    WHERE cid='".(int) $cid."' AND mid='".(int) $mid."'";
        } else {
            $sql = "INSERT INTO courses_marks (cid,mid,mark,alias) VALUES ('".(int) $cid."','".(int) $mid."',".$GLOBALS['adodb']->Quote($mark).",".$GLOBALS['adodb']->Quote($alias).")";
        }
        sql($sql);
    }
}

function is_kurator($mid) {
    if ($mid) {
        $sql = "SELECT did FROM departments WHERE mid='".$mid."' AND application = '".DEPARTMENT_APPLICATION."'";
        $res = sql($sql);
        while($row = sqlget($res)) $rows[] = $row['did'];
        return $rows;
    }
}

function get_course_typedes($cid) {
    $sql = "SELECT TypeDes, chain FROM Courses WHERE CID='".(int) $cid."'";
    $res = sql($sql);
    if (sqlrows($res)) $row = sqlget($res);
    if ($row['TypeDes']<0) $row['TypeDes'] = $row['chain'];
    return $row['TypeDes'];
}

function get_groups_rating_arrays($cid=0) {
    if ($cid>0) $where = " AND CID='".(int) $cid."'";
    $sql = "SELECT DISTINCT gid FROM groupuser WHERE cid='-1'";
    $res = sql($sql);
    while($row = sqlget($res)) {
        $groups[] = $row['gid'];
    }

    $sql = "SELECT CID FROM Courses WHERE Status>0 $where";
    $res = sql($sql);
    while($row = sqlget($res)) {
        $courses[] = $row['CID'];
    }

    if (is_array($courses) && count($courses) && is_array($groups) && count($groups)) {
        foreach($courses as $cid) {
            foreach($groups as $gid) {
                $sql = "SELECT DISTINCT groupuser.mid, IF(courses_marks.mark,courses_marks.mark,0) AS mark
                        FROM groupuser
                        INNER JOIN Students ON (Students.MID=groupuser.mid)
                        LEFT JOIN courses_marks ON (Students.MID=courses_marks.mid)
                        WHERE groupuser.gid='".(int) $gid."' AND
                        Students.CID='".(int) $cid."'
                        ORDER BY courses_marks.mark DESC, Students.MID ASC";
                $res = sql($sql);
                $i=0; unset($last);
                while($row=sqlget($res)) {
                    if (!isset($last)) {
                        $last = $row['mark'];
                        $i++;
                    }
                    else {
                        if ($row['mark']<$last) $i++;
                        $last = $row['mark'];
                    }
                    $ret[$cid][$gid][$row['mid']] = (int) $i;
                }
            }
        }
    }
    return $ret;
}

function get_courses_rating_arrays($cid=0) {
    if ($cid>0) $where = " WHERE CID='".(int) $cid."'";
    $sql = "SELECT CID FROM Courses $where";
    $res = sql($sql);
    while($row = sqlget($res)) {
        $courses[] = $row['CID'];
    }

    if (is_array($courses) && count($courses)) {
        foreach($courses as $cid) {
                $sql = "SELECT Students.MID, IF(courses_marks.mark,courses_marks.mark,0) AS mark
                        FROM Students
                        LEFT JOIN courses_marks ON (Students.MID=courses_marks.mid)
                        WHERE
                        Students.CID='".(int) $cid."'
                        ORDER BY courses_marks.mark DESC,Students.MID ASC";
                $res = sql($sql);
                $i=0; unset($last);
                while($row=sqlget($res)) {
                    if (!isset($last)) {
                        $last = $row['mark'];
                        $i++;
                    }
                    else {
                        if ($row['mark']<$last) $i++;
                        $last = $row['mark'];
                    }
                    $ret[$cid][$row['MID']] = (int) $i;
                }
        }
    }

    return $ret;
}

/**
* Возвращает рейтинг в группе по cid и mid если чела нет в группе то
* рейтинг по всему курсу
*/
function get_group_rating($cid,$mid) {
    $group_rating = get_groups_rating_arrays($cid);
    if (is_array($group_rating[$cid]) && count($group_rating[$cid])) {
        while(list(,$v) = each($group_rating[$cid])) {
            if (in_array($mid,$v)) {
                return (int) (array_search($mid,$v)+1).' из '.count($v);
            }
        }
    }
    $course_ratings = get_courses_rating_arrays($cid);
    if (in_array($mid,$course_ratings[$cid]))
        return (int) (array_search($mid,$course_ratings[$cid])+1).' из '.(int) count($course_ratings[$cid]);
}

function get_departments_you_in_academic($mid) {
    $sql = "SELECT DISTINCT departments.did,departments.name
            FROM departments
            INNER JOIN departments_groups ON (departments_groups.did=departments.did)
            INNER JOIN groupuser ON (groupuser.gid=departments_groups.gid)
            WHERE groupuser.cid='-1' AND groupuser.mid='".(int) $mid."'
            AND departments.application = '".DEPARTMENT_APPLICATION."'";
    $res = sql($sql);
    while($row = sqlget($res)) $rows[$row['did']] = $row['name'];
    return $rows;
}

function get_departments_you_in_corporate($mid) {
    $sql = "SELECT owner_soid FROM structure_of_organ WHERE mid='".$mid."'";
    $res = sql($sql);
    while($row=sqlget($res)) $rows[] = $row['owner_soid'];
    if (is_array($rows) && count($rows)) {
        $sql = "SELECT DISTINCT departments.did,departments.name
                FROM departments
                INNER JOIN departments_soids ON (departments_soids.did=departments.did)
                WHERE departments_soids.soid IN ('".join("','",$rows)."')
                AND departments.application = '".DEPARTMENT_APPLICATION."'";
        $res = sql($sql);
        while($row = sqlget($res)) $ret[$row['did']] = $row['name'];
    }
    return $ret;
}

function get_needed_departments($mid) {
    $rows = array();
    // Если куратор
    $sql = "SELECT did,name FROM departments WHERE mid='".(int) $mid."' AND application = '".DEPARTMENT_APPLICATION."'";
    $res = sql($sql);
    while($row = sqlget($res)) $rows[$row['did']] = $row['name'];
    // в курируемых группах
    //if (defined('APPLICATION_BRANCH') && (APPLICATION_BRANCH==APPLICATION_BRANCH_ACADEMIC)) {
        $deps = get_departments_you_in_academic($mid);
    //} else { // в курируемых оргединицах
    //    $deps = get_departments_you_in_corporate($mid);
    //}
    if (is_array($deps)) {
    	$keys = array_keys($deps);
    	while ($key = array_shift($keys)) {
    		$rows[$key] = $deps[$key];
        }
    }

    if (is_array($rows)) {
        asort($rows);
    }
    return $rows;
}

function get_all_departments() {
    $deps = array();
    $sql  = "SELECT did, name FROM departments WHERE application = '".DEPARTMENT_APPLICATION."'";
    $res = sql($sql);
    while($row = sqlget($res)) {
        $deps[$row['did']] = $row['name'];
    }
    return $deps;
}

function tempdir($dir, $prefix='', $mode=0700) {
   if (substr($dir, -1) != '/') $dir .= '/';
   do {
     $path = $dir.$prefix.mt_rand(0, 9999999);
   } while (!@mkdir($path, $mode));
   return $path;
}

    function get_room_capacity($room) {
        if ($room) {
            $sql = "SELECT volume FROM rooms WHERE rid='".(int) $room."'";
            $res = sql($sql);
            if (sqlrows($res)) {
                $row = sqlget($res);
                return $row['volume'];
            }
        }
    }

    function is_room_busy($room, $begin, $end, $sheid=0) {
        global $adodb;
        $begin = addslashes($begin);
        $end = addslashes($end);
        if ($sheid) {
            $sql_addon = " AND schedule.SHEID NOT IN ('".(int) $sheid."')";
        }
        if ($room) {
            /*
            $sql = "SELECT schedule.SHEID
                    FROM schedule
                    WHERE
                        schedule.rid='".(int) $room."' AND
                        ((schedule.begin <= '{$begin}' AND schedule.end >= '{$end}') OR
                        (schedule.begin >= '{$begin}' AND schedule.begin <= '{$end}') OR
                        (schedule.end >= '{$begin}' AND schedule.end <= '{$end}')) {$sql_addon}";
            */
            $sql = "SELECT schedule.SHEID
                    FROM schedule
                    WHERE
                        schedule.rid='".(int) $room."' AND
                        schedule.begin <= schedule.end AND
                        schedule.timetype = '0' AND
                        ((schedule.begin <= ".$adodb->DBTimestamp($begin)." AND schedule.end >= ".$adodb->DBTimestamp($end).") OR
                        (schedule.begin >= ".$adodb->DBTimestamp($begin)." AND schedule.begin <= ".$adodb->DBTimestamp($end).") OR
                        (schedule.end >= ".$adodb->DBTimestamp($begin)." AND schedule.end <= ".$adodb->DBTimestamp($end).")) {$sql_addon}";
            $res = sql($sql);
            return sqlrows($res);
        }
    }

    function get_room_select($selected, $sheid, $begin, $end, $people, $utf=false, $chid=0, $course) {
        $rooms = getRooms($course, false);
        if (count($rooms )>0) {
            $html = "<select id=\"room\" name='room' size='1' class='sel100' onChange=\"get_room_select();\" style=\"width: 300px\">";
            $html .= "<option value=-1>---</option>";

            $used = array();
            if ($begin && $end) {

                $begin = strtotime($begin);
                $end = strtotime($end);

                $start = $begin;
                $stop  = $end;

                if ($chid > 0) {
                    $end = strtotime(date('Y-m-d', $begin).' '.date('H:i', $stop));
                }

                while($end <= $stop) {

                    $sql = "SELECT title, rid, CHID, begin, end FROM schedule WHERE rid IN ('".join("','", array_keys($rooms))."')
                    AND (
                        (begin >= ".$GLOBALS['adodb']->DBTimestamp($begin)." AND begin <= ".$GLOBALS['adodb']->DBTimestamp($end).")
                        OR (end >= ".$GLOBALS['adodb']->DBTimestamp($begin)." AND end <= ".$GLOBALS['adodb']->DBTimestamp($end).")
                        OR (begin < ".$GLOBALS['adodb']->DBTimestamp($begin)." AND end > ".$GLOBALS['adodb']->DBTimestamp($end).")
                        )";
                    if ($sheid) {
                        $sql .= " AND SHEID <> '".(int) $sheid."'";
                    }
                    $res = sql($sql);
                    while($row = sqlget($res)) {
                        if (in_array($row['CHID'], array(1,2,3,4))) {
                            if (!isScheduleInProgress($begin, $end, strtotime($row['begin']), strtotime($row['end']), $row['CHID'])) {
                                continue;
                            }
                        }
                        $used[$row['rid']] = $row['title'];
                    }

                    switch($chid) {
                        case 1:
                            $begin = strtotime('+1 day', $begin);
                            $end = strtotime('+1 day', $end);
                            break;
                        case 2:
                            $begin = strtotime('+1 week', $begin);
                            $end = strtotime('+1 week', $end);
                            break;
                        case 3:
                            $begin = strtotime('+1 month', $begin);
                            $end = strtotime('+1 month', $end);
                            break;
                        case 4:
                            $begin = strtotime('+2 week', $begin);
                            $end = strtotime('+2 week', $end);
                            break;
                        default:
                            $end = strtotime('+1 year', $end);
                            break;
                    }

                }
            }

            foreach($rooms as $room) {
                $status = $style ='';
                if (isset($used[$room['rid']])) {
                    $status = ' [ занято: '.htmlspecialchars($used[$room['rid']], ENT_QUOTES).' ]';
                    $style = "style='background: #EEEEEE'";
                } else {
                    if ($people) {
                        $capacity = get_room_capacity($room['rid']);
                        if ($capacity < $people) {
                            $status = " [ не более {$capacity} чел. ]";
                            $style = "style='background: #EEEEEE;'";
                        }
                    }
                }
                $html .= "<option $style value=\"{$room['rid']}\" ";
                if ($room['rid']==$selected) $html .= "selected";
                $html .= "> {$room['name']} $status</option>";
            }
            /// THRASH
            /*
            foreach( $rooms as $room ) {
                $sel = $status = $style ='';

                if ($selected == $room['rid']) {
                    $sel = "selected";
                }

                if (is_room_busy($room['rid'],$begin, $end, $sheid)) {
                    $status = " [ занято ]";
                    $style = "style='background: #EEEEEE;'";
                } else {
                    if ($people) {
                        $capacity = get_room_capacity($room['rid']);
                        if ($capacity < $people) {
                            $status = " [ не более {$capacity} чел. ]";
                            $style = "style='background: #EEEEEE;'";
                        }
                    }
                }

                $html .= "<option {$style} value=".$room['rid']." $sel>".$room['name']."{$status} </option>";
            }
            */

            $html .= "</select>";
        } else {
            $html = "<select id='room' name='room' size='1' class='sel100' style='width: 300px'><option value='-1'>---</option></select>";
        }
        if ($utf) $html = iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,'UTF-8',$html);
        return $html;
    }

/**
* Возвращает массив преподов на курсе
* @return array
* @param int $cid курс
*/
function get_teachers_array($cid) {
    if ($cid) {
        $sql = "SELECT DISTINCT People.MID, People.LastName, People.FirstName, People.Patronymic, People.Login
                FROM People
                INNER JOIN Teachers ON (Teachers.MID=People.MID)
                WHERE Teachers.CID='".(int) $cid."'
                ORDER BY People.LastName";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $rows[$row['MID']] = $row;
        }
        return $rows;
    }
}

/**
 * @param unixtimestamp $begin
 * @param unixtimestamp $end
 * @param unixtimestamp $scheduleBegin
 * @param unixtimestamp $scheduleEnd
 * @param int $CHID
 */
function isScheduleInProgress($begin, $end, $scheduleBegin, $scheduleEnd, $CHID) {
    $start = $scheduleBegin;
    $stop  = strtotime(date('Y-m-d', $start).' '.date('H:i', $scheduleEnd));
    while($stop <= $scheduleEnd) {
        if (($start >= $begin && $start >= $end)
        || ($stop >= $begin && $stop <= $end)
        || ($start < $begin && $stop > $end)) {
            return true;
        }

        switch($CHID) {
            case 1:
                $start = strtotime('+ 1 day', $start);
                $stop  = strtotime('+ 1 day', $stop);
                break;
            case 2:
                $start = strtotime('+ 1 week', $start);
                $stop  = strtotime('+ 1 week', $stop);
                break;
            case 3:
                $start = strtotime('+ 1 month', $start);
                $stop  = strtotime('+ 1 month', $stop);
                break;
            case 4:
                $start = strtotime('+ 2 week', $start);
                $stop  = strtotime('+ 2 week', $stop);
                break;
            default:
                $start = strtotime('+ 1 day', $start);
                $stop  = strtotime('+ 1 day', $stop);
                break;
        }

        if ($end < $start) {
            return false;
        }
    }
    return false;
}

/**
* Возвращает select с преподами
* @return string
* @param int $cid
* @param int $current
*/
function get_teacher_select($cid,$current=0,$utf=false, $begin=false, $end=false, $sheid = 0, $chid = 0) {
    $html = "<select name=\"teacher\" id=\"teacher\" size=1 style=\"width: 300px\">";
    if ($cid) {
        if (!$current) $current = $GLOBALS['s']['mid'];
        $teachers = get_teachers_array($cid);
        if (is_array($teachers) && count($teachers)) {

            $used = array();
            if ($begin && $end) {

                $begin = strtotime($begin);
                $end = strtotime($end);

                $start = $begin;
                $stop  = $end;

                if ($chid > 0) {
                    $end = strtotime(date('Y-m-d', $begin).' '.date('H:i', $stop));
                }

                while($end <= $stop) {

                    $sql = "SELECT title, teacher, CHID, begin, end FROM schedule WHERE teacher IN ('".join("','", array_keys($teachers))."')
                    AND (
                        (begin >= ".$GLOBALS['adodb']->DBTimestamp($begin)." AND begin <= ".$GLOBALS['adodb']->DBTimestamp($end).")
                        OR (end >= ".$GLOBALS['adodb']->DBTimestamp($begin)." AND end <= ".$GLOBALS['adodb']->DBTimestamp($end).")
                        OR (begin < ".$GLOBALS['adodb']->DBTimestamp($begin)." AND end > ".$GLOBALS['adodb']->DBTimestamp($end).")
                        )";
                    if ($sheid) {
                        $sql .= " AND SHEID <> '".(int) $sheid."'";
                    }
                    $res = sql($sql);
                    while($row = sqlget($res)) {
                        if (in_array($row['CHID'], array(1,2,3,4))) {
                            if (!isScheduleInProgress($begin, $end, strtotime($row['begin']), strtotime($row['end']), $row['CHID'])) {
                                continue;
                            }
                        }
                        $used[$row['teacher']] = $row['title'];
                    }

                    switch($chid) {
                        case 1:
                            $begin = strtotime('+1 day', $begin);
                            $end = strtotime('+1 day', $end);
                            break;
                        case 2:
                            $begin = strtotime('+1 week', $begin);
                            $end = strtotime('+1 week', $end);
                            break;
                        case 3:
                            $begin = strtotime('+1 month', $begin);
                            $end = strtotime('+1 month', $end);
                            break;
                        case 4:
                            $begin = strtotime('+2 week', $begin);
                            $end = strtotime('+2 week', $end);
                            break;
                        default:
                            $end = strtotime('+1 year', $end);
                            break;
                    }

                }
            }

            foreach($teachers as $teacher) {
                $status = $style ='';
                if (isset($used[$teacher['MID']])) {
                    $status = ' [ занят: '.htmlspecialchars($used[$teacher['MID']], ENT_QUOTES).' ]';
                    $style = "style='background: #EEEEEE'";
                }
                $html .= "<option $style value=\"{$teacher['MID']}\" ";
                if ($teacher['MID']==$current) $html .= "selected";
                $html .= "> {$teacher['LastName']} {$teacher['FirstName']} $status</option>";
            }
        }
    }
    $html .= "</select>";
    if ($utf) $html = iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,'UTF-8',$html);
    return $html;
}

function is_module_published($ModID)
{
    if ($ModID) {
        $sql = "SELECT Pub
                FROM mod_list
                WHERE ModID='".(int) $ModID."'";
        $res = sql($sql);
        if (sqlrows($res) && ($row = sqlget($res))) {
            return $row['Pub'];
        }
    }
}

/**
 * Возвращает html select'a групп
 * используется в sajax
 *
 * @param unknown_type $cid
 */
function get_group_select($cid,$current=0) {
    $html = "<select name=\"gr\" size=1>";
    $html .= "<option value=\"0\"";
    if ($current==0) $html .= " selected ";
    $html .= ">--"._("Все")."--</option>";
    if ($cid) {
        $groups = selGrved($cid, $current, true);
        if (is_array($groups) && count($groups)) {
            foreach($groups as $key=>$group) {
                $html .= "<option value=\"{$key}\"";
                if ($key==$current) $html .= " selected ";
                $html .= ">{$group}</option>";
            }
        }
    }
    $html .= "</select>";
    return $html;
}

function get_time_registered($mid,$cid) {
    if ($mid && $cid) {
        $sql = "SELECT time_registered FROM Students WHERE CID='".(int) $cid."' AND MID='".(int) $mid."'";
        $res = sql($sql);
        if (sqlrows($res) && ($row = sqlget($res))) {
            $registered = $row['time_registered'];
            if (preg_match("/^(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})$/", $registered, &$matches)
            || preg_match("/^(\d{4})-(\d{2})-(\d{2})( (\d{2}):(\d{2}):(\d{2}))*$/", $registered, &$matches)) {
                $time_registered = mktime(0,0,0,$matches[2],$matches[3],$matches[1]);
                $ret = (int) ((floor((time()-$time_registered)/60/60/24))*60*60*24 + 1);
            }
        }
    }
    return $ret;
}

// перевод на летнее время не учитываем
function get_courses_student($mid) {
    $ret = 0;
    $tmstamp = time();
    if ($mid) {
        $sql = "
            SELECT DISTINCT Courses.CID as CID
            FROM Students
            LEFT JOIN Courses ON (Courses.CID=Students.CID)
            WHERE Students.MID='".(int) $mid."' AND
            (
              (Courses.Status > 1) OR 
              (Courses.is_poll='1') AND (Courses.`type` = 0)
            )
            ";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $courses[$row['CID']] = $row['CID'];
        }

        if (is_array($courses) && count($courses)) $ret=$courses;
    }
    return $ret;
}

function get_courses_teacher($mid) {
    $ret = 0;
    if ($mid) {

        $sql = "SELECT DISTINCT subjects.subid as CID, subjects.name AS Title
                FROM Teachers, subjects
                WHERE MID='".$mid."' AND subjects.subid=Teachers.CID
                ORDER BY subjects.name ASC";
        $res = sql($sql);
        while ($row=sqlget($res)) {
           $courses[$row['CID']]=$row['CID'];
        }

        if (is_array($courses) && count($courses)) $ret=$courses;

    }
    return $ret;
}

function get_courses_dean($mid) {
    $ret = 0;
    if ($mid) {

        $sql = "SELECT subid AS CID
                FROM subjects
                ORDER BY name ASC";
        $res = sql($sql);
        while ($row=sqlget($res)) {
           $courses[$row['CID']]=$row['CID'];
        }

        if (is_array($courses) && count($courses)) $ret=$courses;

    }
    return $ret;
}

function get_courses_admin($mid) {
    return get_courses_dean($mid);
}

function get_courses_by_status($mid, $status) {
    $cid = (int) getField('Courses', 'CID', 'is_poll', '1');
    $ret = array();
    switch($status) {
        case 1:
            $ret = get_courses_student($mid);
        break;
        case 2:
            $ret = get_courses_teacher($mid);
        break;
        case 3:
            $ret = get_courses_dean($mid);
        break;
        case 4:
            $ret = get_courses_admin($mid);
        break;
    }
    if (isset($ret[$cid])) {
        unset($ret[$cid]);
    }
    return $ret;
}

function get_kurs_by_status($status = -1) {
      $courses = array();
      $query = "SELECT CID FROM Courses WHERE status > '{$status}' ORDER BY Title";
      $res = sql($query);
      while ($row = sqlget($res)) {
        $courses[$row['CID']] = $row['CID'];
      }
      return count($courses) ? $courses : 0;
}

function registered2time($registered) {
    $ret = 0;
    if (preg_match("/^(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})$/", $registered, &$matches)
    || preg_match("/^(\d{4})-(\d{2})-(\d{2})( (\d{2}):(\d{2}):(\d{2}))*$/", $registered, &$matches)) {
        $ret = mktime($matches[4],$matches[5],$matches[6],$matches[2],$matches[3],$matches[1]);
    }
    return $ret;
}

function get_registered_unixtime($mid,$cid) {
    if ($mid && $cid) {
        $sql = "SELECT time_registered FROM Students WHERE CID='".(int) $cid."' AND MID='".(int) $mid."'";
        $res = sql($sql);
        if (sqlrows($res) && ($row = sqlget($res))) {
            $registered = $row['time_registered'];
            if (preg_match("/^(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})$/", $registered, &$matches)
            || preg_match("/^(\d{4})-(\d{2})-(\d{2})( (\d{2}):(\d{2}):(\d{2}))*$/", $registered, &$matches)) {
                $ret = mktime($matches[4],$matches[5],$matches[6],$matches[2],$matches[3],$matches[1]);
            }
        }
    }
    return $ret;
}

function get_test_perm_edit($tid) {
    if ($GLOBALS['controller']->checkPermission(TESTS_PERM_EDIT_OTHERS)) {
        return true;
    }
    if ($tid && $GLOBALS['controller']->checkPermission(TESTS_PERM_EDIT_OWN)) {
        $sql = "SELECT tid FROM test WHERE tid='".(int) $tid."' AND created_by='".(int) $_SESSION['s']['mid']."'";
        $res = sql($sql);
        return sqlrows($res);
    }
    return false;
}

function get_question_perm_edit($kod) {
    if ($GLOBALS['controller']->checkPermission(QUESTION_PERM_EDIT_OTHERS)) {
        return true;
    }
    if ($kod && $GLOBALS['controller']->checkPermission(QUESTION_PERM_EDIT_OWN)) {
        $sql = "SELECT kod FROM list WHERE kod='".$kod."' AND created_by='".(int) $_SESSION['s']['mid']."'";
        $res = sql($sql);
        return sqlrows($res);
    }
    return false;
}

function is_course_locked($cid) {
    return getField('Courses','locked','CID',(int) $cid);
}

function is_course_question_locked($kod) {
    if ($kod) {
        $k = explode('-',$kod);
        $cid = (int) $k[0];
        if ($cid) {
            return is_course_locked($cid);
        }
    }

    return true;
}

function process_max($max,$value) {
    if ($max=='undefined') {
        $max = $value;
    } else {
        if ($max<0) {
            $max = ($value>$max) ? $value : $max;
        } else {
            if ($value>0) {
                $max += $value;
            }
        }
    }
    return $max;
}

function process_min($min,$value) {
    if ($min=='undefined') {
        $min = $value;
    } else {
        if ($min<0) {
            if ($value<0) $min += $value;
        } else {
            if ($value<0) {
                $min = $value;
            } else {
                $min = ($value<$min) ? $value : $min;
            }
        }
    }
    return $min;
}

function get_course_type_options($type) {
    $ret = "<option value=\"0\"";
    if ($type>=0) $ret .= " selected ";
    $ret .= "> "._("свободный")."</option>";
    $ret .= "<option value=\"-1\"";
    if ($type==-1) $ret .= " selected ";
    $ret .= "> "._("назначаемый")."</option>";
    return $ret;
}

function get_course_type_selects($type, $chain) {
    $ret = "<select name=\"TypeDes\">".get_course_type_options($type)."</select>&nbsp;&nbsp;" . $GLOBALS['tooltip']->display('course_access');
    if ($type>=0) $chain = $type;
    $ret .= "&nbsp; <select name=\"chain\">".CChainsList::get_as_options($chain)."</select>&nbsp;&nbsp;" . $GLOBALS['tooltip']->display('course_chains');
    return $ret;
}

function process_online_tests($mid) {
    if ($mid) {
        $sql = "UPDATE loguser SET status='2' WHERE status=0 AND mid='".(int) $mid."'";
        sql($sql);
    }
}

function clean_schedule_locations() {
    if ($_SESSION['s']['mid']) {
        sql("DELETE FROM schedule_locations WHERE teacher='".(int) $_SESSION['s']['mid']."'");
    }
}

function is_structured($mid) {
    if ($mid>0) {
        $sql = "SELECT soid FROM structure_of_organ WHERE mid='".(int) $mid."'";
        $res = sql($sql);
        if (sqlrows($res)) return true;
    }
    return false;
}

function unicode_urldecode($str)
{
  $res = '';

  $i = 0;
  $max = strlen($str) - 6;
  while ($i <= $max)
  {
   $character = $str[$i];
   if ($character == '%' && $str[$i + 1] == 'u')
   {
     $value = hexdec(substr($str, $i + 2, 4));
     $i += 6;

     if ($value < 0x0080) // 1 byte: 0xxxxxxx
       $character = chr($value);
     else if ($value < 0x0800) // 2 bytes: 110xxxxx 10xxxxxx
       $character =
           chr((($value & 0x07c0) >> 6) | 0xc0)
         . chr(($value & 0x3f) | 0x80);
     else // 3 bytes: 1110xxxx 10xxxxxx 10xxxxxx
       $character =
           chr((($value & 0xf000) >> 12) | 0xe0)
         . chr((($value & 0x0fc0) >> 6) | 0x80)
         . chr(($value & 0x3f) | 0x80);
   }
   else
     $i++;

   $res .= $character;
  }

  return $res . substr($str, $i);
}

function search_user_options($search, $mid=0) {
    //$html = "<select name='$name' id='$id' $extra>\n";
    $html = '';
    if (!empty($search)) {
        $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);
        $search = iconv('UTF-8',$GLOBALS['controller']->lang_controller->lang_current->encoding,unicode_urldecode($search));
        $search = trim($search);
        if ($search=='*') $search = '%';
        $sql = "SELECT MID, LastName, FirstName, Patronymic, Login
                FROM People
                WHERE LastName LIKE '%".addslashes($search)."%'
                OR FirstName LIKE '%".addslashes($search)."%'
                OR Login LIKE '%".addslashes($search)."%'
                ORDER BY LastName, FirstName, Login";
        $res = sql($sql);
        while($row = sqlget($res)) {
            if (!$peopleFilter->is_filtered($row['MID'])) continue;
            $html .= "<option value='{$row['MID']}'";
            if ($row['MID']==$mid) $html .= " selected ";
            $html .= ">".htmlspecialchars($row['LastName'].' '.$row['FirstName'].' '.$row['Patronymic'].' ('.$row['Login'].')',ENT_QUOTES)."</option>\n";
        }
    }
    //$html .= "</select>\n";

    return $html;
}

function get_people_count() {
    $sql = "SELECT COUNT(MID) as count FROM People";
    $res = sql($sql);
    if ($row = sqlget($res)) return $row['count'];
}

// File put contents
if (!defined('FILE_APPEND')) define('FILE_APPEND',1);
if (!function_exists('file_put_contents'))
{
    function file_put_contents($n, $d, $flag = false) {
        $mode = ($flag == FILE_APPEND || strtoupper($flag) == 'FILE_APPEND') ? 'a' : 'w';
        $f = @fopen($n, $mode);
        if ($f === false) {
            return 0;
        } else {
            if (is_array($d)) $d = implode($d);
            $bytes_written = fwrite($f, $d);
            fclose($f);
            return $bytes_written;
        }
    }
}

/**
 * Проверка значение в интервале
 *
 * @param int $value
 * @param string $interval x-y, x-; x;, -y
 */
function checkInterval($value, $interval) {
   if (!empty($interval)) {
       if ($interval[strlen($interval)-1] == '-') $interval = substr($interval,0,-1);
       if ($interval[0] == '-') $interval = '0'.$interval;

       if (strstr($interval,'-') !== false) {
           $interval = explode('-',$interval);
           if (count($interval) == 2) {
               return (($value >= $interval[0]) && ($value <= $interval[1]));
           }
       } else {
           return ($value >= $interval);
       }

   }
   return null;
}

function checkCondition($arguments, $function){
    $return = !(integer)$function;
    foreach ($arguments as $argument) {
        if ($argument !== null) {
            $return = $function ? $return || $argument : $return && $argument;
        }
    }
    return $return;
}

function is_module_can_run($ModID) {
    if ($GLOBALS['s']['perm'] > 1) return true;
    $tweek = time();
    $CID = (int) getField('mod_list','CID','ModID',(int) $ModID);
    if ($CID) {
        if (sqlrows(sql("SELECT is_module_need_check FROM Courses WHERE CID = {$CID} AND is_module_need_check = 0"))) {
            return true;
        }

        if ($time_registered = get_registered_unixtime($_SESSION['s']['mid'], $CID)) {
            $days_registered = (int) (time()-$time_registered);
            $sql = "SELECT DISTINCT schedule.*
                    FROM schedule
                    INNER JOIN scheduleID ON (scheduleID.SHEID=schedule.SHEID)
                    WHERE scheduleID.toolParams LIKE '%module_moduleID=".(int) $ModID.";%'
                    AND schedule.CID='".(int) $CID."'
                    AND ((schedule.timetype = 0
                    AND schedule.begin <= ".$GLOBALS['adodb']->DBDate(time()).")
                    OR (schedule.timetype = 1
                    AND schedule.startday <= '".$days_registered."'))";
            $res = sql($sql);
            require_once($GLOBALS['wwf'].'/lib/classes/WeekSchedule.class.php');
            while($row = sqlget($res)) {
                if (WeekSchedule::check_cond($row)) {
                    return true;
                }
            }
        }
    }

    return false;
}
function getUserCard($mid) {
    require_once($GLOBALS['wwf']."/metadata.lib.php");
    require_once($GLOBALS['wwf']."/positions.lib.php");

    $smarty = new Smarty_els();


    if ($mid > 0) {
        $sql = "SELECT * FROM People WHERE MID='".$mid."'";
        $res = sql($sql);
        if (sqlrows($res)) {
            $info = sqlget($res);

            $photo = getPhoto($mid);
            if (empty($photo)) $photo = "<img src=\"{$GLOBALS['sitepath']}images/people/nophoto.gif\" alt=\""._("Нет фотографии")."\" border=0>";
            $info['photo'] = $photo;

            $metadataTypes = explode(';',REGISTRATION_FORM);
            if (is_array($metadataTypes) && count($metadataTypes)) {
                foreach($metadataTypes as $metadataType) {

                    $metadata = read_metadata (stripslashes($info['Information']), $metadataType);
                    $default_metadata = load_metadata($metadataType);
                    $flow = '';
                    if (is_array($metadata) && count($metadata)) {
                        foreach($metadata as $key => $value) {
                            if (($key == 0) && ($value['flow'] == 'line')) $flow = 'line';
                            if(is_array($value) && count($value)) {
                                if (isset($value['not_public']) && $value['not_public']) {
                                    continue;
                                }
                                if(trim($value['value']) != trim($default_metadata[$key]['value'])) {
                                    if ($flow != 'line') {
                                        $info['meta'][$value['name']]   = $value['value'];
                                        if (strlen($value['title'])) {
                                            $info['titles'][$value['name']] = $value['title'];
                                        } else {
                                            $info['titles'][$value['name']] = get_reg_block_title($metadataType);
                                        }
/*                                        if (isset($value['title'])) {
                                            $info['meta'][$value['title']] = $value['value'];
                                        } else {
                                            $info['meta']['&nbsp;'] = $value['value'];
                                        }
*/                                    } else {

                                        if (!isset($info['meta'][$metadataType])) {
                                            $info['meta'][$metadataType] = '';
                                            $info['titles'][$metadataType] = get_reg_block_title($metadataType);
                                        }

                                        $info['meta'][$metadataType] .= $value['value'].' ';
                                    }
                                }
                                $flow = $value['flow'];
                            }
                        }
                    }

                }
            }

    /*      $metadataTypes = explode(';',REGISTRATION_FORM);
            if (is_array($metadataTypes) && count($metadataTypes)) {
                foreach($metadataTypes as $metadataType) {
                    $arr = read_metadata (stripslashes($info['Information']), $metadataType);
                    if (is_array($arr) && count($arr)) {
                        foreach ($arr as $item) {
                            if (!empty($item['value'])) {
                                if (empty($item['title'])) $item['title'] = get_reg_block_title($metadataType);
                                $info['meta'][$item['title']] = $item['value'];
                            }
                        }
                    }
                }
            }
    */

            $sql = "
                SELECT
                  structure_of_organ.soid as soid,
                  structure_of_organ.name as position,
                  structure_of_organ.code as hiscode,
                  structure_of_organ_parent.name,
                  structure_of_organ_parent.code
                FROM
                  structure_of_organ
                  INNER JOIN structure_of_organ structure_of_organ_parent ON (structure_of_organ.owner_soid = structure_of_organ_parent.soid)
                WHERE
                  (structure_of_organ.`mid` = '{$mid}')
                ORDER BY
                  structure_of_organ_parent.code
            ";
            $res2 = sql($sql);
            while ($row2 = sqlget($res2)) {
                $orgunits[] = array('name' => $row2['name'], 'code' => $row2['code'], 'position' => $row2['position']);
    //            $info['hiscode'] = $row2['hiscode'];
                $info['hiscode'] = get_code_recursive($row2['soid']);
            }
            $info['soid_info'] = $orgunits;
        } else $msg = _("Пользователь не найден");

    } else $msg = _("Пользователь не найден");

    $smarty->assign('info',$info);
    $smarty->assign('msg',$msg);
    $html = $smarty->fetch('userinfo.tpl');
    return $html;
}

function month2number($month) {
    $months = array(
       _('Январь') =>   '01',
       _('Февраль') =>  '02',
       _('Март') =>     '03',
       _('Апрель') =>   '04',
       _('Май') =>      '05',
       _('Июнь') =>     '06',
       _('Июль') =>     '07',
       _('Август') =>   '08',
       _('Сентябрь') => '09',
       _('Октябрь') =>  '10',
       _('Ноябрь') =>   '11',
       _('Декабрь') =>  '12');

    return $months[trim($month)];
}

function getStructureItemCard($soid) {
    $_REQUEST['soid'] = $soid;
    $soidinfocontroller = new CSoidInfoController();
    $soidinfocontroller->init();
    $soidinfocontroller->model = new CSoidInfoModel();
    $soidinfocontroller->model->init();
    $soidinfocontroller->_set_soid();
    $soidinfocontroller->model->execute();
    return $soidinfocontroller->view->fetch($soidinfocontroller->model->position);
}

function get_oids($cid){
	$base_prev_ref = '-1';
	$oids = array();
	do {
		$res1 = sql("SELECT oid, mod_ref, level, Title FROM organizations WHERE CID = '{$cid}' AND prev_ref = {$base_prev_ref}");
		if ($row1 = sqlget($res1)) {
			$oids[] = $row1;
			$base_prev_ref = $row1['oid'];
		} else {
			$base_prev_ref = false; // последний элемент в дереве
		}
	} while($base_prev_ref);
	return $oids;
}

function getFCKEditorCode($name, $value, $width=500, $height=300) {
    require_once($GLOBALS['wwf']."/lib/FCKeditor/fckeditor.php");

    ob_start();
    $oFCKeditor = new FCKeditor($name) ;
    $oFCKeditor->BasePath   = "{$GLOBALS['sitepath']}lib/FCKeditor/";
    $oFCKeditor->Value      = $value;
    $oFCKeditor->Width      = $width;
    $oFCKeditor->Height     = $height;
    $oFCKeditor->ToolbarSet = 'ForumToolbar';
    $fck_code = $oFCKeditor->Create() ;
    $fck_code = ob_get_contents();
    ob_end_clean();

    return $fck_code;
}


function checkPermitionToWatch($scriptName) {
    $scriptName = substr($scriptName, strrpos($scriptName,'/')+1);

    $allowedUrls = array('reg.php4',
                         'orders.php',
                         'order.php',
                         'lib.php',
                         'lib_get.php',
                         'student_loged.php',
                         'student_newcourses.php',
                         'student_shedule.php',
                         'student_announcements.php',
                         'pass.php4');
    if ($GLOBALS['s']['perm']>1) {
        $allowedUrls = array_merge($allowedUrls, array('forum.php',
                                                       /*'guestbook.php4',*/
                                                       'formula.php',)
                                   );
    }

    if (!in_array($scriptName, $allowedUrls) && !$_GET['exit'] && !$_GET['chLevel']) {

        if (($_SESSION['s']['perm'] == 1) && !$_SESSION['s']['skurs']) {
            // есть в претендентах
            if(sqlrows(sql("SELECT * FROM claimants WHERE MID = '{$_SESSION['s']['mid']}'"))) {
                $GLOBALS['controller']->setMessage(_('Вы не зарегистрированы ни на одном из активных курсов и не можете просматривать данную страницу'),false,'orders.php?page_id=m2103');
                $GLOBALS['controller']->terminate();
                //refresh($GLOBALS['sitepath'].'orders.php?page_id=m2103');
                exit();
            }

            // есть в выпускниках
            if(sqlrows(sql("SELECT * FROM graduated WHERE MID = '{$_SESSION['s']['mid']}'"))) {
				$GLOBALS['controller']->setMessage(_('Вы закончили обучение по всем курсам. Вы не можете просматривать данную страницу'),false,'reg.php4?private=1');
                $GLOBALS['controller']->terminate();
                //refresh($GLOBALS['sitepath'].'reg.php4?private=1');
                exit();
            }

            // нет курсов, не является претендентом, не является выпускником
            $GLOBALS['controller']->setMessage(_('Вы не зарегистрированы ни на одном из активных курсов и не можете просматривать данную страницу'),false,'orders.php?page_id=m2103');
            $GLOBALS['controller']->terminate();
            //refresh($GLOBALS['sitepath'].'orders.php?page_id=m2103');
            exit();
        }

        if (($_SESSION['s']['perm'] == 2) && !$_SESSION['s']['tkurs'] && (!isset($_SESSION['s']['old_mid']) || $_SESSION['s']['mid'] == $_SESSION['s']['old_mid'])) {

            $GLOBALS['controller']->setMessage(_('Вы не зарегистрированы ни на одном из активных курсов и не можете просматривать данную страницу'),false,'orders.php?page_id=m2103');
            $GLOBALS['controller']->terminate();
            //refresh($GLOBALS['sitepath'].'orders.php?page_id=m2103');
            exit();
        }
    }
}

/**
 * Ресайз картинки
 *  (с созданием файла)
 */
function makePreviewImage($src_file,$dst_file,$dst_width=600,$dst_height=0) {
    $info = @getimagesize($src_file);
    if(!$info) return false;

    $src_width = $info[0];
    $src_height = $info[1];

    $dst_height = $dst_height ? $dst_height : (($src_height*$dst_width)/$src_width);
    $dst_width = $dst_width ? $dst_width : (($src_width*$dst_height)/$src_height);

    $mime = $info['mime'];
    if($mime != "image/gif" && $mime != "image/jpeg" && $mime != "image/png") return false;
    if(!is_numeric($dst_width) || $dst_width < 1 || !is_numeric($dst_height) || $dst_height < 1) return false;

    if($mime == "image/gif") {
      $src_img = imagecreatefromgif($src_file);
      $dst_img = imagecreate($dst_width,$dst_height);
    }
    elseif($mime == "image/jpeg") {
      $src_img = imagecreatefromjpeg($src_file);
      $dst_img = imagecreatetruecolor($dst_width,$dst_height);
    }
    elseif($mime == "image/png") {
      $src_img = imagecreatefrompng($src_file);
      $dst_img = imagecreatetruecolor($dst_width,$dst_height);
    }

    imagefill($dst_img,0,0,imagecolorallocate($dst_img,255,255,255));

    $src_ratio = $src_width / $src_height;
    $dst_ratio = $dst_width / $dst_height;

    if($src_ratio < $dst_ratio)
    {
      $dst_H = $dst_height;
      $dst_W = ($src_width * $dst_height) / $src_height;
      $dst_X = ($dst_width / 2) - ($dst_W / 2);
      $dst_Y = 0;
    }
    else
    {
      $dst_H = ($src_height * $dst_width) / $src_width;
      $dst_W = $dst_width;
      $dst_X = 0;
      $dst_Y = ($dst_height / 2) - ($dst_H / 2);
    }

    imagecopyresampled($dst_img,$src_img,$dst_X,$dst_Y,0,0,$dst_W,$dst_H,$src_width,$src_height);

    if($mime == "image/gif")
    {
        if(!@imagegif($dst_img,$dst_file)) return false; else chmod($dst_file,0644);
    }
    elseif($mime == "image/jpeg")
    {
        if(!@imagejpeg($dst_img,$dst_file)) return false; else chmod($dst_file,0644);
    }
    elseif($mime == "image/png")
    {
        if(!@imagepng($dst_img,$dst_file)) return false; else chmod($dst_file,0644);
    }
    return true;
  }

function getProviders() {
    $ret = array();
    $sql = "SELECT * FROM providers ORDER BY Title";
    $res = sql($sql);

    while($row = sqlget($res)) {
        $ret[$row['id']] = $row;
    }
    return $ret;
}

function getProvidersList() {
    $ret = array();
    $sql = "SELECT * FROM providers ORDER BY Title";
    $res = sql($sql);

    while($row = sqlget($res)) {
        $ret[$row['id']] = $row['title'];
    }
    return $ret;
}

function webinar_getmaterials($cid, $search, $pointId = null) {
	require_once($GLOBALS['wwf'].'/lib/classes/Webinar.class.php');
	$search = iconv('UTF-8',$GLOBALS['controller']->lang_controller->lang_current->encoding,unicode_urldecode($search));
    return CWebinar::getMaterialsOptions($cid, $search, $pointId);
}

function getAvgRating($cid, $teacher=0) {
    $sql = "SELECT AVG(rating) AS rating FROM ratings WHERE cid = '$cid' AND teacher = '$teacher'";
    $res = sql($sql);

    while($row = sqlget($res)) {
        return $row['rating'];
    }
    return 0;
}

function getProgressBar($proccompl) {
  $str.="<table width='100%' border=0 cellspacing=1 cellpadding=0><tr>";
  if ($proccompl && $proccompl!=_("нет")) {
    $str.=" <td width='".$proccompl."%' align='right' style=\"background: #BBB;\">";
    if ($proccompl>50) $str.=(int) $proccompl."%";
      $str.="</td>";
    }

  if ($proccompl<100) {
    $str.="<td align='left' style=\"background: #FFF\">&nbsp;";
    if ($proccompl<=50) $str.=(int) $proccompl."%";
    $str.="</td>";
  }
  $str.="</tr></table>";
  return $str;
}


function dirinfo($dir){
	$ret['size'] = 0;
	$ret['files'] = 0;
	if (file_exists($dir)) {
	    $d = dir($dir);
	    while($entry = $d->read()) {
	        if ($entry != "." && $entry != "..") {
	            if (is_dir($dir."/".$entry)) {
	                $info = dirinfo($dir."/".$entry);
	                $ret['size'] += $info['size'];
	                $ret['files'] += $info['files'];
	            } else {
	                $ret['size'] += filesize($dir.'/'.$entry);
	                $ret['files']++;
	            }
	        }
	    }
	    $d->close();
	}
    return $ret;
}

function sendRequestPost($host, $port, $index, $params) {
    $packet = "POST /$index HTTP/1.0\r\n";
    $packet .= "Host: $host:$port\r\n";
    $packet .= "Content-Length: ".strlen($params)."\r\n";
    $packet .= "Referer: ".$GLOBALS['sitepath']."\r\n";
    $packet .= "Content-Type: application/x-www-form-urlencoded\r\n";
    $packet .= "Connection: keep-alive\r\n";
    $packet .= "Cache-Control: no-cache\r\n\r\n";
    $packet .= $params."\r\n\r\n";

    $ock = fsockopen(gethostbyname($host), $port);
    $html='';
    if ($ock) {
	    fputs($ock,$packet);
	    while (!feof($ock))
	    {
	        $html .= fgets($ock);
	    }
	    fclose($ock);
    }

    return $html;
}

function getUserToken() {
    if ($_SESSION['s']['login'] && ($_SESSION['s']['mid'] > 0)) {
    	sql("UPDATE People SET Position = '".md5(date('Y-m-d H:i:s'))."', CellularNumber = ".$GLOBALS['adodb']->Quote($GLOBALS['sitepath'])." WHERE MID = ".$_SESSION['s']['mid']);
    	return md5(md5(
    	    $_SESSION['s']['mid'].
    	    getField('People', 'Login', 'MID', $_SESSION['s']['mid']).
    	    getField('People', 'Password', 'MID', $_SESSION['s']['mid']).
    	    getField('People', 'Information', 'MID', $_SESSION['s']['mid']).
            getField('People', 'Position', 'MID', $_SESSION['s']['mid'])
    	));
    }
}

function hasReports($perm) {
    $value = array_search($perm, $GLOBALS['profiles_basic_ids']);
    if (isset($GLOBALS['profiles_inheritance'][$value][0])) {
        $value = $GLOBALS['profiles_inheritance'][$value][0];
    }
    if ($value) {
        $sql = "SELECT reports.report_id FROM reports
                    INNER JOIN reports_roles ON (reports.report_id = reports_roles.report_id)
                    WHERE reports_roles.role = " . $GLOBALS['adodb']->Quote($value); // статус отчета более не имеет значения
        $res = sql($sql);
        if (sqlrows($res)) {
            return true;
        }
    }
    return false;
}
?>