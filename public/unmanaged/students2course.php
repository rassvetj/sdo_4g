<?php
require_once('1.php');
require_once('move2.lib.php');
if (!$s[login]) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");
if ($s['perm']<2) login_error();

$smarty = new Smarty_els();
$GLOBALS['controller']->setHeader(_("Назначение слушателей на курс"));

if (isset($_POST['CID']) && ($_POST['CID']>0)) $MID=$_POST['CID'];

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

// Список курсов
$sql = "SELECT DISTINCT Courses.CID, Courses.Title, Courses.TypeDes, Courses.chain
        FROM Courses
        WHERE Status>0
        ORDER BY Title";
$res = sql($sql);
$courseFilter = new CCourseFilter($GLOBALS['COURSE_FILTERS']);
$courses = array();
while($row = sqlget($res)) {
    if (!$courseFilter->is_filtered($row['CID'])) continue;
    $courses[$row['CID']] = $row['Title'];
    if ($row['TypeDes']<0) $row['TypeDes'] = $row['chain'];
    $courses_types[$row['CID']] = $row['TypeDes'];
}

$sajax_javascript = CSajaxWrapper::init(array('search_people_unused','search_people_used')).$js;

$GLOBALS['controller']->addFilter(_("Курс"),'CID',$courses,$CID,true);
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

if ($CID>0) {
    switch($action) {
        case 'assign':
            $mids = array();
            if ($CID>0) {
                $sql = "SELECT DISTINCT MID FROM claimants WHERE CID='".(int) $CID."'";
                $res = sql($sql);

                while($row = sqlget($res)) {
                    $mids[$row['MID']] = $row['MID'];
                }

                $sql = "SELECT DISTINCT MID FROM Students WHERE CID='".(int) $CID."'";
                $res = sql($sql);

                while($row = sqlget($res)) {
                    $mids[$row['MID']] = $row['MID'];
                }

                // Назначение на курсы
                if (is_array($_POST['need_users']) && count($_POST['need_users'])) {
                    foreach($_POST['need_users'] as $v) {
                        if (!isset($mids[$v])) {
                            assign_person2course($v,$CID);
                        }
                    }
                }
                // Удаление с курсов
                if (is_array($_POST['del_users']) && count($_POST['del_users'])) {
                    foreach($_POST['del_users'] as $v) {
                        if (isset($mids[$v])) {
                            delete_person_from_course($v,$CID);
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

        $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);

        $search = '';
        if (sqlrows($res)<ITEMS_TO_ALTERNATE_SELECT) $search = '*';

        while($row = sqlget($res)) {
            if ($peopleFilter->is_filtered($row['MID'])) {
                $all_users[$row['MID']] = $row['LastName'].' '.$row['FirstName'].' '.$row['Patronymic'].' ('.$row['Login'].')';
                $mids[] = $row['MID'];
            }
        }

        if (is_array($mids) && count($mids)) $sql_mids = "AND MID IN ('".join("','",$mids)."')";

        $do_not_delete = array();
        $sql = "SELECT DISTINCT MID FROM claimants WHERE CID = '".(int) $CID."' AND Teacher='0' $sql_mids";
        $res = sql($sql);
        while($row = sqlget($res)) {
            if (isset($all_users[$row['MID']]))
            $users[$row['MID']] = $all_users[$row['MID']];
            if ($courses_types[$CID]>0) {
                $do_not_delete[] = $row['MID'];
            }
        }

        $smarty->assign('count_pretendents',count($users));

        $sql = "SELECT DISTINCT MID FROM Students WHERE CID = '".(int) $CID."' $sql_mids";
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
                $used .= "<option value=\"$mid\" ";
                if (in_array($mid,$do_not_delete)) $used .= "dontmove=\"dontmove\" style=\"background: #EEEEEE;\"";
                $used .= "> ".htmlspecialchars($name,ENT_QUOTES)."</option>";
            }
        }


        //$smarty->assign('do_not_delete',$do_not_delete);
        $smarty->assign('users',$used);
        $smarty->assign('count_users',count($users));
        $smarty->assign('search',$search);
        $smarty->assign('all_people',$unused);
    }
}

$smarty->assign('sajax_javascript',$sajax_javascript);
$smarty->assign('CID',$CID);
$smarty->assign('okbutton',okbutton());
$smarty->assign('sitepath',$sitepath);
$html = $smarty->fetch('students2course.tpl');
//отображаем тело страницы только при выбранном фильтре
if ($_GET['CID']) {
    echo $html;
    $GLOBALS['controller']->captureFromReturn(CONTENT,$html);
}
$GLOBALS['controller']->terminate();

function search_people_unused($search) {
    global $CID, $courses_types;
    $html = '';
    if (!empty($search) && ($CID>0)) {

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

        $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);

        while($row = sqlget($res)) {
            if ($peopleFilter->is_filtered($row['MID'])) {
                $all_users[$row['MID']] = $row['LastName'].' '.$row['FirstName'].' '.$row['Patronymic'].' ('.$row['Login'].')';
                $mids[] = $row['MID'];
            }
        }

        if (is_array($mids) && count($mids)) $sql_mids = "AND MID IN ('".join("','",$mids)."')";

        $do_not_delete = array();
        $sql = "SELECT DISTINCT MID FROM claimants WHERE CID = '".(int) $CID."' AND Teacher='0' $sql_mids";
        $res = sql($sql);
        while($row = sqlget($res)) {
            if (isset($all_users[$row['MID']]))
            $users[$row['MID']] = $all_users[$row['MID']];
            if ($courses_types[$CID]>0) {
                $do_not_delete[] = $row['MID'];
            }
        }

        $sql = "SELECT DISTINCT MID FROM Students WHERE CID = '".(int) $CID."' $sql_mids";
        $res = sql($sql);
        while($row = sqlget($res)) {
            if (isset($all_users[$row['MID']]))
            $users[$row['MID']] = $all_users[$row['MID']];
        }

        if (is_array($all_users) && count($all_users) && is_array($users) && count($users)) {
            $all_users = array_diff($all_users,$users);
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
    global $CID, $courses_types;
    if ($CID<=0) return;
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

    $do_not_delete = array();
    $sql = "SELECT DISTINCT MID FROM claimants WHERE CID = '".(int) $CID."' AND Teacher='0' $sql_mids";
    $res = sql($sql);
    while($row = sqlget($res)) {
        if (isset($all_users[$row['MID']]))
        $users[$row['MID']] = $all_users[$row['MID']];
        if ($courses_types[$CID]>0) {
            $do_not_delete[] = $row['MID'];
        }
    }

    $GLOBALS['count_pretendents'] = count($users);

    $sql = "SELECT DISTINCT MID FROM Students WHERE CID = '".(int) $CID."' $sql_mids";
    $res = sql($sql);
    while($row = sqlget($res)) {
        if (isset($all_users[$row['MID']]))
        $users[$row['MID']] = $all_users[$row['MID']];
    }

    $GLOBALS['count_users'] = count($users);

    asort($users);
    if (is_array($users) && count($users)) {
        foreach($users as $mid=>$name) {
            $html .= "<option value=\"$mid\" ";
            if (in_array($mid,$do_not_delete)) $html .= "dontmove=\"dontmove\" style=\"background: #EEEEEE;\"";
            $html .= " title='".htmlspecialchars($name,ENT_QUOTES)."'> ".htmlspecialchars($name,ENT_QUOTES)."</option>";
        }
    }
    return $html;
}

?>