<?php

class HM_Subscription_Channel_ChannelTable extends HM_Db_Table
{
    protected $_name = "subscription_channels";
    protected $_primary = "channel_id";
    protected $_sequence = "S_106_1_SUBSCRIPTION_CHANNELS";

    /*protected $_dependentTables = array(
        "HM_Role_StudentTable",
        "HM_Course_Item_ItemTable",
        "HM_Module_Test_TestTable"
    );*/

    protected $_referenceMap = array(
        'Subscription' => array(
            'columns'       => 'channel_id',
            'refTableClass' => 'HM_Subscription_SubscriptionTable',
            'refColumns'    => 'channel_id',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'subscriptions' // имя свойства текущей модели куда будут записываться модели зависимости
        ),

    );

    public function getDefaultOrder()
    {
        return array('subscription_channels.channel_id ASC');
    }
}