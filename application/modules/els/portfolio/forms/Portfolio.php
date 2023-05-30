<?php
class HM_Form_Portfolio extends HM_Form{

	public function init(){

        $this->setMethod(Zend_Form::METHOD_POST);
		$this->setAction($this->getView()->url(array('module' => 'portfolio', 'controller' => 'index', 'action' => 'upload')));
        $this->setName('portfolio');	
		
		
		$this->addElement(new HM_Form_Element_FcbkComplete('student_id', array(
                'required' => true,
                'Label' => _('Пользователь'),
				'Description' => _('Для поиска можно вводить любое сочетание букв из фамилии, имени и отчества'),
                'json_url' => $this->getView()->url(array('module' => 'user', 'controller' => 'ajax', 'action' => 'users-list'), null, true),
                'newel' => false,
                'maxitems' => 1,				
            )			
        ));
		
		
		
		$this->addElement($this->getDefaultFileElementName(), 'u_document', array(            
			'Label' => _('Выберите файл'),			
			'Destination' => Zend_Registry::get('config')->path->upload->temp,
            'Required' => true,
            'Description' => _('Для загрузки использовать файлы форматов: jpg, jpeg, png, gif, Максимальный размер файла &ndash; 10 Mb'),
            'Filters' => array('StripTags'),
            'file_size_limit' => 10485760,
            'file_types' => '*.jpg;*.png;*.gif;*.jpeg',            
            'file_upload_limit' => 12,	
			'user_id' => 0,								
        ));	
		
		$photo = $this->getElement('u_document');
        $photo->addDecorator('UserImage')
                ->addValidator('FilesSize', true, array(
                        'max' => '10MB'
                    )
                )
                ->addValidator('Extension', true, 'jpg,png,gif,jpeg')                
                ->setMaxFileSize(10485760);
		
		
		
        $this->addElement(
            'Submit',
            'submit',
            array(
				'Label' => _('Загрузить'),
				'order' => 99, //--чтобы кнопка была всегда последней				
            ));
			
			parent::init();

    }
	
	 public function getElementDecorators($alias, $first = 'ViewHelper') {
        if ($alias == 'u_document') {
            $decorators = parent::getElementDecorators($alias, 'UserImage');
            array_unshift($decorators, 'ViewHelper');
            return $decorators;
        }
        return parent::getElementDecorators($alias, $first);
    }

}