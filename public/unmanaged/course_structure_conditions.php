<?php
require_once("1.php");
require_once($GLOBALS['wwf'].'/lib/classes/CourseContent.class.php');
require_once($GLOBALS['wwf'].'/lib/json/json.class.php');
if (!$_SESSION['s']['login']) die('{}');

$currentItem = CCourseContentCurrentItem::getCurrentItem($_SESSION['s']['mid'],$_GET['cid']);
$sequence = new CCourseContentSequence($_GET['cid'],$_SESSION['s']['mid']);
$items = $sequence->getSequence();

$var = array();
$var['ids'] = array();
$order = array();
//$failed = CCourseContentSequence::getFailedItems($_GET['cid']);
$passed = CCourseContentSequence::getPassedItems($_GET['cid']);
foreach($items as $item) {
	$class = '';
	if ($currentItem == $item->attributes['oid']) {
        if ($item->attributes['vol1']) {
            $class .= ' ct';
        } else {
	        $class .= ' ci'; // courseStructureCurrentItem
        }
	}
	if ($item->attributes['allowed']) {
	    if ($item->attributes['vol1']) {
	        $class .= ' at';
	    } else {
		    $class .= ' ai'; // courseStructureAllowedItem
	    }
	} else {
		$class .= ' d'; // courseStructureForbiddenItem
	}		
	
	if (isset($passed[$item->attributes['oid']])) {
        if ($item->attributes['vol1']) {
            $class .= ' pt';	    
        } else {
	        $class .= ' pi'; // courseStructureFailedItem
        }
    }
      
	$var['ids']['org'.$item->attributes['oid']] = trim($class);
	array_push($order, 'org'.$item->attributes['oid']);
}
$var['order'] = $order;
$json = new Services_JSON();
exit(''.$json->encode($var).'');

?>