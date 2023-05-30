<?php
require_once('1.php');

if (!$_SESSION['s']['login']) exitmsg(_("Пожалуйста, авторизуйтесь"),$GLOBALS['sitepath']."?$sess");

$edit_visibility = 'hidden';
if ($GLOBALS['controller']->checkPermission(STRUCTURE_OF_ORGAN_PERM_EDIT)) {
    $edit_visibility = 'visible';
}

if (isset($_REQUEST['soid']) && ($_REQUEST['soid']>=0)) {    
    if (($_REQUEST['soid']==0) 
        && (is_structured($_SESSION['s']['mid']) || is_kurator($_SESSION['s']['mid'])) && !isset($_GET['mode'])) {
        // Структура организации
        $soids = array();        
        $soid_owner = array();
        $owner_soids = array();        
        getSoidOwnerArrays($soid_owner, $owner_soids);
        
        $sql = "SELECT owner_soid as soid FROM structure_of_organ WHERE mid='".(int) $_SESSION['s']['mid']."' AND type='1'";
        $res = sql($sql);
        while($row = sqlget($res)) {
        	$soids[$row['soid']] = $row['soid'];
        }
        
        // Учебная структура
        $sql = "SELECT DISTINCT departments_soids.soid as soid
                FROM departments_soids 
                INNER JOIN departments ON (departments.did=departments_soids.did)
                WHERE departments.mid='".(int) $_SESSION['s']['mid']."' AND departments.application = '".DEPARTMENT_APPLICATION."'";
        $res = sql($sql);
        while($row = sqlget($res)) {
            if (is_array($soids) && count($soids)) {
                // Проверка вниз
                $checkSoid = $row['soid'];
                while($checkSoid>0) {                       
                    if (isset($soids[$soid_owner[$checkSoid]])) continue 2;                        
                    $checkSoid = $soid_owner[$checkSoid];
                }
                
                // Проверка вверх
                checkSoidUp($row['soid']);                                
                 
            }
            
        	$soids[$row['soid']] = $row['soid'];
        }

        if (is_array($soids) && count($soids)) {
            $sql = "SELECT * FROM structure_of_organ WHERE soid IN ('".join("','",$soids)."')";
            $res = sql($sql);
            while($row = sqlget($res)) {
            	$xml.= "<item id=\"{$row['soid']}\" type=\"{$row['type']}\" position=\""
            	.htmlspecialchars($row['LastName'].' '.$row['FirstName'],ENT_QUOTES)."\""
            	." info=\"".htmlspecialchars($row['info'],ENT_QUOTES)."\""
            	." department=\""
            	.htmlspecialchars($row['code'],ENT_QUOTES)."\" value=\""
            	.htmlspecialchars($row['name'],ENT_QUOTES)."\" icon=\"images/icons/positions_type_"
            	.(int) $row['type'].".gif\" editVisibility=\"$edit_visibility\" deleteVisibility=\"$edit_visibility\" "
            	.($row['type']==2 ? "innerdata=\"{$GLOBALS['sitepath']}structure.php?soid={$row['soid']}\"" : "")." />";            	
            }
        }
        
    } else {
        $sql = "SELECT structure_of_organ.*, People.LastName, People.FirstName
                FROM structure_of_organ
                LEFT JOIN People ON (People.MID=structure_of_organ.mid)
                WHERE structure_of_organ.owner_soid='".(int) $_REQUEST['soid']."'
                ORDER BY structure_of_organ.type DESC, structure_of_organ.name";
        $res = sql($sql);
        while($row = sqlget($res)) {
        	$xml.= "<item id=\"{$row['soid']}\" type=\"{$row['type']}\" position=\""
        	.htmlspecialchars($row['LastName'].' '.$row['FirstName'],ENT_QUOTES)."\""
            ." info=\"".htmlspecialchars($row['info'],ENT_QUOTES)."\""
        	." department=\""
        	.htmlspecialchars($row['code'],ENT_QUOTES)."\" value=\""
        	.htmlspecialchars($row['name'],ENT_QUOTES)."\" icon=\"images/icons/positions_type_"
        	.(int) $row['type'].".gif\" "
        	." mid=\"{$row['mid']}\" ".(in_array($row['type'],array(0,1)) ? "assignVisibility=\"$edit_visibility\"" : '')." editVisibility=\"$edit_visibility\" deleteVisibility=\"$edit_visibility\" "
        	.((($row['mid']<=0) && (in_array($row['type'],array(0,1)))) ? " checker=\"false\" " : '')
            .(in_array($row['type'],array(0,1)) ? "assign=\"{$row['soid']}\"" : '')
        	.($row['type']==2 ? "innerdata=\"{$GLOBALS['sitepath']}structure.php?soid={$row['soid']}\" bold=\"true\"" : "")." />";
        }    
    }
    $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><tree checkers=\"true\" owner=\"".(int) getField('structure_of_organ','owner_soid','soid',(int) $_REQUEST['soid'])."\">".$xml."</tree>";
    header('Content-Type: text/xml; charset=UTF-8');
    echo iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,'utf-8',$xml);
    exit();
}

$GLOBALS['controller']->captureFromOb(CONTENT);

$smarty = new Smarty_els();
$smarty->assign('sitepath',$GLOBALS['sitepath']);

echo $smarty->fetch('structure.tpl');

$GLOBALS['controller']->captureStop(CONTENT);
$GLOBALS['controller']->terminate();

function getSoidOwnerArrays(&$soid_owner, &$owner_soids) {    
    $sql = "SELECT soid, owner_soid
            FROM structure_of_organ";
    $res = sql($sql);
    while($row = sqlget($res)) {
    	$soid_owner[$row['soid']] = $row['owner_soid'];
    	$owner_soids[$row['owner_soid']][$row['soid']] = $row['soid'];
    }    
}

function checkSoidUp($soid) {
    global $soids, $owner_soids;
    
    if (is_array($owner_soids[$soid]) && count($owner_soids[$soid])) {
        foreach($owner_soids[$soid] as $v) {
            if (isset($soids[$v])) unset($soids[$v]);
            checkSoidUp($v);
        }
    }    
}
?>