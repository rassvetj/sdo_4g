<?php
class Course_ListController extends HM_Controller_Action
{
    //protected $required_permission_level = 3;
    protected $courseCache = array();

    public function indexAction()
    {

        // Функция для преобразования статуса
        $this->showTable('active');

    }

    public function developedAction()
    {

        // Функция для преобразования статуса
        $this->showTable('developed');

    }

    public function activeAction()
    {

        // Функция для преобразования статуса
        $this->showTable('active');

    }

    public function studyonlyAction()
    {

        // Функция для преобразования статуса
        $this->showTable('studyonly');

    }

    public function archivedAction()
    {

        // Функция для преобразования статуса
        $this->showTable('archived');

    }
    
    public function updateGridColumn_Status($status) 
    {
        $statuses = HM_Course_CourseModel::s_getStatuses();
        
        return $statuses[$status];
    }

    /**
     *
     * Показываем таблицу в зависимости от статуса
     * @param unknown_type $status
     * @return unknown|string
     */
    private function showTable($status)
    {

        //Заглушка для даты из-за разного формата.
        // В датагриде своя не работает, потом при подключении пикера нужно убрать
        $params = $this->_request->getParams();

        if ($params['lastUpdateDategrid'] != "")
        {
            $this->_request->setParam('lastUpdateDategrid', $this->dateChanger($params['lastUpdateDategrid']));
        }

        if ($params['archiveDategrid'] != "")
        {
            $this->_request->setParam('archiveDategrid', $this->dateChanger($params['archiveDategrid']));
        }

        if ($params['planDategrid'] != "")
        {
            $this->_request->setParam('activeDategrid', $this->dateChanger($params['planDategrid']));
        }

        if (!$this->_getParam('ordergrid', false) && !$this->isGridAjaxRequest()) {
            $this->_setParam('ordergrid', 'Title_ASC');
        }

        $modelName = $this->getService('Course')->getMapper()->getModelClass();
        $model = new $modelName(null);

        $courses = $this->getService('Course');
        $statusAvail = $model->getStatusAvail();
        $statusSubAvail = $model->getSubStatusAvail();



        if (! in_array($status, $statusAvail))
        {
            $status = 'active';
        }

        $key = array_search($status, $statusAvail);
        
        if ($key === HM_Course_CourseModel::STATUS_ACTIVE) {
            $all = $model->getListSelect(array(
                HM_Course_CourseModel::STATUS_ACTIVE,
                HM_Course_CourseModel::STATUS_STUDYONLY
            ));
        } else {
            $all = $model->getListSelect($key);
        }
        if (/*$status*/$key == HM_Course_CourseModel::STATUS_DEVELOPED) {
            $filterArray = array(
                'Title' => null,
                'provider' => null,/*
                'developStatus' => array(
                    'render' => 'SelectSubStatus'),*/
                'lastUpdateDate' => array(
                    'render' => 'Date'),
                'planDate' => array(
                    'render' => 'Date'),
                'courseFormat' => array('values' => HM_Course_CourseModel::getFormats())
            );

        } elseif (/*$status*/$key == HM_Course_CourseModel::STATUS_ARCHIVED ) {
            $filterArray = array(
                'Title' => null,
                'archiveDate' => array(
                    'render' => 'Date'),
                'courseFormat' => array('values' => HM_Course_CourseModel::getFormats())
            );

        } elseif (/*$status*/$key == HM_Course_CourseModel::STATUS_ACTIVE ) {
            $filterArray = array(
                'Title' => null,
            	'provider' => null,
                'longtime' => null,
                'lastUpdateDate' => array('render' => 'Date'),
                'courseFormat' => array('values' => HM_Course_CourseModel::getFormats()),
                'tags' => array('callback' => array('function' => array($this, 'filterTags')))
            );

        } elseif (/*$status*/$key == HM_Course_CourseModel::STATUS_STUDYONLY ) {
            $filterArray = array(
                'Title' => null,
            	'provider' => null,
                'longtime' => null,
                'lastUpdateDate' => array('render' => 'Date'),
                'courseFormat' => array('values' => HM_Course_CourseModel::getFormats()),
                'tags' => array('callback' => array('function' => array($this, 'filterTags')))
            );

        }

        $filterArray['classifiers'] = null;
        
        $action = 'index';
        if ($status != 'active') $action = $status;
        $grid = $this->getGrid($all, array(
            'CID' => array(
                'hidden' => true),
            'Title' => array(
                'title' => _('Название'),
                'style' => 'width: 500px;',
                'decorator' => $this->view->cardLink($this->view->url(array('action' => 'card', 'course_id' => '')).'{{CID}}', _('Карточка учебного модуля'),'icon','pcard','umcard')." <a href=\"".$this->view->url(array('module' => 'course', 'controller' => 'index', 'action' => 'index', 'course_id' => ''), null, true)."{{CID}}\">{{Title}}</a>"
            ),
            'Status' => array(
                'title' => _('Статус'),
                'style' => 'width: 50px;',
                'callback' => array(
                    'function' => array($this, 'updateGridColumn_Status'),
                    'params' => array('{{Status}}')
                )
            ),
            'provider_id' => array('hidden' => true),
            'provider' => array(
                'title' => _('Поставщик')
            ),
            'planDate' => array(
                'title' => _('Плановая дата публикации')),
            'developStatus' => array(
                'title' => _('Статус разработки')),
            'lastUpdateDate' => array(
                'title' => _('Дата последнего изменения')),
            'archiveDate' => array(
                'title' => _('Дата архивации')),
            'longtime' => array(
                'title' => _('Продолжительность обучения')),
            'courses' => array(
                'title' => _('Используется в учебных курсах')),
            'courseFormat' => array(
                'title' => _('Формат'),
                'callback' => array(
                    'function' => array($this, 'updateFormatColumn'),
                    'params' => array('{{courseFormat}}')
                    )
                ),
            'tags' => array('title' => _('Метки'),
                            'callback' => array(
                                'function'=> array($this, 'displayTags'),
                                'params'=> array('{{tags}}', $this->getService('TagRef')->getCourseType())
                            )),
            'classifiers' => array(
                'title' => _('Классификаторы'),
                'callback' => array(
                    'function' => array($this, 'updateClassifiers'),
                    'params' => array('{{classifiers}}')
                )
            )
        ), $filterArray);

        /*
        $grid->addAction(array(
            'module' => 'course',
            'controller' => 'list',
            'action' => 'view',
            'status' => array(
                'status' => $status)), array(
            'CID'), $this->view->icon('look'));
          */
        $grid->addAction(array(
            'module' => 'course',
            'controller' => 'list',
            'action' => 'edit',
            'status' => array('status' => $status)),
            array('CID'),
            $this->view->icon('edit'));

        if ($status == 'active')
        {
            /*
            $grid->addAction(array(
                'module' => 'course',
                'controller' => 'list',
                'action' => 'archive',
                'status' => array(
                    'status' => $status)), array(
                'CID'), $this->view->icon('archive'));

            $grid->addAction(array(
                'module' => 'course',
                'controller' => 'list',
                'action' => 'develop',
                'status' => array(
                    'status' => $status)), array(
                'CID'), $this->view->icon('develop'));
            */

            if(
                $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_DEAN)
                //$this->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_DEAN
            ){
                $grid->addFixedRows($this->_getParam('module'), $this->_getParam('controller'),$this->_getParam('action'), 'CID');
                $grid->updateColumn('fixType', array('hidden' => true));
            }

            $grid->addMassAction(array(
                'module' => 'course',
                'controller' => 'list',
                'action' => 'archive-by',
                'status' => array(
                    'status' => $status)), _('Назначить статус: архивный'), _('Вы уверены?'));
            $grid->addMassAction(array(
                'module' => 'course',
                'controller' => 'list',
                'action' => 'study-by',
                'status' => array(
                    'status' => $status)), _('Назначить статус: только для использования в учебных курсах'), _('Вы уверены?'));
            $grid->addMassAction(array(
                'module' => 'course',
                'controller' => 'list',
                'action' => 'develop-by',
                'status' => array(
                    'status' => $status)), _('Назначить статус: не опубликован'), _('Вы уверены?'));

            $grid->updateColumn('courses',
                array('callback' =>
                    array('function' => array($this, 'coursesCache'),
                          'params'   => array('{{courses}}', $all)
                    )
                )
            );
        }

