<?php

class HM_Forum_Showed_ShowedTable extends HM_Db_Table
{
    protected $_name = 'forums_messages_showed';
    protected $_primary = array('user_id', 'message_id');
}