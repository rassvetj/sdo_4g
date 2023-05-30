<?php
class HM_Form_Upload extends HM_Form
{
	public function init()
	{
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('upload');
        //$this->setAction($this->getView()->url(array('module' => 'order', 'controller' => 'list', 'action' => 'index', 'subject_id' => $this->getParam('subject_id', 0))));

        $this->addElement('hidden', 'cancelUrl', array(
            'required' => false,
            'value' => $this->getView()->url(array('controller' => 'list', 'action' => 'index'))
        ));

        $this->addElement($this->getDefaultFileElementName(), 'file', array(
            'Label' => _('Файл данных'),
            'Destination' => Zend_Registry::get('config')->path->upload->temp,
            'Validators' => array(
                array('Count', false, 1),
//                array('Extension', false, 'csv')
            ),
            'file_size_limit' => 0,
            //'file_types' => '*.csv',
            'file_upload_limit' => 1,
            'Required' => true
        ));

        //$this->getElement('message')->addFilter(new HM_Filter_Utf8());

        $this->addDisplayGroup(
            array(
                'cancelUrl',
                'file'
            ),
            'znobGroup',
            array('legend' => _('Загрузить файл'))
        );

        $this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
	}

}