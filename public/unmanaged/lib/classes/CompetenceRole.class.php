<?php

class CCompetenceRoleController {
    var $_view = false;
    var $_action;
    var $_step;
    var $_role_id;
    var $_form_data;

    function init() {
        $this->_prepare_variables();
        
        $view_class_name = "CCompetenceRoleView_{$this->_action}_step_{$this->_step}";
        if (!class_exists($view_class_name)) {
            $view_class_name = "CCompetenceRoleView_{$this->_action}";
        }
        if (!class_exists($view_class_name)) {
            $view_class_name = "CCompetenceRoleView_main";            
        }
        
        $this->_view = new $view_class_name;        
        $this->_view->init(&$this);
        
        $class_name = "CCompetenceRoleAction_{$this->_action}_step_{$this->_step}";
        if (!class_exists($class_name)) {
            $class_name = "CCompetenceRoleAction_{$this->_action}";
        }
        if (!class_exists($class_name)) {
            $class_name = "CCompetenceRoleAction_default";
        }
        $action = new $class_name;
        $action->init($this->_get_form_data());
                        
    } 
    
    function display() {
        $this->_view->assign('action',$this->_action);
        $this->_view->assign('step',$this->_step);
        $this->_view->assign('role_id',$this->_role_id);
        $this->_view->display();    
    }   
    
    function _prepare_variables() {
        $this->_action = trim(strip_tags($_REQUEST['action']));
        $this->_step = (int) $_REQUEST['step'];
        if (!$this->_step) {
            $this->_step = 1;
        }
        $this->_role_id = (int) $_REQUEST['role_id'];
        $this->_form_data = $_POST['data'];
    }    

    function _get_form_data() {
        return $this->_form_data;
    }
    
}

class CCompetenceRoleView_add_step_2 extends CCompetenceRoleView {
    var $_template = 'competence_roles_add_step_2.tpl';
    
    function init() {
        parent::init();
        $this->assign('competences',CCompetences::get_names($GLOBALS['s']['competence_roles']['competences']));
        $this->assign('values',$GLOBALS['s']['competence_roles']);
    }
}

class CCompetenceRoleView_add_step_1 extends CCompetenceRoleView {
    var $_template = 'competence_roles_add_step_1.tpl';
    
    function init() {
        parent::init();
        
        $all_competences = CCompetences::get_as_array_coid_name();
        $formulas = CFormula::get_as_array(6);

        if (isset($GLOBALS['s']['competence_roles'])) {
            $competences = CCompetences::get_names($GLOBALS['s']['competence_roles']['competences']);
            if (is_array($competences) && count($competences)) {
                $all_competences = array_diff($all_competences,$competences);
            }
            $this->assign('competences',$competences);
            $this->assign('values',$GLOBALS['s']['competence_roles']);
        }

        $this->assign('formulas',$formulas);
        $this->assign('all_competences', $all_competences);
   }
}

class CCompetenceRoleView_main extends CCompetenceRoleView {
    var $_template = 'competence_roles.tpl';
    
    function init() {
        parent::init();
        
        $roles = array();
        
        $pagerOptions = array(
          'mode'    => 'Sliding',
          'delta'   => 5,
          'perPage' => COMPETENCE_ROLES_PER_PAGE,
        );
        
        $sql = "SELECT id as id, name, formula FROM competence_roles ORDER BY name";
        
        if ($page = Pager_Wrapper_Adodb($GLOBALS['adodb'], $sql, $pagerOptions)) {
            while($row = sqlget($page['result'])) {
                $row['competences'] = CCompetences::get_names(CCompetenceRole::get_competences_id($row['id']));
                $roles[] = $row;
            }
        }
        
        $this->assign('links',$page['links']);
        $this->assign('roles',$roles);
    }
    
}

class CCompetenceRoleView {
    var $_smarty;
    var $_template;
    
    function init() {
        $this->_smarty = new Smarty_els();

        $this->_smarty->assign('SITEPATH',$GLOBALS['sitepath']);
        $this->_smarty->assign('OKBUTTON',okbutton());                
    }
    
    function display() {        
        $GLOBALS['controller']->captureFromReturn(CONTENT,$this->_smarty->fetch($this->_template));
        $GLOBALS['controller']->terminate();
    }    
    
    function assign($name, $value) {
        $this->_smarty->assign($name,$value);        
    }
}

class CCompetenceRoleAction_edit extends CCompetenceRoleAction {
    function init($data) {
        if ($_GET['id']) {
            $role = CCompetenceRole::get((int) $_GET['id']);
            if (is_array($role['competences']) && count($role['competences'])) {
                foreach($role['competences'] as $v) {
                    $competences[] = $v['competence'];
                    $thresholds[$v['competence']] = $v['threshold'];
                }
            }
            $role['competences'] = $competences;
            $role['thresholds'] = $thresholds;
            $GLOBALS['s']['competence_roles'] = $role;
            refresh('competence_roles.php?action=add');
            exit();            
        }
    }        
}

