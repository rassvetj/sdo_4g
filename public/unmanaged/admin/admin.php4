<?php

//defines

$include=TRUE ;

// include

require ("setup.inc.php");
//require ("adm_t.php4");
echo show_tb();
$GLOBALS['controller']->captureFromOb(CONTENT);
require ("adm_fun.php4");

if ($s[perm]<4) exitmsg("-");

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
        $sql = "SELECT DISTINCT MID FROM admins";
        $res = sql($sql);
            
        while($row = sqlget($res)) {
            $mids[$row['MID']] = $row['MID'];
        }
                            
        // Назначение на курсы
        $role = CRole::get_default_role('admin');
        if (is_array($_POST['need_users']) && count($_POST['need_users'])) {
            foreach($_POST['need_users'] as $v) {
                if (!isset($mids[$v])) {                            
                    $res = sql("INSERT INTO admins (MID) VALUES ('$v')");
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
                    sql("DELETE FROM admins WHERE MID='".(int) $v."'");
                }
            }
        }                
        
        $GLOBALS['controller']->setView('DocumentBlank');
        $url = $GLOBALS['sitepath']."admin/admin.php4";
        $GLOBALS['controller']->setMessage(_('Администраторы назначены'), JS_GO_URL, $url);
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
    $sql = "SELECT DISTINCT MID FROM admins WHERE 1=1 $sql_mids";
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
$html = $smarty->fetch('admin.tpl');
echo $html;
$GLOBALS['controller']->captureFromReturn(CONTENT,$html);
$GLOBALS['controller']->terminate();
exit();

function search_people_unused($search) {
    
    $html = '';
    if (!empty($search)) {
        $search = iconv('UTF-8',$GLOBALS['controller']->lang_controller->lang_current->encoding,unicode_urldecode($search));
        $search = trim($search);
        $search = str_replace('*','%',$search);
        $where = "AND (People.LastName LIKE '%".addslashes($search)."%'
        OR People.FirstName LIKE '%".addslashes($search)."%'
        OR People.Login LIKE '%".addslashes($search)."%')";
        $html .= peopleSelect("",$current,"admins",true,true,$where);
    }
    return $html;
}

function search_people_used($search) {
    $html = '';
    $sql = "SELECT People.MID, People.LastName, People.FirstName, People.Login 
            FROM People
            ORDER BY People.LastName, People.FirstName, People.Patronymic, People.Login";        
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
    $sql = "SELECT DISTINCT MID FROM admins WHERE 1=1 $sql_mids";
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

//$connect=get_mysql_base();

 if (isset($_GET['remove'])) {
     removeFromadmins($_GET['remove']);
     $GLOBALS['controller']->setMessage(_("Пользователь удален из администрации"));
 }
 if (isset($_GET['add'])) {
     addFromadmins($_GET['add']);
     $GLOBALS['controller']->setMessage(_("Пользователь добавлен в администрацию"));
 }

 if (isset($ok))
 {
          if  (isset($HTTP_GET_VARS['name']) && isset($HTTP_GET_VARS['email']))

              {
             if  (!empty($HTTP_GET_VARS['name']) && !empty($HTTP_GET_VARS['email']))
                {
                        $result=0;
//                        $result=sql_query(8,return_valid_value($_GET['login']));
                        $result=sql_query(9,return_valid_value($_GET['name']));
//                        $result=sql_query(7,return_valid_value($_GET['pass']));
                        $result=sql_query(10,return_valid_value($_GET['email']));
                        if (!$result) echo "<font color='red'><b>"._("Информация не сохранена")."</b></font>";
                        else echo "<b>"._("Информация сохранена")."</b>";
                } //empty

              } // if all not empty
              else
              {
//                        $res=sql_query(6,"Login");
//                        $result=@sqlget($res);
//                        $login=$result['value'];
                        $res=sql_query(6,"Name");
                        $result=@sqlget($res);
                        $name=$result['value'];
//                        $res=sql_query(6,"Pass");
//                        $result=@sqlget($res);
//                        $pass=$result['value'];
                        $res=sql_query(6,"EMail");
                        $result=@sqlget($res);
                        $email=$result['value'];
              }
         } // if isset $ok-yes
         else
         {
//                        $res=sql_query(6,"Login");
//                        $result=@sqlget($res);
//                        $login=$result['value'];
                        $res=sql_query(6,"Name");
                        $result=@sqlget($res);
                        $name=$result['value'];
//                        $res=sql_query(6,"Pass");
//                        $result=@sqlget($res);
//                        $pass=$result['value'];
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
?>

<center>
<?php
$search = '';
if (get_people_count()<ITEMS_TO_ALTERNATE_SELECT) $search='*';



?>
<script type="text/javascript">
<!--
<?=$sajax_javascript?>
//-->
</script>
<table width=80% class=main cellspacing=0>
<tr><th><?=_("Добавить администратора")?></th></tr>
<tr><td align=left>
<form action="admin.php4" method=get target="_self">
   <input type="button" value="<?=_("Все")?>" style="width: 10%" onClick="if (elm = document.getElementById('search_people')) elm.value='*'; get_user_select('*');" >
   <input id="search_people" type="text" value="<?=$search?>" style="width: 89%" onKeyUp="if (typeof(filter_timeout)!='undefined') clearTimeout(filter_timeout); filter_timeout = setTimeout('get_user_select(\''+this.value+'\');',1000);">
   <br>
   <div id="people">      
   <select id="add" name='add' style="width:100%">
<?
  if ($search == '*')
  echo peopleSelect("",0, "admins", true, true);
?>
   </select>
   </div>
   </td>
</tr>
<tr><td><?=okbutton()?></td></tr>
</table>
</form><br>
<br>
<table width=80% class=main cellspacing=0>
<tr><th><?=_("Имя")?></th><th><?=_("Действие")?></th></tr>
<?
   echo adminsList();
?>
</table>
</center>

<?php


//require_once("adm_b.php4");
$GLOBALS['controller']->captureStop(CONTENT);
echo show_tb();

?>