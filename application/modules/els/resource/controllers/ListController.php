<?php

class Resource_ListController extends HM_Controller_Action_Crud
{

    protected $service     = 'Subject';
    protected $idParamName = 'subject_id';
    protected $idFieldName = 'subid';
    protected $id          = 0;

    protected $_subjectId  = 0;
    protected $_courseId   = 0;
    protected $_key        = 0;

    protected $_resource;
	protected $_currentLang = 'rus'; 
    public function init()
    {
        $form = new HM_Form_Resource();
        $this->_setForm($form);
		
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$this->_currentLang = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);
		
        $this->_subjectId = (int) $this->_getParam('subject_id', 0);
        $this->_courseId = (int) $this->_getParam('course_id', 0);
        $this->_key = (int) $this->_getParam('key', 0);

        if (!$this->isAjaxRequest()) {
            if ($this->_subjectId > 0) { // Делаем страницу расширенной
                $this->_initSubjectExtended();
            }

            if (!$this->_subjectId && ($this->_courseId > 0)) {
                $this->_initCourseExtended();
            }
        }

        if ($resourceId = $this->_getParam('resource_id', 0)) {
            if ($collection = $this->getService('Resource')->find($resourceId)) {
                $this->_resource = $collection->current();
            }
        }

        if (
            ($this->_resource && ($this->_resource->type == HM_Resource_ResourceModel::TYPE_CARD)) ||
            ($this->_getParam('type') == HM_Resource_ResourceModel::TYPE_CARD)
        ) {
            $form->removeSubForm('resourceStep2');
        }


        parent::init();

