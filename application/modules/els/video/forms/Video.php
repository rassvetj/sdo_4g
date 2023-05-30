<?php
/**
 * Форма для редактирования конфиг-файлов Skillsoft курсов
 *
 */
class HM_Form_Video extends HM_Form
{
    //public $status;

    public function init()
    {

        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('info');

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->baseUrl('video/list/')
        ));

        $this->addElement('hidden', 
        				  'nID', 
                          array('Required' => true,
                                'Validators' => array('Int'),
                                'Filters' => array('Int')));
        $this->addElement('text',
        				  'Title', 
                          array('Label' => _('Название'),
                                'Required' => true,
                                'Validators' => array(array('validator' => 'StringLength',
                                               			   'options' => array('max' => 255, 'min' => 3))),
                                'Filters' => array('StripTags')));

        $this->addElement('hidden', 'content', array('Required' => false, 'Value' => ''));

        $this->addElement('file', 'file', array(
                'Label' => _('Файл ресурса'),
                'Destination' => realpath(Zend_Registry::get('config')->path->upload->files),
                'validators' => array(
                    array('Count', false, 1),
                    //array('Extension', false, 'zip'),
                    //array('IsCompressed', false, 'zip')
                )
            ));

        $this->addElement('Submit', 'submit', array(
            'Label' => _('Сохранить')));

        $this->addDisplayGroup(array('cancelUrl',
                                    'Title',
                                    'file',
                                    'submit'),
            				   'resourceGroup',
                               array('legend' => ''));
        
        parent::init(); // required!
    }


}