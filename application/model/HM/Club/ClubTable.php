<?php
class HM_Club_ClubTable extends HM_Db_Table
{
    protected $_name = "Clubs";
    
    protected $_primary = "club_id";
    protected $_sequence = "";
	
    protected $_referenceMap = array(
    );

    public function getDefaultOrder()
    {
        return array('Clubs.club_id ASC');
    }
}