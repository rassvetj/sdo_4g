<?php
class Metadata_IndexController extends HM_Controller_Action
{
    protected $required_permission_level = 4;
    
    public function indexAction()
    {
        $select = $this->getService('MetadataGroup')->getSelect();
        $select->from('metadata_groups', array('group_id', 'name'));
        $grid = $this->getGrid(
            $select,
            array(
                'group_id' => array('hidden' => true),
                'name' => array('title' => _('Название')),
            ),
            array(
                'name' => null,
            )
        );
        $actions = new Bvb_Grid_Extra_Column();
        $actions->position('right')
                ->name(_('Действия'))
                ->decorator(
                "<a href=\"".$this->view->url(array('module' => 'metadata', 'controller' => 'index', 'action' => 'edit'))."/group_id/{{group_id}}\">".$this->view->icon('edit')."</a>"
                ." &nbsp; "
                ."<a href=\"".$this->view->url(array('module' => 'metadata', 'controller' => 'index', 'action' => 'delete'))."/group_id/{{group_id}}\">".$this->view->icon('delete')."</a>");
        $grid->setMassAction(array(
                array(
                    'url'=> $grid->getUrl(),
                    'caption'=> _('Выберите действие')
                ),
                array(
                    'url'=> $this->view->url(array('action' => 'delete-by')),
                    'caption'=> _('Удалить'),
                    'confirm'=> _('Вы уверены?')
                ),
            )
        );
        $grid->addExtraColumns($actions);

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid->deploy();
    }

    public function newAction()
    {
        $form = new HM_Form_Group();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {

                $group = $this->getService('MetadataGroup')->insert(
                    array(
                        'name' => $form->getValue('name'),
                        'roles' => join('~|~', $form->getValue('roles'))
                    )
                );

                if ($group) {
                    $values = $request->getParam('values');
                    if (is_array($values) && count($values)) {
                        $count = 1;
                        foreach($values as $id => $value) {
                            intval($id);
                            if ($id && isset($value['name']) && strlen($value['name'])) {
                                $item = $this->getService('MetadataItem')->insert(
                                    array(
                                        'group_id' => $group->group_id,
                                        'name'     => $value['name'],
                                        'value'    => $value['value'],
                                        'type'     => (int) $value['type'],
                                        'public'   => (int) $value['public'],
                                        'required' => (int) $value['required'],
                                        'editable' => (int) $value['editable'],
                                        'order'    => (int) $count
                                    )
                                );
                            }
                            $count++;
                        }
                    }
                }

                $this->_flashMessenger->addMessage(_('Группа метаданных успешно создана'));
                $this->_redirector->gotoSimple('index', 'index', 'metadata');
             }
        }

        $this->view->action = $this->getRequest()->getActionName();
        $this->view->roles = $this->getService('Unmanaged')->getRoles();
        $this->view->group = $form;

    }

    public function editAction()
    {
        $form = new HM_Form_Group();
        $request = $this->getRequest();

        $groupId = (int) $this->_getParam('group_id', 0);

        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {

                $group = $this->getService('MetadataGroup')->update(
                    array(
                        'group_id' => $form->getValue('group_id'),
                        'name' => $form->getValue('name'),
                        'roles' => join('~|~', $form->getValue('roles'))
                    )
                );

                if ($group) {
                    $this->getService('MetadataItem')->deleteBy($this->getService('MetadataItem')->quoteInto('group_id = ?', $group->group_id));
                    $values = $request->getParam('values');
                    if (is_array($values) && count($values)) {
                        $count = 1;
                        foreach($values as $id => $value) {
                            intval($id);
                            if ($id && isset($value['name']) && strlen($value['name'])) {
                                $data =  array(
                                    'group_id' => $group->group_id,
                                    'name'     => $value['name'],
                                    'value'    => $value['value'],
                                    'type'     => (int) $value['type'],
                                    'public'   => (int) $value['public'],
                                    'required' => (int) $value['required'],
                                    'editable' => (int) $value['editable'],
                                    'order'    => (int) $count
                                );
                                if (isset($value['item_id'])) {
                                    $data['item_id'] = $value['item_id'];
                                }
                                $item = $this->getService('MetadataItem')->insert(
                                    $data
                                );
                            }
                            $count++;
                        }
                    }
                }

                $this->_flashMessenger->addMessage(_('Группа метаданных успешно изменена'));
                $this->_redirector->gotoSimple('index', 'index', 'metadata');
             }
        }

        $group =  false;
        $collection = $this->getService('MetadataGroup')->find($groupId);
        if (count($collection)) {
            $group = $collection->current();
            $group->setValue('roles', explode('|', $group->getValue('roles')));
        }
        $items = $this->getService('MetadataItem')->fetchAll($this->getService('MetadataItem')->quoteInto('group_id = ?', $groupId), 'order');

        $this->view->action = $this->getRequest()->getActionName();
        $this->view->group = $group;
        $this->view->items = $items;
        $this->view->roles = $this->getService('Unmanaged')->getRoles();
        $this->view->form = $form;

    }

    public function deleteAction()
    {
        $id = (int) $this->_getParam('group_id', 0);
        if ($id) {
            $this->getService('MetadataGroup')->delete($id);
            $this->_flashMessenger->addMessage(_('Группа метаданных успешно удалена'));
        }
        $this->_redirector->gotoSimple('index', 'index', 'metadata');
    }

    public function deleteByAction()
    {
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach($ids as $id) {
                    $this->getService('MetadataGroup')->delete($id);
                }
                $this->_flashMessenger->addMessage(_('Группы метаданных успешно удалены'));
            }
        }
        $this->_redirector->gotoSimple('index', 'index', 'metadata');
    }
}