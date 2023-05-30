<?php
require_once('1.php');

//пока эта страница ни кому не нужна ж(
refresh($GLOBALS['sitepath']);
exit();

if ($_REQUEST['blank']) {
    $GLOBALS['controller']->setView('DocumentPopup');
}
$GLOBALS['controller']->captureFromOb(CONTENT);

$module = (int) $_REQUEST['module'];

$smarty = new Smarty_els();

$people = array();

$sql = "SELECT MID, LastName, FirstName, Patronymic FROM People";
$res = sql($sql);

while($row = sqlget($res)) {
    $people[$row['MID']] = $row['LastName'].' '.$row['FirstName'].' '.$row['Patronymic'];
}

if ($_POST['review']) {
    sql("INSERT INTO reviews (mid, module, `date`, review) VALUES ('".$_SESSION['s']['mid']."', '$module', NOW(), ".$GLOBALS['adodb']->Quote($_POST['review']).")");
    refresh('reviews.php?module='.$module.'&blank='.$_REQUEST['blank']);
    exit();
}

$sql = "SELECT * FROM reviews WHERE module = '$module' ORDER BY date DESC";
$res = sql($sql);
$reviews = array();
while($row = sqlget($res)) {
    $row['person'] = $people[$row['mid']];
    $reviews[$row['id']] = $row;
}

$smarty->assign('blank', $_REQUEST['blank']);
$smarty->assign('module', $module);
$smarty->assign('reviews', $reviews);
$smarty->assign('okbutton', okbutton());
$smarty->assign('sitepath', $sitepath);
echo $smarty->fetch('reviews.tpl');

$GLOBALS['controller']->captureStop(CONTENT);
$GLOBALS['controller']->terminate();
?>