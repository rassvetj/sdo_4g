<?php
class Criterion_ListController extends HM_Controller_Action_Crud
{
    public function init()
    {
        $form = new HM_Form_Criterion();
        $this->_setForm($form);
        parent::init();
    }

    public function indexAction()
    {
        $select = $this->getService('Criterion')->getSelect();

        $select->from(
            array(
                'c' => 'criterions'
            )
        );

        $grid = $this->getGrid($select, array(
                'id' => array('hidden' => true),
                'title' => array(
                    'title' => _('Название'),
                ),
                'description' => array(
                    'title' => _('Описание'),
                ),
            )
        );

        $grid->addAction(array(
            'module' => 'criterion',
            'controller' => 'list',
            'action' => 'edit'
        ),
            array('id'),
            $this->view->icon('edit')
        );

        $grid->addAction(array(
            'module' => 'criterion',
            'controller' => 'list',
            'action' => 'delete'
        ),
            array('id'),
            $this->view->icon('delete')
        );

        $grid->addMassAction(
            array(
                'module' => 'criterion',
                'controller' => 'list',
                'action' => 'delete-by',
            ),
            _('Удалить критерии'),
            _('Вы уверены?')
        );

        $this->view->grid = $grid->deploy();
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
    }

    public function create($form)
    {
        $values = $form->getValues();
        unset($values['id']);
        $res = $this->getService('Criterion')->insert($values);
    }

    public function update($form)
    {
        $values = $form->getValues();
        $res = $this->getService('Criterion')->update($values);
    }


    public function delete($id) {
        $this->getService('Criterion')->delete($id);
    }

    public function setDefaults(Zend_Form $form)
    {
        $id = $this->_getParam('id', 0);
        $data = $this->getService('Criterion')->find($id)->asArray();
        $form->populate($data[0]);
    }

}
