<?php

class CPollCourse extends CCourseAdaptor {
    
    function is_exists() {
        $sql = "SELECT CID FROM Courses WHERE is_poll='1'";
        $res = sql($sql);
        if (sqlrows($res)>0) {
            $row = sqlget($res);
            return $row['CID'];
        }
        return false;
    }
        
    function create() {
        if (!($cid = CPollCourse::is_exists())) {
            $cid = parent::create();
        } else {
            $this->_checkDates($cid);
        }
        return $cid;
    }
    
    function _checkDates($cid) {
        if ($cid) {
            $aUpdateAttributes = array();
            if ($info = $this->get($cid)) {
                if ($this->_aAttributes['cBegin']<$info['cBegin']) {
                    $aUpdateAttributes['cBegin'] = $this->_aAttributes['cBegin'];                    
                }

                if ($this->_aAttributes['cEnd']>$info['cEnd']) {
                    $aUpdateAttributes['cEnd'] = $this->_aAttributes['cEnd'];
                }
                
                if (count($aUpdateAttributes)) {
                    $this->_aAttributes = $aUpdateAttributes;
                    $this->update($cid);
                }
            }
        }
        return true;
    }
           
}

?>