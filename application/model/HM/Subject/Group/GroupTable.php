<?php

class HM_Subject_Group_GroupTable extends HM_Db_Table
{
    protected $_name = "Tutors_groups";
    protected $_primary = array('TID');
    
    protected $_referenceMap = array(
    
    );
    public function getDefaultOrder()
    {
        return array('Tutors_groups.TID ASC');
    }
	
}