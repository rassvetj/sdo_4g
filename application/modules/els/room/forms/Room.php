<?php
class HM_Form_Room extends HM_Form
{
	public function init()
	{
        $this->setMethod(Zend_Form::METHOD_POST);
        //$this->setAttrib('enctype', 'multipart/form-data');
        $this->setName('room');

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(array('action' => 'index'))
        ));

        $this->addElement('hidden', 'rid', array(
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

        $this->addElement('text', 'volume', array(
            'Label' => _('Количество мест'),
            'Required' => true,
            'Validators' => array(
                'Int',
                array('GreaterThan', false, array('min' => 0)) 
            ),
            'Filters' => array(
                'Int'
            )
        ));

        $this->addElement('select', 'type', array(
            'Label' => _('Тип'),
            'Required' => true,
            'MultiOptions' => HM_Room_RoomModel::getTypes(),
            'Validators' => array(
                'Int'
            ),
            'Filters' => array(
                'Int'
            )
        ));

        $this->addElement('select', 'status', array(
            'Label' => _('Статус'),
            'Required' => true,
            'MultiOptions' => HM_Room_RoomModel::getStatuses(),
            'Validators' => array(
                'Int'
            ),
            'Filters' => array(
                'Int'
            )
        ));

        $this->addElement('textarea', 'description', array(
            'Label' => _('Описание'),
            'rows' => 5,
            'Required' => false,
            'Validators' => array(
                array('StringLength', 4000, 0),
            ),
            'Filters' => array(
                'StripTags'
            )

        ));

		$this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));

        $this->addDisplayGroup(
            array(
                'cancelUrl',
                'rid',
                'name',
                'volume',
                'type',
                'status',
                'description',
                'submit'
            ),
            'projectGroup',
            array('legend' => _('Аудитория'))
        );

        parent::init(); // required!
	}

}