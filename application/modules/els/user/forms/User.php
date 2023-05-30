<?php
/**
 * Форма для создания и редактирования пользователей
 *
 */
class HM_Form_User extends HM_Form {

    public function init() {

        $userId = $this->getParam('MID', 0);

        // echo $this->user_id;
        $this->setMethod(Zend_Form::METHOD_POST);

        $this->setName('user');

        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $_SERVER['HTTP_REFERER'],
            )
        );

        $this->addElement('hidden',
            'user_id',
            array(
                'Required' => false,
                'value' => $this->getParam('user_id', 0)
            )
        );

        $this->addElement('text', 'mid_external', array('Label' => _('Табельный номер'),
            'Required' => false,
			'Description' => _('Произвольный номер, отображается в карточке пользователя'),
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array('StripTags'),
        )
        );

        $labelLastName   = _('Фамилия');
        $labelFirstName  = _('Имя');
        $labelPatronymic = _('Отчество');
		$labelLastName_en   = _('Фамилия (латиницей)');
        $labelFirstName_en  = _('Имя (латиницей)');
        //$labelPatronymic_en = _('Отчество (латиницей)');

/*        if ($this->getService('Lang')->countLanguages() > 1) {

            $this->addElement('text', 'lastnameLat', array('Label' => _('Фамилия (латиницей)'),
                'Required' => true,
                'Validators' => array(
                    array('StringLength', 255, 1)
                ),
                'Filters' => array('StripTags')
            )
            );

            $this->addElement('text', 'firstnameLat', array('Label' => _('Имя (латиницей)'),
                'Required' => true,
                'Validators' => array(
                    array('StringLength', 255, 1)
                ),
                'Filters' => array('StripTags')
            )
            );

            $labelLastName   .= ' ('._('кириллицей').')';
            $labelFirstName  .= ' ('._('кириллицей').')';
            $labelPatronymic .= ' ('._('кириллицей').')';

        }*/


        $this->addElement('text', 'lastname', array('Label' => $labelLastName,
            'Required' => true,
            'Validators' => array(
                array('StringLength', 255, 1),
                array('AlphaForNames'),
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
                array('StringLength', 255, 1),
                array('AlphaForNames'),
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
		
        $this->addElement('text', 'patronymic', array('Label' => $labelPatronymic,
            'Required' => false,
            'Validators' => array(
                array('StringLength', 255, 1),
                array('AlphaForNames'),
            ),
            'Filters' => array('StripTags')
        )
        );

     /*   $this->addElement('text', 'patronymic_en', array('Label' => $labelPatronymic_en,
            'Required' => false,
            'Validators' => array(
                array('StringLength', 255, 1),
                array('AlphaForNames'),
            ),
            'Filters' => array('StripTags')
        )
        );*/
		
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
                array('Db_NoRecordExists', false, array('table' => 'People', 'field' => 'Login'))
            ),
            'Filters' => array('StripTags'),
            'Description' => $loginErrorMsg
        ));

        $this->addElement('PasswordCheckbox',
            'generatepassword',
            array('Label' => _('Сгенерировать пароль автоматически'),
                'Required' => false,
                'Validators' => array(),
                'Filters' => array('StripTags'),
                'inputs' => array('userpassword', 'userpasswordrepeat'),
                'checked' => true
            )
        );


        $passwordOptions = $this->getService('Option')->getOptions(HM_Option_OptionModel::SCOPE_PASSWORDS);

        $this->addElement('password',
            'userpassword',
            array('Label' => _('Пароль'),
                'Required' => false,
                'Validators' => array(
                    array('identical',
                        false,
                        array('token' => 'userpasswordrepeat')
                    )
                ),
                'Filters' => array('StripTags'),
                'Description' => sprintf(_("Количество символов в пароле должно быть не менее %d"), $passwordOptions['passwordMinLength'])
            )
        );

        $password = $this->getElement('userpassword');
        if($passwordOptions['passwordCheckDifficult'] == 1){
           $password->addValidator('HardPassword');
        }else{
            $password->addValidator('Regex', false, array('/^[a-zа-яёЁ0-9%\\$#!]+$/ui', 'messages' => array(Zend_Validate_Regex::NOT_MATCH => _("Пароль может содержать только латинские или кириллические буквы, а также символы '$', '#' и '!'"))));
        }

        if($passwordOptions['passwordMinLength'] > 0){
            $password->addValidator('StringLength', false, array('min' => $passwordOptions['passwordMinLength']));
        }
        if($passwordOptions['passwordMinPeriod'] > 0
            && !$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ADMIN)
            //&& $this->getService('User')->getCurrentUserRole() != HM_Role_RoleModelAbstract::ROLE_ADMIN
        ){
            $password->addValidator('MinimalDatePassword');
        }
        if($passwordOptions['passwordMinNoneRepeated'] > 0
            && !$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ADMIN)
        //    && $this->getService('User')->getCurrentUserRole() != HM_Role_RoleModelAbstract::ROLE_ADMIN
        ){
            $password->addValidator('AmountPassword');
        }


        $this->addElement('password',
            'userpasswordrepeat',
            array('Label' => _('Повторите пароль'),
                'Required' => false,
                'Filters' => array('StripTags')

            )
        );

        if (
            $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ADMIN) ||
            $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_DEAN)
        ) {
            if (!$userId) {
                $userId = $this->getElement('user_id')->getValue();
            }
            $tags = $userId ? $this->getService('Tag')->getTags($userId, $this->getService('TagRef')->getUserType() ) : '';
            $this->addElement(new HM_Form_Element_FcbkComplete('tags', array(
                    'Label' => _('Метки'),
					'Description' => _('Произвольные слова, предназначены для поиска и фильтрации, после ввода слова нажать &laquo;Enter&raquo;'),
                    'json_url' => $this->getView()->url(array('module' => 'user', 'controller' => 'index', 'action' => 'tags')),
                    'value' => $tags,
                    'Filters' => array()
                )
            ));
        }

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
            'Required' => false,
            'Validators' => array(
                array('EmailAddress')
            ),
            'Filters' => array('StripTags')
        )
        );

        $this->addElement('text', 'tel', array(
            'Label' => _('Рабочий телефон'),
            'Required' => false,
            'Validators' => array(
            ),
            'Filters' => array(
            )
        ));

        $this->addElement('text', 'tel2', array(
            'Label' => _('Мобильный телефон'),
            'Required' => false,
            'Validators' => array(
            ),
            'Filters' => array(
            )
        ));

        /* НА ГАЗПРОМЕ ПРИ МЕРЖЕ ОСТАВИТЬ!!!
        $this->addElement('select', 'team', array(
            'Label' => _('Принадлежность к группе сотрудников'),
            'Required' => false,
            'MultiOptions' => HM_User_Metadata_MetadataModel::getTeamValues()
        ));
        */

        $this->addElement('select', 'status', array('Label' => _('Статус'),
            'Required' => true,
            'Validators' => array(),
            'Filters' => array('StripTags'),
            'multiOptions' => array(
                '0' => 'Активный',
                '1' => 'Заблокирован'
            )
        )
        );
        $roles = HM_Role_RoleModelAbstract::getBasicRoles(false, true);
        if ($this->getRequest()->getActionName() == 'new') {
            $this->addElement('select', 'role', array('Label' => _('Назначить роль'),
                'Required' => false,
                'Validators' => array(),
                'Filters' => array('StripTags'),
                'multiOptions' => $roles
            )
            );

            # Обязательные поля пароля и его подтверждения
            $password = $this->getElement('userpassword');
            $password -> setRequired(true);
            $rpassword = $this->getElement('userpasswordrepeat');
            $rpassword -> setRequired(true);
        }


        // start preparing position_id element
        $positionIdJQueryParams = array(
            'remoteUrl' => $this->getView()->url(array('module' => 'orgstructure', 'controller' => 'ajax', 'action' => 'tree', 'only-departments' => 1))
        );
        /**
         * Тут выбирались айдишники должности, поэтому в селекте вместо того
         * чтобы показывать родительское подразделение с выбранным своим подразделением
         * добавлен еще один шаг по дереву.
         * т.к. uiTreeSelect значение использовал из формы, т.е. опять же значение должности
         * пришлось добавить еще один параметр передаваемый в JqueryParams
         * @author Artem Smirnov <tonakai.personal@gmail.com>
         * @date 28 december 2012
         */
        if ($userId) {
            $units = $this->getService('Orgstructure')->fetchAll(array('mid = ?' => $userId));
            if(count($units)){
                $units = $this->getService('Orgstructure')->fetchAll(array('soid = ?' => $units->current()->owner_soid));
            }
            if (count($units)) {
                $positionIdJQueryParams['selected'] = $units->current()->soid;
                $positionIdJQueryParams['itemId'] = $units->current()->owner_soid;
                $positionIdJQueryParams['ignoreDefaultSelectedValue'] = true;
            }
        }

        $this->addElement('uiTreeSelect', 'position_id', array(
            'Label' => _('Подразделение'),
            'Required' => false,
            'validators' => array(
                'int',
                array('GreaterThan', false, array(-1))
            ),
            'filters' => array('int'),
            'jQueryParams' => $positionIdJQueryParams
        ));

        $this->addElement('text', 'position_name', array(
            'Label' => _('Должность'),
            'Required' => false,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array('StripTags')
        )
        );
		
		
		
		
		 $this->addElement('DatePicker', 'begin_learning', array(
            'Label' 		=> _('Дата начала обучения'),
            'Required' 		=> false,			
            'Filters'  		=> array('StripTags'),
            'JQueryParams' 	=> array(
                'showOn' 			=> 'button',
                'buttonImage' 		=> "/images/icons/calendar.png",
                'buttonImageOnly' 	=> 'true'
            )
        ));


