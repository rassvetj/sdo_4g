<?php

class HM_Form_Authorization extends HM_Form
{
    public function init()
	{
        //$this->setAction($this->getView()->url(array('action' => 'process', 'controller' => 'login', 'module' => 'user')));
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('authorization');

        //$this->setAttrib('enctype', 'multipart/form-data');
        $this->addElement('hidden', 'ref', array(
            'Value' => $_SERVER['REQUEST_URI'],
            'Required' => false,
        ));
        
        $this->addElement('hidden', 'start_login', array(
            'Value' => 1,
            'Required' => true
        ));

        $this->addElement('text', 'login', array(
            'Label' => _('Логин:'),
            'Required' => true
        ));

        $this->addElement('password', 'password', array(
            'Label' => _('Пароль:'),
            'Required' => true
        ));
        
        $passwordOptions = $this->getService('Option')->getOptions(HM_Option_OptionModel::SCOPE_PASSWORDS);

        $login = $this->getParam('login', false);
        if ($login || isset($_COOKIE['hmcaptcha'])) {
            $captcha = $this->getService('Captcha')->getOne($this->getService('Captcha')->find($login));
            if ($captcha && ($captcha->attempts >= $passwordOptions['passwordMaxFailedTry']) 
                && $passwordOptions['passwordFailedActions'] == HM_User_Password_PasswordModel::TYPE_CAPTCHA 
                && $passwordOptions['passwordRestriction'] == HM_User_Password_PasswordModel::RESTRICTION_WITH 
                || isset($_COOKIE['hmcaptcha'])) {

                if (!isset($_COOKIE['hmcaptcha'])) {
                    setcookie('hmcaptcha', true, time() + 3600*24*30*6, '/');
                }

                $this->addElement('captcha', 'captcha', array(
                    'Required' => true,
                    'Label' => _('Код подтверждения:'),
                    'captcha' => 'Image',
                    'separator' => '',
                    'captchaOptions' => array(
                        'captcha' => 'Image',
                        'width' => 145,
                        'height' => 45,
                        'wordLen' => 6,
                        'timeout' => 300,
                        'expiration' => 300,
                        'font'      => APPLICATION_PATH . '/../public/fonts/ptsans.ttf', // Путь к шрифту
                        'imgDir'    => APPLICATION_PATH . '/../public/upload/captcha/', // Путь к изобр.
                        'imgUrl'    => Zend_Registry::get('config')->url->base.'upload/captcha/', // Адрес папки с изображениями
                        'gcFreq'    => 5,
        				'DotNoiseLevel' => HM_Form_Element_Captcha::NOISE_LEVEL,
        				'LineNoiseLevel' => HM_Form_Element_Captcha::NOISE_LEVEL,                    
                    )

                ));
            }
        }

		$this->addElement('submit', 'submit', array(
            'Label' => _('Войти'),
        ));

        $this->addElement('checkbox', 'remember', array(
            'Required' => false,
            'Label' => _('Запомнить')
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