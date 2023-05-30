<?php
class Scale_ListController extends HM_Controller_Action_Crud
{
    public function init()
    {
        $form = new HM_Form_Scale();
        $this->_setForm($form);
        parent::init();
    }

    public function indexAction()
    {
        $select = $this->getService('Scale')->getSelect();

        $select->from(
            array(
                's' => 'scales'
            ),
            array(
                's.scale_id',
                's.name',
                's.type',
                'type_id' => 's.type',
                's.description',
            	'count_values' => new Zend_Db_Expr('COUNT(DISTINCT sv.value_id)'),
            )
        );

        $select
            ->joinLeft(array('sv' => 'scale_values'), 's.scale_id = sv.scale_id', array())
            ->group(array(
                's.scale_id',
                's.name',
                's.description',
                's.type',
            ));
        ;

        $grid = $this->getGrid($select, array(
            'scale_id' => array('hidden' => true),
            'type_id' => array('hidden' => true),
            'name' => array(
                'title' => _('Название'),
                'callback' => array(
                    'function'=> array($this, 'updateName'),
                    'params'=> array('{{scale_id}}', '{{type}}', '{{name}}')
                ),
            ),
            'description' => array(
                'title' => _('Описание'),
            ),
            'type' => array(
                'title' => _('Тип'),
                'callback' => array(
                    'function'=> array($this, 'updateType'),
                    'params'=> array('{{type}}')
                )
            ),
            'count_values' => array(
                'title' => _('Количество значений'),
                'callback' => array(
                    'function'=> array($this, 'updateValues'),
                    'params'=> array('{{scale_id}}', '{{type_id}}', '{{count_values}}')
                )
            ),
        ),
        array(
            'name' => null,
            'count_values' => null,
        ));

        $grid->setActionsCallback(
            array('function' => array($this,'updateActions'),
                'params'   => array('{{type_id}}')
            )
        );

        $grid->addAction(array(
            'module' => 'scale',
            'controller' => 'list',
            'action' => 'edit'
        ),
            array('scale_id'),
            $this->view->icon('edit')
        );

        $grid->addAction(array(
            'module' => 'scale',
            'controller' => 'list',
            'action' => 'delete'
        ),
            array('scale_id'),
            $this->view->icon('delete')
        );

        $grid->addMassAction(
            array(
                'module' => 'scale',
                'controller' => 'list',
                'action' => 'delete-by',
            ),
            _('Удалить шкалы'),
            _('Вы уверены?')
        );

        $grid->setMassActionsCallback(
            array('function' => array($this,'updateActions'),
                'params'   => array('{{type_id}}')
            )
        );

        $this->view->grid = $grid->deploy();
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
    }

    public function create($form)
    {
        $values = $form->getValues();
        unset($values['scale_id']);
//        $this->updateScaleValues($values);
        $res = $this->getService('Scale')->insert($values);
    }

    public function update($form)
    {
        $values = $form->getValues();
//        $this->updateScaleValues($values);
        $res = $this->getService('Scale')->update($values);
    }

    public function updateScaleValues(&$values)
    {
        if ($values['scale_id']) {
            $this->getService('ScaleValue')->deleteBy(array('scale_id = ?' => $values['scale_id']));
        }
        $copy = $values;
        foreach ($copy as $key => $value) {
            $valueId = (int)str_replace('scale_value_', '', $key);
            if ($valueId) {
                if (strlen($value)) {
                    $this->getService('ScaleValue')->insert(array(
                        'scale_id' => $values['scale_id'],
                        'value_id' => $valueId,
                        'value' => $value,
                    ));
                }
                unset($values[$key]);
            }
        }
    }

    public function delete($id) {
        $this->getService('Scale')->delete($id);
    }

    public function setDefaults(Zend_Form $form)
    {
        $scaleId = $this->_getParam('scale_id', 0);
        $scale = $this->getService('Scale')->findDependence('ScaleValue', $scaleId)->current();
        $data = $scale->getData();

        if (count($scale->scaleValues)) {
            foreach ($scale->scaleValues as $value) {
                $data['scale_value_' . $value->value_id] = $value->value;
            }
        }

        $form->populate($data);
    }

    public function updateType($type)
    {
        $types = HM_Scale_ScaleModel::getTypes();
        return isset($types[$type]) ? $types[$type] : HM_Scale_ScaleModel::TYPE_DISCRETE;
    }

    public function updateName($scaleId, $type, $str)
    {
        if (!in_array($type, HM_Scale_ScaleModel::getBuiltInTypes())) {
            return '<a href="' . $this->view->url(array('controller' => 'value', 'action' => 'index', 'scaleId' => $scaleId)) . '">' . $this->view->escape($str) . '</a>';
        } else {
            return $str;
        }
    }

    public function updateValues($scaleId, $type, $num)
    {
        if (!in_array($type, HM_Scale_ScaleModel::getBuiltInTypes())) {
            if ($num == '0') return $num;
            return '<a href="' . $this->view->url(array('controller' => 'value', 'action' => 'index', 'scaleId' => $scaleId)) . '">' . $this->view->escape($num) . '</a>';
        } else {
            switch ($type) {
                case HM_Scale_ScaleModel::TYPE_BINARY:
                    return 2;
                case HM_Scale_ScaleModel::TYPE_TERNARY:
                    return 3;
                case HM_Scale_ScaleModel::TYPE_CONTINUOUS:
                    return '-';
            }
        }
    }

    public function updateActions($type, $actions)
    {
        if (in_array($type, HM_Scale_ScaleModel::getBuiltInTypes())){
            return '';
        }
        return $actions;
    }
}
