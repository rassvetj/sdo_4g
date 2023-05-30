<?php
require_once("1.php");
require_once('metadata.lib.php');
require_once('courses.lib.php');
//require_once('competence.lib.php');
require_once('move2.lib.php');
require_once('formula_calc.php');
require_once('tracks.lib.php');

$mid = $_GET['mid'];
$trid = $_GET['trid'];

if(isset($_POST['ok']) && $_SESSION['post_flag'][md5($_SERVER['REQUEST_URI'])] != $_POST['post_flag']) {
    switch($_POST['action']) {
        case 1:   
	       if((isset($_POST['mark']))&&(!empty($_POST['mark']))) {
		      foreach($_POST['cids'] as $cid) {
			     $mid = $_POST['mid'];
			     $trid = $_POST['trid'];
			     $q = "SELECT * FROM courses_marks WHERE mid = $mid AND cid = $cid";
			     $res = sql($q, "errfn_54");
    			 if(isset($_POST['mark'][$cid]) && isset($_POST['is_reckoning'][$cid])) {			     
	       		      if(sqlrows($res) > 0) {
			     		$query = "UPDATE courses_marks SET mark = '".$_POST['mark'][$cid]."' WHERE mid = $mid AND cid = $cid";
                      }
			          else {
				        $query = "INSERT INTO courses_marks (cid, mid, mark, alias) VALUES ($cid, $mid, '".$_POST['mark'][$cid]."', '')";
			          }
                      $result = sql($query);			          
			     }
		      }   
	       }  
	    break;
        case 2:  
	       if(isset($_POST['cids'])) {
		      if(is_array($_POST['cids'])) {
		          foreach($_POST['cids'] as $cid) {
			         if(isset($_POST['is_reckoning'][$cid])) { 
				        togr($mid, $cid);
			        }
		          }
		      }
	       }
	    break;
    }
    if (is_array($_SESSION['post_flag'])) {
        $_SESSION['post_flag'][md5($_SERVER['REQUEST_URI'])] = $_POST['post_flag'];
    }else {
        $_SESSION['post_flag'] = array(md5($_SERVER['REQUEST_URI'])=>$_POST['post_flag']);        
    }
    
    
    
}



echo show_tb();
$tmp = "<script language='javascript' type=\"text/javascript\">
<!--
    function checkAll(element) {
        var i=1;
        elm = document.getElementById('pip' + element.name + '_' +i);        
        while (elm){
            elm.checked = element.checked;
            i++;
            elm = document.getElementById('pip' + element.name + '_' +i);
        }
    }
//-->    
</script>";
$tmp .= ph("Учебный план ".getpeoplename($mid));

