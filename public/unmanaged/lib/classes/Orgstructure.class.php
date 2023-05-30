<?php
require_once($GLOBALS['wwf'].'/lib/classes/Position.class.php');

class COrgstructureSelectTree {
	function fetch($selected = 0, $selectName = "soid") {
	    $smarty = new Smarty_els();

	    $defaultValue = 0;
	    if ($selected > 0) {
	        $defaultValue = getField('structure_of_organ','owner_soid','soid',$selected);
	    }

	    $smarty->assign('list_name',$selectName);
	    $smarty->assign('container_name','container_'.$selectName);
	    $smarty->assign('list_extra'," style=\"width: 300px;\" ");
	    $smarty->assign('list_default_value',(int) $defaultValue);
	    $smarty->assign('list_selected', (int) $selected);
	    $smarty->assign('url',$GLOBALS['sitepath'].'structure.php');
	    return $smarty->fetch('control_treeselect.tpl');
	}
}

class COrgstructureTree {
	var $id;
	var $items = array();
	var $currentDispayed = false;
	var $html = '';
	var $filters = array();

	function addFilter($filter, $value) {
	    $this->filters[$filter] = $value;
	}

	function isFiltered($item, $filter, $value) {
        setlocale(LC_ALL, 'ru_RU.CP1251');
  	    switch ($filter) {
  	        case "position":
  	            if ($item->attributes['type'] != "2") {
  	                // preg_match('~^.*'.$value.'.*$~is', $item->attributes['name']) > 0
 	            	if(preg_match("/$value/is", $item->attributes['name']) > 0) {
 	            	    $_SESSION['s']['orgstructure']['filtered'][$item->attributes['soid']] = $item->attributes['soid'];
   	            	    return true;
   	            	}
   	            	return false;
	            }
	            else {
	                if (count($item->children)) {
                        foreach($item->children as $child) {
                            if ($this->isFiltered($child, $filter, $value)) {
                                return true;
                            }
                        }
	                }
                    return false;
	            }
	        break;
  	        case "login":
  	            if ($item->attributes['type'] != "2") {
 	            	if($item->attributes['mid'] > 0) {
   	            	    if ((preg_match("/$value/is", $item->attributes['LastName']) > 0) ||
   	            	        (preg_match("/$value/is", $item->attributes['FirstName']) > 0) ||
   	            	        (preg_match("/$value/is", $item->attributes['Patronymic']) > 0) ||
   	            	        (preg_match("/$value/is", $item->attributes['Login']) > 0)) {
   	            	            $_SESSION['s']['orgstructure']['filtered'][$item->attributes['soid']] = $item->attributes['soid'];
   	            	            return true;
   	            	    }
   	            	}
   	            	return false;
	            }
	            else {
	                if (count($item->children)) {
                        foreach($item->children as $child) {
                            if ($this->isFiltered($child, $filter, $value)) {
                                return true;
                            }
                        }
	                }
                    return false;
	            }
            break;
	        case "specialization":
  	            if ($item->attributes['type'] != "2") {
  	                if($item->attributes['specialization']==$value || !$value) {
 	            	    $_SESSION['s']['orgstructure']['filtered'][$item->attributes['soid']] = $item->attributes['soid'];
   	            	    return true;
   	            	}
   	            	return false;
	            }
	            else {
	                if (count($item->children)) {
                        foreach($item->children as $child) {
                            if ($this->isFiltered($child, $filter, $value)) {
                                return true;
                            }
                        }
	                }
                    return false;
	            }
	        break;
	    }
	    return true;
	}

	function isCurrentDisplayed() {
		return $this->currentDispayed;
	}

	function _getBalloon(&$item) {
		$balloon = '';
		if ($item->attributes['mid'] > 0) {
		    $balloon = "title=\"".htmlspecialchars($item->attributes['LastName'].' '.$item->attributes['FirstName'].' '.$item->attributes['Patronymic'], ENT_QUOTES)."\"";
		}
		return $balloon;
	}

	function _getChecked(&$item) {
		$checked = '';
		if (isset($_SESSION['s']['orgstructure']['checked'][$item->attributes['soid']])) {
			if (($item->attributes['type'] != 2) && (!$item->attributes['mid'])) {
			    unset($_SESSION['s']['orgstructure']['checked'][$item->attributes['soid']]);
			    return '';
			}
		    $checked = 'checked';
		}
		return $checked;
	}

