<?php
require_once('../1.php');

$xml = '<?xml version="1.0" encoding="UTF-8"?>';
$xml .= "<root none=\"Нет текущих объявлений\" color1=\"".(APPLICATION_COLOR_1 ? substr(APPLICATION_COLOR_1,1) : 'F78F15')."\" color2=\"".(APPLICATION_COLOR_2 ? substr(APPLICATION_COLOR_2,1) : '3490C5')."\">";
if (is_array($s['skurs']) && count($s['skurs'])) {
   /* $sql = "SELECT posts3.PostID as postid,
                   UNIX_TIMESTAMP(posts3.posted) as posted,
                   posts3.name as name,
                   posts3.CID as cid,
                   posts3.email as email,
                   posts3.text as text,
                   posts3.mid as mid,
                   posts3.startday as startday,
                   posts3.stopday as stopday
            FROM `posts3`
            LEFT JOIN `posts3_mids`
              ON  (posts3_mids.postid=posts3.PostID)
            LEFT JOIN `Courses`
              ON  (posts3.CID=Courses.CID)
            WHERE 
				Courses.is_poll = 0 AND (posts3.CID IN ('1','26')) AND posts3_mids.mid='0'
				OR Courses.is_poll = 0 AND (posts3.CID IN ('".join("','",$s['skurs'])."')) AND posts3_mids.mid='".(int) $_SESSION['s']['mid']."'
				OR Courses.is_poll = 0 AND (posts3.CID IN ('".join("','",$s['skurs'])."')) AND posts3.mid = posts3_mids.mid AND posts3.mid = '".(int) $_SESSION['s']['mid']."'
				OR posts3.CID=0
            ORDER BY posted DESC LIMIT 5 ";*/
    $sql = "SELECT posts3.PostID as postid,
                   UNIX_TIMESTAMP(posts3.posted) as posted,
                   posts3.name as name,
                   posts3.CID as cid,
                   posts3.email as email,
                   posts3.text as text,
                   posts3.mid as mid,
                   posts3.startday as startday,
                   posts3.stopday as stopday
            FROM `posts3`
            LEFT JOIN `posts3_mids`
              ON  (posts3_mids.postid=posts3.PostID)
            LEFT JOIN `Courses`
              ON  (posts3.CID=Courses.CID)
            WHERE
				(Courses.is_poll = 0 AND (posts3.CID IN ('".join("','",$s['skurs'])."')) )
				OR posts3.CID =0
			GROUP BY posts3.postid  	
            ORDER BY posted DESC LIMIT 5 ";
    $result = sql($sql);    
    $i=0;
    while($row = sqlget($result)) {
        $i++;
        $posted = (!$posted) ? $row['posted'] : $posted;
        $row['date1'] = date("G:i", $row['posted']);
        $row['date2'] = date("d.m.y", $row['posted']);
        if ($row['cid']) {
            $row['course'] = cid2title($row['cid']);
            $row['course'] = getIcon('note', $row['course']).' '.$row['course'];
        }
		$text = str_replace(array("\n","\r","&nbsp;"), array('','',' '), strip_tags($row['text']));
        //$text = str_replace(array("\n","&nbsp;"), array('',' '), strip_tags($row['text']));
		if ($i==1 && strlen($text)>70){
			//$text = substr($text,0,70)."<br /><a href=\"guestbook.php4#".$row['postid']."post\">Читать полностью</a>";
            preg_match('|.{40,80} |',$text,$str);            
            $text = $str[0]."<br /><a href=\"guestbook.php4#".$row['postid']."post\">Читать полностью</a>";
		}	
        $dummy = "<item time=\"{$row['date1']}\" author=\"".strip_tags($row['name'])."\" txt=\"".htmlspecialchars($text)."\" author_url=\"javascript: wopen('{$sitepath}userinfo.php?mid={$row['mid']}', 'userinfo{$row['mid']}', 450, 180)\" fulltxt_url=\"\"/>".$dummy;
        //$dummy = "<item time=\"{$row['date1']}\" author=\"".strip_tags($row['name'])."\" txt=\"".htmlspecialchars(strip_tags($text))."\" author_url=\"javascript: wopen('{$sitepath}userinfo.php?mid={$row['mid']}', 'userinfo{$row['mid']}', 450, 180)\" fulltxt_url=\"\"/>".$dummy;
    
    }        
}

$xml .= $dummy."</root>";

header("Content-type: text/xml");
echo iconv($GLOBALS['controller']->lang_controller->lang_current->encoding, 'utf-8', $xml);
//echo file_get_contents('student_shedule.xml');
?>