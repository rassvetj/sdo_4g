<?php
class HM_Form_Department extends HM_Form
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
            'Required' => false,
            'Filters' => array('Int'),
            'Value' => $parent
        )
        );

        $this->addElement('hidden', 'type', array(
            'Required' => false,
            'Filters' => array('Int'),
            'Value' => HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT
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
//		'Description' => _('Краткое обозначение подразделения'), // такие подсказки больше похожи на издевательство
            'Required' => false,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array('StripTags')
        )
        );


        $this->addElement($this->getDefaultWysiwygElementName(), 'info', array(
                'Label' => _('Описание'),
                'Required' => false,
                'class' => 'wide',
                'Validators' => array(
                    array('StringLength', 1000, 1),
                ),
                'Filters' => array('HtmlSanitizeRich'),
            )
        );

        $this->addDisplayGroup(array(
            'cancelUrl',
             'name',
             'code',
             'info'
        ),
            'Users1',
            array('legend' => _('Подразделение'))
        );

        $this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));

        return parent::init();


    }


}