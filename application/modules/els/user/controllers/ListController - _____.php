<?php
class User_ListController extends HM_Controller_Action {
	
	protected $_currentLang = 'rus';

    protected $departmentCache = array();

    /**
     * Стандартная функция displayTags делает очень много запросов в БД
     *
     * @param int      $itemId
     * @param string   $itemType
     * @param Bvb_Grid $grid
     *
     * @return string
     */
    public function displayTags($itemId, $itemType, $grid)
    {
        static $tagsCache = null;

        $tagService = $this->getService('Tag');

        if ($tagsCache === null) {
            $result = $grid->getResult();
            $mids = array();

            foreach ($result as $raw) {
                $mids[$raw['MID']] = $raw['MID'];
            }

            $tagsCache = $tagService->getTagsCache($mids, $itemType);
        }

        $arResult = isset($tagsCache[$itemId]) ? $tagsCache[$itemId] : array();

        if (!count($arResult)) {
            return '';
        }

        asort($arResult);

        //форматирование в раскрывающийся список

        $txt = (count($arResult) > 1) ? '<p class="total">'. $tagService->pluralTagCount(count($arResult)) . '</p>' : '';

        foreach ($arResult as $item) {
            $txt .= "<p>$item</p>";
        }

        return $txt;
    }
        
	//Транслит с русского на английски1
	public function translit($str='') {
		
		$cyrForTranslit = array('а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п',
						   'р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',
						   'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П',
						   'Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я'); 
		$latForTranslit = array('a','b','v','g','d','e','yo','zh','z','i','y','k','l','m','n','o','p',
							'r','s','t','u','f','h','ts','ch','sh','sch','','y','','ae','yu','ya',
							'A','B','V','G','D','E','Yo','Zh','Z','I','Y','K','L','M','N','O','P',
							'R','S','T','U','F','H','Ts','Ch','Sh','Sch','','Y','','Ae','Yu','Ya'); 
							
		return str_replace($cyrForTranslit, $latForTranslit, $str);
	} 

	
	
    /**
     * Экшн для списка пользователей
     */
    public function indexAction() {
		
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$this->_currentLang = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);

        if (!$this->isGridAjaxRequest() && $this->_request->getParam('end[from]grid') == "") {
             $this->_request->setParam('end[from]grid', date('d.m.Y', strtotime('-1 day')));
        }

        $sorting = $this->_request->getParam("ordergrid");
        if ($sorting == ""){
            $this->_request->setParam("ordergrid", 'fio_ASC');
        }

        $this->_request->setParam("masterOrdergrid", 'notempty DESC');

        // #3388 - поле 'статус' удалено
        $select = $this->getService('User')->getSelect();
        // пришлось заменить алиас 'p' на 't1', потому что тэги работают только с таким алиасом..(
        $select->from(array('t1' => 'People'), array(
                    'MID' => 't1.MID',
                    'notempty' => "CASE WHEN (t1.LastName IS NULL AND t1.FirstName IS NULL AND  t1.Patronymic IS NULL) OR (t1.LastName = '' AND t1.FirstName = '' AND t1.Patronymic = '') THEN 0 ELSE 1 END",
                    'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(t1.LastName, ' ') , t1.FirstName), ' '), t1.Patronymic)"),

                    // ВНИМАНИЕ! При мерже с Билайном это поле не нужно! У билайна уже есть соотв.столбцы
                    'departments' => new Zend_Db_Expr('GROUP_CONCAT(d.owner_soid)'),
                    'positions' => new Zend_Db_Expr('GROUP_CONCAT(d.soid)'),
                    'login' => 't1.Login',
                    'email' => 't1.Email',
                    'email_confirmed' => 't1.email_confirmed',
                    'roles' => new Zend_Db_Expr('1'),
                    'date_registered' => 't1.Registered',
                    'status' => 't1.blocked',
                    'ldap' => 't1.isAD',
                    'tags' => 't1.MID',
                    'organization' => 't1.organization',
                ))//->joinLeft(array('r' => 'roles'), 'r.mid = t1.MID', array())
                ->joinLeft(array('d' => 'structure_of_organ'),
                    'd.mid = t1.MID',
                    array()
                )
                ->group(array('t1.MID', 't1.LastName', 't1.FirstName', 't1.Patronymic', 't1.Login', 't1.Email', 't1.email_confirmed', 't1.Registered', 't1.blocked', 't1.isAD', 't1.organization'));


        $roles = HM_Role_RoleModelAbstract::getBasicRoles(false,true);
        //unset($roles[HM_Role_RoleModelAbstract::ROLE_USER]);
        //$roles = array_merge( array("ISNULL" => _('Пользователь')), $roles);
// print $select; exit;


