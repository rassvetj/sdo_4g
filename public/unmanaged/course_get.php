<?php
require_once('1.php');
switch ($s['perm']) {
	case 1:
		$courses = get_courses_student($s['mid']);
		break;
	case 2:
		$courses = get_courses_teacher($s['mid']);
		break;
	default:
		$courses = get_courses_dean($s['mid']);
		break;
}
if (!in_array($_GET['cid'], $courses) && $s['perm']<2) {
	$controller->setView('DocumentBlank');
	$controller->setMessage('Невозможно открыть страницу курса. Страница не существует, либо Вы не авторизованы для её просмотра.', JS_GO_URL, "{$sitepath}",false, false, false, '_top');
	$controller->terminate();
	exit();
}
echo file_get_contents($wwf . '/COURSES/' . $_GET['query']);
?>