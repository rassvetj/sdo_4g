<?php
class HM_Form_Element_AssociativeSelect extends Zend_Form_Element
{
    public $helper = 'associativeSelect';

    public function init()
    {
        $this->setIsArray(true);
        parent::init();
    }
}
