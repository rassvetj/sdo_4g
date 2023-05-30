<?php
class HM_Form_Unical extends HM_Form {
	    public function init() {

        $userId = $this->getParam('MID', 0);

        $this->setMethod(Zend_Form::METHOD_POST);

        $this->setName('userUnical');

		$this->setAttrib('id', 'target');
        $this->setAction('');
		$this->addElement('hidden',
            'user_id',
            array(
                'Required' => false,
                'value' => $this->getParam('user_id', 0)
            )
        );


        $labelLastName   = _('Фамилия');
        $labelFirstName  = _('Имя');
        $labelPatronymic = _('Отчество');
		$labelLastName_en   = _('Фамилия (латиницей)');
        $labelFirstName_en  = _('Имя (латиницей)');
		

        $this->addElement('text', 'lastname', array('Label' => $labelLastName,
            'Required' => true,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array('StripTags')
			
        )
        );

		 $this->addElement('text', 'lastname_en', array('Label' => $labelLastName_en,
            'Required' => false,
            'Validators' => array(
                array('StringLength', 255, 1),
                array('AlphaForNames'),
            ),
            'Filters' => array('StripTags')
        )
        );		
		
        $this->addElement('text', 'firstname', array('Label' => $labelFirstName,
            'Required' => true,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array('StripTags')
        )
        );

        $this->addElement('text', 'patronymic', array('Label' => $labelPatronymic,
            'Required' => false,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array('StripTags')
        )
        );


        $this->addElement('text', 'firstname_en', array('Label' => $labelFirstName_en,
            'Required' => false,
            'Validators' => array(
                array('StringLength', 255, 1),
                array('AlphaForNames'),
            ),
            'Filters' => array('StripTags')
        )
        );		
		
        $this->addElement('radio', 'gender', array(
            'Label' => _('Пол'),
            'Required' => false,
            'Validators' => array(

            ),
            'Value' => 1,
            'Filters' => array(
                'Int'
            ),
            'MultiOptions' => HM_User_Metadata_MetadataModel::getGenderValues(),
            'separator' => ' '
        ));

        $this->addElement('text', 'year_of_birth', array(
            'Label' => _('Год рождения'),
            'Required' => false,
            'Validators' => array(
                array('Between', false, array(1910, date('Y')))
            ),
            'Filters' => array(

            )
        ));

        $loginErrorMsg = _('В логине пользователя допустимы латинские символы, знак подчёркивания и точка');
        $loginValidator = new Zend_Validate_Regex('/^[\w-_\.]+$/');
        $loginValidator->setMessage($loginErrorMsg, 'regexNotMatch');
		
        $this->addElement('text', 'userlogin', array(
            'Label' => _('Логин'),
            'Required' => true,
            'Value' => Zend_Registry::get('serviceContainer')->getService('User')->generateLogin(),
            'Validators' => array(
                array('StringLength', 255, 1),
                array($loginValidator, true),
               // array('Db_NoRecordExists', false, array('table' => 'People', 'field' => 'Login', 'value' => 'user_886'))
            ),
            'Filters' => array('StripTags'),
            'Description' => $loginErrorMsg
        ));

            $tags = $userId ? $this->getService('Tag')->getTags($userId, $this->getService('TagRef')->getUserType() ) : '';
 
        $this->addElement($this->getDefaultFileElementName(), 'photo', array(
            'Label' => _('Фотография'),
            'Destination' => Zend_Registry::get('config')->path->upload->temp,
            'Required' => false,
            'Description' => _('Для загрузки использовать файлы форматов: jpg, jpeg, png, gif. Максимальный размер файла &ndash; 10 Mb'),
            'Filters' => array('StripTags'),
            'file_size_limit' => 10485760,
            'file_types' => '*.jpg;*.png;*.gif;*.jpeg',
            'file_upload_limit' => 1,
            'user_id' => 0
        )
        );

        $photo = $this->getElement('photo');
        $photo->addDecorator('UserImage')
                ->addValidator('FilesSize', true, array(
                        'max' => '10MB'
                    )
                )
                ->addValidator('Extension', true, 'jpg,png,gif,jpeg')
                ->setMaxFileSize(10485760);

//        $this->addElement($this->getDefaultWysiwygElementName(), 'additional_info',
//            array(
//            	'Label' => _('Дополнительная информация'),
//            	'Required' => false,
//            )
//        );

        $this->addElement('text', 'email', array('Label' => _('Контактный e-mail'),
            'Required' => true,
            'Validators' => array(
                array('EmailAddress')
            ),
            'Filters' => array('StripTags')
        )
        );

        $this->addElement('text', 'tel', array(
            'Label' => _('Контактный телефон'),
            'Required' => false,
            'Validators' => array(
            ),
            'Filters' => array(
            )
        ));
		
		 // start preparing position_id element
        $positionIdJQueryParams = array(
            'remoteUrl' => $this->getView()->url(array('module' => 'orgstructure', 'controller' => 'ajax', 'action' => 'tree', 'only-departments' => 1))
        );

        if ($userId) {
            $units = $this->getService('Orgstructure')->fetchAll(array('mid = ?' => $userId));
            if (count($units)) {
                $positionIdJQueryParams['selected'] = $units->current()->soid;
                $positionIdJQueryParams['itemId'] = $units->current()->owner_soid;
            }
        }

        $this->addElement('uiTreeSelect', 'position_id', array(
            'Label' => _('Подразделение'),
            'required' => true,
            'validators' => array(
                'int',
                array('GreaterThan', false, array(-1))
            ),
            'filters' => array('int'),
            'jQueryParams' => $positionIdJQueryParams
        ));

        $this->addElement('text', 'position_name', array(
            'Label' => _('Должность'),
            'Required' => true,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array('StripTags')
        )
        );
		
		$this->addElement('hidden', 'midunical');
		
		$this->addElement('hidden', 'middublicate');

		$this->addElement('hidden', 'mid_external');	
        /* НА ГАЗПРОМЕ ПРИ МЕРЖЕ ОСТАВИТЬ!!!
        $this->addElement('select', 'team', array(
            'Label' => _('Принадлежность к группе сотрудников'),
            'Required' => false,
            'MultiOptions' => HM_User_Metadata_MetadataModel::getTeamValues()
        ));
        */
		parent::init(); 
        
		$this->addDisplayGroup(array(
            'userlogin',
            'user_id',
            'cancelUrl',
            'tags',	
        ),
            'Users1',
			
            array('legend' => _('Учётная запись'),	'style'=>'width:400px;')
			
        );

        $this->addDisplayGroup(array(
            'mid_external',
            'lastname',
			'lastname_en',
            'firstname',
			'firstname_en',
            'patronymic',
            'gender',
            'year_of_birth',
            'email',
            'tel',
            'photo',
            'additional_info'
        ),
            'Users2',
            array('legend' => _('Персональные данные'),'style'=>' width:400px;')
        );
		 if (in_array($this->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_ADMIN))) {
            $classifierElements = $this->addClassifierElements(
                HM_Classifier_Link_LinkModel::TYPE_PEOPLE,
                $userId
            );
            $this->addClassifierDisplayGroup($classifierElements);
        }
		
        $this->addDisplayGroup(
            array(
                'position_id',
                'position_name',
            ),
            'UserOrgstructureDublicate',
            array('legend' => _('Место работы'),'style'=>'width:400px;')
        );
		$this->addElement('Submit', 'unionDublicate', array('Label' => _('Сохранить'),'id'=>'unionDublicate'));
    }

}