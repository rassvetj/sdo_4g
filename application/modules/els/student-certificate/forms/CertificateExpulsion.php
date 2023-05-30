<?php
class HM_Form_CertificateExpulsion  extends HM_Form
{
	protected $id = 'certificate';
	
    public function init()
	{
		$user           = $this->getService('User')->getCurrentUser();	
		$userInfo		= $this->getService('UserInfo')->getCurrentUserInfo();			
		$types          = HM_StudentCertificate_StudentCertificateModel::getTypes();		
		$validator 		= new Zend_Validate_EmailAddress();		
		$fio            = $user->getName();
		$email          = $user->EMail;
		$phone          = $user->Phone;
		$course         = (int)$userInfo->course;
		$direction      = $userInfo->specialty;
		
		$this->setMethod(Zend_Form::METHOD_POST);
        $this->setName($this->id);
		
		$this->setAction(
            $this->getView()->url(
                array(
                    'module'     => 'student-certificate',
                    'controller' => 'certificate',
                    'action'     => 'create',
                )
            )
        );
		
		$this->addElement('select','type', array(
			'label'        => _('Вид справки/документа'),
			'value'        => HM_StudentCertificate_StudentCertificateModel::TYPE_EXPULSION,
			'multiOptions' => HM_StudentCertificate_StudentCertificateModel::getCertificateTypes(),
		));
		
		$this->addElement('text', 'fio', array(
            'Label'    => _('Ф.И.О.'),
            'Required' => true,
			'Value'    => $fio,					
			'Readonly' => true,
        ));
		
		if ($validator->isValid($email)) {
			$this->addElement('text', 'email', array(
				'Label'      => _('E-Mail'),
				'Value'      => $email,
				'Required'   => true,
				'Validators' => array('EmailAddress'),
				'Readonly'   => true,
			));
		} else {
			$this->addElement('text', 'email', array(
				'Label'      => _('E-Mail'),
				'Value'      => '',
				'Required'   => true,
				'Validators' => array('EmailAddress'),				
			));
		}
		
		if (!empty($phone)){
			$this->addElement('text', 'phone', array(                        
				'Label'      => _('Номер телефона'),
				'Value'      => $phone,
				'Required'   => true,				
				'Readonly'   => true,
			));
		} else {
			$this->addElement('text', 'phone', array(                        
				'Label'      => _('Номер телефона'),
				'Value'      => '',			
				'Required'   => true,				
			));
		}
		
		
		if (!empty($direction)){
			$this->addElement('text', 'direction', array(
				'Label'      => _('Направление подготовки'),
				'Value'      => $direction,
				'Required'   => true,				
				'Readonly'   => true,
			));
		} else {
			$this->addElement('text', 'direction', array(
				'Label'      => _('Направление подготовки'),
				'Value'      => '',
				'Required'   => true,
			));
		}
		
		
		if (!empty($course)){
			$this->addElement('text', 'course', array(                        
				'Label'      => _('Курс'),
				'Value'      => $course,
				'Required'   => true,				
				'Readonly'   => true,
			));
		} else {
			$this->addElement('text', 'course', array(                        
				'Label'      => _('Курс'),
				'Value'      => '',			
				'Required'   => true,				
			));
		}
		
		$this->addElement('select', 'study_form', array(
			'Label' 	   => _('Форма обучения'),
			'multiOptions' => array('' => _('-- выберите --')) + HM_StudentCertificate_StudentCertificateModel::getStudyForms(),
			'Required' 	   => true,
		));
		
		$this->addElement('select', 'basis_learning', array(
			'Label' 	   => _('Основа обучения'),
			'multiOptions' => array('' => _('-- выберите --')) + HM_StudentCertificate_StudentCertificateModel::getBasisLearningList(),
			'Required' 	   => true,			
		));
		
		
		$this->addElement('select', 'expulsion_type', array(
			'Label' 	   => _('Тип отчисления'),
			'multiOptions' => array('' => _('-- выберите --')) + HM_StudentCertificate_StudentCertificateModel::getExpulsionTypes(),
			'Required' 	   => true,			
		));
		
		$this->addElement('text', 'university', array(                        
			'Label'      => _('Вуз, в который осуществляется перевод'),
			'Value'      => '',			
			'Required'   => false,				
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
        ));
		
		$this->addElement('button', 'btn_get_order', array(
            'Label'    => _('Сформировать и скачать заявление'),
			'class'    => 'btn_get_order',
			'disabled' => 'disabled',
			'onClick'  => 'getOrder(); return false;',
			'data-url' => $this->getView()->url(array(	'module'     => 'student-certificate', 
														'controller' => 'file', 
														'action'     => 'get-statement', 
														'download'   => 1, 
														'type'       => HM_StudentCertificate_StudentCertificateModel::TYPE_EXPULSION
													)),
        ));
		
		$this->addDisplayGroup(
            array(
				'fio',
				'email',
				'phone',
				'direction',
				'course',
            ),
            'group_1',
            array(
				'class'  => '',
			)
        );
		
		$this->addDisplayGroup(
            array(
				'study_form',
				'basis_learning',
				'expulsion_type',
				'university',
				'file_order',
				'btn_get_order',
            ),
            'group_2',
            array(
				'class'  => '',
			)
        );
		
		
			
		
		
		$this->addElement('submit', 'submit', array(
            'Label' => _('Отправить'),
        ));
		
		parent::init();
	}
	
	public function change($type, $additional = array())
	{
		$request        = $this->getRequest();
		$expulsion_type = (int)$request->getParam('expulsion_type');
		
		if($expulsion_type == HM_StudentCertificate_StudentCertificateModel::EXPULSION_TYPE_TO_ANOTHER_UNIVERSITY){
			$this->getElement('university')->setOptions(array('Required' => true));
		} else {
			$this->getElement('university')->setOptions(array('Required' => false));
		}
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