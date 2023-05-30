<?php
require_once('1.php');
require_once('metadata.lib.php');

$controller->setView('DocumentPopup');
//$controller->enableNavigation();
$cid = (int)$_GET['cid'];
$result = @sql("SELECT *
                FROM Teachers t
                LEFT JOIN People p
                    ON t.MID = p.MID
                WHERE t.CID = '{$cid}'
                ");
$prepods = array();
while($res = sqlget($result)){
    $prepods[] = "<a href=\"javascript:void(0);\" onClick=\"wopen('userinfo.php?mid={$res['MID']}','user_{$res['MID']}', '400', '300')\">{$res['LastName']}&nbsp;{$res['FirstName']}</a>";
}

$result=@sql("SELECT * FROM Courses WHERE Status>0 AND TypeDes>-1 AND CID='{$cid}'");
$k=0;
if ($res=sqlget($result)) {
	$controller->setHeader($res['Title']);

	$des = read_metadata(stripslashes($res['Description']), COURSES_DESCRIPTION);
    $des = view_metadata_as_text($des, COURSES_DESCRIPTION);
	if (strlen(strip_tags($des))) {
		$controller->captureFromReturn(CONTENT, _('Период: ').$res['cBegin'].'&nbsp;&nbsp;'.$res['cEnd']);
		$controller->captureFromReturn(CONTENT, _('Преподаватели: ').join(',&nbsp;',$prepods));
	    $controller->captureFromReturn(CONTENT, $des);
		
	} else {
		$controller->setMessage(_('описание курса не задано'), JS_CLOSE_SELF_REFRESH_OPENER);
	}
} else {
	$controller->setMessage(_('не найдено'));
}
$controller->terminate();
?>
