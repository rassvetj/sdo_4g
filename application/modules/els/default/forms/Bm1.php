<?php
class HM_Form_BM1 extends HM_Form
{
    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('bm1');

        $this->addElement('div', 'login', array(
            'innerText' => 'text1'
        ));

        parent::init();
    }

}
