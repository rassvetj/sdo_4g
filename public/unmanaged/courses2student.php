<?php
require_once('1.php');
require_once('move2.lib.php');
if (!$s[login]) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");
if ($s['perm']<2) login_error();

$smarty = new Smarty_els();
$GLOBALS['controller']->setHeader(_("Назначение курсов слушателю"));

if (isset($_POST['MID']) && ($_POST['MID']>0)) $MID=$_POST['MID'];

if (get_people_count()<ITEMS_TO_ALTERNATE_SELECT) {
	// Список пользователей
	$sql = "SELECT DISTINCT People.MID, People.LastName, People.FirstName, People.Patronymic, People.Login
            FROM People
            ORDER BY People.LastName";
	$res = sql($sql);
	$peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);
	while($row = sqlget($res)) {
		if ($peopleFilter->is_filtered($row['MID']))
		$people[$row['MID']] = $row['LastName'].' '.$row['FirstName'].' '.$row['Patronymic'].' ('.$row['Login'].')';
	}

	$GLOBALS['controller']->addFilter(_(CObject::toUpperFirst("слушатель")),'MID',$people,$MID,true);

} else {

	require_once($GLOBALS['wwf'].'/lib/sajax/SajaxWrapper.php');
	$js =
	"
        function show_user_select(html) {
            var elm = document.getElementById('users');
            if (elm) elm.innerHTML = '<select name=MID id=MID>'+html+'</select>';
        }

        function get_user_select(str) {
            var current = 0;

            var select = document.getElementById('MID');
            if (select) current = select.value;

            var elm = document.getElementById('users');
            if (elm) elm.innerHTML = '<select><option>"._("Загружаю данные...")."</option></select>';

            x_search_user_options(str, current, show_user_select);
        }

        ";

	$sajax_javascript = CSajaxWrapper::init(array('search_user_options')).$js;
	$GLOBALS['controller']->addFilter(_('Фильтр пользователей'),'search',false,$search,false,0,true,"onKeyUp=\"if (typeof(filter_timeout)!='undefined') clearTimeout(filter_timeout); filter_timeout = setTimeout('get_user_select(\''+this.value+'\');',1000);\"","<input type=\"button\" value=\""._("Все")."\" onClick=\"if (elm = document.getElementById('search')) elm.value='*'; get_user_select('*');\"> ");
	$GLOBALS['controller']->addFilter(_('Пользователь'), 'users', 'div', '<select name=MID id=MID>'.search_user_options(iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,'UTF-8',$search),$MID).'</select>', true);
	$GLOBALS['controller']->addFilterJavaScript($sajax_javascript);
}

if ($MID>0) {
	switch($action) {
		case 'assign':
		if ($MID>0) {
			// Назначение на курсы
			if (is_array($_POST['need_courses']) && count($_POST['need_courses'])) {
				foreach($_POST['need_courses'] as $v) {
					if ($v>0) {
						assign_person2course($MID,$v);
					}
				}
			}
			// Удаление с курсов
			if (is_array($_POST['del_courses']) && count($_POST['del_courses'])) {
				foreach($_POST['del_courses'] as $v) {
					if ($v>0) {
						delete_person_from_course($MID,$v);
					}
				}
			}
		}

		default:
		$all_courses = array(); $person_courses = array();
		$sql = "SELECT * FROM Courses WHERE Status > 0 ORDER BY Title";
		$res = sql($sql);
		$courseFilter = new CCourseFilter($GLOBALS['COURSE_FILTERS']);
		while($row = sqlget($res)) {
			if (!$courseFilter->is_filtered($row['CID'])) continue;
			$all_courses[$row['CID']] = $row['Title'];
			if ($row['TypeDes']<0) $row['TypeDes'] = $row['chain'];
			$all_chains[$row['CID']] = $row['TypeDes'];
		}

		$sql = "SELECT DISTINCT CID FROM claimants WHERE MID = '".(int) $MID."' AND Teacher='0'";
		$res = sql($sql);
		while($row = sqlget($res)) {
			$chains[$row['CID']] = $all_chains[$row['CID']];

			if (isset($all_courses[$row['CID']]))
			$person_courses[$row['CID']] = $all_courses[$row['CID']];
		}

		$sql = "SELECT DISTINCT CID FROM Students WHERE MID = '".(int) $MID."'";
		$res = sql($sql);
		while($row = sqlget($res)) {
			if (isset($all_courses[$row['CID']]))
			$person_courses[$row['CID']] = $all_courses[$row['CID']];
		}

		if (is_array($all_courses) && count($all_courses) && is_array($person_courses) && count($person_courses)) {
			$all_courses = array_diff($all_courses,$person_courses);
		}
		asort($person_courses);
		$smarty->assign_by_ref('chains',$chains);
		$smarty->assign_by_ref('person_courses',$person_courses);
		$smarty->assign_by_ref('all_courses',$all_courses);
	}
}
$smarty->assign('MID',$MID);
$smarty->assign('okbutton',okbutton());
$smarty->assign('sitepath',$sitepath);
$html = $smarty->fetch('courses2student.tpl');
//отображаем тело страницы только при выбранном фильтре
if ($_GET['MID']) {
    echo $html;
    $GLOBALS['controller']->captureFromReturn(CONTENT,$html);
}
$GLOBALS['controller']->terminate();


?>