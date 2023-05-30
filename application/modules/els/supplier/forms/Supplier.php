<?php
class HM_Form_Supplier extends HM_Form {
	
	public function init(){
		        
        $this->setMethod(Zend_Form::METHOD_POST);
        
        $this->setName('supplier');
        
        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(
                array(
                    'module' => 'supplier',
                    'controller' => 'list',
                    'action' => 'index'
                )
            )
        )
        );
        
        $this->addElement('hidden', 'supplier_id', array(            
            'Required' => true,
            'Validators' => array(
                'Int'
            ),
            'Filters' => array(
                'Int'
            )
        ));
        
        $this->addElement('text', 'title', array(
            'Label' => _('Название'),
            'Required' => true,
            'Validators' => array(
                array('StringLength',
                    255,
                    1
                )
            ),
            'Filters' => array('StripTags'),
            'class' => 'wide'
        )
        );
        
        $this->addElement('textarea', 'address', array(
            'Label' => _('Адрес'),
            'rows' => 5,
            'Required' => false,
            'Validators' => array(
                array('StringLength', 4000, 0),
            ),
            'Filters' => array(
                'StripTags'
            )

        ));
        
        $this->addElement('textarea', 'contacts', array(
            'Label' => _('Контакты'),
            'rows' => 5,
            'Required' => false,
            'Validators' => array(
                array('StringLength', 4000, 0),
            ),
            'Filters' => array(
                'StripTags'
            )

        ));
        
        $this->addElement($this->getDefaultWysiwygElementName(), 'description', array(
            'Label' => _('Описание'),
            'Required' => false,
            'class' => 'wide',
            'Filters' => array('HtmlSanitizeRich'),
        )
        );
        
        $this->addElement('Submit', 'submit', array(            
            'Label' => _('Сохранить')
        ));

        $this->addDisplayGroup(
        	array(
	            'cancelUrl',
	            'supplier_id',
        		'title',
        		'address',
        		'contacts',
        		'description',
	            'submit'
        	),
            'groupSupplier',
            array(
            'legend' => _('Провайдер обучения')
            ));
        
        parent::init(); // required!
        
	}
	
}