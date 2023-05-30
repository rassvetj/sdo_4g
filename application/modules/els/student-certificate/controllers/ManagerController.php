<?php
class StudentCertificate_ManagerController extends HM_Controller_Action_Crud {

	protected $_studCertService = null;

    protected $_studentCertificateID  = 0;
	

	public function init()
    {	
		$this->_studentCertificateID = (int) $this->_getParam('student_certificate_id', 0);
        $this->_studCertService = $this->getService('StudentCertificate');
		
        parent::init();
    }

    public function indexAction()
    {
		
		//$this->view->setContextNavigation('course'); // course - секция меню файла application/settings/context.xml
		
		
		
		$this->getHelper('viewRenderer')->setNoRender();
		
		$config = Zend_Registry::get('config');
		$this->view->setHeader(_('Все заявки'));
		
		//$select = $this->_studCertService->getIndexSelect();		
		$select = $this->_studCertService->getManagerSelect();
		
		$gridId = 'grid';
		
		$grid = $this->getGrid(
            $select,
            array(
                'student_certificate_id' => array('hidden' => true),                
                'StudyCode' 	=> array('hidden' => true),
				'CertID'	=> array('title' => _('ID')),                
				'Type' 		=> array(
					'title' 	=> _('Тип'),
					'callback' 	=> array('function' => array($this, 'updateType'), 'params' => array('{{Type}}')),
				),   
				'Number'	=> array('title' => _('Кол-во')),                
				'Destination'	=> array('title' => _('Место требования')),                
				'Status'	=> array(
					'title' 	=> _('Статус'),
					'callback' 	=> array('function' => array($this, 'updateStatus'), 'params' => array('{{Status}}')),					
				),  					
				'DateCreate'	=> array(
					'title' 	=> _('Дата'),
					'callback' 	=> array('function' => array($this, 'updateDateCreate'), 'params' => array('{{DateCreate}}'))
				),    
				'author'		=> array('title' => _('Заказал')),   
				'Faculty'		=> array('title' => _('Факультет')),
				'GroupName'		=> array('title' => _('Группа')),                
				
				'City'			=> array('title' => _('Город')),                
				'Street'		=> array('title' => _('Улица')),                
				'Postcode'		=> array('title' => _('Индекс')),                
				
				'Employer'		=> array('title' => _('Работодатель')),                
				'Direction'		=> array('title' => _('Направление')),                
				'Submission'	=> array('title' => _('Место представления')),                
				'Course'		=> array('title' => _('Курс')),                
				'Year'			=> array('title' => _('Год')),  				
				'group_name'	=> array('title' => _('Группа')),  				
				'programm_name'	=> array('title' => _('Программа')),  				
				'EMail'			=> array('title' => _('EMail')),  				
								
            ),
            array(
				'Type' 			=> array('values' => HM_StudentCertificate_StudentCertificateModel::getTypes()),
				'Status' 		=> array('values' => HM_StudentCertificate_StudentCertificateModel::getStatuses()),
                'Destination' 	=> null,
                'author' 		=> null,                              
				'DateCreate' 	=> array('render' => 'DateSmart'),				
				'Faculty' 		=> null,
				'GroupName' 	=> null,
				'City' 			=> null,				
				'Street' 		=> null,				
				'Postcode' 		=> null,				
				'Employer' 		=> null,
				'Direction' 	=> null,
				'Submission' 	=> null,
				'Course' 		=> null,
				'Year' 			=> null,
				'group_name'	=> null,
				'programm_name'	=> null,
				'EMail'			=> null,
            ),
            $gridId
        );	

		$grid->addMassAction(
            array('action' => 'set-status'),
            _('Назначить статус')
        );
		
		$grid->addSubMassActionSelect(
            array($this->view->url(array('action' => 'set-status'))),
            'status',            
            HM_StudentCertificate_StudentCertificateModel::getStatuses()
        );
		
		$grid->updateColumn('DateCreate', array(
            'format' => array(
                'DateTime',
                array('date_format' => Zend_Locale_Format::getDateTimeFormat())
                //array('date_format' => Zend_Locale_Format::getDateFormat())
															 
            ),
            'callback' => array(
                'function' => array($this, 'updateDate'),
                'params' => array('{{DateCreate}}')
            )
        )
        );
		
		$content_grid = $grid->deploy();
		

		//$form = new HM_Form_StudentCertificate();		
		//$this->view->form = $form;						
		//$content_form = $this->view->render('index/certificateForm.tpl'); //--для виджета и этой формы разные шаблоны tpl
		
		/* Не аыводит контент в шаблон без явного указания пути и setNoRender*/
		$this->view->gridAjaxRequest = $this->isAjaxRequest();
		
		$this->view->content_grid = $content_grid;		
		//$this->view->content_form = $content_form;		
		$this->view->content_form = '';
		//$content = $this->view->render('index/index.tpl');
		$content = $this->view->render('index/manager.tpl');
		
		
		
		echo $content;
		
		
	}
	
	
	public function updateType($type) {
        $types = HM_StudentCertificate_StudentCertificateModel::getTypes();
        return $types[$type];
    }
	