        $grid = $this->getGrid($select,
            array(
                'MID' => array('hidden' => true),
                'notempty' => array('hidden' => true),
                'email_confirmed' => array('hidden' => true),
                'fio' => array(
					'title' => _('ФИО'),
					'decorator' => $this->view->cardLink($this->view->url(array(
																			'module' => 'user',
																			'controller' => 'list',
																			'action' => 'view',
																			'gridmod' => null,
																			'user_id' => ''), null, true) . '{{MID}}') . '<a href="'.$this->view->url(array(
																																					'module' => 'user',
																																					'controller' => 'edit',
																																					'action' => 'card',
																																					'gridmod' => null,
																																					'user_id' => ''), null, true) . '{{MID}}'.'">'. '{{fio}}</a>'),
																																					
                'login' => array('title' => _('Логин')),
                'email' => array(
                    'title' => _('Email'),
                    'callback' => array(
                        'function' => array($this, 'updateEmail'),
                        'params' => array('{{email}}', '{{email_confirmed}}', $this->getService('Option')->getOption('regValidateEmail'))
                    )
                ),
                'departments' => array(
                    'title' => _('Подразделение'),
                    'callback' => array(
                        'function' => array($this, 'departmentsCache'),
                        'params' => array('{{departments}}', $select)
                    )
                ),
                'positions' => array(
                    'title' => _('Должность'),
                    'callback' => array(
                        'function' => array($this, 'departmentsCache'),
                        'params' => array('{{positions}}', $select, true)
                    )
                ),
                'roles' => array('title' => _('Роли')),
                'date_registered' => array('title' => _('Дата регистрации')),
                'status' => array('title' => _('Статус')),
                'ldap' => array('hidden' => true),
                'tags' => array(
                    'title' => _('Метки')
                ),
				'organization' => array(
                    'title' => _('Организация')
                ),
				
            ),
            array('fio' => null,
                'login' => null,
                'departments' =>
                    array(
                        'callback' => array(
                            'function'=>array($this, 'deparmentFilter'),
                            'params'=>array()
                        )
                    ),
                'positions' =>
					array(
						'callback' => array(
							'function'=>array($this, 'positionFilter'),
							'params'=>array()
						)
					),
                'email' => null,
                'date_registered' => array('render' => 'DateSmart'),
                'roles' =>
                    array('values' => $roles,
                        'callback' => array(
                            'function'=>array($this, 'roleFilter'),
                            'params'=>array()
                    )
                ),//array('values' => $roles),
                'status' => array('values' => array(0 => _('Активный'), 1 => _('Заблокирован'))),
                'tags' => array('callback' => array('function' => array($this, 'filterTags'))),				
				'organization' => null,
				/* 'organization' => 
				    array(
                        'callback' => array(
                            'function'=>array($this, 'updateOrganisation'),
                            'params'=>array('{{organisation}}')
						)	
                    ), */
            ),
            'grid',
            'notempty'
        );
	
        $grid->beHappy();

        $grid->addAction(array(
            'module' => 'user',
            'controller' => 'list',
            'action' => 'edit'
        ),
            array('MID'),
            $this->view->icon('edit')
        );

        $grid->addAction(array(
            'module' => 'user',
            'controller' => 'list',
            'action' => 'delete'
        ),
            array('MID'),
            $this->view->icon('delete')
        );

        $grid->addAction(array(
            'module' => 'message',
            'controller' => 'send',
            'action' => 'index'
        ),
            array('MID'),
            _('Отправить сообщение')
        );

        $grid->addAction(array(
            'module' => 'user',
            'controller' => 'list',
            'action' => 'login-as'
        ),
            array('MID'),
            _('Войти от имени пользователя'),
            _('Вы действительно хотите войти в систему от имени данного пользователя? При этом все функции Вашей текущей роли будут недоступны. Вы сможете вернуться в свою роль при помощи обратной функции "Выйти из режима". Продолжить?') // не работает??
        );

        $grid->addMassAction(array('action' => 'index'), _('Выберите действие'));
        $grid->addMassAction(array('action' => 'delete-by'), _('Удалить'), _('Вы подтверждаете удаление отмеченных пользователей? При этом будут удалены все данные о пользователях, включая статистику обучения.'));
        $grid->addMassAction(array('action' => 'block'), _('Заблокировать'), _('Вы подтверждаете блокировку отмеченных пользователей? При этом для данных пользователей будет закрыт доступ к системе, но все данные о пользователе сохранятся.'));
        $grid->addMassAction(array('action' => 'unblock'), _('Разблокировать'));
        $grid->addMassAction(array('action' => 'assign'), _('Назначить роль'));
        $grid->addMassAction(array('action' => 'unassign'), _('Отменить назначение роли'));
        $grid->addMassAction(array('action' => 'assign-tag'), _('Назначить метку'));
        $grid->addMassAction(array('action' => 'unassign-tag'), _('Отменить назначение метки'));
        $grid->addMassAction(array('action' => 'send-confirmation'), _('Выслать письмо для подтверждения Email-адреса'));
        $grid->addMassAction(array('action' => 'set-confirmed'), _('Подтвердить Email-адрес'));
        $grid->addMassAction(array('module' => 'message',
                                   'controller' => 'send',
                                   'action' => 'index'/*,
                                   'subject' => $subject,
                                   'subject_id' => $subjectId*/),
                             _('Отправить сообщение'));


        $grid->updateColumn('tags', array(
            'callback' => array(
                'function'=> array($this, 'displayTags'),
                'params'=> array(
                    '{{MID}}',
                    $this->getService('TagRef')->getUserType(),
                    $grid
                )
            )
        ));

