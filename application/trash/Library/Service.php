<?php
class Library_Service extends Object_Service 
{
    protected static $_instance;

    public function __construct() {
        $this->_table = new Library_Table();
    }

    /**
     * @return Library_Service
     */
    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function insert(HM_LibraryItem $item) {
    	$item->mid = HM_Identity::getUserId();
    	$item->is_active_version = 1;
        return $this->_table->insert($item->getAsArray());
    }
    
    public function update(HM_LibraryItem $item) {
    	return $this->_table->update($item->getAsArray(array('bid')), $this->_table->getAdapter()->quoteInto('bid = ?', $item->bid));
    }
    
    /**
     * @param int|array $itemId
     * @return HM_LibraryItem
     */
    public function get($itemId) 
    {
        if ($items = $this->_table->find($itemId)) {
            if ($items->count()) {
                return $items->current();
            }
        }
    }
    
}