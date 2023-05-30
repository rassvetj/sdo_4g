<?php

class User_EditController extends HM_Controller_Action_User {

    private function _prepareForm(Zend_Form $form, $user) {
        //Удаляем административные поля
        if ($this->getService('Acl')->inheritsRole(
                $this->getService('User')->getCurrentUserRole(),
                array(
                    HM_Role_RoleModelAbstract::ROLE_ENDUSER,
                    HM_Role_RoleModelAbstract::ROLE_TUTOR
                )
        )) {
            $form->removeElement('role');
            $form->removeElement('status');
            $form->removeElement('generatepassword');
            $form->removeElement('mid_external');
            $form->removeDisplayGroup('Users3');

            $form->removeElement('position_id');
            $form->removeElement('position_name');
            $form->removeDisplayGroup('UserOrgstructure');
            
            
            $form->removeElement('userlogin');
            $form->removeElement('generatepassword');
            $form->removeElement('userpassword');
            $form->removeElement('userpasswordrepeat');
            $form->removeElement('tags');
            $form->removeElement('position_name');
            $form->removeDisplayGroup('Users1');
            
            $form->removeElement('mid_external');
            $form->removeElement('lastname');
            $form->removeElement('firstname');
            $form->removeElement('patronymic');
            $form->removeElement('lastnameLat');
            $form->removeElement('firstnameLat');
            $form->removeElement('gender');
            $form->removeElement('year_of_birth');
            $form->removeElement('team');
            $form->removeElement('additional_info');
            $form->removeElement('tel');
            $form->removeElement('tel2');
            $form->removeElement('email');
            
            
        }

        $elem = $form->getElement('cancelUrl');
        $elem->setOptions(array('Value' => $this->view->url(array(
                'module' => 'user',
                'controller' => 'edit',
                'action' => 'card',
                'user_id' => $user->MID
            )))
        );

        $elem = $form->getElement('userpassword');
        if($elem){
            $elem->setOptions(array('Required' => false));
        }
        
        //removeValidator
        $elem = $form->getElement('userlogin');
        if($elem){
            $elem->removeValidator('Db_NoRecordExists');

            $elem->addValidator('Db_NoRecordExists', false, array('table' => 'People',
                'field' => 'Login',
                'exclude' => array(
                    'field' => 'MID',
                    'value' => $user->MID
                )
                    )
            );
        }
        
        $elem = $form->getElement('user_id');
        if($elem){
            $elem->addValidator('Db_RecordExists', false, array('table' => 'People',
                'field' => 'MID'
                    )
            );
        }
        // Убираем редактирование логина и пароля для пользователя из AD
        if ($user->isAD) {
            $user->prepareFormLdap($form, $this);
        }
    }

