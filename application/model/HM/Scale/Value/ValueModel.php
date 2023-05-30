<?php
class HM_Scale_Value_ValueModel extends HM_Model_Abstract
{
    const VALUE_NA = -1;

    const VALUE_BINARY_ON = 1;
    const VALUE_TERNARY_ON = 1;
    const VALUE_TERNARY_OFF = 0;

    static public function getTextStatus($scaleId, $value)
    {
        if (empty($scaleId)) {
            return $value;
        }

        if ($value == self::VALUE_NA) {
            return _("Не пройдено");
        }
        switch ($scaleId) {
            case HM_Scale_ScaleModel::TYPE_BINARY:
                if ($value == self::VALUE_BINARY_ON){
                    return _("Пройдено");
                }
            case HM_Scale_ScaleModel::TYPE_TERNARY:
                if ($value == self::VALUE_TERNARY_ON) {
                     return _("Пройдено успешно");
                } elseif ($value == 0) {
                    return _("Пройдено неуспешно");
                }
            default:
                return $value;
        }
    }
}
