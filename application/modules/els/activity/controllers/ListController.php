<?php
class Activity_ListController extends HM_Controller_Action_Crud
{
    protected $_resource;
    protected $_activityResource;

    protected $_activityResourceCache;

    public function init()
    {
        $form = new HM_Form_Resource();
        $this->_setForm($form);

        $resourceId = (int) $this->_getParam('resource_id', 0);
        $activityId = (int) $this->_getParam('activity_id', 0);
        $activityType = (int) $this->_getParam('activity_type', 0);
        if ($resourceId) {
            $this->_resource = $this->getService('Resource')->getOne($this->getService('Resource')->find($resourceId));
        } elseif ($activityId && $activityType) {
            $this->_activityResource = $this->getService('ActivityResource')->fetchAll(array(
                'activity_id = ?' => $activityId,
                'activity_type = ?' => $activityType
            ))->current();
        }
        $this->setDefaults($form);

        parent::init();
    }

    protected function _redirectToIndex()
    {
        $this->_redirector->gotoSimple('index', 'list', 'activity');
        parent::_redirectToIndex();
    }

    public function indexAction()
    {
    	$default   = new Zend_Session_Namespace('default');
        $order     = $this->_request->getParam("ordergrid");

        if ($order == ""){
            $this->_request->setParam("ordergrid", 'isresource_DESC');
        }

    	if (!isset($default->grid['activity-list-index']['grid'])) {
    		$default->grid['activity-list-index']['grid']['filters']['isresource'] = 1; // по умолчанию показываем только опубликованные
    	}

        $types = HM_Activity_Resource_ResourceModel::getActivityTypes();
        $filters = array(
                'activity_name' => null,
                'volume' => null,
                'updated' => array('render' => 'date', array('transform' => 'dateChanger')),
                'activity_type_name' => array('values' => $types),
                'subject_name' => null,
                'tags' => array('callback' => array('function' => array($this, 'filterTags'))),
                'status' => array('values' => array(
                    HM_Resource_ResourceModel::STATUS_UNPUBLISHED    => _('Не опубликован'),
                    HM_Resource_ResourceModel::STATUS_PUBLISHED      => _('Опубликован'),
                    // 3-го не дано (RHENRHEE: кто бы сомневался)
                )),
                'isresource' => array('values' => array(0 => _('Нет'), 1 => ('Да'))),
        );

        $select = $this->getService('ActivityResource')->getSelect();
        $select->from(
            array('ar' => 'activity_resources'),
            array(
                'resource_id',
                'subject_id',
                'activity_id',
                'activity_name',
                'activity_type_name' => 'activity_type',
                'activity_type',
                'subject_name',
                'volume',
                'updated',
                'tags'=>'resource_id',
                'status',
                'isresource' => 'CASE WHEN (resource_id IS NULL) THEN 0 ELSE 1 END',
        ));

        // временно отключаем чат; нужно делать Action для просмотра истории чата
        $select->where('activity_type != ?', 512);

        $grid = $this->getGrid(
            $select,
            array(
                'resource_id' => array('hidden' => true),
                'subject_id' => array('hidden' => true),
                'activity_id' => array('hidden' => true),
                'activity_type' => array('hidden' => true),
                'activity_name' => array(
                    'title' => _('Название'),
                    'callback' => array(
                        'function' => array($this, 'updateName'),
                        'params' => array('{{resource_id}}', '{{activity_type}}', '{{activity_id}}')
                    )
                ),
                'subject_name' => array(
                    'title' => _('Учебный курс'),
                    'callback' => array(
                        'function' => array($this, 'updateSubject'),
                        'params' => array('{{subject_id}}', '{{subject_name}}')
                    )
                ),
                'volume' => array('title' => _('Объём') . HM_View_Helper_Footnote::marker(1)),
                'updated' => array('title' => _('Дата последнего изменения')),
                'activity_type_name' => array('title' => _('Тип')),
                'status' => array('title' => _('Статус ресурса')),
            	'tags' => array('title' => _('Метки')),
            	'isresource' => array('hidden' => true),
//                 array(
//         	        'title' => _('Является ресурсом БЗ?'),
//                     'callback' => array(
//                         'function' => array($this, 'updateIsResource'),
//                         'params' => array('{{isresource}}')
//                     )
//     	        ),
            ),
            $filters,
            'grid'
        );

        $grid->setClassRowCondition("'{{isresource}}' == 1", "selected");

        $this->view->footnote(_('Для сервисов "Форум" и "Блог" объём означает количество сообщений; в сервисе "Wiki" - количество страниц'), 1);

        $grid->setGridSwitcher(array(
  			array('name' => 'isresource', 'title' => _('ресурсы на основе сервисов взаимодействия'), 'params' => array('isresource' => 1)),
  			array('name' => 'isresource', 'title' => _('все сервисы взаимодействия в курсах'), 'params' => array('isresource' => null), 'order' => 'isresource', 'order_dir' => 'DESC'),
  		));

        $grid->addAction(
            array('module' => 'activity', 'controller' => 'list', 'action' => 'new'),
            array('activity_id', 'activity_type'),
            _('Создать ресурс')
        );

        $grid->addAction(
            array('module' => 'activity', 'controller' => 'list', 'action' => 'edit'),
            array('resource_id'),
            $this->view->icon('edit')
        );

        $grid->addAction(
            array('module' => 'activity', 'controller' => 'list', 'action' => 'delete'),
            array('resource_id'),
            _('Удалить ресурс')
        );

        $grid->updateColumn('status',
            array('callback' =>
                array('function' =>
                    array($this,'updateStatus'),
                    'params'   => array('{{status}}')
                )
            )
        );

        $grid->updateColumn('activity_type_name',
            array('callback' =>
                array('function' =>
                    array($this,'updateType'),
                    'params'   => array('{{activity_type_name}}')
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
                'params'=> array('{{tags}}', $this->getService('TagRef')->getResourceType())
            )
        ));

        $grid->setActionsCallback(
            array('function' => array($this,'updateActions'),
                'params'   => array('{{resource_id}}')
            )
        );

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid->deploy();
    }

