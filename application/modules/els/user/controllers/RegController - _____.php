<?php
class User_RegController extends HM_Controller_Action
{
    /**
     * Сохранение данных пользователя, отправка уведомления
     * @param HM_Form_User $form
     * @return null | HM_User_UserModel;
     */
    private function createUser(HM_Form_User $form)
    {
         //Извлекаем из формы регистрации ФИО пользователя
        //$lastName   =   $form->getValue('lastname');
        //$firstName  =   $form->getValue('firstname');
        //$patronymic =   $form->getValue('patronymic');    
        //Делаем запрос в БД(Table)`People` и проверяем существует ли такой пользователь
        //если существует, то кладем в переменную dublicated MID пользователя на которого
        //похож, регистрирующийся пользователь - дубликат
        //$dublicated = $this->getService('User')->checkDublicate($lastName,$firstName,$patronymic);        
        # создаем юзера

        
        $array = array(
                   'Login' => $form->getValue('userlogin'),
                   'FirstName' => $this->FilterString($form->getValue('firstname')),
                   'LastName' => $this->FilterString($form->getValue('lastname')),
                   'LastNameLat' => ((null !== $form->getValue('lastnameLat')) ? $form->getValue('lastnameLat') : ''),
                   'FirstNameLat' => ((null !== $form->getValue('firstnameLat')) ? $form->getValue('firstnameLat') : ''),
                   'Patronymic' => $this->FilterString($form->getValue('patronymic')),
                   'email' => $form->getValue('email'),
				   'Gender' => $form->getValue('gender'),
				   'email_confirmed' => HM_User_UserModel::EMAIL_NOT_CONFIRMED,
                   'Password' => new Zend_Db_Expr("PASSWORD(" . $this->getService('User')->getSelect()->getAdapter()->quote($form->getValue('userpassword')) . ")"),
				   'blocked' => $this->getService('Option')->getOption('regValidateEmail') ? 1 : 0 || $this->getService('Option')->getOption('regAutoBlock') ? 1 : 0,
                   'Registered'     => 0,
                   //'dublicate'      =>((null !==  $dublicated) ?  $dublicated : '')
                );
        
        if ($form->getValue('year_of_birth')) {
            $array['BirthDate'] = $form->getValue('year_of_birth').'-01-01';
        }

        $user = $this->getService('User')->insert($array);

        if ( $user ) {

            // Добавляем в оргструктуру
            if ($form->getValue('position_id')) {
                $this->getService('Orgstructure')->insertUser($user->MID, $form->getValue('position_id'), $form->getValue('position_name'));
            }

            $messenger = $this->getService('Messenger');
            if ($this->getService('Option')->getOption('regValidateEmail')) {
                
                $hash = $this->getService('User')->getEmailConfirmationHash($user->MID);
                // @todo: как определить scheme?
                $url = ($_SERVER['HTTPS'] == 'on' ? ' https' : 'http') . '://'. $_SERVER['SERVER_NAME'] . Zend_Registry::get('config')->url->base . $this->view->url(array(
                    'module' => 'user',        
                    'controller' => 'reg',        
                    'action' => 'confirm-email',        
                    'user_id' => $user->MID,        
                    'key' => $hash,        
                ), NULL, true);
                $url = '<a href="' . $url . '">' . $url . '</a>';
                
                # Шлём письмо о необходимости подтверждения email
                $messenger->setOptions(
                    HM_Messenger::TEMPLATE_REG_CONFIRM_EMAIL,
                    array(
                        'email_confirm_url' => $url,
                    )
                );
            } else {
                # Шлём письмо о регистрации
            $messenger->setOptions(
                HM_Messenger::TEMPLATE_REG,
                array(
                    'login' => $user->Login,
                    'password' => $form->getValue('userpassword')
                )
            );
            }
            $messenger->send(HM_Messenger::SYSTEM_USER_ID, $user->MID);

            # Сохраняем метаданные
            $user->setMetadataValues($this->getService('User')->getMetadataArrayFromForm($form));
            $user = $this->getService('User')->update($user->getValues());

            # сохраняем фото
            $photo = $form->getElement('photo');
            if($photo->isUploaded()) {
                $path = $this->getService('User')->getPath(Zend_Registry::get('config')->path->upload->photo, $user->MID);
                $photo->addFilter('Rename', array('target' => $path . $user->MID . '.jpg', 'overwrite' => true));
                @unlink($path . $user->MID . '.jpg');
                $photo->receive();
                if ($photo->isReceived()) {
                    $img = PhpThumb_Factory::create($path . $user->MID . '.jpg');
                    $img->resize(HM_User_UserModel::PHOTO_WIDTH, HM_User_UserModel::PHOTO_HEIGHT);
                    $img->save($path . $user->MID . '.jpg');
                }
            }
        }

        return $user;
    }

