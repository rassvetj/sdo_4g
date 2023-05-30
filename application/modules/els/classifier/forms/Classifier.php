<?php
class HM_Form_Classifier extends HM_Form
{

    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        //$this->setAttrib('enctype', 'multipart/form-data');
        $this->setName('classifier');
        
        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(array('module' => 'classifier', 'controller' => 'list', 'action' => 'index', 'type' => $this->getParam('type', 0), 'parent' => $this->getParam('parent', 0)))
        ));
        
        $this->addElement('hidden', 'classifier_id', array(
            'Required' => true,
            'Validators' => array(
                'Int'),
            'Filters' => array(
                'Int')));

        $this->addElement('hidden', 'type', array(
            'Required' => true,
            'Validators' => array(
                'Int'),
            'Filters' => array(
                'Int')));
        
        $this->addElement('text', 'name', array(
            'Label' => _('Название'),
            'Required' => true,
            'Validators' => array(
                array(
                    'StringLength',
                    255,
                    1)),
            'Filters' => array(
                'StripTags'))

        );
        
        $this->addElement('text', 'name_translation', array(
            'Label' => _('Название').(' (en)'),
            'Required' => false,
            'Validators' => array(
                array(
                    'StringLength',
                    255,
                    1)),
            'Filters' => array(
                'StripTags'))

        );

        $this->addElement($this->getDefaultFileElementName(), 'icon', array(
                'Label' => _('Иконка'),
                'Destination' => Zend_Registry::get('config')->path->upload->temp,
                'Required' => false,
                'Description' => _('Для загрузки следует использовать картинки следующих типов: jpg,png,gif,jpeg. Максимальный размер загружаемого файла - 10 Мб.'),
                'Filters' => array('StripTags'),
                'file_size_limit' => 10485760,
                'file_types' => '*.jpg;*.png;*.gif;*.jpeg',
                'file_upload_limit' => 1,
                'classifierType' => null,
            )
        );

        $photo = $this->getElement('icon');
        $photo->addDecorator('ClassifierImage')
            ->addValidator('FilesSize', true, array(
                'max' => '10MB'
            )
        )
            ->addValidator('Extension', true, 'jpg,png,gif,jpeg')
            ->setMaxFileSize(10485760);


                        
        $this->addElement('Submit', 'submit', array(
            'Label' => _('Сохранить')));
        
        $this->addDisplayGroup(array(
            'cancelUrl',
            'classifier_id',
            'name',
			'name_translation',
            'icon',
            'submit'
            ), 
            'classifiers', array(
            'legend' => _('Рубрика классификатора')));

        parent::init(); // required!
    }

}