/*        $userId = $this->getRequest()->getParam('MID', 0) > 0 ? $this->getRequest()->getParam('MID', 0): $this->getRequest()->getParam('user_id', 0);
        $role = $this->getService('User')->getCurrentUserRole();

        if($userId > 0 && $role == HM_Role_RoleModelAbstract::ROLE_ADMIN  && $this->getService('User')->isRoleExists($userId, HM_Role_RoleModelAbstract::ROLE_DEAN)){

            $options = $this->getService('Dean')->getResponsibilityOptions($userId);

            $this->addElement('checkbox', 'unlimited',
                array(
                	'Label' => _('Без ограничений'),
                    'Required' => false,
                    'Validators' => array(),
                    'Filters' => array('StripTags'),
                    'Value' => $options['unlimited']
                )
            );

            $this->addElement('checkbox', 'assign_new',
                array(
                	'Label' => _('Все новые учебные курсы'),
                    'Required' => false,
                    'Validators' => array(),
                    'Filters' => array('StripTags'),
                    'Value' => $options['assign_new']
                )
            );


        }*/


        $this->addDisplayGroup(array(
            'userlogin',
            'user_id',
            'generatepassword',
            'userpassword',
            'userpasswordrepeat',
            'cancelUrl',
            'tags',
        ),
            'Users1',
            array('legend' => _('Учётная запись'))
        );

        $this->addDisplayGroup(array(
            'mid_external',
            'lastname',
			'lastname_en',
            'firstname',
			'firstname_en',
            'patronymic',
//			'patronymic_en',          
//            'lastnameLat',
//            'firstnameLat',
            'gender',
            'year_of_birth',
            'email',
            'tel',
            'tel2',
            //'team',
            'photo',
            'additional_info'
        ),
            'Users2',
            array('legend' => _('Персональные данные'))
        );

        if ((null != $this->getElement('status')) || (null != $this->getElement('role'))) {
            $this->addDisplayGroup(array(
                'status',
                'role'
            ),
                'Users3',
                array('legend' => _('Назначения'))
            );
        }

