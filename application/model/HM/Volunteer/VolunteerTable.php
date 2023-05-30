<?php
class HM_Volunteer_VolunteerTable extends HM_Db_Table
{
    protected $_name = "volunteer_members";
    protected $_primary = "volunteer_member_id";
    protected $_sequence = "";

	
    protected $_referenceMap = array();

    public function getDefaultOrder()
    {
        return array('volunteer_members.volunteer_member_id ASC');
    }
}