<?php
class HM_StudyCard_StudyCardTable extends HM_Db_Table
{
	//protected $_name = "UopInfoStud";	
	protected $_name = "study_cards";
	
    //protected $_primary = "UopInfoID";
    protected $_primary = "study_card_id";
	
    protected $_sequence = "";

    protected $_referenceMap = array(
        
    );

	
    public function getDefaultOrder()
    {
        //return array('UopInfoStud.UopInfoID ASC');
        //return array('study_cards.study_card_id ASC');
    }
	
}