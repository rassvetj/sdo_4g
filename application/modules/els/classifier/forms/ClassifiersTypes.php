<?php
class HM_Form_ClassifiersTypes extends HM_Form
{

    public function init()
    {
        $model = new HM_Classifier_Type_TypeModel(null);        
        
        $this->setMethod(Zend_Form::METHOD_POST);
        //$this->setAttrib('enctype', 'multipart/form-data');
        $this->setName('classifiersTypes');
        
        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(array('module' => 'classifier', 'controller' => 'list-types', 'action' => 'index'))
        ));
        
        $this->addElement('hidden', 'type_id', array(
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
        
        $this->addElement('multiCheckbox', 'link_types', array(
            'Label' => _('Области применения'),
            'Required' => false,
            'MultiOptions' => HM_Classifier_Link_LinkModel::getTypes(),
            'Filters' => array(
                'StripTags'
            )
        ));


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
        
        $this->addDisplayGroup(
            array(
                'cancelUrl',
                'type_id',
                'name',
                'link_types',
                'icon',
                'submit'
            ),
            'classifiersTypes', array(
            'legend' => _('Классификатор')));

        parent::init(); // required!
    }


    public function getElementDecorators($alias, $first = 'ViewHelper') {
        if ($alias == 'icon') {
            $decorators = parent::getElementDecorators($alias, 'ClassifierImage');
            array_unshift($decorators, 'ViewHelper');
            return $decorators;
        }
        return parent::getElementDecorators($alias, $first);
    }


}