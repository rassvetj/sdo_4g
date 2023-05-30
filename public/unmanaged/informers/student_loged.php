<?php
require_once('../1.php');

$xml = '<?xml version="1.0" encoding="UTF-8"?>';
$xml .= "<root none=\"Нет активных пользователей\" color1=\"".(APPLICATION_COLOR_1 ? substr(APPLICATION_COLOR_1,1) : 'F78F15')."\" color2=\"".(APPLICATION_COLOR_2 ? substr(APPLICATION_COLOR_2,1) : '3490C5')."\">";

$mids = array();
$sql = "SELECT DISTINCT mid, stop 
        FROM sessions 
        WHERE logout = '0' AND stop >= ".$GLOBALS['adodb']->DBTimestamp(time()-60*60*24)." 
        ORDER BY stop DESC";
$res = sql($sql);

while($row = sqlget($res)) {
    $mids[$row['mid']] = $row['mid'];
}

if (count($mids)) {
    $sql = "SELECT MID, LastName, FirstName, Login, Patronymic FROM People WHERE MID IN ('".join("','", $mids)."')";
    $res = sql($sql);
    
    while($row = sqlget($res)) {
        $mids[$row['MID']] = $row;
    }
    
    foreach($mids as $mid => $info) {
        $xml .= "<item awatar=\"{$sitepath}reg.php4?getimg=$mid\" user=\"".(($info['LastName'] || $info['FirstName']) ? $info['LastName'].($row['FirstName'] ? ' '.$info['FirstName'] : '') : $info['Login'])."\" user_url=\"javascript: wopen('{$sitepath}userinfo.php?mid={$mid}', 'userinfo{$mid}', 450, 180)\"/>";
    }
}

$xml .= "</root>";

header("Content-type: text/xml");
echo iconv($GLOBALS['controller']->lang_controller->lang_current->encoding, 'utf-8', $xml);
//echo file_get_contents('student_shedule.xml');
?>