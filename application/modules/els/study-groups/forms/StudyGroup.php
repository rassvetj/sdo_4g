<?php
class HM_Form_StudyGroup extends HM_Form
{
	public function init()
	{

        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('study-group');

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(array('action' => 'index'))
        ));

        $this->addElement('hidden', 'group_id', array(
            'Required' => true,
            'Validators' => array('Int'),
            'Filters' => array('Int')
        ));

        $this->addElement('text', 'name', array(
            'Label' => _('Название'),
            'Required' => true,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array(
                'StripTags'
            )
        ));

//        $this->addElement('RadioGroup', 'type', array(
//            'Value' => HM_StudyGroup_StudyGroupModel::TYPE_CUSTOM,
//            'Label' => '',
//            'MultiOptions' => HM_StudyGroup_StudyGroupModel::getTypes(),
//            'form' => $this,
//            'dependences' => array(
//                HM_StudyGroup_StudyGroupModel::TYPE_CUSTOM => array(),
//                HM_StudyGroup_StudyGroupModel::TYPE_AUTO => array('positions', 'departments')
//            )
//        ));
//
//        $positions = $this->getService('Orgstructure')->getPositionsCodes();
//
//        $this->addElement('UiMultiSelect', 'positions',
//            array(
//                'Label' => _('Должности'),
//                'Required' => false,
//                'multiOptions' => $positions,
//                'class' => 'multiselect'
//            )
//        );
//        $departments = array();
//        $collection = $this->getService('Orgstructure')->fetchAll(array(
//            'type = ?' => HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT
//        ), 'name');
//        if (count($collection)) {
//            $departments = $collection->getList('soid', 'name');
//        }
//        $this->addElement('UiMultiSelect', 'departments',
//            array(
//                'Label' => _('Подразделения'),
//                'Required' => false,
//                'Filters' => array(
//                    'Int'
//                ),
//                'multiOptions' => $departments,
//                'class' => 'multiselect'
//            )
//        );

        $this->addDisplayGroup(array(
                'cancelUrl',
                'group_id',
                'name'
            ),
            'studyGroup',
            array('legend' => _('Общие свойства'))
        );

//        $this->addDisplayGroup(
//            array(
//                'type',
//                'positions',
//                'departments'
//            ),
//            'studyGroup2',
//            array('legend' => _('Тип'))
//        );


        $this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));


        parent::init(); // required!
	}
}