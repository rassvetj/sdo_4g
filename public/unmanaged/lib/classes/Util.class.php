<?
class ActionsUtil{

	function getActionsBasic($profile_basic_name){
        if (!is_array($profile_basic_name)) {
            $profile_basic_name = array($profile_basic_name);
        }
		$return = $used = array();
		if ($GLOBALS['domxml_object']){
			if (is_array($groups = $GLOBALS['domxml_object']->get_elements_by_tagname("group"))) {
				foreach ($groups as $group) {
					if (is_array($profiles = explode(DELIMITER_ACTIONS, $group->get_attribute('profiles')))) {
						for ($i = 0; $i < count($profiles); $i++) $profiles[$i] = trim($profiles[$i]);
                        foreach($profile_basic_name as $profileName) {
                            if (isset($used[$group->get_attribute('id')])) {
                                break;
                            }
                            if (in_array($profileName, $profiles)) {
                                $used[$group->get_attribute('id')] = $group->get_attribute('id');
  							    $action = new Action();
							    $action->initialize(array($group->get_attribute('id'), 'group', iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding, $group->get_attribute('name')), $group->get_attribute('icon')));
							    $return[] = $action;
						    }
					    }
				    }
			    }
		    }
		}
		return $return;
	}

	function getActionsExtended($pmid){
		$return = array();
		if ($GLOBALS['domxml_object']){
			$query = "
				SELECT
				  permission2act.acid
				FROM
				  permission2act
				WHERE
				  (permission2act.pmid = '{$pmid}')
			";
			$res = sql($query);
			while ($row = sqlget($res)) {
				if ($element = $GLOBALS['domxml_object']->get_element_by_id($row['acid'])){
					if (in_array($element->tagname, array('tab', 'link', 'option'))) {
						if ($page_id = ActionsUtil::getMenuElementPageId($row['acid'])){
							$element = $GLOBALS['domxml_object']->get_element_by_id($page_id);
						} else {
							unset($element);
						}
					}
					if (isset($element)){
						$action = new Action();
						$action->initialize(array($element->get_attribute('id'), $element->tagname, iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding, $element->get_attribute('name')), $element->get_attribute('icon')));
						$return[] = $action;
					}
				}
			}
		}
		return $return;
	}

	function getLinksDefault($page_id){
		$return = array();
		if ($GLOBALS['domxml_object']){
			if ($page = $GLOBALS['domxml_object']->get_element_by_id($page_id)){
				if (count($links = $page->get_elements_by_tagname("link"))) {
					foreach ($links as $element) {
                        if (!$element->has_attribute('hide') || ($element->get_attribute('hide')=='false'))
						    $return[] = $element->get_attribute('id');
					}
				}
			}
		}
		return $return;
	}

	function getMenuGroup($group_action_id){
		if ($GLOBALS['domxml_object']){
			if ($group = $GLOBALS['domxml_object']->get_element_by_id($group_action_id)){
				$order = ($val = $group->get_attribute('order')) ? $val : 1000;
				return array(iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding, _($group->get_attribute('name'))), $group->get_attribute('icon'), $order, $group->get_attribute('hide'));
			}
		}
	}

	function getMenuElements($group_action_id){
		$return = array();
		if ($GLOBALS['domxml_object']){
			if ($group = $GLOBALS['domxml_object']->get_element_by_id($group_action_id)){
				if (count($customs = $group->get_elements_by_tagname("custom"))) {
					$custom = array_shift($customs);
					return ActionsUtil::getMenuElementsCustom($custom->get_attribute('id'));
				}
                if ($group->has_child_nodes()) {
                    $children = $group->child_nodes();
                    foreach($children as $item) {
                        if (method_exists($item, 'tagname')) {
                            if ($item->tagname() == 'page') {
        						$page_id = $item->get_attribute('id');
                                //todo custom permissions
        						if ($GLOBALS['controller']->checkPermission($page_id)) {
        							$element = new MenuElement();
        							$element->initialize($page_id);
        							$element->appendMenuId();
        							$return[$element->id] = $element;
        						}                        
                            }
                            
                            if ($item->tagname() == 'subgroup') {
                                if ($item->has_child_nodes()) {
                                    $element = new MenuSubGroup();
                                    $subItems = $item->child_nodes();
                                    $withRigthPermissions = 0;
                                    foreach($subItems as $subItem) {
                                        if (method_exists($subItem, 'tagname')) {
                                        if ($subItem->tagname() == 'page') {
                    						$page_id = $subItem->get_attribute('id');
                                            //todo custom permissions
                    						if ($GLOBALS['controller']->checkPermission($page_id)) {
                    							$subElement = new MenuElement();
                    							$subElement->initialize($page_id);
                    							$subElement->appendMenuId();
                                                $element->addChild($subElement);
                                                $withRigthPermissions++;
                    							//$return[$element->id] = $element;
                    						}                        
                                        }                                
                                    }
                                    }
                                    if($withRigthPermissions > 0){
                                        $return[] = $element;
                                    }
                                }
                            }
                        }
                    }
                }
                /*
				if (is_array($pages = $group->get_elements_by_tagname("page"))) {
					foreach ($pages as $page) {
						$page_id = $page->get_attribute('id');
//						todo custom permissions
						if ($GLOBALS['controller']->checkPermission($page_id)) {
							$element = new MenuElement();
							$element->initialize($page_id);
							$element->appendMenuId();
							$return[$element->id] = $element;
						}
					}
				}
                                    */
			}
		}
		return $return;
	}

	function getMenuElement($page_action_id){
		if ($GLOBALS['domxml_object']){
			if ($page = $GLOBALS['domxml_object']->get_element_by_id($page_action_id)){
				return array(iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding, _($page->get_attribute('name'))), $page->get_attribute('url'), $page->get_attribute('alt'), iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding, $page->get_attribute('name_full')), $page->get_attribute('target'));
			}
		}
	}

	function getMenuElementsCustom($custom_action_id){
		$return = array();
		

		switch ($custom_action_id) {
		    case 'm990':

		        $group_id = ActionsUtil::getMenuElementGroupId($custom_action_id);
		         
		        $query = "SELECT value FROM OPTIONS WHERE name ='activity'";
		        $res = sql($query);
		        $val = sqlget($res);
		        $val = unserialize($val['value']);

                //ksort($val);
                if (is_array($val) && count($val)) {
                    foreach($val as $key => $value){
                        $element = new MenuElement();
                        $element->setGroup($group_id);
                        $value['url'] = substr($value['url'], 1);
                        $element->initializeCustom(array($custom_action_id . $key, _($value['name']), $value['url'], _($value['name'])));
                        $element->appendMenuId();
                        $return[$custom_action_id . $key] = $element;
                    }
                }
                
		        break;
			case 'm1301':
				if(count($courses = $GLOBALS['controller']->user->getCourses())){
					foreach ($courses as $course) {
						$group_id = ActionsUtil::getMenuElementGroupId($custom_action_id);
						$custom_id = $group_id . $course->id;
						$url = "subject/index/index/subject_id/{$course->id}";
/*						if ($_SESSION['s']['perm'] == 2) {						
                            $url = "cms/course_constructor.php?CID={$course->id}";
						}
*/						$alt = _("Открыть программу курса");
						if ($course->locked && ($_SESSION['s']['perm']>1)) {
						    $course->title .= " <img border=0 style=\"vertical-align: bottom\" src=\"".$GLOBALS['controller']->view_root->root_url."/images/icons/lock_.gif\">";
						}
						$element = new MenuElement();
						$element->setGroup($group_id);
						$element->initializeCustom(array($custom_id, $course->title, $url, $alt));
						$element->appendMenuId();
						$return[$custom_id] = $element;
					}

				}

				if (!count($courses)) {
                    $group_id = ActionsUtil::getMenuElementGroupId($custom_action_id);
                    if ($group_id == 'm13') {
                        if(count($courses = $GLOBALS['controller']->user->getCoursesAll())){
                            foreach ($courses as $course) {
                                $custom_id = $group_id . $course->id;
                                $url = "teachers/manage_course.php4?CID={$course->id}";
                                $alt = _("Открыть программу курса");
                                if ($course->locked && ($_SESSION['s']['perm']>1)) {
                                    $course->title .= " <img border=0 style=\"vertical-align: bottom\" src=\"".$GLOBALS['controller']->view_root->root_url."/images/icons/lock_.gif\">";
                                }
                                $element = new MenuElement();
                                $element->setGroup($group_id);
                                $element->initializeCustom(array($custom_id, $course->title, $url, $alt));
                                $element->appendMenuId();
                                $return[$custom_id] = $element;
                            }
                        }

                        $this->hide = true;
                    }
				}

				break;
			default:
				break;
		}

		return $return;
	}

	function getMenuElementGroupId($page_action_id){
		$return = array();
		if ($GLOBALS['domxml_object']){
			if ($page = $GLOBALS['domxml_object']->get_element_by_id($page_action_id)){
				$parent = $page->parent_node();
				return $parent->get_attribute('id');
			}
		}
	}

	function getMenuElementPageId($action_id){
		$return = array();
		if ($GLOBALS['domxml_object']){
			if ($element = $GLOBALS['domxml_object']->get_element_by_id($action_id)){
				$parent = $element->parent_node();
				return $parent->get_attribute('id');
			}
		}
	}

	function getTab($tab_id){
		if ($GLOBALS['domxml_object']){
			if ($tab = $GLOBALS['domxml_object']->get_element_by_id($tab_id)){
				return array(iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding, _($tab->get_attribute('name'))), iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding, _($tab->get_attribute('name_full'))), $tab->get_attribute('order'), $tab->get_attribute('href'));
			}
		}
	}

	function getLink($link_id){
		if ($GLOBALS['domxml_object']){
			if ($link = $GLOBALS['domxml_object']->get_element_by_id($link_id)){
				return array(iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding, _($link->get_attribute('name'))), $link->get_attribute('url'), $link->get_attribute('target'), iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding, _($link->get_attribute('alt'))), $link->get_attribute('params'), $link->get_attribute('order'), $link->get_attribute('hide'), iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding,_($link->get_attribute('confirm'))), $link->get_attribute('anchor'));
			}
		}
	}

	function getType($id){
		if ($id === CONTENT) return 'Content';
		if ($id === CONTENT_EXPANDED) return 'ContentExpanded';
		if ($id === CONTENT_COLLAPSED) return 'ContentCollapsed';
		if ($id === NEWS) return 'News';
		if ($GLOBALS['domxml_object']){
			return ($node = $GLOBALS['domxml_object']->get_element_by_id($id)) ? $node->tagname : TRASH;
		}
	}

	function checkPermissionExtended($id, $profile_current){
        $pmid = str_replace(PREFIX_PROFILE, '', $profile_current->name);
		$query = "
			SELECT
			  *
			FROM
			  permission2act
			WHERE
			  pmid = '{$pmid}' AND
			  acid LIKE '{$id}'
		";
		$res = sql($query);
        $ret = sqlrows($res);
        //if (($ret <= 0) && (strlen($id)>5)) $ret = ActionsUtil::checkPermissionExtended(substr($id,0,-2),$profile_current);
		return $ret;
	}

	function checkPermissionBasic($id, $profile_current){
		if ($GLOBALS['domxml_object']){
			if ($element = $GLOBALS['domxml_object']->get_element_by_id($id)){
                $profiles = array($profile_current->name);
                if (isset($GLOBALS['profiles_inheritance'][$profile_current->name])) {
                    $profiles = $GLOBALS['profiles_inheritance'][$profile_current->name];
                }

				do {

					if ($profile_attr = $element->get_attribute('profiles')) {
                        $false = 0;
						foreach (explode(DELIMITER_ACTIONS, $profile_attr) as $profile) {
							$profile_name = trim($profile);

                            if ($profile_current->name == $profile_name) {
                                return true;
                            }

                            foreach($profiles as $profileIndex => $profile) {
                                if ($profile_name == $profile) {
                                    return true;
                                } elseif ($profile_name == "~{$profile}") {
                                    $false++;
                                    unset($profiles[$profileIndex]);
                                    if (!count($profiles)) {
                                        return false;
                                    }
							    }
						    }
						}
/*                        if ($false >= count($profiles)) {
                            return false;
                        }*/
					}
					$element = $element->parent_node();
				} while ((get_class($element) === 'domelement') || (get_class($element) === 'php4DOMElement'));
			}
		}
	}

	function order(&$array){
		$keys = array_keys($array);
		for ($i = count($array) - 1; $i > 1 ; $i--) {
			for ($j = 0; $j < $i; $j++) {
				if (!isset($array[$keys[$j]]->order)) return true;
				if ($array[$keys[$j]]->order > $array[$keys[$j+1]]->order){
					$tmp = $array[$keys[$j]];
					$array[$keys[$j]] = $array[$keys[$j+1]];
					$array[$keys[$j+1]] = $tmp;
				}
			}
		}
	}

	function &getSelectedGroupById($id){
		/*
		for ($i = 0; $i < count($GLOBALS['controller']->menu->groups); $i++) {
			if ($GLOBALS['controller']->menu->groups[$i]->id == $id) return $GLOBALS['controller']->menu->groups[$i];
		}
		*/
		if(is_array($GLOBALS['controller']->menu->groups) && count($GLOBALS['controller']->menu->groups)){
	        foreach ($GLOBALS['controller']->menu->groups as $group) {
	            if ($group->id == $id) return $group;
	        }
		}
		else return null;
	}
}
?>