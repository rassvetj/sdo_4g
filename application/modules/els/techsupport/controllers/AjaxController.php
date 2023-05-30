<?php

class Techsupport_AjaxController extends HM_Controller_Action
{

    public function init()
    {
        $this->_helper->getHelper('layout')->disableLayout();
//        $this->getHelper('viewRenderer')->setNoRender();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getResponse()->setHeader('Content-type', 'text/html; charset='.Zend_Registry::get('config')->charset);
    }
    
    public function getFormAction() {
        //не удалять! форма в шаблоне
    }
    
    public function viewAction() {
        $supportRequestId = (int) $this->_getParam('support_request_id', 0);
        $techsupportService = $this->getService('Techsupport');
        
        $request = $techsupportService->find($supportRequestId)->current();
        
        $this->view->request     = $request;

        $this->view->viewPageUrl = $this->view->url(
            array(
                'module'     => 'techsupport',
                'controller' => 'ajax',
                'action'     => 'view-page',
                'support_request_id' => $supportRequestId
            ), null, true
        );
    }

    public function viewPageAction() {
        $this->_redirector = $this->_helper->getHelper('ConditionalRedirector');

        $supportRequestId = (int) $this->_getParam('support_request_id', 0);
        $techsupportService = $this->getService('Techsupport');

        $request = $techsupportService->find($supportRequestId)->current();

        if (!$request) {
            $this->_redirector->gotoSimple('index', 'list', 'techsupport');
            exit();
        }

        if ($request->user_id != $this->getService('User')->getCurrentUserId()) {
            //не делаем проверку на достуаность "Войти от имени", так как страница доступна только администратору
            $this->getService('User')->authorizeOnBehalf($request->user_id);
        }
        $this->_redirector->gotoUrl($request->url);
        exit();
    }


    public function postRequestAction() {
        $this->getHelper('viewRenderer')->setNoRender();
                
        $params  = $this->_getAllParams();
        $request = $this->getRequest();
        $referer = $request->getHeader('referer');
        
        $userService        = $this->getService('User');
        $techsupportService = $this->getService('Techsupport');
		
		$user_id = $userService->getCurrentUserId();
		if(!$user_id){
			echo _('Необходимо авторизоваться!');	
		} else {        
			if(!($params['theme'] == '')){
				$data = array();
				$data['theme']               = $params['theme'];
				$data['problem_description'] = $params['problem_description'];
				$data['wanted_result']       = $params['wanted_result'];
				$data['date_']               = date("Y-m-d H:i:s");
				$data['status']              = HM_Techsupport_TechsupportModel::SATUS_NEW;
				$data['user_id']             = $user_id;
				$data['url']                 = $referer;

				$result = $techsupportService->insert($data);
				if($result){
					echo _('Запрос успешно отправлен!');
					
					$user = $userService->getCurrentUser();
					$statuses = HM_Techsupport_TechsupportModel::getStatuses();
					
					$messageData = array(
						'id'     => $result->support_request_id,
						'title'  => $result->theme,
						'status' => $statuses[$result->status],
						'lfname' => $user->LastName . ' ' . $user->FirstName . ' ' . $user->Patronymic,
					);
					$this->statusChangedMessage($messageData, $result->user_id);
					$this->sendEmail($messageData);
				} else {
					echo _('Ошибка, заявка не была добавлена!');
				}
				
			} else {
				echo _('Не заполнено обязательно поле!');
			}
		}
    }
    
    public function statusChangedMessage($messageData, $user_id) {
        $messenger = $this->getService('Messenger');

        $messenger->setOptions(
            HM_Messenger::TEMPLATE_SUPPORT_STATUS,
            $messageData
        );

        $messenger->send(HM_Messenger::SYSTEM_USER_ID, $user_id);
    }
	
	//--позже перевести на сервис Messenger + шаблон письма. Сечас явное указание получателя.
	public function sendEmail($messageData){
		
		$mail = new Zend_Mail(Zend_Registry::get('config')->charset);
            
		$messageText .= 'Новая заявка в разделе "Техническая поддержка"<br>';
		$messageText .= '<b>Тема:</b> '.$messageData['title'].'<br>';								
		$messageText .= '<b>Текущий статус:</b> '.$messageData['status'].'<br>';
		$messageText .= '<b>Ф.И.О.:</b> '.$messageData['lfname'];
		
		$mail->addTo('helpsdo@rgsu.net');
		$mail->setSubject('Новая заявка в резделе "Техническая поддержка"');
		$mail->setType(Zend_Mime::MULTIPART_RELATED);
		$mail->setFromToDefaultFrom();
		$mail->setBodyHtml($messageText, Zend_Registry::get('config')->charset);			
		try {
			$mail->send();						
			return true;
		} catch (Zend_Mail_Exception $e) {
			return false;
		}	
	}
    
}