<?php
//defines
$include=TRUE ;

require ("setup.inc.php");
echo show_tb();
$GLOBALS['controller']->captureFromOb(CONTENT);
require ("adm_fun.php4");

require_once($GLOBALS['wwf'].'/lib/sajax/SajaxWrapper.php');

$js = 
    "
    function show_user_select(html) {
        var elm = document.getElementById('people');
        if (elm) elm.innerHTML = '<select size=10 id=\"all_users\" name=\"del_users[]\" multiple style=\"width:100%\">'+html+'</select>';
    }
            
    function get_user_select(str) {
        var current = 0;
        
        var select = document.getElementById('search_people');
        if (select) current = select.value;
        
        var elm = document.getElementById('people');
        if (elm) elm.innerHTML = '<select size=10 id=\"all_users\" name=\"del_users[]\" multiple style=\"width:100%\"><option>"._("Загружаю данные...")."</option></select>';
                
        get_user_select_used('');
        x_search_people_unused(str, show_user_select);
    }

    function show_user_select_used(html) {
        var elm = document.getElementById('people_used');
        if (elm) elm.innerHTML = '<select size=10 id=\"users\" name=\"need_users[]\" multiple style=\"width: 100%\">'+html+'</select>';            
    }
            
    function get_user_select_used(str) {
        var elm = document.getElementById('people_used');
        if (elm) elm.ennerHTML = '<select size=10 id=\"users\" name=\"need_users[]\" multiple style=\"width: 100%\"><option>"._("Загружаю данные...")."</option></select>';
        x_search_people_used(str, show_user_select_used);
    }    
    
    function showHideString(obj, str) {                
        str = str ? str : '"._("введите часть имени или логина")."';
        if (obj.value == str) {
            obj.value = '';
            obj.style.fontStyle = 'normal';
            obj.style.color = 'black';
        }else {
            if (!obj.value) {
                obj.style.fontStyle = 'italic';
                obj.style.color = 'grey';
                obj.value = str;                
            }
        }
    }
    $(function() {
        showHideString($('#search_people').get(0))
    });
    ";

$sajax_javascript = CSajaxWrapper::init(array('search_people_unused','search_people_used')).$js;

$smarty = new Smarty_els();

switch($action) {
    case 'assign':
        $mids = array();            
        $sql = "SELECT DISTINCT MID FROM deans";
        $res = sql($sql);
            
        while($row = sqlget($res)) {
            $mids[$row['MID']] = $row['MID'];
        }
                            
        // Назначение на курсы
        $role = CRole::get_default_role('dean');
        if (is_array($_POST['need_users']) && count($_POST['need_users'])) {
            foreach($_POST['need_users'] as $v) {
                if (!isset($mids[$v])) {                            
                    $res = sql("INSERT INTO deans (MID) VALUES ('$v')");
                    if ($res) {
                        CRole::add_mid_to_role($v, $role);
                    }
                }
            }
        }
        // Удаление с курсов
        if (is_array($_POST['del_users']) && count($_POST['del_users'])) {
            foreach($_POST['del_users'] as $v) {
                if (isset($mids[$v])) {
                    $res = sql("DELETE FROM deans WHERE MID='".(int) $v."'");
                    if ($res) {
                        $res = sql("SELECT pmid FROM permission_groups WHERE type LIKE 'dean'");
                        $rows = array();
                        while($row = sqlget($res)) $rows[] = $row['pmid'];
                        if (is_array($rows) && count($rows)) {
                            sql("DELETE FROM permission2mid WHERE mid='".(int) $v."' AND pmid IN ('".join("','",$rows)."')", "admErr03");          
                        }
                    }
                }
            }
        }                
        
        $GLOBALS['controller']->setView('DocumentBlank');
        $url = $GLOBALS['sitepath']."admin/dekan.php4";
        $GLOBALS['controller']->setMessage(_('Учебная администрация назначена'), JS_GO_URL, $url);
        $GLOBALS['controller']->terminate();
        exit();
        
    default:
    $sql = "SELECT People.MID, People.LastName, People.FirstName, People.Patronymic, People.Login 
            FROM People 
            ORDER BY People.LastName, People.FirstName, People.Login";
    $res = sql($sql);
    
    //$peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);
    
    $search = '';
    //if (sqlrows($res)<ITEMS_TO_ALTERNATE_SELECT) $search = '*';
    
    while($row = sqlget($res)) {
        //if ($peopleFilter->is_filtered($row['MID'])) {
            $all_users[$row['MID']] = $row['LastName'].' '.$row['FirstName'].' '.$row['Patronymic'].' ('.$row['Login'].')';
            $mids[] = $row['MID'];
        //}
    }
    
            
    if (is_array($mids) && count($mids)) $sql_mids = "AND MID IN ('".join("','",$mids)."')";
    
    $do_not_delete = array();
    $sql = "SELECT DISTINCT MID FROM deans WHERE 1=1 $sql_mids";
    $res = sql($sql);
    while($row = sqlget($res)) {
        if (isset($all_users[$row['MID']]))
        $users[$row['MID']] = $all_users[$row['MID']];
    }
    
    if (is_array($all_users) && count($all_users) && is_array($users) && count($users)) {
        $all_users = array_diff($all_users,$users);
    }
    
    asort($users);        
    
    $unused = '';
    if (is_array($all_users) && count($all_users) && ($search=='*')) {
        foreach($all_users as $mid=>$name) {
           $unused .= "<option value=\"$mid\"> ".htmlspecialchars($name,ENT_QUOTES)."</option>";
        }
    }

    $used = '';
    if (is_array($users) && count($users)) {
        foreach($users as $mid=>$name) {
            $used .= "<option value=\"$mid\"";
            if (in_array($mid,$do_not_delete)) $used .= "dontmove=\"dontmove\" style=\"background: #EEEEEE;\"";
            $used .= "> ".htmlspecialchars($name,ENT_QUOTES)."</option>";                
        }
    }
    
    
    $smarty->assign('users',$used);
    $smarty->assign('count_users',count($users));
    $smarty->assign('search',$search);
    $smarty->assign('all_people',$unused);
}