    public function updateSubject($subjectId, $subjectName)
    {
        $url = $this->view->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'card', 'subject_id' => $subjectId));
        return sprintf('<a href="%s">%s</a>', $url, $subjectName);
    }

    public function updateName($resourceId, $activityType, $activityId)
    {
        if (!isset($_activityResourceCache)) {
            $_activityResourceCache = array();
            foreach ($this->getService('ActivityResource')->fetchAll() as $activityResource) {
                $this->_activityResourceCache[$activityResource->activity_type . '.' . $activityResource->activity_id] = $activityResource;
            }
        }
        if (isset($this->_activityResourceCache[$activityType . '.' . $activityId])) {
            $activityName = $this->_activityResourceCache[$activityType . '.' . $activityId]->getDefaultName();
        }
        if (!$resourceId) {
            return $activityName;
        } else {
            $url = $this->_activityResourceCache[$activityType . '.' . $activityId]->getUrl();
            return
                $this->view->cardLink($this->view->url(array('module' => 'resource', 'controller' => 'list', 'action' => 'card', 'resource_id' => $resourceId)), _('Карточка информационного ресурса')) .
                $this->view->cardLink($this->view->url(array('module' => 'resource', 'controller' => 'list', 'action' => 'card', 'resource_id' => $resourceId)), _('Карточка информационного ресурса')) .
                '<a href="' . $this->view->url(array('module' => 'resource', 'controller' => 'index', 'action' => 'index', 'resource_id' => $resourceId)) . '">' . $activityName . '</a>';
        }
    }

    public function updateStatus($status)
    {
        $statuses = HM_Resource_ResourceModel::getStatuses();
        return $statuses[$status];
    }

    public function updateIsResource($isresource)
    {
        return $isresource ? _('Да') : _('Нет');
    }

    public function updateActions($resourceId, $actions)
    {
        $actions = explode('<li>', $actions);

        // 0 - не action
        if (!$resourceId){
            unset($actions[3]);
            unset($actions[2]);
        } else {
            unset($actions[1]);
        }

        return implode('<li>', $actions);
    }


    public function deleteAction()
    {
        $params = $this->_getAllParams();
        $id = (int) $this->_getParam('resource_id', 0);
        if ($id) {
            $this->delete($id);

            $this->getService('Tag')->deleteTags($id, HM_Tag_Ref_RefModel::TYPE_RESOURCE);

            $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_DELETE));
        }
        $this->_redirectToIndex();
    }

    public function updateType($type)
    {
        $types = HM_Activity_Resource_ResourceModel::getActivityTypes();
        return $types[$type];
    }

    public function create(Zend_Form $form)
    {
        $data = $form->getNonClassifierValues();
        unset($data['resource_id']);
        unset($data['tags']);
        $data['type'] = HM_Resource_ResourceModel::TYPE_ACTIVITY;
        $data['title'] = $this->_activityResource->getDefaultName();
		//$data['title_translation'] = $this->_activityResource->getDefaultNameTranslate();

        $resource = $this->getService('Resource')->insert($data);
        $this->getService('Resource')->linkClassifiers($resource->resource_id, $form->getClassifierValues());

        if ($tags = $form->getValue('tags')) {
            $this->getService('Tag')->update( $tags, $resource->resource_id, $this->getService('TagRef')->getResourceType() );
        }
    }

    public function update(Zend_Form $form)
    {
        $data = $form->getNonClassifierValues();
        unset($data['tags']);
        unset($data['type']);
		//$data['title_translation'] = $this->_activityResource->getDefaultName();
        $resource = $this->getService('Resource')->update($data);
        $this->getService('Resource')->linkClassifiers($resource->resource_id, $form->getClassifierValues());

        $tags = array_unique($form->getParam('tags', array()));
        $this->getService('Tag')->update($tags, $resource->resource_id, $this->getService('TagRef')->getResourceType());

    }

    public function delete($id)
    {
        $this->getService('Resource')->delete($id);
    }

    public function setDefaults(Zend_Form $form)
    {
        $data = array();
        if (!empty($this->_resource)) {
            $data = $this->_resource->getValues();
            $data['related_resources'] = $this->getService('Resource')->setDefaultRelatedResources($this->_resource->related_resources);
        } elseif (!empty($this->_activityResource)) {
            $data = $this->_activityResource->getData();
            $data['title'] = $this->_activityResource->getDefaultName();
        }
        $data['type'] = HM_Resource_ResourceModel::TYPE_ACTIVITY;
        $form->setDefaults($data);
    }

}