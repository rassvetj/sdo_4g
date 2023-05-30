<?php

class Chat_IndexController extends HM_Controller_Action_Activity
{
    protected $_subjectName;
    protected $_subjectId;
    protected $_lessonId;
    protected $_isModerator;
    protected $_isCurrentUserInChannel;
    protected $_channelsUsersCount = array();
    
    public function preDispatch()
    {
        if($this->_request->getActionName() == 'channelusers') {
            return;
        }
        if($this->getService('User')->getCurrentUserRole() == 'guest') {
            $this->_redirector->gotoSimple('index', 'index', 'index');
        }
        parent::preDispatch();
        $this->getService('ChatChannels')->updateTotalChannel($this->getService('User')->getCurrentUserId());
        $this->_subjectName = $this->_getParam('subject', '');
        if(empty($this->_subjectName)) {
            $this->_subjectName = null;
        }
        $this->_subjectId = (int) $this->_getParam('subject_id', 0);
        $this->_lessonId = (int) $this->_getParam('lesson_id', 0);
        $channelsSrv = $this->getService('ChatChannels');
        $this->view->subjectName = $this->_subjectName;
        $this->view->subjectId = $this->_subjectId;
        $this->view->isModerator = $this->_isModerator = $channelsSrv->isCurrentUserActivityModerator();
        // $this->view->isModerator = $this->_isModerator = true;
        
        $this->view->canCreate = $this->_isCurrentUserInChannel = false;
        $channelId = $this->_getParam('channel_id', 0);
        if ($channelId) {
            $channel = $channelsSrv->getById($channelId);
            $this->view->subjectName = $this->_subjectName = $channel->subject_name;
            $this->view->subjectId = $this->_subjectId = $channel->subject_id;
            if($channel && $channel->id && !$channelsSrv->isCurrentUserInChannel($channel)) {
                $this->_flashMessenger->addMessage(array('message' => _('Вы не входите в число назначенных пользователей канала'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
                $this->_redirector->gotoSimple('index', 'index', 'chat', array('subject' => $this->_subjectName, 'subject_id' => $this->_subjectId));
            }
        }
        if(!$channel || !$channel->id) {
            $channel = $channelsSrv->getGeneralChannel($this->_subjectId ? $this->_subjectId : 0, $this->_subjectName, $this->_lessonId);
        }
        $this->view->canEdit = $this->view->isModerator && !$channel->is_general && !$channel->lesson_id;
        $this->view->canDelete = $this->view->isModerator && !$channel->is_general && !$channel->lesson_id;
        $this->_isCurrentUserInChannel = $channelsSrv->isCurrentUserInChannel($channel);
        $this->view->canCreate = $this->_isCurrentUserInChannel && !$channel->lesson_id;
        if(!$this->_subjectName && !$this->_subjectId) {
            $this->view->canCreate = false;
        }
        $this->view->isCallFromLesson = (int) $this->_getParam('lesson_id', 0);
        $config = Zend_Registry::get('config');
        $this->view->headLink()->appendStylesheet($config->url->base.'css/chat.css');
    }
    
    private function _checkModerPermissions()
    {
        if (!$this->getService('ChatChannels')->isCurrentUserActivityModerator()) {
            $this->_flashMessenger->addMessage(array('message' => _('Вы не являетесь модератором данного вида взаимодействия'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
            $channelId = $this->_getParam('channel_id', 0);
            if ($channelId) {
                $this->_redirector->gotoSimple('index', 'index', 'chat', array(
                    'subject' => $this->_subjectName, 
                    'subject_id' => $this->_subjectId
                ));
            } else {
                $this->_redirector->gotoSimple('index', 'index', 'index', array(
                    'subject' => $this->_subjectName, 
                    'subject_id' => $this->_subjectId
                ));
        }
    }
    }
    
    private function _checkUserPermissions()
    {
        if (!$this->_isCurrentUserInChannel) {
            $this->_flashMessenger->addMessage(array('message' => _('Вы не являетесь модератором данного вида взаимодействия'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
            $channelId = $this->_getParam('channel_id', 0);
            if ($channelId) {
                $this->_redirector->gotoSimple('index', 'index', 'chat', array(
                    'subject' => $this->_subjectName, 
                    'subject_id' => $this->_subjectId
                ));
            } else {
                $this->_redirector->gotoSimple('index', 'index', 'index', array(
                    'subject' => $this->_subjectName, 
                    'subject_id' => $this->_subjectId
                ));
            }
        }
    }

    public function indexAction()
    {
        $this->_checkUserPermissions();
        $channelsSrv = $this->getService('ChatChannels');
        $historySrv = $this->getService('ChatHistory');
        
        $defaultSession = new Zend_Session_Namespace('defaultChat');
        if(isset($this->getRequest()->viewType)) {
            $defaultSession->viewType = (($this->getRequest()->viewType == 'table') ? 'table' : 'default');
    }
        if(!isset($defaultSession->viewType) || 
            (isset($defaultSession->viewType) && $defaultSession->viewType == 'table' && !$this->_isModerator)) {
            $defaultSession->viewType = 'default';
        }
        $this->view->viewType = $viewType = $defaultSession->viewType;
        // if(!$this->_subjectName && !$this->_subjectId) {
            // $this->view->viewType = $viewType = 'default';
        // }

        if ($viewType == 'table') {
            $this->_channelsUsersCount = $channelsSrv->getChannelsUsersCount($this->_subjectId, $this->_subjectName);
            
            $select = $channelsSrv->getChannelsSelect($this->_subjectId, $this->_subjectName);
            $grid = $this->getGrid(
                $select,
                array(
                    'id' => array('hidden' => true),
                    'is_general' => array('hidden' => true),
                    'name' => array('title' => _('Название'), 'escape' => false),
                    'users_count' => array('title' => _('Количество участников'), 'escape' => false),
                    'access_time' => array('title' => _('Время доступности канала'), 'escape' => false),
                    'last_update' => array('title' => _('Время последнего сообщения'), 'escape' => false),
                    'start_date' => array('hidden' => true),
                    'end_date' => array('hidden' => true),
                    'start_time' => array('hidden' => true),
                    'end_time' => array('hidden' => true)
                ),
                array(
                    'name' => null,
                    'users_count' => null,
                    'access_time' => null,
                    'last_update' => array('render' => 'Date')
                ),
                'grid_chat'
            );
            
            $grid->addAction(array(
                'module' => 'chat',
                'controller' => 'index',
                'action' => 'edit'
            ),
                array('id' => 'channel_id'),
                $this->view->icon('edit')
            );

            $grid->addAction(array(
                'module' => 'chat',
                'controller' => 'index',
                'action' => 'delete'
            ),
                array('id' => 'channel_id'),
                $this->view->icon('delete')
            );

            $grid->addMassAction(
                array('module' => 'chat', 'controller' => 'index', 'action' => 'delete-by', 'subject' => $this->_subjectName, 'subject_id' => $this->_subjectId),
                _('Удалить'),
                _('Вы подтверждаете удаление отмеченных записей?')
            );

            $grid->updateColumn('name', array(
                'searchType' => 'like'
            ));
            $grid->updateColumn('users_count', array(
                'callback' => array(
                    'function'=> array($this, 'getUserCountForGrid'),
                    'params'=> array('{{id}}')
                )
            ));
            $grid->updateColumn('access_time', array(
                'callback' => array(
                    'function'=> array($this, 'getAccessTimeForGrid'),
                    'params'=> array('{{start_date}}', '{{end_date}}', '{{start_time}}', '{{end_time}}')
                )
            ));
            $grid->updateColumn('last_update', array(
                'callback' => array(
                    'function'=> array($this, 'getLastMessageDateForGrid'),
                    'params'=> array('{{id}}')
                )
            ));
            $grid->setActionsCallback(
                array('function' => array($this,'updateActions'),
                      'params'   => array('{{is_general}}')
                )
            );
            
            $filters = new Bvb_Grid_Filters();
            $filters->addFilter('name');
            $filters->addFilter('access_time', array(
                'render' => 'SubjectDate'
            ));
            $grid->addFilters($filters);

            $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
            $this->view->grid = $grid->deploy();
        } else {
            $channelId = $this->_getParam('channel_id', 0);
            if ($channelId) {
                $channel = $channelsSrv->getById($channelId);
            }
            if(!$channel->id || !$channel->isAvialable()) {
                if($channel && $channel->id && !$channel->isAvialable()) {
                    $this->_flashMessenger->addMessage(array('message' => _(
                        sprintf('Канал доступен %s', $this->getAccessTimeForGrid($channel->start_date, $channel->end_date, $channel->start_time, $channel->end_time))
                    ), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
                    $this->_redirector->gotoSimple('index', 'index', 'chat', array(
                        'subject' => $this->_subjectName, 
                        'subject_id' => $this->_subjectId
                    ));
                    }
                $channel = $channelsSrv->getGeneralChannel($this->_subjectId, $this->_subjectName, $this->_lessonId);
                }
            $channel->usersOnline = $channelsSrv->usersOnline($channel);
            $this->view->channel = $channel;
            
            $showInChannel = $this->getService('Option')->getOption('chat_messages_show_in_channel');
            if(!$showInChannel) {
                $showInChannel = Zend_Registry::get('config')->chat->messages->show_in_channel;
                $this->getService('Option')->setOption('chat_messages_show_in_channel', $showInChannel);
            }
            $messages = $historySrv->getByChannel($channel->id, $showInChannel);
            $this->view->messages = $messages;
            $this->_sideBar();
        }
    }
    
    public function updateActions($isGeneral, $actions)
    {
        if (!$isGeneral) {
            return $actions;
        } else {
            $title = _('Очистить ленту');
            $confitm = _('Вы действительно хотите очистить ленту общего канала?');
            $actions = '<menu class="grid-row-actions">
                <ul class="dropdown">
                <li>
                    <a href ="'. $this->view->url(array(
                        'module' => 'chat',
                        'controller' => 'index',
                        'action' => 'clear-general',
                        'subject' => $this->_subjectName,
                        'subject_id' => $this->_subjectId
                    ), null, true).'"><img style="margin-right:5px" src="/images/icons/delete.gif" title="'.$title.'" onClick = "if (confirm(\''.$confitm.'\')) return true; return false;"  class = "ui-els-icon " /> <span onClick = "if (confirm(\''.$confitm.'\')) return true; return false;" >'.$title.'</span>
                    </a>
                </li>
                </ul>
            </menu>';
            return $actions;
        }
    }
    
    public function getUserCountForGrid($channelId)
    {
        return isset($this->_channelsUsersCount[$channelId]) ? $this->_channelsUsersCount[$channelId] : 0;
    }
    
    public function getAccessTimeForGrid($startDate, $endDate, $startTime, $endTime)
    {
        if($startDate != null && $endDate != null) {
            return sprintf(_('с %s по %s'), 
                $this->getDateForGrid($startDate), $this->getDateForGrid($endDate)
            );
        } else if($startDate != null && $startTime != null && $endTime != null) {
            return sprintf(_('%s с %s по %s'), 
                $this->getDateForGrid($startDate),
                substr($startTime, 0, -2).':'.substr($startTime, -2),
                substr($endTime, 0, -2).':'.substr($endTime, -2)
            );
        } else {
            return _('Без ограничений');
        }
    }
    
    public function getLastMessageDateForGrid($channelId)
    {
        $message = $this->getService('ChatHistory')->fetchAll(
            array('channel_id = ?' => $channelId),
            'created DESC', 
            1
        )->current();
        if($message && $message->id) {
            return $this->getDateForGrid($message->created).' '.date('H:i', strtotime($message->created));
        }
        return '';
    }
    
    public function getDateForGrid($date)
    {
        $date = new Zend_Date($date, 'YYYY-MM-DD HH:mm:ss');
        return iconv('UTF-8', Zend_Registry::get('config')->charset, $date->toString(HM_Locale_Format::getDateFormat()));
        // return $date->toString(HM_Locale_Format::getDateFormat());
    }
    
    private function _sideBar()
    {
        $channelsSrv = $this->getService('ChatChannels');
        
        $channels = $channelsSrv->fetchAllManyToMany('Users', 'ChatRefUsers', $channelsSrv->getChannelsCondition($this->_subjectId, $this->_subjectName));
        $archive = $channelsSrv->getArchive($this->_subjectId, $this->_subjectName);
        foreach($channels as $k=>$channel){
            $uids = $channelsSrv->getChannelUserIds($channel);
            if(!in_array($this->getService('User')->getCurrentUserId(), $uids)) {
                unset($channels[$k]);
            }
            $users = $channelsSrv->usersOnline($channel);
            $channel->usersOnline = $users;
        }

        $this->view->archive = $archive;
        $this->view->channels = $channels;
        $this->view->curUserId = $this->getService('User')->getCurrentUserId();
    }
    
    public function viewAction()
    {
        $this->_checkUserPermissions();
        $channelsSrv = $this->getService('ChatChannels');
        $historySrv = $this->getService('ChatHistory');
       
        $channelId = $this->_getParam('channel_id', 0);
        $channel = $channelsSrv->getById($channelId);
        if(!$channel->id || !$channelsSrv->isCurrentUserInChannel($channel)) {
            $this->_flashMessenger->addMessage(array('message' => _('Вы не входите в число назначенных пользователей канала'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
            $this->_redirector->gotoSimple('index', 'index', 'chat', array('subject' => $this->_subjectName, 'subject_id' => $this->_subjectId));
        }
        $channel->usersOnline = $channelsSrv->usersOnline($channel);
        $this->view->channel = $channel;
        $messages = $historySrv->getByChannel($channel->id);
        $this->view->messages = $messages;
        $this->_sideBar();
    }

    public function sendAction()
    {
        // check is AJAX request or not
        $request = $this->getRequest();
        if (!$this->getRequest()->isXmlHttpRequest() || !$this->getRequest()->isPost()) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }
        
        $channelId = $request->getParam('channel_id', 0);
        $channelsSrv = $this->getService('ChatChannels');
        $historySrv = $this->getService('ChatHistory');
        $channel = $channelsSrv->getById($channelId);
        if(!$channel->id || !$channelsSrv->isCurrentUserInChannel($channel)) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }
        $msg = $historySrv->insert(array(
            'channel_id' => $channelId,
            'sender' => $this->getService('User')->getCurrentUserId(),
            'receiver' => $request->getParam('receiver'),
            'message' => iconv("UTF-8", Zend_Registry::get('config')->charset, $request->getParam('message'))
        ));
        $sender = $this->getService('User')->getCurrentUser();
        $data = $msg->getValues();
        $data['message'] = $request->getParam('message');
        $data['sender_id'] = $sender->MID;
        $data['sender_login'] = $sender->Login;
        $data['created'] = date('d.m.Y, H:i', strtotime($data['created']));

        header('Content-type: application/json; charset=UTF-8');
        print HM_Chat_ChatHistoryService::postToChat($data);
        exit;
    }
    
    public function usersListAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();
        $this->getResponse()->setHeader('Content-type', 'text/html; charset='.Zend_Registry::get('config')->charset);

        $q = urldecode($this->_getParam('q', ''));
        $q = iconv('UTF-8', Zend_Registry::get('config')->charset, $q);
        $users =  $this->getService('ChatChannels')->getActivityUsers();
        $res = array();
        foreach($users as $user) {
            if (strlen($q) > 0) {
                if(strstr($user->LastName, $q) !== false ||
                strstr($user->FirstName, $q) !== false ||
                strstr($user->Patronymic, $q) !== false ||
                strstr($user->Login, $q) !== false) {
                    $res[$user->MID] = $user->getName();
                }
            } else {
                $res[$user->MID] = $user->getName();
            }
        }
        $response = '';
        $channelId = $this->_getParam('channel_id', 0);
        $channel = $this->getService('ChatChannels')->getById($channelId);
        $userIds = array($this->getService('User')->getCurrentUserId());
        if($channel && $channel->id) {
            $userIds = $this->getService('ChatChannels')->getChannelUserIds($channel);
        }
        foreach($res as $id => $name) {
            if(in_array($id, $userIds)) {
                $response .= $id.'+='.$name."\n";
            } else {
                $response .= $id.'='.$name."\n";
            }
        }
        echo rtrim($response);
    }
    
    public function channelusersAction()
    {
        $channelId = $this->_getParam('channel_id', 0);
        $channel = $this->getService('ChatChannels')->getById($channelId);
        if (!$channel || !$channel->id) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }
        $userTokens = array();
        if($channel->users && count($channel->users) > 0) {
        foreach($channel->users as $user) {
                $userTokens []= $user->generateKey();
    }
        }
        header('Content-type: application/json; charset=UTF-8');
        print Zend_Json::encode($userTokens);
        exit;
    }
    
    
    public function channelStatAction()
    {
        // check is AJAX request or not
        $request = $this->getRequest();
        if (!$this->getRequest()->isXmlHttpRequest()) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();
        $this->getResponse()->setHeader('Content-type', 'text/html; charset=UTF-8');
       
        $channelsSrv = $this->getService('ChatChannels');

        $channelId = $request->getParam('channel_id', 0);
        $channel = $channelsSrv->getById($channelId);
        if(!$channel && !$channel->id) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }
        $res = array();
        $res['users'] = array();
        $res['channels'] = array();
        $res['archive'] = array();
        
        $users = $channelsSrv->usersOnline($channel);
        foreach($users as $user) {
            $u = array();
            $u['id'] = $user->MID;
            $u['photo'] = Zend_Registry::get('config')->url->base . $user->getPhoto();
            $u['login'] = iconv(Zend_Registry::get('config')->charset, "UTF-8", $user->Login);
            $u['name'] = iconv(Zend_Registry::get('config')->charset, "UTF-8", $user->getName());
            $res['users'] []= $u;
        }
        
        $channels = $channelsSrv->fetchAllManyToMany('Users', 'ChatRefUsers', $channelsSrv->getChannelsCondition($this->_subjectId ? $this->_subjectId : 0, $this->_subjectName));
        $archive = $channelsSrv->getArchive($this->_subjectId ? $this->_subjectId : 0, $this->_subjectName);

        foreach($channels as $k=>$channel) {
            $uids = $channelsSrv->getChannelUserIds($channel);
            if(in_array($this->getService('User')->getCurrentUserId(), $uids)) {
            $ch = $channel->getValues();
            $ch['name'] = iconv(Zend_Registry::get('config')->charset, "UTF-8", $channel->name);
            $ch['usersCount'] = count($channelsSrv->usersOnline($channel));
            $res['channels'] []= $ch;
        }
        }
        

        foreach($archive as $channel) {
            $ch = $channel->getValues();
            $ch['name'] = iconv(Zend_Registry::get('config')->charset, "UTF-8", $channel->name);
            $ch['start_date'] = date('d.m.Y', strtotime($channel->start_date));
            $ch['end_date'] = date('d.m.Y', strtotime($channel->end_date));
            if($channel->start_time && $channel->end_time) {
                $ch['start_time'] = $channel->getStartTime();
                $ch['end_time'] = $channel->getEndTime();
            }
            $res['archive'] []= $ch;
        }
        
        header('Content-type: application/json; charset=UTF-8');
        print Zend_Json::encode($res);
        exit;
    }

    public function newAction()
    {
        $this->_checkUserPermissions();
        $channelsSrv = $this->getService('ChatChannels');
        $form = new HM_Form_Channel();
        $form->setAction($this->view->url(array(
            'module' => 'chat', 
            'controller' => 'index', 
            'action' => 'new'
        )));
        $this->view->form = $form;

        $request = $this->getRequest();
        if ($request->isPost() && $form->isValid($request->getParams())) {
            $data = $this->_getChannelFormData($form);
            $channel = $channelsSrv->insert($data);
            foreach($form->getValue('users') as $userId) {
                $this->getService('ChatRefUsers')->insert(array(
                    'channel_id' => $channel->id,
                    'user_id' => $userId
                ));
            }
            
            $this->_flashMessenger->addMessage(_('Канал успешно создан'));
            $this->_redirector->gotoSimple('index', 'index', 'chat', array('subject' => $this->_subjectName, 'subject_id' => $this->_subjectId));
        }
    }
    
    public function editAction()
        {
        $this->_checkUserPermissions();
        $id = (int) $this->_getParam('channel_id', 0);
        
        $channelsSrv = $this->getService('ChatChannels');
        $channel = $this->getService('ChatChannels')->getById($id);
        if($channel->is_general) {
            $this->_flashMessenger->addMessage(array('message' => _('Вы не можете редактировать общий канал'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
            $this->_redirector->gotoSimple('index', 'index', 'chat', array('subject' => $this->_subjectName, 'subject_id' => $this->_subjectId));
        }
        if(!$channel || !$channel->id) {
            $this->_flashMessenger->addMessage(array('message' => _('Канал не найден'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
            $this->_redirector->gotoSimple('index', 'index', 'chat', array('subject' => $this->_subjectName, 'subject_id' => $this->_subjectId));
        }
        
        $form = new HM_Form_Channel();
        $form->setAction($this->view->url(array(
            'module' => 'chat', 
            'controller' => 'index', 
            'action' => 'edit',
            'channel_id' => $id
        )));
        $this->view->form = $form;

        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $data = $this->_getChannelFormData($form);
                $data['id'] = $id;
                $channelsSrv->update($data);
                $this->getService('ChatRefUsers')->deleteBy(array('channel_id = ?' => $id));
                foreach($form->getValue('users') as $userId) {
                    $this->getService('ChatRefUsers')->insert(array(
                        'channel_id' => $id,
                        'user_id' => $userId
                    ));
                }

                $this->_flashMessenger->addMessage(_('Канал успешно изменен'));
                $this->_redirector->gotoSimple('index', 'index', 'chat', array('subject' => $this->_subjectName, 'subject_id' => $this->_subjectId));
            }
        } elseif ($channel) {
            $values = $channel->getValues();
            $values['users'] = array();
            if($channel->users && count($channel->users) > 0) {
                foreach($channel->users as $user) {
                    $values['users'] []= $user->MID;
                }
            }
            $values['start_date'] = ( $channel->start_date )? date('d.m.Y', strtotime($values['start_date'])) : date("d.m.Y");
            $values['end_date'] = ( $channel->end_date )? date('d.m.Y', strtotime($values['end_date'])) : date("d.m.Y");
            $values['date_access'] = HM_Chat_ChatChannelsModel::TIMETYPE_FREE;
            $values['time'] = array('09:00', '18:00');
            if($channel->start_date && $channel->end_date) {
                $values['date_access'] = HM_Chat_ChatChannelsModel::TIMETYPE_DATES;
            }
            if($channel->start_time && $channel->end_time) {
                $values['current_date'] = $values['start_date'];
                $values['date_access'] = HM_Chat_ChatChannelsModel::TIMETYPE_TIMES;
                $values['time'] = array($channel->getStartTime(), $channel->getEndTime());
            }
            // print_r($values);exit;
            $form->setDefaults($values);
        }
    }
    
    private function _getChannelFormData($form)
        {
        $data = array(
            'subject_name' => $this->_subjectName,
            'subject_id' => $this->_subjectId,
            'name' => $form->getValue('name'),
            'show_history' => $form->getValue('show_history'),
            'start_date' => null,
            'end_date' => null,
            'start_time' => null,
            'end_time' => null
        );
        $dateAccess = (int)$form->getValue('date_access');
        switch($dateAccess) {
            case HM_Chat_ChatChannelsModel::TIMETYPE_DATES:
                $data['start_date'] = $form->getValue('start_date');
                $data['end_date'] = $form->getValue('end_date');
            break;
            case HM_Chat_ChatChannelsModel::TIMETYPE_TIMES:
                $times = $form->getValue('time');
                foreach($times as &$time) {
                    $time = (int)str_replace(':', '', $time);
                }
                $data['start_date'] = $form->getValue('current_date');
                $data['start_time'] = $times[0];
                $data['end_time'] = $times[1];
            break;
        }
        return $data;
    }

    public function deleteAction()
    {
        $this->_checkUserPermissions();
        $id = $this->_getParam('channel_id', 0);
        if ($id) {
            $channel = $this->getService('ChatChannels')->find($id)->current();
            if($channel && $channel->id && !$channel->is_general) {
                $this->getService('ChatChannels')->delete($id);
        }
        }

        $this->_flashMessenger->addMessage(_('Канал успешно удален'));
        $this->_redirector->gotoSimple('index', 'index', 'chat', array('subject' => $this->_subjectName, 'subject_id' => $this->_subjectId));
    }


    public function deleteByAction()
        {
        $this->_checkModerPermissions();
        $ids = explode(',', $this->_request->getParam('postMassIds_grid_chat'));
        foreach ($ids as $value) {
            $channel = $this->getService('ChatChannels')->find($value)->current();
            if($channel && $channel->id && !$channel->is_general) {
                $this->getService('ChatChannels')->delete($value);
        }
    }
        $this->_flashMessenger->addMessage(_('Каналы успешно удалены'));
        $this->_redirector->gotoSimple('index', 'index', 'chat', array('subject' => $this->_subjectName, 'subject_id' => $this->_subjectId));
    }
    
    public function clearGeneralAction()
    {
        $this->_checkModerPermissions();
        $channel = $this->getService('ChatChannels')->getGeneralChannel($this->_subjectId, $this->_subjectName);
        $this->getService('ChatHistory')->deleteBy(array('channel_id = ?' => $channel->id));
        $this->_flashMessenger->addMessage(array('message' => _('Лента сообщений общего канала чата успешно очищена')));
        $this->_redirector->gotoSimple('index', 'index', 'chat', array(
            'subject' => $this->_subjectName, 
            'subject_id' => $this->_subjectId
        ));
    }
}
