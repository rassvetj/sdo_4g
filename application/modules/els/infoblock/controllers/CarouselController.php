<?php
class Infoblock_CarouselController extends HM_Controller_Action
{
	public function init()
	{
		parent::init();
		$this->_helper->ContextSwitch()->addActionContext('check-online', 'json')->initContext();
	}
	
	static public function getInvitationText()
	{
	    return sprintf(_('Приглашаю Вас принять участие в чате'));
	}

	public function checkOnlineAction()
	{
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();
	    
	    $usersOnline = array();
	    $users = $this->_getParam('users', array());
	    if (count($users)) {
	        $usersOnline = $this->getService('User')->getUsersOnline($users);
	        if (count($usersOnline)) $usersOnline = array_combine($usersOnline, $usersOnline);
	    }

	    exit(Zend_Json::encode($usersOnline));
	}
	
	public function indexAction()
	{
        $channelsSrv = $this->getService('ChatChannels');
        $users = $this->_getParam('users', array());
        $portalOptions = unserialize($this->getService('Option')->getOption('activity'));
        $chatPortalEnabled = false;
        foreach ($portalOptions as $option) {
            if ($option['name'] == _('Чат')) {
                $chatPortalEnabled = true;
                break;
            }
        }

        if (!$chatPortalEnabled) {
            $this->_flashMessenger->addMessage(_('Cервис взаимодействия запрещен администратором'));
            $this->_redirector->gotoSimple('index', 'index', 'default');
        }

        if (count($users)) {         
            $data = array(
                'subject_name' => null, // top-level chat        
                'subject_id' => 0,         
                'name' => sprintf(_('Чат в %s'), date('H:i')),         
                'start_date' => date('d.m.Y'),         
                'end_date' => null, // в 23:59:59 надо спать, а не приглашать однокурсников в чат          
                'start_time' => 0,         
                'end_time' => 3599,         
            );
            $channel = $channelsSrv->insert($data);

            $usersInserted = array();

            $users[] = $this->getService('User')->getCurrentUserId();
            foreach($users as $userId) {
                if (isset($usersInserted[$userId])) {
                    continue;
                }
                $this->getService('ChatRefUsers')->insert(array(
                    'channel_id' => $channel->id,
                    'user_id' => $userId
                ));
                $usersInserted[$userId] = $userId;
            }
            
            // авто-пост сообщения
            $msg = $this->getService('ChatHistory')->insert(array(
                'channel_id' => $channel->id,
                'sender' => $this->getService('User')->getCurrentUserId(),
//                 'receiver' => $userId,
                'message' => self::getInvitationText(),
            ));
            
//             $sender = $this->getService('User')->getCurrentUser();
//             $data = $msg->getValues();
//             $data['message'] = self::getInvitationText();
//             $data['sender_id'] = $sender->MID;
//             $data['sender_login'] = $sender->Login;
//             $data['created'] = date('d.m.Y, H:i', strtotime($data['created']));
//             try {
//                 HM_Chat_ChatHistoryService::postToChat($data);            
//             } catch (Exception $e) {
//                 // do nothing
//             }
            $this->_flashMessenger->addMessage(_('Канал успешно создан'));
            $this->_redirector->gotoSimple('index', 'index', 'chat', array('subject' => $this->_subjectName, 'subject_id' => $this->_subjectId, 'channel_id' => $channel->id));
        }
        $this->_flashMessenger->addMessage(_('Не выбран ни один участник чата'), HM_Notification_NotificationModel::TYPE_ERROR);
        $this->_redirector->gotoSimple('index', 'index', 'default');
	}
}