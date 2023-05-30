<?php

require_once("1.php");
require_once($GLOBALS['wwf']."/lib/scorm/scorm.lib.php");

$oid = isset($_GET['oid']) ? (int) $_GET['oid'] : 0;
if (isset($_GET['bid']) && $_GET['bid']) {
    $module = (int) $_GET['bid'];
} else {
    $module = getField('organizations','module','oid',$oid);    
}
if (isset($_GET['cid'])) {
    $cid = (int) $_GET['cid'];
} else {
    $cid = getField('organizations','cid','oid',$oid);        
}

if (($oid >= 0) && ($module > 0)) {

$ModID = $oid; $McID = $module;
if ($usertrack=scorm_get_track($s['mid'],$oid,$module,$cid)) {
    $userdata = $usertrack;
} else {
    
    $userdata->status = '';
    $userdata->score_raw = '';
    
}

$userdata->student_id = $s['mid'];
$userdata->student_name = $s['user']['lname'].', '.$s['user']['fname'];
$userdata->mode = 'normal';

if ($userdata->mode == 'normal') {
    $userdata->credit = 'credit';
} else {
    $userdata->credit = 'no-credit';
}


$sql="SELECT content,scorm_params FROM library WHERE bid='$module'";
$sql_result=sql($sql);
$res=sqlget($sql_result);

$scorm_params = unserialize($res['scorm_params']);
if (is_array($scorm_params) && count($scorm_params)) {
    if (isset($scorm_params['datafromlms'])) $userdata->datafromlms = $scorm_params['datafromlms'];
    if (isset($scorm_params['masteryscore'])) $userdata->datafromlms = $scorm_params['masteryscore'];
    if (isset($scorm_params['maxtimeallowed'])) $userdata->datafromlms = $scorm_params['maxtimeallowed'];
    if (isset($scorm_params['timelimitaction'])) $userdata->datafromlms = $scorm_params['timelimitaction'];
}

$modType = $res['content'];
$time = time();

$scorm->auto = 1;

switch($modType) {
    
    case 'SCORM_1.2':
        include_once("{$_SERVER['DOCUMENT_ROOT']}/lib/scorm/datamodels/scorm1_2.js.php");
    break;
    case 'SCORM_1.3':
        include_once("{$_SERVER['DOCUMENT_ROOT']}/lib/scorm/datamodels/scorm1_3.js.php");
    break;
//    case 'AICC':
//        include_once ('datamodels/aicc.js.php');
//    break;
    default:
        include_once("{$_SERVER['DOCUMENT_ROOT']}/lib/scorm/datamodels/scorm1_2.js.php");
    break;    
    
}
   
}

?>