        if ($this->_courseId > 0) { // Создание ресурса из конструктора учебного модуля
            $form->setDefault('cancelUrl', $this->view->url(array('module' => 'course', 'controller' => 'structure', 'action' => 'index', 'subject_id' => $this->_subjectId, 'course_id' => $this->_courseId, 'key' => $this->_key), null, true));

            $course = $this->getOne($this->getService('Course')->find($this->_courseId));
            if ($course) {
                $this->getService('Unmanaged')->setSubHeader($course->Title);
            }
        }

    }

    private function _initSubjectExtended()
    {
        $this->id = (int) $this->_getParam($this->idParamName, 0);
        $subject = $this->getOne($this->getService($this->service)->find($this->id));

        $this->view->setExtended(
            array(
                'subjectName' => $this->service,
                'subjectId' => $this->id,
                'subjectIdParamName' => $this->idParamName,
                'subjectIdFieldName' => $this->idFieldName,
                'subject' => $subject
            )
        );

        $this->_subject = $subject;

        /*if ($this->_subjectId && $this->_subject->access_mode == HM_Subject_SubjectModel::MODE_FREE && $this->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_STUDENT){
            $this->view->deleteContextMenu('subject');
            $this->view->addInfoBlock('freeAccessToSubjectBlock', array('title' => $this->_subject->shortname ? $this->_subject->shortname : _('Содержание'), 'subject' => $this->_subject));
            $this->view->setHeader($this->_subject->getName());
        }*/

        // hack для корректного отображения баяна и хлебных крошек
        if ($this->_courseId > 0) {
            $this->view->addContextNavigationModifier(
                new HM_Navigation_Modifier_Remove_SubPages('resource', 'cm::subject:page7_5')
            );
        } else {
            $this->view->addContextNavigationModifier(
                new HM_Navigation_Modifier_Remove_SubPages('resource', 'cm::subject:page7_1')
            );
        }
    }

    private function _initCourseExtended()
    {
        $course = $this->getOne($this->getService('Course')->find($this->_courseId));

        $this->view->setExtended(
            array(
                'subjectName' => 'Course',
                'subjectId' => $this->_courseId,
                'subjectIdParamName' => 'course_id',
                'subjectIdFieldName' => 'CID',
                'subject' => $course
            )
        );
    }


    protected function _redirectToIndex()
    {
        if ($this->_courseId <= 0) {
            $this->_redirector->gotoSimple('index', 'list', 'resource', array('subject_id' => $this->_subjectId));
        }

        if ($this->_courseId > 0) {
            $this->_redirector->gotoSimple('index', 'structure', 'course', array('subject_id' => $this->_subjectId, 'course_id' => $this->_courseId, 'key' => $this->_key));
        }

        parent::_redirectToIndex();
    }

    public function assignAction()
    {
        $gridId = ($this->id) ? "grid{$this->id}" : 'grid';
        $postMassIds = $this->_getParam("postMassIds_{$gridId}", '');
        $subjectId = $this->_subject->subid;
        if (strlen($postMassIds)) {

            $ids = explode(',', $postMassIds);
            $section = $this->getService('Section')->getDefaultSection($subjectId);
            $currentOrder = $this->getService('Section')->getCurrentOrder($section);

            if (count($ids)) {
                foreach($ids as $id) {

                    $res = $this->getService('SubjectResource')->find($this->id, $id);

                    if(count($res) == 0){
                        $rr = $this->getService('SubjectResource')->insert(array(
                            'subject_id' => $this->id,
                            'resource_id' => $id,
                        ));
                    }
                    $this->getService('Resource')->createLesson($this->_subject->subid, $id, $section, ++$currentOrder);
                }

                $this->getService('Subject')->update(array(
                    'last_updated' => $this->getService('Subject')->getDateTime(),
                    'subid' => $this->id
                ));


                $this->_flashMessenger->addMessage(_('Информационные ресурсы успешно назначены на курс'));
            }
        }
        $this->_redirectToIndex();
    }

    public function unassignAction()
    {
        $gridId = ($this->id) ? "grid{$this->id}" : 'grid';
        $postMassIds = $this->_getParam("postMassIds_{$gridId}", '');
        $subjectId = $this->_getParam('subject_id', 0);
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                $bool = false;
                foreach($ids as $id) {
                    $this->getService('Resource')->clearLesson($this->_subject, $id);
                    $this->getService('SubjectResource')->delete(array($this->id, $id));
                }

                if($bool == false){
                    $this->_flashMessenger->addMessage(_('Назначение успешно отменено'));
                }else{
                    $this->_flashMessenger->addMessage(_('Невозможно отменить назначение для некоторых информационных ресурсов'));
                }
            }
        }
        $this->_redirectToIndex();
    }


    public function itemsAction()
    {
        $this->indexAction();

        $this->view->subjectId = $this->_subjectId;
        $this->view->courseId = $this->_courseId;
        $this->view->key = $this->_key;
    }


    public function indexAction()
    {
        $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base . 'css/content-modules/material-icons.css');

        $isTeacher = $this->getService('Acl')->inheritsRole(
                $this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TEACHER);
        
        $subjectId = $this->_getParam('subject_id', 0);
        $types     = HM_Resource_ResourceModel::getTypes();
        $gridId = ($subjectId) ? "grid{$subjectId}" : 'grid';
        $default   = new Zend_Session_Namespace('default');
        $order     = $this->_request->getParam("order{$gridId}");

        if ($subjectId && !isset($default->grid['resource-list-index'][$gridId])) {
            $default->grid['resource-list-index'][$gridId]['filters']['subject'] = $subjectId; // по умолчанию показываем только слушателей этого курса
        }


        if ($order == ""){
            $this->_request->setParam("order{$gridId}", 'title_ASC');
        }

        $switcher = $this->_getParam('switcher', 0);
        if($switcher && $switcher != 'index'){
            $this->getHelper('viewRenderer')->setNoRender();
            $action = $switcher.'Action';
            $this->$action();
            $this->view->render('list/'.$switcher.'.tpl');
            return true;
        }


        $this->view->subjectId = $subjectId;
        $types = HM_Resource_ResourceModel::getTypes();
        $filters = array(
                'title' => null,
                'subject' => null,
                //'volume' => null,
                'updated' => array(
                    'render' => 'date',
                    array(
                        'transform' => 'dateChanger'
                    )
                ),
                'type' => array('values' => $types),
                'location' => array('values' => HM_Resource_ResourceModel::getLocaleStatuses()),
                'tags' => array('callback' => array('function' => array($this, 'filterTags'))),
                'classifiers' => null, //array('callback' => array('function' => array($this, 'filterTags')))
                'subject_control' => null,
        );

        $rolesWithFilter = array(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, HM_Role_RoleModelAbstract::ROLE_MANAGER);
		$isManager = false;
        if(in_array($this->getService('User')->getCurrentUserRole(), $rolesWithFilter)){
			$isManager = true;
            $filters['public'] = array('values' => HM_Resource_ResourceModel::getStatuses());
// #10213
//             if($this->_getParam('publicgrid', '') == '' && $this->_getParam('gridmod', '') != 'ajax'){
//                 $this->_setParam('publicgrid', 1);
//             }
        }else{
            $this->_setParam('publicgrid', 1);
        }

        $group = array(
            't.resource_id',
            't.created_by',
            't.title',
			't.title_translation',
            't.type',
            't.filetype',
            't.filename',
            't.activity_type',
            't.volume',
            't.status',
            't.updated',
            't.location',
            't.status',
            't.subject_id',
            't.type',
            'p.LastName',
            'p.FirstName',
            'p.Patronymic',
        );
        
        
        
        if ($subjectId > 0) {

            if($order == ''){
                $this->_setParam('ordergrid', 'subject_ASC');
            }

            $select = $this->getService('Resource')->getSelect();
            $select->from(
                    array('t' => 'resources'),
                    array(
                          't.resource_id',
                          't.created_by',
                          't.title',
						  't.title_translation',
                          't.location',
                          'locationtemp'   =>'t.location',
                          'statustemp'     => 't.status',
                          'subjecttemp'    => 't.subject_id',
                          'subject'        => 's.subject_id',
                          'type',
                          'filetype',
                          'filename',
                          'activity_type',
                          'typetemp'       => 't.type',
                          't.volume',
                          't.updated',
                          'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                          'tags' => 't.resource_id',
                          'classifiers' => new Zend_Db_Expr("GROUP_CONCAT(c.name)"),
						  'classifiers_translation' => new Zend_Db_Expr("GROUP_CONCAT(c.name_translation)"),
                          ));


            $subSelect = $this->getService('Resource')->getSelect();
            $subSelect->from(array('s' => 'subjects_resources'), array('subject_id', 'resource_id'))->where('subject_id = ?', $subjectId);

            $select->joinLeft(
                       array('s' => $subSelect),
                       't.resource_id = s.resource_id',
                       array())
                   ->joinLeft(array('p' => 'People'), 'p.MID = t.created_by', array())
                   ->where('(t.location = ' . (int) HM_Resource_ResourceModel::LOCALE_TYPE_GLOBAL . ' AND t.status IN (' . (int) HM_Resource_ResourceModel::STATUS_PUBLISHED . ',' . (int) HM_Resource_ResourceModel::STATUS_STUDYONLY . ')) OR t.subject_id = ' . (int) $subjectId);
            $group = array_merge(array('s.subject_id'), $group);

        }else{

            if($order == ''){
                $this->_setParam('ordergrid', 'public_DESC');
            }
            $select = $this->getService('Resource')->getSelect();
            $select->from(
                array('t' => 'resources'),
                array(
                    'resource_id',
                    'created_by',
                    'title',
					'title_translation',
                    'type',
                    'filetype',
                    'filename',
                    'activity_type',
                    'volume',
                    'public' => 'status',
                    'updated',
                    'locationtemp'   =>'t.location',
                    'statustemp'     => 't.status',
                    'subjecttemp'    => 't.subject_id',                    
                    'typetemp'       => 't.type',
                    'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                    'tags'=>'resource_id',
                    'classifiers' => new Zend_Db_Expr("GROUP_CONCAT(c.name)"),
					'classifiers_translation' => new Zend_Db_Expr("GROUP_CONCAT(c.name_translation)"),
					'subject_control'    	 => 't.subject_id',
                )
            )
            ->joinLeft(array('p' => 'People'), 'p.MID = t.created_by', array())
            ->where('location = ?', HM_Resource_ResourceModel::LOCALE_TYPE_GLOBAL)
            ;

        }

        $select->joinLeft(
            array('cl' => 'classifiers_links'),
            'cl.item_id = t.resource_id AND cl.type = ' . HM_Classifier_Link_LinkModel::TYPE_RESOURCE,
            array()
        );
        $select->joinLeft(
            array('c' => 'classifiers'),
            'cl.classifier_id = c.classifier_id',
            array()
        );
        		
        
        
        $select->where('t.db_id IS NULL OR t.db_id = ?', '');
        $select->where('t.parent_id = 0 OR t.parent_id IS NULL');

        $select->group($group);
        
        $grid = $this->getGrid(
            $select,
            array(
                'resource_id' => array('title' => '#'),
                'subjecttemp' => array('hidden' => true),
                'created_by' => array('hidden' => true),
                'statustemp' => array('hidden' => true),
                'locationtemp' => array('hidden' => true),
                'filetype' => array('hidden' => true),
                'filename' => array('hidden' => true),
                'activity_type' => array('hidden' => true),
                'typetemp' => array('hidden' => true),
                'title' => array(
                    'title' => _('Название'),
                    'callback' => array(
                        'function' => array($this, 'updateResourceName'),
                        'params' => array('{{resource_id}}', '{{title}}', '{{title_translation}}', '{{type}}', '{{filetype}}', '{{filename}}', '{{activity_type}}')
                    ),
                ),
				'title_translation' => array('hidden' => true),
                'volume' => array('title' => _('Объём')),
                'updated' => array('title' => _('Дата последнего изменения')),
                'fio' => (!$subjectId && Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_DEVELOPER)) ? array('title' => _('Создан пользователем'), 'decorator' => $this->view->cardLink($this->view->url(array('module' => 'user', 'controller' => 'list','action' => 'view', 'user_id' => '')).'{{created_by}}',_('Карточка пользователя')).'<a href="'.$this->view->url(array('module' => 'user', 'controller' => 'edit', 'action' => 'card', 'user_id' => '')) . '{{created_by}}'.'">'.'{{fio}}</a>') : array('hidden' => true),
                'location' => array('title' => _('Место хранения')),
                'type' => array('title' => _('Тип ресурса')),
                   'subject' => array(
                    'title' => _('Доступ для слушателей'),
                    'callback' => array(
                        'function' => array($this, 'updateSubjectColumn'),
                        'params' => array(HM_Event_EventModel::TYPE_RESOURCE, '{{resource_id}}', '{{subject}}', $subjectId)
                    )
                ),
                'public' => array('title' => _('Статус')),
                'tags' => array('title' => _('Метки')),
				'subject_control' => (!$isManager) ? (array('hidden' => true)) : (array('title' => _('Id курса'))),
                'classifiers' => array(
                    'title' => _('Классификаторы'),
                    'callback' => array(
                        'function' => array($this, 'updateClassifiers'),
                        'params' => array('{{classifiers}}', '{{classifiers_translation}}'),
				
                    )
                ),
				'classifiers_translation' => array('hidden' => true),
            ),
            $filters,
            $gridId
        );

        if ($subjectId && $this->getService('Acl')->isCurrentAllowed('privileges:gridswitcher')) {
            
            $options = array(
                  'local' => array('name' => 'local', 'title' => _('используемые в данном учебном курсе'), 'params' => array('subject' => $subjectId)),
                  'global' => array('name' => 'global', 'title' => _('все, включая ресурсы из Базы знаний'), 'params' => array('subject' => null), 'order' => 'subject', 'order_dir' => 'DESC'),
              );
            
            $event = new sfEvent(null, HM_Extension_ExtensionService::EVENT_FILTER_GRID_SWITCHER);
            Zend_Registry::get('serviceContainer')->getService('EventDispatcher')->filter($event, $options);
            $options = $event->getReturnValue();
                        
            $grid->setGridSwitcher($options);
        }
        
        
