<?php
class HM_Form_UploadFile extends HM_Form
{
	public function init()
	{
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setAttrib('enctype', 'multipart/form-data');
        $this->setName('upload');

        $this->addElement('hidden', 'webinar_id', array(
            'Required' => true
        ));

        $this->addElement('File', 'files', array(
                                                 'Label'      => _('Файлы'),
                                                 'Required'   => false,
                                                 'Filters'    => array('StripTags'),
                                                 'Destination' => Zend_Registry::get('config')->path->upload->temp,
                                                 'file_size_limit' => 10485760,
                                                 'file_types' => '*.jpg;*.png;*.gif;*.jpeg;*.svg;*.swf;*.flv',
                                                 // псевдо снятие лимита
                                                 'file_upload_limit' => 1000
                                           )
        );

        $this->getElement('files')->addValidator('Extension', true, 'jpg,png,gif,jpeg,bmp,svg,swf,flv');

        $this->addDisplayGroup(
            array(
                'files'
            ),
            'uploadFileGroup',
            array('legend' => _('Загрузить файл'))
        );

        $this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
	}
}