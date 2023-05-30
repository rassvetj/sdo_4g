<?php

$base_roles = array();//$profiles_basic; //array('guest','student','teacher','dean','admin');

$base_roles_lang = array();//$profiles_basic_aliases; //array(_("Гость"), _("Слушатель"), _("Преподаватель"), _("Учебная администрация"), _("Администратор"));

$i = 0;
foreach ($profiles_basic_ids as $k=>$v) {
    $base_roles_lang[$v] = $profiles_basic_aliases[$k];
    $base_roles[$v] = $profiles_basic[$i++];
}

$necessary_actions = array(_("Главная страница")=>'index');

class CActions {
    
    var $actions;
    var $necessary;
    
    function CActions() {
        $this->actions = $this->parse_actions();        
    }
    
    function get_necessary() {
        return $this->necessary;
    }
    
    function parse_actions() {
        $filename = FILE_ACTIONS;
        if (file_exists($filename) && is_file($filename)) {
            $strXML = file_get_contents($filename);
            $objXML = new xml2Array();
            $arrXML = $objXML->parse($strXML);
            unset($strXML);
            if (is_array($arrXML)) {
                $objects = array();
                $objects = $this->parse_actions_xml($arrXML,$objects);
            }
        
        }
        
        //pr($objects);
        return $objects;
    }

