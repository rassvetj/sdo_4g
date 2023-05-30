<?php
class HM_Form_Scale extends HM_Form {

    public function init() {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('scales');

        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(array('action' => 'index'))
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

        $this->addElement('text', 'name', array(
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

        $this->addElement('textarea', 'description', array(
            'Label' => _('Описание'),
            'Required' => false,
            'Filters' => array('StripTags'),
            'class' => 'wide'
        ));

        $types = array_intersect_key(HM_Scale_ScaleModel::getTypes(), array_flip(HM_Scale_ScaleModel::getCustomTypes()));
        $this->addElement('select', 'type', array(
            'Label' => _('Тип'),
            'required' => true,
            'validators' => array(
                'int',
                array('GreaterThan', false, array('min' => -2, 'messages' => array(Zend_Validate_GreaterThan::NOT_GREATER => _("Необходимо выбрать значение из списка"))))
            ),
            'filters' => array('int'),
            'multiOptions' => $types,
        ));

//        $scaleValueDescriptions = array();
//        $scaleId = Zend_Registry::get('serviceContainer')->getService('Option')->getOption('competenceScaleId');
//        // @todo: надо отсортировать по 'ScaleValue.value'; в MSSQL не работает 3-й параметр
//        $scale = Zend_Registry::get('serviceContainer')->getService('Scale')->fetchAllDependenceJoinInner('ScaleValue', Zend_Registry::get('serviceContainer')->getService('Scale')->quoteInto('self.scale_id = ?', $scaleId))->current();
//
//        foreach ($scale->scaleValues as $value) {
//            $this->addElement('text', $scaleValueDescriptions[] = 'scale_value_' . $value->value_id, array(
//                'Label' => $value->value,
//                'Required' => false,
//            ));
//        }

        $this->addDisplayGroup(array(
            'cancelUrl',
            'name',
            'type',
            'description',
        ),
            'scales',
            array('legend' => _('Общие свойства'))
        );

//        if (count($scaleValueDescriptions)) {
//            $this->addDisplayGroup(
//                $scaleValueDescriptions,
//                'descriptions',
//                array('legend' => _('Описание уровней развития'))
//            );
//        }
//
        $this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
    }
}