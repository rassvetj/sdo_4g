<?php
class HM_DataSheet_Field_Render_Text extends HM_DataSheet_Field_Render_Abstract
{
    public function render()
    {

        $options = $this->getOptions();
        $pattern = '.*';
        if (isset($options['pattern'])) {
            $pattern = $options['pattern'];
        }

        return sprintf('
            <input
            type="text"
            id="%s"
            name="%s"
            value="%s"
            pattern="%s">',
            self::DEFAULT_FIELD_NAME.'_'.$this->getHorizontalId().'_'.$this->getVerticalId(),
            self::DEFAULT_FIELD_NAME.'_'.$this->getHorizontalId().'_'.$this->getVerticalId(),
            $this->getValue(),
            $pattern
        );
    }
}