/*        if($userId > 0 && $role == HM_Role_RoleModelAbstract::ROLE_ADMIN && $this->getService('Dean')->userIsDean($userId)){
            $this->addDisplayGroup(array(
                'unlimited',
                'assign_new'
            ),
                'Responsibility',
                array('legend' => _('Область ответственности'))
            );
        }*/

        $this->addDisplayGroup(
            array(
                'position_id',
                'position_name'
            ),
            'UserOrgstructure',
            array('legend' => _('Место работы'))
        );
		
		$this->addDisplayGroup(
            array(
                'begin_learning',                
            ),
            'UserLearning',
            array('legend' => _('Обучение'))
        );
		
		
		


        if (in_array($this->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_ADMIN))) {
            $classifierElements = $this->addClassifierElements(
                HM_Classifier_Link_LinkModel::TYPE_PEOPLE,
                $userId
            );
            $this->addClassifierDisplayGroup($classifierElements);
        }


        $this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));

        $event = new sfEvent(null, HM_Extension_ExtensionService::EVENT_FILTER_FORM_USER);
        $this->getService('EventDispatcher')->filter($event, $this);

        parent::init(); // required!

    }


    public function getElementDecorators($alias, $first = 'ViewHelper') {
        if ($alias == 'photo') {
            $decorators = parent::getElementDecorators($alias, 'UserImage');
            array_unshift($decorators, 'ViewHelper');
            return $decorators;
        }
        return parent::getElementDecorators($alias, $first);
    }


}