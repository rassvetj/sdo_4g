<?php
class HM_Form_StudentSendDocument  extends HM_Form
{
    public function init()
	{
		$user 			= $this->getService('User')->getCurrentUser();
		
		$types 			= HM_StudentCertificate_StudentCertificateModel::getTypes();
		$types_comments = HM_StudentCertificate_StudentCertificateModel::getTypesComments();
		$validator 		= new Zend_Validate_EmailAddress();
		
		$fio 			= $user->LastName.' '.$user->FirstName.' '.$user->Patronymic;
		$email 			= $user->EMail;
		
		
		$this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('sdoc');
		
		$this->addElement('text', 'fio_d', array(
            'Label' 	=> _('Ф.И.О.:'),
            'Required' 	=> true,
			'Value' 	=> $fio,			
			'Disabled' 	=> 'disabled',
			'Readonly' 	=> true,
        ));
		
		if ($validator->isValid($email)) {		
			$this->addElement('text', 'email_d', array(                        
				'Label' 		=> _('E-Mail:'),
				'Value' 		=> $email,			
				'Required' 		=> true,	
				'Validators' 	=> array('EmailAddress'),												
				'Disabled' 		=> 'disabled',
				'Readonly' 		=> true,
			));
		} else {
			$this->addElement('text', 'email_d', array(                        
				'Label' 		=> _('E-Mail:'),
				'Value' 		=> '',			
				'Validators' 	=> array('EmailAddress'),				
				'Required' 		=> true,							
			));
		}
		
		$this->addElement('select','type', array(  //Ex: 1_3 - первый тип формы, 3 тип справки
			'label' 		=> _('Вид справки/документа'),
			'value' 		=> '10', 			
			'multiOptions' 	=> array(												
				10 => $types[10],				
				11 => $types[11],				
				12 => $types[12],
				
				HM_StudentCertificate_StudentCertificateModel::TYPE_MILITARY_DOC => $types[HM_StudentCertificate_StudentCertificateModel::TYPE_MILITARY_DOC],
				
				16 => $types[16],					
				17 => $types[17],					
				18 => $types[18],
			),
		));
		
		$this->addElement('textarea', 'destination', array(            
			'Label' => _('Комментарий'),
            'rows'	=> '5',				
        ));	
				         
		$this->addElement($this->getDefaultFileElementName(), 'u_document', array(            
			'Label' 			=> _('Прикрепите документ'),			
			'Destination' 		=> Zend_Registry::get('config')->path->upload->temp,
            'Required' 			=> true,
            'Description' 		=> _('Для загрузки использовать файлы форматов: jpg, jpeg, png, gif, pdf, doc, docx, xls, xlsx. Максимальный размер файла &ndash; 10 Mb'),
            'Filters' 			=> array('StripTags'),
            'file_size_limit' 	=> 10485760,
            'file_types' 		=> '*.jpg;*.png;*.gif;*.jpeg, *.pdf,*.doc,*.docx,*.xls,*.xlsx',            
            'file_upload_limit' => 1,	
			'user_id' 			=> 0,								
        ));	
		
		$photo = $this->getElement('u_document');
        $photo->addDecorator('UserImage')
                ->addValidator('FilesSize', true, array(
                        'max' => '10MB'
                ))
                ->addValidator('Extension', true, 'jpg,png,gif,jpeg,pdf,doc,docx,xls,xlsx')                
                ->setMaxFileSize(10485760);
				
				
				
		$this->addElement($this->getDefaultFileElementName(), 'u_photo', array(            
			'Label' 			=> _('Прикрепите фотографию'),			
			'Destination' 		=> Zend_Registry::get('config')->path->upload->temp,
            'Required' 			=> true,
            'Description' 		=> _('Для загрузки использовать файл в формате jpg. Максимальный размер файла &ndash; 500 Кb.'),
            'Filters' 			=> array('StripTags'),
            'file_size_limit' 	=> 512000,
            'file_types' 		=> '*.jpg',            
            'file_upload_limit' => 1,	
			'user_id' 			=> 0,								
        ));	
		
		$photo2 = $this->getElement('u_photo');
        $photo2->addDecorator('UserImage')
                ->addValidator('FilesSize', true, array(
                        'max' => '500KB',                        
                ))
                ->addValidator('Extension', true, 'jpg')                
                ->setMaxFileSize(512000);
		
		
		$this->addElement('text', 'd_faculty', array(                        
			'Label'		=> _('Факультет:'),
			'Value'		=> '',			
			'Required'	=> true,					
		));
		
		$this->addElement('text', 'd_document_series', array(
			'Label' 	=> _('Серия документа:'),
			'Value' 	=> '',
			'Required' 	=> false,
		));
		
		$this->addElement('text', 'd_document_number', array(
			'Label'			=> _('Номер документа:'),
			'Value' 		=> '',
			'Required'		=> true,
			'Description' 	=> _('Если у документа отсутствует номер, ставить б/н'),
		));
		
		$this->addElement('DatePicker', 'd_document_issue_date', array( 
            'Label'			=> _('Дата выдачи документа'),
            'Required' 		=> true,
            'Validators' 	=> array(array('StringLength', false, array('min' => 10, 'max' => 50))),
            'Filters' 		=> array('StripTags'),
            'JQueryParams' 	=> array(
                'showOn' 		  => 'button',
                'buttonImage' 	  => "/images/icons/calendar.png",
                'buttonImageOnly' => 'true'
            ),
			'class'	=> 'date_picker',
        ));
		
		$this->addElement('text', 'd_document_issue_by', array(
			'Label' 	=> _('Кем выдан документ:'),
			'Value' 	=> '',
			'Required' 	=> true, 
		));
		
		
		$this->addElement('select', 'd_privilege_type', array(
			'Label' 		=> _('Вид льготы:'),
			'multiOptions' 	=> array('' => _('-- выберите --')) + HM_StudentCertificate_StudentCertificateModel::getPrivilegeTypeList(),
			'Required' 		=> false, 
		));
		
		$this->addElement('text', 'd_portfolio_link', array(
			'Label' 	=> _('Ссылка на портфолио'),
			'Value' 	=> '',
			'Required' 	=> false, 
		));
		
		
		$this->addElement($this->getDefaultFileElementName(), 'd_document_file', array(            
			'Label' 			=> _('Документ основание'),			
			'Destination' 		=> Zend_Registry::get('config')->path->upload->temp,
            'Required' 			=> false,
            'Description' 		=> _('Для загрузки использовать файлы форматов: jpg, jpeg, png, gif, pdf, doc, docx. Максимальный размер файла &ndash; 5 Mb'),
            'Filters' 			=> array('StripTags'),
            'file_size_limit' 	=> 5242880,
            'file_types' 		=> '*.jpg;*.png;*.gif;*.jpeg, *.pdf,*.doc,*.docx',            
            'file_upload_limit' => 10,
			'user_id' 			=> 0,								
        ));
		
		
		
		$this->addDisplayGroup(
            array(
                'fio_d',
                'email_d',
                'type',
                'destination',
                'u_document',
                'u_photo',
            ),
            'd_main',
            array(
				#'class' => '',
				'legend' => _(''),				
			)
        );
		
		$this->addDisplayGroup(
            array(
                'd_faculty',              
                'd_document_series',              
                'd_document_number',              
                'd_document_issue_date',              
                'd_document_issue_by',              
                'd_privilege_type',              
                'd_portfolio_link',              
                'd_document_file',              
            ),
            'd_additional',
            array(
				#'class' => '',
				'legend' => _(''),				
			)
        );
		
		
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