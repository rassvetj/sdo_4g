<?php
class HM_Form_GroupPage extends HM_Form{
	
	public function init(){
		
		$this->setMethod(Zend_Form::METHOD_POST);
        
        $this->setName('group-page');
        
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

        $roles = HM_Role_RoleModelAbstract::getBasicRoles(true, true);

        $this->addElement('select', 'role',
            array(
                'Label' => _('Роль'),
                'Required' => false,
                'Validators' => array(),
                'Filters' => array('StripTags'),
                'MultiOptions' => $roles
            )
        );
        
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
                
		$this->addElement('Submit', 'submit', array(            
            'Label' => _('Сохранить')
        ));

        $this->addDisplayGroup(array(
            'cancelUrl',
            'group_id',
        	'role',
        	'name',
        	'ordr',
            'submit'),
            'groupPages',
            array(
            'legend' => _('Группа страниц')
            ));
        
        parent::init(); // required!
        
	}
	
}