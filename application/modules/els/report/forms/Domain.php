<?php
class HM_Form_Domain extends HM_Form
{
	public function init()
	{
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('domain');

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(array('module' => 'report', 'controller' => 'list', 'action' => 'index'), null, true)
        ));

        $config = new HM_Report_Config();

        $this->addElement('select', 'domain', array(
                'label' => _('Область отчёта'),
                'required' => true,
                'multiOptions' => $config->getDomains()
            )
        );

        $this->addElement('text', 'name', array(
                'label' => _('Название шаблона отчёта'),
                'required' => true,
            )
        );

        /*
        $this->addElement('checkbox', 'status', array(
                'label' => _('Опубликован'),
            )
        );
        */

        $roles = HM_Role_RoleModelAbstract::getBasicRoles(false, true); // без гостя, с объединением младших ролей в enduser'а

        $this->addElement('multiCheckbox', 'roles', array(
            'Label' => _('Доступен для ролей'),
            'Required' => false,
            'Validators' => array(
            ),
            'Filters' => array(
            ),
            'separator' => '<br/><br/>',
            'MultiOptions' => $roles
        ));

        $this->addDisplayGroup(
            array(
                'cancelUrl',
                'domain',
                'name',
                //'status',
                'roles',
                'submit'
            ),
            'resourceGroup',
            array('legend' => _('Общие'))
        );

        $this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
	}

}