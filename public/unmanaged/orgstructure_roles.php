<?php
require_once('1.php');
require_once('lib/classes/Position.class.php');
require_once('move2.lib.php');
require_once('lib/classes/CCourseAdaptor.class.php');
require_once('lib/classes/Chain.class.php');

if (!$_SESSION['s']['login']) exitmsg(_("Пожалуйста, авторизуйтесь"),$GLOBALS['sitepath']);

$GLOBALS['controller']->setView('DocumentPopup');
$GLOBALS['controller']->setHeader(_('Виды оценок'));
$GLOBALS['controller']->enableNavigation();

$GLOBALS['controller']->captureFromOb(CONTENT);

$smarty = new Smarty_els();

$items = $courses = false;

if (isset($_POST['action']) && is_array($_POST['items']) && is_array($_POST['courses'])) {
    
	$items  = $_POST['items'];
	$courses = $_POST['courses'];
	
	if (count($items) && count($courses)) {
	    if ($_POST['action'] == 1) {
            sql("DELETE FROM structure_of_organ_roles WHERE soid IN ('".join("','", $items)."') AND role IN ('".join("','", $courses)."')");
            foreach($items as $mid) {
                foreach($courses as $cid) {
                    sql("INSERT INTO structure_of_organ_roles (soid, role) VALUES ('".(int) $mid."','".(int) $cid."')");
                }
            }            
	        
	    }
	    if ($_POST['action'] == 2) {
	        sql("DELETE FROM structure_of_organ_roles WHERE soid IN ('".join("','", $items)."') AND role IN ('".join("','", $courses)."')");
	    }
	}
	
	$msg = _('Виды оценок успешно удалены');
	if ($_POST['action'] == 1) { 
	   $msg = _('Виды оценок успешно назначены');
	}
	
	$GLOBALS['controller']->setView('DocumentBlank');
	$GLOBALS['controller']->setMessage($msg, JS_GO_URL, $sitepath.'orgstructure_info.php');	
	$GLOBALS['controller']->terminate();
	exit();
	
}

//$GLOBALS['controller']->setTab('m070610');

if (isset($_SESSION['s']['orgstructure']['current']) && $_SESSION['s']['orgstructure']['current']) {
    $_id = $_SESSION['s']['orgstructure']['current'];
    //$GLOBALS['controller']->setTab('m070611', array('href' => "orgstructure_main.php?id=$_id"));
}

/*$GLOBALS['controller']->setTab('m070612');
$GLOBALS['controller']->setTab('m070613');
$GLOBALS['controller']->setTab('m070614');
*/
//$GLOBALS['controller']->setCurTab('m070612');

if (isset($_SESSION['s']['orgstructure']['checked']) && is_array($_SESSION['s']['orgstructure']['checked'])) {
	array_walk($_SESSION['s']['orgstructure']['checked'],'intval');
	
	$peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);
	
	$slaves = CUnitPosition::getSlavesIdAll($_SESSION['s']['orgstructure']['checked']);
    $slaves = array_merge($slaves, $_SESSION['s']['orgstructure']['checked']);
    $slaves = array_unique($slaves);	

	$sql = "SELECT t1.mid, t1.soid, t1.name, t1.type, t2.soid as owner_soid, t2.name as owner_name
	        FROM structure_of_organ t1
	        LEFT JOIN structure_of_organ t2 ON (t2.soid = t1.owner_soid)
	        WHERE t1.soid IN ('".join("','",$slaves)."')
	        ORDER BY t2.name
	        ";
	$res = sql($sql);
		
	$positions = $mids = array();
	while($row = sqlget($res)) {
		if (($row['mid'] > 0) && !$peopleFilter->is_filtered($row['mid'])) continue;
		
	    $positions[] = CPosition::getPosition($row);
//	    if ($row['mid'] > 0) {
//	    	$mids[$row['mid']] = $row['mid'];
//	    }
	    
	    if ($row['type'] == 2) {
	    	$positions = array_merge($positions, getSlavesAll(array($row['soid'])));
	    }
	}
	
/*	if (count($mids)) {
		$sql = "SELECT MID, LastName, FirstName, Patronymic FROM People WHERE MID IN ('".join("','",$mids)."')";
		$res = sql($sql);
		
		while($row = sqlget($res)) {
		    $mids[$row['MID']] = $row['LastName'].' '.$row['FirstName'].' '.$row['Patronymic'];
		}
	}
*/
	if (count($positions)) {
		foreach($positions as $item) {
			//if ($item->attributes['mid'] > 0) {
				//$item->attributes['person'] = $mids[$item->attributes['mid']];
			//}
			if (in_array($item->attributes['type'], array(0,1))) {
			    $items[$item->attributes['owner_soid']][$item->attributes['soid']] = $item;
			}
		}			
	}
		
	$sql = "SELECT id as CID, name as Title FROM competence_roles ORDER BY name";
	$res = sql($sql);
	
	while($row = sqlget($res)) {
	    //if (isset($row['type']) && ($row['type'] == 1)) continue;
	    $courses[$row['CID']] = $row['Title'];
	}
	
}

$smarty->assign('items', $items);
$smarty->assign('courses', $courses);
$smarty->assign('okbutton',okbutton());
$smarty->assign('sitepath',$sitepath);
echo $smarty->fetch('orgstructure_roles.tpl');

$GLOBALS['controller']->captureStop(CONTENT);
$GLOBALS['controller']->terminate();

function getSlavesAll($soids) {
    $ret = array();
    if (is_array($soids) && count($soids)) {
        foreach($soids as $soid) {
            $sql = "SELECT t1.mid, t1.soid, t1.name, t1.type, t2.soid as owner_soid, t2.name as owner_name 
                    FROM structure_of_organ t1
                    LEFT JOIN structure_of_organ t2 ON (t2.soid = t1.owner_soid) 
                    WHERE t1.owner_soid = ".(int) $soid.' 
                    ORDER BY t2.name';
            $res = sql($sql);
              
            while($row = sqlget($res)) {
                $ret[] = CPosition::getPosition($row);
                if ($row['type']==2) {
                    $ret = array_merge($ret, getSlavesAll(array($row['soid'])));
                }
            }
        }
    }
    return $ret;
}

?>