    function parse_actions_xml($blocks,$objects) {
            static $parents = array();        
            static $group;
            static $page;
            static $type;
            static $profiles;

            if (count($blocks) > 0) {
                foreach ($blocks as $block) {
                    switch($block['name']) {
                        case 'ACTIONS':
                        case 'SUBGROUP':
                            $objects = $this->parse_actions_xml($block['children'],$objects);
                        break;
                        case 'GROUP':
                            $group = addslashes($block['attrs']['ID']);
                            $objects[$group]['id'] = $group;
                            $objects[$group]['name'] = isset($block['attrs']['NAME']) ? addslashes(iconv('UTF-8',$GLOBALS['controller']->lang_controller->lang_current->encoding,$block['attrs']['NAME'])) : '';
                            $objects[$group]['icon'] = isset($block['attrs']['ICON']) ? $block['attrs']['ICON'] : '';
                            $objects[$group]['order'] = isset($block['attrs']['ORDER']) ? $block['attrs']['ORDER'] : '';
                            $objects[$group]['profiles'] = isset($block['attrs']['PROFILES']) ? $block['attrs']['PROFILES'] : '';
                            $profiles[0] = explode(',',$objects[$group]['profiles']);
                            array_walk($profiles[0],'array_trim');
                            $objects[$group]['profiles'] = join(',',$profiles[0]);
                            $objects = $this->parse_actions_xml($block['children'],$objects);                        
                        break;
                        case 'PAGE':
                            $type = 'pages';
                            $page = addslashes($block['attrs']['ID']);
                            $objects[$group]['pages'][$page]['id'] = $page;
                            $objects[$group]['pages'][$page]['name'] = isset($block['attrs']['NAME']) ? addslashes(iconv('UTF-8',$GLOBALS['controller']->lang_controller->lang_current->encoding,$block['attrs']['NAME'])) : '';
                            $objects[$group]['pages'][$page]['name_full'] = isset($block['attrs']['NAME_FULL']) ? addslashes(iconv('UTF-8',$GLOBALS['controller']->lang_controller->lang_current->encoding,$block['attrs']['NAME_FULL'])) : '';
                            $objects[$group]['pages'][$page]['url'] = isset($block['attrs']['URL']) ? $block['attrs']['URL'] : '';
                            $objects[$group]['pages'][$page]['order'] = isset($block['attrs']['ORDER']) ? $block['attrs']['ORDER'] : '';
                            $current_profiles = isset($block['attrs']['PROFILES']) ? $block['attrs']['PROFILES'] : '';
                            $objects[$group]['pages'][$page]['profiles'] = $this->get_profiles($profiles[0], $current_profiles);
                            $profiles[1] = explode(',',$objects[$group]['pages'][$page]['profiles']);
                            $objects = $this->parse_actions_xml($block['children'],$objects);
                        break;
                        case 'CUSTOM':
                            $type = 'customs';
                            $page = addslashes($block['attrs']['ID']);                        
                            $objects[$group]['customs'][$page]['id'] = $page;
                            $objects[$group]['customs'][$page]['name'] = isset($block['attrs']['NAME']) ? addslashes(iconv('UTF-8',$GLOBALS['controller']->lang_controller->lang_current->encoding,$block['attrs']['NAME'])) : '';
                            $objects[$group]['customs'][$page]['name_full'] = isset($block['attrs']['NAME_FULL']) ? addslashes(iconv('UTF-8',$GLOBALS['controller']->lang_controller->lang_current->encoding,$block['attrs']['NAME_FULL'])) : '';
                            $current_profiles = isset($block['attrs']['PROFILES']) ? $block['attrs']['PROFILES'] : '';
                            $objects[$group]['customs'][$page]['profiles'] = $this->get_profiles($profiles[0],$current_profiles);
                            $profiles[1] = explode(',',$objects[$group]['customs'][$page]['profiles']);
                            $objects = $this->parse_actions_xml($block['children'],$objects);                        
                        break;
                        case 'LINK':
                            $id = addslashes($block['attrs']['ID']);
                            $objects[$group][$type][$page]['links'][$id]['id'] = $id;
                            $objects[$group][$type][$page]['links'][$id]['name'] = isset($block['attrs']['NAME']) ? addslashes(iconv('UTF-8',$GLOBALS['controller']->lang_controller->lang_current->encoding,$block['attrs']['NAME'])) : '';
                            $objects[$group][$type][$page]['links'][$id]['url'] = isset($block['attrs']['URL']) ? $block['attrs']['URL'] : '';
                            $objects[$group][$type][$page]['links'][$id]['alt'] = isset($block['attrs']['ALT']) ? $block['attrs']['ALT'] : '';
                            $objects[$group][$type][$page]['links'][$id]['target'] = isset($block['attrs']['TARGET']) ? $block['attrs']['TARGET'] : '';
                            $objects[$group][$type][$page]['links'][$id]['params'] = isset($block['attrs']['PARAMS']) ? $block['attrs']['PARAMS'] : '';
                            $objects[$group][$type][$page]['links'][$id]['order'] = isset($block['attrs']['ORDER']) ? $block['attrs']['ORDER'] : '';
                            $current_profiles = isset($block['attrs']['PROFILES']) ? $block['attrs']['PROFILES'] : '';
                            $objects[$group][$type][$page]['links'][$id]['profiles'] = $this->get_profiles($profiles[1],$current_profiles);
                            $objects = $this->parse_actions_xml($block['children'],$objects);                        
                        break;
                        case 'TAB':
                            $id = addslashes($block['attrs']['ID']);
                            $objects[$group][$type][$page]['tabs'][$id]['id'] = $id;
                            $objects[$group][$type][$page]['tabs'][$id]['name'] = isset($block['attrs']['NAME']) ? addslashes(iconv('UTF-8',$GLOBALS['controller']->lang_controller->lang_current->encoding,$block['attrs']['NAME'])) : '';
                            $objects[$group][$type][$page]['tabs'][$id]['url'] = isset($block['attrs']['URL']) ? $block['attrs']['URL'] : '';
                            $objects[$group][$type][$page]['tabs'][$id]['alt'] = isset($block['attrs']['ALT']) ? $block['attrs']['ALT'] : '';
                            $objects[$group][$type][$page]['tabs'][$id]['target'] = isset($block['attrs']['TARGET']) ? $block['attrs']['TARGET'] : '';
                            $objects[$group][$type][$page]['tabs'][$id]['params'] = isset($block['attrs']['PARAMS']) ? $block['attrs']['PARAMS'] : '';
                            $objects[$group][$type][$page]['tabs'][$id]['order'] = isset($block['attrs']['ORDER']) ? $block['attrs']['ORDER'] : '';
                            $current_profiles = isset($block['attrs']['PROFILES']) ? $block['attrs']['PROFILES'] : '';
                            $objects[$group][$type][$page]['tabs'][$id]['profiles'] = $this->get_profiles($profiles[1],$current_profiles);                        
                            $objects = $this->parse_actions_xml($block['children'],$objects);                        
                        break;
                        case 'OPTION':
                            $id = addslashes($block['attrs']['ID']);
                            $objects[$group][$type][$page]['options'][$id]['id'] = $id;
                            $objects[$group][$type][$page]['options'][$id]['name'] = isset($block['attrs']['NAME']) ? addslashes(iconv('UTF-8',$GLOBALS['controller']->lang_controller->lang_current->encoding,$block['attrs']['NAME'])) : '';
                            $current_profiles = isset($block['attrs']['PROFILES']) ? $block['attrs']['PROFILES'] : '';
                            $objects[$group][$type][$page]['options'][$id]['profiles'] = $this->get_profiles($profiles[1],$current_profiles);
                            $objects = $this->parse_actions_xml($block['children'],$objects);                        
                        break;
                    }                
                }
            }        
            return $objects;    
    }

