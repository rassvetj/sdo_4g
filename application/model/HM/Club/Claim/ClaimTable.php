<?php
class HM_Club_Claim_ClaimTable extends HM_Db_Table
{
    protected $_name = "club_claims";
    protected $_primary = "claim_id";
    protected $_sequence = "";

    protected $_referenceMap = array(
        
    );

    public function getDefaultOrder()
    {
        return array('claim.claim_id ASC');
    }
}