	public function updateStatus($status) {
        $statuses = HM_StudentCertificate_StudentCertificateModel::getStatuses();
        return $statuses[$status];
    }
	
	public function updateDate($date)
    {
		if (!strtotime($date)) return '';
		
        return $date;
    }
	
	
	
	public function setStatusAction() {
		
        $status = (int) $this->_getParam('status', 0);
        
        $userService = $this->getService('User');
        
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        $result = true;		
		if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach($ids as $id) {
                    $data = array(
                        'CertID' => $id,
                        'Status' => $status,                    
                    );
                    
                    $result = $this->_studCertService->update($data);
					
                    if($result){
                        
						//$user = $userService->find($result->StudyID)->current();
						//$user = $userService->find($result->StudyCode)->current();
						
						$user = false;
						
						$collection = $userService->fetchAll(
							$userService->quoteInto(array('mid_external = ?'), array($result->StudyCode))
						);
						
						if (count($collection) == 1) { //--Если больше одного пользователя, то выборку не делаем
							$user = $userService->getOne($collection);							 
						}
						
						
						//$this->_flashMessenger->addMessage('22'); 
						
                        $statuses = HM_StudentCertificate_StudentCertificateModel::getStatuses();
                        $types = HM_StudentCertificate_StudentCertificateModel::getTypes();
						$fio = $user->LastName . ' ' . $user->FirstName . ' ' . $user->Patronymic;
						$orderStatus = $statuses[$result->Status];
						
						$orderName = $types[$result->Type].', '.$result->Destination.' от '.date('d.m.Y', strtotime($result->DateCreate));
						
						
                        $messageData = array(
                            'id'     => $result->CertID,
                            'title'  => $orderName, //$result->theme,
                            'Status' => $orderStatus,
                            'lfname' => $fio,
                        );
						
						$messageDataEmail = array(
							'id' => $result->CertID,
							'type' => $types[$result->Type],
							'destination' => $result->Destination,
							'date' => date('d.m.Y', strtotime($result->DateCreate)),
							'status' => $orderStatus,
						);
						
						
						
                        //$this->sendMessage($messageData, $result->StudyID, HM_Messenger::TEMPLATE_SUPPORT_STATUS); //24 шаблон
                        //$this->sendMessage($messageData, $result->StudyCode, HM_Messenger::TEMPLATE_SUPPORT_STATUS); //24 шаблон
						
						$this->sendEmail($user->EMail, $fio, $messageDataEmail);
                    }					
                }
            }
        }
        
        if($result){
            $this->_flashMessenger->addMessage(_('Статусы успешно назначены!'));            
        }
        $this->_redirectToIndex();
    }
	
	
	//--Уведомление НЕ создается, и НЕ заносится в БД. Выяснить причину.		
	public function sendMessage($messageData, $user_id, $template) {
        $messenger = $this->getService('Messenger');

        $messenger->setOptions(
            $template,
            $messageData
        );
		
		$messenger->send(HM_Messenger::SYSTEM_USER_ID, $user_id);					
    }
	
	public function sendEmail($toEmail, $toName, $data) {
		//$this->_flashMessenger->addMessage($toEmail);
		//$this->_flashMessenger->addMessage($toName);
		
		if(!$toEmail || !$toName || !$data){
			return false;
		}
		
		$validator = new Zend_Validate_EmailAddress();
		
        if (strlen($toEmail) && $validator->isValid($toEmail)) {
            $mail = new Zend_Mail(Zend_Registry::get('config')->charset);
            
			//$messageText = '<b>№</b> '.$data['id'].'<br>';
			$messageText .= '<b>Тип:</b> '.$data['type'].' от '.$data['date'].'<br>';
			$messageText .= '<b>Место назначения:</b> '.$data['destination'].'<br>';			
			$messageText .= '<b>Текущий статус:</b> '.$data['status'];
			
			$mail->addTo($toEmail, $toName);
            $mail->setSubject('Изменение статуса справки');
			$mail->setType(Zend_Mime::MULTIPART_RELATED);
			$mail->setFromToDefaultFrom();
			$mail->setBodyHtml($messageText, Zend_Registry::get('config')->charset);			
			try {
				$mail->send();
				            
				return true;
            } catch (Zend_Mail_Exception $e) {//  			
                return false;
            }
		}			
		return false;		
	}
	
}