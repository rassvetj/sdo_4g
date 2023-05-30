<?php
class Scale_ValueController extends HM_Controller_Action_Crud
{
    private $_scale;

    public function init()
    {
        $form = new HM_Form_Value();
        $this->_setForm($form);

        parent::init();

        $scaleId = (int) $this->_getParam('scaleId', 0);
        if ($scaleId) {
            $this->_scale = $this->getOne(
                $this->getService('Scale')->find($scaleId)
            );
        } else {
            $this->_redirector->gotoSimple('index', 'list', 'scale');
        }

        $this->getService('Unmanaged')->setHeader(_('Значения шкалы'));
        $this->getService('Unmanaged')->setSubHeader($this->_scale->name);

    }

    public function indexAction()
    {
        $select = $this->getService('ScaleValue')->getSelect();

        $select->from(
            array(
                'sv' => 'scale_values'
            ),
            array(
                'value_id',
                'value',
                'text',
                'description',
            )
        );

        $select
            ->where('scale_id = ?', $this->_scale->scale_id);

        $grid = $this->getGrid($select, array(
            'value_id' => array('hidden' => true),
            'value' => array('title' => _('Значение')),
            'text' => array('title' => _('Текстовое значение')),
            'description' => array('title' => _('Описание')),
        ),
            array(
                'value' => null,
                'text' => null,
                'description' => null,
            )
        );

        $grid->addAction(array(
            'module' => 'scale',
            'controller' => 'value',
            'action' => 'edit'
        ),
            array('value_id'),
            $this->view->icon('edit')
        );

        $grid->addAction(array(
            'module' => 'scale',
            'controller' => 'value',
            'action' => 'delete'
        ),
            array('value_id'),
            $this->view->icon('delete')
        );

        $grid->addMassAction(
            array(
                'module' => 'scale',
                'controller' => 'value',
                'action' => 'delete-by',
            ),
            _('Удалить значения'),
            _('Вы уверены?')
        );

        $this->view->grid = $grid->deploy();

    }

    protected function _redirectToIndex()
    {
        $this->_redirector->gotoSimple('index', null, null, array('scaleId' => $this->_scale->scale_id));
    }

    public function create($form)
    {
        $values = $form->getValues();
        unset($values['scale_value_id']);
        $values['scale_id'] = $this->_scale->scale_id;
        $res = $this->getService('ScaleValue')->insert($values);
    }

    public function update($form)
    {
        $values = $form->getValues();
        $values['scale_id'] = $this->_scale->scale_id;
        $res = $this->getService('ScaleValue')->update($values);
    }

    public function delete($id) {
        $this->getService('ScaleValue')->delete($id);
    }

    public function setDefaults(Zend_Form $form)
    {
        $valueId = $this->_getParam('value_id', 0);
        $value = $this->getService('ScaleValue')->find($valueId)->current();
        $data = $value->getData();
        $form->populate($data);
    }
}