        if ($status == 'developed')
        {
/*            $grid->addAction(array(
                'module' => 'course',
                'controller' => 'list',
                'action' => 'public',
                'status' => array(
                    'status' => $status)), array(
                'CID'), $this->view->icon('note2'));*/

            $grid->addMassAction(array(
                'module' => 'course',
                'controller' => 'list',
                'action' => 'public-by',
                'status' => array(
                    'status' => $status)), _('Назначить статус: опубликован'));
            $grid->addMassAction(array(
                'module' => 'course',
                'controller' => 'list',
                'action' => 'study-by',
                'status' => array(
                    'status' => $status)), _('Назначить статус: только для использования в учебных курсах'), _('Вы уверены?'));

        }

        if ($status == 'archived')
        {
/*            $grid->addAction(array(
                'module' => 'course',
                'controller' => 'list',
                'action' => 'develop',
                'status' => array(
                    'status' => $status)), array(
                'CID'), $this->view->icon('develop'));*/

            $grid->addMassAction(array(
                'module' => 'course',
                'controller' => 'list',
                'action' => 'develop-by',
                'status' => array(
                    'status' => $status)), _('Назначить статус: в разработке'), _('Вы уверены?'));
            $grid->addMassAction(array(
                'module' => 'course',
                'controller' => 'list',
                'action' => 'study-by',
                'status' => array(
                    'status' => $status)), _('Назначить статус: только для использования в учебных курсах'), _('Вы уверены?'));

        }

        $grid->addAction(array(
            'module' => 'course',
            'controller' => 'list',
            'action' => 'delete',
            'status' => array('status' => $status)),
            array('CID'),
            $this->view->icon('delete', _('Удалить'), "if (confirm('"._('Вы подтверждаете удаление учебного модуля? Если модуль импортирован в систему, все материалы будут удалены. Если модуль создан в системе на основе ресурсов Базы знаний, соответствующие ресурсы удалены не будут.')."')) return true; return false;"));

        # редактирование конфигов Skillsoft
        $grid->addAction(array(
            'module' => 'course',
            'controller' => 'list',
            'action' => 'editconfig',
            'status' => array('status' => $status)),
            array('CID'=>'crsid'),
            _('Редактировать конфигурационный файл'));

        $grid->setActionsCallback(
                array('function' => array($this,'updateActions'),
                      'params'   => array('{{provider_id}}')
                )
            );
        $grid->addMassAction(array(
            'module' => 'course',
            'controller' => 'list',
            'action' => 'delete-by',
            'status' => array(
                'status' => $status)), _('Удалить'), _('Вы подтверждаете удаление отмеченных модулей? Если модуль импортирован в систему, все материалы будут удалены. Если модуль создан в системе на основе ресурсов Базы знаний, соответствующие ресурсы удалены не будут.'));

