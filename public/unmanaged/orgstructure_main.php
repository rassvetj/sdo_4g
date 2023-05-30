<?php
require_once('1.php');
require_once('lib/classes/Orgstructure.class.php');
require_once('lib/classes/CompetenceRole.class.php');
require_once('positions.lib.php');

if (!$_SESSION['s']['login']) exitmsg(_("Пожалуйста, авторизуйтесь"),$GLOBALS['sitepath']);

$id = (int) $_REQUEST['id'];

$GLOBALS['controller']->setView('DocumentPopup');
$GLOBALS['controller']->setHeader(_('Редактировать'));
$GLOBALS['controller']->enableNavigation();
$GLOBALS['controller']->view_root->disableBreadCrumbs();

switch($_REQUEST['action']) {
	case 'delete':
        if ($GLOBALS['controller']->checkPermission(STRUCTURE_OF_ORGAN_PERM_EDIT)) {
            
        	CPosition::deleteItem($id);
        	
        	unset($_SESSION['s']['orgstructure']['current']);
        	
            $GLOBALS['controller']->setView('DocumentBlank');
            $GLOBALS['controller']->captureFromOb(CONTENT);
            echo "<script type=\"text/javascript\" language=\"JavaScript\">
                  <!-- 
                      parent.leftFrame.location.reload(); 
                  //-->
                  </script>";
            $GLOBALS['controller']->captureStop(CONTENT);
            $GLOBALS['controller']->setMessage(_('Элемент успешно удалён'),JS_GO_URL,"{$sitepath}orgstructure_info.php?page_id={$GLOBALS['controller']->page_id}");
            $GLOBALS['controller']->terminate();
            exit();      
        } else {
            $GLOBALS['controller']->setView('DocumentBlank');
            $GLOBALS['controller']->setMessage(_('У Вас не хватает прав'),JS_GO_URL,"{$sitepath}orgstructure_main.php?page_id={$GLOBALS['controller']->page_id}&id=".$owner);
            $GLOBALS['controller']->terminate();
            exit();                     
        }
		break;
	case 'add':
        if ($GLOBALS['controller']->checkPermission(STRUCTURE_OF_ORGAN_PERM_EDIT)) {
            $name  = $_POST['name'];
            $code  = $_POST['code'];
            $owner = (int) $_POST['owner'];
            $type  = (int) $_POST['type'];
            
            if (strlen($name)) {
                $msg = check_logic_of_structure($owner,$type);
                if (!empty($msg)) {
                    $GLOBALS['controller']->setView('DocumentBlank');
                	$GLOBALS['controller']->setMessage($msg,JS_GO_URL,"{$GLOBALS['sitepath']}orgstructure_main.php?page_id={$GLOBALS['controller']->page_id}&id=".$owner);
                    $GLOBALS['controller']->terminate();
                    exit();
                }

                if (($type == 2) && !$GLOBALS['controller']->checkPermission(STRUCTURE_OF_ORGAN_PERM_ADD_ORGUNIT)) {
                	refresh("{$GLOBALS['sitepath']}orgstructure_main.php?page_id={$GLOBALS['controller']->page_id}&id=".$owner);
	                $GLOBALS['controller']->terminate();
                }
                
                $own = $enemy = 1;
                $display_results = $threshold = 0;
                
                if ($owner) {
                    $sql = "SELECT * FROM structure_of_organ WHERE soid = '".(int) $owner."'";
                    $res = sql($sql);
                    
                    while($row = sqlget($res)) {
                        $own             = $row['own_results'];
                        $enemy           = $row['enemy_results'];
                        $display_results = $row['display_results'];
                        $threshold       = $row['threshold'];
                    }
                }
                

                $res = sql("
                    INSERT INTO structure_of_organ (name,type,owner_soid,code,own_results,enemy_results,display_results,threshold) 
                    VALUES (
                    ".$GLOBALS['adodb']->Quote($name).",
                    '$type',
                    '$owner',
                    ".(strlen($code) ? $GLOBALS['adodb']->Quote($code) : 'NULL').",
                    '$own',
                    '$enemy',
                    '$display_results',
                    '$threshold')");
                
                if (sqllast()) {
                	$GLOBALS['controller']->setView('DocumentBlank');
                	$GLOBALS['controller']->captureFromOb(CONTENT);
                	echo "<script type=\"text/javascript\" language=\"JavaScript\">
                	      <!-- 
                	          parent.leftFrame.location.reload(); 
                	      //-->
                	      </script>";
                	$GLOBALS['controller']->captureStop(CONTENT);
		            $GLOBALS['controller']->setMessage(_('Элемент успешно добавлен'),JS_GO_URL,"{$sitepath}orgstructure_main.php?page_id={$GLOBALS['controller']->page_id}&id=".$owner);
		            $GLOBALS['controller']->terminate();
		            exit();                	
                } else {
                    $GLOBALS['controller']->setView('DocumentBlank');
                	$GLOBALS['controller']->setMessage(_('Ошибка добавления элемента'),JS_GO_URL,"{$sitepath}orgstructure_main.php?page_id={$GLOBALS['controller']->page_id}&id=".$owner);
                    $GLOBALS['controller']->terminate();
                    exit();                                 	
                }
            } else {
            	$GLOBALS['controller']->setView('DocumentBlank');
                $GLOBALS['controller']->setMessage(_('Введите название'),JS_GO_URL,"{$sitepath}orgstructure_main.php?page_id={$GLOBALS['controller']->page_id}&id=".$owner);
                $GLOBALS['controller']->terminate();
                exit();                             	
            }
        } else {
        	$GLOBALS['controller']->setView('DocumentBlank');
            $GLOBALS['controller']->setMessage(_('У Вас не хватает прав'),JS_GO_URL,"{$sitepath}orgstructure_main.php?page_id={$GLOBALS['controller']->page_id}&id=".$owner);
            $GLOBALS['controller']->terminate();
            exit();                   	
        }
		break;
	case 'update':

		if ($GLOBALS['controller']->checkPermission(STRUCTURE_OF_ORGAN_PERM_EDIT)) {
			if ($id > 0) {
				
				$name  = $_POST['name'];
	            $owner = (int) $_POST['owner'];
				$code  = $_POST['code'];
				$type  = (int) $_POST['type'];
				$info  = $_POST['info'];
				$mid   = (int) $_POST['mid'];
				$roles = $_POST['roles'];
				
				if ($type == 2) {
    				$own_results     = (int) $_POST['allow_own_results'];
    				$enemy_results   = (int) $_POST['allow_enemy_results'];
    				$display_results = (int) $_POST['result_display_method'];
    				$threshold       = (int) $_POST['threshold'];
    				if ($threshold < 0)   $threshold = 0;
    				if ($threshold > 100) $threshold = 100;
				}
				
	            $child_soids = getChildrenIdArray($id);
	            $_sql = in_array($owner, $child_soids) ? "" : "owner_soid = '".$owner."',";
		        if ($owner == $id) $_sql = '';
		   
	 		    /**
			    * Проверки логики структуры организации
			    */
		   
		        $msg = check_logic_of_structure($owner,$type,'edit',$id);
		   
	  		    $sql = "UPDATE structure_of_organ
			            SET name            = ".$GLOBALS['adodb']->Quote($name).",
			                info            = ".$GLOBALS['adodb']->Quote($info).",		              
  			                type            = '$type',
			                mid             = '$mid',
	                        $_sql
			                code = ".(strlen($code) ? $GLOBALS['adodb']->Quote($code) : 'NULL')."
			            WHERE soid = '$id'";
			    $res = false;
	            if (empty($msg)) $res = sql($sql);
			    
			    if ($res && ($type == 2)) {
			    	CPosition::updatePreferences($id, array(
			    	    'own_results'     => $own_results, 
			    	    'enemy_results'   => $enemy_results,
			    	    'display_results' => $display_results, 
			    	    'threshold'       => $threshold));
			    }
		   
		        sql("DELETE FROM structure_of_organ_roles WHERE soid='$id'");
		        if (is_array($roles) && count($roles)) {
			        foreach($roles as $role) {
			            sql("INSERT INTO structure_of_organ_roles (soid,role) VALUES ('$id','".(int) $role."')");
			        }
		        }
			
		   		
				if (!empty($msg)) {
					$GLOBALS['controller']->setView('DocumentBlank');
		            $GLOBALS['controller']->setMessage($msg,JS_GO_URL,"{$sitepath}orgstructure_main.php?page_id={$GLOBALS['controller']->page_id}&id=".$id);
		            $GLOBALS['controller']->terminate();
		            exit();
				}			
			}
			$GLOBALS['controller']->setView('DocumentBlank');
			$GLOBALS['controller']->captureFromOb(CONTENT);
            echo "<script type=\"text/javascript\" language=\"JavaScript\">
                  <!-- 
                      parent.leftFrame.location.reload(); 
                  //-->
                  </script>";
            $GLOBALS['controller']->captureStop(CONTENT);
		    $GLOBALS['controller']->setMessage(_('Данные обновлены'),JS_GO_URL,"{$sitepath}orgstructure_info.php?page_id={$GLOBALS['controller']->page_id}&id=".$id);
		    $GLOBALS['controller']->terminate();
            exit();
		} else {
			$GLOBALS['controller']->setView('DocumentBlank');
            $GLOBALS['controller']->setMessage(_('У Вас не хватает прав'),JS_GO_URL,"{$sitepath}orgstructure_main.php?page_id={$GLOBALS['controller']->page_id}&id=".$id);
            $GLOBALS['controller']->terminate();
            exit();            			
		}
		break;
}

require_once('lib/sajax/SajaxWrapper.php');
$sajaxJavascript = CSajaxWrapper::init(array('search_people_unused'));

$GLOBALS['controller']->captureFromOb(CONTENT);

$smarty = new Smarty_els();

$search = $people = $userCard = '';
$item = $competenceRolesList2ListHtml = false;



if ($id) {
	
//    $GLOBALS['controller']->setLink('m070601', array($id));
    
	$_SESSION['s']['orgstructure']['current'] = $id;
	
	// Главная инфа по элементу структуры
	$item = CPosition::get($id);
	
	if ($item->attributes['mid'] > 0) {
		$userCard = getUserCard($item->attributes['mid']);
	}
	
    if (($item->attributes['type'] != 2) && (get_people_count() < ITEMS_TO_ALTERNATE_SELECT)) $search = '*';      
	
	// Виды оценки
    $roles = CCompetenceRoles::get_as_array_by_soid($id,true);
       
    if (is_array($all = CCompetenceRoles::get_as_array(true))) {
        foreach($all as $role) {
            if (!isset($roles[$role['id']])) {
                $allRoles[$role['id']] = $role['name'];
            }
        }
    }
       
    if (is_array($roles)) {
        foreach($roles as $role) {
            $usedRoles[$role['id']] = $role['name'];
        }
    }
    
    $_smarty = new Smarty_els();
    $_smarty->assign('list1_title', _('Виды оценки'));
    $_smarty->assign('list2_title', _('Назначенные оценки'));
    $_smarty->assign('list1_name', 'allroles');
    $_smarty->assign('list2_name', 'roles');
    $_smarty->assign('list1_data', $allRoles);
    $_smarty->assign('list2_data', $usedRoles);
    $competenceRolesList2ListHtml = $_smarty->fetch('control_list2list_simple.tpl');
}

/*$GLOBALS['controller']->setTab('m070610');
$GLOBALS['controller']->setTab('m070611', array('href' => "orgstructure_main.php?page_id={$GLOBALS['controller']->page_id}&id=$id"));
$GLOBALS['controller']->setTab('m070612', array('href' => "orgstructure_main.php?page_id={$GLOBALS['controller']->page_id}&id=$id&type=add"));
$GLOBALS['controller']->setTab('m070613');
$GLOBALS['controller']->setTab('m070614');
*/

//$GLOBALS['controller']->setCurTab(isset($_GET['type']) && $_GET['type'] == "add" ? 'm070612' : 'm070611');

$smarty->assign('id',$id);
$smarty->assign('userCard', $userCard);
$smarty->assign('people', search_people_unused($search,@$item->attributes['mid']));
$smarty->assign('item',$item);
$smarty->assign('owner',COrgstructureSelectTree::fetch(@$item->attributes['owner_soid'],'owner'));
$smarty->assign('competenceRolesList2ListHtml', $competenceRolesList2ListHtml);
$smarty->assign('permStructureEdit', $GLOBALS['controller']->checkPermission(STRUCTURE_OF_ORGAN_PERM_EDIT));
$smarty->assign('permStructureAddUnit',  $GLOBALS['controller']->checkPermission(STRUCTURE_OF_ORGAN_PERM_ADD_ORGUNIT));
$smarty->assign('okbutton',okbutton());
$smarty->assign('sitepath',$sitepath);
$smarty->assign('sajaxJavascript',$sajaxJavascript);
echo $smarty->fetch('orgstructure_main.tpl');

$GLOBALS['controller']->captureStop(CONTENT);
$GLOBALS['controller']->terminate();

function search_people_unused($search, $current) {
    $html = '';
    $html .= "<option value=0>- "._("укажите")." -</option>";
    if ($current>0) {
        $sql = "SELECT People.MID, People.LastName, People.FirstName, People.Login
                FROM People
                WHERE People.MID = '".(int) $current."'";
        $res = sql($sql);
        if ($row = sqlget($res)) {
            $html .= "<option selected value='".(int) $row['MID']."'> ".htmlspecialchars($row['LastName'].' '.$row['FirstName'].' ('.$row['Login'].')',ENT_QUOTES)."</option>";
            $html .= "<option value=0> ------</option>";
        }
    }
    if (!empty($search)) {
        $search = iconv('UTF-8',$GLOBALS['controller']->lang_controller->lang_current->encoding,unicode_urldecode($search));
        $search = trim($search);
        $search = str_replace('*','%',$search);
        $where = "AND (People.LastName LIKE '%".addslashes($search)."%'
        OR People.FirstName LIKE '%".addslashes($search)."%'
        OR People.Login LIKE '%".addslashes($search)."%') AND People.MID NOT IN ('".(int) $current."')";
        $html .= peopleSelect4Position("Students", $current, $_REQUEST['soid'], '', true, $where);
    }    
    return $html;
}

function getChildrenIdArray($id) {
    $return_array = array();
    $query = "SELECT soid FROM structure_of_organ WHERE owner_soid = '".(int) $id."'";
    $res = sql($query);
    while ($row = sqlget($res)) {
        $return_array[] = $row['soid'];
        $tmp_array = getChildrenIdArray($row['soid']);
        foreach ($tmp_array as $tmp_value) {
            $return_array[] = $tmp_value;
        }       
    }
    return $return_array;
}

?>