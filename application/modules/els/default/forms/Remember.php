<?php
class HM_Form_Remember extends HM_Form
{
    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('remember');

        $this->addElement('text', 'login', array(
            'Label' => _('Имя (Логин)'),
            'Required' => true,
            'Validators' => array(
                array('StringLength', 255, 3)
            ),
            'Filters' => array(
                'StripTags'
            )
        ));

        $this->addElement('Submit', 'submit', array('Label' => _('Восстановить')));

        $this->addDisplayGroup(
            array(
                'login',
                'submit'
            ),
            'rememberGroup',
            array('legend' => _('Восстановление пароля'))
        );
        parent::init();
    }
}