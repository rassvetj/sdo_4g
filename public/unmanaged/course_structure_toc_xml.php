<?php
require_once("1.php");
require_once($GLOBALS['wwf'].'/lib/classes/CourseContent.class.php');

$cid = (int) $_GET['cid'];

$xml = ''; 
$children = array(); $parent = 0;


if (isset($_REQUEST['soid']) && ($_REQUEST['soid']>=0)) {    
    if ($_REQUEST['soid']==0) {
        $children = CCourseContent::getChildLevel($cid);        
    } else {
        $cid = (int) getField('organizations', 'cid', 'oid', (int) $_REQUEST['soid']);
        $level = (int) getField('organizations', 'level', 'oid', (int) $_REQUEST['soid']);
        
        
        $items = array();
        $sql = "SELECT oid, prev_ref, level FROM organizations WHERE cid = '".(int) $cid."' AND level IN ('$level','".($level-1)."')";
        $res = sql($sql);
        
        while($row = sqlget($res)) {
            $items[$row['oid']] = array('oid' => $row['oid'], 'prev_ref' => $row['prev_ref'], 'level' => $row['level']);
        }

        if (isset($items[$_REQUEST['soid']])) {
            $item = $items[$_REQUEST['soid']];
            while($item['level'] >= $level) {
                if (!isset($items[$item['prev_ref']])) break;
                $item = $items[$item['prev_ref']];
            }
        }
        
        if ($item['oid'] != $_REQUEST['soid']) {
            $parent = $item['oid'];
        }
        
        $children = CCourseContent::getChildLevel($cid, $_REQUEST['soid'],$level+1);

        if (!count($children)) {
            if ($parent > 0) {
                
                $_REQUEST['soid'] = $parent;

                $cid = (int) getField('organizations', 'cid', 'oid', (int) $_REQUEST['soid']);
                $level = (int) getField('organizations', 'level', 'oid', (int) $_REQUEST['soid']);
                
                
                $items = array();
                $sql = "SELECT oid, prev_ref, level FROM organizations WHERE cid = '".(int) $cid."' AND level IN ('$level','".($level-1)."')";
                $res = sql($sql);
                
                while($row = sqlget($res)) {
                    $items[$row['oid']] = array('oid' => $row['oid'], 'prev_ref' => $row['prev_ref'], 'level' => $row['level']);
                }
            
                if (isset($items[$_REQUEST['soid']])) {
                    $item = $items[$_REQUEST['soid']];
                    while($item['level'] >= $level) {
                        if (!isset($items[$item['prev_ref']])) break;
                        $item = $items[$item['prev_ref']];
                    }
                }
                
                if ($item['oid'] != $_REQUEST['soid']) {
                    $parent = $item['oid'];
                }
                
                $children = CCourseContent::getChildLevel($cid, $_REQUEST['soid'],$level+1);
                
            } else {
                $children = CCourseContent::getChildLevel($cid);                                
            }
        }
        
    }
}

if (is_array($children) && count($children)) {
    foreach($children as $item) {
        $xml .= "<item id=\"".$item->attributes['oid']."\" type=\"2\" value=\"".htmlspecialchars($item->attributes['title'], ENT_QUOTES)."\" />";
    }
} 

$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><tree owner=\"$parent\">".$xml."</tree>";
header('Content-Type: text/xml; charset=UTF-8');
echo iconv($GLOBALS['controller']->lang_controller->lang_current->encoding, 'utf-8', $xml);

?>