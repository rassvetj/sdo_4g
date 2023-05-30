<?php
require_once('1.php');
require_once('move2.lib.php');
require_once('tracks.lib.php');

if (!$s[login]) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");
if ($s['perm']<2) login_error();

$smarty = new Smarty_els();
$GLOBALS['controller']->setHeader(_("Назначение слушателей на специальность"));

$trid = $_REQUEST['trid'];

require_once($GLOBALS['wwf'].'/lib/sajax/SajaxWrapper.php');

$js =
    "
    function show_user_select(html) {
        var elm = document.getElementById('people');
        if (elm) elm.innerHTML = '<select size=10 id=\"all_users\" name=\"del_users[]\" multiple style=\"width:100%\">'+html+'</select>';
        prepare_options('all_users', dropped, assigned);
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
        prepare_options('users', assigned, dropped);
    }

    function get_user_select_used(str) {
        var elm = document.getElementById('people_used');
        if (elm) elm.ennerHTML = '<select size=10 id=\"users\" name=\"need_users[]\" multiple style=\"width: 100%\"><option>"._("Загружаю данные...")."</option></select>';
        x_search_people_used(str, show_user_select_used);
    }

    ";

// Список специальностей
$sql = "SELECT trid, name
        FROM tracks
        ORDER BY name";
$res = sql($sql);

$tracks = array();
while($row = sqlget($res)) {
    $tracks[$row['trid']] = $row['name'];
}

$sajax_javascript = CSajaxWrapper::init(array('search_people_unused','search_people_used')).$js;

$GLOBALS['controller']->addFilter(_("Специальность"),'trid',$tracks,$trid,true);

$groups = array();
$groups = array_merge($groups,selGrved('-1',$GLOBALS['grId'],true));
$GLOBALS['controller']->addFilter(_("Группа"),'grId',$groups,$GLOBALS['grId'],false);

$group = $GLOBALS['grId'];
$gid = (int) substr($group,1);
if ($gid>0) {
    switch ($group[0]) {
        case 'd':
            $sql_join = "
                INNER JOIN cgname ON (cgname.cgid=Students.cgid)
                INNER JOIN Students ON (Students.MID=People.MID)
                ";
            $sql_where = "WHERE cgname.cgid='".(int) $gid."'";
        break;
        case 'g':
            $sql_join = "INNER JOIN groupuser ON (groupuser.mid=People.MID)";
            $sql_where = "WHERE groupuser.gid='".(int) $gid."'";
        break;
    }
}