/*
        $grid->addAction(
            array('module' => 'resource', 'controller' => 'list', 'action' => 'card'),
            array('resource_id'),
            $this->view->icon('view')
        );
*/
        if ($this->_courseId) {  // Если страница вызвана из конструктора учебныйх модулей
            $grid->addMassAction(
                array('module' => 'resource', 'controller' => 'list', 'action' => 'assign-to-course'),
                _('Подключить в учебный модуль'),
                _('Вы уверены?')
            );
        } else {
            $grid->addAction(
                array('module' => 'resource', 'controller' => 'list', 'action' => 'download'),
                array('resource_id'),
                _('Скачать')
            );

            $grid->addAction(
                array('module' => 'resource', 'controller' => 'list', 'action' => 'edit'),
                array('resource_id'),
                $this->view->icon('edit')
            );

            $grid->addAction(
                array('module' => 'resource', 'controller' => 'list', 'action' => 'delete'),
                array('resource_id'),
                $this->view->icon('delete')
            );

            if($subjectId > 0){
                $grid->addMassAction(
                    array('module' => 'resource', 'controller' => 'list', 'action' => 'assign'),
                    _('Использовать в курсе и открыть свободный доступ для слушателей')
                );
                $grid->addMassAction(
                    array('module' => 'resource', 'controller' => 'list', 'action' => 'unassign'),
                    _('Не использовать в курсе и закрыть доступ для слушателей')
                );


            }

        $grid->addMassAction(
            array('module' => 'resource', 'controller' => 'list', 'action' => 'delete-by'),
            _('Удалить'),
            _('Вы уверены?')
        );

            $grid->setActionsCallback(
                array('function' => array($this,'updateActions'),
                      'params'   => array('{{locationtemp}}', '{{subjecttemp}}', '{{typetemp}}', '{{resource_id}}', '{{created_by}}')
                )
            );
        }

        $grid->updateColumn('location',
            array('callback' =>
                array('function' =>
                    array($this,'updateStatus'),
                    'params'   => array('{{location}}')
                )
            )
        );

        $grid->updateColumn('type',
            array('callback' =>
                array('function' =>
                    array($this,'updateType'),
                    'params'   => array('{{type}}')
                )
            )
        );

        $grid->updateColumn('public',
            array('callback' =>
                array('function' =>
                    array($this,'updatePublic'),
                    'params'   => array('{{public}}')
                )
            )
        );

        $grid->updateColumn('updated', array(
            'callback' => array(
                'function' => array(
                    new HM_Resource_ResourceModel(array()),
                    'dateTime'),
                'params' => array(
                    '{{updated}}')))
        );

        $grid->updateColumn('tags', array(
                'callback' => array(
                    'function'=> array($this, 'displayTags'),
                    'params'=> array('{{tags}}', $this->getService('TagRef')->getResourceType(), $subjectId, '{{locationtemp}}')
                )
            ));

        if ($subjectId) $grid->setClassRowCondition("'{{subject}}' != ''", "selected");

        $this->view->isTeacher = $isTeacher;
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid->deploy();
    }
    
    protected function _getMessages()
    {
        return array(
            self::ACTION_INSERT => _('Ресурс успешно создан'),
            self::ACTION_UPDATE => _('Ресурс успешно обновлён'),
            self::ACTION_DELETE => _('Ресурс успешно удалён'),
            self::ACTION_DELETE_BY => _('Ресурсы успешно удалены')
        );
    }

    public function create(Zend_Form $form)
    {
        $subjectId = (int)$this->_getParam('subject_id', 0);
        $data = $form->getSubForm('resourceStep1')->getNonClassifierValues();

        if($subjectId == 0){
            $data['location'] = 1;
        } else {
            $data['subject_id'] = $subjectId;
        }
        unset($data['resource_id']);
        unset($data['tags']);

        $resource = $this->getService('Resource')->insert($data);

        if($subjectId > 0){
            $this->getService('SubjectResource')->insert(array('subject_id' => $subjectId, 'resource_id' => $resource->resource_id));
            $this->getService('Resource')->createLesson($this->_subject->subid, $resource->resource_id);
        }

        if ($resource && !$subjectId) {
            $this->getService('Resource')->linkClassifiers($resource->resource_id, $form->getSubForm('resourceStep1')->getClassifierValues());
        }

        if ($subform = $form->getSubForm('resourceStep2')) {

            $url = $subform->getElement('url');
            if ($resource && $url) {
                $data = $resource->getValues();
                $data['url'] = $url->getValue();
                $this->getService('Resource')->update($data);
            }

            $content = $subform->getElement('content');
            if ($resource && $content) {
                $data = $resource->getValues();
                $data['content'] = $content->getValue();
                $data['volume'] = HM_Files_FilesModel::toByteString(strlen($data['content']));
                $this->getService('Resource')->update($data);
            }

            $file = $subform->getElement('file');
            if ($resource && $file && $file->isUploaded()) {
                $file->receive();
                if ($file->isReceived()) {

                    $filename = $file->getFileName();
                    if (count($filename) > 1) {
                        $filename = $this->getService('Resource')->prepareMultipleFiles($resource, $file);
                        $count = $this->getService('Resource')->updateDependentResources($resource, $file);
                    $resource->volume = HM_Files_FilesModel::toByteString(filesize($filename));
                } else {
                    $resource->volume = $file->getFileSize();
                }

                    $resource->filename = basename($filename);
                    $resource->filetype = HM_Files_FilesModel::getFileType($resource->filename);

                    $filter = new Zend_Filter_File_Rename(
                        array(
                            'source' => $filename,
                            'target' => realpath(Zend_Registry::get('config')->path->upload->resource).'/'.$resource->resource_id,
                            'overwrite' => true
                        )
                    );
                    if ($filter->filter($filename)) {
                        $this->getService('Resource')->update($resource->getValues());
                    }
                }
            }

            $file = $subform->getElement('filezip');
            if ($resource && $file && $file->isUploaded()) {
                $file->receive();
                $oldUmask = umask(0);
                $resoursePath = realpath(Zend_Registry::get('config')->path->upload->public_resource);
                $target = $resoursePath . '/' . $resource->resource_id . '/';
                $volume = $file->getFileSize();

                if ( !is_dir($target)) {
                    mkdir($target, 0755);
                }

                $filter = new Zend_Filter_Decompress( array('adapter' => 'Zip', 'options' => array('target' => $target)));
                $filter -> filter($file->getFileName());

                if ( file_exists( $resoursePath . '/zip/' . basename($file->getFileName()))) {
                    unlink( $resoursePath . '/zip/' . basename($file->getFileName()) );
                }

                $resource = $this->getService('Resource')->update(array(
                    'resource_id' => $resource->resource_id,
                    'url' => $this->_getParam('url', 'index.htm'),
                    'volume' => $volume,
                ));


                umask($oldUmask);
            }
        }

        if ($resource && ($this->_subjectId >= 0) && ($this->_courseId > 0)) {
            // Если ресурс создаётся из конструктора учебного модуля, то создаём раздел в учебном модуле

            // Делаем ресурс опубликованным
            $resource = $this->getService('Resource')->update(array('resource_id' => $resource->resource_id, 'status' => HM_Resource_ResourceModel::STATUS_PUBLISHED));

            $this->getService('CourseItem')->append(
                array(
                    'title' => $resource->title,
                    'cid' => $this->_courseId,
                    'module' => 0,
                    'vol2' => $resource->resource_id
                ),
                $this->_key
            );
        }

        if ($tags = $form->getValue('tags')) {
            $this->getService('Tag')->update( $tags, $resource->resource_id, $this->getService('TagRef')->getResourceType() );
        }

    }


    public function update(Zend_Form $form)
    {
        $data = $form->getSubForm('resourceStep1')->getNonClassifierValues();
        unset($data['tags']);
        unset($data['related_resources']);
        if ($this->_resource && ($this->_resource->type != HM_Resource_ResourceModel::TYPE_CARD)) {
            unset($data['type']);
        }

        $resource = $this->getService('Resource')->update($data);

        $tags = array_unique($form->getParam('tags', array()));
        $this->getService('Tag')->update($tags, $resource->resource_id, $this->getService('TagRef')->getResourceType());

        if ($resource && !$this->_getParam('subject_id', 0)) {
            $this->getService('Resource')->linkClassifiers($resource->resource_id, $form->getSubForm('resourceStep1')->getClassifierValues());
        }
    }

    public function delete($id)
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $resource  = $this->getService('Resource')->getOne($this->getService('Resource')->findDependence('Revision', $id));
        if(!empty($resource) && $this->getService('Resource')->isEditable($resource->subject_id, $subjectId, $resource->location)){
            // убираем мусор
            if ( $resource->filename ) {
               $filePath = realpath(Zend_Registry::get('config')->path->upload->resource) . '/' . $resource->resource_id;
               if ( file_exists($filePath) ) {
                   unlink($filePath);
               }
            }

            if ($resource->type == HM_Resource_ResourceModel::TYPE_FILESET) {
                $resourcePath = realpath(Zend_Registry::get('config')->path->upload->public_resource) . '/' . $resource->resource_id . '/';
                $this->getService('Course')->removeDir($resourcePath);
                foreach ($resource->revisions as $revision) {
                    $revisionPath = realpath(Zend_Registry::get('config')->path->upload->public_resource) . '/revision/' . $revision->revision_id . '/';
                    $this->getService('Course')->removeDir($revisionPath);
                }
            }

            $this->getService('Resource')->delete($id);
            $this->getService('Resource')->clearLesson(null, $id);
            $this->getService('ResourceRevision')->deleteBy(array('resource_id = ?' => $id));

            $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_DELETE));
        }
    else
        $this->_flashMessenger->addMessage(array('message' => _('Вы не можете удалить ресурс, созданный в Базе знаний'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR)); 

    }

    public function setDefaults(Zend_Form $form)
    {
        $resourceId = (int) $this->_getParam('resource_id', 0);

        $resource = $this->getService('Resource')->getOne($this->getService('Resource')->find($resourceId));

        if ($resource) {
            $data = $resource->getValues();
            $data['related_resources'] = $this->getService('Resource')->setDefaultRelatedResources($data['related_resources']);
            $form->setDefaults($data);
        }

    }

    public function cardAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getResponse()->setHeader('Content-type', 'text/html; charset='.Zend_Registry::get('config')->charset);
        $resourceId = (int) $this->_getParam('resource_id', 0);
        $this->view->resource = false;
        $this->view->resource = $this->getService('Resource')->getOne(
            $this->getService('Resource')->find($resourceId)
        );
    }

    public function updateStatus($status)
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $statuses = HM_Resource_ResourceModel::getLocaleStatuses();

        return $statuses[$status];
      /*  if($subjectId == $locale && $status == HM_Resource_ResourceModel::STATUS_UNPUBLISHED){
            return $statuses[HM_Resource_ResourceModel::LOCALE_TYPE_LOCAL];
        }else{
            return $statuses[HM_Resource_ResourceModel::LOCALE_TYPE_GLOBAL];
        }*/
    }

    public function updateSubject($subject)
    {

        if($subject !=''){
            return _('Да');
        }else{
            return _('Нет');
        }

    }

    public function updateActions($status, $subjectId, $type, $resourceId, $createdBy, $actions)
    {
        $subject_id = $this->_getParam('subject_id', 0);
        $filename = APPLICATION_PATH . '/../public/upload/webinar-records/' . $resourceId . '.zip';

        if ($type != HM_Resource_ResourceModel::TYPE_WEBINAR){
            $this->unsetAction($actions, array(
                    'action' => 'download',
                    'publicgrid' => null,
                    'ordergrid' => null,
                    'ordergrid' . $subject_id => null,
                    'subjectgrid' . $subject_id => null,
                    'resource_id' => null,
                ));
        } else {
            $this->unsetAction($actions, array('module' => 'resource', 'controller' => 'list', 'action' => 'edit'));
            $this->unsetAction($actions, array('module' => 'resource', 'controller' => 'list', 'action' => 'delete'));
            if (!file_exists($filename)) {
                return '';
            }
        }

        if (
            ($createdBy != $this->getService('User')->getCurrentUserId()) &&
            !$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_MANAGER) &&
            !$subject_id // манагеру всё можно, внутри курса с локальными ресурсами тоже
        ) {
            $this->unsetAction($actions, array('module' => 'resource', 'controller' => 'list', 'action' => 'edit'));
            $this->unsetAction($actions, array('module' => 'resource', 'controller' => 'list', 'action' => 'delete'));
        }

        return $actions;
    }


    public function deleteAction()
    {
        $params = $this->_getAllParams();
        $id = (int) $this->_getParam('resource_id', 0);
        if ($id) {
            $this->delete($id);

            $this->getService('Tag')->deleteTags($id, HM_Tag_Ref_RefModel::TYPE_RESOURCE);
        }
        $this->_redirectToIndex();
    }



    public function updateType($type)
    {
        $types = HM_Resource_ResourceModel::getTypes();
        return $types[$type];
    }


    public function updatePublic($status)
    {
        $statuses = HM_Resource_ResourceModel::getStatuses();
        return $statuses[$status];

    }

    /**
     * Подключить ресурс к учебному модулю из конструктора учебных модулей
     * @return void
     */
    public function assignToCourseAction()
    {
        $gridId = ($this->_subjectId) ? "grid{$this->_subjectId}" : 'grid';

        $ids = explode(',' ,$this->_getParam('postMassIds_'.$gridId, ''));
        if (count($ids)) {
            foreach($ids as $id) {
                $resource = $this->getOne($this->getService('Resource')->find($id));
                if ($resource) {
                    $this->getService('CourseItem')->append(
                        array(
                            'title' => $resource->title,
                            'cid' => $this->_courseId,
                            'module' => 0,
                            'vol2' => $resource->resource_id
                        ),
                        $this->_key
                    );
                }
            }
        }

        $this->_flashMessenger->addMessage(_('Ресурсы успешно подключены'));
        $this->_redirector->gotoSimple('index', 'structure', 'course', array('key' => $this->_key, 'subject_id' => $this->_subjectId, 'course_id' => $this->_courseId));

    }

    public function deleteByAction()
    {
        $gridId = ($this->_subjectId) ? "grid{$this->_subjectId}" : 'grid';
        $postMassIds = $this->_getParam('postMassIds_'.$gridId, '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach($ids as $id) {
                    $temp = $this->delete($id);
                    if($temp === false){
                        $error = true;
                    } else {
                        $this->getService('Tag')->deleteTags($id, HM_Tag_Ref_RefModel::TYPE_RESOURCE);
                    }
                }
                if($error === false){
                    $this->_flashMessenger->addMessage($this->_getMessage((count($ids) > 1)? self::ACTION_DELETE_BY : self::ACTION_DELETE));
                }else{
                    $this->_flashMessenger->addMessage(_('Ресурсы успешно удалены'));
                }
            }
        }
        $this->_redirectToIndex();
    }

    public function downloadAction() {
        $resource_id = (int) $this->_getParam('resource_id', 0);
        $filename = APPLICATION_PATH . '/../public/upload/webinar-records/' . $resource_id . '.zip';
        if ($resource_id && file_exists($filename)) {
            $this->_helper->sendFile($filename, 'application/zip');
            exit();
        }
        $this->_flashMessenger->addMessage(_('Файла записи вебинара не существует'));
        $this->_redirectToIndex();
    }
}