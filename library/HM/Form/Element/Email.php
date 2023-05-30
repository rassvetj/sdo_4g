<?php

class HM_Form_Element_Email extends Zend_Form_Element_Text {
    public function init() {
        $this->setLabel(_('E-mail'));
        $this->setAttrib('maxLenght', 80);
        $this->addValidator('EMailAddress');
        $this->addFilter('StringTrim');
    }
}