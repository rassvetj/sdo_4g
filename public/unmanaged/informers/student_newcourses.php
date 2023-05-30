<?php
require_once('../1.php');
require_once('metadata.lib.php');

$xml = '<?xml version="1.0" encoding="UTF-8"?>';
$xml .= "<root none=\"Нет новых курсов\" color1=\"".(APPLICATION_COLOR_1 ? substr(APPLICATION_COLOR_1,1) : 'F78F15')."\" color2=\"".(APPLICATION_COLOR_2 ? substr(APPLICATION_COLOR_2,1) : '3490C5')."\">";
/*
$sql = "SELECT *
        FROM Courses c
        WHERE c.Status>1 
          AND c.type = '1'
          AND c.TypeDes>-1 
          AND c.cEnd > NOW()              
              ".((is_array($_SESSION['s']['skurs']) && count($_SESSION['s']['skurs'])) ? "AND c.CID NOT IN ('".join("','", $_SESSION['s']['skurs'])."')" : '')."
        ORDER BY Title";
 */
// Проверка на принадлежность курса к специальности/ если в табл спец есть хоть одна запись то добавляем доп условия к запросу
$addquery = (intval(sqlvalue("SELECT count(*) as count FROM tracks2course"))>0)?" INNER JOIN tracks2course a ON c.CID!=a.CID ":'';

$sql = "SELECT c.*
        FROM Courses c".$addquery."
        WHERE c.Status>1          
          AND c.TypeDes=0
          AND c.cEnd > NOW()
              ".((is_array($_SESSION['s']['skurs']) && count($_SESSION['s']['skurs'])) ? "AND c.CID NOT IN ('".join("','", $_SESSION['s']['skurs'])."')" : '')."			
		ORDER BY Title";	 

$res = sql($sql);
while($row = sqlget($res)) {
    $des = read_metadata(stripslashes($row['Description']), COURSES_DESCRIPTION);
    $des = view_metadata_as_text($des, COURSES_DESCRIPTION);
    $des = strip_tags($des);
    if (empty($des)) {
        $des = _('Нет описания');
    }
    $xml .= "<item course=\"".htmlspecialchars($row['Title'])."\" url=\"{$sitepath}course_structure.php?CID={$row['CID']}\" description=\"".strip_tags($des)."\"/>";
}

$xml .= "</root>";

header("Content-type: text/xml");
echo iconv($GLOBALS['controller']->lang_controller->lang_current->encoding, 'utf-8', $xml);
//echo file_get_contents('student_shedule.xml');
?>