        $grid->updateColumn('status',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateStatus'),
                    'params' => array('{{status}}')
                )
            )
        );

        $grid->updateColumn('fio',
            array(
                'callback' =>
					array(
						'function' => array($this, 'updateName'),
						'params' => array('{{fio}}', '{{MID}}')
					)
            )
        );

        $grid->updateColumn('roles',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateRole'),
                    'params' => array('{{MID}}', $grid)
                )
            )
        );

        $grid->updateColumn('date_registered', array(
            'format' => array(
                'DateTime',
                array('date_format' => Zend_Locale_Format::getDateTimeFormat())
            ),
            'callback' => array(
                'function' => array($this, 'updateDate'),
                'params' => array('{{date_registered}}')
            )
        )
        );
		
        $grid->updateColumn('organization',
            array(
                'callback' =>
					array(
						'function' => array($this, 'updateOrganization'),
						'params' => array('{{organization}}')
					)
            )
        );		

        $grid->setActionsCallback(
            array(
                'function' => array($this, 'updateActions'),
                'params'   => array('{{ldap}}')
            )
        );

        unset($roles[HM_Role_RoleModelAbstract::ROLE_USER]);
        unset($roles[HM_Role_RoleModelAbstract::ROLE_STUDENT]);

        $assignableRoles = array();
        foreach ($roles as $key => $role) {
            if (($key !== 'ISNULL') && !$this->getService('Acl')->inheritsRole($key, HM_Role_RoleModelAbstract::ROLE_ENDUSER)) {
                $assignableRoles[$key] = $role;
            }
        }

        $grid->addSubMassActionSelect(array($this->view->url(array('action' => 'assign'))),
                                     'role[]',
                                      $assignableRoles);
        $grid->addSubMassActionSelect(array($this->view->url(array('action' => 'unassign'))),
                                     'role[]',
                                      $assignableRoles);
        $grid->addSubMassActionFcbk(array($this->view->url(array('action' => 'assign-tag'))),
                                     'tags');
        $grid->addSubMassActionFcbk(array($this->view->url(array('action' => 'unassign-tag'))),
                                     'tags');

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid->deploy();


    }

    public function deparmentFilter($data)
    {

        $field = $data['field'];
        $value = $data['value'];
        $select = $data['select'];

        // Только больше 4 символов чтобы много не лезло в in
        if(strlen($value) > 4){
            $fetch = $this->getService('Orgstructure')->fetchAll(array('name LIKE LOWER(?)' => "%" . $value . "%"));

            $data = $fetch->getList('soid', 'name');
            $select->where('d.owner_soid IN (?)', array_keys($data));
        }
    }

    public function roleFilter($data){
        $value=$data['value'];
        $select=$data['select'];
        if (!empty($value)){
            $select->joinInner(array('rs'=>'roles_source'),$this->quoteInto('rs.MID = t1.MID AND rs.role = ?', $value),array());
        }
    }

    public function positionFilter($data)
    {

        $field = $data['field'];
        $value = $data['value'];
        $select = $data['select'];

        // Только больше 4 символов чтобы много не лезло в in
        if(strlen($value) > 4){
            $fetch = $this->getService('Orgstructure')->fetchAll(array('name LIKE LOWER(?)' => "%" . $value . "%"));

            $data = $fetch->getList('soid', 'name');
            $select->where('d.soid IN (?)', array_keys($data));
        }
    }





    /**
     * Экшен снятия ролей с юзера
     */
    public function unassignAction()
    {
        $arRoles = HM_Role_RoleModelAbstract::getBasicRoles();
        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));
        $roles = $this->_request->getParam('role',array());
        $service = $this->getService('User');

        foreach ($ids as $value) {
            foreach ( $roles as $role) {
                if ( array_key_exists($role, $arRoles)) {
                    $service->removalRole($value, $role);
                }
            }
        }

        $this->_flashMessenger->addMessage(_('Роли успешно убраны'));
        $this->_redirector->gotoSimple('index', 'list', 'user');
    }


    /**
     * Экшн для присваивания ролей
     */
    public function assignAction() {

        $roles = HM_Role_RoleModelAbstract::getBasicRoles();
        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));
        $role = $this->_request->getParam('role');
        $service = $this->getService('User');
        // Флаг, есть ли ошибки
        $error = false;
        foreach ($ids as $value) {
            $res = $service->assignRole($value, $role);
            if ($res === false) {
                $error = true;
            }
        }
        if ($error === true) {
            $this->_flashMessenger->addMessage(_('Некоторым пользователям уже была присвоена роль '));
        } else {
            $this->_flashMessenger->addMessage(_('Пользователям успешно добавлена роль '));
        }
        $this->_redirector->gotoSimple('index', 'list', 'user');

    }


    /**
     * Экшн для блокировки
     */
    public function blockAction() {
        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));
        // Нельзя заблокировать себя
        if ($key = array_search($this->getService('User')->getCurrentUserId(), $ids)) {
            unset($ids[$key]);
        }

        $array = array('blocked' => 1);
        $res = $this->getService('User')->updateWhere($array, array('MID IN (?)' => $ids));
        if ($res > 0) {
            $this->_flashMessenger->addMessage(_('Пользователи успешно заблокированы!'));
            $this->_redirector->gotoSimple('index', 'list', 'user');
        } else {
            $this->_flashMessenger->addMessage(_('Произошла ошибка во время блокировки пользователей!'));
            $this->_redirector->gotoSimple('index', 'list', 'user');
        }
    }

    /**
     * Экшн для разблокировки
     */
    public function unblockAction() {
        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));

        $array = array('blocked' => 0);

        $res = $this->getService('User')->updateWhere($array, array('MID IN (?)' => $ids));
        if ($res > 0) {
            $url = 'http://' . $_SERVER['SERVER_NAME'] . Zend_Registry::get('config')->url->base;
            $this->getService('User')->notifyUserUnblock(
                array(
                    'id' => $ids,
                    'placeholders' => array(
                        'URL' =>  '<a href="' . $url . '">' . $url . '</a>'
                    ),
                )
            );
            $this->_flashMessenger->addMessage(_('Пользователи успешно разблокированы!'));
            $this->_redirector->gotoSimple('index', 'list', 'user');
        } else {
            $this->_flashMessenger->addMessage(_('Произошла ошибка во время разблокировки пользователей!'));
            $this->_redirector->gotoSimple('index', 'list', 'user');
        }
    }

    /**
     * Экшн для удаления
     */
    public function deleteAction() {
        $userId = (int) $this->_getParam('MID', 0);
        if ($userId) {
            if ($userId == $this->getService('User')->getCurrentUserId()) {
                $this->_flashMessenger->addMessage(_('Вы не можете удалить сами себя'));
                $this->_redirector->gotoSimple('index', 'list', 'user');
            }
            $this->getService('User')->delete($userId);
        }
        $this->_flashMessenger->addMessage(_('Пользователь успешно удалён'));
        $this->_redirector->gotoSimple('index', 'list', 'user');
    }

    /**
     * Экшн для массового удаления
     */
    public function deleteByAction() {
        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));
        $service = $this->getService('User');
        foreach ($ids as $value) {

            if ($value != $this->getService('User')->getCurrentUserId()) {
                $service->delete(intval($value));
            } else {
                $this->_flashMessenger->addMessage(_('Вы не можете удалить себя!'));
                $this->_redirector->gotoSimple('index', 'list', 'user');
            }
        }
        $this->_flashMessenger->addMessage(_('Пользователи успешно удалены'));
        $this->_redirector->gotoSimple('index', 'list', 'user');
    }

    /**
     * Экшн для создания пользователя
     */
    public function newAction() {
        $form = new HM_Form_User();

        if ($this->_getParam('generatepassword', 0) == 1) {
            $password = $this->getService('User')->getRandomString();
            $this->_setParam('userpassword', $password);
            $this->_setParam('userpasswordrepeat', $password);
        }

        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getParams())) {
                $array = array('login' => $form->getValue('userlogin'),
                    //'password' => $form->getValue('userpassword'),
                    'firstname' => $this->filterString($form->getValue('firstname')),
                    'lastname' => $this->filterString($form->getValue('lastname')),
                    'patronymic' => $this->filterString($form->getValue('patronymic')),
                    'email' => $form->getValue('email'),
                    'blocked' => $form->getValue('status')
                );

                $array += array('Password' => new Zend_Db_Expr("PASSWORD(" . $this->getService('User')->getSelect()->getAdapter()->quote($form->getValue('userpassword')) . ")"));

                if (null !== $form->getValue('mid_external')) {
                    $array+= array('mid_external' => $form->getValue('mid_external'));
                }

                if (null !== $form->getValue('lastnameLat')) {
                    $array+= array('LastNameLat' => $form->getValue('lastnameLat'));
                }

                if (null !== $form->getValue('firstnameLat')) {
                    $array+= array('FirstNameLat' => $form->getValue('firstnameLat'));
                }

                if (null !== $form->getValue('gender')) {
                    $array+= array('Gender' => $form->getValue('gender'));
                }

                // нет необходимости использовать Metadata для сохранения одного лишь телефона
                // пишем и в Phone и в Information для обратной совместимости
                // правильное поле - Phone
                if (null !== $form->getValue('tel')) {
                    $array+= array('Phone' => $form->getValue('tel'));
                }

                if (null !== $form->getValue('tel2')) {
                    $array+= array('CellularNumber' => $form->getValue('tel2'));
                }

                $yearOfBirth = $form->getValue('year_of_birth');
                if (!empty($yearOfBirth)) {
                    $array+= array('BirthDate' => $form->getValue('year_of_birth') . '-01-01');
                }

                $user = $this->getService('User')->insert($array);
                $claimant = $this->getService('Claimant')->updateClaimant();
                // Добавляем метаданные
                if ($user) {
                    $user->setMetadataValues(
                        $this->getService('User')->getMetadataArrayFromForm($form)
                    );

                    $user = $this->getService('User')->update($user->getValues());

                    $classifiers = $form->getClassifierValues();
                    $this->getService('Classifier')->unlinkItem($user->MID, HM_Classifier_Link_LinkModel::TYPE_PEOPLE);
                    if (is_array($classifiers) && count($classifiers)) {
                        foreach($classifiers as $classifierId) {
                            if ($classifierId > 0) {
                                $this->getService('Classifier')->linkItem($user->MID, HM_Classifier_Link_LinkModel::TYPE_PEOPLE, $classifierId);
                            }
                        }
                    }

                    $positionName = $form->getValue('position_name');
                    if ($ownerSoid = $form->getValue('position_id')) {
                        $this->getService('Orgstructure')->assignUser($user->MID, $ownerSoid, $positionName);
                    }
                    
                }

                // Обрабатываем фотку
                $photo = $form->getElement('photo');
                if($photo->isUploaded()){
                    $path = $this->getService('User')->getPath(Zend_Registry::get('config')->path->upload->photo, $user->MID);
                    $photo->addFilter('Rename', $path . $user->MID . '.jpg', 'photo', array( 'overwrite' => true));
                    $photo->receive();
                    $img = PhpThumb_Factory::create($path . $user->MID . '.jpg');
                    $img->resize(HM_User_UserModel::PHOTO_WIDTH, HM_User_UserModel::PHOTO_HEIGHT);
                    $img->save($path . $user->MID . '.jpg');
                }

                // Добавляем роль если такая существует
                if (array_key_exists($form->getValue('role'), HM_Role_RoleModelAbstract::getBasicRoles(false))) {
                    $this->getService('User')->assignRole($user->MID, $form->getValue('role'));
                }

                // метки
                $tags = array_unique($form->getParam('tags', array()));
                $this->getService('Tag')->update($tags, $user->MID, $this->getService('TagRef')->getUserType());


                if ($user) {

                    // Шлём сообщение о регистрации
                    $messenger = $this->getService('Messenger');
                    $messenger->setOptions(
                        HM_Messenger::TEMPLATE_REG,
                        array(
                            'login' => $user->Login,
                            'password' => $form->getValue('userpassword')
                        )
                    );
                    $messenger->send(HM_Messenger::SYSTEM_USER_ID, $user->MID);
                }

                $this->_flashMessenger->addMessage(_('Учётная запись создана успешно!'));
                $this->_redirector->gotoSimple('index', 'list', 'user');
            } else
            {
                $form->populate($this->_request->getParams());
            }
        }

        $this->view->form = $form;
    }

    private function _prepareEditForm(Zend_Form $form, $user)
    {
        $elem = $form->getElement('generatepassword');
        $elem->setValue(false);

        $elem = $form->getElement('userpassword');
        $elem->setOptions(array('Required' => false));

        //removeValidator
        $elem = $form->getElement('userlogin');
        $elem->removeValidator('Db_NoRecordExists');

        $elem->addValidator('Db_NoRecordExists', false, array('table' => 'People',
            'field' => 'Login',
            'exclude' => array(
                'field' => 'MID',
                'value' => $user->MID
            )
        )
        );

        $elem = $form->getElement('user_id');
        $elem->addValidator('Db_RecordExists', false, array('table' => 'People',
            'field' => 'MID'
        )
        );

        if ($this->_getParam('generatepassword') == 1) {
            $password = $this->getService('User')->getRandomString();
            $this->_setParam('userpassword', $password);
            $this->_setParam('userpasswordrepeat', $password);
        }

        // Убираем редактирование логина и пароля для пользователя из AD
        if ($user->isAD) {
            $user->prepareFormLdap($form, $this);
        }

    }

    /**
     * Экшн для редактирования пользователя
     */
    public function editAction() {
        $userId = (int) $this->_request->getParam('MID', 0);

        $user = $this->getOne($this->getService('User')->find($userId));
        if (!$user) {
            $this->_flashMessenger->addMessage(_('Пользователь не найден'));
            $this->_redirector->gotoSimple('index', 'list', 'user');
        }

        $form = new HM_Form_User();

        $this->_prepareEditForm($form, $user);

        if ($this->_request->isPost()) {

            if ($form->isValid($this->_request->getParams())) {

                $disabledArray = ($user->isAD)? $user->getLdapDisabledFormFields() : array();

                $array = array(
                    'MID' => $userId,
                    'email' => $form->getValue('email'),
                    'blocked' => $form->getValue('status'),
                    'need_edit' => 0
                );

                $formValuesArray = array (
                   'userlogin' => 'Login',
                   'firstname' => 'FirstName',
                   'lastname' => 'LastName',
                   'patronymic' => 'Patronymic',
                   'userpassword' => 'Password',
                   'mid_external' => 'mid_external',
                   'gender' => 'gender',
                   'lastnameLat' => 'LastNameLat',
                   'firstnameLat' => 'FirstNameLat',
                   'tel' => 'Phone',
                   'tel2' => 'CellularNumber',
                   'year_of_birth' => 'BirthDate',
                );

                $checkNotEmpty = array (
                   'lastnameLat' => 'LastNameLat',
                   'firstnameLat' => 'FirstNameLat',
                );

                $checkedValuesArray = array_intersect_key(
                    $formValuesArray,
                    array_flip(array_diff(array_keys($formValuesArray), $disabledArray))
                );
                foreach ($checkedValuesArray as $field => $title) {
                    if (null !== ($value = $form->getValue($field))) {
                        /**
                         * @todo Нужно избавиться от следующей проверки.
                         */
                        if (in_array($field, $checkNotEmpty)) {
                            if (!strlen($value)) {
                                continue;
                            }
                        }
                        $array[$title] = $value;
                    }
                }

                //шифруем пароль
                if (empty($array['Password']))
                    unset($array['Password']);
                else
                    $array['Password'] = new Zend_Db_Expr("PASSWORD('" . $array['Password'] . "')");

                if (isset($array['BirthDate'])) {
                    $array['BirthDate'] .= '-01-01';
                }

                $isUserActivated = $user->blocked ? !$array['blocked'] : false;
                $user = $this->getService('User')->update($array);
                if ($isUserActivated) {
                    $url = 'http://' . $_SERVER['SERVER_NAME'] . Zend_Registry::get('config')->url->base;
                    $this->getService('User')->notifyUserUnblock(
                        array(
                            'id' => $userId,
                            'placeholders' => array(
                                'URL' =>  '<a href="' . $url . '">' . $url . '</a>'
                            ),
                        )
                    );
                }
                $claimant = $this->getService('Claimant')->updateClaimant();
                //print_r($claimant); exit;

                // Сохраняем метаданные
                if ($user) {
                    
// DEPRECATED!
//                     $user->setMetadataValues(
//                         $this->getService('User')->getMetadataArrayFromForm($form)
//                     );

//                     $user = $this->getService('User')->update($user->getValues());

                    $classifiers = $form->getClassifierValues();
                    $this->getService('Classifier')->unlinkItem($user->MID, HM_Classifier_Link_LinkModel::TYPE_PEOPLE);
                    if (is_array($classifiers) && count($classifiers)) {
                        foreach($classifiers as $classifierId) {
                            if ($classifierId > 0) {
                                $this->getService('Classifier')->linkItem($user->MID, HM_Classifier_Link_LinkModel::TYPE_PEOPLE, $classifierId);
                            }
                        }
                    }
                    
                    $positionName = $form->getValue('position_name');
                    if ($ownerSoid = $form->getValue('position_id')) {
                        $this->getService('Orgstructure')->assignUser($user->MID, $ownerSoid, $positionName);
                    }
                }

                // Обрабатываем фотку
                $photo = $form->getElement('photo');
                if($photo->isUploaded()){
                    $path = $this->getService('User')->getPath(Zend_Registry::get('config')->path->upload->photo, $user->MID);
                    $photo->addFilter('Rename', $path . $user->MID . '.jpg', 'photo', true);
                    unlink($path . $user->MID . '.jpg');
                    $photo->receive();
                    $img = PhpThumb_Factory::create($path . $user->MID . '.jpg');
                    $img->resize(HM_User_UserModel::PHOTO_WIDTH, HM_User_UserModel::PHOTO_HEIGHT);
                    $img->save($path . $user->MID . '.jpg');
                }

                //Обрабатываем область ответственности

                /*
                 * Этот код не нужен, так так область ответственности теперь редактируется в отдельной форме
                 *
                if($user->MID > 0
                    && $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ADMIN)
                   //&& $this->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_ADMIN
                   && $this->getService('User')->isRoleExists($user->MID, HM_Role_RoleModelAbstract::ROLE_DEAN)){
                    $this->getService('Dean')->setResponsibilityOptions(array(
                                                                             'user_id' => (int) $user->MID,
                                                                             'unlimited_subjects' => $form->getValue('unlimited'),
                                                                             'unlimited_classifiers' => $form->getValue('unlimited'),
                                                                             'assign_new_subjects' => $form->getValue('unlimited')
                                                                        ));
                }
                */

                // метки
                $tags = array_unique($form->getParam('tags', array()));
                $this->getService('Tag')->update($tags, $user->MID, $this->getService('TagRef')->getUserType());

                if ($user && !empty($array['Password'])) {
                    // Шлём сообщение о смене пароля
                    $messenger = $this->getService('Messenger');
                    $messenger->setOptions(
                        HM_Messenger::TEMPLATE_PASS,
                        array(
                            'login' => $user->Login,
                            'password' => $form->getValue('userpassword')
                        )
                    );
                    $messenger->send(HM_Messenger::SYSTEM_USER_ID, $user->MID);
                }

                $this->_flashMessenger->addMessage(_('Учётная запись отредактирована успешно!'));
                $this->_redirector->gotoUrl($form->getValue('cancelUrl'));

            } else {
                $elem = $form->getElement('photo');
                $elem->setOptions(array('user_id' => $userId));
                // $form->populate($arr);
                $arr = array(
                    'userlogin' => $user->Login,
                    'userpassword' => $user->Password,
                    'firstname' => $user->FirstName,
                    'lastname' => $user->LastName,
                    'patronymic' => $user->Patronymic,
                    'firstnameLat' => $user->FirstNameLat,
                    'lastnameLat' => $user->LastNameLat,
                    'email' => $user->EMail,
                    'status' => $user->blocked,
                    'user_id' => $userId,
                    'mid_external' => $user->mid_external
                );

                // чтобы тэги не сбрасывались после неуспешной валидации
                $post = $this->_request->getParams();
                $post['tags'] = $this->getService('Tag')->convertAllToStrings($post['tags']);
                $form->populate(array_merge($arr, $post));

            }
        } else {

            $date = strtotime($user->BirthDate);
            $birthDate = date('Y', $date);
            $arr = array(
                'userlogin' => $user->Login,
                'userpassword' => $user->Password,
                'firstname' => $user->FirstName,
                'lastname' => $user->LastName,
                'patronymic' => $user->Patronymic,
                'firstnameLat' => $user->FirstNameLat,
                'lastnameLat' => $user->LastNameLat,
                'gender' => $user->Gender,
                'tel' => $user->Phone,
                'tel2' => $user->CellularNumber,
                'year_of_birth' => $birthDate,
                'email' => $user->EMail,
                'status' => $user->blocked,
                'user_id' => $userId,
                'mid_external' => $user->mid_external
            );

            if ($form->getElement('position_id')) {
                $units = $this->getService('Orgstructure')->fetchAll(array('mid = ?' => $userId));
                if (count($units)) {
                    $arr['position_id'] = $units->current()->soid;
                    $arr['position_name'] = $units->current()->name;
                    //pr($form->getElement('position_id')->g)
                }
            }

// DEPRECATED!
//             $metadata = $user->getMetadataValues();
//             if (count($metadata)) {
//                 foreach($metadata as $name => $value) {
//                     if (!isset($arr[$name])) { // если поля заданы напрямую и в метаданных, то приоритет имеют первые; #11006
//                         $arr[$name] = $value;
//                     }
//                 }
//             }

            $elem = $form->getElement('photo');
            $elem->setOptions(array('user_id' => $userId));
            $form->populate($arr);
        }
        $this->view->form = $form;
    }


    /**
     * Экшн для обзора пользователя
     */
    public function viewAction() {

        if ($this->isAjaxRequest()) {
            $this->_helper->getHelper('layout')->disableLayout();
            Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        }
        //$this->getResponse()->setHeader('Content-type', 'text/html; charset=' . Zend_Registry::get('config')->charset);

        $userId = $this->_getParam('user_id', 0);
        $subjectId = $this->_getParam('subject_id', 0);
        $user = $this->getOne($this->getService('User')->find($userId));
		
		$isStudent = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),array(HM_Role_RoleModelAbstract::ROLE_STUDENT));
		
		if(
			$isStudent
			&&
			$this->getService('User')->getCurrentUserId() != $userId
			&&
			!$this->getService('User')->isRoleExists($userId, HM_Role_RoleModelAbstract::ROLE_TEACHER)
			&&
			!$this->getService('User')->isRoleExists($userId, HM_Role_RoleModelAbstract::ROLE_TUTOR)		
		){ //--студент может просматривать только свою карточку, тьюторов и преподавателей.
			if ($this->isAjaxRequest()) {
				echo _('Вы не можете просматривать информацию этого пользователя.');
				exit();	
			}
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
					'message' => _('У вас нет доступа к этому разделу'))
			);            
			$this->_redirect('/');        
		}
		
        if ($user) {
            $metaData = $user->getMetadataValues();
            $user->additionalData = $metaData['additional_info'];
			

            if ( $subjectId ) {
                $userGrop = $this->getService('GroupAssign')
                                 ->getOne($this->getService('GroupAssign')
                                               ->fetchAllDependence('Group',
                                                                     array('mid=?'=>$userId,'cid=?' => $subjectId)));
                if( count($userGrop->groups) ) {
                    $userGropsList = array();
                    foreach ($userGrop->groups as $group){
                        $userGropsList[] = $group->name;
                    }
                    $user->currentCourseGroups = implode('<br/>', $userGropsList);
                }
            }

            $userGropsList = $this->getService('StudyGroupUsers')->getUserGroups($user->MID);
            if ($userGropsList) {
                $user->studyGroups = '';
                foreach ($userGropsList as $group) {
                    if ($user->studyGroups == '') {
                        $user->studyGroups = $group['name'];
                    } else {
                        $user->studyGroups .= ', ' . $group['name'];
                    }
                }
            }

        }
        $this->view->data = $user;

    }


    //  Функции для обработки полей в таблице


    /**
     * @param string $field Поле из таблицы
     * @return string Возвращаем статус
     */
    public function updateStatus($field) {
        if ($field == 0) {
            return _('Активный');
        } else {
            return _('Заблокирован');
        }
    }

    public function updateName($name, $userId) {
        $name = trim($name);
        if (!strlen($name)) {
            $name = sprintf(_('Пользователь #%d'), $userId);
        }
		
		if($this->_currentLang == 'eng')
			$name = $this->translit($name);		
		
        return $name;
    }

    public function updateDate($date)
    {
        if (!strtotime($date)) return '';
        return $date;
    }

    public function updateActions($ldap, $actions)
    {
        if ($ldap) {
            $actions = explode('</a>', $actions);  // fucking hardcode
            unset($actions[1]);
            $actions = join('</a>', $actions);
        }

        return $actions;
    }

    public function generateAction() {
        $form = new HM_Form_Generate();

        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getParams())) {
                $this->getService('User')->generate(
                    $form->getValue('number'),
                    $form->getValue('prefix'),
                    $form->getValue('password'),
                    $form->getValue('role')
                );
                $this->_flashMessenger->addMessage(_('Учётные записи сгенерированы успешно!'));
                $this->_redirector->gotoSimple('index', 'list', 'user', array('logingrid' => $form->getValue('prefix'), 'ordergrid' => 'login_ASC'));
            }
        }
        $this->view->form = $form;
    }
	
	public function updateOrganization ($name) {
		
		return _($name);
		
	}

    public function assignTagAction()
    {
        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));
        $tags = array_unique($this->_getParam('tags', array()));

        $tagsCache = $this->getService('Tag')->getTagsCache($ids, $this->getService('TagRef')->getUserType());

        foreach ($ids as $userId) {
            if (!isset($tagsCache[$userId])) $tagsCache[$userId] = array();
            $this->getService('Tag')->update(($tagsCache[$userId] + $tags), $userId, $this->getService('TagRef')->getUserType());
        }
        $this->_flashMessenger->addMessage(_('Метка успешно назначена пользователям'));
        $this->_redirector->gotoSimple('index', 'list', 'user');

    }

    public function unassignTagAction()
    {
        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));
        $tags = array_unique($this->_getParam('tags', array()));

        $tagsCache = $this->getService('Tag')->getTagsCache($ids, $this->getService('TagRef')->getUserType());

        foreach ($ids as $userId) {
            if (!isset($tagsCache[$userId])) $tagsCache[$userId] = array();
            foreach ($tags as $tag) {
                if ($this->getService('Tag')->isNewTag($tag)) continue;
                if (isset($tagsCache[$userId][$tag])) unset($tagsCache[$userId][$tag]);
            }
            $this->getService('Tag')->update($tagsCache[$userId], $userId, $this->getService('TagRef')->getUserType());
        }
        $this->_flashMessenger->addMessage(_('Назначение метки пользователям отменено'));
        $this->_redirector->gotoSimple('index', 'list', 'user');

    }

    public function sendConfirmationAction()
    {
        if (count($ids = explode(',', $this->_request->getParam('postMassIds_grid')))) {
            $users = $this->getService('User')->fetchAll(array('MID IN (?)' => $ids));
            foreach ($users as $user) {

                $hash = $this->getService('User')->getEmailConfirmationHash($user->MID);
                $url = ($_SERVER['HTTPS'] == 'on' ? ' https' : 'http') . '://'. $_SERVER['SERVER_NAME'] . Zend_Registry::get('config')->url->base . $this->view->url(array(
                    'module' => 'user',
                    'controller' => 'reg',
                    'action' => 'confirm-email',
                    'user_id' => $user->MID,
                    'key' => $hash,
                ));
                $url = '<a href="' . $url . '">' . $url . '</a>';

                $messenger = $this->getService('Messenger');
                $messenger->setOptions(
                    HM_Messenger::TEMPLATE_REG_CONFIRM_EMAIL,
                    array(
                        'email_confirm_url' => $url,
                    )
                );
                $messenger->send(HM_Messenger::SYSTEM_USER_ID, $user->MID);

                if ($user->email_confirmed == HM_User_UserModel::EMAIL_CONFIRMED) {
                    $data = $user->getValues();
                    $data['email_confirmed'] = HM_User_UserModel::EMAIL_NOT_CONFIRMED;
                    $this->getService('User')->update($data);
                }
            }

            $this->_flashMessenger->addMessage(_('Письма с подтверждением email-адреса успешно разосланы'));
            $this->_redirector->gotoSimple('index', 'list', 'user');
        }
    }

    public function setConfirmedAction()
    {
        if (count($ids = explode(',', $this->_request->getParam('postMassIds_grid')))) {
            $users = $this->getService('User')->fetchAll(array('MID IN (?)' => $ids));
            foreach ($users as $user) {

                if ($user->email_confirmed == HM_User_UserModel::EMAIL_NOT_CONFIRMED) {
                    $data = $user->getValues();
                    $data['email_confirmed'] = HM_User_UserModel::EMAIL_CONFIRMED;
                    //$data['blocked'] = $this->getService('Option')->getOption('regAutoBlock') ? 1 : 0; // разблокируем если не предусмотрено ручное разблокирование
                    $this->getService('User')->update($data);
                }
            }

            $this->_flashMessenger->addMessage(_('Email-адреса подтверждены'));
            $this->_redirector->gotoSimple('index', 'list', 'user');
        }
    }

    public function filterString($stringInput)
    {
        return mb_convert_case(str_replace(" ","",trim($stringInput)),MB_CASE_TITLE,"UTF-8");
    }
}