class CCompetenceRoleAction_delete extends CCompetenceRoleAction {
    function init($data) {
        if ($_GET['id']) {
            CCompetenceRole::delete((int) $_GET['id']);
            refresh('competence_roles.php');
            exit();            
        }
    }    
}

class CCompetenceRoleAction_add_step_2 extends CCompetenceRoleAction {
    function init($data) {
        if (!isset($GLOBALS['s']['competence_roles'])) {
            refresh('competence_roles.php?action=add');
            exit();
        }
        
        if (is_array($data) && count($data)) {
            $formParser = new CCompetenceRoleFormDataParser();
            $formParser->init($data);
            $data = $formParser->get_as_array();
            $GLOBALS['s']['competence_roles']['thresholds'] = $data;
            
            // add or edit
            $role = new CCompetenceRole();
            $role->init($GLOBALS['s']['competence_roles']);
            if ($GLOBALS['s']['competence_roles']['id']) {
                $role->update();
            } else {
                $role->create();
            }
                            
            refresh('competence_roles.php');
            exit();
                       
        }
    }
}

class CCompetenceRoleAction_add_step_1 extends CCompetenceRoleAction {
    function init($data) {
        if (is_array($data) && count($data)) {
            $formParser = new CCompetenceRoleFormDataParser();
            $formParser->init($data);
            $data = $formParser->get_as_array();
            if (isset($GLOBALS['s']['competence_roles'])) {
                if (isset($GLOBALS['s']['competence_roles']['id'])) {
                    $data['id'] = $GLOBALS['s']['competence_roles']['id'];
                }
                if (isset($GLOBALS['s']['competence_roles']['thresholds'])) {
                    $data['thresholds'] = $GLOBALS['s']['competence_roles']['thresholds'];
                }
            }
            $GLOBALS['s']['competence_roles'] = $data;
            
            if (empty($data['name'])) {
                $GLOBALS['controller']->setView('DocumentBlank');
                $GLOBALS['controller']->setMessage('Введите название роли',JS_GO_URL,'competence_roles.php?action=add');
                $GLOBALS['controller']->terminate();
                exit();
            }

            if (!count($data['competences'])) {
                $GLOBALS['controller']->setView('DocumentBlank');
                $GLOBALS['controller']->setMessage(_("Выберите необходимые компетенции"),JS_GO_URL,'competence_roles.php?action=add');
                $GLOBALS['controller']->terminate();
                exit();
            }
                                            
            refresh('competence_roles.php?action=add&step=2');
            exit();
                       
        }
    }
}

class CCompetenceRoleAction_default extends CCompetenceRoleAction {
    function init($dat) {
        unset($GLOBALS['s']['competence_roles']);
    }
}

class CCompetenceRoleAction {    
    function init($data) {
        // do something
    }
}

class CCompetenceRoleFormDataParser {
    var $_data;
    
    function init($array) {
        if (is_array($array) && count($array)) {
            foreach($array as $k=>$v) {
                $k = addslashes(trim(strip_tags($k)));
                if (isset($v['array'])) {
                    if (is_array($v['array']) && count($v['array'])) {
                        array_walk($v['array'],'strip_tags');
                        array_walk($v['array'],'trim');
                        $this->_data[$k]  = $v['array'];
                    }
                }
                if (isset($v['string']))  $this->_data[$k] = (string) nl2br(trim(strip_tags($v['string'])));
                if (isset($v['int']))     $this->_data[$k] = (int) $v['int'];                
                if (isset($v['integer'])) $this->_data[$k] = (int) $v['integer'];
                if (isset($v['double']))  $this->_data[$k] = (double) $v['double'];
            }
        }
    }
    
    function get_as_array() {
        return $this->_data;
    }
}

class CCompetenceRole {
    var $_attributes;
    
    function init($attributes=false) {
        $this->_attributes = $attributes;
    }    
    
    
    function _save_competences($id,$competences, $thresholds=array()) {
        if (is_array($competences) && count($competences)) {
            sql("DELETE FROM competence_roles_competences WHERE role='{$id}'");
            foreach ($competences as $v) {
                $sql = "INSERT INTO competence_roles_competences
                        (role,competence,threshold)
                        VALUES ('{$id}','{$v}',".$GLOBALS['adodb']->Quote($thresholds[$v]).")";
                sql($sql);                
            }
        }
    }
    
