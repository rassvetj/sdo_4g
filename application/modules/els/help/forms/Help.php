<?php
class HM_Form_Help extends HM_Form
{

    public function init()
    {   
        
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('Help');
        
/*        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false
        ));*/
        
        $this->addElement('hidden', 'help_id', array(
            'Required' => true,
            'Validators' => array(
                'Int'),
            'Filters' => array(
                'Int')));
/*        
        $this->addElement('hidden', 'hmodule', array(
            'Required' => true,
            'Validators' => array(
        		array('StringLength', 255, 1)),
            'Filters' => array(
            	'StripTags')));
        
        $this->addElement('hidden', 'hcontroller', array(
            'Required' => true,
            'Validators' => array(
        		array('StringLength', 255, 1)),
            'Filters' => array(
            	'StripTags')));
        
        $this->addElement('hidden', 'haction', array(
            'Required' => true,
            'Validators' => array(
        		array('StringLength', 255, 1)),
            'Filters' => array(
            	'StripTags')));
                
        $this->addElement('hidden', 'page_id', array(
            'Required' => true,
            'Validators' => array(
        		array('StringLength', 255, 1)),
            'Filters' => array(
            	'StripTags')));*/
        		
        $this->addElement('text', 'title', array(
            'Label' => _('Заголовок'),
            'Validators' => array(
        		array('StringLength', 255, 1)),
            'Filters' => array(
            	'StripTags'),
        	'Style' => 'width:500px'
       ));
                    
        $this->addElement($this->getDefaultWysiwygElementName(), 'text', array(
            'Label' => _('Текст помощи'),
            'toolbar' => 'hmToolbarMaxi',
            'Required' => false,
            'Validators' => array(
                array('StringLength',4096,3)
            ),
            'Filters' => array('HtmlSanitizeRich'),
            'toolbar' => 'hmToolbarMaxi'
        ));
        
         $this->addElement('checkbox', 'moderated', array(
             'Label' => _('Отрецензировано'),
         	 'Value' => 0
         ));
        
        
        $this->addElement('Button', 'cancel', array(
            'Label' => _('Отмена'),
        	'onClick' => "$('#ui-lightdialog-1').dialog('close');"
        ));
                
        $this->addElement('Submit', 'submit', array(
            'Label' => _('Сохранить')));
        
        $textField = $this->getElement('text');
        $textField->setAttrib('width', 650)
                  ->setAttrib('height', 200);
    
        
        $this->addDisplayGroup(
	        array(
	            'cancelUrl',
	            'help_id',
	        	'hmodule',
	        	'hcontroller',
	        	'haction',
	        	'page_id',
	            'title',
	            'text',
	        	'moderated',
	            'submit',
	        	'cancel'
	        ), 
	            'Help', array(
	            'legend' => _('Помощь'))
	    );

        parent::init(); // required!
    }
    
    
    public function getElementDecorators($alias, $first = 'ViewHelper'){
        if(in_array($alias, array('moderated'))){
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