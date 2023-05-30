<?php
class HM_Form_Policies extends HM_Form
{

    public function init()
    {   
        
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('Policies');
        
        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'value' => $this->getView()->url(array('module' => 'default', 'controller' => 'index', 'action' => 'index'))
        ));
       
        
        $this->addElement('text', 'passwordMinLength', array(
            'Label' => _('Минимальная длина пароля'),
			'Description' => _('Минимальное число символов для создания пароля'),
            'Validators' => array(
        		array('Int'), array('GreaterThan', false, array(0))),
            'Filters' => array(
            	'Int'),
       ));
       
       $this->addElement('text', 'passwordMinNoneRepeated', array(
            'Label' => _('Минимальное количество неповторяющихся паролей'),
			'Description' => _('При нулевом значении разрешается использование одного и того же старого пароля'),
            'Validators' => array(
        		array('Int'), array('GreaterThan', false, array(-1))),
            'Filters' => array(
            	'Int'),
       ));

       $this->addElement('text', 'passwordMaxPeriod', array(
            'Label' => _('Максимальный срок действия'),
			'Description' => _('Период времени (в днях) использования пароля, прежде чем система потребует заменить его. Нулевое значение снимает это ограничение'),
            'Validators' => array(
        		array('Int'), array('GreaterThan', false, array(-1))),
            'Filters' => array(
            	'Int'),
       ));
       $this->addElement('text', 'passwordMinPeriod', array(
            'Label' => _('Минимальный срок действия'),
			'Description' => _('Период времени (в днях) использования пароля, прежде чем пользователь сможет заменить его. Нулевое значение снимает это ограничение'),
            'Validators' => array(
            		array('Int'), 
            		array('GreaterThan', false, array(-1)),
            		//array('SmallerThan', 'maxPeriod', array(0)),
        		),
            'Filters' => array(
            	'Int'),
       ));
       
       //Максимальный срок действия
                    
             
         $this->addElement('checkbox', 'passwordCheckDifficult', array(
             'Label' => _('Проверять сложность пароля'),
         	 'Value' => 0
         ));
         
         $this->addElement('text', 'passwordMaxFailedTry', array(
            'Label' => _('Максимальное количество неуспешных попыток авторизации'),
            'Validators' => array(
        		array('Int'), array('GreaterThan', false, array(0))),
            'Filters' => array(
            	'Int'),
         ));
         $this->addElement('select', 'passwordFailedActions', array(
            'Label' => _('Действие системы по достижению максимального количества попыток'),
            'Filters' => array('Int'),
         	'multiOptions' => HM_User_Password_PasswordModel::getWrongPasswordTypes()
         ));
         
         
         $this->addElement('RadioGroup', 'passwordRestriction', array(
            'Label' => '',
        	'Value' => 0,
            //'Required' => true,
            'MultiOptions' => HM_User_Password_PasswordModel::getRestrictionTypes(),
            'form' => $this,
            'dependences' => array(
                                 1 => array('passwordMaxFailedTry', 'passwordFailedActions')
                             )
        ));
         
         
         
         
  /*      
         Без ограничений
С ограничением
* Максимальное количество неуспешных попыток авторизации; тип - text;
* Действие системы по достижению максимального количества попыток; тип - select, значения: 
* 	"Требовать подтверждение ручного ввода пароля (captcha)" и 
* 	"Блокировать учетную запись"
        */
        $this->addDisplayGroup(
	            array(
    	            'passwordMinLength',
    	        	'passwordMinNoneRepeated',
    	            'passwordCheckDifficult'
	           ), 
	           'Requirements', 
	           array(
	           	   'legend' => _('Требования к паролю')
	           )
	    );
	    
	    $this->addDisplayGroup(
	            array(
    	            'passwordMaxPeriod',
    	        	'passwordMinPeriod',
	           ), 
	           'Dates', 
	           array(
	           	   'legend' => _('Срок действия пароля')
	           )
	    );
	    
	    
	    $this->addDisplayGroup(
	            array(
	                'passwordRestriction',
    	            'passwordMaxFailedTry',
    	        	'passwordFailedActions',
	           ), 
	           'Restriction', 
	           array(
	           	   'legend' => _('Ограничение неуспешных попыток авторизации')
	           )
	    );

	    $this->addElement('Submit', 'submit', array(
            'Label' => _('Сохранить')));
      
	    
	    
	    
        parent::init(); // required!
    }
    
    public function getElementDecorators($alias, $first = 'ViewHelper'){
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
    
    
    }
}