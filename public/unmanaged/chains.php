<?php
require_once("1.php");
require_once("lib/classes/Chain.class.php");
if (USE_AT_INTEGRATION) {
require_once($GLOBALS['wwf'].'/lib/sajax/SajaxWrapper.php');
$js = "
    function showPositions(arr) {
        var elm = document.getElementById('positions');
        if (elm) {
            elm.length = 0;
            for (i=0;i<arr.length;i++ ) {
                try {
                    elm.add(new Option(arr[i].name,arr[i].soid),null);
                }
                catch (ex) {
                    elm.add(new Option(arr[i].name,arr[i].soid));
                }
                elm.options[elm.options.length-1].title = arr[i].name;
            }
        }
    }

    function getPositions(elm) {
        //var elm = document.getElementById('positions');
        soid = elm.options[elm.selectedIndex].value;
        if (elm && soid.substr(0,2)!='p:' && soid !='downloading') {
            elm.length = 0;
            try {
                elm.add(new Option('"._('Загрузка...')."','downloading'),null);
            }
            catch (ex) {
                elm.add(new Option('"._('Загрузка...')."','downloading'));
            }
            x_getPositions(soid, showPositions);
        }
    }";
}
if (!$_SESSION['s'][login]) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");

if (ereg('[0-9a-zA-Z]',$action)){
    $GLOBALS['controller']->setHelpSection($action);
}
$GLOBALS['controller']->captureFromOb(CONTENT);
$smarty = new Smarty_els();
$smarty->assign('sitepath',$sitepath);
$smarty->assign('action',$action);
$smarty->assign('okbutton',okbutton());
if (USE_AT_INTEGRATION) {
    $smarty->assign('js',CSajaxWrapper::init(array('getPositions')).$js);
}

switch($_POST['post']) {
    case 'add':
        $chain = new CChain();
        $chain->init($_POST);
        $chain->create();
        refresh("{$sitepath}chains.php");
    break;
    case 'edit':
        $chain = new CChain();
        $chain->init($_POST);
        $chain->modify();
        refresh("{$sitepath}chains.php");
    break;
}

switch($action) {
    case 'edit':
        $GLOBALS['controller']->setHeader(_("Редактирование цепочки согласования"));
        $smarty->assign('chain',CChain::get_as_array($_GET['id']));
    case 'add':
        $GLOBALS['controller']->setHeader(_("Редактирование цепочки согласования"));
        if (USE_AT_INTEGRATION) {
            $smarty->assign('positions',getPositions(0));
        } else {
            $smarty->assign('positions',CChainItems::get_all_positions());
        }
        $smarty->assign('departments',CChainItems::get_all_departments());
        $smarty->assign('others',CChainItems::get_all_others());
        if ($action=='add')
        $smarty->assign('chain',array('name'=>trim(strip_tags($_POST['chain_name']))));        
        $html = $smarty->fetch('chains_edit.tpl');
    break;
    case 'delete':
        CChain::delete($_GET['id']);
        refresh("{$sitepath}chains.php");
    break;
    default:
        $smarty->assign('chains',CChainsList::get_as_array());
        $html = $smarty->fetch('chains.tpl');
}

echo $html;
$GLOBALS['controller']->captureStop(CONTENT);
$GLOBALS['controller']->terminate();

function getPositions ($soid) {
    //pr($_SERVER);
    //die();
    $positions = array();
    if (!$soid) {
        //корневой элемент
        $soid = sqlval("SELECT soid FROM structure_of_organ WHERE owner_soid='0'");
        $soid = $soid['soid'];
    }elseif (substr($soid,0,2) =='p:') {
        //должность
        $soid = sqlval("SELECT owner_soid FROM structure_of_organ WHERE soid='".substr($soid,2)."'");
        $soid = $soid['owner_soid'];
    }else {
        $owner_soid = sqlval("SELECT owner_soid FROM structure_of_organ WHERE soid='$soid'");
        $owner_soid = $owner_soid['owner_soid'];
    }

    $res = sql("SELECT * FROM structure_of_organ WHERE owner_soid = '$soid'");
    while ($row = sqlget($res)) {
        //soid = 1 - подразделение; soid = _1 - должность
        if ($row['type']==2) {
            array_unshift($positions, array('soid'=>$row['soid'],'name'=>$row['name']));
        }else {
            $positions[] = array('soid'=>'p:'.$row['soid'],'name'=>$row['name']);
        }
    }
    if ($owner_soid) {
        array_unshift($positions,array('soid'=>$owner_soid,'name'=>'..'));
    }
    $positions['length'] = count($positions);
    return $positions;
}
?>