<?php
class HM_Form_Generate extends HM_Form {

    public function init() {

        $this->setMethod(Zend_Form::METHOD_POST);

        $this->setName('generate');

        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(array(
                    'module' => 'user',
                    'controller' => 'list',
                    'action' => 'index'
                )
                )
            )
        );

        $this->addElement('text', 'number', array('Label' => _('Количество'),
            'Required' => true,
            'Validators' => array(
                'Int',
                array('GreaterThan', false, array('min' => 0))
            ),
            'Filters' => array(
                'Int'
            )
        )
        );

        $this->addElement('select', 'role', array('Label' => _('Роль'),
            'Required' => true,
            'Validators' => array(),
            'Filters' => array('StripTags'),
            'multiOptions' => HM_Role_RoleModelAbstract::getBasicRoles(false, true) //array_merge(array(_('Пользователь (без роли)')), HM_Role_RoleModelAbstract::getBasicRoles(false))
        )
        );

        $this->addElement('text', 'prefix', array('Label' => _('Логин (префикс)'),
		'Description' => _('Несколько латинских символов, например, stud_, в итоге логин получит вид stud_001, stud_002 и т. д.'),
            'Required' => true,
            'Validators' => array(
                array('Regex', true, '/^[\w-_]+$/'),
                array('StringLength', 255, 1)
            ),
            'Filters' => array(
                'StripTags'
            )
        )
        );

        $this->addElement('password',
            'password',
            array('Label' => _('Пароль'),
                'Required' => true,
                'Validators' => array(
                 ),
                'Filters' => array('StripTags')
            )
        );
        
        $passwordOptions = $this->getService('Option')->getOptions(HM_Option_OptionModel::SCOPE_PASSWORDS);
        $password = $this->getElement('password');
        if($passwordOptions['passwordCheckDifficult'] == 1){
            $password->addValidator('HardPassword');
        }else{
            $password->addValidator('Regex', false, array('/^[a-zа-яёЁ0-9%\\$#!]+$/i'));
        }
        
        if($passwordOptions['passwordMinLength'] > 0){
            $password->addValidator('StringLength', false, array('min' => $passwordOptions['passwordMinLength']));
        }

        $this->addDisplayGroup(array(
            'cancelUrl',
            'number',
            'role',
            'prefix',
            'password'
        ),
            'Generate',
            array('legend' => _('Генерация пользователей'))
        );

        $this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
    }

}