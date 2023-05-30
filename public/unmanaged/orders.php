<?php
require_once("1.php");
require_once("lib/classes/Chain.class.php");
require_once("move2.lib.php");

if (!$s[login]) exitmsg("Пожалуйста, авторизуйтесь","/?$sess");

$GLOBALS['controller']->setHeader(_('Просмотр заявок'));
$GLOBALS['controller']->captureFromOb(CONTENT);
$GLOBALS['controller']->changePageId('m2103'); //делаем так, ибо другого способа установить нужную для данной страницы помощь после перехода по ссылке с другой страницы - невозможно.
$smarty = new Smarty_els();
$smarty->assign('sitepath',$sitepath);
$smarty->assign('action',$action);
$smarty->assign('okbutton',okbutton());

if ($s['perm']==1) $MID=$s['mid'];
else {
    if ($GLOBALS['controller']->checkPermission(ORDERS_PERM_AGREEM))
    $smarty->assign('agreem','yes');

    if (get_people_count()<ITEMS_TO_ALTERNATE_SELECT) {
        // Список пользователей
        $sql = "SELECT DISTINCT People.MID, People.LastName, People.FirstName, People.Login
                FROM People
                ORDER BY People.LastName";
        $res = sql($sql);
        //$peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);
        $people['-2'] = _('Все');
        while($row = sqlget($res)) {
            //if ($peopleFilter->is_filtered($row['MID']))
            $people[$row['MID']] = $row['LastName'].' '.$row['FirstName'].' ('.$row['Login'].')';
        }
        $GLOBALS['controller']->addFilter(_('Пользователь'),'MID',$people,$MID,true);
    } else {

        require_once($GLOBALS['wwf'].'/lib/sajax/SajaxWrapper.php');
        $js =
            "
            function show_user_select(html) {
                var elm = document.getElementById('users');
                if (elm) elm.innerHTML = '<select name=MID id=MID>'+html+'</select>';
            }

            function get_user_select(str) {
                var current = 0;

                var select = document.getElementById('MID');
                if (select) current = select.value;

                var elm = document.getElementById('users');
                if (elm) elm.innerHTML = '<select><option>"."Загружаю данные..."."</option></select>';

                x_search_people_unused(str, current, show_user_select);
            }

            ";

        $sajax_javascript = CSajaxWrapper::init(array('search_people_unused')).$js;

        $GLOBALS['controller']->addFilter(_('Фильтр пользователей'),'search',false,$search,false,0,true,"onKeyUp=\"if (typeof(filter_timeout)!='undefined') clearTimeout(filter_timeout); filter_timeout = setTimeout('get_user_select(\''+this.value+'\');',1000);\"","<input type=\"button\" value=\""._("Все")."\" onClick=\"if (elm = document.getElementById('search')) elm.value='*'; get_user_select('*');\"> ");
        $GLOBALS['controller']->addFilter(_('Пользователь'), 'users', 'div', '<select name=MID id=MID>'.search_people_unused($search,$MID).'</select>', true);
        $GLOBALS['controller']->addFilterJavaScript($sajax_javascript);

    }
}
$html = '';
switch($action) {
    case 'accept':
        if ($GLOBALS['controller']->checkPermission(ORDERS_PERM_AGREEM))
            CChainFilter::accept_now($_GET['cid'],$_GET['mid']);
        refresh("{$sitepath}orders.php?MID=".$_GET['mid']);
    break;
    case 'deny':
        if ($GLOBALS['controller']->checkPermission(ORDERS_PERM_AGREEM))
            delfromabitur($_GET['mid'],$_GET['cid']);
        refresh("{$sitepath}orders.php?MID=".$_GET['mid']);
    break;
    default:
        $MID = intval($MID);
        $MIDS = array($MID);
        $peopleList = false;
        if ($MID==-2) {
            //$peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);
            $sql = "SELECT claimants.MID, People.LastName, People.FirstName, People.Patronymic, People.Login
                    FROM claimants
                    LEFT JOIN People ON (People.MID=claimants.MID)
                    WHERE Teacher='0'
                    ORDER BY People.LastName, People.FirstName, People.Login";
            $res = sql($sql);

            while($row = sqlget($res)) {
                //if (!$peopleFilter->is_filtered($row['MID'])) continue;
                $MIDS[$row['MID']] = $row['MID'];
                $info[$row['MID']] = $row;
            }
            $peopleList = true;
        }
        $logItems = array();
        foreach($MIDS as $MID) {
        unset($log);
        if ($MID) {

            $chainFilter = new CChainFilter();
            $chainFilter->_get_chains_by_courses();

            $sql = "SELECT CID FROM claimants WHERE MID='".$MID."' AND Teacher='0'";
            $res = sql($sql);
            while($row = sqlget($res)) {
                $CID = intval($row['CID']);

                $log[$CID]['cid'] = $CID;
                $log[$CID]['mid'] = $MID;
                $log[$CID]['items'] = CChainLog::get_as_array($CID,$MID);

                if ($chainFilter->chain_by_course[$CID]) {

                    $chains[$CID] = CChainItems::get_as_array($chainFilter->chain_by_course[$CID]);
                    if (is_array($chains[$CID]) && count($chains[$CID])) {
                        foreach($chains[$CID] as $k=>$v) {
                            $v['object'] = $v['type'];
                            if (isset($log[$CID]['items'][$k])) {
                                $log[$CID]['items'][$k]['status'] = '<font color="green">'._("согласовано").'</font>';
                                $log[$CID]['items'][$k]['item'] = $v['item'];
                            } else {
                                $v['subject'] = CChainItems::get_subject($CID,$MID,$v);
                                $log[$CID]['items'][$k] = $v;
                                $log[$CID]['items'][$k]['status'] = '<font color="red">'._("ожидание").'</font>';
                            }
                        }
                    }

                }
                if (is_array($log[$CID]['items']) && count($log[$CID]['items'])) {
                    ksort($log[$CID]['items']);
                    foreach($log[$CID]['items'] as $k=>$v) {
                        $log[$CID]['items'][$k]['object'] =
                            CChainItems::get_type_name($log[$CID]['items'][$k]['object'],$log[$CID]['items'][$k]['item']);
                        if (is_array($log[$CID]['items'][$k]['subject'])) {
                            $subject = '';
                            foreach($log[$CID]['items'][$k]['subject'] as $vv)
                                $subject .= mid2name($vv).'<br>';
                            $log[$CID]['items'][$k]['subject'] = $subject;
                        } else
                            $log[$CID]['items'][$k]['subject'] = mid2name($log[$CID]['items'][$k]['subject']);
                        //$log[cid2title($CID)]['items'][$k] = $log[$CID]['items'][$k];
                    }
                    $log[$CID]['count'] = count($log[$CID]['items']);
                    $log[cid2title($CID)] = $log[$CID];
                    unset($log[$CID]);
                }

            }
	        if ((is_array($log) && count($log)) || (!$peopleList && is_array($info) && count($info))) {
	            //$smarty->assign('log',$log);
	            //$smarty->assign('skin_url',$GLOBALS['controller']->view_root->skin_url);
	            if (strlen($info[$MID]['LastName']) || strlen($info[$MID]['FirstName'])) {
	               //$smarty->assign('name',trim(htmlspecialchars($info[$MID]['LastName'].' '.$info[$MID]['FirstName'].' '.$info[$MID]['Patronymic'],ENT_QUOTES)));
	               $name = trim(htmlspecialchars($info[$MID]['LastName'].' '.$info[$MID]['FirstName'].' '.$info[$MID]['Patronymic'],ENT_QUOTES));
	            } else {
                   //$smarty->assign('name',trim(htmlspecialchars($info[$MID]['Login'],ENT_QUOTES)));
                   $name = trim(htmlspecialchars($info[$MID]['Login'],ENT_QUOTES));
	            }
	            $logItems[] = array('name'=>$name, 'log'=>$log);
	        }

        }
    }
}