$smarty->assign('sajax_javascript',$sajax_javascript);
$smarty->assign('okbutton',okbutton());
$smarty->assign('sitepath',$sitepath);
$html = $smarty->fetch('dekan.tpl');
echo $html;
$GLOBALS['controller']->captureFromReturn(CONTENT,$html);
$GLOBALS['controller']->terminate();
exit();


define("FILE_LOG", "zlog/xml/log.xml");
define("ENCODING_WESTERN", "ISO-8859-1");
define("ENCODING_RUSSIAN", "CP1251");


 if (isset($_GET['remove'])) {
    removeFromDeans($_GET['remove']);
    $GLOBALS['controller']->setMessage(_("Пользователь удален из учебной администрации"));
 }
 if (isset($_GET['add'])) {
    addFromDeans($_GET['add']);
    $GLOBALS['controller']->setMessage(_("Пользователь добавлен в учебную администрацию"));
    $GLOBALS['controller']->setMessage(_("Пользователь добавлен в учебную администрацию"));
 }

 if (isset($ok))
         {
          if  (isset($HTTP_GET_VARS['name']) && isset($HTTP_GET_VARS['email']))

              {
             if  (!empty($HTTP_GET_VARS['name']) && !empty($HTTP_GET_VARS['email']))
                {
                        $result=0;
                        $result=sql_query(9,return_valid_value($_GET['name']));
                        $result=sql_query(10,return_valid_value($_GET['email']));
                        if (!$result) echo "<font color='red'><b>New Information not added</b></font>";
                        else {
                            if ($GLOBALS['controller']->enabled) $GLOBALS['controller']->setMessage(_("Информация сохранена"));
                            else
                            echo "<b>Save succes</b>";
                        }
                } //empty

              } // if all not empty
              else
              {
                        $res=sql_query(6,"Name");
                        $result=@sqlget($res);
                        $name=$result['value'];
                        $res=sql_query(6,"EMail");
                        $result=@sqlget($res);
                        $email=$result['value'];
              }
         } // if isset $ok-yes
         else
         {
                        $res=sql_query(6,"Name");
                        $result=@sqlget($res);
                        $name=$result['value'];
                        $res=sql_query(6,"EMail");
                        $result=@sqlget($res);
                        $email=$result['value'];
         }

debug_yes("array",$HTTP_COOKIE_VARS);
debug_yes("array",$HTTP_GET_VARS);
debug_yes("result",$result);
debug_yes("login",$login);
debug_yes("name",$name);
debug_yes("pass",$pass);
debug_yes("email",$email);

