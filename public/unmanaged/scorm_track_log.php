<?php
require_once("1.php");
require_once("lib/scorm/scorm.lib.php");

if (!$s[login]) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");
if ($s[perm]<1) exitmsg(_("Нехватает полномочий"),"/?$sess");
$GLOBALS['controller']->setView('DocumentPopup');
$GLOBALS['controller']->setHeader(_("Информация о выполнении задания"));
$GLOBALS['controller']->captureFromOb(CONTENT);
$trackID = (isset($_GET['trackid'])) ? $_GET['trackid'] : 0;
if ($trackID<=0) echo "<script>window.close();</script>";

$sql = "SELECT scorm_tracklog.*, CONCAT(CONCAT(People.LastName,' '),People.FirstName) as FIO, library.title as modTitle
        FROM scorm_tracklog 
        LEFT JOIN People ON (People.MID=scorm_tracklog.mid)
        LEFT JOIN library ON (library.bid=scorm_tracklog.McID)
        WHERE trackID='".(int) $trackID."'";
$res = sql($sql);
if (sqlrows($res)) {
    
    $row = sqlget($res);
    $trackdata = unserialize($row['trackdata']);
    if (is_array($trackdata) && count($trackdata)) {
        $i=0;
        while($trackdata['cmi.interactions.'.$i.'.type']) {
            $interaction_data[$i]['id'] = $trackdata['cmi.interactions.'.$i.'.id'];
            $interaction_data[$i]['type'] = $trackdata['cmi.interactions.'.$i.'.type'];
            $interaction_data[$i]['weighting'] = $trackdata['cmi.interactions.'.$i.'.weighting'];
            $interaction_data[$i]['lerner_response'] = $trackdata['cmi.interactions.'.$i.'.student_response'];
            if (isset($trackdata['cmi.interactions.'.$i.'.lerner_response']))
            $interaction_data[$i]['lerner_response'] = $trackdata['cmi.interactions.'.$i.'.lerner_response'];
            $interaction_data[$i]['result'] = $trackdata['cmi.interactions.'.$i.'.result'];
            $interaction_data[$i]['latency'] = $trackdata['cmi.interactions.'.$i.'.latency'];
            $i++;
        }
    }
        
    //echo "<b>{$row['FIO']}</b>";
    echo "
        <table class=main cellspacing=0>
        <tr><th colspan=2>{$row['FIO']}</th></tr>
        <tr><td>"._("Материал:")." </td><td>{$row['modTitle']}</td></tr>
        <tr><td>"._("Курс:")." </td><td>".cid2title($row['cid'])."</td></tr>
        <tr><td>"._("Начал:")." </td><td>".date("d.m.Y H:i:s",strtotime($row['start']))."</td></tr>
        <tr><td>"._("Закончил:")." </td><td>".date("d.m.Y H:i:s",strtotime($row['stop']))."</td></tr>
        <tr><td>"._("Длительность:")." </td><td>".duration(strtotime($row['stop'])-strtotime($row['start']))."</td></tr>
        <tr><td>"._("Набрано балов:")." </td><td>".(int) $row['score']." (".(int) $row['scoremin']." - ".(int) $row['scoremax'].")</td></tr>        
        <tr><td>"._("Статус:")." </td><td>".$row['status']."</td></tr>
        </table><br>
    ";
    if (is_array($interaction_data) && count($interaction_data)) {
        echo "
            <table border=0 width=100% cellpadding=0 cellspacing=0 bgcolor=black><tr><td>
            <table width=100% cellspacing=1 cellpadding=4 border=0>
            <tr><td bgcolor=white colspan=7 align=center>"._("Взаимодействия")."</td></tr>
            <tr>
            <td bgcolor=white>#</td><td bgcolor=white>id</td><td bgcolor=white>"._("Тип")."</td>
            <td bgcolor=white>"._("Ответ")."</td><td bgcolor=white>Результат</td><td bgcolor=white>"._("Время")."</td>
            <td bgcolor=white>"._("Вес")."</td></tr>";        
        foreach($interaction_data as $k=>$v) {
            echo "
                <tr><td bgcolor=white>{$k}</td><td bgcolor=white>{$v['id']}</td><td bgcolor=white>{$v['type']}</td>
                <td bgcolor=white>{$v['lerner_response']}</td>
                <td bgcolor=white>{$v['result']}</td><td bgcolor=white>{$v['latency']}</td><td bgcolor=white>{$v['weighting']}</td></tr>";
        }
        echo "</table></td></tr></table>";
    }
    
}
$GLOBALS['controller']->captureStop(CONTENT);
$GLOBALS['controller']->terminate();

?>