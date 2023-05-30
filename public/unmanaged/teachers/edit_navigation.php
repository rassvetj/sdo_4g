<?php

   require_once("dir_set.inc.php4");
  // echo show_tb();
   require_once("manage_course.lib.php4");
   require_once("organization.lib.php");
   require_once("../metadata.lib.php");
   require_once('../lib/classes/CourseContent.class.php');
   require_once('../lib/classes/Task.class.php');
   require_once('../lib/classes/Module.class.php');

   $controller->setView('DocumentPopup');

   $_SESSION['itemID'] = array();
   $_REQUEST['submove'] = true;

   if (!$_SESSION['s']['login']) exitmsg("Пожалуйста, авторизуйтесь","/?$sess");
   if (!in_array($_SESSION['s']['perm'],array(2,3,4))) login_error();
   ?>
<HTML>
<head>
<META content="text/html; charset=<?php echo $GLOBALS['controller']->lang_controller->lang_current->encoding;?>" http-equiv="Content-Type">
<TITLE>eLearning Server 3000</TITLE>
<SCRIPT src="<?=$sitepath?>js/hide.js" language="JScript" type="text/javascript"></script>
<title>eLearn Server 3000</title>
<link rel="stylesheet" href="<?=$sitepath?>styles/style.css" type="text/css">
</head>
<body>
 <?
//   $links=$_GET['course'];

/*
    if(isset($_GET['CID'])) {
     $CID=$_GET['CID'];
     $sql = "SELECT * FROM Teachers WHERE CID='".(int) $CID."' AND MID='".(int) $_SESSION['s']['mid']."'";
     $res = sql($sql);
     if (!sqlrows($res)) {
         $GLOBALS['controller']->setView('DocumentBlank');
         $GLOBALS['controller']->setMessage('Вы не являетесь преподавателем на данном курсе!',JS_GO_URL,'javascript: window.close();');
         $GLOBALS['controller']->terminate();
         exit();
     }
   }
*/
	
   if (in_array($make, array('after','before','append'))) {
       $CID = (int) getField('organizations','cid','oid', (int) $_GET['item_id']);
   }

   if( !isset($CID) )
    echo _("НЕ ОПРЕДЕЛЕН ИДЕНТИФИКАТОР КУРСА !!!");

   /**
    * Проверка на заблокированность курса
    */
   if ($CID && is_course_locked($CID)) {
       $GLOBALS['controller']->setView('DocumentBlank');
       $GLOBALS['controller']->setMessage(_("Курс заблокирован. Данная операция невозможна"),JS_GO_URL,$GLOBALS['sitepath'].'teachers/manage_course.php4?CID='.(int) $CID);
       $GLOBALS['controller']->terminate();
       exit();
   }

   $PID=$_GET['PID'];

   echo show_title($CID);
