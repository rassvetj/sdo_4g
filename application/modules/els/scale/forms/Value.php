<?php
class HM_Form_Value extends HM_Form {

    public function init() {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('value');

        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(array('controller' => 'list', 'action' => 'index'))
            )
        );

        $this->addElement('hidden',
            'scale_id',
            array(
                'Required' => true,
                'Validators' => array('Int'),
                'Filters' => array('Int')
            )
        );

        $this->addElement('hidden',
            'value_id',
            array(
                'Required' => true,
                'Validators' => array('Int'),
                'Filters' => array('Int')
            )
        );

        $this->addElement('text', 'value', array(
            'Label' => _('Значение'),
            'Description' => _('Можно использовать отрицательные значения, они не учитывыаются в подсчете итоговых оценок'),
            'Required' => false,
            'Filters' => array('StripTags'),
        )
        );

        $this->addElement('text', 'text', array(
            'Label' => _('Текстовое значение'),
            'Required' => false,
            'Filters' => array('StripTags'),
        )
        );

        $this->addElement('textarea', 'description', array(
            'Label' => _('Описание'),
            'Required' => false,
            'Filters' => array('StripTags'),
        ));

        $this->addDisplayGroup(array(
            'cancelUrl',
            'value',
            'text',
            'description',
        ),
            'value_group',
            array('legend' => _('Общие свойства'))
        );

        $this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
    }
}