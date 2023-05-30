<?php
class HM_RecordCard_RecordCardTable extends HM_Db_Table
{    
	//protected $_name = "RegCardStud";
	protected $_name = "record_cards";
    //protected $_primary = "RegCardID";
    protected $_primary = "record_card_id";
    protected $_sequence = "";

    protected $_referenceMap = array(
        
    );

    public function getDefaultOrder()
    {
        //return array('RegCardStud.RegCardID ASC');
        return array('record_cards.record_card_id ASC');
    }

}