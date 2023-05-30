<?php
require_once('1.php');
require_once('move2.lib.php');
if (!$s[login]) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");
if ($s['perm']<2) login_error();

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
$GLOBALS['controller']->setHeader(_("Назначение преподавателей на курс"));

if (isset($_POST['CID']) && ($_POST['CID']>0)) $CID=$_POST['CID'];

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
$GLOBALS['controller']->addFilter(_("Курс"),'CID',$courses,$CID,true);

/* попробуем отказаться от этого фильтра - вызывает много вопросов

$statuses = array(1=>_(CObject::toUpperFirst("слушатель")), _('Преподаватель'), _('Учебная администрация'),_('Администраторы'));

if (!isset($_GET['status'])) $_GET['status'] = 2;

$GLOBALS['controller']->AddFilter(_("Статус"),'status',$statuses,$_GET['status'],false);
*/
if ($CID>0) {
    switch($action) {
        case 'assign':
            if ($CID>0) {
                // Назначение на курсы
                if (is_array($_POST['need_users']) && count($_POST['need_users'])) {
                    foreach($_POST['need_users'] as $v) {
                        if ($v>0) {
                            $sql = "SELECT * FROM Teachers WHERE MID='".(int) $v."' AND CID='".(int) $CID."'";
                            $res = sql($sql);
                            if (!sqlrows($res)) {
                                sql("INSERT INTO Teachers (MID,CID) VALUES ('".(int) $v."','".(int) $CID."')");
                                mailToteach("forced", $v, $CID);
                                sql("DELETE FROM claimants WHERE MID='".(int) $v."' AND CID='".(int) $CID."' AND Teacher='1'");
                            }
                        }
                    }
                }
                // Удаление с курсов
                if (is_array($_POST['del_users']) && count($_POST['del_users'])) {
                    foreach($_POST['del_users'] as $v) {
                        if ($v>0) {
                            $sql = "SELECT * FROM Teachers WHERE MID='".(int) $v."' AND CID='".(int) $CID."'";
                            $res = sql($sql);
                            if (sqlrows($res)) {
                                sql("DELETE FROM Teachers WHERE MID='".(int) $v."' AND CID='".(int) $CID."'");
                                mailToteach("remteach",$v,$CID);

                                $res = sql("SELECT * FROM Teachers WHERE MID='".(int) $v."'");
                                if (!sqlrows($res)) {
                                    $sql = "SELECT permission_groups.pmid
                                            FROM permission_groups
                                            INNER JOIN permission2mid ON (permission2mid.pmid=permission_groups.pmid)
                                            WHERE permission2mid.mid='".(int) $v."' AND permission_groups.type='teacher'";
                                    $res = sql($sql);
                                    while($row = sqlget($res)) {
                                        sql("DELETE FROM permission2mid WHERE mid='".(int) $v."' AND pmid='".(int) $row['pmid']."'");
                                    }
                                }
                            }
                        }
                    }
                }
            }

        default:

        switch($_GET['status']) {
          case "1": $sql_join = "INNER JOIN Students ON Students.MID = People.MID"; break;
          case "2": $sql_join = "INNER JOIN Teachers ON Teachers.MID = People.MID"; break;
          case "3": $sql_join = "INNER JOIN deans ON deans.MID = People.MID"; break;
          case "4": $sql_join = "INNER JOIN admins ON admins.MID = People.MID"; break;
        }
        $all_courses = array(); $person_courses = array();
        $sql = "SELECT People.MID, People.LastName, People.FirstName, People.Patronymic, People.Login
                FROM People
                $sql_join
                $sql_where
                ORDER BY People.LastName, People.FirstName";
        $res = sql($sql);

        $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);

        while($row = sqlget($res)) {
            if ($peopleFilter->is_filtered($row['MID'])) {
                $all_users[$row['MID']] = $row['LastName'].' '.$row['FirstName'].' '.$row['Patronymic'].' ('.$row['Login'].')';
                $mids[] = $row['MID'];
            }
        }

        if (is_array($mids) && count($mids)) $sql_mids = "AND MID IN ('".join("','",$mids)."')";

        $sql = "SELECT DISTINCT MID FROM Teachers WHERE CID = '".(int) $CID."' $sql_mids";
        $res = sql($sql);
        while($row = sqlget($res)) {
            if (isset($all_users[$row['MID']]))
            $users[$row['MID']] = $all_users[$row['MID']];
        }

        if (is_array($all_users) && count($all_users) && is_array($users) && count($users)) {
            $all_users = array_diff($all_users,$users);
        }

        asort($users);

        $search = '';
        //if (get_people_count()<ITEMS_TO_ALTERNATE_SELECT) $search = '*';

        $unused = '';
        if (is_array($all_users) && count($all_users) && ($search=='*')) {
            foreach($all_users as $mid=>$name) {
               $unused .= "<option value=\"$mid\" title='".htmlspecialchars($name,ENT_QUOTES)."'> ".htmlspecialchars($name,ENT_QUOTES)."</option>";
            }
        }

        $used = '';
        if (is_array($users) && count($users)) {
            foreach($users as $mid=>$name) {
                $used .= "<option value=\"$mid\"";
                $used .= "title='".htmlspecialchars($name,ENT_QUOTES)."'> ".htmlspecialchars($name,ENT_QUOTES)."</option>";
            }
        }

        $smarty->assign('users',$used);
        $smarty->assign('count_users',count($users));
        $smarty->assign('search',$search);
        $smarty->assign('all_users',$unused);
    }
}
$smarty->assign('sajax_javascript',$sajax_javascript);
$smarty->assign('CID',$CID);
$smarty->assign('okbutton',okbutton());
$smarty->assign('sitepath',$sitepath);
$html = $smarty->fetch('teachers2course.tpl');
//отображаем тело страницы только при выбранном фильтре
if ($_GET['CID']) {
    echo $html;
    $GLOBALS['controller']->captureFromReturn(CONTENT,$html);
}
$GLOBALS['controller']->terminate();

