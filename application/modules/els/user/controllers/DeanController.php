<?php
class User_DeanController extends HM_Controller_Action_User 
{
    /**
     * Экшн для списка курсов
     */
    public function assignAction() 
    {

        $userId = $this->_getParam('user_id', 0);
        if(!$userId) $userId = $this->getService('User')->getCurrentUserId();
        
        $form = new HM_Form_AssignDean();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                
            	$values = $form->getValues();
                $res = $this->getService('Dean')->deleteSubjectsResponsibilities($userId);

                if (!$values['unlimited_subjects'] ) {
                    if(is_array($values['subjects']) && count($values['subjects'])){
                        foreach($values['subjects'] as $subjectId) {
                            $res = $this->getService('Dean')->addSubjectResponsibility($userId, $subjectId);
                        }
                    }
                }
                else{
                    $this->getService('Dean')->insert(
                        array('MID' => $userId)
                    );
                }

                $classifiers = $form->getClassifierValues();
                $this->getService('DeanResponsibility')->deleteResponsibilities($userId);

                if (!isset($values['unlimited_classifiers'])) {
                    $values['unlimited_classifiers'] = 1;
                }

                if (!$values['unlimited_classifiers'] && is_array($classifiers) && count($classifiers)) {
                    foreach($classifiers as $classifierId) {
                        $res = $this->getService('DeanResponsibility')->addResponsibility($userId, $classifierId);
                    }
                }

                $this->getService('Dean')->setResponsibilityOptions(array(
                                                                         'user_id' => (int) $userId,
                                                                         'unlimited_subjects' => $values['unlimited_subjects'],
                                                                         'unlimited_classifiers' => $values['unlimited_classifiers'],
                                                                         'assign_new_subjects' => $values['assign_new_subjects']
                                                                    ));

                $this->_flashMessenger->addMessage(_('Области ответственности успешно изменены'));
        		$this->_redirector->gotoSimple('assign', 'dean', 'user', array('user_id' => $userId));

            }
        } else {

            $values = $this->getService('Dean')->getResponsibilityOptions($userId);
            $values['subjects'] = $values['classifiers'] = array();

            if(!$this->getService('Dean')->userIsDean($userId)){
                $form->addAttribs(array('onSubmit' => 'return confirm("'._('Вы уверены, что хотите добавить роль организатора обучения данному пользователю?').'")'));
            }

            if($this->getService('Dean')->getSubjectsResponsibilities($userId)){
                $values['subjects'] = $this->getService('Dean')->getAssignedSubjectsResponsibilities($userId)->getList('subid', 'subid');
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

