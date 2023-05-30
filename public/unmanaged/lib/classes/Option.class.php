<?php

$GLOBALS['table_options'] = 'OPTIONS'; // методы вызываются статически, приходится использовать глобальную переменную

// Должен заканчиваться слэшем
class COption {
    var $name;
    var $value;
    
    function init($name, $value) {
        $value = $GLOBALS['adodb']->Quote($value);
        $this->name = trim(strip_tags($name));
        $this->value = trim(strip_tags($value));
    }
    
    function _is_updated($exists_value) {
        if ($this->value!=$exists_value) return true;
        return false;
    }
    
    function _update_option() {
        $sql = "UPDATE " . $GLOBALS['table_options'] . "
                SET value=".$this->value." 
                WHERE name=".$GLOBALS['adodb']->Quote($this->name)."";

        $GLOBALS['options'][$this->name] = $this->value;

        return sql($sql);
    }
    
    function _insert_option() {
        $sql = "INSERT INTO " . $GLOBALS['table_options'] . "
                (`name`,`value`) 
                VALUES 
                (".$GLOBALS['adodb']->Quote($this->name).",".$this->value.")";
        sql($sql);

        $GLOBALS['options'][$this->name] = $this->value;

        return sqllast();
    }
    
    function save() {
        //if (!empty($this->name)) {
            $sql = "SELECT * FROM " . $GLOBALS['table_options'] . " WHERE name=".$GLOBALS['adodb']->Quote($this->name)."";
            $res = sql($sql);
            if (sqlrows($res)) {
                $row = sqlget($res);
                if ($this->_is_updated($row['value']))
                    $this->_update_option();
            } else {
                $this->_insert_option();
            }
        //}
    }
        
    function upload_file($name, $replace = true) {
    	$options_save_path = ($GLOBALS['table_options'] != 'OPTIONS') ? $GLOBALS['wwf'] . '/' . str_replace('options_', '', $GLOBALS['table_options']) . '/options/' : OPTION_FILES_REPOSITORY_PATH;
        if (isset($_FILES['options']['name'][$name]['file']) && 
            !empty($_FILES['options']['name'][$name]['file'])) {
            $old_filename = COption::get_value($name);
            if (!empty($old_filename)) {
	            if ($replace) {
	            	@unlink($options_save_path.$old_filename);
	            } elseif (file_exists($options_save_path.$old_filename)) {
		            $arr = pathinfo($_FILES['options']['name'][$name]['file']);
		            $_FILES['options']['name'][$name]['file'] = $arr['filename'] . time() . "." . $arr['extension'];
	            }
            }
            $dest_filename = to_translit($_FILES['options']['name'][$name]['file']);
            $dest_filepath = $options_save_path.$dest_filename;
            if (move_uploaded_file($_FILES['options']['tmp_name'][$name]['file'],$dest_filepath)) {
                makePreviewImage($dest_filepath, $dest_filepath, 230, 130);   
                return $dest_filename;
            }
        }
    }
    
    /**
    * @desc Сохраняет массив опций
    * @param array $options вида name[type] = value,
    * где type: text, file
    */
    function save_array($options) {
        if (is_array($_FILES['options']['name']) && count($_FILES['options']['name'])) {
            foreach($_FILES['options']['name'] as $k=>$v) $options[$k]['file'] = $v['file'];
        }
        if (is_array($options) && count($options)) {
            reset($options);
            while(list($k,$v) = each($options)) {
                if (isset($v['file']) && !empty($v['file'])) {
                	$replace = (!in_array($k, array('logo'))); //на надо убивать старый логотип - может пригодиться
                    $ret = COption::upload_file($k, $replace);
                    if (!empty($ret)) $v['text'] = $ret;
                }
                if (isset($v['text']) || isset($v['array']) || isset($v['int']) || isset($v['double'])) {
                    if (isset($v['text'])) {
                        $value = $v['text'];
                    }
                    if (isset($v['array'])) {
                        $value = serialize($v['array']);
                    }
                    if (isset($v['int'])) {
                        $value = (int) $v['int'];
                    }
                    if (isset($v['double'])) {
                        $value = (double) $v['double'];
                    }
                    
                    $option = new COption();
                    $option->init($k,$value);
                    $option->save();
                }
            }
        }
    }
    
    function get_value($name) {
        if (isset($GLOBALS['options']) && isset($GLOBALS['options'][$name])) {
            return $GLOBALS['options'][$name];
        }
        $sql = "SELECT * FROM " . $GLOBALS['table_options'] . " WHERE name=".$GLOBALS['adodb']->Quote($name)."";
        $res = sql($sql);
        if (sqlrows($res)) {
            $row = sqlget($res);
            $GLOBALS['options'][$name] = $row['value'];
            return $row['value'];
        }
    }
    
    function _process_defaults(&$options) {
        $defaults = array(
            'regform_email_required' => 1,
            'regform_items' => 'add_info',
            'drawboard_port' => '50012',
            'disable_copy_material' => 0,
            'question_edit_additional_rows' => 3,
            'transliterate' => 1,
            'import_ims_compatible' => 1,
            'course_organization_tree_view' => 0,
            'course_description_format' => 'simple', // simple,standart
            'answers_local_log_full' => 1,
        	'default_currency' => 'RUB',
            'headStructureUnitName' => _('Организационная структура'),
            'edo_subdivision_root_name' =>_('Учебная структура')
        );

        if (!is_array($options) && !count($options)) $option = array();
        
        foreach($defaults as $name=>$value) {
            if (!isset($options[$name])) {
                $options[$name] = $value;
            }
        }
        
        return $options;
    }
    
    function _process($name, $value) {        
        switch($name) {
            case 'regform_items':
                if (!empty($value)) {
                    $value = unserialize($value);
                    if (is_array($value) && count($value)) {
                        $value = join(';',$value);
                    }
                }
            break;
        }
        return $value;
    }
        
    function get_all_as_array($prefix='') {

        $options = array();
        $options['logo'] = $GLOBALS['options']['logo'] = '';

        if (!empty($prefix)) $sql_where = " WHERE name LIKE '{$prefix}_%' ";
        $sql = "SELECT name, value
                FROM " . $GLOBALS['table_options'] . " {$sql_where}";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $row['value'] = COption::_process($row['name'],$row['value']);
            $options[$row['name']] = $row['value'];
            $GLOBALS['options'][$row['name']] = $row['value'];
        }
                        
        return COption::_process_defaults($options);
    }                   
}

?>