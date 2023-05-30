<?php

//defines

$include=TRUE ;
error_reporting(2039);

// include

require ("setup.inc.php");
//require ("adm_t.php4");
echo show_tb();
echo ph(_("Просмотр файла reg.log фиксирующего регистрацию на сервер"));
echo "<table>";
include ("../reg.log");
echo "</table>";

//require_once("adm_b.php4");
echo show_tb();
?>