        /*  $actions = new Bvb_Grid_Extra_Column();
        $actions->position('right')->name(_('Действия'))->decorator("<a href=\"" . $this->view->url(array(
            'module' => 'course',
            'controller' => 'list',
            'action' => 'edit')) . "/cid/{{CID}}/status/" . $status . "/\">" . $this->view->icon('edit') . "</a>" . " &nbsp; " . "<a href=\"" . $this->view->url(array(
            'module' => 'course',
            'controller' => 'list',
            'action' => 'delete')) . "/cid/{{CID}}/status/" . $status . "/\">" . $this->view->icon('delete') . '</a>');
        $grid->setMassAction(array(
            array(
                'url' => $this->view->url(array(
                    'module' => 'course',
                    'controller' => 'list',
                    'action' => 'massaction',
                    'status' => array(
                        'status' => $status))),
                'caption' => _('Выберите действие')),
            array(
                'url' => $this->view->url(array(
                    'module' => 'course',
                    'controller' => 'list',
                    'action' => 'delete-by',
                    'status' => array(
                        'status' => $status))),
                'caption' => _('Удалить'),
                'confirm' => _('Вы уверены?'))));
        $grid->addExtraColumns($actions);*/

        $grid->updateColumn('developStatus', array(
            'callback' => array(
                'function' => array(
                    $model,
                    'statusFunction'),
                'params' => array(
                    '{{developStatus}}',
                    $statusSubAvail))));

        $grid->updateColumn('lastUpdateDate', array(
            'callback' => array(
                'function' => array(
                    $model,
                    'dateFunction'),
                'params' => array(
                    '{{lastUpdateDate}}'))));
        $grid->updateColumn('planDate', array(
            'callback' => array(
                'function' => array(
                    $model,
                    'dateFunction'),
                'params' => array(
                    '{{planDate}}'))));
        $grid->updateColumn('archiveDate', array(
            'callback' => array(
                'function' => array(
                    $model,
                    'dateFunction'),
                'params' => array(
                    '{{archiveDate}}'))));

        $grid->updateColumn('provider', array(
            'callback' => array(
                'function' => array(
                    $this,
                    'getProviderString'),
                'params' => array(
                    '{{provider_id}}', '{{provider}}')
            )
        ));

