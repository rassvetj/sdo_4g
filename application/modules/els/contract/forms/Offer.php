<?php
class HM_Form_Offer extends HM_Form
{

    public function init()
    {   
        
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('Offer');
        
        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'value' => $this->getView()->url(array('module' => 'default', 'controller' => 'index', 'action' => 'index'))
        ));
        
//        $this->addElement('RadioGroup', 'regAllow', array(
//            'Label' => _('Свободная регистрация'),
//            'Description' => _('Если установлена данная опция, система позволяет новым пользователям самостоятельно регистрироваться в системе; возможность доступа непосредственно к учебным материалам определяется настройками этих материалов.'),
//            'MultiOptions' => array(
//                0 => 'Запретить регистрацию без подачи заявки на курс',
//                1 => 'Разрешить свободную регистрацию',
//            ),
//            'form' => $this,
//            'dependences' => array(
//                0 => array(),
//                1 => array('regRequireAgreement', 'regUseCaptcha', 'regValidateEmail', 'regAutoBlock'),
//            )
//        ));    
        
        $this->addElement('checkbox', 'regDeny', array(
            'Label' => _('Запретить регистрацию без подачи заявки на курс'),
            'Description' => _('Если установлена данная опция, система не позволяет новым пользователям самостоятельно регистрироваться в системе; возможность доступа непосредственно к учебным материалам определяется настройками этих материалов.'),
            
        ));
        
        $this->addElement('checkbox', 'regRequireAgreement', array(
            'Label' => _('Требовать согласие на обработку персональных данных'),
            'Description' => _('Если установлена данная опция, пользователю в процессе регистрации будет предложено ознакомиться с условиями и подтвердить согласие на хранение и обработку его персональных данных.'),
        ));        
       
        $this->addElement('checkbox', 'regUseCaptcha', array(
            'Label' => _('Подтверждать ручной ввод данных (CAPTCHA)'),
            'Description' => _('Если установлена данная опция, система потребует подтвердить ручной ввод данных с помощью механизма CAPTCHA.'),
        ));        
       
        $this->addElement('checkbox', 'regValidateEmail', array(
            'Label' => _("Блокировать нового пользователя до подтверждения email'a"),
            'Description' => _("Если установлена данная опция, система автоматически заблокирует вновь созданную учетную запись, до подтверждения email'а, введенного при регистрации."),
        ));        
       
        $this->addElement('checkbox', 'regAutoBlock', array(
            'Label' => _('Блокировать нового пользователя до проверки администрацией'),
            'Description' => _('Если установлена данная опция, система автоматически заблокирует вновь созданную учетную запись, с возможностью последующего ручного разблокирования администратором.'),
        ));        
       
        $this->addElement($this->getDefaultWysiwygElementName(), 'contractOfferText', array(
            'Label' => _('Публичная оферта на оказание образовательных услуг'),
            'Required' => true,
            'Validators' => array(
                array(
                    'validator' => 'StringLength',
                    'options' => array('min' => 3)
            )),
            'Filters' => array('HtmlSanitizeRich'),
            'connectorUrl' => $this->getView()->url(array(
                'module' => 'storage',
                'controller' => 'index',
                'action' => 'elfinder',
                'subject' => $this->getView()->subjectName,
                'subject_id' => $this->getView()->subjectId
            )),
            'toolbar' => 'hmToolbarMidi',
            'fmAllow' => true
        ));
        
        $this->addElement($this->getDefaultWysiwygElementName(), 'contractPersonalDataText', array(
            'Label' => _('Согласие на обработку персональных данных'),
            'Required' => true,
            'Validators' => array(
                array(
                    'validator' => 'StringLength',
                    'options' => array('min' => 3)
            )),
            'Filters' => array('HtmlSanitizeRich'),
            'connectorUrl' => $this->getView()->url(array(
                'module' => 'storage',
                'controller' => 'index',
                'action' => 'elfinder',
                'subject' => $this->getView()->subjectName,
                'subject_id' => $this->getView()->subjectId
            )),
            'toolbar' => 'hmToolbarMidi',
            'fmAllow' => true
        ));
     
        $this->addDisplayGroup(
                array(
                    'regDeny',
//                    'regAllow', 
                    'regRequireAgreement', 
                    'regUseCaptcha', 
                    'regValidateEmail', 
                    'regAutoBlock'
               ), 
               'Allow', 
               array(
                      'legend' => _('Регистрационные требования')
               )
        );
        
        $this->addDisplayGroup(
                array(
                    'contractOfferText',
                    'contractPersonalDataText'
               ), 
               'Requirements', 
               array(
                      'legend' => _('Информационные страницы')
               )
        );
        
        $this->addElement('Submit', 'submit', array(
            'Label' => _('Сохранить'))
        );
      
        $event = new sfEvent(null, HM_Extension_ExtensionService::EVENT_FILTER_FORM_CONTRACT);
        $this->getService('EventDispatcher')->filter($event, $this);
        
        
        parent::init(); // required!
    }
    
   /* public function getElementDecorators($alias, $first = 'ViewHelper'){
        if(in_array($alias, array('checkPasswordDifficult'))){
            return array ( // default decorator
                array($first),
                array('RedErrors'),
                array('Description', array('tag' => 'p', 'class' => 'description')),
                array('Label', array('tag' => 'span', 'placement' => Zend_Form_Decorator_Abstract::APPEND, 'separator' => '&nbsp;')),
                array(array('data' => 'HtmlTag'), array('tag' => 'dd', 'class'  => 'element'))
            );
        }else{
            return parent::getElementDecorators($alias, $first);
        }
    
    
    }*/
}