//   echo show_description($CID);

    $doRefresh = false;

    if (!empty($_GET['make'])){
	    $GLOBALS['adodb']->UpdateClob('Courses', 'tree', '', "CID='{$CID}'");
    }

    if ($_REQUEST['groupstep'] <= 0) $_REQUEST['groupstep'] = COURSE_ITEM_MOVE_STEP;
    intval($_REQUEST['groupstep']);

	switch( $_REQUEST['make'] ){
     case "additem":
            $_SESSION['itemID'] = array();
            $_SESSION['itemID'][add_new_item_structure_safe($after)] = true;
            unset( $_GET['itemTitle'] );
     break;
     case "deleteItem":
            $level = 'unknown';
            if ((getField('organizations', 'level', 'oid', $_GET['itemID']) == 0) && $_REQUEST['submove']) {
                $level = 0;
            }
            //if (countElements($CID, $level) > 1) {
                delete_item($_GET['itemID'], $_REQUEST['submove'], $CID);
                CCourseContent::checkStructure($CID);
                $_SESSION['itemID'] = array();
            //}
     break;
     case "delete_all":
            if (countElements($CID) > 1) {
                $_SESSION['itemID'] = array();
                delete_all_items($CID);
            }
            sql("INSERT INTO organizations (title, cid, prev_ref, level) VALUES ('"._("пустой элемент")."','{$CID}','-1', '0')");
     break;
     case "next_level":
          CCourseItem::move($_GET['itemID'],'right',$_REQUEST['submove'],($_REQUEST['groupmove'] ? $_REQUEST['groupstep'] : 1));
          $_SESSION['itemID'][$_GET['itemID']] = true;
          //next_level( $_GET['itemID'] );
     break;
     case "prev_level":
          CCourseItem::move($_GET['itemID'],'left',$_REQUEST['submove'],($_REQUEST['groupmove'] ? $_REQUEST['groupstep'] : 1));
          $_SESSION['itemID'][$_GET['itemID']] = true;
          //prev_level( $_GET['itemID'] );
     break;
     case "up_item":
     case "down_item":
            /*
            $direction = ($_REQUEST['make'] == 'up_item') ? 'up' : 'down';
            CCourseItem::move($_GET['itemID'],$direction,$_REQUEST['submove'],($_REQUEST['groupmove'] ? $_REQUEST['groupstep'] : 1));
  			*/
            $direction = ($_REQUEST['make'] == 'up_item') ? 'Up' : 'Down';
            $method = "getPositionMove{$direction}";
            list($insert_after, $replace_with, $last_in_moving_branch, $old_prev_ref, $CID, $level) = CCourseContent::$method($_GET['itemID']);
            if ($insert_after && $insert_after != $_GET['itemID']){
                sql("UPDATE organizations SET prev_ref = '$insert_after' WHERE oid = '{$_GET['itemID']}'"); // передвинули
		        sql("UPDATE organizations SET prev_ref = '{$last_in_moving_branch}' WHERE prev_ref = '{$insert_after}' AND oid <> '{$_GET['itemID']}' AND cid = '$CID'"); // восстановили цепочку там куда пришли
		        sql("UPDATE organizations SET prev_ref = '$old_prev_ref' WHERE oid = '{$replace_with}'"); // восстановили цепочку там где стояли
            }
           $_SESSION['itemID'][$_GET['itemID']] = true;
           //down_item( $_GET['itemID'] );

     break;
     case "edit_item":
       // сохранить линки на уч. материалы                                       $
           //save_links( $CID,  $PID, $_GET['modules'], $_GET['item_title'] );
           $_SESSION['itemID'][$_GET['item_id']] = true;
           if (substr($_GET['new_id'],0,6) == 'otask_') {
               $parts = explode('_',$_GET['new_id']);
               if ($parts[1] > 0) {
                   CCourseItem::setTask($_GET['item_id'], (int) $parts[1]);
                   CCourseContent::checkStructure($CID);
               }
           } elseif (substr($_GET['new_id'],0,5) == 'orun_') {
               $parts = explode('_',$_GET['new_id']);
               if ($parts[1] > 0) {
                   CCourseItem::setRun($_GET['item_id'], (int) $parts[1]);
                   CCourseContent::checkStructure($CID);
               }
           } elseif (substr($_GET['new_id'],0,10)== 'omaterial_') {
               $parts = explode('_',$_GET['new_id']);
               if ($parts[1] > 0) {
                   CCourseItem::setMaterial($_GET['item_id'], (int) $parts[1]);
                   CCourseContent::checkStructure($CID);
               }
           } else {

    	       if (isset($_GET['module_'.$_GET['item_id']])) {
    	           update_organization((int) $_GET['item_id'], $_GET['module_'.$_GET['item_id']]);
    	       } else {
    	           update_organization((int) $_GET['item_id'], $_GET['new_id']);
    	       }
           if (strlen(trim($_GET['item_title']))) {
               set_item_title( $_GET['item_id'], $_GET['item_title'] );
    	       }

           }

       // сохранить метаданные
           $names=get_posted_names( $_GET );
           $meta=set_metadata( $_GET, get_posted_names( $_GET ), "item" );
           save_item_metadata( $_GET['item_id'], $meta );

           $doRefresh = true;

     break;
     case "additems":
     	if (is_array($_POST['bids']) && count($_POST['bids'])){
     	    foreach ($_POST['bids'] as $bid) {
         		$res = sql("SELECT oid FROM organizations INNER JOIN library ON organizations.module = library.bid WHERE bid = '{$bid}' LIMIT 1"); // может быть много ссылок на один модуль. берем первую попавшуюся
         		if ($row = sqlget($res)) {
    	            $dummy_id = add_new_item(_("<пустой элемент>"), $CID, 0, '', '', 0, 0);
    	            update_organization($dummy_id, "o{$row['oid']}");
         		}
     	    }
     	}
     break;
     case 'before':
     case 'after':
     case 'append':
           $_SESSION['itemID'][$_GET['item_id']] = true;
           ob_end_clean();
           die(CCourseItem::moveItem($_GET['item_id'],$_GET['dest_id'],$_REQUEST['make']));
     break;
   }
   unset( $_GET );

   refresh($GLOBALS['sitepath'].'course_constructor_workarea.php?CID='.$CID."&refresh=".$doRefresh."#element_{$_SESSION['itemID']}");
   exit();

   $GLOBALS['controller']->captureFromOb(CONTENT);
   echo show_mod_organization( $PID, $CID, 1 );
   $GLOBALS['controller']->captureStop(CONTENT);
   $controller->terminate();

?>