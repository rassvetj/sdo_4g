<?php
class HM_DataSheet_Field_Render_Checkbox extends HM_DataSheet_Field_Render_Abstract
{
    public function render()
    {
        $checked = '';
        if ($this->getValue() > 0) {
            $checked = 'checked';
        }

        return sprintf('<input type="checkbox" name="%s" value="1" %s/>', self::DEFAULT_FIELD_NAME.'_'.$this->getHorizontalId().'_'.$this->getVerticalId(),$checked);
    }
}