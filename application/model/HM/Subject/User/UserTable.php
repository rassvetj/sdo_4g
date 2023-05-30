<?php

class HM_Subject_User_UserTable extends HM_Db_Table
{
    protected $_name = "Tutors_users";
    protected $_primary = array('TID');
    
    protected $_referenceMap = array(
    
    );
    public function getDefaultOrder()
    {
        return array('Tutors_users.TID ASC');
    }
	
}