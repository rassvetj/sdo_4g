<?php
/**
* Отчет по обучаемым - сводные - группы
*/

require_once($sitepath.'lib/classes/xml2array.class.php');
require_once($sitepath.'lib/classes/Roles.class.php');

class CInstalledModulesReport extends CReportData {
    
    function getReportData() {

            $actions = new CActions();
            if (is_array($actions->actions) && count($actions->actions)) {
                
                foreach($actions->actions as $k=>$v) {
                    unset($row);
                    $row['name'] = $v['name'];
                    $row['type'] = _("модуль");                    
                    $row['url'] = $v['url'];
                    $row['profiles'] = $v['profiles'];
                    $this->_process_profiles($v['profiles'],$row);
                    $row['id'] = $v['id'];
                    $this->data[] = $row;
                    if (is_array($v['pages']) && count($v['pages'])) {
                        foreach($v['pages'] as $vv) {
                            $row['name'] = '..'.$vv['name'];
                            $row['type'] = _("страница");                    
                            $row['url'] = $vv['url'];
                            $row['profiles'] = $vv['profiles'];
                            $this->_process_profiles($vv['profiles'],$row);
                            $row['id'] = $vv['id'];
                            $this->data[] = $row;
                            if (is_array($vv['links']) && count($vv['links'])) {
                                foreach($vv['links'] as $vvv) {
                                $row['name'] = '....'.$vvv['name'];
                                $row['type'] = _("действие");                    
                                $row['url'] = $vvv['url'];
                                $row['profiles'] = $vvv['profiles'];
                                $this->_process_profiles($vvv['profiles'],$row);
                                $row['id'] = $vvv['id'];
                                $this->data[] = $row;
                                }
                            }
                            if (is_array($vv['tabs']) && count($vv['tabs'])) {
                                foreach($vv['tabs'] as $vvv) {
                                $row['name'] = '....'.$vvv['name'];
                                $row['type'] = _("вкладка");                    
                                $row['url'] = $vvv['url'];
                                $row['profiles'] = $vvv['profiles'];
                                $this->_process_profiles($vvv['profiles'],$row);
                                $row['id'] = $vvv['id'];                                    
                                $this->data[] = $row;
                                }
                            }
                            if (is_array($vv['options']) && count($vv['options'])) {
                                foreach($vv['options'] as $vvv) {
                                $row['name'] = '....'.$vvv['name'];
                                $row['type'] = _("настройка");                    
                                $row['url'] = $vvv['url'];
                                $row['profiles'] = $vvv['profiles'];
                                $this->_process_profiles($vvv['profiles'],$row);
                                $row['id'] = $vvv['id'];
                                $this->data[] = $row;
                                }
                            }
                        }                        
                    }

                    if (is_array($v['customs']) && count($v['customs'])) {
                        foreach($v['customs'] as $vv) {
                            $row['name'] = '..'.$vv['name'];
                            $row['type'] = 'custom';
                            $row['url'] = $vv['url'];
                            $row['profiles'] = $vv['profiles'];
                            $this->_process_profiles($vv['profiles'],$row);
                            $row['id'] = $vv['id'];
                            $this->data[] = $row;
                            if (is_array($vv['links']) && count($vv['links'])) {
                                foreach($vv['links'] as $vvv) {
                                $row['name'] = '....'.$vvv['name'];
                                $row['type'] = _("действие");                    
                                $row['url'] = $vvv['url'];
                                $row['profiles'] = $vvv['profiles'];
                                $this->_process_profiles($vvv['profiles'],$row);
                                $row['id'] = $vvv['id'];                                    
                                $this->data[] = $row;
                                }
                            }
                            if (is_array($vv['tabs']) && count($vv['tabs'])) {
                                foreach($vv['tabs'] as $vvv) {
                                $row['name'] = '....'.$vvv['name'];
                                $row['type'] = _("закладка");                    
                                $row['url'] = $vvv['url'];
                                $row['profiles'] = $vvv['profiles'];
                                $this->_process_profiles($vvv['profiles'],$row);
                                $row['id'] = $vvv['id'];
                                $this->data[] = $row;
                                }
                            }
                            if (is_array($vv['options']) && count($vv['options'])) {
                                foreach($vv['options'] as $vvv) {
                                $row['name'] = '....'.$vvv['name'];
                                $row['type'] = _("настройка");                    
                                $row['url'] = $vvv['url'];
                                $row['profiles'] = $vvv['profiles'];
                                $this->_process_profiles($vvv['profiles'],$row);
                                $row['id'] = $vvv['id'];
                                $this->data[] = $row;
                                }
                            }
                        }                        
                    }

                }
                
            }
                        
            // ОБЯЗАТЕЛЬНО!! ВЫПОЛНИТЬ ФУНКЦИЮ ПРЕДКА
            $this->data = parent::getReportData($this->data);
            
            return $this->data;
            
    }
    
    
    /**
    * Функция должна возвращать массив:
    */
    function getReportInputField($inputFieldName,$inputFieldData=false) {
        
        $ret = '';                        
        return $ret;
    }

    function _process_profiles($profiles, &$row) {
        $aProfiles = explode(',',$profiles);
        $row['profile_'.PROFILE_GUEST] = '';
        $row['profile_'.PROFILE_STUDENT] = '';
        $row['profile_'.PROFILE_TEACHER] = '';
        $row['profile_'.PROFILE_DEAN] = '';
        $row['profile_'.PROFILE_DEVELOPER] = '';
        $row['profile_'.PROFILE_MANAGER] = '';
        $row['profile_'.PROFILE_ADMIN] = '';
        if (is_array($aProfiles) && count($aProfiles)) {
            foreach($aProfiles as $profile) {
                switch(trim($profile)) {
                    case PROFILE_GUEST:
                        $row['profile_'.PROFILE_GUEST] = '+';
                    break;
                    case PROFILE_STUDENT:
                        $row['profile_'.PROFILE_STUDENT] = '+';
                    break;
                    case PROFILE_TEACHER:
                        $row['profile_'.PROFILE_TEACHER] = '+';
                    break;
                    case PROFILE_DEAN:
                        $row['profile_'.PROFILE_DEAN] = '+';
                    break;
                    case PROFILE_ADMIN:
                        $row['profile_'.PROFILE_ADMIN] = '+';
                    break;
                    case PROFILE_DEVELOPER:
                        $row['profile_'.PROFILE_DEVELOPER] = '+';
                    break;
                    case PROFILE_MANAGER:
                        $row['profile_'.PROFILE_MANAGER] = '+';
                    break;
                }
            }
        }
    }
    
}



?>