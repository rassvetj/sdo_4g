<?php
class HM_Metadata_Item_ItemModel extends HM_Model_Abstract
{
    const TYPE_STRING = 0;
    const TYPE_TEXT   = 1;
    const TYPE_SELECT = 2;
    const TYPE_DATE   = 3;
    const TYPE_FILE   = 4;
    const TYPE_RADIO  = 5;

    static public function getTypes()
    {
        return array(
            self::TYPE_STRING => _('Строка'),
            self::TYPE_TEXT   => _('Текст'),
            self::TYPE_SELECT => _('Список значений'),
            self::TYPE_RADIO  => _('Список значений (radio)'),
            self::TYPE_DATE   => _('Дата'),
            self::TYPE_FILE   => _('Файл')
        );
    }
}