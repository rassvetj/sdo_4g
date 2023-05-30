<?php
require_once('1.php');
require_once($wwf.'/lib/classes/Module.class.php');
require_once("lib/FCKeditor/fckeditor.php");
require_once('Pager/examples/Pager_Wrapper.php');
require_once('lib/rep/report.lib.php');

$pagerOptions = array(
    'mode'    => 'Sliding',
    'delta'   => 5,
    'perPage' => 25,
);

if ($_SESSION['s']['perm']!=4) exitmsg(_("У Вас нет полномочий администратора"), $GLOBALS['sitepath']);

//input data
$action     =      $_REQUEST['action'];
$rtid       = (int)$_REQUEST['id'];
$inputData  =      $_POST['data'];

$crntUser   =      $_SESSION['s']['mid'];
$tblName    =      'report_templates'; 

$GLOBALS['controller']->captureFromOb(CONTENT);

$result = array();

switch ($action){
    case 'edit':
        if (!$rtid) break;
        
        $sql = "SELECT *
                FROM `$tblName`
                WHERE rtid = '$rtid'";
        $res = sql($sql);
    
        $result = sqlget($res);
    case 'refresh':
        if ($action == 'refresh'){
            $result = $inputData;
        }
    case 'add':
        $reportsList = array();
        foreach ($reports as $repName=>$repInfo){
            if (isset($repInfo['fields']) && $repInfo['type']==6) $reportsList[$repName] = $repInfo['title'];
        }
        
        if (!isset($result['report_name'])){
            $dummy = reset($reportsList);
            $result['report_name'] = key($reportsList);
        }
        
        $helpStr = '<ul>';
        foreach ($reports[ $result['report_name'] ]['fields'] as $fieldName => $info){
            $helpStr .= "<li>$fieldName - [".strtoupper($info['field'])."]</li>";
        }
        $helpStr .= '</ul>';
                
        $GLOBALS['controller']->setHelpSection($helpStr);
                
        $smarty = new Smarty_els();
        
        ob_start();
		$oFCKeditor             = new FCKeditor('data[template]') ;
 		$oFCKeditor->BasePath	= "{$GLOBALS['sitepath']}lib/FCKeditor/";
		$oFCKeditor->Value		= $result['template'] ;
		$oFCKeditor->Create();
		$result['template']     = ob_get_contents();
		ob_clean();
        
        $smarty->assign('data', $result);
        $smarty->assign('reports', $reportsList);
        $smarty->assign('okbutton', okbutton());
        $smarty->assign('cancelbutton', button(_("Отмена"), '', 'cancel', "document.location.href=\"{$GLOBALS['sitepath']}reportGenerator.php\";return false;"));
       
        echo $smarty->fetch('reportGenerator_edit.tpl');

        $GLOBALS['controller']->setView('DocumentPopup');
        $GLOBALS['controller']->captureStop(CONTENT);
        $GLOBALS['controller']->terminate();        
        exit();
        break;
        
    case 'update':
        if (!$rtid){
            
            $sql = "INSERT INTO `$tblName`
                      (`template_name`,`report_name`,`created`,`creator`,`edited`,`editor`,`template`)
                    VALUES
                      (".$GLOBALS['adodb']->Quote($inputData['template_name']).",
                       ".$GLOBALS['adodb']->Quote($inputData['report_name']).",
                      '".time()."',
                      '$crntUser',
                      '".time()."',
                      '$crntUser',
                      '".$inputData['template']."')";
        }else{
            $sql = "UPDATE $tblName
                    SET                       
                      `template_name` = ".$GLOBALS['adodb']->Quote($inputData['template_name']).",
                      `report_name`   = ".$GLOBALS['adodb']->Quote($inputData['report_name']).",
                      `edited`        = '".time()."',
                      `editor`        = '$crntUser',
                      `template`      = ".$GLOBALS['adodb']->Quote($inputData['template'])."
                    WHERE `rtid` = '$rtid'";
        }
        
        sql($sql);
        $GLOBALS['controller']->setMessage(_('Данные сохранены'), JS_GO_URL, $GLOBALS['sitepath'].'reportGenerator.php');
        break;
        
    case 'delete':
        if (!$rtid) break;        
        sql("DELETE FROM `$tblName` WHERE `rtid` = '$rtid'");
        break;
}  
 
//отображение списка 
$sql = "SELECT * FROM `$tblName`";
$page = Pager_Wrapper_Adodb($adodb, $sql, $pagerOptions);
if ($page) {
    while($row = sqlget($page['result'])) {        
        $items[$row['rtid']] = $row['template_name'];
    }
}

$smarty = new Smarty_els();
//$smarty->assign('perm_edit', $GLOBALS['controller']->checkPermission(SPECIALIZATIONS_PERM_EDIT));        
$smarty->assign('caption', _("создать шаблон"));
$smarty->assign('url', $GLOBALS['sitepath']."reportGenerator.php?action=add");

$smarty->assign('perm_edit', true);        
$smarty->assign('page', $page);
$smarty->assign('items', $items);
$smarty->assign('icon_edit',   getIcon('edit'));
$smarty->assign('icon_delete', getIcon('delete'));
$smarty->assign('okbutton', okbutton());

echo $smarty->fetch('reportGenerator.tpl');

$GLOBALS['controller']->captureStop(CONTENT);
$GLOBALS['controller']->terminate();
?>