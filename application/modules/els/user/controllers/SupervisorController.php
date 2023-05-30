<?php
class User_SupervisorController extends HM_Controller_Action_User
{
    /**
     * Экшн для списка курсов
     */
    public function assignAction() 
    {
        //$this->view->addContextNavigationModifier()
        $this->view->addContextNavigationModifier(
            new HM_Navigation_Modifier_Remove_Page('resource', 'cm:user:page5_1')
        );


        $this->view->setHeaderOptions(array(
            'pageTitle' => _('Назначение области ответственности наблюдателю'),
            'panelTitle' => $this->view->getPanelShortname(array('subject' => $this->_subject, 'subjectName' => 'subject')),
        ));

        $userId = $this->_getParam('user_id', 0);
        if(!$userId) $userId = $this->getService('User')->getCurrentUserId();

        $form = new HM_Form_AssignSupervisor();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                
            	$values = $form->getValues();
				

                $this->getService('SupervisorResponsibility')->deleteResponsibilities($userId);


                if ($values['limited'] ) {
                    if(is_array($values['subjects']) && count($values['subjects'])){						
						if(in_array(-1, $values['subjects']))	{	$ids = $this->getService('Subject')->fetchAll()->getList('subid');	}
						else									{	$ids = $values['subjects']; }
						
                        foreach($ids as $subjectId) {
                             $this->getService('SupervisorResponsibility')->addResponsibility($userId, $subjectId,
                                 HM_Role_Supervisor_Responsibility_ResponsibilityModel::SUBJECT_RESPONSIBILITY_TYPE);
                        }
                    }
                    elseif(is_array($values['groups']) && count($values['groups'])){						
						if(in_array(-1, $values['groups']))	{	$ids = $this->getService('StudyGroup')->fetchAll()->getList('group_id');	}
						else								{	$ids = $values['groups']; }
						
                        foreach($ids as $groupId) {
                            $this->getService('SupervisorResponsibility')->addResponsibility($userId, $groupId,
                                HM_Role_Supervisor_Responsibility_ResponsibilityModel::GROUP_RESPONSIBILITY_TYPE);
                        }
                    }
                    elseif(is_array($values['programms']) && count($values['programms'])){
						if(in_array(-1, $values['programms']))	{	$ids = $this->getService('Programm')->fetchAll()->getList('programm_id');	}
						else									{	$ids = $values['programms']; }
						
						foreach($ids as $programmId) {
                            $this->getService('SupervisorResponsibility')->addResponsibility($userId, $programmId,
                                HM_Role_Supervisor_Responsibility_ResponsibilityModel::PROGRAMM_RESPONSIBILITY_TYPE);
                        }
                    }
                    elseif(is_array($values['students']) && count($values['students'])){
                        foreach($values['students'] as $peopleId) {
                            $this->getService('SupervisorResponsibility')->addResponsibility($userId, $peopleId,
                                HM_Role_Supervisor_Responsibility_ResponsibilityModel::STUDENT_RESPONSIBILITY_TYPE);
                        }
                    }
                }


                $this->_flashMessenger->addMessage(_('Области ответственности успешно изменены'));
        		$this->_redirector->gotoSimple('assign', 'supervisor', 'user', array('user_id' => $userId));

            }
        } else {

            $values = array(); //= $this->getService('Dean')->getResponsibilityOptions($userId);
            $values['subjects'] = $values['groups'] =  $values['programms'] = array();
            if( $responsibilityType = $this->getService('SupervisorResponsibility')->getResponsibilityType($userId)){
                $values['limited'] = $responsibilityType;
                        $responsibilities = $this->getService('SupervisorResponsibility')->fetchAll($this->quoteInto(
                            array('user_id = ?'),
                            array($userId)
                        ))->getList('responsibility_id', 'responsibility_id');

                        if($responsibilities){
                    if( $responsibilityType == HM_Role_Supervisor_Responsibility_ResponsibilityModel::SUBJECT_RESPONSIBILITY_TYPE ){
                        $values['subjects'] = $responsibilities;
                    } elseif( $responsibilityType == HM_Role_Supervisor_Responsibility_ResponsibilityModel::GROUP_RESPONSIBILITY_TYPE) {
                        $values['groups'] = $responsibilities;
                    } elseif( $responsibilityType == HM_Role_Supervisor_Responsibility_ResponsibilityModel::PROGRAMM_RESPONSIBILITY_TYPE) {
                        $values['programms'] = $responsibilities;
                    } elseif( $responsibilityType == HM_Role_Supervisor_Responsibility_ResponsibilityModel::STUDENT_RESPONSIBILITY_TYPE) {

                        if (is_array($responsibilities) && count($responsibilities)) {
                            $users = $this->getService('User')->fetchAll(
                                $this->getService('User')->quoteInto('MID IN (?)', $responsibilities),
                                'LastName'
                            );
                            if (count($users)) {
                                $users_fio = array();
                                foreach($users as $user) {
                                    $users_fio[$user->MID] = $user->getName();
                                }
                            }
                            $values['students'] = $users_fio;
                        }
                    }
                }

            }

            $form->populate($values);
        }

        $this->view->form = $form;
    }


    //  Функции для обработки полей в таблице


    /**
     * @param string $field Поле из таблицы
     * @return string Возвращаем статус
     */
    public function updateStatus($field) {
    	$userId = $this->_getParam('user_id', 0);
    	//pr($field);
    	$options = $this->getService('Dean')->getResponsibilityOptions($userId);
    	if($options['unlimited_subjects'] == 1){
            return _('Да');        	    
    	}
        if ($field == $userId) {
            return _('Да');
        } else {
            return _('Нет');
        }
    }

    public function updateName($name, $subjectId) {

        return '<a href="' .
                $this->view->url(
                    array('module' => 'subject',
                        'controller' => 'index',
                        'action' => 'index',
                        'subject_id' => $subjectId
                    )
                ) .
                '">' . $name . '</a>';


    }


}

