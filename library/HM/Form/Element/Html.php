<?php
class HM_Form_Element_Html extends Zend_Form_Element
{
    public $helper = 'formHtml';

    public function render()
    {
        return $this->_value;
    }
}