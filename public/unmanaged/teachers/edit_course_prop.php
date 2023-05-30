<?php

   require_once("dir_set.inc.php4");
  // echo show_tb();
   require_once("manage_course.lib.php4");
//   require_once("..\courses.lib.php");
   require_once("organization.lib.php");
//   require_once("show_modules.lib.php");


   require_once("../metadata.lib.php");
   
	$controller->setView('DocumentPopup');   
	
   ?>
<HTML>
<head>
<META content="text/html; charset=windows-1251" http-equiv="Content-Type">
<TITLE>eLearning Server 3000</TITLE>
<SCRIPT src="<?=$sitepath?>js/hide.js" language="JScript" type="text/javascript"></script>
<title>eLearn Server 3000</title>
<link rel="stylesheet" href="<?=$sitepath?>styles/style.css" type="text/css">
</head>
<body>
 <?
   if(isset($_GET['CID']))
     $CID=$_GET['CID'];
   if( !isset($CID) )
    echo _("НЕ ОПРЕДЕЛЕН ИДЕНТИФИКАТОР КУРСА !!!");

   $PID=$_GET['PID'];
   if ( isset( $_POST['save_prop'] )  ){
       save_course_properies( $_POST, $CID );
       $GLOBALS['controller']->setMessage(_("Описание сохранено"),JS_CLOSE_SELF_REFRESH_OPENER);
       $GLOBALS['controller']->terminate();
       exit();
       exit("<script>window.close();</script>");
   }

   echo show_title($CID);
   $GLOBALS['controller']->setHeader(_("Редактировать описание курса:")." ".cid2title($CID));
   $GLOBALS['controller']->captureFromOb(CONTENT);

//   echo "<form action='".$sitepath."teachers/edit_course_prop.php?mid=$MID&CID=$CID&PID=$MID' target=_self id=self method=POST>";
   echo "<form action='".$sitepath."teachers/edit_course_prop.php?CID=$CID' target=_self id=self method=POST>";
   echo show_description( $CID, 2 );
   echo "</form>";
   
   $GLOBALS['controller']->captureStop(CONTENT);


/*   switch( $_GET['make'] ){
     case "edit_item":
       // сохранить метаданные
           $names=get_posted_names( $_GET );
           $meta=set_metadata( $_GET, get_posted_names( $_GET ), "item" );
            save_item_metadata( $_GET['item_id'], $meta );

     break;
     case "save_links":
     break;

   }*/

   unset( $_GET );

//   echo show_mod_organization( $PID, $CID, 1 );

	$controller->terminate();   
?>