function search_people_unused($search) {
    global $CID;
    $html = '';
    if (!empty($search) && ($CID>0)) {
        $search = iconv('UTF-8',$GLOBALS['controller']->lang_controller->lang_current->encoding,unicode_urldecode($search));
        $search = trim($search);
        $search = str_replace('*','%',$search);
        switch($_GET['status']) {
          case "1": $sql_join = "INNER JOIN Students ON Students.MID = People.MID"; break;
          case "2": $sql_join = "INNER JOIN Teachers ON Teachers.MID = People.MID"; break;
          case "3": $sql_join = "INNER JOIN deans ON deans.MID = People.MID"; break;
          case "4": $sql_join = "INNER JOIN admins ON admins.MID = People.MID"; break;
        }

        $all_courses = array(); $person_courses = array();
        $sql = "SELECT People.MID, People.LastName, People.FirstName, People.Patronymic, People.Login
                FROM People
                $sql_join
                WHERE
                LOWER(People.LastName) LIKE LOWER('%".substr($GLOBALS['adodb']->Quote($search),1,-1)."%')
                OR LOWER(People.FirstName) LIKE LOWER('%".substr($GLOBALS['adodb']->Quote($search),1,-1)."%')
                OR LOWER(People.Login) LIKE LOWER('%".substr($GLOBALS['adodb']->Quote($search),1,-1)."%')
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

        $sql = "SELECT DISTINCT MID FROM Teachers WHERE CID = '".(int) $CID."' $sql_mids";
        $res = sql($sql);
        while($row = sqlget($res)) {
            if (isset($all_users[$row['MID']]))
            $users[$row['MID']] = $all_users[$row['MID']];
        }

        if (is_array($all_users) && count($all_users) && is_array($users) && count($users)) {
            $all_users = array_diff($all_users,$users);
        }

        if (is_array($all_users) && count($all_users)) {
            foreach($all_users as $mid => $name) {
                $html .= "<option value=\"$mid\" title='".htmlspecialchars($name)."'>".htmlspecialchars($name)."</option>";
            }
        }
    }
    return $html;
}

function search_people_used($search) {
    global $CID;
    if ($CID<=0) return;
    $html = '';
    switch($_GET['status']) {
        case "1": $sql_join = "INNER JOIN Students ON Students.MID = People.MID"; break;
        case "2": $sql_join = "INNER JOIN Teachers ON Teachers.MID = People.MID"; break;
        case "3": $sql_join = "INNER JOIN deans ON deans.MID = People.MID"; break;
        case "4": $sql_join = "INNER JOIN admins ON admins.MID = People.MID"; break;
    }

    $sql = "SELECT People.MID, People.LastName, People.FirstName, People.Patronymic, People.Login
            FROM People
            $sql_join
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

    $sql = "SELECT DISTINCT MID FROM Teachers WHERE CID = '".(int) $CID."' $sql_mids";
    $res = sql($sql);
    while($row = sqlget($res)) {
        if (isset($all_users[$row['MID']]))
        $users[$row['MID']] = $all_users[$row['MID']];
    }

    if (is_array($users) && count($users)) {
        foreach($users as $mid => $name) {
            $html .= "<option value=\"$mid\" title='".htmlspecialchars($name)."'>".htmlspecialchars($name)."</option>";
       }
    }

    return $html;

}

?>