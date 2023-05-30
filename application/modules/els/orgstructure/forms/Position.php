<?php
class HM_Form_Position extends HM_Form
{
    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('deparment');

        $parent = (int) $this->getParam('parent', 0);

        $orgId = (int) $this->getParam('org_id', 0);
        if ($orgId) {
            $item = $this->getService('Orgstructure')->getOne(
                $this->getService('Orgstructure')->find($orgId)
            );

            if ($item) {
                $parent = $item->owner_soid;
            }
        }


        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(array(
                                                'module' => 'orgstructure',
                                                'controller' => 'list',
                                                'action' => 'index',
                                                'key' => $parent
                                           ), null, true)
        ));

        $this->addElement('hidden', 'soid', array(
            'Required' => true,
            'Filters' => array('Int'),
            'Value' => 0
        )
        );

        $this->addElement('hidden', 'owner_soid', array(
            'Required' => true,
            'Filters' => array('Int'),
            'Value' => $parent
        )
        );

        $this->addElement('hidden', 'type', array(
            'Required' => true,
            'Filters' => array('Int'),
            'Value' => HM_Orgstructure_OrgstructureModel::TYPE_POSITION
        )
        );

        $this->addElement('text', 'name', array('Label' => _('Название'),
            'Required' => true,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array('StripTags')
        )
        );

        $this->addElement('text', 'code', array('Label' => _('Краткое название'),
//		'Description' => _('Краткое обозначение штатной должности'), // такие подсказки больше похожи на издевательство
            'Required' => false,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array('StripTags')
        )
        );

        $this->addElement('textarea', 'info',
            array(
            	'Label' => _('Дополнительная информация'),
            	'Required' => false,
            	'Validators' => array(
                    array('StringLength', 1000, 1),
                ),
            	'Filters' => array('StripTags')
            )
        );

        $this->addElement('checkbox', 'is_manager', array(
                'Label' => _('Руководитель подразделения'),
                'Required' => false,
                //'Description' => _('Если флажок установлен, то д.'),
            )
        );

        $this->addElement(new HM_Form_Element_FcbkComplete('mid', array(
                'required' => true,
                'Label' => _('Пользователь'),
				'Description' => _('Для поиска можно вводить любое сочетание букв из фамилии, имени и отчества'),
                'json_url' => $this->getView()->url(array('module' => 'user', 'controller' => 'ajax', 'action' => 'users-list'), null, true),
                'newel' => false,
                'maxitems' => 1
            )
        ));

        $this->addDisplayGroup(array(
             'cancelUrl',
             'name',
             'mid',
             'code',
             'is_manager',
             'info'
        ),
            'Users1',
            array('legend' => _('Штатная должность'))
        );

        $this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));

        return parent::init();

    }
}