    function get_profiles($profiles, $current_profiles) {
        if (empty($current_profiles)) {
            $ret = join(',',$profiles);
        } else {
            $profiles_arr = explode(',',$current_profiles);
            array_walk($profiles_arr,'array_trim');
            foreach($profiles_arr as $k=>$v) {
                // todo вышестоящие + нижестоящие                                                                            
                if ($v[0] == '~') $profiles_del[] = substr($v,1);
                else $profiles[] = $v;
            }
            $profiles = array_unique($profiles);
            if (is_array($profiles_del) && count($profiles_del)) {
                $profiles_arr = array_diff($profiles,$profiles_del);
                $ret = join(',',$profiles_arr);
            } else {
                $ret = join(',',$profiles);
            }
            
        }
        return $ret;
    }
    
    /**
    * roles - array
    */
    function get_actions_names_by_roles($roles) {
        $this->necessary = false;
        if (is_array($roles) && count($roles)) {
            $pattern = '/('.join('|',$roles).')/';
        }
        
        if (is_array($this->actions) && count($this->actions)) {            
            foreach($this->actions as $k=>$v) {
                if (preg_match($pattern,$v['profiles'])) {
                    if (!in_array($v['name'],$GLOBALS['necessary_actions']))
                        $ret[$k] = $v['name'];
                    else $this->necessary[$k] = $v['name'];
                }
                if (is_array($v['pages']) && count($v['pages'])) {
                    foreach($v['pages'] as $kk=>$vv) {
                        if (preg_match($pattern,$vv['profiles'])) {
                            if (!in_array($v['name'],$GLOBALS['necessary_actions'])) 
                                $ret[$kk] = $v['name'].' :: '.$vv['name'];
                            else 
                                $this->necessary[$kk] = $v['name'];
                        }
                    }
                }
                if (is_array($v['customs']) && count($v['customs'])) {
                    foreach($v['customs'] as $kk=>$vv) {
                        if (preg_match($pattern,$vv['profiles'])) {
                            if (!in_array($v['name'],$GLOBALS['necessary_actions'])) 
                                $ret[$kk] = $v['name'].' :: '.$vv['name'];
                            else 
                                $this->necessary[$kk] = $v['name'];
                        }
                    }
                }                
            }            
        }
        
        return $ret;        
    }

    function get_all_actions_names() {
        
        if (is_array($this->actions) && count($this->actions)) {            
            foreach($this->actions as $k=>$v) {
                $ret[$k] = $v['name'];
                $ret = array_merge($ret,$this->get_actions_names($v,'pages'));
                $ret = array_merge($ret,$this->get_actions_names($v,'customs'));
            }            
        }
        return $ret;        
    }
    