    public function confirmEmailAction()
    {
        $hash = $this->_getParam('key');
        $userId = $this->_getParam('user_id');
        if ($user = $this->getService('User')->checkEmailConfirmationHash($hash, $userId)) {
            $this->getService('User')->updateWhere(array(
                'email_confirmed' => HM_User_UserModel::EMAIL_CONFIRMED,
                'blocked' => $this->getService('Option')->getOption('regAutoBlock') ? 1 : 0, // разблокируем если не предусмотрено ручное разблокирование  
            ), array(
                'MID = ?' => $userId        
            ));
            
            if (!$this->getService('Option')->getOption('regAutoBlock')) {
                $this->_flashMessenger->addMessage(_('Email успешно подтверждён'));

                try {
                    $this->getService('User')->authorize($user->Login, null, false, true); // авторизовать без пароля; сработает только один раз  
                } catch(HM_Exception_Auth $e) {
                    // nope
                }

            } else {
                $this->_flashMessenger->addMessage(array(
                    'type'    => HM_Notification_NotificationModel::TYPE_SUCCESS,
                    'message' => _('Email успешно подтверждён. Вы сможете начать работу с системой после подтверждения администрацией.'))
                );
            }
        } else {
            $this->_flashMessenger->addMessage(array(
                'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Email не подтверждён.'))
            );            
        }
        $this->_redirector->gotoSimple('index', 'index', 'default');
    }

    private function _prepareForm(Zend_Form $form)
    {
        if ($this->getService('Option')->getOption('regUseCaptcha')) {
            $this->addCaptcha($form);
        }
        if ($this->getService('Option')->getOption('regRequireAgreement')) {
            $this->addContractOfferFields($form);
        }
        $form->removeElement('role');
        $form->removeElement('status');
        $form->removeElement('generatepassword');
        $form->removeElement('mid_external');
        $form->removeDisplayGroup('Users3');

        if (!count($this->getService('Orgstructure')->fetchAll(array('type = ?' => HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT)))) {
            $form->removeElement('position_id');
            $form->removeElement('position_name');
            $form->removeDisplayGroup('UserOrgstructure');
            }

        return $form;
    }

    /**
     * Экшен само-регистрации пользователя
     */
    public function selfAction()
    {
        # установка заголовка
        $this->view->setHeader(_('Регистрация нового пользователя'));
        # настройка формы регистрации
        $form = new HM_Form_User();
        $this->_prepareForm($form);

        $elem = $form->getElement('cancelUrl');
        $elem->setOptions(array('Value' => $this->view->url(array(
            'module' => 'default',
            'controller' => 'index',
            'action' => 'index'
        ))));

        $elem = $form->getElement('userpassword');
        $elem->setOptions(array('Required' => true));

        $elem = $form->getElement('userpasswordrepeat');
        $elem->setOptions(array('Required' => true));

        # обработка результатов формы
        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getPost())) {

                $user = $this->createUser($form);

                if ($user) {
                    # Назначаем роль
                    $this->getService('User')->assignRole($user->MID, HM_Role_RoleModelAbstract::ROLE_STUDENT);
                }

                if (!$this->getService('Option')->getOption('regValidateEmail')) {
                $this->_flashMessenger->addMessage(_('Ваша регистрация успешно завершена'));

                # авторизация нового пользователя
                try {
                    $this->getService('User')->authorize($user->Login, $form->getValue('userpassword'));
                } catch(HM_Exception_Auth $e) {
                    // nope
                }

                } else {
                    $this->_flashMessenger->addMessage(array(
                        'type'    => HM_Notification_NotificationModel::TYPE_CRIT,
                        'message' => _('Для завершения регистрации необходимо подтвердить email. На адрес, указанный в форме регистрации, отправлено письмо, содержащее ссылку для подтверждения.'))
                    );
                }
                $this->_redirector->gotoSimple('index', 'index', 'default');
            }
        }

         $this->replaceContractAgreeValue($form);
         $this->view->form = $form;

    }

    public function subjectAction()
    {

        $subjectId = (int) $this->_getParam('subid', $this->_getParam('subject_id', 0));
        $programmId = (int) $this->_getParam('programm_id', 0);

        if (!$subjectId) {
            $this->_flashMessenger->addMessage(_('Не выбран учебный курс для регистрации'));
            $this->_redirector->gotoSimple('index', 'catalog', 'subject');
        }

        $subject = $this->getOne($this->getService('Subject')->find($subjectId));
        if (!$subject) {
            $this->_flashMessenger->addMessage(_('Учебный курс не найден'));
            $this->_redirector->gotoSimple('index', 'catalog', 'subject');
        }

        if (!in_array($subject->reg_type, array(HM_Subject_SubjectModel::REGTYPE_FREE, HM_Subject_SubjectModel::REGTYPE_MODER))) {
            $this->_flashMessenger->addMessage(_('Данный учебный курс не имеет свободной регистрации'));
            $this->_redirector->gotoSimple('index', 'catalog', 'subject');
        }

        // Юзер уже зарегистрирован
        if ($this->getService('User')->getCurrentUserId()) {

            $this->getService('Subject')->assignUser($subjectId, $this->getService('User')->getCurrentUserId());

            if ($subject->reg_type == HM_Subject_SubjectModel::REGTYPE_MODER && $subject->claimant_process_id) {
                $this->_flashMessenger->addMessage(sprintf(_('Ваша заявка на учебный курс "%s" успешно отправлена'), $subject->name));
                $params = array('confirm_id' => null);
            } else {
                $this->_flashMessenger->addMessage(sprintf(_('Вы успешно зарегистрировались на учебный курс "%s"'), $subject->name));
                $params = array('confirm_id' => $subjectId);
            }

            if ($programmId > 0) {
                $this->_redirector->gotoSimple('index', 'list', 'subject', array('switcher'=>'programm'));
            }

            $this->getService('User')->switchRole(HM_Role_RoleModelAbstract::ROLE_STUDENT); // переключаем на слушателя в текущей сессии;
            $messenger = $this->getService('Messenger');
            $messenger->sendAllFromChannels();
            $this->_redirector->gotoSimple('index', 'catalog', 'subject', $params);
        }

        // Юзер не зареген

        $this->view->setHeader(sprintf(_('Регистрация на учебный курс "%s"'), $subject->name));

        $form = new HM_Form_User();
        $this->_prepareForm($form);

        $elem = $form->getElement('cancelUrl');
        $elem->setOptions(array('Value' => $this->view->url(array(
            'module' => 'subject',
            'controller' => 'catalog',
            'action' => 'index'
        ))));

        $elem = $form->getElement('userpassword');
        $elem->setOptions(array('Required' => true));

        $elem = $form->getElement('userpasswordrepeat');
        $elem->setOptions(array('Required' => true));

        $form->addElement('hidden', 'subject_id', array(
            'value' => $subjectId
        ));

        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getPost())) {


                $user = $this->createUser($form);

                if ($user) {
                    # Назначаем на учебный курс
                    $this->getService('Subject')->assignUser($subjectId, $user->MID);
                }

                if ($subject->reg_type == HM_Subject_SubjectModel::REGTYPE_MODER) {
                    $this->_flashMessenger->addMessage(sprintf(_('Ваша заявка на учебный курс "%s" успешно отправлена'), $subject->name));
                    $params = array('confirm_id' => null);
                } else {
                    $this->_flashMessenger->addMessage(sprintf(_('Вы успешно зарегистрировались на учебный курс "%s"'), $subject->name));
                    $params = array('confirm_id' => $subjectId);
                }

                try {
                    $this->getService('User')->authorize($user->Login, $form->getValue('userpassword'));
                } catch(HM_Exception_Auth $e) {
                    // nope
                }
                $messenger = $this->getService('Messenger');
                $messenger->sendAllFromChannels();

                $this->_redirector->gotoSimple('index', 'catalog', 'subject', $params);
            }
        }

        $this->replaceContractAgreeValue($form);
        $this->view->form = $form;

    }

    private function replaceContractAgreeValue($form)
    {
        $agreeCheck = $form->getElement('contract_agree');
        if ($agreeCheck) {
            $agreeCheck->setValue(0);
        }
    }

    private function addCaptcha(HM_Form_User $form)
    {

        $form->addElement('captcha', 'captcha', array(
            'Required' => true,
            'Label' => _('Код подтверждения:'),
            'captcha' => 'Image',
            'separator' => '',
            'captchaOptions' => array(
                'captcha' => 'Image',
                'width' => 145,
                'height' => 45,
                'wordLen' => 6,
                'timeout' => 300,
                'expiration' => 300,
                'font'      => APPLICATION_PATH . '/../public/fonts/ptsans.ttf', // Путь к шрифту
                'imgDir'    => APPLICATION_PATH . '/../public/upload/captcha/', // Путь к изобр.
                'imgUrl'    => Zend_Registry::get('config')->url->base.'upload/captcha/', // Адрес папки с изображениями
                'gcFreq'    => 5,
				'DotNoiseLevel' => HM_Form_Element_Captcha::NOISE_LEVEL,
				'LineNoiseLevel' => HM_Form_Element_Captcha::NOISE_LEVEL,                    
            )
        ));
        $captcha = $form->getElement('captcha');
        $captcha->setOrder(5);
        $captcha->setDecorators(array(
            array('RedErrors'),
            array(array('wrapper1' => 'HtmlTag'), array('tag' => 'dd', 'class'  => 'element')),
            array('Label', array('tag' => 'dt')),
            array(array('wrapper2' => 'HtmlTag'), array('tag' => 'div', 'class'  => 'captcha')),
        ));
    }

    private function addContractOfferFields(HM_Form_User $form)
    {

        $form->addElement('checkbox',
        				  'contract_agree',
                           array('onClick' => "if($(this).is(':checked')) {	$('#submit').removeAttr('disabled'); } else {	$('#submit').attr('disabled','disabled') }"));

        $contractTexts = $this->getService('Option')->getOptions(HM_Option_OptionModel::SCOPE_CONTRACT);

        $agreeCheck = $form->getElement('contract_agree');
        $agreeCheck->setLabel('Нажимая кнопку «Сохранить», я соглашаюсь с ' .
                              $this->view->dialogLink('Публичная оферта на оказание образовательных услуг',
                              						   $contractTexts['contractOfferText'],
                              						   'Публичной офертой на оказание образовательных услуг',
                              						   array('width' => 600,
                              						   	     'height' => 600,
                              						         'buttons' => array(_('Распечатать') => "window.open('".$this->view->url(array('module'=>'contract','controller'=>'index','action'=>'print','contract'=>'offer'))."','','');"))) .
                              ' и даю ' .
                              $this->view->dialogLink('Согласие на обработку персональных данных',
                              						   $contractTexts['contractPersonalDataText'],
                              						   'Согласие на обработку моих персональных данных',
                              						   array('width' => 600,
                              						   	     'height' => 600,
                              						         'buttons' => array(_('Распечатать') => "window.open('".$this->view->url(array('module'=>'contract','controller'=>'index','action'=>'print','contract'=>'personal'))."','','');"))));
        $agreeCheck->setRequired(true);
        $agreeCheck->setDecorators($form->getCheckBoxDecorators('contract_agree'));
        $agreeCheck->getDecorator('Label')
                   ->setOptions($agreeCheck->getDecorator('Label')->getOptions() +
                                array('escape' => false));

        $agreeCheck->setOrder(10);

        $form->getElement('submit')->setOrder(20)->setAttrib('disabled', true);
    }

    public function subjectsAction(){

        $postMassIds = $this->_getParam('postMassIds_grid', '');

        if (strlen($postMassIds) && $this->getService('User')->getCurrentUserId()) {
            $ids    = explode(',', $postMassIds);
            $params = array('confirm_id' => null);
            if (count($ids)) {
                foreach($ids as $subjectId) {

                    if (!$subjectId) continue;
                    $subject = $this->getOne($this->getService('Subject')->find($subjectId));
                    if (!$subject) continue;
                    if (!in_array($subject->reg_type, array(HM_Subject_SubjectModel::REGTYPE_FREE, HM_Subject_SubjectModel::REGTYPE_MODER))) continue;
                    $this->getService('Subject')->assignUser($subjectId, $this->getService('User')->getCurrentUserId());

                    if (count($ids) == 1 && $subject->reg_type == HM_Subject_SubjectModel::REGTYPE_MODER && !$subject->claimant_process_id) {
                        $params = array('confirm_id' => $subjectId);
                    }
                }

                if ( $this->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_USER) {
                    $this->getService('User')->switchRole(HM_Role_RoleModelAbstract::ROLE_STUDENT);
                }

                $messenger = $this->getService('Messenger');
                $messenger->sendAllFromChannels();
                $this->_flashMessenger->addMessage(_('Ваши заявки поданы успешно'));
                $this->_redirector->gotoSimple('index', 'catalog', 'subject', $params);
            }
        }

        // заполняем нового юзера

        $this->view->setHeader(_('Регистрация на учебные курсы'));

        $form = new HM_Form_User();
        if ($this->getService('Option')->getOption('regRequireAgreement')) {
        $this->addContractOfferFields($form);
        }
        $form->removeElement('role');
        $form->removeElement('status');
        $form->removeElement('generatepassword');
        $form->removeElement('mid_external');
        $form->removeDisplayGroup('Users3');

        $elem = $form->getElement('cancelUrl');
        $elem->setOptions(array('Value' => $this->view->url(array(
            'module' => 'subject',
            'controller' => 'catalog',
            'action' => 'index'
        ))));

        $elem = $form->getElement('userpassword');
        $elem->setOptions(array('Required' => true));

        $elem = $form->getElement('userpasswordrepeat');
        $elem->setOptions(array('Required' => true));

        $form->addElement('hidden', 'subjects', array(
            'value' => $postMassIds
        ));

        # регим нового юзера
        if ($this->_request->isPost() && !$this->_getParam('postMassIds_grid', '')) {
            if ($form->isValid($this->_request->getPost())) {

                $user = $this->createUser($form);

                if ($user) {
                    # Назначаем на учебный курс
                    $ids = explode(',', $form->getValue('subjects'));
                    $params = array('confirm_id' => null);
                    if (count($ids)) {
                        foreach($ids as $subjectId) {

                            if (!$subjectId) continue;
                            $subject = $this->getOne($this->getService('Subject')->find($subjectId));
                            if (!$subject) continue;
                            if (!in_array($subject->reg_type, array(HM_Subject_SubjectModel::REGTYPE_FREE, HM_Subject_SubjectModel::REGTYPE_MODER))) continue;
                            $this->getService('Subject')->assignUser($subjectId, $user->MID);

                            if (count($ids) == 1 && $subject->reg_type == HM_Subject_SubjectModel::REGTYPE_MODER && !$subject->claimant_process_id) {
                                $params = array('confirm_id' => $subjectId);
                            }
                        }
                    }
                }


                $this->_flashMessenger->addMessage(_('Ваши заявки на учебные курсы успешно отправлены'));

                try {
                    $this->getService('User')->authorize($user->Login, $form->getValue('userpassword'));
                } catch(HM_Exception_Auth $e) {
                    // nope
                }
                $messenger = $this->getService('Messenger');
                $messenger->sendAllFromChannels();
                $this->_redirector->gotoSimple('index', 'catalog', 'subject', $params);
            }
        }

        $this->replaceContractAgreeValue($form);
        $this->view->form = $form;

    }
	public function FilterString($stringInput)
	{
		return mb_convert_case(str_replace(" ","",trim($stringInput)),MB_CASE_TITLE,"UTF-8");
	}
}