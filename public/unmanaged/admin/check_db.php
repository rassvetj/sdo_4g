<?php

$include=TRUE;

require ("setup.inc.php");
//require ("adm_t.php4");
echo show_tb();
require ("adm_fun.php4");

//$connect=get_mysql_base();

echo ph(_("Проверка БД"));
//debug_yes("array",$HTTP_COOKIE_VARS);
?>

</center>


<?php

  // createTable( "rooms" );
  // createTable( "tracks" );
  // createTable( "organizations" );
 //  createTable( "tracks2mid" );
//   createTable( "money" );
//   createTable( "periods" );
//   createTable( "departments" );

//   upgradeTable("Students");

// upgradeTable("Courses");
//   upgradeTable("schedule");
//   upgradeTable("groupname");
//  upgradeTable("EventTools");
   $GLOBALS['controller']->captureFromOb(CONTENT);

   showTables( TRUE );

   $GLOBALS['controller']->captureStop(CONTENT);

//require("adm_b.php4");
   echo show_tb();
?>