        $grid->updateColumn('longtime', array(
            'callback' => array(
                'function' => array(
                    $this,
                    'updateLongtime'),
                'params' => array(
                    '{{longtime}}')
            )
        ));
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid->deploy();
    }


    /**
     * Функция убирает действие "Редактировать конфиг-файл" для не Skillsoft-овских курсов
     * @param int $type
     * @param unknown_type $actions
     * @return Ambiguous|string
     */
    public function updateActions($type, $actions) {
        if ( $type == HM_Provider_ProviderModel::SKILLSOFT ) {
            return $actions;
        } else {
            $tmp = explode('<li>', $actions);
            //unset($tmp[2]);
            // убираем последний элемент массива, где должен быть пункт редактирования конфиг-файла
            array_pop($tmp);
            return implode('<li>', $tmp);
        }
    }

    /**
     * Редакирование конфиг-файла Skillsoft курсов
     */
    public function editconfigAction()
    {
        $cid = ( int ) $this->_getParam('crsid', 0);
        $status = $this->_getParam('status', 'active');
        $data = $this->getOne($this->getService('Course')->fetchAll(sprintf('CID = %d', $cid)));
        $request = $this->getRequest();

        // нет курса в БД
        if ( !$data ) {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
                                                    'message' => _('Модуль не найден.')));

            $this->_redirector->gotoSimple($status, 'list', 'course');
            return ;
        }
        // не Skillsoft
        if ( $data->provider != HM_Provider_ProviderModel::SKILLSOFT) {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
                                                    'message' => _('Действие доступно только для курсов SkillSoft.')));

            $this->_redirector->gotoSimple($status, 'list', 'course');
            return ;
        }
        // не указано имя конфиг файла в БД
        if ( !$data->provider_options ) {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
                                                    'message' => _('Файл не найден.')));
            $this->_redirector->gotoSimple($status, 'list', 'course');
            return ;
        }

        $form = new HM_Form_Config();
        $configFile = APPLICATION_PATH . "/../public/unmanaged/COURSES/course{$data->CID}/{$data->provider_options}";

        // файл есть, но нет прав на запись
        if (file_exists($configFile) && !is_writable($configFile)) {
            $old_mask = umask(0);
            chmod($configFile, 0777);
            umask($old_mask);
            // если бубен не помог
            if (! is_writable($configFile)) {
                $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
                                                    'message' => _('Файл конфигурации не доступен для записи.')));
                $this->_redirector->gotoSimple($status, 'list', 'course');
                return ;
            }
        }

        if ( $request->isPost() ) {
            if ( $form->isValid($request->getParams()) ) {
                $content = $this->_getParam('content','');
                $file = fopen($configFile, "w+");
                $result = fwrite($file, $content);
                fclose($file);

                if ( !$result ) {
                    $this->_flashMessenger->addMessage(_('При сохранении конфигурационного файла произошла ошибка'));
                    $this->_redirector->gotoSimple($status, 'list', 'course');
                    return ;
                } else {
                    $this->_flashMessenger->addMessage(_('Конфигурационный файл успешно сохранен'));
                    $this->_redirector->gotoSimple($status, 'list', 'course');
                    return ;
                }
            } else {
                if ( !file_exists($configFile)) {
                    $fileContent = '';
                } else {
                    $fileContent = file_get_contents($configFile);
                }
                $dataArr = array(
                'cid' => $data->CID,
                'content' => $fileContent);

                $form->populate($dataArr);
                $this->view->form = $form;
            }
        } else {
            # конфиг-файл не существует
            if ( !file_exists($configFile)) {
                $this->_flashMessenger->addMessage(_('Файл-конфигурации курса не существует, он будет создан при сохранении.'));
                $fileContent = '';
            } else {
                $fileContent = file_get_contents($configFile);
            }
            $dataArr = array(
                'cid' => $data->CID,
                'content' => $fileContent);

            $form->populate($dataArr);
            $this->view->form = $form;
        }
    }

    /**
     * Редактирование курса
     */
    public function editAction()
    {

        $cid = ( int ) $this->_getParam('CID', 0);
        if(!$cid) {
            $cid = ( int ) $this->_getParam('course_id', 0);
        }

        $modelName = $this->getService('Course')->getMapper()->getModelClass();
        $model = new $modelName(null);


        $status = $this->_request->getParam('status');
        $form = $this->_prepareForm(new HM_Form_Course());
        $request = $this->getRequest();

        // Если форма валидна то
        if ($request->isPost())
        {
            $form->populate($request->getParams());
            $this->_setRequiredElements($form);
            if ($form->isValid($request->getPost()))
            {

                $emulate = 0;
                $course = $this->getOne($this->getService('Course')->find($form->getValue('cid')));
                if ($course) {
                    $emulate = $course->emulate;
                }

                //Обработка поступающих параметров
                $workdate = $request->getParam('WorkDate');
                $subjects = $request->getParam('subjects');

                if (array_search(0, $subjects) !== false)
                    $subjects = array();

                if (! empty($subjects))
                {
                    $did = ';' . implode(';', $subjects) . ';';
                } else
                {
                    $did = '';
                }

                $is_module_need_check = 0;
                $sequence = 0;
                if ($struct = $request->getParam('struct'))
                {
                    switch ($struct)
                    {
                        case 1 :
                            $is_module_need_check = 0;
                            $sequence = 0;
                            break;
                        case 2 :
                            $is_module_need_check = 1;
                            $sequence = 0;
                            break;
                        case 3 :
                            $is_module_need_check = 0;
                            $sequence = 1;
                            break;
                    }
                }

                $cid = ( int ) $this->_request->getParam('CID', 0);
                $data = array(
                    'CID' => $form->getValue('cid'),
                    'Title' => $form->getValue('name'),
					'title_translation' => $form->getValue('name_translation'),
                    'Description' => $form->getValue('describe'),
					'description_translation' => $form->getValue('describe_translation'),
                    //'Status' => $form->getValue('status'),
					 'Status' => $this->id ? HM_Course_CourseModel::STATUS_STUDYONLY : $form->getValue('status'),
                    //'cBegin' => ($workdate['from'] ? $workdate['from'] : 0),
                    //'cEnd' => ($workdate['to'] ? $workdate['to'] : 0),
                    'provider' => $form->getValue('provider'),
                    'lastUpdateDate' => date('Y-m-d'),
                    'planDate' => $form->getValue('planDate'),
                    'longtime' => $form->getValue('hours'),
                    'typeDes' => $form->getValue('access'),
                    //'chain' => $form->getValue('coordination'),
                    'did' => $did,
                    'sequence' => $sequence,
                    /*'has_tree' => $form->getValue('has_tree'),*/
                    'new_window' => $form->getValue('new_window'),
                    //'emulate' => $form->getValue('emulate'),
                    'is_module_need_check' => $is_module_need_check);

/*                if ($request->getParam('developStatus') != "")
                {
                    $data['developStatus'] = $request->getParam('developStatus');
                }*/
                $course = $this->getService('Course')->update($data);

                if ($course) {
                    $classifiers = $form->getClassifierValues();
                    $this->getService('Classifier')->unlinkItem($course->CID, HM_Classifier_Link_LinkModel::TYPE_COURSE);
                    if (is_array($classifiers) && count($classifiers)) {
                        foreach($classifiers as $classifierId) {
                            if ($classifierId > 0) {
                                $this->getService('Classifier')->linkItem($course->CID, HM_Classifier_Link_LinkModel::TYPE_COURSE, $classifierId);
                            }
                        }
                    }
                }

                $Comp = $this->getService('CourseComp2Course');

                $competences = $request->getParam('competence');

                $this->getService('Tag')->update($form->getParam('tags',array()), $course->CID, $this->getService('TagRef')->getCourseType());

                // Вместо того чтобы делать проверку,просто удаляем старые и добавляем все новые, так меньше ресурсов требуется
                // Операций с диском конечно чуть больше, но пропадает необходимость в проверках
                $Comp->deleteBy($Comp->quoteInto('cid = ?', $cid));
                foreach ( $competences as $value )
                {
                    $Comp->insert(array(
                        'cid' => $cid,
                        'coid' => $value));

                }

                // emulate ie
                try {

                    if ($course && ($emulate != $form->getValue('emulate'))) {
                        $this->getService('Course')->emulate($course->CID, $form->getValue('emulate'));
                    }

                    $this->_flashMessenger->addMessage(_('Учебный модуль успешно изменен'));

                } catch(HM_Exception $e) {
                    if ($course) {
                        // Возвращаем предыдущий emulate
                        $course->emulate = $emulate;
                        $this->getService('Course')->update($course->getValues());
                    }
                    $this->_flashMessenger->addMessage(array('message' => $e->getMessage(), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
                }

                //$this->_redirector->gotoSimple($status, 'list', 'course');
                $this->_redirector->gotoUrl($form->getValue('cancelUrl', $this->view->url(array('module' => 'course', 'controller' => 'list', 'action' => $status))));

            } else
            {

                $arr = $request->getParams();

                // Тут Подготавливаем массив для   populate competens
                // Заполняем поля в случае ошибочного ввода
                $allComp = $this->getService('CourseCompetence')->fetchAll();
                $allComp = $allComp->getList('coid', 'name');
                $competences = $request->getParam('competence');
                $arrayComp = $model->getDiff($allComp, $competences);
                $arr['competents'] = $arrayComp;
                $form->populate($arr);
                $this->view->form = $form;
            }

        } else
        {

            $data = $this->getOne($this->getService('Course')->fetchAll(sprintf('CID = %d', $cid)));

            if ($data->did !== '')
            {
                $did = explode(';', trim($data->did, ';'));
            } else
            {
                $did = array(
                    0);

            }

            if ($data->is_module_need_check == 0 && $data->sequence == 0)
                $struct = 1;
            if ($data->is_module_need_check == 1 && $data->sequence == 0)
                $struct = 2;
            if ($data->is_module_need_check == 0 && $data->sequence == 1)
                $struct = 3;

            // Тут Подготавливаем массив для   populate competens
            $allComp = $this->getService('CourseCompetence')->fetchAll();

            $allComp = $allComp->getList('coid', 'name');
            $comp = $this->getService('CourseComp2Course')->fetchAll(sprintf('cid = %d', $cid));
            if (!empty($comp))
            {
                $competents = $comp->getList('coid', 'coid');

                $arrayComp = $model->getDiff($allComp, $competents);
            } else
            {
                $arrayComp = array(
                    $allComp,
                    array());

            }


            $date= new Zend_Date($data->planDate, 'yyyy-MM-dd');

            $dataArr = array(
                'cid' => $data->CID,
                'name' => $data->Title,
				'name_translation' => $data->title_translation,
                'status' => $data->Status,
                'describe' => $data->Description,
				'describe_translation' => $data->description_translation,
                'provider' => $data->provider,
                'hours' => $data->longtime,
                'planDate' => $date->toString(HM_Locale_Format::getDateFormat()),
                'WorkDate' => array(
                    'from' => $data->cBegin,
                    'to' => $data->cEnd),
                'access' => $data->TypeDes,
                'coordination' => $data->chain,
                'subjects' => $did,
                'struct' => $struct,
                'competents' => $arrayComp,
                /*'has_tree'   => $data->has_tree,*/
                'new_window' => $data->new_window,
                'emulate' => $data->emulate,
                'developStatus' => $data->developStatus,
                'tags' => $this->getService('Tag')->getTags($data->CID, $this->getService('TagRef')->getCourseType()));

            if ($data->Status == HM_Course_CourseModel::STATUS_ACTIVE) {
                $form->removeElement('planDate');
            }

            $form->populate($dataArr);
            $this->_setRequiredElements($form);
            $this->view->form = $form;
        }
    }

    private function _setRequiredElements(Zend_Form $form)
    {
        if (false === strstr($form->getValue('cancelUrl', ''), 'developed')) {
            // пока нет общей политики публикации ресурсов в БЗ - разрешаем всё.
            // $form->getElement('describe')->setRequired(true);
            return HM_Course_CourseModel::STATUS_ACTIVE;
        }
        return HM_Course_CourseModel::STATUS_DEVELOPED;
    }

    private function _setSubjectExtendedView()
    {
        $subjectId = $this->id = (int) $this->_getParam('subject_id', 0);
        $subject = $this->getOne($this->getService('Subject')->find($subjectId));

        $this->view->setExtended(
            array(
                'subjectName' => 'Subject',
                'subjectId' => $subjectId,
                'subjectIdParamName' => 'subject_id',
                'subjectIdFieldName' => 'subid',
                'subject' => $subject
            )
        );
    }

    /**
    * @param Zend_Form $form
    * @return Zend_Form
    */
    private function _prepareForm(Zend_Form $form)
    {
        if (
            $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TEACHER)
            //in_array($this->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_TEACHER))
        ) {

            // Создание учебного модуля под преподавателем
            $subjectId = (int) $this->_getParam('subject_id', 0);

            if (!$this->getService('Teacher')->isUserExists($subjectId, $this->getService('User')->getCurrentUserId())) {
                throw new HM_Permission_Exception(_('Не хватает прав'));
            }

            $this->_setSubjectExtendedView();

            if ($subjectId) {
                foreach(array('groupCourse2', 'groupCourse3') as $groupName) {
                    $group = $form->getDisplayGroup($groupName);
                    foreach(array_keys($group->getElements()) as $elementName) {
                        $form->removeElement($elementName);
                    }
                    $form->removeDisplayGroup($groupName);
                }
				$form->removeElement('status');
                if (!$this->_request->isPost()) {
                    $form->setDefault('cancelUrl', $_SERVER['HTTP_REFERER']);
                }
            }
        }
        
        if (
            $this -> getService('Acl') -> inheritsRole($this -> getService('User') -> getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_MANAGER)
        ) {
            $status = $form -> getElement('status');
            if($status){
                unset($status -> options[HM_Course_CourseModel::STATUS_ACTIVE]);
            }
        }
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_DEVELOPER)){
            $status = $form->getElement('status');
            if($status){
                unset($status->options[HM_Course_CourseModel::STATUS_ACTIVE]);
                unset($status->options[HM_Course_CourseModel::STATUS_STUDYONLY]);
                unset($status->options[HM_Course_CourseModel::STATUS_ARCHIVED]);
            }
        }
        return $form;
    }
    
    /**
     * Создание курса
     */
    public function newAction()
    {
        $modelName = $this->getService('Course')->getMapper()->getModelClass();
        $model = new $modelName(null);

        $status = $this->_request->getParam('status');
        $form = $this->_prepareForm(new HM_Form_Course());
        $defaultValues = $form->getValues();
        $request = $this->getRequest();

        // Если форма валидна то
        if ($request->isPost())
        {
            $form->populate($request->getParams());
            $courseStatus = $this->_setRequiredElements($form);
            if ($form->isValid($request->getParams()))
            {
				//Обработка поступающих параметров
                $workdate = $request->getParam('WorkDate');
                $subjects = $request->getParam('subjects');

                if (array_search(0, $subjects) !== false)
                    $subjects = array();

                if (! empty($subjects))
                {
                    $did = ';' . implode(';', $subjects) . ';';
                } else
                {
                    $did = '';
                }

                $is_module_need_check = 0;
                $sequence = 0;
                if ($struct = $request->getParam('struct'))
                {
                    switch ($struct)
                    {
                        case 1 :
                            $is_module_need_check = 0;
                            $sequence = 0;
                            break;
                        case 2 :
                            $is_module_need_check = 1;
                            $sequence = 0;
                            break;
                        case 3 :
                            $is_module_need_check = 0;
                            $sequence = 1;
                            break;
                    }
                }

                $data = array(
                    'Title' => $form->getValue('name'),
					#'title_translation' => $form->getValue('name_translation'),
                    'Description' => $form->getValue('describe'),
					#'description_translation' => $form->getValue('describe_translation'),
                    //'cBegin' => $workdate['from'],
                    // 'cEnd' => $workdate['to'],
                    //'Status' => $form->getValue('status'),
					 'Status' => $this->id ? HM_Course_CourseModel::STATUS_STUDYONLY : $form->getValue('status'),
                    'provider' => (int) $form->getValue('provider'),
                    'developStatus' => 0,
                    'lastUpdateDate' => date('Y-m-d'),
                    'createDate' => $this->getService('Course')->getDateTime(),
                    'planDate' => $form->getValue('planDate'),
                    // 'longtime' => $request->getParam( 'DurationDate' ),
                    //'tree' => 'a:3:{s:5:"level";i:1;s:10:"attributes";a:1:{s:3:"oid";i:-1;}s:8:"children";a:0:{}}',
                    'TypeDes' => (int) $form->getValue('access'),
                    'chain' => (int) $form->getValue('coordination'),
                    'did' => $did,
                    'sequence' => $sequence,
                    'is_module_need_check' => $is_module_need_check,
                    /*'has_tree' => $form->getValue('has_tree'),*/
                    'new_window' => $form->getValue('new_window'),
                    'emulate' => $form->getValue('emulate'),
                    'longtime' => $form->getValue('hours'),
                    'format' => HM_Course_CourseModel::FORMAT_FREE,
                    'author' => $this->getService('User')->getCurrentUserId()
                );
                // Добавляем данные и подготавливаем сообщение
                $courseService = $this->getService('Course');
                /*@var $course HM_Course_CourseModel */
				
				
				try {
					$course = $courseService->insert($data);
				} catch (Exception $e) {
					echo 'Ошибка: ',  $e->getMessage(), "\n";
					die;
				}
				
				

                $Comp = $this->getService('CourseComp2Course');
                $competences = $request->getParam('competence');
				foreach ( $competences as $value )
                {
                    $Comp->insert(array(
                        'cid' => $course->CID,
                        'coid' => $value));
                }

                if ( $course ) {

                    if ( $tags = $form->getParam('tags') ) {
                        $this->getService('Tag')->update($tags, $course->CID, $this->getService('TagRef')->getCourseType());
                    }

                    $classifiers = $form->getClassifierValues();
                    $this->getService('Classifier')->unlinkItem($course->CID, HM_Classifier_Link_LinkModel::TYPE_COURSE);
                    if (is_array($classifiers) && count($classifiers)) {
                        foreach($classifiers as $classifierId) {
                            if ($classifierId > 0) {
                                $this->getService('Classifier')->linkItem($course->CID, HM_Classifier_Link_LinkModel::TYPE_COURSE, $classifierId);
                            }
                        }
                    }

                    // Если создаётся учебный модуль под преподом, то сразу назначаем на учебный курс
                    $subjectId = (int) $this->_getParam('subject_id', 0);
                    if ($subjectId > 0) {
                        $course = $this->getService('Course')->update(
                            array('CID' => $course->CID, 'chain' => $subjectId)
                        );

                        $this->getService('Subject')->linkCourse($subjectId, $course->CID);

                        $this->_subject = $this->getOne($this->getService('Subject')->find($subjectId));
                        $this->getService('Course')->createLesson($this->_subject->subid, $course->CID);
                        
                        $course->setValue('subject_id', $this->_subject->subid);
                        
                    }
                }

                $this->_flashMessenger->addMessage(_('Учебный модуль успешно добавлен'));
                //$this->_redirector->gotoSimple('developed', 'list', 'course');
                $this->_redirector->gotoUrl($form->getValue('cancelUrl', $this->view->url(array('module' => 'course', 'controller' => 'list', 'action' => 'developed'))));

            } else
            {
                $arr = $request->getParams();
                $arr['competents'] = $model->getDiff($defaultValues['competents'][0], $request->getParam('competence'));
                $form->populate($arr);

            }
        } else {
            $form->setDefault('cancelUrl', $_SERVER['HTTP_REFERER']);
            $this->_setRequiredElements($form);
        }

        $this->view->form = $form;

    }


    public function newDefaultAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();

        $result = false;
        $defaults = $this->getService('Course')->getDefaults();
        $defaults['title'] = $this->_getParam('title');
        $subjectId = $defaults['chain'] = $this->_getParam('subject_id');
        if (strlen($defaults['title']) && $subjectId) {
            if ($course = $this->getService('Course')->insert($defaults)) {

                if ($this->getService('Subject')->linkCourse($subjectId, $course->CID)) {
    				$this->getService('Subject')->update(array(
                        'last_updated' => $this->getService('Subject')->getDateTime(),
                        'subid' => $subjectId
                    ));

    				$this->_subject = $this->getOne($this->getService('Subject')->find($subjectId));
    				$this->getService('Course')->createLesson($this->_subject->subid, $course->CID);

                    $result = $course->CID;
                }
            }
        }
        exit(Zend_Json::encode($result));
    }

    /**
     * Удаление курса
     */
    public function deleteAction()
    {

        $status = $this->_request->getParam('status');
        $cid = ( int ) $this->_request->getParam('CID', 0);
        if ($cid)
        {
            $this->getService('Course')->delete($cid);
            $this->getService('Course')->clearLesson(null, $courseId);
        }
        $this->_flashMessenger->addMessage(_('Учебный модуль успешно удален'));
        $this->_redirector->gotoSimple($status, 'list', 'course');

    }

    /**
     * Массовая обработка. Пока только удаление
     */
    public function deleteByAction()
    {

        $status = $this->_request->getParam('status');
        $cids = $this->_request->getParam('postMassIds_grid');
        $cids = explode(',', $cids);
        if (! empty($cids))
        {
            foreach ( $cids as $value )
            {
                $this->getService('Course')->delete($value);
                $this->getService('Course')->clearLesson(null, $value);
            }
        }

        $this->_flashMessenger->addMessage(_('Учебные модули успешно удалены'));
        $this->_redirector->gotoSimple($status, 'list', 'course');

    }

    public function massactionAction()
    {

        $status = $this->_request->getParam('status');
        $this->_flashMessenger->addMessage(_('Действие не выбрано'));
        $this->_redirector->gotoSimple($status, 'list', 'course');

    }

    /**
     * Экшн для аякса. Возвращает значения для заполнения левого поля Напрвлений деятельности
     */
    public function getDirectionsAction()
    {

        exit();
    }

    /**
     * Экшн для теста датапикера
     */
    public function testpickerAction()
    {

    }

    /**
     * Функция для конвертации ввода даты в нужный формат ISO_8601
     * @param unknown_type $date
     */
    public function dateChanger($date){

            $dateObject = new Zend_Date($date, HM_Locale_Format::getDateFormat());

            $value=$dateObject->toString('yyyy-MM-dd');


    }

    /**
     * Экшн для публикации курса
     */
    public function publicAction()
    {
        $cid = $this->_request->getParam('CID');
        $status = $this->_request->getParam('status');

        try {
            $this->getService('Course')->publish($cid);
            $this->_flashMessenger->addMessage(_('Учебный модуль успешно опубликован.'));
        } catch (HM_Exception $e) {

            $this->_flashMessenger->addMessage(
                array(
                    'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => sprintf(_('В ходе публикации модуля произошла ошибка. %s'), $e->getMessage())
                )
            );
        }

        $this->_redirector->gotoSimple($status, 'list', 'course');



    }


    /**
     * Экшн для публикации множества курсов
     */
    public function publicByAction()
    {

        $status = $this->_request->getParam('status');
        $cids = $this->_request->getParam('postMassIds_grid');
        $cids = explode(',', $cids);

        $errors = array();

        if (! empty($cids))
        {

            foreach ( $cids as $value )
            {
                try {
                    $this->getService('Course')->publish($value);
                } catch (HM_Exception $e) {
                    $errors[] = $e->getMessage();
                }

            }
        }




        if (!count($errors))
        {
            $this->_flashMessenger->addMessage(_('Учебные модули успешно опубликованы.'));
        } else
        {
            $this->_flashMessenger->addMessage(
                array(
                    'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => sprintf(_('В ходе публикации модулей произошла ошибка. Некоторые модули не подготовлены для публикации. %s'), join('<br>', $errors))
                )
            );
        }


        $this->_redirector->gotoSimple($status, 'list', 'course');



    }




 /**
     * Экшн для архивации курса
     */
    public function archiveAction()
    {
        $cid = $this->_request->getParam('CID');
        $status = $this->_request->getParam('status');

        $result = $this->archive($cid);

        if ($result !== false)
        {
            $this->_flashMessenger->addMessage(_('Учебный модуль успешно отправлен в архив.'));
        } else
        {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('В ходе архивации модуля произошла ошибка. Заполните все обязательные поля.')));
        }


        $this->_redirector->gotoSimple($status, 'list', 'course');



    }


    /**
     * Экшн для архивации множества курсов
     */
    public function archiveByAction()
    {


        $errors=true;

        $status = $this->_request->getParam('status');
        $cids = $this->_request->getParam('postMassIds_grid');
        $cids = explode(',', $cids);
        if (! empty($cids))
        {
            foreach ( $cids as $value )
            {
                $result = $this->archive($value);
                if($result===false){
                    $errors=false;
                }

            }
        }




        if ($errors !== false)
        {
            $this->_flashMessenger->addMessage(_('Учебные модули успешно отправлены в архив.'));
        } else
        {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('В ходе архивации модулей произошла ошибка. Некоторые модули не подготовлены для архивации.')));
        }


        $this->_redirector->gotoSimple($status, 'list', 'course');



    }



    /**
     * Экшн для пометки курса
     */
    public function studyAction()
    {
        $cid = $this->_request->getParam('CID');
        $status = $this->_request->getParam('status');

        $result = $this->studyonly($cid);

        if ($result !== false)
        {
            $this->_flashMessenger->addMessage(_('Учебный модуль успешно помечен.'));
        } else
        {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('В ходе переноса модуля произошла ошибка.')));
        }


        $this->_redirector->gotoSimple($status, 'list', 'course');



    }

    /**
     * Экшн для отправки учиться множества курсов
     */
    public function studyByAction()
    {


        $errors=true;

        $status = $this->_request->getParam('status');
        $cids = $this->_request->getParam('postMassIds_grid');
        $cids = explode(',', $cids);
        if (! empty($cids))
        {
            foreach ( $cids as $value )
            {
                $result = $this->studyonly($value);
                if($result===false){
                    $errors=false;
                }

            }
        }




        if ($errors !== false)
        {
            $this->_flashMessenger->addMessage(_('Учебные модули успешно помечены.'));
        } else
        {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('В ходе переноса модулей произошла ошибка.')));
        }


        $this->_redirector->gotoSimple($status, 'list', 'course');



    }