if((isset($mid))&&(!empty($mid))&&(isset($trid))&&(!empty($trid))) {
	 $query = "SELECT * FROM tracks2mid WHERE mid = $mid AND trid = $trid";
	 $result = sql($query);
	 if(sqlrows($result) == 0) {
	 	$tmp .= _("Данный человек не обучается на данной специальности");
	 }
	 else {
	    $cids = array();
	    $query = "SELECT DISTINCT CID FROM Students WHERE mid = $mid";
	    $result = sql($query);
	    while($row = sqlget($result)) {
	        $cids[] = $row['CID'];
	    }
	 	$query = "SELECT * FROM tracks2course WHERE trid = $trid ORDER BY level";
	 	$result = sql($query);
	 	$courses = array();
	 	while($row = sqlget($result)) {
	 	    if(in_array($row['cid'], $cids)) {
	 		    $levels[$row['level']][] = $row['cid'];
	 		    $courses[$row['cid']]['level'] = $row['level'];
	 		    $sub_query = "SELECT * FROM courses_marks WHERE mid = $mid AND cid = ".$row['cid'];
	 		    $sub_result = sql($sub_query);
	 		    if(sqlrows($sub_result) > 0) {
    	 			$sub_row = sqlget($sub_result);
	 			   $courses[$row['cid']] = array('mark' => $sub_row['mark']);
	 		    }
	 		    else {
    	 			$courses[$row['cid']] = array('mark' => "");
	 		    }
	 	    }	 		
	 	}
	 	//Начинаем выводить форму с курсами
	 	if((is_array($levels))&&(!empty($levels))) {
	 		if($s['perm'] == 3) {
	 			$tmp .= "<form action='' method='POST'>";
	 		}
	 		foreach($levels as $key => $courses_by_level) {
	 			$tmp .= "<p><b>$key-ый семестр</b>";
	 			$tmp .= "<table width=100% class=main cellspacing=0>";
	 			if($s['perm'] == 3) {
	 				$tmp .= "<th width=10><input type='checkbox' name='$key' onChange='checkAll(this)'/></th>";
	 			}	 			
	 			$tmp .= "<th>Название</th>";
	 			if($s['perm'] == 3) {
	 				$tmp .= "<th>Оценка</th>";
	 			}
	 			$_dont_show_descr = true;
	 			if((is_array($courses_by_level))&&(!empty($courses_by_level))) {
	 				$counter = 1;
	 			    foreach($courses_by_level as $cid) {
	 					$BORDER = 0;
	 					$tmp .= "<tr>";
	 					if($s['perm'] == 3) {
	 						$tmp .= "<td width=10 align=center>";
	 						$tmp .= "<input type='hidden' name='cids[]' value='$cid' />";
	 						$tmp .= "<input type='hidden' name='trid' value='$trid' />";
	 						$tmp .= "<input type='hidden' name='mid' value='$mid' />";
	 						$tmp .= "<input type='hidden' name='post_flag' value='".md5(time())."' />";
	 						$tmp .= "<input type='checkbox' name='is_reckoning[$cid]' id='pip{$key}_".$counter++."'/>";
	 						$tmp .= "</td>";
	 					}
	 					$tmp .= "<td width=100%>".get_title_course_by_id($cid)."</td>";	 		 					
	 					if($s['perm'] == 3) {
	 						$tmp .= "<td align=center>";
	 						$tmp .= "<input type='text' id='def' name='mark[$cid]' size='2' maxlength='2' value='".$courses[$cid]['mark']."' />";
	 						$tmp .= "   </td>";				
	 					}
				
	 					$tmp .= "</tr>";
	 					if($s['perm'] == 3) {
	 						$colspan = " colspan = 3";
	 					}
	 					else {
	 						$colspan = "";
	 					}
	 				}
	 			}
	 			$tmp .= "</table>";
	 		}
	 		if($s['perm'] == 3) { 
                $tmp.="
                <table cellspacing=0 border=0 cellpadding=2 align=right>
                <tr><td>
                <input type='hidden' name='ok' value='ok' />
                <select name=\"action\">
                <option value=\"1\">сохранить оценки</option>
                <option value=\"2\">закончить обучение по курсу</option>
                </select>
                </td>
                <td>
                ".okbutton()."
                </td>
                <td>&nbsp;</td>
                <td>
                ".okbutton(_('завершить'),'','exit','if (window.opener && !window.opener.closed) {window.opener.location.reload();window.close();}')."
                </td>
                </tr></table>";
	 			$tmp .= "</form>";
	 		}
	 	} else {
	 	    $GLOBALS['controller']->setView('DocumentBlank');
	 	    $GLOBALS['controller']->setMessage(sprintf(_("%s не обучается на каких-либо курсах специальности"), CObject::toUpperFirst("слушатель")),JS_GO_URL, 'javascript: window.close();');
	 	    $GLOBALS['controller']->terminate();
	 	    exit();
	 	}
	 }	
}
else {
	$tmp .= "Нет идентификатора учащегося или специальности для которого требуется учебный план";
}
$GLOBALS['controller']->setView('DocumentPopup');
$GLOBALS['controller']->setHeader('Учебный план');
$GLOBALS['controller']->captureFromReturn(CONTENT,$tmp);
echo $tmp; 
echo show_tb();
?>