<?

   if (!defined("dimatestcron")) exit("?");
   include_once("1.php");
   include_once("test.inc.php");



// Кеш счетчика вопросов
// прошло ли 10 минут
if (cron_update($GLOBALS['wwf']."/temp/cron_countquest.time",10*60)==1) {

   putlog(_("Запуск")." cron_countquest...");
   $res=sql("SELECT * FROM test","errTC22");
   while ($r=sqlget($res)) {
      $res2=test_getkod($r);
      $qty=sqlrows($res2);
      sqlfree($res2);
      $res3=sql("UPDATE test SET cache_qty=$qty WHERE tid=$r[tid]","errTC28");
      sqlfree($res3);
   }
   sqlfree($res);
   putlog(" ... "._("завершение")." cron_countquest!");

}







?>