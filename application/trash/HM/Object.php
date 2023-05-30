<?php
class HM_Object extends Zend_Db_Table_Row_Abstract {
//    protected $_attrs;

/*    public function __construct($attrs = array()) {
        if (is_array($attrs) && count($attrs)) {
            foreach($attrs as $name=>$value) {
                $this->_attrs[$name] = $value;
            }
        }
    }

    public function __set($name, $value) {
        $this->_attrs[$name] = $value;
    }

    public function __get($name) {
        if (isset($this->_attrs[$name])) {
            return $this->_attrs[$name];
        }
    }
*/
    public function getAsArray($exclude = null) {
    	$attrs = $this->_data;
    	if (is_array($exclude)) {
    		foreach($exclude as $item) {
    			if (isset($attrs[$item])) {
    			    unset($attrs[$item]);
    			}
    		}
    	}
        return $attrs;
    }

/*    public function kill($name) {
        unset($this->_data[$name]);
    }*/

}
