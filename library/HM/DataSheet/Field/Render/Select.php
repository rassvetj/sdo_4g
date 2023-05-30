<?php
class HM_DataSheet_Field_Render_Select extends HM_DataSheet_Field_Render_Abstract
{
     public function render()
     {
         $html = sprintf('<select name="%s">', self::DEFAULT_FIELD_NAME.'_'.$this->getHorizontalId().'_'.$this->getVerticalId());

         $options = $this->getOptions();

         if (isset($options['values']) && is_array($options['values']) && count($options['values'])) {
             foreach($options['values'] as $value => $text) {
                 $selected = '';
                 if ($value == $this->getValue()) {
                     $selected = 'selected="selected"';
                 }

                 $html .= sprintf('<option %s value="%s"> %s</option>', $selected, $value, $text);
             }
         }

         $html .= "</select>";


         return $html;
     }
}