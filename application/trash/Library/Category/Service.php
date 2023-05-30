<?php
class Library_Category_Service extends Object_Service 
{
    protected static $_instance;

    public function __construct() {
        $this->_table = new Library_Category_Table();
    }

    /**
     * @return Library_Category_Service
     */
    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function insert($data) 
    {
    	$category = $this->_table->createRow($data);
    	$category->save();
    	return $category->catid;
    }
    
    /**
     * @param int|array $itemId
     * @return HM_LibraryCategory
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