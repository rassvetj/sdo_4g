<?php
class HM_Form_StudentCertificate  extends HM_Form
{
    public function init()
	{
		$user = $this->getService('User')->getCurrentUser();
		
		$types = HM_StudentCertificate_StudentCertificateModel::getTypes();
		#$types_comments = HM_StudentCertificate_StudentCertificateModel::getTypesComments();
		
		//$type_form_1 = HM_StudentCertificate_StudentCertificateModel::FORM_TYPE_ORDER;
		//$type_form_2 = HM_StudentCertificate_StudentCertificateModel::FORM_TYPE_SEND_DOC;
		
		
		
		$fio = $user->LastName.' '.$user->FirstName.' '.$user->Patronymic;
		$email = $user->EMail;
		
		$this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('order');
		
		/*
		$this->addElement('hidden', 'email', array(                        
			'Value' => $email,			
			'disabled' => 'disabled',
        ));
		*/
		
		$this->addElement('text', 'fio_c', array(
            'Label' => _('Ф.И.О.:'),
            'Required' => true,
			'Value' => $fio,					
			'Disabled' => 'disabled',
			'Readonly' => true,
        ));
		
		$validator = new Zend_Validate_EmailAddress();
		if ($validator->isValid($email)) {		
		//if($email &&  $email != ''){
			$this->addElement('text', 'email_c', array(                        
				'Label' => _('E-Mail:'),
				'Value' => $email,			
				'Required' => true,		
				'Validators' => array('EmailAddress'),								
				'Disabled' => 'disabled',
				'Readonly' => true,
			));
		} else {
			$this->addElement('text', 'email_c', array(                        
				'Label' => _('E-Mail:'),
				'Value' => '',			
				'Required' => true,	
				'Validators' => array('EmailAddress'),					
			));
		}
		
		
		$this->addElement('text', 'faculty_c', array(                        
				'Label' => _('Факультет:'),
				'Value' => '',			
				'Required' => true,					
		));
		
		$this->addElement('select','type', array(  //Ex: 1_3 - первый тип формы, 3 тип справки
			'label' => _('Вид справки/документа'),
			'value' => '1', 
			'multiOptions' => array(				
					'1' => $types[1],
					'2' => $types[2],
					'3' => $types[3],
					'13'=> $types[13],
					#'4' => $types[4],					
					'5' => $types[5],
					'6' => $types[6],
					'7' => $types[7],				
					'8' => $types[8],				
					'9' => $types[9],											
					#'14' => $types[14],
					'15' => $types[15],	
					
					#'16' => $types[16],					
					#'17' => $types[17],					
					#'18' => $types[18],					
					'19' => $types[19],					
					'21' => $types[21],					
			),
		));
		
		$this->addElement('select','count', array( 
			'label' => _('Количество'),
			'value' => '1', 
			'multiOptions' => array(
				'1' => _('1'),
				'2' => _('2'),
				'3' => _('3'),
				'4' => _('4'),
				'5' => _('5'),				
			),
		));
		
		$this->addElement('textarea', 'destination', array(            
			'Label' => _('Место требования справки'),
            'Required' => true,
			//'cols' => '12',
			'rows' => '5',				
        ));
		
		$this->addElement('textarea', 'place_work', array(            
			'Label' 	=> _('Место работы'),
            'Required' 	=> false,
			'rows' 		=> '5',				
        ));
		
		$this->addElement('text', 'period', array(            
			'Label' 	=> _('Период начисления стипендии'),
            'Required' 	=> false,
			'Value' 	=> '',
        ));


		$this->addElement('text', 'Postcode', 	array('Label' => _('Индекс:'), 					 'Value' => '', 'Required' => false, ));
		$this->addElement('text', 'city_c', 	array('Label' => _('Город/область/край:'), 		 'Value' => '', 'Required' => false, ));
		$this->addElement('text', 'street_c', 	array('Label' => _('Улица, дом, квартира:'),	 'Value' => '', 'Required' => false, ));
		$this->addElement('text', 'employer_c',	array('Label' => _('Наименование работодателя:'),'Value' => '', 'Required' => false, )); #при выборе типа справки: справка-вызов
		
		$this->addElement('DatePicker', 'date_from', array( #при выборе типа справки: справка-вызов
            'Label'			=> _('с'),
            'Required' 		=> false,
            'Validators' 	=> array(array('StringLength', false, array('min' => 10, 'max' => 50))),
            'Filters' 		=> array('StripTags'),
            'JQueryParams' 	=> array(
                'showOn' 		  => 'button',
                'buttonImage' 	  => "/images/icons/calendar.png",
                'buttonImageOnly' => 'true'
            ),
			'class'	=> 'date_picker'
        ));
		
		$this->addElement('DatePicker', 'date_to', array( #при выборе типа справки: справка-вызов
            'Label'			=> _('по'),
            'Required' 		=> false,
            'Validators' 	=> array(array('StringLength', false, array('min' => 10, 'max' => 50))),
            'Filters' 		=> array('StripTags'),
            'JQueryParams' 	=> array(
                'showOn' 		  => 'button',
                'buttonImage' 	  => "/images/icons/calendar.png",
                'buttonImageOnly' => 'true'
            ),
			'class'	=> 'date_picker'
        ));
		
		
		
		
		
		
		
		$this->addElement($this->getDefaultFileElementName(), 'file_c', array(            
			'Label' => _('Прикрепите документ'),			
			'Destination' => Zend_Registry::get('config')->path->upload->temp,
            'Required' => false,
            'Description' => _('Для загрузки использовать файлы форматов: jpg, jpeg, png, gif, pdf, doc, docx, xls, xlsx. Максимальный размер файла &ndash; 5 Mb'),
            'Filters' => array('StripTags'),
            'file_size_limit' => 5242880,
            'file_types' => '*.jpg;*.png;*.gif;*.jpeg, *.pdf,*.doc,*.docx,*.xls,*.xlsx',            
            'file_upload_limit' => 1,	
			'user_id' => 0,								
        ));	
		
		
		$directions = Zend_Registry::get('serviceContainer')->getService('StudentCertificate')->getDirectionList();
		#при выборе тип заявки копия лицензии и аккредитации
		$this->addElement('select', 'direction_c', array(
			'Label' 		=> _('Направление:'),
			'multiOptions' 	=> array('' => _('-- выберите --')) + $directions,
			'Required' 		=> false, 
		));
		
		#при выборе тип заявки копия лицензии и аккредитации
		$this->addElement('text', 'course_c', array(
			'Label' 	=> _('Курс:'),
			'Value'	 	=> '',
			'Required' 	=> false,
			'maxlength' => '2',
			'filters'	=> array('StringTrim'),
			'size' => '10',
			'validators'=>array(               
                array(
                    'validator'=>'Int',
                    'options'=>array(
                        'messages'=>'Введите число'
                    ),
                    'breakChainOnFailure'=>true
                )
			),
		)); 
		
		
		$this->addElement('text', 'document_series', 		array('Label' => _('Серия документа:'), 		'Value' => '', 'Required' => false, ));
		$this->addElement('text', 'document_number',		array('Label' => _('Номер документа:'), 		'Value' => '', 'Required' => false, ));
		$this->addElement('DatePicker', 'document_issue_date', array( 
            'Label'			=> _('Дата выдачи документа'),
            'Required' 		=> false,
            'Validators' 	=> array(array('StringLength', false, array('min' => 10, 'max' => 50))),
            'Filters' 		=> array('StripTags'),
            'JQueryParams' 	=> array(
                'showOn' 		  => 'button',
                'buttonImage' 	  => "/images/icons/calendar.png",
                'buttonImageOnly' => 'true'
            ),
			'class'	=> 'date_picker',
        ));
		$this->addElement('text', 'document_issue_by', 		array('Label' => _('Кем выдан документ:'), 		'Value' => '', 'Required' => false, ));
		$this->addElement('select', 'privilege_type', array(
			'Label' 		=> _('Вид льготы:'),
			'multiOptions' 	=> array('' => _('-- выберите --')) + HM_StudentCertificate_StudentCertificateModel::getPrivilegeTypeList(),
			'Required' 		=> false, 
		));
		   
		$this->addElement('DatePicker', 'privilege_date', array( 
            'Label'			=> _('Срок действия льготы'),
            'Required' 		=> false,
            'Validators' 	=> array(array('StringLength', false, array('min' => 10, 'max' => 50))),
            'Filters' 		=> array('StripTags'),
            'JQueryParams' 	=> array(
                'showOn' 		  => 'button',
                'buttonImage' 	  => "/images/icons/calendar.png",
                'buttonImageOnly' => 'true'
            ),
			'class'	=> 'date_picker',
			'Description' 		=> _('Дата окончания льготы указана в справке. Если бессрочно, оставьте поле незаполненным'),
        ));
		$this->addElement($this->getDefaultFileElementName(), 'document_file', array(            
			'Label' 			=> _('Документ основание'),			
			'Destination' 		=> Zend_Registry::get('config')->path->upload->temp,
            'Required' 			=> false,
            'Description' 		=> _('Для загрузки использовать файлы форматов: jpg, jpeg, png, gif, pdf, doc, docx. Максимальный размер файла &ndash; 5 Mb'),
            'Filters' 			=> array('StripTags'),
            'file_size_limit' 	=> 5242880,
            'file_types' 		=> '*.jpg;*.png;*.gif;*.jpeg, *.pdf,*.doc,*.docx',            
            'file_upload_limit' => 1,	
			'user_id' 			=> 0,								
        ));
		
		
		/*
		$this->addElement('text', 'year_c', array( #при выборе тип заявки копия лицензии и аккредитации
			'Label' 	=> _('Год:'),
			'Value' 	=> '',
			'Required' 	=> false,
			'maxlength'	=> '4',
			'filters'	=> array('StringTrim'),
			'size' => '10',
			'validators'=>array(               
                array(
                    'validator'=>'Int',
                    'options'=>array(
                        'messages'=>'Введите год'
                    ),
                    'breakChainOnFailure'=>true
                )
			),
		)); 
		*/
		$this->addElement('text', 'place_c',	array('Label' => _('Место представления:'),	'Value' => '', 'Required' => false, )); #при выборе тип заявки копия лицензии и аккредитации
		
		
		$this->addDisplayGroup(
            array(
                'Postcode',          
                'city_c',          
                'street_c',          
                'employer_c',
				'date_from',
				'date_to',				
				'file_c',
            ),
            'additional',
            array(
				#'class' => '',
				'legend' => _('Обязательны для студентов дистанционной формы обучения'),				
			)
        );
		
		$this->addDisplayGroup(
            array(
                'direction_c',
                'course_c',
                'year_c',
                'place_c',                       
            ),
            'additional_2',
            array(
				#'class' => '',
				#'legend' => _('Дополнительно'),
			)
        );
		
		/*		         
		$this->addElement($this->getDefaultFileElementName(), 'u_document', array(            
			'Label' => _('Прикрепите документ'),			
			'Destination' => Zend_Registry::get('config')->path->upload->temp,
            'Required' => true,
            'Description' => _('Для загрузки использовать файлы форматов: jpg, jpeg, png, gif, pdf, doc, docx, xls, xlsx. Максимальный размер файла &ndash; 10 Mb'),
            'Filters' => array('StripTags'),
            'file_size_limit' => 10485760,
            'file_types' => '*.jpg;*.png;*.gif;*.jpeg, *.pdf,*.doc,*.docx,*.xls,*.xlsx',
            //'file_types' => '*.jpg;*.png;*.gif;*.jpeg',
            'file_upload_limit' => 1,	
			'user_id' => 0,								
        ));	
		*/
		/*
		$photo = $this->getElement('u_document');
        $photo->addDecorator('UserImage')
                ->addValidator('FilesSize', true, array(
                        'max' => '10MB'
                    )
                )
                ->addValidator('Extension', true, 'jpg,png,gif,jpeg,pdf,doc,docx,xls,xlsx')
                //->addValidator('Extension', true, 'jpg,png,gif,jpeg')
                ->setMaxFileSize(10485760);
				
		
		
		$this->addDisplayGroup(
            array(
                'destination',          
            ),
            'destination_area',
            array(
				'class' => 'el-group-clear el-group-'.$type_form_1,				
			)
        );
		 
		$this->addDisplayGroup(
            array(
                'u_document',          
            ),
            'u_area',
            array(
				'class' => 'el-group-clear hidden el-group-'.$type_form_2,				
			)
        );
		*/
		
		 
		//$photo->class = 'text;
		//$photo = $this->getElement('destination');
		//$photo->setAttrib('class', 'text'):		
		//$photo = $this->getElement('u_document');
		//$photo->setAttribute('class', 'my333');
				
				
		
		$this->addElement('submit', 'submit', array(
            'Label' => _('Отправить'),
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