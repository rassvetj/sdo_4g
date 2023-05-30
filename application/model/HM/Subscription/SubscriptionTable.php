<?php

class HM_Subscription_SubscriptionTable extends HM_Db_Table
{
    protected $_name = "subscriptions";
    protected $_primary = "subscription_id";
    protected $_sequence = "S_106_1_SUBSCRIPTION";

    /*protected $_dependentTables = array(
        "HM_Role_StudentTable",
        "HM_Course_Item_ItemTable",
        "HM_Module_Test_TestTable"
    );*/

    protected $_referenceMap = array(
        'Channel' => array(
            'columns'       => 'channel_id',
            'refTableClass' => 'HM_Subscription_Channel_ChannelTable',
            'refColumns'    => 'channel_id',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'channels' // имя свойства текущей модели куда будут записываться модели зависимости
        ),

    );

    public function getDefaultOrder()
    {
        return array('subscription.subscription_id ASC');
    }
}