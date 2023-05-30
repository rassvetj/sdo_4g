<?php
class HM_Form_Export extends HM_Form {

	public function init(){

        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('export');

		
		
		if (in_array($this->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR))) {
			$usersGroupList = $this->getService('StudyGroup')->getGroupsByResponsibility($this->getService('User')->getCurrentUserId());
				
			if($usersGroupList && count($usersGroupList)){				
				$list = $usersGroupList;			
			} else {
				$list = array(-1 => _('Нет доступных групп'));
				
			}
			
			$this->addElement('select','groups', array( 
				'label' => _('Группы'),			
				'multiOptions' => $list,
				'Required' => true,	
				'multiple' => 'multiple',
				'style' => 'background-color: #FFFFFF; border: 1px solid #7E9DB9; height: 1.636em; line-height: 1.636em; width: 27.273em;',				
			));
			
			$this->addElement('select','sessions', array( 
				'label' => _('Сессии'),			
				'multiOptions' => array(						
					0 => _('Все'),
				),
				'Required' => true,	
				'multiple' => 'multiple',
				'style' => 'background-color: #FFFFFF; border: 1px solid #7E9DB9; height: 1.636em; line-height: 1.636em; width: 27.273em;',
			));
			
			
			
		}
		
		
		$this->addElement('select','report_type', array( 
			'label' => _('Тип отчета'),			
			'multiOptions' => HM_User_UserModel::getTypeExport(),
			'Required' => true,	
			'style' => 'background-color: #FFFFFF; border: 1px solid #7E9DB9; height: 1.636em; line-height: 1.636em; width: 27.273em;',
		));
		
		
		
        $this->addElement(
            'hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(array(
                'module'     => 'user',
                'controller' => 'export',
                'action'     => 'index'
            ))
        ));

        $this->addElement('DatePicker', 'date_from', array(
            'Label' => _('От'),
            'Required' => false,
            'Validators' => array(
                array(
                    'StringLength',
                false,
                array('min' => 10, 'max' => 50)
                ),
            ),
            'Filters' => array('StripTags'),
            'JQueryParams' => array(
                'showOn' => 'button',
                'buttonImage' => "/images/icons/calendar.png",
                'buttonImageOnly' => 'true'
            ),
            'value' => date('d.m.Y', time()-60*60*24*31),
            'dateFormat' => 'dd.mm.yy',
        ));

        $this->addElement('DatePicker', 'date_to', array(
            'Label' => _('До'),
            'Required' => false,
            'Validators' => array(
                array(
                    'StringLength',
                false,
                array('min' => 10, 'max' => 50)
                ),
                array(
                    'DateGreaterThanFormValue',
                    false,
                    array('name' => 'date_from')
                )
            ),
            'Filters' => array('StripTags'),
            'JQueryParams' => array(
                'showOn' => 'button',
                'buttonImage' => "/images/icons/calendar.png",
                'buttonImageOnly' => 'true'
            ),
            'value' => date('d.m.Y'),
            'dateFormat' => 'dd.mm.yy',
        ));
        
        
		
		if (in_array($this->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR))) {
			$this->addDisplayGroup(
				array(
					'groups',					
					'sessions',					
				),
				'additional',
				array('legend' => _(''))
			);
		}

        $this->addDisplayGroup(
            array(
                'report_type',
                'date_from',
                'date_to',
            ),
			'base',            
            array('legend' => _(''))
        );

      

        $this->addElement(
            'Submit',
            'submit',
            array(
                 'Label' => _('Загрузить')
            ));

        parent::init();

    }

}