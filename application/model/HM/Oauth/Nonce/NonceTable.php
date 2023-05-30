<?php

class HM_Oauth_Nonce_NonceTable extends HM_Db_Table
{
    protected $_name = "oauth_nonces";
    protected $_primary = "nonce_id";
    protected $_sequence = "S_42_1_OAUTH_NONCES";

  

    public function getDefaultOrder()
    {
        return array('oauth_nonces.nonce_id DESC');
    }
}