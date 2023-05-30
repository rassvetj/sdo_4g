<?php
require("../1.php");
require("../metadata.lib.php");
require("Teachers.class.php");
require("Teacher.class.php");
if (!$s[login]) {
	exitmsg("Пожалуйста, авторизуйтесь","/?$sess");
}
if ($s[perm]<3) {
	exitmsg("Доступ к странице имеют представители учебной администрации, или администраторы","/?$sess");
}

echo show_tb();
echo ph("Учебная нагрузка преподавателей");

$teachers = Teachers::get_all_as_array(1);
if (isset($_POST['date']['from']['Month'])) {
	$sec_from = mktime(0,0,0,$_POST['date']['from']['Month'],$_POST['date']['from']['Day'],$_POST['date']['from']['Year']);
	$sec_to = mktime(0,0,0,$_POST['date']['to']['Month'],$_POST['date']['to']['Day'],$_POST['date']['to']['Year']);
}
$smarty_tpl = new Smarty_els;
$smarty_tpl->assign("teachers", $teachers);
$smarty_tpl->assign("teacher_selected", $_POST['mid']);
$smarty_tpl->assign("from_timestamp", $sec_from);
$smarty_tpl->assign("to_timestamp", $sec_to);
echo $smarty_tpl->fetch('teachers_load_select.tpl');

if($sec_from > $sec_to){
          echo "<h3 style='color: black'>Неверно задан временной диапазон.</h3>";
}
else {
if(isset($view))
{
require_once("teachers_load_view.php");
unset($view);
}
echo show_tb();
}
?>