    public function indexAction() {

        if ($this->_userId != $this->getService('User')->getCurrentUserId()) {
            if (!$this->getService('Acl')->isCurrentAllowed(HM_Acl::RESOURCE_USER_CONTROL_PANEL, HM_Acl::PRIVILEGE_EDIT)) {
                throw new HM_Permission_Exception(_('Не хватает прав доступа.'));
            }
        }

        $userId = $this->_userId;

        $user = $this->getOne($this->getService('User')->findDependence('Position', $userId));
        $arrayPast = array(
            'FirstName' => $user->FirstName,
            'LastName' => $user->LastName,
            'Patronymic' => $user->Patronymic,
            'mid_external' => $user->mid_external,
        );
        if (!$user) {
            $this->_flashMessenger->addMessage(array('message' => _('Пользователь не найден'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
            $this->_redirector->gotoSimple('index', 'index', 'default');
        }

        $form = new HM_Form_User();

        $this->_prepareForm($form, $user);

        // нехороший код, продублирован в ListController
        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getPost())) {
                $disabledArray = ($user->isAD) ? $user->getLdapDisabledFormFields() : array();

                $array = array(
                    'MID' => $userId,
                    'email' => $form->getValue('email'),
                    'need_edit' => 0,
                    'blocked' => $form->getValue('status'),
                );

                $formValuesArray = array(
                    'userlogin' => 'Login',
                    'firstname' => 'FirstName',
                    'lastname' => 'LastName',
                    'patronymic' => 'Patronymic',
                    'userpassword' => 'Password',
                    'mid_external' => 'mid_external',
                   'year_of_birth' => 'BirthDate',
                    'gender' => 'gender',
                    'lastnameLat' => 'LastNameLat',
                    'firstnameLat' => 'FirstNameLat',
                   'tel' => 'Phone',
                   'tel2' => 'CellularNumber',
                );

                $checkNotEmpty = array(
                    'lastnameLat' => 'LastNameLat',
                    'firstnameLat' => 'FirstNameLat',
                );

                $checkedValuesArray = array_intersect_key(
                        $formValuesArray, array_flip(array_diff(array_keys($formValuesArray), $disabledArray))
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

                $user = $this->getService('User')->update($array);
                $claimant = $this->getService('Claimant')->updateClaimant();

                // Сохраняем метаданные
                if ($user) {
// DEPRECATED!
//                     $user->setMetadataValues(
//                         $this->getService('User')->getMetadataArrayFromForm($form)
//                     );

//                     $user = $this->getService('User')->update($user->getValues());

                    if (count($classifiers = $form->getClassifierValues())) {
                        $this->getService('Classifier')->unlinkItem($user->MID, HM_Classifier_Link_LinkModel::TYPE_PEOPLE);
                        if (is_array($classifiers) && count($classifiers)) {
                            foreach ($classifiers as $classifierId) {
                                if ($classifierId > 0) {
                                    $this->getService('Classifier')->linkItem($user->MID, HM_Classifier_Link_LinkModel::TYPE_PEOPLE, $classifierId);
                                }
                            }
                        }
                    }

                    $positionName = $form->getValue('position_name');
                    if ($ownerSoid = $form->getValue('position_id')) {
                    $this->getService('Orgstructure')->assignUser($user->MID, $ownerSoid, $positionName);
                    }

                }

                $photo = $form->getElement('photo');
                if ($photo->isUploaded()) {
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

                // set classifiers
                /*                $classifiers = $form->getClassifierValues();
                  $this->getService('ClassifierLinks')->setClassifiers($userId, HM_Classifier_Link_LinkModel::TYPE_PEOPLE, $classifiers); */

                //Обрабатываем область ответственности - wtf??

                if ($user->MID > 0
                        && $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ADMIN)
                        //&& $this->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_ADMIN
                        && $this->getService('User')->isRoleExists($user->MID, HM_Role_RoleModelAbstract::ROLE_DEAN)) {
                    $this->getService('Dean')->setResponsibilityOptions($user->MID, $form->getValue('unlimited_subjects'), $form->getValue('assign_new'));
                    if ($form->getValue('unlimited_subjects') == 1) {
                        $this->getService('Dean')->deleteBy(array('MID = ?' => $user->MID));
                        $this->getService('Dean')->insert(array('MID' => $user->MID, 'subject_id' => 0));
                    }
                }

                // метки
                // Изменять метки может только администратор
                if ($user->MID > 0
                        && $this->getService('Acl')->inheritsRole(
                                $this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ADMIN)) {

                    $tags = array_unique($form->getParam('tags', array()));
                    $this->getService('Tag')->update($tags, $user->MID, $this->getService('TagRef')->getUserType());
                }

                if ($user && !empty($array['Password'])) {
                    // Шлём сообщение о смене пароля
                    $messenger = $this->getService('Messenger');
                    $messenger->setOptions(
                            HM_Messenger::TEMPLATE_PASS, array(
                        'login' => $user->Login,
                        'password' => $form->getValue('userpassword')
                            )
                    );
                    $messenger->send(HM_Messenger::SYSTEM_USER_ID, $user->MID);
                }

                $user->role = $this->getService('User')->getCurrentUserRole();
                if ($this->getService('User')->getCurrentUserId() == $user->MID) {
                    $this->getService('User')->initUserIdentity($user);
                    $s = new Zend_Session_Namespace('s');
                    $s->login = $user->Login;
                    $s->user['lname'] = $user->LastName;
                    $s->user['fname'] = $user->FirstName;
                    $s->user['patronymic'] = $user->Patronymic;
                    $this->_flashMessenger->addMessage(_('Данные пользователя успешно изменены'));
                    $this->_redirector->gotoSimple('card', 'edit', 'user', array('user_id' => $user->MID));
                } else {
                    $this->_flashMessenger->addMessage(_('Личные данные успешно изменены!'));
                    $this->_redirector->gotoSimple('card', 'edit', 'user', array('user_id' => $user->MID));
                }
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
                    'tel' => $user->Phone,
                    'tel2' => $user->CellularNumber,
                    'status' => $user->blocked,
                    'user_id' => $userId,
                    'mid_external' => $user->mid_external
                );

                if (count($user->positions)) {
                    $position = $user->positions->current();
                    $arr['position_id'] = $position->soid;
                    $arr['position_name'] = $position->name;
                }

                // чтобы тэги не сбрасывались после неуспешной валидации
                $post = $this->_request->getParams();
                $post['tags'] = $this->getService('Tag')->convertAllToStrings($post['tags']);
                $form->populate(array_merge($arr, $post));
            }
        } else {

            $birthDate = new HM_Date($user->BirthDate);
            $arr = array(
                'userlogin' => $user->Login,
                'userpassword' => $user->Password,
                'firstname' => $user->FirstName,
                'lastname' => $user->LastName,
                'patronymic' => $user->Patronymic,
                'firstnameLat' => $user->FirstNameLat,
                'lastnameLat' => $user->LastNameLat,
                'gender' => $user->Gender,
                'year_of_birth' => $birthDate->toString('y'),
                'email' => $user->EMail,
                'tel' => $user->Phone,
                'tel2' => $user->CellularNumber,
                'status' => $user->blocked,
                'user_id' => $userId,
                'mid_external' => $user->mid_external
            );

            if (count($user->positions)) {
                $position = $user->positions->current();
                $arr['position_id'] = $position->soid;
                $arr['position_name'] = $position->name;
            }

            $metadata = $user->getMetadataValues();
            if (count($metadata)) {
                foreach ($metadata as $name => $value) {
                    $arr[$name] = $value;
                }
            }

            $elem = $form->getElement('photo');
            $elem->setOptions(array('user_id' => $userId));
            $form->populate($arr);
            $this->view->user = $user;
        }
        $this->view->form = $form;
    }

    public function cardAction() {
        $role = $this->_request->getParam("role");
        if ($role == 'supervisor') {
            $this->view->addContextNavigationModifier(
                    new HM_Navigation_Modifier_Remove_Page('resource', 'cm:user:page6')
            );
            $this->view->addContextNavigationModifier(
                    new HM_Navigation_Modifier_Remove_Page('resource', 'cm:user:page5_1')
            );
        }
        $user = $this->getOne($this->getService('User')->find($this->_userId));

        $metaData = $user->getMetadataValues();
        $user->additionalData = $metaData['additional_info'];
        $this->view->user = $user;
    }

    public function filterString($stringInput) {
        return mb_convert_case(str_replace(" ", "", trim($stringInput)), MB_CASE_TITLE, "UTF-8");
    }

}

