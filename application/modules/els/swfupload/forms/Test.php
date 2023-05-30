<?php
class HM_Form_Test extends HM_Form
{
	public function init()
	{
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setAttrib('enctype', 'multipart/form-data');
        $this->setName('test');
        
        $this->addElement($this->getDefaultFileElementName(), 'file1', array(
		    'Label' => '',
		    'Destination' => realpath(Zend_Registry::get('config')->path->upload->resource),
		    'Validators' => array(
		        array('Count', false, 1),
                array('Extension', false, 'zip')
		        //array('IsCompressed', false, 'zip')
		    )
		));

        $this->addElement($this->getDefaultFileElementName(), 'file2', array(
		    'Label' => '',
		    'Destination' => realpath(Zend_Registry::get('config')->path->upload->resource),
		    'Validators' => array(
		        array('Count', false, 1),
		        //array('Extension', false, 'zip'),
		        //array('IsCompressed', false, 'zip')
		    )
		));

		$this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
	}

}