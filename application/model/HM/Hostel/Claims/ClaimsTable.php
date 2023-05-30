<?php

class HM_Hostel_Claims_ClaimsTable extends HM_Db_Table

{
    protected $_name = "hostel_claims";
    protected $_primary = "claim_id";
    protected $_sequence = "";

    protected $_referenceMap = array(    
    );

    public function getDefaultOrder()
    {
        return array('hostel_claims.claim_id ASC');
    }
}