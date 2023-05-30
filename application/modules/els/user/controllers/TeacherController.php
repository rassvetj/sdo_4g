<?php
class User_TeacherController extends HM_Controller_Action_User
{



    /**
     * Экшн для списка курсов
     */
    public function assignAction()
    {

        $userId = $this->_getParam('user_id', 0);

        $order = $this->_getParam('ordergrid');
        if($order == ''){
            // @todo: есть подозрение, что в Orcale оно работает наоборот
            $this->_setParam('ordergrid', 'status_DESC');
        }

        $select = $this->getService('Subject')->getSelect();


        $subSelect = $this->getService('Subject')->getSelect();

        $subSelect->from(array('Teachers'), array('MID', 'CID'))->where('MID = ?', $userId);

        $select->from(array('s' => 'subjects'), array())
                ->joinLeft(array('d' => $subSelect),
                    's.subid = d.CID',
                    array(
                        'subid' => 's.subid',
                        'name' => 's.name',
                        'status' => 'd.MID'
                    )
                )
                ->group(array('s.subid', 's.name', 'd.MID'));

        // Область ответственности
        $options = $this->getService('Dean')->getResponsibilityOptions($this->getService('User')->getCurrentUserId());
        if($options['unlimited_subjects'] != 1){
            $select->joinInner(array('d2' => 'deans'), 'd2.subject_id = s.subid', array())
                   ->where('d2.MID = ?', $this->getService('User')->getCurrentUserId());
        }
        $grid = $this->getGrid($select,
            array('subid' => array('hidden' => true),
                'name' => array('title' => _('Название'), 'decorator' => $this->view->cardLink($this->view->url(array('module' => 'subject', 'controller' => 'list', 'action' => 'card', 'subject_id' => ''), null, true) . '{{subid}}') . '<a href="'.$this->view->url(array('module' => 'lesson', 'controller' => 'list', 'action' => 'index', 'subject_id' => ''), null, true) . '{{subid}}'.'">'. ' {{name}}</a>'),
                'login' => array('title' => _('Логин')),
                'role' => array('title' => _('Роли')),
                'status' => array('title' => _('Назначен?'))
            ),
            array(
            	'name' => null,
                'status' => array('values' => array( $userId => _('Да'), 'ISNULL' => _('Нет'),)),
            )
        );

        //$grid->addMassAction(array('action' => 'index'), _('Выберите действие'));

        if($this->getService('User')->isRoleExists($userId, HM_Role_RoleModelAbstract::ROLE_TEACHER)){
        	$grid->addMassAction(array('action' => 'do'), _('Назначить курсы'));
        }else{
        	$grid->addMassAction(array('action' => 'do'), _('Назначить курсы'), _('Вы уверены, что хотите назначить пользователя преподавателем?'));
        }
        $grid->addMassAction(array('action' => 'delete'), _('Отменить назначение курсов'), _('Вы подтверждаете отмену назначение курсов?'));


        $grid->updateColumn('status',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateStatus'),
                    'params' => array('{{status}}')
                )
            )
        );

		if ($userId) $grid->setClassRowCondition("'{{status}}' != ''", "selected");

		$grid->addFixedRows($this->_getParam('module'), $this->_getParam('controller'),$this->_getParam('action'), 'subid');
        $grid->updateColumn('fixType', array('hidden' => true));


        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid->deploy();


    }


    /**
     * Экшн для присваивания ответственностей
     */
    public function doAction() {
		$userId = $this->_getParam('user_id', 0);
        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));
        $service = $this->getService('Teacher');

        // Флаг, есть ли ошибки
        $error = false;
        foreach ($ids as $value) {
            if($this->getService('Dean')->isSubjectResponsibility($this->getService('User')->getCurrentUserId(), $value)){
                $res = $service->insert(
                    array(
                    	'MID' => $userId,
                        'CID' => $value
                    )
                );
                if ($res === false) {
                    $error = true;
                }
            }
        }
        if ($error === true) {
            $this->_flashMessenger->addMessage(_('Пользователь уже был назначен на некоторые курсы'));
        } else {
            $this->_flashMessenger->addMessage(_('Курсы успешно добавлены'));
        }
        $this->_redirector->gotoSimple('assign', 'teacher', 'user', array('user_id' => $userId));

    }



    /**
     * Экшн для удаления ответственностей
     */
    public function deleteAction() {
		$userId = $this->_getParam('user_id', 0);
        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));
        $service = $this->getService('Teacher');

        // Флаг, есть ли ошибки
        $error = false;
        foreach ($ids as $value) {
            if($this->getService('Dean')->isSubjectResponsibility($this->getService('User')->getCurrentUserId(), $value)){
                $res = $service->deleteBy(
                    array(
                    	'MID = ?' => $userId,
                        'CID = ?' => $value
                    )
                );
            }
        }
        if ($error === true) {
            $this->_flashMessenger->addMessage(_('На некоторых курсах пользователь не был преподавателем'));
        } else {
            $this->_flashMessenger->addMessage(_('Курсы успешно удалены'));
        }
        $this->_redirector->gotoSimple('assign', 'teacher', 'user', array('user_id' => $userId));

    }



    //  Функции для обработки полей в таблице


    /**
     * @param string $field Поле из таблицы
     * @return string Возвращаем статус
     */
    public function updateStatus($field) {
    	$userId = $this->_getParam('user_id', 0);
    	//pr($field);
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