/**
     * Экшн для отправки курса на доработку
     */
    public function developAction()
    {
        $cid = $this->_request->getParam('CID');
        $status = $this->_request->getParam('status');

        $result = $this->develop($cid);

        if ($result !== false)
        {
            $this->_flashMessenger->addMessage(_('Статус модуля успешно изменен.'));
        } else
        {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('В ходе изменения статуса произошла ошибка. Заполните все обязательные поля.')));
        }


        $this->_redirector->gotoSimple($status, 'list', 'course');



    }



/**
     * Экшн для архивации множества курсов
     */
    public function developByAction()
    {


        $errors=true;

        $status = $this->_request->getParam('status');
        $cids = $this->_request->getParam('postMassIds_grid');
        $cids = explode(',', $cids);
        if (! empty($cids))
        {
            foreach ( $cids as $value )
            {
                $result = $this->develop($value);
                if($result===false){
                    $errors=false;
                }

            }
        }




        if ($errors !== false)
        {
            $this->_flashMessenger->addMessage(_('Статус модулей успешно изменен.'));
        } else
        {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('В ходе операции произошла ошибка.')));
        }


        $this->_redirector->gotoSimple($status, 'list', 'course');



    }


    /**
     * Экшн для просмотра карточки курса
     */
    public function viewAction()
    {
        $cid = $this->_request->getParam('CID');
        $status = $this->_request->getParam('status');

        $data = $this->getOne($this->getService('Course')->fetchAll(sprintf('cid = %d', $cid)));


        $this->view->courseData=$data;



    }

    /**
     * Метод публикует курс
     * @param unknown_type $courseId
     * @return string
     */
    public function pub($courseId)
    {
        $this->getService('Course')->publish($courseId);
    }

    /**
     * Метод архивирует курс
     * @param unknown_type $course
     * @return string
     */
    public function archive($course)
    {


        $data = $this->getOne($this->getService('Course')->fetchAll(sprintf('cid = %d', $course)));

        if ($data->Status == HM_Course_CourseModel::STATUS_STUDYONLY)
        {

            $dataArr = array(
                'CID' => $course,
                'Status' => HM_Course_CourseModel::STATUS_ARCHIVED,
                'archiveDate' => date('Y-m-d'));

            $this->getService('Course')->update($dataArr);
            return true;
        }

        return false;
    }

        /**
     * Метод помечает курс для учебных целей
     * @param unknown_type $course
     * @return string
     */
    public function studyonly($course)
    {


        $data = $this->getOne($this->getService('Course')->fetchAll(sprintf('cid = %d', $course)));

        /*if ($data->Status == HM_Course_CourseModel::STATUS_ACTIVE)
        {*/

            $dataArr = array(
                'CID' => $course,
                'Status' => HM_Course_CourseModel::STATUS_STUDYONLY,
                'archiveDate' => date('Y-m-d'));

            $this->getService('Course')->update($dataArr);
            return true;
        /*}

        return false;*/
    }



 /**
     * Метод отправляет курс в разработку
     * @param unknown_type $course
     * @return string
     */
    public function develop($course)
    {


        $data = $this->getOne($this->getService('Course')->fetchAll(sprintf('cid = %d', $course)));

//#17878 - разрешаем переводить в опубликован независимо от тек. статуса
//        if ($data->Status == HM_Course_CourseModel::STATUS_STUDYONLY || $data->Status == HM_Course_CourseModel::STATUS_ARCHIVED)
        {

            $dataArr = array(
                'CID' => $course,
                'Status' => HM_Course_CourseModel::STATUS_DEVELOPED,
                'developStatus' => HM_Course_CourseModel::SUBSTATUS_DEV);

            $this->getService('Course')->update($dataArr);
            return true;
        }

        return false;
    }

    public function subjectsAction()
    {
        $courseId = (int) $this->_getParam('CID', 0);
        $course = $this->getService('Course')->getOne($this->getService('Course')->find($courseId));

        $q = urldecode($this->_getParam('q', ''));
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();
        $this->getResponse()->setHeader('Content-type', 'text/html; charset='.Zend_Registry::get('config')->charset);

        $where = null;
        if (strlen($q)) {
            $q = '%'.iconv('UTF-8', Zend_Registry::get('config')->charset, $q).'%';
            $where = $this->getService('CourseRubric')->quoteInto('LOWER(name) LIKE LOWER(?)', $q);
        }

        $collections = $this->getService('CourseRubric')->fetchAll($where, 'name');
        $subjects = $collections->getList('did', 'name');
        if (is_array($subjects) && count($subjects)) {
            $count = 0;
            foreach($subjects as $did => $name) {
                if ($count > 0) {
                    echo "\n";
                }
                if ($course && (false != strstr($course->did, ';'.$did.';'))) {
                    $did .= '+';
                }
                echo sprintf("%s=%s", $did, $name);
                $count++;
            }
        }
    }

    public function cardAction()
    {

        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getResponse()->setHeader('Content-type', 'text/html; charset='.Zend_Registry::get('config')->charset);
        $courseId = (int) $this->_getParam('course_id', 0);
        $this->view->isAjaxRequest = $this->isAjaxRequest();

//         $this->_helper->getHelper('layout')->disableLayout();
//         Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
//         $this->getResponse()->setHeader('Content-type', 'text/html; charset='.Zend_Registry::get('config')->charset);
//        $courseId = (int) $this->_getParam('course_id', 0);
//        $this->view->isAjaxRequest = $this->isAjaxRequest();

        $this->view->course = $this->getService('Course')->getOne(
            $this->getService('Course')->find($courseId)
        );

    }

    public function getProviderString($providerId, $provider)
    {
        if ($providerId > 0) {
            $provider = $this->view->cardLink($this->view->url(array('module' => 'provider', 'controller' => 'list', 'action' => 'card', 'provider_id' => $providerId)), _('Карточка поставщика')).$provider;
        }
        return $provider;

    }

    public function updateLongtime($longtime)
    {
        if (!empty($longtime)) {
            return sprintf(_('%d час.'), $longtime);
        }
        return '';
    }

    public function updateFormatColumn($format)
    {
        return HM_Course_CourseModel::getFormat($format);
    }
}