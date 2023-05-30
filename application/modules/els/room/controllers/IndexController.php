<?php

class Room_IndexController extends HM_Controller_Action
{
    protected $required_permission_level = 3;

    public function indexAction()
    {

        $select = $this->getService('User')->getSelect();
        $select->from('rooms', array('rid', 'name', 'type', 'volume'));
        $grid = $this->getGrid(
            $select,
            array(
                'rid' => array('hidden' => true),
                'name' => array('title' => _('Название')),
                'type' => array('title' => _('Тип'), 'helper' => array('name' => 'roomType', 'params' => array('{{type}}'))),
                'volume' => array('title' => _('Количество мест'))
            ),
            array(
                'name' => null,
                'type' => array('values' => HM_Room_RoomModel::getTypes()),
                'volume' => null
            )
        );
        $actions = new Bvb_Grid_Extra_Column();
        $actions->position('right')
                ->name(_('Действия'))
                ->decorator(
                "<a href=\"".$this->view->url(array('module' => 'room', 'controller' => 'index', 'action' => 'edit'))."/rid/{{rid}}\">".$this->view->icon('edit')."</a>"
                ." &nbsp; "
                ."<a href=\"".$this->view->url(array('module' => 'room', 'controller' => 'index', 'action' => 'delete'))."/rid/{{rid}}\">".$this->view->icon('delete')."</a>");
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
        $form = new HM_Form_Room();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $room = $this->getService('Room')->insert($form->getValues());
                $this->_flashMessenger->addMessage(_('Аудитория успешно создана'));
                $this->_redirector->gotoSimple('index', 'index', 'room');
            }
        }
        $this->view->form = $form;
    }

    public function deleteAction()
    {
        $roomId = (int) $this->_getParam('rid', 0);
        if ($roomId) {
            $this->getService('Room')->delete($roomId);
            $this->_flashMessenger->addMessage(_('Аудитория успешно удалена'));
        }
        $this->_redirector->gotoSimple('index', 'index', 'room');
    }

    public function deleteByAction()
    {
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach($ids as $id) {
                    $this->getService('Room')->delete($id);
                }
                $this->_flashMessenger->addMessage(_('Аудитории успешно удалены'));
            }
        }
        $this->_redirector->gotoSimple('index', 'index', 'room');
    }

    public function editAction()
    {
        $roomId = (int) $this->_getParam('rid', 0);
        $form = new HM_Form_Room();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $room = $this->getService('Room')->update($form->getValues());
                $this->_flashMessenger->addMessage(_('Аудитория успешно отредактирована'));
                $this->_redirector->gotoSimple('index', 'index', 'room');
            }
        } else {
            $collection = $this->getService('Room')->find($roomId);
            if (count($collection)) {
                $room = $collection->current();
                $form->setDefaults($room->getValues());
            }
        }
        $this->view->form = $form;
    }

    public function validateFormAction($form = null)
    {
        $form = new HM_Form_Room();
        parent::validateFormAction($form);
    }
}