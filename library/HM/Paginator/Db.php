<?php

class HM_Paginator_Db {
    
    protected $_page    = 1;
    protected $_perPage = null;
    protected $_total   = null;
    protected $_enabled = false;
    
    
    public function __construct($page, $perPage = null) {
        $this->_page    = $page;
        if ($perPage === null) {
            $this->_perPage = Zend_Registry::get('config')->dimensions->table_rows_per_page;
        } else {
            $this->_perPage = $perPage;                        
        }
    }
    
    public function fetchAll(Zend_Db_Table_Abstract $table, Zend_Db_Select $select) {
        $this->_total = count($table->fetchAll($select));
        if ($this->_perPage < $this->_total) {
            $select->limitPage($this->_page, $this->_perPage);
            $this->_enabled = true;
        }
        
        return $table->fetchAll($select);
    }
    
    public function isEnabled() {
        return $this->_enabled;
    }
    
    public function getPage() {
        return $this->_page;       
    }
    
    public function getPerPage() {
        return $this->_perPage;
    }
    
    public function getTotal() {
        return $this->_total;
    }
}