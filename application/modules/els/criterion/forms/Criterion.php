<?php
class HM_Form_Criterion extends HM_Form {

    public function init() {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('criterions');

        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(
                        array(
                            'action' => 'index',
                            'controller' => 'list',
                            'module' => 'criterion'
                        ), false, true)
            )
        );

        $this->addElement('text', 'title', array(
                'Label' => _('Название'),
                'Required' => true,
                'Validators' => array(
                    array('StringLength',
                        false,
                        array('min' => 1, 'max' => 255)
                    )
                ),
                'Filters' => array('StripTags'),
                'class' => 'wide'
            )
        );

        $this->addElement('hidden',
            'id',
            array(
                'Required' => true,
                'Validators' => array('Int'),
                'Filters' => array('Int')
            )
        );

        $this->addElement('textarea', 'description', array(
            'Label' => _('Описание'),
            'Required' => false,
            'Filters' => array('StripTags')
        ));

        $this->addDisplayGroup(array(
                'id',
                'title',
                'description',
            ),
            'criterions',
            array('legend' => _('Общие свойства'))
        );

        $this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
    }
}