<?php
class HM_Form_Element_MultiText extends Zend_Form_Element
{
    public $helper = 'multiText';

    public function init()
    {
        $this->setIsArray(true);
        parent::init();
    }
}
