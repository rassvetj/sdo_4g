<?php
class HM_Form_Certificate  extends HM_Form
{
	protected $id = 'certificate';
	
    public function init()
	{
		$user           = $this->getService('User')->getCurrentUser();		
		$types          = HM_StudentCertificate_StudentCertificateModel::getTypes();		
		$types_comments = HM_StudentCertificate_StudentCertificateModel::getTypesComments();
		$validator 		= new Zend_Validate_EmailAddress();		
		$fio            = $user->getName();
		$email          = $user->EMail;
		$phone          = $user->Phone;
		$groups         = $this->getService('StudyGroupUsers')->getUserGroups($user->MID);
		$group          = isset($groups[0]['name']) ? ($groups[0]['name']) : ('');
		$directions     = $this->getService('StudentCertificate')->getDirectionList();
		
		$this->setMethod(Zend_Form::METHOD_POST);
        $this->setName($this->id);
		$this->setAttrib('class', 'form-default');
		
		$this->setAction(
            $this->getView()->url(
                array(
                    'module'     => 'student-certificate',
                    'controller' => 'certificate',
                    'action'     => 'create',
                )
            )
        );
		
		$this->addElement('text', 'fio_c', array(
            'Label'    => _('Ф.И.О.:'),
            'Required' => true,
			'Value'    => $fio,					
			'class'    => 'input-disabled fio_c',
			'Readonly' => true,
        ));
		
		if ($validator->isValid($email)) {		
			$this->addElement('text', 'email_c', array(                        
				'Label'      => _('E-Mail:'),
				'Value'      => $email,			
				'Required'   => true,		
				'Validators' => array('EmailAddress'),								
				'class'      => 'input-disabled email_c',
				'Readonly'   => true,
			));
		} else {
			$this->addElement('text', 'email_c', array(                        
				'Label'      => _('E-Mail:'),
				'Value'      => '',			
				'Required'   => true,	
				'Validators' => array('EmailAddress'),
				'class'      => 'email_c',
			));
		}
		
		if (!empty($phone)){
			$this->addElement('text', 'phone', array(                        
				'Label'      => _('Номер телефона:'),
				'Value'      => $phone,
				'Required'   => true,
				'class'      => 'input-disabled phone',
				'Readonly'   => true,
			));
		} else {
			$this->addElement('text', 'phone', array(                        
				'Label'      => _('Номер телефона:'),
				'Value'      => '',			
				'Required'   => true,
				'class'      => 'phone',
			));
		}
		
		if (!empty($group)){
			$this->addElement('text', 'group', array(                        
				'Label'      => _('Группа:'),
				'Value'      => $group,
				'Required'   => true,
				'class'      => 'input-disabled group',
				'Readonly'   => true,
			));
		} else {
			$this->addElement('text', 'group', array(                        
				'Label'      => _('Группа:'),
				'Value'      => '',			
				'Required'   => true,
				'class'      => 'group',
			));
		}
		
		
		$this->addElement('text', 'faculty_c', array(                        
			'Label'    => _('Факультет:'),
			'Value'    => '',			
			'Required' => true,
			'class'    => 'faculty_c',
		));
		
		$this->addElement('select','type', array(
			'label'        => _('Вид справки/документа'),
			#'value'        => HM_StudentCertificate_StudentCertificateModel::TYPE_STUDY_COGNIZANCE,
			'multiOptions' => HM_StudentCertificate_StudentCertificateModel::getCertificateTypes(),
		));
		
		$this->addElement('select','count', array( 
			'label'        => _('Количество'),
			'value'        => '1',
			'class'        => 'count',
			'multiOptions' => array(
				'1' => _('1'),
				'2' => _('2'),
				'3' => _('3'),
				'4' => _('4'),
				'5' => _('5'),				
			),
		));
		
		$this->addElement('textarea', 'destination', array(            
			'Label'    => _('Место требования справки'),
            'Required' => true,
			'rows'     => '5',
			'class'    => 'destination',
        ));
		
		$this->addElement('select','signature_type', array( 
			'label'        => _('Вид справки'),
			'value'        => '',
			'class'        => 'signature_type',
			'multiOptions' => array('' => _('- выберите -')) + HM_StudentCertificate_StudentCertificateModel::getSignatureTypes(),
		));
		
		$this->addElement('select','delivery_method', array( 
			'label'        => _('Способ получения справки'),
			'value'        => '',
			'class'        => 'delivery_method',
			'multiOptions' => array('' => _('- выберите -')) + HM_StudentCertificate_StudentCertificateModel::getDeliveryMethods(),
		));
	
		
		$this->addElement('textarea', 'place_work', array(            
			'Label'    => _('Место работы'),
            'Required' => false,
			'rows' 	   => '5',		
            'class'    => 'place_work',			
        ));
		
		$this->addElement('text', 'period', array(            
			'Label'    => _('Период начисления стипендии'),
            'Required' => false,
			'Value'    => '',
			'class'    => 'period',
        ));

		$this->addElement('text', 'Postcode', array(
			'Label'    => _('Индекс:'),
			'Value'    => '', 
			'Required' => false,
			'class'    => 'Postcode',
		));
		
		$this->addElement('text', 'city_c', array(
			'Label'    => _('Город/область/край:'),
			'Value'    => '',
			'Required' => false,
			'class'    => 'city_c',
		));
		
		$this->addElement('text', 'street_c', array(
			'Label'    => _('Улица, дом, квартира:'),
			'Value'    => '',
			'Required' => false,
			'class'    => 'street_c',
		));
		
		$this->addElement('text', 'employer_c', array(
			'Label'    => _('Наименование работодателя:'),
			'Value'    => '',
			'Required' => false,
			'class'    => 'employer_c',			
		)); 
		
		$this->addElement('DatePicker', 'date_from', array(
            'Label'			=> _('с'),
            'Required' 		=> false,
            'Validators' 	=> array(array('StringLength', false, array('min' => 10, 'max' => 50))),
            'Filters' 		=> array('StripTags'),
            'class'	        => 'date_picker date_from',
			'JQueryParams' 	=> array(
                'showOn' 		  => 'button',
                'buttonImage' 	  => "/images/icons/calendar.png",
                'buttonImageOnly' => 'true',
            ),
        ));
		
		$this->addElement('DatePicker', 'date_to', array(
            'Label'			=> _('по'),
            'Required' 		=> false,
            'Validators' 	=> array(array('StringLength', false, array('min' => 10, 'max' => 50))),
            'Filters' 		=> array('StripTags'),
            'class'         => 'date_picker date_to',
			'JQueryParams' 	=> array(
                'showOn' 		  => 'button',
                'buttonImage' 	  => "/images/icons/calendar.png",
                'buttonImageOnly' => 'true',
            ),
        ));
		
		$this->addElement($this->getDefaultFileElementName(), 'file_c', array(
			'Label'             => _('Прикрепите документ'),
			'Destination'       => Zend_Registry::get('config')->path->upload->temp,
            'Required'          => false,
            'Description'       => _('Для загрузки использовать файлы форматов: jpg, jpeg, png, gif, pdf, doc, docx, xls, xlsx. Максимальный размер файла &ndash; 5 Mb'),
            'Filters'           => array('StripTags'),
            'file_size_limit'   => 5242880,
            'file_types'        => '*.jpg;*.png;*.gif;*.jpeg, *.pdf,*.doc,*.docx,*.xls,*.xlsx',
            'file_upload_limit' => 1,
			'user_id'           => 0,
			'class'             => 'file_c',
        ));

		$this->addElement($this->getDefaultFileElementName(), 'file_order', array(
			'Label'             => _('Прикрепите заявление'),
			'Destination'       => Zend_Registry::get('config')->path->upload->temp,
            'Required'          => false,
            'Description'       => _('Для загрузки использовать файлы форматов: jpg, jpeg, png, gif, pdf, doc, docx, xls, xlsx. Максимальный размер файла &ndash; 5 Mb'),
            'Filters'           => array('StripTags'),
            'file_size_limit'   => 5242880,
            'file_types'        => '*.jpg;*.png;*.gif;*.jpeg, *.pdf,*.doc,*.docx,*.xls,*.xlsx',
            'file_upload_limit' => 1,
			'user_id'           => 0,
			'class'             => 'file_order',
        ));	

		$this->addElement($this->getDefaultFileElementName(), 'file_passport', array(
			'Label'             => _('Скан-копия паспорта'),
			'Destination'       => Zend_Registry::get('config')->path->upload->temp,
            'Required'          => false,
            'Description'       => _('Для загрузки использовать файлы форматов: jpg, jpeg, png, gif, pdf, doc, docx, xls, xlsx. Максимальный размер файла &ndash; 5 Mb'),
            'Filters'           => array('StripTags'),
            'file_size_limit'   => 5242880,
            'file_types'        => '*.jpg;*.png;*.gif;*.jpeg, *.pdf,*.doc,*.docx,*.xls,*.xlsx',
            'file_upload_limit' => 0,
			'user_id'           => 0,
			'class'             => 'file_passport',
        ));	
		
		
		
		
		$this->addElement('select', 'direction_c', array(
			'Label' 	   => _('Направление:'),
			'multiOptions' => array('' => _('-- выберите --')) + $directions,
			'Required' 	   => false,
			'class'        => 'direction_c',
		));
		
		$this->addElement('text', 'course_c', array(
			'Label' 	=> _('Курс:'),
			'Value'	 	=> '',
			'Required' 	=> false,
			'maxlength' => '2',
			'filters'	=> array('StringTrim'),
			'size'      => '10',
			'class'     => 'course_c',
			'validators' => array(array(
				'validator' => 'Int',
                'options'   => array(
                    'messages' => 'Введите число',
                ),
                'breakChainOnFailure' => true,
            )),
		)); 
		
		$this->addElement('text', 'document_series', array(
			'Label'    => _('Серия документа:'),
			'Value'    => '',
			'Required' => false,
			'class'    => 'document_series',
		));
		
		$this->addElement('text', 'document_number', array(
			'Label'    => _('Номер документа:'),
			'Value'    => '',
			'Required' => false,
			'class'    => 'document_number',
		));
		
		$this->addElement('DatePicker', 'document_issue_date', array( 
            'Label'			=> _('Дата выдачи документа'),
            'Required' 		=> false,
            'Validators' 	=> array(array('StringLength', false, array('min' => 10, 'max' => 50))),
            'Filters' 		=> array('StripTags'),
            'class'	        => 'date_picker document_issue_date',
			'JQueryParams' 	=> array(
                'showOn' 		  => 'button',
                'buttonImage' 	  => "/images/icons/calendar.png",
                'buttonImageOnly' => 'true',
            ),			
        ));
		
		$this->addElement('text', 'document_issue_by', array(
			'Label'    => _('Кем выдан документ:'),
			'Value'    => '',
			'Required' => false,
			'class'	   => 'document_issue_by',
		));
		
		$this->addElement('select', 'privilege_type', array(
			'Label' 	   => _('Вид льготы:'),
			'multiOptions' => array('' => _('-- выберите --')) + HM_StudentCertificate_StudentCertificateModel::getPrivilegeTypeList(),
			'Required' 	   => false, 
			'class'	       => 'privilege_type',
		));
		   
		$this->addElement('DatePicker', 'privilege_date', array( 
            'Label'			=> _('Срок действия льготы'),
            'Required' 		=> false,
            'Validators' 	=> array(array('StringLength', false, array('min' => 10, 'max' => 50))),
            'Filters' 		=> array('StripTags'),
            'class'	        => 'date_picker privilege_date',
			'Description' 	=> _('Дата окончания льготы указана в справке. Если бессрочно, оставьте поле незаполненным'),
			'JQueryParams' 	=> array(
                'showOn' 		  => 'button',
                'buttonImage' 	  => "/images/icons/calendar.png",
                'buttonImageOnly' => 'true'
            ),
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
			'class'	            => 'document_file',
        ));
		
		$this->addElement('text', 'place_c', array(
			'Label'    => _('Место представления:'),
			'Value'    => '',
			'Required' => false,
			'class'	   => 'place_c',
		));
		
		$this->addElement('select', 'transfer_type', array(
			'Label' 	   => _('Тип перевода:'),
			'multiOptions' => array('' => _('-- выберите --')) + HM_StudentCertificate_StudentCertificateModel::getTransferTypes(),
			'Required' 	   => false,
			'class'        => 'transfer_type',
		));
		
		$this->addElement('select', 'organization', array(
			'Label' 	   => _('Организация'),
			'multiOptions' => array('' => _('-- выберите --')) + HM_StudentCertificate_StudentCertificateModel::getOrganizations(),
			'Required' 	   => false,
			'class'        => 'organization',
			'onChange'     => 'changeOrganization($(this))',
		));
		
		$this->addElement('select', 'program', array(
			'Label' 	   => _('Программа обучения'),
			'multiOptions' => array('' => _('-- выберите --')) + HM_StudentCertificate_StudentCertificateModel::getPrograms(),
			'Required' 	   => false,
			'class'        => 'program',
		));
		
		$this->addElement('select', 'faculty', array(
			'Label' 	   => _('Факультет'),
			'multiOptions' => array('' => _('-- выберите --')) + HM_StudentCertificate_StudentCertificateModel::getFaculties(),
			'Required' 	   => false,
			'class'        => 'faculty',
		));
		
		$this->addElement('select', 'direction_desired', array(
			'Label' 	   => _('Желаемое направление подготовки'),
			'multiOptions' => array('' => _('-- выберите --')) + $directions,
			'Required' 	   => false,
			'class'        => 'direction_desired',
		));
		
		$this->addElement('select', 'study_form', array(
			'Label' 	   => _('Форма обучения'),
			'multiOptions' => array('' => _('-- выберите --')) + HM_StudentCertificate_StudentCertificateModel::getStudyForms(),
			'Required' 	   => false,
			'class'        => 'study_form',
		));
		
		$this->addElement('select', 'basis_learning', array(
			'Label' 	   => _('Основа обучения'),
			'multiOptions' => array('' => _('-- выберите --')) + HM_StudentCertificate_StudentCertificateModel::getBasisLearningList(),
			'Required' 	   => false,
			'class'        => 'basis_learning',
		));
		
		$this->addElement('button', 'btn_get_order', array(
            'Label'    => _('Сформировать и скачать заявление'),
			'class'    => 'btn_get_order',
			'disabled' => 'disabled',
			'onClick'  => 'getOrder(); return false;',
			'data-url' => $this->getView()->url(array('module' => 'student-certificate', 'controller' => 'certificate', 'action' => 'get-order', 'download' => 1)),
        ));
		
		$this->addDisplayGroup(
            array(
                'fio_c',
                'email_c',
                'faculty_c',
                'phone',
                'group',
                'count',
                'destination',
                'signature_type',
                'delivery_method',
                'period',
                'place_work',
            ),
            'additional_main',
            array(
				'class'  => 'fio_c email_c faculty_c phone group count destination period place_work signature_type delivery_method',
			)
        );
		
		$this->addDisplayGroup(
            array(
                'document_series',
                'document_number',
                'document_issue_date',
                'document_issue_by',
                'privilege_type',
                'privilege_date',
                'document_file',
            ),
            'additional_3',
            array(
				'class'  => 'document_series document_number document_issue_date document_issue_by privilege_type privilege_date document_file',
			)
        );
		
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
				'legend' => _('Обязательны для студентов дистанционной формы обучения'),
				'class'  => 'Postcode city_c street_c employer_c date_from date_to file_c',
			)
        );
		
		$this->addDisplayGroup(
            array(
                'direction_c',
                'course_c',
                'year_c',
                'place_c',
                'transfer_type',
                'organization',
				'faculty',
                'program',
                'direction_desired',
                'study_form',
                'basis_learning',
                'btn_get_order',
                'file_order',                
                'file_passport',                
				
            ),
            'additional_2',
            array(
				'class' => 'direction_c course_c year_c place_c transfer_type organization faculty program direction_desired study_form basis_learning btn_get_order file_order file_passport',
			)
        );
			
		
		
		$this->addElement('submit', 'submit', array(
            'Label' => _('Отправить'),
        ));
		
		parent::init();
	}
	
	public function change($type, $additional = array())
	{	
		$fields = array(
			'fio_c','email_c','faculty_c','type','count','destination','place_work','period','Postcode','city_c','street_c','employer_c','date_from','date_to','file_c','direction_c','course_c','document_series',
			'document_number','document_issue_date','document_issue_by','privilege_type','privilege_date','document_file','place_c', 'phone', 'signature_type', 'delivery_method',
		);
		
		foreach($fields as $fieldName){
			$this->getElement($fieldName)->setOptions(array('Required' => false));	
		}
		$this->getElement('faculty_c')->setOptions(array('Required' => true));
		
		if(in_array($type, array(
			HM_StudentCertificate_StudentCertificateModel::TYPE_STUDY_COGNIZANCE,
			HM_StudentCertificate_StudentCertificateModel::TYPE_STUDY,
			HM_StudentCertificate_StudentCertificateModel::TYPE_GRANT,
			HM_StudentCertificate_StudentCertificateModel::TYPE_GIA,
			HM_StudentCertificate_StudentCertificateModel::TYPE_VALIDATION,
			HM_StudentCertificate_StudentCertificateModel::TYPE_LICENSE,
			HM_StudentCertificate_StudentCertificateModel::TYPE_OUT_OF_ORDER,
		))){
			$this->getElement('signature_type' )->setOptions(array('Required' => true));
			$this->getElement('delivery_method')->setOptions(array('Required' => true));
		}
		
		
		if($type == HM_StudentCertificate_StudentCertificateModel::TYPE_GIA){
			$this->getElement('employer_c'  )->setOptions(array('Required' => true));
			$this->getElement('place_work'  )->setOptions(array('Required' => true));
			$this->getElement('destination'	)->setOptions(array('Required' => true));
			return true;
		}
		
		if($type == HM_StudentCertificate_StudentCertificateModel::TYPE_LICENSE){
			$this->getElement('destination'	)->setOptions(array('Required' => true));
			$this->getElement('direction_c'	)->setOptions(array('Required' => true));
			$this->getElement('course_c'	)->setOptions(array('Required' => true));
			$this->getElement('place_c'		)->setOptions(array('Required' => true));
			return true;
		} 
			
		if($type == HM_StudentCertificate_StudentCertificateModel::TYPE_VALIDATION){
			$this->getElement('destination')->setOptions(array('Required' => true));
			$this->getElement('place_work' )->setOptions(array('Required' => true));
			return true;
		}
			
		if($type == HM_StudentCertificate_StudentCertificateModel::TYPE_GRANT){
			$this->getElement('destination' )->setOptions(array('Required' => true));
			$this->getElement('period'      )->setOptions(array('Required' => true));
			return true;
		} 
		
		if(in_array($type, array(
			HM_StudentCertificate_StudentCertificateModel::TYPE_GRANT_STATE_SOCIAL,
			HM_StudentCertificate_StudentCertificateModel::TYPE_GRANT_STATE_SOCIAL_INCREASED,
			HM_StudentCertificate_StudentCertificateModel::TYPE_GRANT_STATE_ACADEMIC_INCREASED,
			HM_StudentCertificate_StudentCertificateModel::TYPE_MATERIAL_HELP,
		))){			
			$this->getElement('document_series'		)->setOptions(array('Required' => true));
			$this->getElement('document_number'		)->setOptions(array('Required' => true));
			$this->getElement('document_issue_date'	)->setOptions(array('Required' => true));
			$this->getElement('document_issue_by'	)->setOptions(array('Required' => true));
			$this->getElement('privilege_type'		)->setOptions(array('Required' => true));
			$this->getElement('document_file'		)->setOptions(array('Required' => true));
			return true;
		}
		
		
		if($type == HM_StudentCertificate_StudentCertificateModel::TYPE_TRANSFER_CLAIM){
			
			$this->getElement('faculty_c')->setOptions(array('Required' => false));
			
			$this->getElement('course_c'		 )->setOptions(array('Required' => true));
			$this->getElement('transfer_type'	 )->setOptions(array('Required' => true));
			$this->getElement('organization'	 )->setOptions(array('Required' => true));
			$this->getElement('program'		     )->setOptions(array('Required' => true));
			$this->getElement('direction_desired')->setOptions(array('Required' => true));
			$this->getElement('study_form'		 )->setOptions(array('Required' => true));
			$this->getElement('basis_learning'	 )->setOptions(array('Required' => true));
			$this->getElement('file_order'		 )->setOptions(array('Required' => true));
			$this->getElement('file_passport'	 )->setOptions(array('Required' => true));
			$this->getElement('phone'		     )->setOptions(array('Required' => true));
			
			if($additional['organization'] == 'РГСУ МОСКВА'){			
				$this->getElement('faculty')->setOptions(array('Required' => true));
			}
			return true;
		}
		
		
		
		$this->getElement('destination'			)->setOptions(array('Required' => true));
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