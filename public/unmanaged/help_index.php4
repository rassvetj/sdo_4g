<?
//require_once("dir_set.inc.php4");
   require_once('1.php');
   require_once('news.lib.php4');

//if (empty($HTTP_COOKIE_VARS['userMID'])) $top1=true;

//require_once($path."top.php4");

echo show_tb();
echo show_info_block( 0, "[ALL-CONTENT]", "-~help~-"  );// выводит информацию блоками

//echo show_tb();
//require_once($path."bottom.php4");

?>