    function get_actions_names($v,$action) {
        $ret = array();
        if (is_array($v[$action]) && count($v[$action])) {
            foreach($v[$action] as $kk=>$vv) {
                $ret[$kk] = $v['name'].' :: '.$vv['name'];
                $vv['name'] = $ret[$kk];
                $ret = array_merge($ret,$this->get_subactions_names($vv,'links'));
                $ret = array_merge($ret,$this->get_subactions_names($vv,'tabs'));
                $ret = array_merge($ret,$this->get_subactions_names($vv,'options'));
            }
        }        
        return $ret;
    }
    
    function get_subactions_names($vv,$sub) {
        $ret = array();
        if (is_array($vv[$sub]) && count($vv[$sub])) {
            foreach($vv[$sub] as $kkk=>$vvv)
                $ret[$kkk] = $vv['name'].' :: '.$vvv['name'];
        }  
        return $ret;
    }
    
    function get_actions_by_pages($pages,$roles,$roles_only=false) {
        if (is_array($pages) && count($pages)) {
            foreach($pages as $k=>$v) {
                if (strlen($v)==5) {
                    $group = substr($v,0,3);
                    if (isset($this->actions[$group]['pages'][$v])) {
                        $ret[$v] = $this->actions[$group]['pages'][$v];
                        $ret[$v]['name'] = $this->actions[$group]['name'].' :: '
                                           .$this->actions[$group]['pages'][$v]['name'];
                        if ($roles_only)
                        $this->get_only_role_actions($ret[$v],$roles);
                    }
                    if (isset($this->actions[$group]['customs'][$v])) {
                        $ret[$v] = $this->actions[$group]['customs'][$v];
                        $ret[$v]['name'] = $this->actions[$group]['name'].' :: '.$this->actions[$group]['customs'][$v]['name'];
                        if ($roles_only)
                        $this->get_only_role_actions($ret[$v],$roles);
                    }
                }
            }
        } 
        return $ret;        
    }
    
    /**
    * @param array $roles
    */
    function get_only_role_actions(&$actions, $roles) {        
        if (is_array($actions) && count($actions)) {
            if (is_array($roles) && count($roles)) {
                $pattern = '/('.join('|',$roles).')/';
                $arr_names = array('tabs','links','options');
                foreach($arr_names as $name) {
                    if (is_array($actions[$name]) && count($actions[$name])) {
                        foreach($actions[$name] as $k=>$v) {
                            if (!preg_match($pattern,$v['profiles'])) unset($actions[$name][$k]);
                        }
                    }
                }
            }
        }        
    }
                
    function prepare_actions_to_save($actions, $pages) {
        
        
        $ret = array();
        $groups = array();
        
        if (is_array($actions) && count($actions) && is_array($pages) && count($pages)) {
            foreach($pages as $page) {
                $group = substr($page,0,3);
                if ((strlen($page)>=5) && !in_array($group,$groups)) $groups[] = $group;
            }
            $ret = array_merge($groups,$pages,$actions);
            $ret = array_unique($ret);
        }
        return $ret;
        //
        
        if (is_array($actions) && count($actions) && is_array($pages) && count($pages)) {        
            foreach($actions as $v) {
                $page = substr($v,0,5);
                $tmp[$page][] = $v;
            }
                        
            foreach($pages as $v) {                
                if (strlen($v)==5) {
                    $group = substr($v,0,3);
                    if (isset($this->actions[$group]['pages'][$v])) {
                        $count1 = count($this->actions[$group]['pages'][$v]['links']) 
                                + count($this->actions[$group]['pages'][$v]['tabs']) 
                                + count($this->actions[$group]['pages'][$v]['options']);
                    }
                    if (isset($this->actions[$group]['customs'][$v])) {
                        $count1 = count($this->actions[$group]['customs'][$v]['links']) 
                                + count($this->actions[$group]['customs'][$v]['tabs']) 
                                + count($this->actions[$group]['customs'][$v]['options']);                        
                    }
                    $count2 = count($tmp[$v]);
                    if ($count1 == $count2) $ret[] = $v;
                    else $ret = array_merge($ret,$tmp[$v]);
                    
                }                
            }
            foreach($pages as $v) {
                if ((strlen($v)==5) && !isset($tmp[$v]) && !in_array($v,$ret)) $ret[] = $v;
            }
        }
//        pr($actions);
//        pr($pages);
//        pr($ret);
//        die();
        return $ret;
    }
        
}

