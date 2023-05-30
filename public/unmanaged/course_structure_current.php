<?php
require_once("1.php");
require_once($GLOBALS['wwf'].'/lib/classes/CourseContent.class.php');

if (!$_SESSION['s']['login']) die();

$cid = (int) $_GET['cid'];
/*if ($tpo_type = (int) getField('Courses','tpo_type','CID',$cid)) {
    exit("<?xml version=\"1.0\" encoding=\"UTF-8\"?><items><item type=\"current\" id=\"0\" module=\"0\" position=\"1\" total=\"1\" /></items>");
}
*/
$currentItem = CCourseContentCurrentItem::getCurrentItem($_SESSION['s']['mid'],$cid);
$sequence = new CCourseContentSequence($cid, $_SESSION['s']['mid']);
$sequence = $sequence->getSequence();
if (is_array($sequence) && count($sequence)) {
    $prev = $current = $next = $last = false;
    $i = 1;
    foreach($sequence as $item) {
        if (empty($currentItem) && ($i==1)) {
            $currentItem = $item->attributes['oid'];
        }
        $item->attributes['position'] = $i;
        if ($current) {
            $next = $item;
            break;
        }
        if ($item->attributes['oid'] == $currentItem) {
            $current = $item;
            $prev = $last;                        
        }
        $last = $item;
        $i++;
    }
}
$total = count($sequence);
$xml = '<items>';
if ($prev) {
    $xml .= "<item id=\"{$prev->attributes['oid']}\" module=\"{$prev->attributes['module']}\" position=\"{$prev->attributes['position']}\" total=\"$total\" type=\"prev\" />";
}
if ($current) {
    $xml .= "<item id=\"{$current->attributes['oid']}\" module=\"{$current->attributes['module']}\" position=\"{$current->attributes['position']}\" total=\"$total\" type=\"current\" />";
}
if ($next) {
    $xml .= "<item id=\"{$next->attributes['oid']}\" module=\"{$next->attributes['module']}\" position=\"{$next->attributes['position']}\" total=\"$total\" type=\"next\" />";
}
$xml .= '</items>';
$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>".$xml;
exit(iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,'UTF-8',$xml));

?>