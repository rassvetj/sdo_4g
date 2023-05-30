<?php

require_once("1.php");

ob_start();

print_r($_POST);
print_r($_GET);

$content = ob_get_contents();

$fp = fopen('scorm_track.log','w+');
fwrite($fp,$content);
fclose($fp);

ob_clean();

/**
* Обработка данных, возвращаемых sco
*/

foreach ($_POST as $element => $value) {
    
    if (substr($element,0,3) == 'cmi') {
        $element = str_replace('__','.',$element);
        $element = preg_replace('/_(\d+)/',".\$1",$element);
        
        switch($element) {
            case 'cmi.core.lesson_status':
            case 'cmi.completion_status':
			case 'cmi.success_status':
                $cmi_completion_status = $value;
            break;
            case 'cmi.core.score.raw':
            case 'cmi.score.raw':
                $cmi_score_raw = $value;
            break;
            case 'cmi.core.score.min':
            case 'cmi.score.min':
                $cmi_score_min = $value;
            break;
            case 'cmi.core.score.max':
            case 'cmi.score.max':
                $cmi_score_max = $value;
            break;
            /*case 'cmi.core.score.scaled':
            case 'cmi.score.scaled':
                $cmi_score_raw = $value;
                $cmi_score_max = 1;
                $cmi_score_min = -1;
            break;*/
        }
        
        $trackData[$element] = $value;
    }
    
}

$ModID = isset($_GET['ModID']) ? (int) $_GET['ModID'] : 0;
$McID  = isset($_GET['McID']) ? (int) $_GET['McID'] : 0;
$cid   = isset($_GET['cid']) ? (int) $_GET['cid'] : 0;
$time  = isset($_GET['time']) ? $_GET['time'] : time();

if ($stud && !$admin && !$dean && !$teach) {

    if ($ModID) {
        $cid = (int) getField('organizations', 'cid', 'oid', (int) $ModID);
    }
    
/*    $sql = "SELECT cid FROM organizations WHERE oid='".$ModID."'";
    $res = sql($sql);
    
    if (sqlrows($res)==1) {
        $row = sqlget($res);
        
        $cid = (int) $row['cid'];
*/        
    if ($McID) {
        $sql = "INSERT INTO scorm_tracklog 
            (mid,cid,ModID,McID,trackdata,start,stop,score,scoremax,scoremin,status)
            VALUES
            ('".(int) $s['mid']."',
            '".(int) $cid."',
            '".$ModID."',
            '".$McID."',
            '".serialize($trackData)."',
            '".date('Y-m-d H:i:s',$time)."',
            NOW(),
            '".(double) $cmi_score_raw."',
            '".(double) $cmi_score_max."',
            '".(double) $cmi_score_min."',
            ".$GLOBALS['adodb']->Quote($cmi_completion_status).")";

        sql($sql);
        
        $res = true;
    }
        
//    }
    
//    sqlfree($res);

}

header("Content-type: application/x-www-form-urlencoded");
if ($res) echo "true\n0";
else echo "false\n101";

?>