<?php

class HM_Role_GuestTable extends HM_Db_Table
{
    protected $_name = "session_guest";
    protected $_primary = "session_guest_id";
    protected $_sequence = "S_100_SESSION_GUEST";

    public function getDefaultOrder()
    {
        return array('session_guest.session_guest_id');
    }
}