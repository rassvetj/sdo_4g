<?php
class Object_Service {
    protected static $_instance;
    protected $_table;

    public function constructor() {
        
    }

    public function getTable() {
        return $this->_table;
    }
}