$smarty->assign('skin_url',$GLOBALS['controller']->view_root->skin_url);
$smarty->assign('logItems',$logItems);

$html .= $smarty->fetch('orders.tpl');

//отображаем тело страницы только при выбранном фильтре
if ($_GET['MID'] || $GLOBALS['s']['perm']<2) {
    echo $html;
    $GLOBALS['controller']->captureStop(CONTENT);
}
$GLOBALS['controller']->terminate();

function search_people_unused($search, $mid=0) {
    $html = "<option value=\"-2\"> "._("Все")."</option>";
    if (!empty($search)) {
        //$peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);
        $search = iconv('UTF-8',$GLOBALS['controller']->lang_controller->lang_current->encoding,unicode_urldecode($search));
        $search = trim($search);
        if ($search=='*') $search = '%';
        $sql = "SELECT MID, LastName, FirstName, Patronymic, Login
                FROM People
                WHERE LastName LIKE '%".addslashes($search)."%'
                OR FirstName LIKE '%".addslashes($search)."%'
                OR Login LIKE '%".addslashes($search)."%'
                ORDER BY LastName, FirstName, Login";
        $res = sql($sql);
        while($row = sqlget($res)) {
            //if (!$peopleFilter->is_filtered($row['MID'])) continue;
        	$html .= "<option value='{$row['MID']}'";
        	if ($row['MID']==$mid) $html .= " selected ";
        	$html .= ">".htmlspecialchars($row['LastName'].' '.$row['FirstName'].' '.$row['Patronymic'].' ('.$row['Login'].')',ENT_QUOTES)."</option>\n";
        }
    }

    return $html;
}

?>