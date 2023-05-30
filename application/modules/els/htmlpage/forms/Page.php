<?php
class HM_Form_Page extends HM_Form{
	
	public function init(){
		
		$this->setMethod(Zend_Form::METHOD_POST);
        
        $this->setName('page');
        
        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(
                array(
                    'module' => 'htmlpage',
                    'controller' => 'list',
                    'action' => 'index'
                )
            )
        ));
        
        $this->addElement('hidden', 'page_id', array(            
            'Required' => true,
            'Validators' => array(
                'Int'
            ),
            'Filters' => array(
                'Int'
            )
        ));
        
        $this->addElement('hidden', 'group_id', array(            
            'Required' => true,
            'Validators' => array(
                'Int'
            ),
            'Filters' => array(
                'Int'
            )
        ));
        
        $this->addElement('text', 'name', array(
            'Label' => _('Название'),
            'Required' => true,
            'Validators' => array(
                array('StringLength',
                    255,
                    1
                )
            ),
            'Filters' => array('StripTags')
        )
        );
		
		// Add pagetitle translation
        $this->addElement('text', 'nametranslation', array(
            'Label' => _('Название').(' (en)'),
            'Required' => false,
            'Validators' => array(
                array('StringLength',
                    255
                )
            ),
            'Filters' => array('StripTags')
        )
        );		

        $this->addElement('text', 'ordr', array(
            'Label' => _('Порядок следования'),
            'Required' => false,
            'Value' => HM_Htmlpage_HtmlpageModel::ORDER_DEFAULT,
            'Validators' => array(
                array('Digits')
            ),
            'Filters' => array('StripTags')
        )
        );        

        $this->addElement('text', 'url', array(
                'Label' => _('URL-адрес для перенаправления'),
                'Required' => false,
                'Validators' => array(
                    array('StringLength',
                        255,
                        1
                    )
                ),
                'Filters' => array('StripTags')
            )
        );
        
        $this->addElement($this->getDefaultWysiwygElementName(), 'text', array(
            'Label' => _('Содержимое'),
            //'Filters' => array('HtmlSanitizeRich'),
            'Required' => false,
            'connectorUrl' => $this->getView()->url(array(
                'module' => 'storage',
                'controller' => 'index',
                'action' => 'elfinder'
            )),
            'toolbar' => 'hmToolbarMaxi',
            'fmAllow' => true
        )
        );
		
		// Add new Wysiwyg Textarea for english translation for Page
        $this->addElement($this->getDefaultWysiwygElementName(), 'translation', array(
            'Label' => _('Перевод'),
            //'Filters' => array('HtmlSanitizeRich'),
            'Required' => false,
            'connectorUrl' => $this->getView()->url(array(
                'module' => 'storage',
                'controller' => 'index',
                'action' => 'elfinder'
            )),
            'toolbar' => 'hmToolbarMaxi',
            'fmAllow' => true
        )
        );
        
		$this->addElement('Submit', 'submit', array(            
            'Label' => _('Сохранить')
        ));

        $this->addDisplayGroup(array(
            'cancelUrl',
            'page_id',
        	'name',
			'nametranslation',
        	'ordr',
        	'url',
        	'text',
			'nametranslation',
        	'translation',
            'submit'),
            'groupPages',
            array(
            'legend' => _('Информационная страница')
            ));
        
        parent::init(); // required!
        
	}
	
}