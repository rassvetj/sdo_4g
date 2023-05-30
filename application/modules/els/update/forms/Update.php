<?php

class HM_Form_Update extends HM_Form
{
    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setAttrib('enctype', 'multipart/form-data');
        $this->setName('subjects');

        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(array('action' => 'index'))
            )
        );

        $this->addElement($this->getDefaultFileElementName(), 'file', array(
		    'Label' => _('Файл обновления (*.zip)'),
            'Required' => true,
		    'Destination' => realpath(Zend_Registry::get('config')->path->upload->temp),
		    'validators' => array(
		        array('Count', false, 1),
		        array('Extension', false, 'zip'),
		        //array('IsCompressed', false, 'zip')
		    ),
            'file_types' => '*.zip',
            'file_upload_limit' => 1
		));

        $this->addDisplayGroup(
            array(
                'file',
            ),
            'updatesGroup',
            array('legend' => _('Установка обновления'))
        );

        $this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));

        parent::init();
    }
}