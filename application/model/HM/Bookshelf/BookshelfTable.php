<?php
class HM_Bookshelf_BookshelfTable extends HM_Db_Table
{
    protected $_name = "bookshelf";
    protected $_primary = "bookshelf_id";
    protected $_sequence = "";

	//protected $_dependentTables = array(
      //  "HM_User_UserTable",      
    //);
	
    protected $_referenceMap = array(    	
    );

    public function getDefaultOrder()
    {
        return array('bookshelf.bookshelf_id ASC');
    }
}