switch ($_POST['hid_act']) {
        case "add_pmgroup":
                $r = sql("SELECT * FROM permission_groups WHERE name='{$_POST['txt_pmname']}'");
                if (!sqlrows($r)) {
                        $r = sql("INSERT INTO permission_groups (name, type) values ('{$_POST['txt_pmname']}', 'dean')");
                }
                break;
        case "edit_pmgroup":
                    $r = sql("DELETE FROM permission2act WHERE pmid!='1' AND type = 'dean'");
                    if (is_array($_POST['ch_pmgroups'])) {
                        foreach ($_POST['ch_pmgroups'] as $acid => $arrPmid) {
                                foreach ($arrPmid as $pmid => $blabla) {
                                        $r = sql("INSERT INTO permission2act (acid, pmid, type) values ('{$acid}', '{$pmid}', 'dean')");
                                }
                        }
                    }
                break;
        case "add_to_pmgroup":
                if (is_array($_POST['sel_pmgroup'])){
                        $arrMids = array_keys($_POST['sel_pmgroup']);
                        $strMids = implode(", ", $arrMids);
                        foreach ($_POST['sel_pmgroup'] as $mid => $pmid) {
                                /*$q = "
                                        SELECT
                                          CONCAT(People.LastName, ' ',  People.FirstName, ' ', People.Patronymic) as full_name,
                                          permission_groups.name as pm_name
                                        FROM
                                          permission2mid
                                          INNER JOIN permission_groups ON (permission2mid.pmid = permission_groups.pmid)
                                          INNER JOIN People ON (permission2mid.`mid` = People.`MID`)
                                        WHERE
                                          permission2mid.`mid`='{$mid}'
                                ";*/
                                $q = "
                                        SELECT
                                          ".$adodb->Concat("People.LastName","' '","People.FirstName", "' '", "People.Patronymic")." as full_name,
                                          permission_groups.name as pm_name
                                        FROM
                                          permission2mid
                                          INNER JOIN permission_groups ON (permission2mid.pmid = permission_groups.pmid)
                                          INNER JOIN People ON (permission2mid.`mid` = People.`MID`)
                                        WHERE
                                          permission2mid.`mid`='{$mid}' AND permission_groups.type = 'dean'
                                ";
                                $r = sql($q);
                                $a = sqlget($r);
                                $r = sql("SELECT * FROM permission_groups WHERE pmid='{$pmid}'");
                                $aa = sqlget($r);
                                $query = "SELECT * FROM permission_groups WHERE type = 'dean'";
                                $result = sql($query,"err");
                                $in_condition = "(";
                                while($row = sqlget($result)) {
                                        $in_condition .= $row['pmid'].",";
                                }
                                $in_condition = trim($in_condition, ",").")";
                                $r = sql("DELETE FROM permission2mid WHERE mid='{$mid}' AND pmid  IN $in_condition", "errrrr");
                                $r = sql("INSERT INTO permission2mid (pmid, mid) values ('{$pmid}', '{$mid}')");
                                if ($r && ($a['pm_name'] != $aa['name'])) 
                                    if((float) PHP_VERSION < 5)
                                        logPmAlteration($a['full_name'], $a['pm_name'], $aa['name']);
                        }
                }
                break;
        default:
                break;
}

if (isset($_GET['del'])) {
        $intDefaultPmid = getDefaultPmGroup("dean");
        if ($intDefaultPmid != (integer)$_GET['del']) {
            $r = sql("UPDATE permission2mid SET pmid='{$intDefaultPmid}' WHERE pmid={$_GET['del']}");
            $r = sql("DELETE FROM permission2act WHERE pmid={$_GET['del']}");
            $r = sql("DELETE FROM permission_groups WHERE pmid={$_GET['del']}");
        }
}

?>

<center>
<?php
$GLOBALS['controller']->captureFromOb(TRASH);
?>
<br><br><br>
<table align="center" border="0" cellspacing="1" cellpadding="5" width="80%" class=br  style="font-size:13px">
   <tr align="center">
      <td class=questt>
        <b><a href="dekan.php4"><?=_("Учебная администрация")?></a></b>
          </td>
   </tr>
  </table>
<br>
<?php
$GLOBALS['controller']->captureStop(TRASH);
 if (isset($name) && isset($email))
        {
?>

<!--form action="dekan.php4" method=get target="_self" name="dekan">

<table align="center" border="0" cellspacing="1" cellpadding="5" width="80%" class=br  style="font-size:13px">
   <tr align="center">
      <td width="50%" class=questt>
           <?=get_red_text(_("Имя")." ",$name)?>
          </td>
      <td width="50%" class=questt>
           <input name="name" style="width:100%" value="<?=$name?>">
          </td>
   </tr>

   <tr align="center">
      <td width="50%" bgcolor="white">
           <?=get_red_text("E-mail",$email)?>
          </td>
      <td width="50%" bgcolor="white">
           <input name="email" style="width:100%" value="<?=$email?>">
          </td>
   </tr>
   <tr align="center">
      <td width="50%" class=questt>
           <input type=submit name="ok" value="<?=_("Сохранить")?>">
          </td>
      <td width="50%" class=questt>
           <input type=reset name="ok" value="<?=_("Сброс")?>">
          </td>
   </tr>
  </table>
</form-->


<?
 } //if isset all varibles

 $strXmlLink = $sitepath . FILE_LOG;

