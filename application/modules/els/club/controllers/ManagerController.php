<?php
class Club_ManagerController extends HM_Controller_Action
{
	private $_serviceClubClaim 	= null;
	
	public function indexAction(){
		
		$this->view->setHeader(_('Заявки по кружкам'));
		
		if(!$this->_serviceClubClaim)	{ $this->_serviceClubClaim 	= $this->getService('ClubClaim');	}
		
		$gridId = 'grid-claim';
		$select = $this->_serviceClubClaim->getSelect();
		$select->from(
            array(
                'cc' => 'club_claims'
            ),
            array(
                'claim_id'		=>	'cc.claim_id',	
                'user_id'		=>	'cc.user_id',	
                'fio'			=>	'cc.fio',
				'club_name'		=>	'cl.name',	                	
                'group_name'	=>	'cc.group_name',	
                'email'			=>	'cc.email',	
                'date_created'	=>	'cc.date_created',	
			)
		);
		$select->joinLeft(array('cl' => 'clubs'), 'cl.club_id = cc.club_id', array());		 
		
		
		$grid = $this->getGrid(
            $select,
            array(
                'claim_id' 		=> array('hidden' => true),
                'user_id' 		=> array('hidden' => true),				
				'fio' 			=> array('title' => _('Студент')),
				'club_name'		=> array('title' => _('Кружок')),
				'group_name'	=> array('title' => _('Группа')),
				'email'			=> array('title' => _('Email')),				
                'date_created'	=> array('title' => _('Дата подачи')),
            ),
            array(
				'fio' 			=> null,
				'club_name'		=> null,
				'group_name'	=> null,
				'email' 		=> null,
				'date_created'	=> array('render' => 'DateSmart'),
            ),
            $gridId
        );

		$grid->updateColumn('date_created', array(
            'format' => array(
                'DateTime',
                array('date_format' => Zend_Locale_Format::getDateTimeFormat())                
															 
            ),
            'callback' => array(
                'function' => array($this, 'updateDate'),
                'params' => array('{{date_created}}')
            )
        ));		
				
		$this->view->grid 				= $grid->deploy();
		$this->view->gridAjaxRequest	= $this->isAjaxRequest();		

	}
	
	public function updateDate($date){
     	if (!strtotime($date)) return '';
		
        return $date;
    }
	
	
	
}