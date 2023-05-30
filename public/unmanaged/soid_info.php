<?php

require_once("1.php");
require_once("metadata.lib.php");
require_once("positions.lib.php");

// !!! ВАЖНО $_REQUEST['soid'] - это soid_external

unset($controller->page_id);
if (!$s[login]) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");

$soidinfocontroller = new CSoidInfoController();
$soidinfocontroller->init();
$soidinfocontroller->execute();

class CSoidInfoController {
    var $view;
    var $model;
    
    function init() {
        $this->view = new CSoidInfoView();
        $this->view->title = _("Организационная единица");
    }
    function execute() {
        $this->model = new CSoidInfoModel();
        $this->model->init();
        $this->_set_soid();
        $this->model->execute();
        $this->view->display($this->model->position);
    }
    function _set_soid() {
        $this->model->soid = $_REQUEST['soid'];
    }
}

class CSoidInfoView {
    var $title;
    
    function display(&$arr) {
		$tpl = new Smarty_els();
        $tpl->assign_by_ref('this',$arr);
        $GLOBALS['controller']->setView('DocumentBlank');
        $GLOBALS['controller']->setHeader($this->title);        
        $GLOBALS['controller']->setContent($tpl->fetch('soid_info.tpl'));
        $GLOBALS['controller']->terminate();
    }
}

class CSoidInfoModel {    
    var $soid;
    var $position;
    
    function init() { 
    }
    
    function execute() {
        if ($this->soid) {
            $this->position = new CPosition();
            $this->position->init($this->_get_info());
        }
    }
    
    function _get_info() {
        $sql = "SELECT structure_of_organ.*, People.* FROM structure_of_organ 
                LEFT JOIN People ON (People.MID=structure_of_organ.mid)
                WHERE soid='".$this->soid."'";
        $res = sql($sql);
        if (sqlrows($res)) {
            $row = sqlget($res);
            if ($row['type']!=2)
            $row['code'] = get_code_recursive($row['soid']);
            return $row;
        }
    }
}

class CPosition {
    var $attributes;
    
    function init($arr) {
        $this->attributes = $arr;
        $this->_get_additional_info();
    }

    function _get_additional_info() {
        if ($this->attributes['type']==2) {
            $this->attributes['boss'] = $this->_get_boss();
            $this->attributes['boss']['photo'] = $this->_get_photo($this->attributes['boss']['mid']);
            $this->attributes['boss']['metadata'] = $this->_read_metadata($this->attributes['boss']['Information']);
        }
        $this->attributes['photo'] = $this->_get_photo($this->attributes['mid']);
        $this->attributes['metadata'] = $this->_read_metadata($this->attributes['Information']);
        $this->attributes['orgunit'] = $this->_get_orgunit();
    }
    
    function _read_metadata($metadata) {
        $info['Information'] = $metadata;
        $metadataTypes = explode(';',REGISTRATION_FORM);
        if (is_array($metadataTypes) && count($metadataTypes)) {
            foreach($metadataTypes as $metadataType) {

                $metadata = read_metadata (stripslashes($info['Information']), $metadataType);                
                $default_metadata = load_metadata($metadataType);
                $flow = '';
                if (is_array($metadata) && count($metadata)) {
                    foreach($metadata as $key => $value) {
                        if (($key == 0) && ($value['flow'] == 'line')) $flow = 'line';
                        if(is_array($value) && count($value)) {
                            if (isset($value['not_public']) && $value['not_public']) {
                            	continue;
                            }
                            if(trim($value['value']) != trim($default_metadata[$key]['value'])) {
                                if ($flow != 'line') {
                                    if (isset($value['title'])) {
                                        $ret[$value['title']] = $value['value'];
                                    } else {
                                        $ret['&nbsp;'] = $value['value'];
                                    }
                                } else {
                                    if (!isset($ret[get_reg_block_title($metadataType)])) {
                                        $ret[get_reg_block_title($metadataType)] = '';
                                    }
                                       
                                    $ret[get_reg_block_title($metadataType)] .= $value['value'].' ';
                                }
                            }
                            $flow = $value['flow'];
                        }
                    }
                }
                                
            }
        }
        return $ret;
    }
            
    function _get_boss() {  
        if ($this->attributes['soid']>0) {
            $sql = "SELECT structure_of_organ.*, People.* FROM structure_of_organ 
                    LEFT JOIN People ON (People.MID=structure_of_organ.mid)
                    WHERE owner_soid='".(int) $this->attributes['soid']."' AND type='1'";
            $res = sql($sql);
            if (sqlrows($res)) return sqlget($res);
        }
    }
    
    function _get_orgunit() {
        if ($this->attributes['owner_soid']>0) {
            $sql = "SELECT * FROM structure_of_organ WHERE soid='".(int) $this->attributes['owner_soid']."'";
            $res = sql($sql);
            if (sqlrows($res)) return sqlget($res);
        }
    }
    
    function _get_photo($mid) {
        if ($mid > 0) $photo = getPhoto($mid);
        if (empty($photo)) $photo = "<img src=\"{$GLOBALS['sitepath']}images/people/nophoto.gif\" alt=\""._("Нет фотографии")."\" border=0>";
        return $photo;
    }
}

?>