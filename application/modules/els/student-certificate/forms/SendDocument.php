<?php
class HM_Form_SendDocument  extends HM_Form
{
	protected $id = 'send_document';
	
    public function init()
	{
		$user           = $this->getService('User')->getCurrentUser();		
		$types          = HM_StudentCertificate_StudentCertificateModel::getTypes();		
		$types_comments = HM_StudentCertificate_StudentCertificateModel::getTypesComments();
		$validator 		= new Zend_Validate_EmailAddress();		
		$fio            = $user->getName();
		$email          = $user->EMail;		
		
		$this->setMethod(Zend_Form::METHOD_POST);
        $this->setName($this->id);
		
		$this->setAction(
            $this->getView()->url(
                array(
                    'module'     => 'student-certificate',
                    'controller' => 'send-document',
                    'action'     => 'send',
                )
            )
        );
		
		$this->addElement('text', 'fio_d', array(
            'Label'    => _('Ф.И.О.:'),
            'Required' => true,
			'Value'    => $fio,			
			'class'    => 'input-disabled fio_d',
			'Readonly' => true,
        ));
		
		if ($validator->isValid($email)) {		
			$this->addElement('text', 'email_d', array(                        
				'Label' 	 => _('E-Mail:'),
				'Value' 	 => $email,			
				'Required'   => true,	
				'Validators' => array('EmailAddress'),												
				'class'      => 'input-disabled fio_d',
				'Readonly' 	 => true,
			));
		} else {
			$this->addElement('text', 'email_d', array(                        
				'Label' 		=> _('E-Mail:'),
				'Value' 		=> '',			
				'Validators' 	=> array('EmailAddress'),				
				'Required' 		=> true,
				'class'         => 'email_d',
			));
		}
		
		$this->addElement('select','type', array(  //Ex: 1_3 - первый тип формы, 3 тип справки
			'label' 		=> _('Вид справки/документа'),
			'value' 		=> HM_StudentCertificate_StudentCertificateModel::TYPE_SNILS, 			
			'multiOptions' 	=> HM_StudentCertificate_StudentCertificateModel::getSendDocTypes(),
		));
		
		$this->addElement('textarea', 'destination', array(            
			'Label' => _('Комментарий'),
            'rows'	=> '5',
			'class'         => 'destination',			
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
			'class'             => 'u_document',
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
			'class'             => 'u_photo',			
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
			'class'         => 'd_faculty',			
		));
		
		$this->addElement('text', 'd_document_series', array(
			'Label' 	=> _('Серия документа:'),
			'Value' 	=> '',
			'Required' 	=> false,
			'class'         => 'd_document_series',
		));
		
		$this->addElement('text', 'd_document_number', array(
			'Label'			=> _('Номер документа:'),
			'Value' 		=> '',
			'Required'		=> true,
			'Description' 	=> _('Если у документа отсутствует номер, ставить б/н'),
			'class'         => 'd_document_number',
		));
		
		$this->addElement('DatePicker', 'd_document_issue_date', array( 
            'Label'			=> _('Дата выдачи документа'),
            'Required' 		=> true,
            'Validators' 	=> array(array('StringLength', false, array('min' => 10, 'max' => 50))),
            'Filters' 		=> array('StripTags'),
            'class'	        => 'date_picker',
			'JQueryParams' 	=> array(
                'showOn' 		  => 'button',
                'buttonImage' 	  => "/images/icons/calendar.png",
                'buttonImageOnly' => 'true'
            ),
			'class'         => 'd_document_issue_date',
			
        ));
		
		$this->addElement('text', 'd_document_issue_by', array(
			'Label' 	=> _('Кем выдан документ:'),
			'Value' 	=> '',
			'Required' 	=> true, 
			'class'         => 'd_document_issue_by',
		));		
		
		$this->addElement('select', 'd_privilege_type', array(
			'Label' 		=> _('Вид льготы:'),
			'multiOptions' 	=> array('' => _('-- выберите --')) + HM_StudentCertificate_StudentCertificateModel::getPrivilegeTypeList(),
			'Required' 		=> false,
			'class'         => 'd_privilege_type',			
		));
		
		$this->addElement('text', 'd_portfolio_link', array(
			'Label' 	=> _('Ссылка на портфолио'),
			'Value' 	=> '',
			'Required' 	=> false, 
			'class'     => 'd_portfolio_link',
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
			'class'             => 'd_document_file',			
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
				'legend' => _(''),	
				'class'         => 'fio_d email_d type destination u_document u_photo',
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
				'legend' => _(''),
				'class'         => 'd_faculty d_document_series d_document_number d_document_issue_date d_document_issue_by d_privilege_type d_portfolio_link d_document_file',				
			)
        );
		
		
		$this->addElement('submit', 'submit', array(
            'Label' => _('Отправить'),
        ));
		
		parent::init();
	}
	
	public function change($type)
	{
		if($type == HM_StudentCertificate_StudentCertificateModel::TYPE_PHOTO){
			$this->getElement('u_photo'                 )->setOptions(array('Required' => true,));
			
			$this->getElement('u_document'              )->setOptions(array('Required' => false,));			
			$this->getElement('d_document_series'		)->setOptions(array('Required' => false));
			$this->getElement('d_document_number'		)->setOptions(array('Required' => false));
			$this->getElement('d_document_issue_date'	)->setOptions(array('Required' => false));
			$this->getElement('d_document_issue_by'		)->setOptions(array('Required' => false));
			$this->getElement('d_privilege_type'		)->setOptions(array('Required' => false));
			$this->getElement('d_faculty'				)->setOptions(array('Required' => false));
			return true;
		}
		
		$this->getElement('u_document')->setOptions(array('Required' => true,));
		
		$this->getElement('u_photo')->setOptions(array('Required' => false,));
		
		if(in_array($type, array(
			HM_StudentCertificate_StudentCertificateModel::TYPE_GRANT_STATE_SOCIAL,
			HM_StudentCertificate_StudentCertificateModel::TYPE_GRANT_STATE_SOCIAL_INCREASED, 
			HM_StudentCertificate_StudentCertificateModel::TYPE_GRANT_STATE_ACADEMIC_INCREASED, 
			HM_StudentCertificate_StudentCertificateModel::TYPE_MATERIAL_HELP
		))){
			$this->getElement('d_document_series'		)->setOptions(array('Required' => true));
			$this->getElement('d_document_number'		)->setOptions(array('Required' => true));
			$this->getElement('d_document_issue_date'	)->setOptions(array('Required' => true));
			$this->getElement('d_document_issue_by'		)->setOptions(array('Required' => true));
			$this->getElement('d_privilege_type'		)->setOptions(array('Required' => true));
			$this->getElement('d_portfolio_link'		)->setOptions(array('Required' => true));
			$this->getElement('d_faculty'				)->setOptions(array('Required' => true));
			return true;
		} 
		
		$this->getElement('d_document_series'		)->setOptions(array('Required' => false));
		$this->getElement('d_document_number'		)->setOptions(array('Required' => false));
		$this->getElement('d_document_issue_date'	)->setOptions(array('Required' => false));
		$this->getElement('d_document_issue_by'		)->setOptions(array('Required' => false));
		$this->getElement('d_privilege_type'		)->setOptions(array('Required' => false));
		$this->getElement('d_faculty'				)->setOptions(array('Required' => false));
		return true;		
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