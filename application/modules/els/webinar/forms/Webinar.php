<?php
class HM_Form_Webinar extends HM_Form
{
	public function init()
	{
        //$subjectId = $this->getParam('subject_id', 0);

        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setAttrib('enctype', 'multipart/form-data');
        $this->setName('webinar');

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(
                                             array('action' => 'index')
                       )
            )
        );
        
        $this->addElement('hidden', 'subject_id', array(
            'Required' => true,
            'Validators' => array('Int'),
            'Filters' => array('Int')
        ));

        $this->addElement('hidden', 'webinar_id', array(
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

        $this->addElement($this->getDefaultFileElementName(), 'files', array(
                                                 'Label'      => _('Файлы'),
												 'Description' => _('Допускаются для загрузки файлы форматов jpg, jpeg, png, gif, svg, swf, flv'),
                                                 'Required'   => false,
                                                 'Filters'    => array('StripTags'),
                                                 'Destination' => Zend_Registry::get('config')->path->upload->temp,
                                                 'file_size_limit' => 10485760,
                                                 'file_types' => '*.jpg;*.png;*.gif;*.jpeg;*.svg;*.swf;*.flv',
		                                         'Required' => true,
                                                 // псевдо снятие лимита
                                                 'file_upload_limit' => 1000
                                           )
        );
        $this->getElement('files')->addValidator('Extension', true, 'jpg,png,gif,jpeg,bmp,svg,swf,flv');
        

		$this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));

        $this->addDisplayGroup(
            array(
                'cancelUrl',
                'name',
                'files',
                'submit'
            ),
            'resourceGroup',
            array('legend' => _('Материалы вебинара'))
        );

        parent::init(); // required!
	}
}