<?php

class HM_Oauth_App_AppTable extends HM_Db_Table
{
    protected $_name = "oauth_apps";
    protected $_primary = "app_id";
    protected $_sequence = "S_42_1_OAUTH_APPS";

  

    public function getDefaultOrder()
    {
        return array('oauth_apps.app_id DESC');
    }
}