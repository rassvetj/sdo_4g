<?php

class CFormParser {
    var $data;
    
    function CFormParser($array=false) {
        if ($array === false) $array = $_REQUEST['form']; 
        if (is_array($array) && count($array)) {
            foreach($array as $k=>$v) {
                $k = trim(strip_tags($k));
                if (isset($v['array'])) {
                    
                    if (isset($v['array']['int'])) {
                        if (is_array($v['array']['int']) && count($v['array']['int'])) {
                            array_walk($v['array']['int'],'intval');
                            $this->data[$k] = $v['array']['int'];
                        }
                    }
                    if (isset($v['array']['double'])) {
                        if (is_array($v['array']['string']) && count($v['array']['double'])) {
                            array_walk($v['array']['double'],'doubleval');                        
                            $this->data[$k] = $v['array']['double'];
                        }
                    }
                    if (isset($v['array']['html'])) {
                        if (is_array($v['array']['html']) && count($v['array']['html'])) {
                            array_walk($v['array']['html'],'trim');
                            $this->data[$k] = $v['array']['html'];                        
                        }
                    }
                    if (isset($v['array']['string'])) {
                        if (is_array($v['array']['string']) && count($v['array']['string'])) {
                            array_walk($v['array']['string'],'strip_tags');
                            array_walk($v['array']['string'],'trim');                        
                            $this->data[$k] = $v['array']['string'];
                        }
                    }
                    if (isset($v['array']['date'])) {
                        if (is_array($v['array']['date']) && count($v['array']['date'])) {
                            $date = mktime(
                                $v['array']['date']['Time_Hour'],
                                $v['array']['date']['Time_Minute'],
                                $v['array']['date']['Time_Second'],
                                $v['array']['date']['Date_Month'],
                                $v['array']['date']['Date_Day'],
                                $v['array']['date']['Date_Year']);
                            $this->data[$k] = $date;
                        }
                    }
                }
                if (isset($v['html']))   $this->data[$k] = (string) trim($v['html']);
                if (isset($v['string'])) $this->data[$k] = (string) nl2br(trim(strip_tags($v['string'])));
                if (isset($v['int']))    $this->data[$k] = (int) $v['int'];
                if (isset($v['double'])) $this->data[$k] = (double) $v['double'];            
            }
        }
    }
        
    
    function get() {
        return $this->data;
    }
}

?>