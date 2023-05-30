<?php
class Hostel_IndexController extends HM_Controller_Action
{    	
	public function init(){
		if(!$this->getService('User')->isMainOrganization()){
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
                      'message' => _('У вас нет доступа к этому разделу'))
			);			
			$this->_redirect('/');		
		}
	}
	
	public function indexAction()
    {   
		$this->_redirect('/');
		//pr($this->_flashMessenger);
		
		$this->view->headLink()->appendStylesheet('/css/rgsu_style.css');  	
		
		$user = $this->getService('User')->getCurrentUser();		
        $mid_external = $user->mid_external;
		
		
		$select = $this->getService('HostelClaims')->getDefaultSelect($user->mid_external);	
		
		$grid = $this->getGrid($select,
            array(				
				'address_name' => array('title' => _('Общежитие')),				
				'room_name' => array('title' => _('Комната')),				
				'date_create' => array('title' => _('Дата сооздания')),				
				'date_update' => array('title' => _('Дата изменения')),								
				'status' => array('title' => _('Статус')),
				'type' => array('title' => _('Тип')),								
				'date_reject' => array('title' => _('Дата отклонения')),
				'reason_reject' => array('title' => _('Причина отклонения')),
				'claim_id' => array('title' => _('Действия')),				
			),
			array(				
			),
			'grid'
		);
		
		
		$grid->updateColumn('date_create', array(
            'format' => array(
                'DateTime',
                array('date_format' => 'dd.MM.yyyy   ') //--что-то странное с форматом происходит.
            ),
            'callback' => array(
                'function' => array($this, 'updateDate'),
                'params' => array('{{date_create}}')
            )
        ));		
		$grid->updateColumn('date_update', array(
            'format' => array(
                'DateTime',
                array('date_format' => 'dd.MM.yyyy   ')
            ),
            'callback' => array(
                'function' => array($this, 'updateDate'),
                'params' => array('{{date_update}}')
            )
        ));		
		$grid->updateColumn('date_reject', array(
            'format' => array(
                'DateTime',
                array('date_format' => 'dd.MM.yyyy   ')
            ),
            'callback' => array(
                'function' => array($this, 'updateDate'),
                'params' => array('{{date_reject}}')
            )
        ));		
		$grid->updateColumn('type',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateType'),
                    'params' => array('{{type}}')
                )
            )
        );
		$grid->updateColumn('claim_id',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateAction'),
                    'params' => array('{{claim_id}}', '{{status}}')
                )
            )
        );
		$grid->updateColumn('status', 
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateStatus'),
                    'params' => array('{{status}}')
                )
            )
        );
		
		
		$this->view->grid = $grid->deploy();
		$this->view->gridAjaxRequest = $this->isAjaxRequest();
		
		
		if(
			!$this->getService('HostelClaims')->isExsistNewOrder($mid_external) //--еще нет новых, необработанных заявок.
		){
			$form = new HM_Form_Hostel();
			$this->view->form = $form;			
			$this->view->isShowFormArea = true;
		}
    }
	
	
	public function getRoomsAction(){
		$this->_helper->getHelper('layout')->disableLayout();		
		$this->getHelper('viewRenderer')->setNoRender();
		
		if ($this->_request->isPost()) {
			$addres_id = $this->_request->getParam('addres_id', false);
			
			if($addres_id){
				$serviceHostel = $this->getService('Hostel');						
				$json = Zend_Json::encode($serviceHostel->getListRooms($addres_id));
			} else {
				$json = Zend_Json::encode(
					array(
						array(
							'key' 	=> '0',
							'value'	=> _('Любая')),
					)
				);
			}
			
			echo $json;
		}		
		exit();
	}
	
	public function updateDate($date)
    {
        if (!strtotime($date)) return '';
        return $date;
    }
	
	public function updateStatus($status_id) {
        $statuses = HM_Hostel_Claims_ClaimsModel::getStatuses();
		
		return $statuses[$status_id];
    }
	
	public function updateType($type_id) {        
		$types = HM_Hostel_Claims_ClaimsModel::getTypes();
		
		return $types[$type_id];
    }
	
	public function updateAction($claim_id, $status){				
		if(!$claim_id){
			return '';
		}
		
		if(!is_int($status)){ //-определяем по тексту статуса его ID
			$statusesIDs = HM_Hostel_Claims_ClaimsModel::getStatusesIDs();
			$status = $statusesIDs[$status];
		}
		
		$claim_id = (int)$claim_id;
		$status = (int)$status;
		
		if($claim_id < 1){
			return '';
		}
		
		if($status == HM_Hostel_Claims_ClaimsModel::STATUS_NEW){
			return '<a class="vButton ui-button ui-widget ui-state-default ui-corner-all" onClick="if (confirm(\'Отменить заявку?\')) return true; return false;" href="/hostel/edit/cancel/claim_id/'.$claim_id.'">'._('Отменить заявку').'</a>';	
		}
		return '';		
	}
	
}