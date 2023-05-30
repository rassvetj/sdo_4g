<?php
class Resource_RelatedController extends HM_Controller_Action_Resource
{
    public function assignAction()
    {
        $default = new Zend_Session_Namespace('default');

        // какая-то очень хитрая логика не позволяет только resource_id
        // для работы gridSwitcher нужно чтобы передавался параметр, отиличный от primaryKey таблицы
        $resourceId = max($this->_getParam('resource_id', 0), $this->_getParam('resourceId', 0));
        $this->_setParam('resourceId', $resourceId);

        $resource = $this->getService('Resource')->getOne($this->getService('Resource')->find($resourceId));
        $gridId = "grid{$resourceId}";

        // @todo: использовать HM_View_PageHeader
        $this->view->setHeader(_('Связанные ресурсы'));

        $order = $this->_getParam('ordergrid');
        if($order == ''){
            // @todo: есть подозрение, что в Orcale оно работает наоборот
            $this->_setParam('ordergrid', 'related_DESC');
        }

    	if ($resourceId && !isset($default->grid['resource-related-assign'][$gridId])) {
    		$default->grid['resource-related-assign'][$gridId]['filters']['related'] = 1; // по умолчанию показываем связанные
    	}
        $relatedResources = !empty($resource->related_resources) ? $resource->related_resources : 0;

        $select = $this->getService('Resource')->getSelect();
        $select->from(
            array('t' => 'resources'),
            array(
                'resource_id',
                'created_by',
                'title',
                'type',
                'filetype',
                'filename',
                'activity_type',
                'updated',
                'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                'tags'=>'resource_id',
                'related' => new Zend_Db_Expr("CASE WHEN (resource_id IN ({$relatedResources})) THEN 1 ELSE 0 END"),
            )
        )
        ->joinLeft(array('p' => 'People'), 'p.MID = t.created_by', array())
        ->where('location = ?', HM_Resource_ResourceModel::LOCALE_TYPE_GLOBAL)
        ->where('t.db_id IS NULL OR t.db_id = ?', '')
        ->where('t.parent_id = 0 OR t.parent_id IS NULL')
        ->where('t.resource_id != ?', $resource->resource_id);

//         if ($notAll) {
//             $select->where('resource_id IN (?)', $relatedResources);
//         }

        $fields = array(
            'resource_id' => array('title' => '#'),
            'created_by' => array('hidden' => true),
            'filetype' => array('hidden' => true),
            'filename' => array('hidden' => true),
            'activity_type' => array('hidden' => true),
            'title' => array(
                'title' => _('Название'),
                'callback' => array(
                    'function' => array($this, 'updateResourceName'),
                    'params' => array('{{resource_id}}', '{{title}}', '{{type}}', '{{filetype}}', '{{filename}}', '{{activity_type}}')
                ),
            ),
            'updated' => array('title' => _('Дата последнего изменения')),
            'fio' => ((!$subjectId) && Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_DEVELOPER)) ? array('title' => _('Создан пользователем'), 'decorator' => $this->view->cardLink($this->view->url(array('module' => 'user', 'controller' => 'list','action' => 'view', 'user_id' => '')).'{{created_by}}',_('Карточка пользователя')).'<a href="'.$this->view->url(array('module' => 'user', 'controller' => 'edit', 'action' => 'card', 'user_id' => '')) . '{{created_by}}'.'">'.'{{fio}}</a>') : array('hidden' => true),
            'type' => array('title' => _('Тип ресурса')),
        	'tags' => array('title' => _('Метки')),
        	'related' => array('title' => _('Связан с данным ресурсом?')),
        );

        $types = HM_Resource_ResourceModel::getTypes();
        $filters = array(
                'title' => null,
                'updated' => array(
                    'render' => 'date',
                    array(
                        'transform' => 'dateChanger'
                    )
                ),
                'type' => array('values' => $types),
                'related' => array('values' => array(0 => _('Нет'), 1 => _('Да'), )),
                'tags' => array('callback' => array('function' => array($this, 'filterTags')))
        );

        $grid = $this->getGrid(
            $select,
            $fields,
            $filters,
            $gridId
        );

        $grid->setGridSwitcher(array(
  			array('name' => 'related', 'title' => _('связанные ресурсы'), 'params' => array('related' => 1), 'order' => 'resource_id'),
  			array('name' => 'all', 'title' => _('все ресурсы Базы знаний'), 'params' => array('related' => null), 'order' => 'related', 'order_dir' => 'DESC'),
        ));


    	$grid->addMassAction(array('action' => 'do'), _('Связать ресурсы'), _('Вы уверены, что хотите связать выделенные ресурсы с данным ресурсом?'));
        $grid->addMassAction(array('action' => 'delete'), _('Удалить связи с ресурсами'), _('Вы уверены, что хотите удалить связи с выделенными ресурсами?'));

        $grid->updateColumn('related',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateRelated'),
                    'params' => array('{{related}}')
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

        $grid->updateColumn('type',
            array('callback' =>
                array('function' =>
                    array($this,'updateType'),
                    'params'   => array('{{type}}')
                )
            )
        );

		if ($resourceId) $grid->setClassRowCondition("'{{related}}' == '1'", "selected");

// 		$grid->addFixedRows($this->_getParam('module'), $this->_getParam('controller'),$this->_getParam('action'), 'resource_id');
//         $grid->updateColumn('fixType', array('hidden' => true));

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid->deploy();
    }


    public function doAction()
    {
		$resourceId = max($this->_getParam('resource_id', 0), $this->_getParam('resourceId', 0));
		$resource = $this->getService('Resource')->getOne($this->getService('Resource')->find($resourceId));

		$gridId = "grid{$resourceId}";
        $ids = explode(',', $this->_request->getParam('postMassIds_' . $gridId));
        $relatedResources = !empty($resource->related_resources) ? explode(',', $resource->related_resources) : array();

        $data = $resource->getValues();
        $data['related_resources'] = array_unique(array_merge($ids, $relatedResources));
        $this->getService('Resource')->update($data);

        $this->_flashMessenger->addMessage(_('Ресурсы связаны успешно'));
        $this->_redirector->gotoSimple('assign', 'related', 'resource', array('resource_id' => $resourceId));
    }

    public function deleteAction()
    {
		$resourceId = max($this->_getParam('resource_id', 0), $this->_getParam('resourceId', 0));
		$resource = $this->getService('Resource')->getOne($this->getService('Resource')->find($resourceId));

		$gridId = "grid{$resourceId}";
        $ids = explode(',', $this->_request->getParam('postMassIds_' . $gridId));
        $relatedResources = !empty($resource->related_resources) ? explode(',', $resource->related_resources) : array();

        $data = $resource->getValues();
        $data['related_resources'] = array_unique(array_diff($relatedResources, $ids));
        $this->getService('Resource')->update($data);

        $this->_flashMessenger->addMessage(_('Связи успешно удалены'));
        $this->_redirector->gotoSimple('assign', 'related', 'resource', array('resource_id' => $resourceId));
    }


    public function updateType($type)
    {
        $types = HM_Resource_ResourceModel::getTypes();
        return $types[$type];
    }


    public function updateRelated($yepNope) {
        return $yepNope ? _('Да') : _('Нет');
    }
}