$search = '';
if (get_people_count()<ITEMS_TO_ALTERNATE_SELECT) $search='*'; 
?>
<script type="text/javascript">
<!--
<?=$sajax_javascript?>
//-->
</script>
<table width=80% class=main cellspacing=0>
<tr><th><?=_("Добавить в учебную адинистрацию")?></th></tr>
<tr><td align=left>
<form action="dekan.php4" method=get target="_self">
   <input type="button" value="<?=_("Все")?>" style="width: 10%" onClick="if (elm = document.getElementById('search_people')) elm.value='*'; get_user_select('*');" >
   <input id="search_people" type="text" value="<?=$search?>" style="width: 89%" onKeyUp="if (typeof(filter_timeout)!='undefined') clearTimeout(filter_timeout); filter_timeout = setTimeout('get_user_select(\''+this.value+'\');',1000);">
   <br>
   <div id="people">    
   <select id="add" name='add' style="width:100%">
<?
  if ($search=='*')  
  echo peopleSelect("",0, "deans", true, true);
?>
   </select>
   </div>
   </td>
</tr>
<tr><td><?=okbutton()?></td></tr>
</table><br>
</form>
<table width=80% class=main cellspacing=0>
<form action="dekan.php4" method="POST" target="_self">
<tr><th><?=_("Имя")?></th><th><?=_("Действие")?></th></tr>
<?
   echo deansList();
?>
  </tr>
</form>
</table>
</center>
<?php
//require_once("adm_b.php4");
$GLOBALS['controller']->captureStop(CONTENT);
echo show_tb();

function logPmAlteration($strPerson, $strPrev, $strNext)
{
        $strCharsetPerson = (ord($strPerson) < 192) ? ENCODING_WESTERN : ENCODING_RUSSIAN ;
        $strCharsetPrev= (ord($strPrev) < 192) ? ENCODING_WESTERN : ENCODING_RUSSIAN ;
        $strCharsetNext = (ord($strNext) < 192) ? ENCODING_WESTERN : ENCODING_RUSSIAN ;

        $strPerson = iconv($strCharsetPerson, "UTF-8", $strPerson);
        $strPrev = iconv($strCharsetPrev, "UTF-8", $strPrev);
        $strNext = iconv($strCharsetNext, "UTF-8", $strNext);

        $strPath = $_SERVER['DOCUMENT_ROOT'] . "/" . FILE_LOG;

        if ($xml = domxml_open_file($strPath)) {

                $log = $xml->document_element();

                $permissions = $xml->create_element("permissions");
                $permissions->set_attribute("date", date("Y-m-d H:i:s"));
                $log->append_child($permissions);

                $person = $xml->create_element("person");
                $person->set_content($strPerson);
                $permissions->append_child($person);

                $ch_from = $xml->create_element("ch_from");
                $ch_from->set_content($strPrev);
                $permissions->append_child($ch_from);

                $ch_to = $xml->create_element("ch_to");
                $ch_to->set_content($strNext);
                $permissions->append_child($ch_to);

                $xml->dump_file($strPath, false, true);
        }
}

function search_people_unused($search, $current=0) {
    $html = '';
    if (!empty($search)) {
        $search = iconv('UTF-8',$GLOBALS['controller']->lang_controller->lang_current->encoding,unicode_urldecode($search));
        $search = trim($search);
        $search = str_replace('*','%',$search);
        $where = "AND (People.LastName LIKE '%".addslashes($search)."%'
        OR People.FirstName LIKE '%".addslashes($search)."%'
        OR People.Login LIKE '%".addslashes($search)."%')";
        $html .= peopleSelect("",$current,"deans",true,true,$where);
    }
    return $html;
}

function search_people_used($search) {
    $do_not_delete = array();
    $html = '';
    $sql = "SELECT People.MID, People.LastName, People.FirstName, People.Patronymic, People.Login 
            FROM People
            ORDER BY People.LastName, People.FirstName, People.Login";        
    $res = sql($sql);
        
//    $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);
                
    while($row = sqlget($res)) {
//        if ($peopleFilter->is_filtered($row['MID'])) {
            $all_users[$row['MID']] = $row['LastName'].' '.$row['FirstName'].' '.$row['Patronymic'].' ('.$row['Login'].')';
            $mids[] = $row['MID'];
//        }
    }
                
    if (is_array($mids) && count($mids)) $sql_mids = "AND MID IN ('".join("','",$mids)."')";

    $users = array();
    $sql = "SELECT DISTINCT MID FROM deans WHERE 1=1 $sql_mids";
    $res = sql($sql);
    while($row = sqlget($res)) {
        if (isset($all_users[$row['MID']]))
        $users[$row['MID']] = $all_users[$row['MID']];
    }

    asort($users);        
    if (is_array($users) && count($users)) {
        foreach($users as $mid=>$name) {
            $html .= "<option value=\"$mid\"";
            if (in_array($mid,$do_not_delete)) $html .= "dontmove=\"dontmove\" style=\"background: #EEEEEE;\"";
            $html .= "> ".htmlspecialchars($name,ENT_QUOTES)."</option>";
        }
    }
    return $html;
}

?>