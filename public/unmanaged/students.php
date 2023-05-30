<?php
require_once('1.php');
require_once('move2.lib.php');
if (!$s[login]) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");
if ($s['perm']<2) login_error();

$ctrl = new CStudentsController();
$ctrl->init();
$ctrl->execute();

class CStudentsController {
    var $view;
    var $model;

    function init() {
        $this->view = new CStudentsView();
        $this->view->title = _("Все слушатели и курсы");
    }

    function execute() {
        $this->model = new CStudentsModel();
        $this->model->init();
        switch($_POST['post_action']) {
            case 'assign':
                $this->model->assign();
            break;
        }
        $this->view->display();
    }
}

class CStudentsView {
    var $title;

    function display() {
        $tpl = new Smarty_els();
        if (is_array($GLOBALS['ctrl']->model->results) && count($GLOBALS['ctrl']->model->results))
            $GLOBALS['controller']->setMessage(_("Обработано обучаемых:").' '.(int) count($GLOBALS['ctrl']->model->results),
                JS_GO_URL,$GLOBALS['sitepath'].'students.php');
        $tpl->assign('okbutton',okbutton());
        $tpl->assign_by_ref('this',$GLOBALS['ctrl']);
        $GLOBALS['controller']->setHeader($this->title);

        $groups['all'] = _("Все");
        $groups = array_merge($groups,selGrved('-1',$GLOBALS['grId'],true));
        $GLOBALS['controller']->addFilter(_("Группа"),'grId',$groups,$GLOBALS['grId'],true);
        //отображаем тело страницы только при выбранном фильтре
        if ($_GET['grId']) {
            $GLOBALS['controller']->setContent($tpl->fetch('students.tpl'));
        }
        $GLOBALS['controller']->terminate();
    }
}

class CStudentsModel {
    var $students;
    var $courses;
    var $results = array();

    function init() {
        $this->students = new CStudentsList();
        $this->students->init();
        $this->courses = new CCoursesList();
        $this->courses->init();
    }

    function assign() {
        if (is_array($_POST['students']) && count($_POST['students'])
            && is_array($_POST['courses']) && count($_POST['courses'])) {
                reset($_POST['students']);
                while(list($k,$v) = each($_POST['students'])) {
                    if ($v>0) {
                        reset($_POST['courses']);
                        while(list($kk,$vv) = each($_POST['courses'])) {
                            switch($_POST['action']) {
                                case 'assign':
                                    $this->_assign_person($v,$vv);
                                break;
                                case 'delete':
                                    $this->_delete_person($v,$vv);
                                break;
                            }
                            $this->results[$v] = 'processed';
                        }
                    }
                }
        }
    }

    function _get_course_typedes($cid) {
        $sql = "SELECT TypeDes,chain FROM Courses WHERE CID='".(int) $cid."'";
        $res = sql($sql);
        if (sqlrows($res)) $row = sqlget($res);
        if ($row['TypeDes']!=0) $row['TypeDes'] = $row['chain'];
        return $row['TypeDes'];
    }

    function _is_exists_in_claimants($mid,$cid) {
        $sql = "SELECT * FROM claimants WHERE MID='".(int) $mid."' AND CID='".(int) $cid."' AND Teacher='0'";
        $res = sql($sql);
        return sqlrows($res);

    }

    function _is_exists_in_students($mid, $cid) {
        $sql = "SELECT * FROM Students WHERE MID='".(int) $mid."' AND CID='".(int) $cid."'";
        $res = sql($sql);
        return sqlrows($res);
    }

    function _assign_to_claimant($mid,$cid) {
        sql("INSERT INTO claimants (MID,CID,Teacher) VALUES ('".(int) $mid."', '".(int) $cid."', 0)");
        return sqllast();
    }

    function _assign_person($mid,$cid) {
        if ($this->_get_course_typedes($cid)<=0) {
            return tost($mid,$cid,1,0,1);
        } else {
            if (!$this->_is_exists_in_claimants($mid,$cid)
            && !$this->_is_exists_in_students($mid,$cid)) {
                return $this->_assign_to_claimant($mid,$cid);
            }
        }
    }

    function _delete_from_claimant($mid,$cid) {
        $typeDes = getField('Courses','TypeDes','CID',$cid);
        if ($typeDes<0) $typeDes = getField('Courses','chain','CID',$cid);
        if ($typeDes<=0) {
            sql("DELETE FROM claimants WHERE MID='".(int) $mid."' AND CID='".(int) $cid."'");
        }
        return;
    }

    function _delete_from_student($mid,$cid) {
        sql("DELETE FROM Students WHERE MID='".(int) $mid."' AND CID='".(int) $cid."'");
        return;
    }

    function _delete_person($mid,$cid) {
        if ($this->_is_exists_in_claimants($mid,$cid)) {
            $this->_delete_from_claimant($mid,$cid);
        } else {
            $this->_delete_from_student($mid,$cid);
        }
    }
}

class CStudentsList {
    var $list;

    function init() {
        $this->list = $this->_get_list();
    }

    function _get_list() {
        $group = $GLOBALS['grId'];
        $gid = (int) substr($group,1);
        //pr($gid);
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

        $sql = "SELECT DISTINCT People.MID, People.LastName, People.FirstName, People.Patronymic, People.Login
                FROM People
                $sql_join
                $sql_where
                ORDER BY People.LastName";
        if (!empty($GLOBALS['grId'])) $res = sql($sql);
        $i = 1;
        $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);
        while($row = sqlget($res)) {
            $row['n'] = $i++;
            if ($peopleFilter->is_filtered($row['MID']))
            $rows[] = $row;
        }
        return $rows;
    }
}

class CCoursesList {
    var $list;

    function init() {
        $this->list = $this->_get_list();
    }
    function _get_list() {
        $courseFilter = new CCourseFilter($GLOBALS['COURSE_FILTERS']);
        $sql = "SELECT * FROM Courses WHERE Status>0 AND UNIX_TIMESTAMP(cEnd) > ".time()." ORDER BY Title";
        $res = sql($sql);
        $i=1;
        while($row = sqlget($res)) {
            if (!$courseFilter->is_filtered($row['CID'])) continue;
            $row['n'] = $i++;
            $rows[] = $row;
        }
        return $rows;
    }
}

?>