    function create() {
        if (!$this->_attributes['id']) {
            $data = $this->_attributes;
            unset($data['competences']);
            unset($data['thresholds']);
            foreach(array_keys($data) as $k) {
                $data[$k] = $GLOBALS['adodb']->Quote($data[$k]);
            }
            $sql = "INSERT INTO competence_roles
                    (".join(',',array_keys($data)).")
                    VALUES (".join(",",array_values($data)).")";
            sql($sql);            
            if ($id = sqllast()) {
                $this->_save_competences($id,$this->_attributes['competences'],$this->_attributes['thresholds']);
            }
        }
    }
    
    function update() {
        if ($this->_attributes['id']) {
            $id = $this->_attributes['id'];
            unset($this->_attributes['id']);
            $data = $this->_attributes;
            unset($data['thresholds']);
            unset($data['competences']);
            
            foreach($data as $k=>$v) {
                $_sql[] = "$k=".$GLOBALS['adodb']->Quote($v);
            }
            
            if (is_array($_sql) && count($_sql)) {            
                $sql = "UPDATE competence_roles SET ".join(',',$_sql)." WHERE id=".(int) $id;
                sql($sql);
            }
            $this->_save_competences($id,$this->_attributes['competences'],$this->_attributes['thresholds']);
        }        
    }
    
    function delete($id) {
        if ($id) {
            sql("DELETE FROM competence_roles_competences WHERE role='".(int) $id."'");
            sql("DELETE FROM competence_roles WHERE id=".(int) $id);
        }
    }
    
    function get_competences_id($role) {
        if ($role) {
            $sql = "SELECT * FROM competence_roles_competences WHERE role=".(int) $role;
            $res = sql($sql);
            while($row = sqlget($res)) {
                $ret[] = $row['competence'];
            }
            return $ret;
        }
    }
        
    function get_competences($role) {
        if ($role) {
            $sql = "SELECT * FROM competence_roles_competences WHERE role=".(int) $role;
            $res = sql($sql);
            while($row = sqlget($res)) {
                $ret[] = $row;
            }
            return $ret;
        }
    }    
    
    function get($id) {
        if ($id) {
            $sql = "SELECT * FROM competence_roles WHERE id=".(int) $id;
            $res = sql($sql);
            if (sqlrows($res) && ($row = sqlget($res))) {
                $row['competences'] = CCompetenceRole::get_competences($row['id']);
            }
            return $row;
        }
    }
    
    function getName($id) {
        if ($id) {
            $sql = "SELECT name FROM competence_roles WHERE id='".(int) $id."'";
            $res = sql($sql);
            
            while($row = sqlget($res)) {
                return $row['name'];
            }
        }
        return false;
    }
    
}

class CCompetenceRoles {
    
    function getCompetencesBySoid($soid) {
        $ret = array();
        if ($soid) {
            $roles = CPosition::get_roles($soid);
            if (is_array($roles) && count($roles)) {
                foreach($roles as $role)
                $sql = "SELECT * FROM competence_roles_competences WHERE role=".(int) $role['id'];
                $res = sql($sql);
                while($row = sqlget($res)) {
                    $ret['role'][$row['competence']] = $row;
                }
            }
        }
        return $ret;
    }
    
    function get_as_array_by_soid($soid,$without_competences=false) {
        if ($soid) {
            $sql = "SELECT competence_roles.* 
                    FROM structure_of_organ_roles
                    INNER JOIN competence_roles
                    ON (structure_of_organ_roles.role = competence_roles.id) 
                    WHERE structure_of_organ_roles.soid='".(int) $soid."'
                    ORDER BY name";
            $res = sql($sql);
            while($row = sqlget($res)) {
                if (!$without_competences) {
                    if (!isset($ret[$row['id']])) {
                        $row['competences'] = CCompetences::get_names(CCompetenceRole::get_competences_id($row['id']));
                    }
                }
                $ret[$row['id']] = $row;
            }
            return $ret;
        }
    }
    
    function get($id = array(), $without_competences = false) {
        if (count($id)) {
            $where = " WHERE id IN ('".join("','",$id)."') ";
        }
        $sql = "SELECT * FROM competence_roles $where ORDER BY name";
        $res = sql($sql);
        while($row = sqlget($res)) {
            if (!$without_competences) {
                $row['competences'] = CCompetences::get_names(CCompetenceRole::get_competences_id($row['id']));
                
                $competenceRole = new CCompetenceRole();
                $competenceRole->init($row);
                
                $ret[$row['id']] = $competenceRole;
            }
        }
        return $ret;
    }
    
    function get_as_array($without_competences=false) {
        $sql = "SELECT * FROM competence_roles ORDER BY name";
        $res = sql($sql);
        while($row = sqlget($res)) {
            if (!$without_competences) {
                $row['competences'] = CCompetences::get_names(CCompetenceRole::get_competences_id($row['id']));
            }
            $ret[$row['id']] = $row;
        }
        return $ret;
    }
    
}

?>