class CRole {
    
    function CRole() {
        
    }
    
    function save($values) {        
        if (isset($values['pmid']) && ($values['pmid']>0)) {
            $this->update($values);
        } else {
            $pmid = $this->add($values);
        }
        
        return $pmid;                    
    }
    
    function _default_clear($values) {
        global $base_roles;
        if ($values['default']) {
            $sql = "UPDATE permission_groups SET `default`='0' WHERE type='".$base_roles[$values['base_role']]."'";
            sql($sql);
        }
    }
    
    function add($values) {
        global $base_roles;
        
        CRole::_default_clear($values);
        $sql = "INSERT INTO permission_groups 
                (name,`default`,type,application) 
                VALUES 
                (".$GLOBALS['adodb']->Quote($values['role_name']).",'".$values['default']."','".$base_roles[strval($values['base_role'])]."','".APPLICATION_ROLE_ALIAS."')";
        sql($sql);
        $pmid = sqllast();
        if ($pmid) {
            foreach($values['actions'] as $v) {
                $sql = "INSERT INTO permission2act (pmid,acid,type)
                        VALUES ('".(int) $pmid."','".$v."','".$base_roles[$values['base_role']]."')";
                sql($sql);
            }
        }
        return $pmid;
    }
    
    function update($values) {
        global $base_roles;
        
        CRole::_default_clear($values);
        if (isset($values['pmid']) && ($values['pmid']>0)) {
            $sql = "UPDATE permission_groups 
                    SET 
                        name=".$GLOBALS['adodb']->Quote($values['role_name']).", 
                        type='".$base_roles[$values['base_role']]."',
                        `default`='".$values['default']."'
                    WHERE pmid='".(int) $values['pmid']."'";            
            sql($sql);
            $sql = "DELETE FROM permission2act WHERE pmid='".(int) $values['pmid']."'";
            sql($sql);
            foreach($values['actions'] as $v) {
                $sql = "INSERT INTO permission2act (pmid,acid,type)
                        VALUES ('".(int) $values['pmid']."','".$v."','".$base_roles[strval($values['base_role'])]."')";
                sql($sql);
            }        
        }
    }
        
    function get_info($id) {
        global $base_roles;
        if ($id>0) {
            $sql = "SELECT * FROM permission_groups WHERE pmid='".(int) $id."'";
            $res = sql($sql);
            $acts = new CActions();
            while($row = sqlget($res)) {
                $ret['pmid'] = $row['pmid'];
                $ret['base_role'] = array_search($row['type'],$base_roles);
                $ret['role_name'] = $row['name'];
                $ret['default'] = $row['default'];
                                
                $actions = $this->get_perms_by_role($id);
                foreach ($actions as $v) {
                    if (!empty($v)) {
                        $page = substr($v,0,5);
                        $ret['pages'][] = $page;
                        /*
                        if (strlen($v)==5) {
                            $actions_by_page = $acts->get_actions_by_pages(array($page));
                            if (is_array($actions_by_page[$page]['links']) && count($actions_by_page[$page]['links']))
                            foreach($actions_by_page[$page]['links'] as $v) {
                                $ret['actions'][] = $v['id'];
                            }
                            if (is_array($actions_by_page[$page]['tabs']) && count($actions_by_page[$page]['tabs']))
                            foreach($actions_by_page[$page]['tabs'] as $v) {
                                $ret['actions'][] = $v['id'];
                            }
                            if (is_array($actions_by_page[$page]['options']) && count($actions_by_page[$page]['options']))
                            foreach($actions_by_page[$page]['options'] as $v) {
                                $ret['actions'][] = $v['id'];
                            }
                        } else 
                        */
                        $ret['actions'][] = $v;
                    }
                }
                $ret['pages'] = array_unique($ret['pages']);
            }
            
        }
        return $ret;
    }
    
