<?php
class HM_Scale_ScaleModel extends HM_Model_Abstract
{
    const TYPE_CONTINUOUS = 1;
    const TYPE_BINARY = 2;
    const TYPE_TERNARY = 3;

    // @todo пока не используется
    const TYPE_DISCRETE = -1;

    public function getTypes()
    {
        return array(
            self::TYPE_CONTINUOUS => _('Значения от 0 до 100'),
            self::TYPE_BINARY => _('Фиксированный набор значений (2)'),
            self::TYPE_TERNARY => _('Фиксированный набор значений (3)'),
            self::TYPE_DISCRETE => _('Произвольный набор значений'),
        );
    }

    public function getBuiltInTypes()
    {
        return array(
            self::TYPE_BINARY,
            self::TYPE_TERNARY,
            self::TYPE_CONTINUOUS,
        );
    }

    public function getCustomTypes()
    {
        return array(
            self::TYPE_DISCRETE,
        );
    }

    static public function getRange($scaleId)
    {
        switch ($scaleId) {
        	case HM_Scale_ScaleModel::TYPE_CONTINUOUS:
        		return array(0, 100);
        	case HM_Scale_ScaleModel::TYPE_BINARY:
        		return array(0, 1);
        	case HM_Scale_ScaleModel::TYPE_TERNARY:
        		return array(0, 1);
        }
    }

    // see alse HM_Scale_Value_ValueConverter
    public function getValueId($value)
    {
        if (!count($this->scaleValues)) return false;
        foreach ($this->scaleValues as $scaleValue) {
            if ($scaleValue->value == $value) return $scaleValue->value_id;
        }
        return false;
    }

    // see alse HM_Scale_Value_ValueConverter
    public function getValueValue($scaleValueId)
    {
        if (!count($this->scaleValues)) return false;
        foreach ($this->scaleValues as $scaleValue) {
            if ($scaleValue->value_id == $scaleValueId) return $scaleValue->value;
        }
        return false;
    }

    public function getValueText($scaleValueId)
    {
        if (!count($this->scaleValues)) return false;
        foreach ($this->scaleValues as $scaleValue) {
            if ($scaleValue->value_id == $scaleValueId) return $scaleValue->text;
        }
        return false;
    }
}