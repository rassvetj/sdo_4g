<?php
class Order_UnionController extends HM_Controller_Action
{
    public function indexAction()
    {     	
		$this->view->setHeader(_('Объединение дубликатов'));
		$row = array();
			
		$dubParam = $this->_getParam('dublicate',0);
		
		$midParam = $this->_getParam('MID',0);
		$formDublicate 	= new HM_Form_Dublicate();
		$formUnical 	= new HM_Form_Unical();
		
		$rowUsers = $this->getService('Claimant')->queryDublicate($midParam, $dubParam);
		
		$this->view->formUnical 	= $formUnical;
		$this->view->formDublicate 	= $formDublicate;
			
		$this->view->formUnical 	= $formUnical->populate($rowUsers[0]); 
		$this->view->formDublicate 	= $formDublicate->populate($rowUsers[1]);

		if ($this->_request->isPost()) 
		{
			if ($formUnical->isValid($this->_request->getPost())) 
			{
					$user = $this->getOne($this->getService('User')->find($formUnical->getValue('midunical')));
				
					$resultUpdate = $this->getService('Claimant')->updateUnic($formUnical->getValue('midunical'),$formUnical->getValue('middublicate')); 
					
					$disabledArray = ($user->isAD)? $user->getLdapDisabledFormFields() : array();

					$array = array('MID' => $formUnical->getValue('midunical'),
						'email' => $formUnical->getValue('email'),
						'need_edit' => 0
					);

					if (null !== $formUnical->getValue('userlogin') && !in_array('userlogin', $disabledArray)) {
						$array += array('Login' => $formUnical->getValue('userlogin'));
					}

					if (null !== $formUnical->getValue('firstname') && !in_array('firstname', $disabledArray)) {
						$array += array('FirstName' => $this->FilterString($formUnical->getValue('firstname')));
					}

					if (null !== $formUnical->getValue('lastname') && !in_array('lastname', $disabledArray)) {
						$array += array('LastName' => $this->FilterString($formUnical->getValue('lastname')));
					}

					if (null !== $formUnical->getValue('patronymic') && !in_array('patronymic', $disabledArray)) {
						$array += array('Patronymic' => $this->FilterString($formUnical->getValue('patronymic')));
					}

					if (null !== $formUnical->getValue('gender')  && !in_array('gender', $disabledArray)) {
						$array+= array('Gender' => $formUnical->getValue('gender'));
					}

					if (null !== $formUnical->getValue('year_of_birth')  && !in_array('year_of_birth', $disabledArray)) {
						$array+= array('BirthDate' => $formUnical->getValue('year_of_birth') . '-01-01');
					}

					$user = $this->getService('User')->update($array);
					
					if ($user) 
					{
						$user->setMetadataValues(
							$this->getService('User')->getMetadataArrayFromForm($formUnical)
						);

						$user = $this->getService('User')->update($user->getValues());

						$classifiers = $formUnical->getClassifierValues();
						$this->getService('Classifier')->unlinkItem($user->MID, HM_Classifier_Link_LinkModel::TYPE_PEOPLE);
						if (is_array($classifiers) && count($classifiers)) {
							foreach($classifiers as $classifierId) {
								if ($classifierId > 0) {
									$this->getService('Classifier')->linkItem($user->MID, HM_Classifier_Link_LinkModel::TYPE_PEOPLE, $classifierId);
								}
							}
						}

						// Добавляем в оргструктуру
						if (null !== $formUnical->getValue('position_id')) {
							$this->getService('Orgstructure')->insertUser($user->MID, $formUnical->getValue('position_id'), $formUnical->getValue('position_name'));
						}

					}


					// Обрабатываем фотку
					$photo = $formUnical->getElement('photo');
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

				/*	if($user->MID > 0
						&& $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ADMIN)
					   //&& $this->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_ADMIN
					   && $this->getService('User')->isRoleExists($user->MID, HM_Role_RoleModelAbstract::ROLE_DEAN)){
						$this->getService('Dean')->setResponsibilityOptions(array(
																				 'user_id' => (int) $user->MID,
																				 'unlimited_subjects' => $formUnical->getValue('unlimited'),
																				 'unlimited_classifiers' => $formUnical->getValue('unlimited'),
																				 'assign_new_subjects' => $formUnical->getValue('unlimited')
																			));
					}

					// метки
					$tags = array_unique($formUnical->getParam('tags', array()));
					$this->getService('Tag')->update($tags, $user->MID, $this->getService('TagRef')->getUserType());

					$this->_flashMessenger->addMessage(_('Учётная запись отредактирована успешно!'));
					//$this->_redirector->gotoSimple('index', 'list', 'user');
						*/
						
					/////////////////////////////////////////////////////////////////////////////
					/////////////////////////////////////////////////////////////////////////////			
					$resultDelete = $this->getService('User')->deleteDublicate($formUnical->getValue('middublicate'));	
					$this->getService('Claimant')->updateClaimant();
					$this->_redirector->gotoSimple('index', 'list', 'order');
			}
		}
    }
	public function FilterString($stringInput)
	{
		return mb_convert_case(str_replace(" ","",trim($stringInput)),MB_CASE_TITLE,"UTF-8");
	}
}    