    function get_perms_by_role($pmid) {        
        if ($pmid>0) {
            $sql = "SELECT * FROM permission2act WHERE pmid='".(int) $pmid."'";
            $res = sql($sql);
            while($row = sqlget($res)) {                
                $ret[] = $row['acid'];                
            }            
        }        
        return $ret;
    }
    
    function _base2name($base) {
        global $profiles_basic_aliases;
    	$roles = $profiles_basic_aliases; //array('student' => _('Слушатель'), 'teacher' => _('Преподаватель'), 'dean' => _('Учебная администрация'), 'admin' => _('Администратор'));
    	if (isset($roles[$base])) {
    		return $roles[$base];
    	}
    	
    	return $base;
    }
    
    function get_all() {
        //$sql = "SELECT * FROM permission_groups WHERE `default`='0' ORDER BY name";
        $sql = "SELECT * FROM permission_groups ORDER BY name";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $row['perms'] = $this->get_perms_by_role($row['pmid']);
            $row['type'] = $this->_base2name($row['type']);
            $ret[] = $row;
        }
        return $ret;
    }
    
    function get_name($pmid) {
        $sql = "SELECT name FROM permission_groups WHERE pmid='".(int) $pmid."'";
        $res = sql($sql);
        if (sqlrows($res)) $row = sqlget($res);
        return $row['name'];
    }

    function get_type($pmid) {
        $sql = "SELECT type FROM permission_groups WHERE pmid='".(int) $pmid."'";
        $res = sql($sql);
        if (sqlrows($res)) $row = sqlget($res);
        return $row['type'];
    }
    
    function del($id) {        
        
        $sql = "DELETE FROM permission2act WHERE pmid='".(int) $id."'";
        sql($sql);
        
        $sql = "DELETE FROM permission2mid WHERE pmid='".(int) $id."'";
        sql($sql);
        
        //$sql = "DELETE FROM permission_groups WHERE `default`='0' AND pmid='".(int) $id."'";
        $sql = "DELETE FROM permission_groups WHERE pmid='".(int) $id."'";
        sql($sql);
        
    }
    
    function get_all_people() {        
        $sql = "SELECT * FROM People ORDER BY LastName,Login";
        $res = sql($sql);
        while($row = sqlget($res)) {            
            $ret[$row['MID']] = $row['Login']." ({$row['LastName']} {$row['FirstName']})";
        }        
        return $ret;
    }
    
    function get_people_by_pmid($pmid) {        
        $sql = "SELECT People.LastName as LastName, People.FirstName as FirstName, People.Login as Login, People.MID as MID 
                FROM People INNER JOIN permission2mid ON (People.MID=permission2mid.mid)
                WHERE permission2mid.pmid='".(int) $pmid."'
                ORDER BY People.LastName, People.FirstName, People.Login";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $ret[$row['MID']] = $row['Login']." ({$row['LastName']} {$row['FirstName']})";
        }        
        return $ret;        
    }
    
    function add_mid_to_role($mid,$pmid,$role_type='') {
        if ($mid>0) {
            if ($pmid>0) {
                $sql = "SELECT * FROM permission2mid WHERE pmid='".(int) $pmid."' AND mid='".(int) $mid."'";
                $res = sql($sql);
                if (!sqlrows($res)) {
                    $sql = "INSERT INTO permission2mid 
                            (pmid,mid) 
                            VALUES 
                            ('".(int) $pmid."','".(int) $mid."')";
                    sql($sql);
                    $ret = sqllast();
                }
            }
            CRole::_add_mid_to_baserole($mid,$pmid,$role_type);
        }
        return $ret;
    }
    
    function _add_mid_to_baserole($mid,$pmid,$role_type='') {
        if ($mid>0) {
            $type = $role_type;
            if ($pmid>0) {
                $type = CRole::get_type($pmid);
            }
            switch ($type) {
                case 'admin':
                    $sql = "SELECT MID FROM admins WHERE MID='".(int) $mid."'";
                    $res = sql($sql);
                    if (!sqlrows($res)) {
                        $sql = "INSERT INTO admins (MID) VALUES ('".(int) $mid."')";
                        sql($sql);
                        $ret = sqllast();
                    }
                break;
                case 'dean':
                    $sql = "SELECT MID FROM deans WHERE MID='".(int) $mid."'";
                    $res = sql($sql);
                    if (!sqlrows($res)) {
                        $sql = "INSERT INTO deans (MID) VALUES ('".(int) $mid."')";
                        sql($sql);
                        $ret = sqllast();
                    }
                break;
                case 'teacher':
                    $sql = "SELECT MID FROM Teachers WHERE MID='".(int) $mid."'";
                    $res = sql($sql);
                    if (!sqlrows($res)) {
                        $sql = "INSERT INTO Teachers (MID,CID) VALUES ('".(int) $mid."','0')";
                        sql($sql);
                        $ret = sqllast();
                    }
                break;
                case 'student':
                    $sql = "SELECT MID FROM Students WHERE MID='".(int) $mid."'";
                    $res = sql($sql);
                    if (!sqlrows($res)) {
                        $sql = "INSERT INTO Students (MID,CID) VALUES ('".(int) $mid."','0')";
                        sql($sql);
                        $ret = sqllast();
                    }
                break;
            }
        }
        return $ret;
    }

    function _del_mids_from_baserole($mids,$pmid) {
        if (is_array($mids) && count($mids) && ($pmid>0)) {
            $type = CRole::get_type($pmid);
            foreach($mids as $k=>$v) {
                if (count_user_roles_by_type($v,$type)>0) unset($mids[$k]);
            }
            switch ($type) {
                case 'admin':
                    /**
                    * Проверка на последнего админа
                    * Если последний мид из массива mids является последним админом
                    * то он игнорится
                    */
                    $sql = "SELECT * FROM admins WHERE MID NOT IN ('".join("','",$mids)."')";
                    $res = sql($sql);
                    if (!sqlrows($res)) $lucky = array_pop($mids);
                    
                    $sql = "DELETE FROM admins WHERE MID IN ('".join("','",$mids)."')";
                    $res = sql($sql);
                break;
                case 'dean':
                    $sql = "DELETE FROM deans WHERE MID IN ('".join("','",$mids)."')";
                    $res = sql($sql);
                break;
                case 'teacher':
                    $sql = "DELETE FROM Teachers WHERE MID IN ('".join("','",$mids)."')";
                    $res = sql($sql);
                break;
                case 'student':
                    $sql = "DELETE FROM Students WHERE MID IN ('".join("','", $mids)."')";
                    $res = sql($sql);
                break;
            }
        }
        return $ret;
    }
    
    /**
    * mids - array
    */
    function del_mids_from_role($mids,$pmid) {        
        if (is_array($mids) && count($mids) && ($pmid>0)) {            
            $sql = "SELECT mid FROM permission2mid 
                    WHERE pmid='".(int) $pmid."' AND mid IN ('".join("','",$mids)."')";
            $res = sql($sql);
            while($row = sqlget($res)) $rows[] = $row['mid'];
            
            $sql = "DELETE 
                    FROM permission2mid 
                    WHERE pmid='".(int) $pmid."' AND mid IN ('".join("','",$mids)."')";
            sql($sql);
            CRole::_del_mids_from_baserole($rows,$pmid);
        }        
    }
    
    /**
    * @desc Возвращает id роли по умолчанию для типа type
    * @return int id
    * @param string $type
    */
    function get_default_role($type="student") {
        global $base_roles;
        
        if (in_array($type,$base_roles)) {
            $sql = "SELECT pmid 
                    FROM permission_groups 
                    WHERE type='$type' AND `default`='1'";
            $res = sql($sql);
            if (sqlrows($res) && ($row = sqlget($res))) return $row['pmid'];
        }
    }
    
}