if ($trid>0) {
    switch($action) {
        case 'assign':
            $mids = array();
            if ($trid>0) {
                $sql = "SELECT DISTINCT mid FROM tracks2mid WHERE trid='".(int) $trid."'";
                $res = sql($sql);

                while($row = sqlget($res)) {
                    $mids[$row['mid']] = $row['mid'];
                }

                // Назначение на курсы
                if (is_array($_POST['need_users']) && count($_POST['need_users'])) {
                    foreach($_POST['need_users'] as $v) {
                        if (!isset($mids[$v])) {
                            registration2track($trid,$v);
                        }
                    }
                }
                // Удаление с курсов
                if (is_array($_POST['del_users']) && count($_POST['del_users'])) {
                    foreach($_POST['del_users'] as $v) {
                        if (isset($mids[$v])) {
                            delFromTrack($v,$trid);
                        }
                    }
                }
            }

        default:
        $sql = "SELECT People.MID, People.LastName, People.FirstName, People.Patronymic, People.Login
                FROM People
                $sql_join
                $sql_where
                ORDER BY People.LastName, People.FirstName, People.Login";
        $res = sql($sql);

        //$peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);

        $search = '';
        if (sqlrows($res)<ITEMS_TO_ALTERNATE_SELECT) $search = '*';

        while($row = sqlget($res)) {
            $all_users[$row['MID']] = $row['LastName'].' '.$row['FirstName'].' '.$row['Patronymic'].' ('.$row['Login'].')';
            $mids[] = $row['MID'];
        }

        $sql_mids = '';
        if (is_array($mids) && count($mids)) $sql_mids = "AND MID IN ('".join("','",$mids)."')";

        $notClaimants = array();
        $sql = "SELECT DISTINCT mid FROM tracks2mid WHERE trid = '".(int) $trid."' AND level>'0' $sql_mids";
        $res = sql($sql);
        while($row = sqlget($res)) {
            if (isset($all_users[$row['mid']])) {
                $notClaimants[$row['mid']] = $all_users[$row['mid']];
            }
        }

        $users = array();
        $sql = "SELECT DISTINCT mid FROM tracks2mid WHERE trid = '".(int) $trid."' AND level='0' $sql_mids";
        $res = sql($sql);
        while($row = sqlget($res)) {
            if (isset($all_users[$row['mid']])) {
                $users[$row['mid']] = $all_users[$row['mid']];
            }
        }

        if (is_array($all_users) && count($all_users) && is_array($users)) {
            $all_users = array_diff($all_users,array_merge($users, $notClaimants));
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


        //$smarty->assign('do_not_delete',$do_not_delete);
        $smarty->assign('users',$used);
        //$smarty->assign('count_users',count($notClaimants));
        //$smarty->assign('count_pretendents',count($users));
        $smarty->assign('search',$search);
        $smarty->assign('all_people',$unused);
    }
}

$smarty->assign('sajax_javascript',$sajax_javascript);
$smarty->assign('trid',$trid);
$smarty->assign('okbutton',okbutton());
$smarty->assign('sitepath',$sitepath);
$html = $smarty->fetch('students2track.tpl');
echo $html;
$GLOBALS['controller']->captureFromReturn(CONTENT,$html);
$GLOBALS['controller']->terminate();

function search_people_unused($search) {
    global $trid;
    $html = '';
    if (!empty($search) && ($trid>0)) {

        $group = $GLOBALS['grId'];
        $gid = (int) substr($group,1);
        if ($gid>0) {
            switch ($group[0]) {
                case 'd':
                    $sql_join = "
                        INNER JOIN cgname ON (cgname.cgid=Students.cgid)
                        INNER JOIN Students ON (Students.MID=People.MID)
                        ";
                    $sql_where = "AND cgname.cgid='".(int) $gid."'";
                break;
                case 'g':
                    $sql_join = "INNER JOIN groupuser ON (groupuser.mid=People.MID)";
                    $sql_where = "AND groupuser.gid='".(int) $gid."'";
                break;
            }
        }
        $search = iconv('UTF-8',$GLOBALS['controller']->lang_controller->lang_current->encoding,unicode_urldecode($search));
        $search = trim($search);
        $search = str_replace('*','%',$search);
        $sql = "SELECT People.MID, People.LastName, People.FirstName, People.Patronymic, People.Login
                FROM People
                $sql_join
                WHERE (People.LastName LIKE '%".addslashes($search)."%'
                OR People.FirstName LIKE '%".addslashes($search)."%'
                OR People.Login LIKE '%".addslashes($search)."%')
                $sql_where
                ORDER BY People.LastName, People.FirstName, People.Login";
        $res = sql($sql);

        //$peopleFilter = new CPeopleFilter(array('CPeopleFilter_Academic'));

        while($row = sqlget($res)) {
          //  if ($peopleFilter->is_filtered($row['MID'])) {
                $all_users[$row['MID']] = $row['LastName'].' '.$row['FirstName'].' '.$row['Patronymic'].' ('.$row['Login'].')';
                $mids[] = $row['MID'];
          //  }
        }

        if (is_array($mids) && count($mids)) $sql_mids = "AND MID IN ('".join("','",$mids)."')";

        $notClaimants = array();
        $sql = "SELECT DISTINCT mid FROM tracks2mid WHERE trid = '".(int) $trid."' AND level>'0' $sql_mids";
        $res = sql($sql);
        while($row = sqlget($res)) {
            if (isset($all_users[$row['mid']])) {
                $notClaimants[$row['mid']] = $all_users[$row['mid']];
            }
        }

        $users = array();
        $sql = "SELECT DISTINCT mid FROM tracks2mid WHERE trid = '".(int) $trid."' AND level='0' $sql_mids";
        $res = sql($sql);
        while($row = sqlget($res)) {
            if (isset($all_users[$row['mid']]))
            $users[$row['mid']] = $all_users[$row['mid']];
        }

        if (is_array($all_users) && count($all_users) && is_array($users)) {
            $all_users = array_diff($all_users,array_merge($users, $notClaimants));
        }

        if (is_array($all_users) && count($all_users)) {
            foreach($all_users as $mid=>$name) {
                $html .= "<option value=\"$mid\" title='".htmlspecialchars($name)."'>".htmlspecialchars($name)."</option>";
            }
        }

    }
    return $html;
}

function search_people_used($search) {
    global $trid;
    if ($trid<=0) return;
    $html = '';
    $group = $GLOBALS['grId'];
    $gid = (int) substr($group,1);
    if ($gid>0) {
        switch ($group[0]) {
            case 'd':
                $sql_join = "
                    INNER JOIN cgname ON (cgname.cgid=Students.cgid)
                    INNER JOIN Students ON (Students.MID=People.MID)
                    ";
                $sql_where = "AND cgname.cgid='".(int) $gid."'";
            break;
            case 'g':
                $sql_join = "INNER JOIN groupuser ON (groupuser.mid=People.MID)";
                $sql_where = "AND groupuser.gid='".(int) $gid."'";
            break;
        }
    }
    $sql = "SELECT People.MID, People.LastName, People.FirstName, People.Patronymic, People.Login
            FROM People
            $sql_join
            $sql_where
            ORDER BY People.LastName, People.FirstName, People.Login";
    $res = sql($sql);

    $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);

    while($row = sqlget($res)) {
        if ($peopleFilter->is_filtered($row['MID'])) {
            $all_users[$row['MID']] = $row['LastName'].' '.$row['FirstName'].' '.$row['Patronymic'].' ('.$row['Login'].')';
            $mids[] = $row['MID'];
        }
    }

    if (is_array($mids) && count($mids)) $sql_mids = "AND MID IN ('".join("','",$mids)."')";

    $sql = "SELECT DISTINCT mid FROM tracks2mid WHERE trid = '".(int) $trid."' AND level='0' $sql_mids";
    $res = sql($sql);
    while($row = sqlget($res)) {
        if (isset($all_users[$row['mid']]))
        $users[$row['mid']] = $all_users[$row['mid']];
    }

    $GLOBALS['count_users'] = count($users);

    asort($users);
    if (is_array($users) && count($users)) {
        foreach($users as $mid=>$name) {
            $html .= "<option value=\"$mid\"";
            /*if (in_array($mid,$do_not_delete)) $html .= "dontmove=\"dontmove\" style=\"background: #EEEEEE;\"";*/
            $html .= " title='".htmlspecialchars($name,ENT_QUOTES)."'> ".htmlspecialchars($name,ENT_QUOTES)."</option>";
        }
    }
    return $html;
}

?>