<?php

class HM_Chat_ChatChannelsTable extends HM_Db_Table
{
    protected $_name = "chat_channels";
    protected $_primary = "id";
    protected $_sequence = "S_ID_CHAT_CHANNELS";

    protected $_referenceMap = array(
        'ChatRefUsers' => array(
            'columns'       => 'id',
            'refTableClass' => 'HM_Chat_ChatRefUsersTable',
            'refColumns'    => 'channel_id',
            'propertyName'  => 'chatrefusers' // РёРјСЏ СЃРІРѕР№СЃС‚РІР° С‚РµРєСѓС‰РµР№ РјРѕРґРµР»Рё РєСѓРґР° Р±СѓРґСѓС‚ Р·Р°РїРёСЃС‹РІР°С‚СЊСЃСЏ РјРѕРґРµР»Рё Р·Р°РІРёСЃРёРјРѕСЃС‚Рё
        )
    );

    public function getDefaultOrder()
    {
        return array();
    }
}