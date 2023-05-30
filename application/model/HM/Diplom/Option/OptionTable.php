<?php
class HM_Diplom_Option_OptionTable extends HM_Db_Table
{
    protected $_name = "diplom_options";
    protected $_primary = "option_id";


    public function getDefaultOrder()
    {
        return array('diplom_options.option_id ASC');
    }
}