	function _getTitle(&$item) {
		//$title = "<a href=\"{$GLOBALS['sitepath']}orgstructure_info.php?id={$item->attributes['soid']}\" target=\"mainFrame\" onClick=\"$('#toc').find('a').each(function(i) { $(this).removeClass('structureCurrentItem') }); $(this).addClass('structureCurrentItem'); $('#checkbox{$item->attributes['soid']}').get(0).checked = true; if (structureItemClick) structureItemClick('{$item->attributes['soid']}',true); if (parent.mainFrame.get_checked_items) parent.mainFrame.getCheckedItems(); return true;\"";
        $title = "<a href=\"{$GLOBALS['sitepath']}orgstructure_info.php?id={$item->attributes['soid']}\" target=\"mainFrame\" onClick=\"if (structureItemClick) structureItemClick(this, '{$item->attributes['soid']}')\"";
	    if (isset($_SESSION['s']['orgstructure']['current']) && ($item->attributes['soid'] == $_SESSION['s']['orgstructure']['current'])) {
			$title .= " class=\"current-item\" ";
		}
        $title .= ">";
		$title .= htmlspecialchars($item->attributes['name'], ENT_QUOTES);
		$title .= "</a>";
		return $title;
	}

	function _getIcon(&$item, $new = false) {
	    if ($new) return '';
	    $icon = "<img border=0 hspace=2 src=\"".$GLOBALS['sitepath']."images/icons/positions_type_".(int) $item->attributes['type'].".gif\" align=\"absmiddle\" />";
	    return $icon;
	}

	function _getClass(&$item, $new = false) {
		$class = '';
        if (($item->attributes['type'] == 2) && !$item->opened) {
            if ($new) {
               $class .= ' branch ';
            } else {
        	   $class .= ' hasChildren ';
            }
        }

        if (($item->attributes['type'] == 2) && $item->opened) {
            if ($new) {
                $class .= ' branch-expanded ';
            } else {
                $class .= ' open ';
            }
        }

        if ($new) {
            switch($item->attributes['type']) {
                case 0:
                    $class .= ' position-default ';
                    break;
                case 1:
                    $class .= ' position-boss ';
                    break;
                case 2:
                    $class .= ' position-unit ';
                    break;
            }
        }

        return $class;
    }

    function _getCheckbox(&$item) {
    	$disabled = '';
    	if (($item->attributes['type'] != 2) && $item->attributes['mid'] <= 0) {
    		$disabled = 'disabled';
    	}
    	$checkbox = "<input $disabled type=\"checkbox\" id=\"checkbox{$item->attributes['soid']}\" name=\"items[]\" value=\"{$item->attributes['soid']}\" onClick=\"if (_structureItemClick) _structureItemClick('{$item->attributes['soid']}',this.checked); if (parent.mainFrame.get_checked_items) parent.mainFrame.getCheckedItems(); return true;\" ".$this->_getChecked($item)." />";
    	return $checkbox;
    }

	function _iterateFetch(&$item, $new = false) {
	    if (count($this->filters)) {
	        foreach ($this->filters as $filter => $value) {
		        if (!$this->isFiltered($item, $filter, $value)) {
			        return $this->html;
		        }
	        }
	    }
		if (isset($_SESSION['s']['orgstructure']['current']) && ($item->attributes['soid'] == $_SESSION['s']['orgstructure']['current'])) {
			$this->currentDispayed = true;
		}

        $id    = "soid_{$item->attributes['soid']}";

		$this->html .= sprintf("<li id=\"%s\" class=\"%s\" %s>%s%s%s\n",
		    $id,
	        $this->_getClass($item, $new),
	        $this->_getBalloon($item),
	        $this->_getCheckbox($item),
	        $this->_getIcon($item, $new),
		    $this->_getTitle($item)
		    );

	   if (isset($item->children) && is_array($item->children)) {
	       $this->html .= '<ul>';
	       foreach($item->children as $child) {
	           $this->_iterateFetch($child, $new);
	       }
	       $this->html .= '</ul>';
	   }
       $this->html .= '</li>';
	}

	function fetch($new = false) {
		if (count($this->items)) {
			foreach($this->items as $item) {
			    $this->_iterateFetch($item, $new);
			}
			if (strlen($this->html)) {
			     $this->html = "<ul>" . $this->html . "</ul>";
			}
			else {
			    $this->html = "<ul><li>"._("Ничего не найдено")."</li></ul>";
			}
		}
		return $this->html;
	}

	function _initializeOpenedItem(&$item) {
		if ($item->attributes['type'] == 2) {
			$_SESSION['s']['orgstructure']['opened'][$item->attributes['soid']] = $item->attributes['soid'];
		}
	}

    function initializeOpened($items = false) {
        if (!$items) {
            unset($_SESSION['s']['orgstructure']['opened']);
    		$items = $this->items;
    	}
    	if (count($items)) {
        	foreach($items as $item) {
        		$this->_initializeOpenedItem($item);
        		if (isset($item->children) && count($item->children) && count($item->children)) {
        			$this->initializeOpened($item->children);
        		}
        	}
        }
    }

	function initialize($id = 0) {
		$this->id = (int) $id;
		if ($this->id == 0) {
			$this->items = $this->_initializeHead();
		} else {
			$this->items = $this->_initializeBranch($this->id);
		}
	}

