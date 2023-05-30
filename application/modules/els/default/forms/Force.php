<?php

class HM_Form_Force extends HM_Form
{
    public function init()
	{
        //$this->setAction($this->getView()->url(array('action' => 'process', 'controller' => 'login', 'module' => 'user')));
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('passwordForce');

        $this->addElement('password', 'password', array(
            'Label' => _('Новый пароль:'),
            'Required' => true,
        	'Validators' => array(
                    array('identical',
                        false,
                        array('token' => 'passwordRepeat')
                    )
                )
        ));

        $this->addElement('password', 'passwordRepeat', array(
            'Label' => _('Подтвердите пароль:'),
            'Required' => true
        ));
        
        
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
        if($passwordOptions['passwordMinPeriod'] > 0){
            $password->addValidator('MinimalDatePassword');
        }
        if($passwordOptions['passwordMinNoneRepeated'] > 0){
            $password->addValidator('AmountPassword');
        }
        
        
        
        $this->addDisplayGroup(
            array(
                'password',
                'passwordRepeat'
            ),
            'rememberGroup',
            array('legend' => _('Обновление пароля'))
        );

		$this->addElement('submit', 'submit', array(
            'Label' => _('Изменить пароль'),
        ));

        parent::init();
	}
    /*
    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return;
        }

        $decorators = $this->getDecorators();

        if (empty($decorators)) {

            $this->addDecorator('FormElements')
                 ->addDecorator('HtmlTag', array('tag' => 'table', 'class' => 'form'))
                 ->addDecorator('Form');
        }
    }*/

    public function getElementDecorators($alias, $first = 'ViewHelper') {
        if ($alias == 'captcha') {
            return array(
                array('RedErrors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'dd', 'class'  => 'element')),
                array('Label', array('tag' => 'dt'))
            );
/*            return array (
                    array('RedErrors'),
                    array('Description', array('tag' => 'p', 'class' => 'description')),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'  => 'element')),
                    array('Label', array('tag' => 'td', 'disableRequired' => false)),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr'))
            );*/
        }

        return parent::getElementDecorators($alias);
        /*
        return array ( // default decorator
                array($first),
                array('RedErrors'),
                array('Description', array('tag' => 'p', 'class' => 'description')),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'  => 'element')),
                array('Label', array('tag' => 'td', 'disableRequired' => false)),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr'))
        );

         */
    }
     /*
    public function getButtonElementDecorators($alias, $first = 'ViewHelper') {
        $decorators = array($first);

        if (null != $this->getElement('prevSubForm')) {
            $decorators[] = array(array('prev' => 'Button'), array('placement' => 'prepend', 'label' => _('Назад'), 'url' => $this->getView()->url(array('subForm' => $this->getElement('prevSubForm')->getValue()))));
        }

        if (null != $this->getElement('cancelUrl')) {
            $decorators[] = array(array('cancel' => 'Button'), array('placement' => 'append', 'label' => _('Отмена'), 'url' => $this->getElement('cancelUrl')->getValue()));
        }

        $decorators = array_merge($decorators, array(

            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'  => 'element', 'colspan' => 2, 'align' => 'left')),
            array(array('row' => 'HtmlTag'), array('tag' => 'tr'))
            
        ));
        return $decorators;
    }
*/

    public function getButtonElementDecorators($alias, $first = 'ViewHelper') {
        $decorators = array(
            $first,
            array(array('data' => 'HtmlTag'), array('tag' => 'dd', 'openOnly' => true))
        );

        return $decorators;
    }

    public function getCheckBoxDecorators($alias, $first = 'ViewHelper')
    {
        return array (
            array($first),
            array('RedErrors'),
            array('Label', array('tag' => 'span', 'placement' => Zend_Form_Decorator_Abstract::APPEND, 'separator' => '&nbsp;')),
            array(array('data' => 'HtmlTag'), array('tag' => 'dd', 'closeOnly' => true))
        );

    }
}