/**
* Класс для формирования руководства по роли
* информация в руководстве: пермишены, хелпы к пермишным
*/
class CRoleInfo {
    var $_id;
    var $_info;
    
    function init($id) {
        if ($id) {
            $this->_info['id'] = $this->_id = $id;
            $this->_info['name'] = CRole::get_name($this->_id);
            $this->_info['type'] = CRole::get_type($this->_id);
            $this->_info['perms'] = $this->_get_perms(); 
        }        
    }        
    
    function _get_all_help_files() {
        $ret = array();
        if (is_dir(DIR_HELP) && ($dh = opendir(DIR_HELP))) {
            while(($file = readdir($dh)) !== false) {
                if (is_file(DIR_HELP.'/'.$file))  {
                    $parts = explode('.',$file);
                    $parts = explode('-',$parts[0]);
                    $type = 'all';
                    if (in_array($parts[count($parts)-1],array('guest','student','teacher','dean','admin'))) $type = $parts[count($parts)-1];
                    $ret[$parts[0]][$type][] = DIR_HELP.'/'.$file;
                }
            }
            closedir($dh);
        }
        return $ret;
    }
        
    function _get_perms() {
        $perms = CRole::get_perms_by_role($this->_id);
        if (is_array($perms) && count($perms) && $GLOBALS['domxml_object']) {
            asort($perms);
            $helpfiles = $this->_get_all_help_files();
            $i=$ii=$iii=0;
            foreach($perms as $perm) {
				if ($element = $GLOBALS['domxml_object']->get_element_by_id($perm)) {
                    if (strlen($perm)>5) {
                        if ($element->tagname=='link') {
                            $mainPerm = substr($perm,0,5);
                            $ret[$mainPerm]['actions'][$perm] = iconv('UTF-8',$GLOBALS['controller']->lang_controller->lang_current->encoding,$element->get_attribute('name'));
                        }
                        continue;
                    }
                    
                    if (strlen($perm)<=3) {
                        $ret[$perm]['i'] = ++$i.".";
                        $ii=$iii=0;
                    }

                    if (strlen($perm)==5) {
                        $ret[$perm]['i'] = $i.".".++$ii.".";
                        $iii=0;
                        if ($element->tagname=='page') {
                            $ret[$perm]['url'] = $GLOBALS['sitepath'].$element->get_attribute('url')."?page_id={$perm}";
                        }
                    }

                    if (strlen($perm)>5) {
                        $ret[$perm]['i'] = $i.".".$ii.".".++$iii.".";
                    }
                    
                    $ret[$perm]['name'] = iconv('UTF-8',$GLOBALS['controller']->lang_controller->lang_current->encoding,$element->get_attribute('name'));                    
                    
                    $ret[$perm]['helpfiles'] = $helpfiles[$perm]['all'];
                    if (is_array($helpfiles[$perm][$this->_info['type']])) 
                        $ret[$perm]['helpfiles'] = $helpfiles[$perm][$this->_info['type']];                    
                    
                    if ($ret[$perm]['name'] == '%1') $ret[$perm]['name'] = 'Курс';
                    $ret[$perm]['name'] = str_replace('%1','',$ret[$perm]['name']);
                    
                    if (is_array($ret[$perm]['helpfiles']) && count($ret[$perm]['helpfiles']))
                        foreach($ret[$perm]['helpfiles'] as $hf) $ret[$perm]['files'][] = substr($hf,strrpos($hf,'/')+1);
                }
            }
        }
        return $ret;
    }
        
    function get() {
        return $this->_info;
    }
}

function array_trim(&$item) {
    $item = trim($item);
}
    
?>