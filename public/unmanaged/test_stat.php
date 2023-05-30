<?

   require_once("1.php");
   require_once("test.inc.php");

   $ss="test_e1";

   if (!$s[login]) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");
   if ($s[perm]<2) exitmsg(_("К этой странице могут обратится только: преподаватель,  представитель учебной администрации, администратор"),"/?$sess");
   if (count($s[tkurs])==0) exitmsg(_("Вы зарегистрированы в статусе преподавателя, но на данный момент вы не преподаете ни на одном из курсов."),"/?$sess");
   if (!isset($cid)) $cid=reset($s[tkurs]);

   intvals("cid tid");

switch ($c) {

case "stat":

   if (!isset($s[tkurs][$cid])) exitmsg(_("Вы не преподаватель данного курса."),"results.php4?$sess");

   echo show_tb();
   echo ph(_("Статистика заданий"),"");
   $GLOBALS['controller']->captureFromOb(CONTENT);
   $GLOBALS['controller']->setHeader(_("Статистика заданий"));

   $ss=showQuestionStatistic( $tid, $cid, "" );

   echo $ss;
   $GLOBALS['controller']->captureStop(CONTENT);
   echo show_tb();

   return;

}


?>