	function _initializeBranch($owner) {
		$items = array();
		$sql = "SELECT structure_of_organ.*, People.Login, People.LastName, People.FirstName, People.Patronymic
                FROM structure_of_organ
                LEFT JOIN People ON (People.MID=structure_of_organ.mid)
                WHERE structure_of_organ.owner_soid='".$owner."'
                ORDER BY structure_of_organ.type DESC, structure_of_organ.name";
		$res = sql($sql);

		while($row = sqlget($res)) {
		    switch($row['type']) {
		    	case 2:
		    		$items[$row['soid']] = new CUnitPosition($row);
		    		break;
		    	case 1:
                    $items[$row['soid']] = new CHeadPosition($row);
		    		break;
		    	default:
                    $items[$row['soid']] = new CSlavePosition($row);
		    		break;
		    }

		    $items[$row['soid']]->opened = false;

		    if (isset($_SESSION['s']['orgstructure']['opened'][$row['soid']]) || count($this->filters)) {
		    	$items[$row['soid']]->children = $this->_initializeBranch($row['soid']);
		    	$items[$row['soid']]->opened = true;
	            if (count($this->filters) && !isset($_SESSION['s']['orgstructure']['opened'][$row['soid']])) {
	               $_SESSION['s']['orgstructure']['opened_filter'][$row['soid']] = $row['soid'];
                }
		    }

		}

		return $items;
	}

	function _initializeHead() {
		if ((is_structured($_SESSION['s']['mid']) || is_kurator($_SESSION['s']['mid'])) &&
		    !(($_SESSION['s']['perm'] == 3) && ATUser::isManager($_SESSION['s']['mid']) && !is_kurator($_SESSION['s']['mid']))) {
	        $soids = $soid_owner = $owner_soid = array();
	        list($soid_owner, $owner_soid) = $this->_getOwnerArrays();
	        $sql = "SELECT owner_soid as soid FROM structure_of_organ WHERE mid='".(int) $_SESSION['s']['mid']."' AND type='1'";
	        $res = sql($sql);
	        while($row = sqlget($res)) {
	            $soids[$row['soid']] = $row['soid'];
	        }

	        if ($_SESSION['s']['perm'] == 3) {
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
	                COrgstructureTree::_checkItemUp($row['soid'], $soids, $owner_soid);

	            }

	            $soids[$row['soid']] = $row['soid'];
	        }
	        }

	        $items = array();
	        if (is_array($soids) && count($soids)) {
	            $sql = "SELECT structure_of_organ.*, People.Login, People.LastName, People.FirstName, People.Patronymic
                FROM structure_of_organ
                LEFT JOIN People ON (People.MID=structure_of_organ.mid)
                WHERE structure_of_organ.soid IN ('".join("','",$soids)."')
                ORDER BY structure_of_organ.type DESC, structure_of_organ.name";
	            $res = sql($sql);
	            while($row = sqlget($res)) {
			        switch($row['type']) {
		                case 2:
		                    $items[$row['soid']] = new CUnitPosition($row);
		                    break;
		                case 1:
		                    $items[$row['soid']] = new CHeadPosition($row);
		                    break;
		                default:
		                    $items[$row['soid']] = new CSlavePosition($row);
		                    break;
		            }

		            if (isset($_SESSION['s']['orgstructure']['opened'][$row['soid']]) || count($this->filters)) {
	                    $items[$row['soid']]->children = $this->_initializeBranch($row['soid']);
	                    $items[$row['soid']]->opened = true;
	                    if (count($this->filters) && !isset($_SESSION['s']['orgstructure']['opened'][$row['soid']])) {
	                        $_SESSION['s']['orgstructure']['opened_filter'][$row['soid']] = $row['soid'];
	                    }
	                }
	            }
	        }
		} else {
		     $items = $this->_initializeBranch(0);
		}

		return $items;

	}

	function _getOwnerArrays() {
		$soid_owner = $owner_soid = array();
	    $sql = "SELECT soid, owner_soid
	            FROM structure_of_organ";
	    $res = sql($sql);
	    while($row = sqlget($res)) {
	        $soid_owner[$row['soid']]                     = $row['owner_soid'];
	        $owner_soid[$row['owner_soid']][$row['soid']] = $row['soid'];
	    }
	    return array($soid_owner, $owner_soid);
	}

	function _checkItemUp($soid, &$soids, &$owner_soid) {

	    if (is_array($owner_soid[$soid]) && count($owner_soid[$soid])) {
	        foreach($owner_soid[$soid] as $v) {
	            if (isset($soids[$v])) unset($soids[$v]);
	            COrgstructureTree::_checkItemUp($v, $soids, $owner_soid);
	        }
	    }
	}

	function getSpecializations(){
	    $sql = "SELECT * FROM `specializations`";
	    $res = sql($sql);

	    $ret = array();
	    while ($row = sqlget($res)){
	        $ret[$row['spid']] = $row['name'];
	    }
	    return $ret;
	}
}
?>