<?php

class HM_Oauth_Token_TokenTable extends HM_Db_Table
{
    protected $_name = "oauth_tokens";
    protected $_primary = "token_id";
    protected $_sequence = "S_42_1_OAUTH_TOKENS";

  

    public function getDefaultOrder()
    {
        return array('oauth_tokens.token_id DESC');
    }
}