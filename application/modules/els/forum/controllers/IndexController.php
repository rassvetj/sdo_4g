<?php

/**
 *
 * @todo Подумать о фильтрации XSS атак в текстах тем и сообщений !!!
 * 
 */
class Forum_IndexController extends HM_Controller_Action_Activity implements
    HM_Forum_Library_Constants,
    Es_Entity_EventViewer
{

    // Сообщения на странице
    protected $pageMsg = array(
        'newSection'     => 'Создать тему',
        'errNewSectnion' => 'Не удалось создать тему, повторите попытку позже',
        'pinSection'     => 'Закрепить тему',
        'unpinSection'   => 'Открепить тему',
        'loadMsg'        => 'Загрузить текст сообщения',
        'errMsgsLoading' => 'Ошибка при загрузке сообщений',
        'expComments'    => 'Развернуть список сообщений',
        'hideComments'   => 'Скрыть сообщения',
        'hiddenComment'  => 'Скрытое сообщение',
        'newTheme'       => 'Новая тема',
        'newComment'     => 'Новое сообщение',
        'openTheme'      => 'Открыть тему',
        'loadComment'    => 'Загрузить текст сообщения',
        'jumpToParent'   => 'К родительскому сообщению',
        'score'          => 'Оценка',
        'reply'          => 'Ответить',
        'deleteComment'  => 'Удалить сообщение',
        'deletedComment' => 'Сообщение удалено',
        'messagesAll'    => 'все',
        'messagesNew'    => 'новые'
    );

    /**
     * @var HM_Forum_ForumService
     */
    protected $forumService;

    /**
     * @var HM_Forum_Forum_ForumModel
     */
    protected $forum;

    public function init(){
        $this->_routing();
        parent::init();
        $this->_forumInit();
        $this->_viewInit();
    }

    public function preDispatch(){
        parent::preDispatch();

        // Обработка входа на форум занятия как на независимый форум
        $routeName = $this->getFrontController()->getRouter()->getCurrentRouteName();
        if($routeName === self::ROUTE_FORUM && $this->forum->subject_id > 0){
            $route = array(
                'module'               => 'forum',
                'controller'           => 'index',
                'action'               => 'index',
                self::PARAM_SUBJECT    => 'subject',
                self::PARAM_SUBJECT_ID => $this->forum->subject_id,
                self::PARAM_SECTION_ID => $this->forum->section->section_id ? $this->forum->section->section_id : 0
            );
            $this->_redirector->gotoRoute($route, self::ROUTE_SUBJECT, true);
        }
    }

    // Переопределение действия в зависимости от параметров запроса
    protected function _routing(){
        switch(true){
            // Приоритет вывода разделов/тем
        case $this->_hasParam('order'):
            $this->getRequest()->setActionName('order');
            break;

            // Список сообщений темы
        case $this->_hasParam('msglist') && $this->_getParam('msglist') == 1:
            $this->getRequest()->setActionName('msglist');
            break;

            // Удаление сообщения
        case $this->_hasParam('delete'):
            $this->getRequest()->setActionName('msgdelete');
            break;

            // Удалить раздел
        case $this->_hasParam('sdelete'):
            $this->getRequest()->setActionName('sectdelete');
            break;

            // Оценка сообщения
        case $this->_hasParam('rating') && $this->_hasParam(self::PARAM_MESSAGE_ID):
            $this->getRequest()->setActionName('msgrating');
            break;

            // Отдельное сообщение
        case $this->_hasParam(self::PARAM_MESSAGE_ID):
            $this->getRequest()->setActionName('message');
            break;

            // Закрытие темы
        case $this->_hasParam('close'):
            $this->getRequest()->setActionName('themeclose');
            break;		
        }		
    }

    /**
     * Инициализация форума
     * 
     * По завершению работы функции, в сущности $this->forum находится объект форума
     * со структурой разделов и сообщений если таковые имеются
     */
    protected function _forumInit(){
        $routeName = $this->getFrontController()->getRouter()->getCurrentRouteName();
        $sectionId = (int) $this->_getParam(self::PARAM_SECTION_ID, 0);
        $this->forumService = $this->getService('Forum');

        switch($routeName){
            // Независимый форум, на пример форум портала
        case self::ROUTE_FORUM:
            if(!$this->_hasParam(self::PARAM_FORUM_ID)) $this->criticalError(_(self::ERR_MSG_FORUM_NM));
            $forumId = (int) $this->_getParam(self::PARAM_FORUM_ID);

            $this->_forumStructInit();
            try{ $this->forum = $this->forumService->getForum($forumId, $sectionId); }
            catch(HM_Exception $e){ $this->criticalError($e->getMessage(), $e->getCode(), array(), $routeName); }

                break;

            // Форум занятия
        case self::ROUTE_SUBJECT:
        case self::ROUTE_DEFAULT:
            // Курс
            $subject = $this->getActivitySubject();

            //для дефолтного роута, если subject_id не передается, то и форум инитить не надо
            if (!$subject && $routeName == self::ROUTE_DEFAULT) return;

            if(!$subject) $this->criticalError(_(self::ERR_MSG_NOSUBJECT));

            // Занятие
            $lesson = $this->getActivityLesson();

            $this->_forumStructInit();
            try{ $this->forum = $this->forumService->getForumBySubject($subject, $sectionId, $lesson); }
            catch(HM_Exception $e){ $this->criticalError($e->getMessage(), $e->getCode()); }

                break;
        }
    }

    // Параметры отображения форума
    protected function _forumStructInit(){
        // Отображение сообщений
        if($this->_hasParam('mode')){
            // Возможные параметры => их соответствие параметрам конфигурации
            // Устанавливающие в true
            $paramsMapTrue = array(
                'new'     => 'only_new',
                'tree'    => 'as_tree',
                'time'    => 'order_by_time',
                'reverse' => 'order_reverse'
            );
            // Устанавливающие в false
            $paramsMapFalse = array(
                'all'      => 'only_new',
                'list'     => 'as_tree',
                'natural'  => 'order_by_time',
                'straight' => 'order_reverse'
            );  

            $params = explode(':', $this->_getParam('mode'));
            $params = array_map('trim', $params);
            $params = array_flip($params);

            $paramsTrue  = array_intersect_key($paramsMapTrue, $params);
            $paramsFalse = array_intersect_key($paramsMapFalse, $params);

            $paramsTrue  = array_fill_keys($paramsTrue, true);
            $paramsFalse = array_fill_keys($paramsFalse, false);

            $params = array('messages' => array('structure' => $paramsTrue + $paramsFalse));
            $this->forumService->setConfig($params);
        }

        // Предпросмотр списка сообщений
        if($this->_hasParam('msglist')){
            $this->forumService->getConfig()->messages->structure->preview = true;
        }

        // Отображение разделов/тем
        if($this->_hasParam('smode')){
            switch($this->_getParam('smode')){
                // По последнему сообщению
            case 'lastmsg':
                $this->forumService->getConfig()->sections->structure->order_last_msg = true;
                break;

                // В порядке добавления
            case 'natural':
                $this->forumService->getConfig()->sections->structure->order_last_msg = false;
                break;                    
            }
        }
    }

    // Настройка View и передача начальных параметров
    protected function _viewInit(){
        $this->_helper
            ->ContextSwitch()
            ->setAutoJsonSerialization(true)
            ->addActionContext('subscribe', 'json')
            ->addActionContext('unsubscribe', 'json')
            ->initContext('json');

        // Сообщения на странице
        $this->view->placeholder('page_messages')->exchangeArray(array_map('_', $this->pageMsg));

        // Информация о текущем пользователе
        $currentUser = $this->getService('User')->getCurrentUser();		
		if(!$currentUser->MID){
			$this->_redirect('/');
		}
		
        $this->view->placeholder('current_user')->exchangeArray($currentUser->getData());

        // Если запрошен раздел являющийся темой
        if(isset($this->forum->section) && $this->forum->section->flags->theme){
            $this->_helper->viewRenderer->setRender('theme');

            // Параметры для переключателя режимов (HM_View_Helper_HeadSwitcher)
            $params = array_flip(array('module', 'controller', 'action', 'switcher'));
            foreach($params as $param => &$value) $value = $this->_getParam($param);
            $this->view->switcherParams = $params;

            // Оценки для сообщений
            $ratings = $this->forumService->getConfig()->ratings->toArray();
            $this->view->ratings = array_map('_', $ratings);

            // кешируем пользователей по id из deleted_by и edited_by
            foreach($this->forum->section->messages as $message) {
                if ($message->deleted_by) {
                    $users[] = $message->deleted_by;
                } elseif($message->edited_by) {
                    $users[] = $message->edited_by;
                }
            }

            // является ли текущий пользователь подписчиком темы
            if ($this->forum->section->lesson_id) {
                $subscribeUsers = $this->getService('SubscriptionChannel')->getOne($this->getService('SubscriptionChannel')->fetchAllDependence('Subscription',array('lesson_id=?'=>$this->forum->section->lesson_id)));
                if ($subscribeUsers && count($subscribeUsers->subscriptions)){
                    $subscribeUsers = $subscribeUsers->subscriptions->getList('user_id');
                    $this->view->isSubscriber = in_array($this->getService('User')->getCurrentUserId(),$subscribeUsers);
                } else {
                    $this->view->isSubscriber = false;
                }
            }
            if (count($users)){
                $peoples = $this->getService('User')->fetchAll(array('MID IN (?)' => $users));
                if (count($peoples)){
                    foreach($peoples as $usr) {
                        $moderatorsList[$usr->MID] = $usr->getName();
                    }
                    $this->view->moderatorsList = $moderatorsList;
                }
            }
        }

        $this->view->forum = $this->forum;
    }

    public function indexAction(){
        try {
            $this->getService('EventServerDispatcher')->trigger(
                Es_Service_Dispatcher::EVENT_UNSUBSCRIBE,
                $this,
                array('filter' => $this->getFilterByRequest($this->getRequest()))
            );  
            $this->view->currentUserId = $this->getService('User')->getCurrentUserId();
            $this->formSection();
            $this->formMessage();
            $this->formTheme();
        }
        catch(HM_Exception $e){
            $this->_flashMessenger->addMessage(array(
                'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => $e->getMessage()
            ));                     
        }
    }

	
	// /forum/index/update-theme/section_id/33
	//--Не доделал. Изменяет тему. Сделать через ajax?
	public function updateThemeAction(){		
		/*
		//$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();		
		
		$request = $this->getRequest();
		$form = new HM_Form_Theme();
		
		$request = $this->getRequest();				
		$sectionId = $request->getParam('section_id', false);
		
		$section = $this->getService('ForumSection')->getOne($this->getService('ForumSection')->find($sectionId));

        if (!$section) {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Тема не найдена')));
            $this->_redirector->gotoUrl($_SERVER['HTTP_REFERER']);
        }		
		if($request->isPost()){
			if($form->isValid($request->getPost()) && !$this->isAjaxRequest()){						
				$data = array(
                    'section_id' => $sectionId,
                    'title'      => $form->getValue('title'),
                    'text'      => $form->getValue('text'),
                    'edited'   => date('Y-m-d H:i:s',time()),
					'last_msg'   => $section->last_msg,
				);	
				$result = $this->getService('ForumSection')->update($data);				
				if ($result) {
                    $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_SUCCESS, 'message' => _('Тема обновлена')));
                } else {
                    $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Произошла ошибка при обновлении темы')));
                }
                //$this->_redirector->gotoUrl($form->getValue('cancelUrl'));
                $this->_redirector->gotoUrl($_SERVER['HTTP_REFERER']);
			}		
		} else {
			$form->addElement('submit', 'submit', array(
				'label'       => _('Изменить'),		
				'class'       => 'ui-widget ui-button topic-create',				
			));			
			if(!$sectionId){				
				$this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Переданы неверные параметры')));
				//$this->_redirector->gotoUrl($form->getValue('cancelUrl'));
				$this->_redirector->gotoUrl($_SERVER['HTTP_REFERER']);
			}		
			$sect = $this->getService('ForumSection')->getSection($sectionId);
			if(!$sect){				
				$this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Переданы неверные параметры')));
				//$this->_redirector->gotoUrl($form->getValue('cancelUrl'));
				$this->_redirector->gotoUrl($_SERVER['HTTP_REFERER']);
			}		
			$form->getElement('title')->setValue($sect->title);
			$form->getElement('text')->setValue($sect->text);			
        }		
        echo $form;	
		*/		
	}
	
    public function getFilterByRequest(Zend_Controller_Request_Http $request) {
        $filter = $this->getService('ESFactory')->newFilter();
        $filter->setUserId((int)$this->getService('User')->getCurrentUserId());

        $section = $request->getParam('section_id');
        $group = $this->getService('ESFactory')->eventGroup(
            HM_Forum_ForumService::EVENT_GROUP_NAME_PREFIX, (int)$section
        );
        if ($group->getId() !== null) {
            $filter->setGroupId($group->getId());
        }
        $filter->setEventId($request->getParam('eventId', null));
        return $filter;
    } 

    protected function formSection(){
        if(!$this->_hasParam('newsection')) return;

        // Пользователь должен быть модератором
        if(!$this->forum->moderator) return;

        // Форум должен допускать существование разделов
        if(!$this->forum->flags->subsections) return;

        $form = new HM_Form_Section();
        $this->view->formSection = $form;
        $request = $this->getRequest();

        if(!$request->isPost() || (!$form->isValid($request->getPost()) && !$this->isAjaxRequest())){
            if ($this->forum->flags->subsections || $this->_hasParam('newsection')) {
                $this->_helper->viewRenderer->setViewScriptPathSpec(':controller/:action-form.:suffix');
            }
            return $form;
        }

        if($this->isAjaxRequest()){
            $this->_helper->layout->setLayout('ajax');
            $this->_helper->viewRenderer->setNoRender();
        }

        // Обработка отправленного запроса из формы
        if($form->isValid($request->getPost())){
            $section = array(
                'title'     => $form->getValue('title'),
                'text'      => $form->getValue('text')
            );

            $parent = isset($this->forum->section) ? $this->forum->section : null;
            $section = $this->forumService->createSection($section, $this->forum, $parent);

            if($this->isAjaxRequest()){
                $section->count_msg = 0;
                echo $this->view->partial('themes.tpl', array(
                    'moderator' => $this->forum->moderator,
                    'sections'  => array($section),
                    'forum'     => $this->forum,
                ));
            }
            else {
                $this->_flashMessenger->addMessage(array(
                    'message' => sprintf(_('Категория «%s» успешно создана'), $form->getValue('title')),
                    'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
                ));
                $this->_redirector->gotoRoute(array(
                    'forum_id' => null,
                    'section_id' => null,
                    'newsection' => null,
                ));
            }
        }
        elseif ($this->isAjaxRequest()) {
            $this->getResponse()->setHttpResponseCode(400);
            echo $this->view->notifications(array_merge($this->_flashMessenger->getCurrentMessages(), array(array(
                'message' => _('Название категории не может быть пустым'),
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
            ))), array('html' => true));
            $this->_flashMessenger->clearCurrentMessages();
        }

        return $form;
    }

    protected function formTheme(){
        $form = new HM_Form_Theme();
        $this->view->formTheme = $form;

        // Для форумов имеющих разделы создание темы возможно только в разделе
        if($this->forum->flags->subsections && !isset($this->forum->section)) return;

        // Нельзя создать тему в теме
        if($this->forum->section->flags->theme) return;

        $request = $this->getRequest();
        if(!$request->isPost() || (!$form->isValid($request->getPost()) && !$this->isAjaxRequest())) {
            if ($this->forum->flags->subsections || $this->_hasParam('newtheme')) {
                $this->_helper->viewRenderer->setViewScriptPathSpec(':controller/:action-form.:suffix');
            }
            return $form;
        }

        if($this->isAjaxRequest()){
            $this->_helper->layout->setLayout('ajax');
            $this->_helper->viewRenderer->setNoRender();
        }

        // Обработка отправленного запроса из формы
        if($form->isValid($request->getPost())){
            $section = array(
                'title'     => $form->getValue('title'),
                'text'      => $form->getValue('text'),
                'flags'     => array('theme' => true)
            );

            $section = $this->forumService->createSection($section, $this->forum, $this->forum->section);

            if($this->isAjaxRequest()){
                $section->count_msg = 0;
                echo $this->view->partial('themes.tpl', array(
                    'moderator' => $this->forum->moderator,
                    'sections'  => array($section),
                    'forum'     => $this->forum,
                ));
            }
            else {
                $this->_flashMessenger->addMessage(array(
                    'message' => sprintf(_('Тема «%s» успешно создана'), "<a href=\"{$section->url()}\">".$form->getValue('title')."</a>"),
                    'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
                    'hasMarkup' => true,
                ));
                $this->_redirector->gotoRoute(array(
                    'forum_id' => null,
                    'section_id' => null,
                    'newtheme' => null,
                ));
            }
        }
        elseif ($this->isAjaxRequest()) {
            $this->getResponse()->setHttpResponseCode(400);
            echo $this->view->notifications(array_merge($this->_flashMessenger->getCurrentMessages(), array(array(
                'message' => _('Название темы не может быть пустым'),
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
            ))), array('html' => true));
            $this->_flashMessenger->clearCurrentMessages();
        }

        return $form;
    }

    protected function formMessage(){
        // Создание сообщений возможно только в темах
        if(!isset($this->forum->section) || !$this->forum->section->flags->theme) return;

        // Нельзя создавать сообщения в закрытых темах
        if($this->forum->section->flags->closed) return;

        $form = new HM_Form_Message;
        $this->view->formMessage = $form;
        $this->view->formAnswer = new HM_Form_Answer;


        $request = $this->getRequest();
        if(!$request->isPost()) return $form;

        if($this->isAjaxRequest()){
            $this->_helper->layout->setLayout('ajax');
            $this->_helper->viewRenderer->setNoRender();
        }

        // Обработка отправленного запроса из формы
        if($form->isValid($request->getPost())){
            $message = array(
                'title'     => $form->getValue('title'),
                'text'      => $form->getValue('text'),
                'is_hidden' => $form->getValue('is_hidden',0),
                'answer_to' => $this->_getParam('answer_to', 0),
            );

            $message = $this->forumService->addMessage($message, $this->forum, $this->forum->section);

            // отправка уведомлений
            if ( $message ) {
                $parentMsg = $this->getService('ForumMessage')->getOne($this->getService('ForumMessage')->fetchAll(array('message_id=?' => $message->answer_to)));
                $section   = $this->getService('ForumSection')->getOne($this->getService('ForumSection')->fetchAll(array('section_id=?' => $message->section_id)));
                $forum     = $this->getService('ForumForum')->getOne($this->getService('ForumForum')->fetchAll(array('forum_id=?' => $message->forum_id)));

                if ($forum && $section) {

                    $messageParam = array(
                        'MESSAGE_USER_NAME' => $message->user_name,
                        'SECTION_NAME'      => ($section && $section->title)? $section->title : '',
                        'FORUM_NAME'        => ($forum && $forum->title)? $forum->title : '',
                        'MESSAGE_URL'       => $this->view->serverUrl($section->url($forum->subject_id && $section->lesson_id ? array(HM_Controller_Action_Activity::PARAM_SUBJECT_ID => $forum->subject_id) : array()))
                    );

                    $messenger = $this->getService('Messenger');

                    // получение списка пользователей подписанных на уведомления
                    // по данному заданию подписка может осуществляться только на занятия, с глобального форума и СВ уведомления не слать
					$subscribeUsers  = array();
					if ($section->lesson_id) {
                        $subscribeUsers = $this->getService('SubscriptionChannel')->getOne($this->getService('SubscriptionChannel')->fetchAllDependence('Subscription',array('lesson_id=?'=>$section->lesson_id)));
                        if ($subscribeUsers && count($subscribeUsers->subscriptions)){
                            $subscribeUsers = $subscribeUsers->subscriptions->getList('user_id');
                        } else {
                            $subscribeUsers  = array();
                        }
                    }


                    if ($message->is_hidden) {
                        // уведомление о скрытом сообщение
                        $messenger->setOptions( HM_Messenger::TEMPLATE_FORUM_NEW_HIDDEN_ANSWER, $messageParam);
                        if ($message->answer_to && $parentMsg ) {
                            // шлется только автору родительского сообщения
                            if (in_array($parentMsg->user_id, $subscribeUsers)) {
                                $messenger->send(HM_Messenger::SYSTEM_USER_ID, $parentMsg->user_id);
                            }
                        } else {
                            // если нет родительского - сообщение шлется автору темы
                            if (in_array($section->user_id, $subscribeUsers)) {
                                $messenger->send(HM_Messenger::SYSTEM_USER_ID, $section->user_id);
                            }
                        }
                    } else {
                        // уведомления об обычном сообщении
                        $messenger->setOptions( HM_Messenger::TEMPLATE_FORUM_NEW_ANSWER, $messageParam);

                        if ($section->lesson_id) {
                            //шлются всем студентам на занятии
                            $data = $this->getService('LessonAssign')->fetchAll(array('SHEID=?' => $section->lesson_id));
                        } elseif ($forum->subject_id) {
                            // или всем слушателям на курсе
                            $data = $this->getService('Student')->fetchAll(array('CID=?' => $forum->subject_id));
                        }

                        $students = (count($data))? array_unique($data->getList('MID')) : array();
						if(!empty($students)){
							foreach($students as $studentID) {
								if (!$studentID || !in_array($studentID, $subscribeUsers)) continue;
								$messenger->send(HM_Messenger::SYSTEM_USER_ID, $studentID);
							}
						}
                        // а так же автору темы
                        if (in_array($section->user_id, $subscribeUsers) && !in_array($section->user_id, $students)) {
                            $messenger->send(HM_Messenger::SYSTEM_USER_ID, $section->user_id);
                        }
                    }
                }
            }

            if($this->isAjaxRequest()){
                echo $this->view->partial('messages-theme.tpl', array(
                    'section'  => $this->forum->section,
                    'messages' => array($message),
                    'forum'    => $this->forum
                ));
            }
            else $this->_redirector->gotoRoute();
        }
        elseif($this->isAjaxRequest()) {
            $this->getResponse()->setHttpResponseCode(400);
            echo $this->view->notifications(array_merge($this->_flashMessenger->getCurrentMessages(), array(array(
                'message' => _('Текст сообщения не может быть пустым'),
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
            ))), array('html' => true));
            $this->_flashMessenger->clearCurrentMessages();
        };

        return $form;
    }

    /**
     * Отдаёт список сообщений определённой темы
     * Используется для запрошенного через AJAX предпросмотра сообщений 
     */
    public function msglistAction(){
        if($this->isAjaxRequest()) $this->_helper->layout->setLayout('ajax');
        $this->_helper->viewRenderer->setNoRender();

        if(isset($this->forum->section->flags->theme)){
            echo $this->view->partial('messages.tpl', array(
                'section'  => $this->forum->section,
                'messages' => $this->forum->section->messages,
                'forum'    => $this->forum,
                'currentUserId' => $this->getService('User')->getCurrentUserId(),
                'root'     => true
            ));
        }
    }

    /**
     * Удаление сообщения
     * Фактически сообщение остаётся в базе, с установленным флагом "deleted"
     */
    public function msgdeleteAction(){
        // $this->_helper->viewRenderer->setNoRender();

        // Удаление сообщения возможно только при нахождении в разделе и только через AJAX запрос
        if(!$this->isAjaxRequest() || !isset($this->forum->section)) return;

        $this->_helper->layout->setLayout('ajax');

        $messageId = (int) $this->_getParam('delete');
        $message = $this->forumService->getMessage($messageId);

        // Проверка на соответствие ID раздела сообщения текущему ID раздела форума
        if($this->forum->section->section_id != $message->section_id) return;

        // Удалить сообщение могут только модератор и сам автор сообщения
        $userId = $this->getService('User')->getCurrentUserId();
        if(!$this->forum->moderator && ($userId != $message->user_id || !empty($message->rating))) return;
        $message->delete();
        if (!isset($this->view->moderatorsList[$message->deleted_by])) {
            $this->view->moderatorsList[$message->deleted_by] = $this->getService('User')->getCurrentUser()->getName();
    }

        echo $this->view->partial('messages-theme.tpl', array(
            'section'    => $this->forum->section,
            'messages'   => array($message),
            'forum'      => $this->forum,
            'isDeleting' => true,
            'currentUserId' => $userId,
            'moderatorsList' => $this->view->moderatorsList,
        ));
    }
    /**
     * Удаление раздела/темы
     */
    public function sectdeleteAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        // Удаление раздела возможно только при нахождении в разделе и только через AJAX запрос
        if(!$this->isAjaxRequest() || !isset($this->forum->section)) return;

        // Проверка на соответствие ID форума раздела текущему ID форума
        if($this->forum->section->forum_id != $this->forum->forum_id) return;

        // Удалить раздел может только модератор
        if(!$this->forum->moderator) return;

        $this->forum->section->delete();

    }

    /**
     * Оценка сообщения
     */
    public function msgratingAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        // Выставление оценки возможно только модератором при нахождении в разделе и только через AJAX запрос
        if(!$this->isAjaxRequest() || !$this->forum->moderator || !isset($this->forum->section)) return;

        $messageId = (int) $this->_getParam(self::PARAM_MESSAGE_ID);
        $rating = (int) $this->_getParam('rating');
        $subject = $this->getActivitySubject();

        $message = $this->forumService->getMessage($messageId, $subject);

        // Выставить оценку можно только сообщению оставленному слушателем
        if(!$message->createdByStudent) return;

        // Проверка на соответствие ID раздела сообщения текущему ID раздела форума
        if($this->forum->section->section_id != $message->section_id) return;

        $message->setRating($rating);

        // уведомление автора сообщения
        $section   = $this->getService('ForumSection')->getOne($this->getService('ForumSection')->fetchAll(array('section_id=?' => $message->section_id)));
        $forum     = $this->getService('ForumForum')->getOne($this->getService('ForumForum')->fetchAll(array('forum_id=?' => $message->forum_id)));

        if ($forum && $section) {

            // получение списка пользователей подписанных на уведомления
            // по данному заданию подписка может осуществляться только на занятия, с глобального форума и СВ уведомления не слать
            if ($section->lesson_id) {
                $subscribeUsers = $this->getService('SubscriptionChannel')->fetchAllDependence('Subscription',array('lesson_id=?'=>$section->lesson_id));
                if (count($subscribeUsers->subscriptions)){
                    $subscribeUsers = $subscribeUsers->subscriptions->getList('user_id');
                } else {
                    $subscribeUsers  = array();
                }
            }

            $messageParam = array(
                'MESSAGE_USER_NAME' => $message->user_name,
                'SECTION_NAME'      => ($section && $section->title)? $section->title : '',
                'FORUM_NAME'        => ($forum && $forum->title)? $forum->title : '',
                'MESSAGE_URL'       => $this->view->serverUrl($section->url())
            );

            $messenger = $this->getService('Messenger');

            $messenger->setOptions( HM_Messenger::TEMPLATE_FORUM_NEW_MARK, $messageParam);
            if (in_array($message->user_id, $subscribeUsers)) {
                $messenger->send(HM_Messenger::SYSTEM_USER_ID, $message->user_id);
            }
        }
    }

    /**
     * Отдаёт текст определённого сообщения
     * Используется для AJAX подгрузки контента слишком длинных сообщений и отметки сообщения как прочитанного
     */
    public function messageAction(){
        if($this->isAjaxRequest()) $this->_helper->layout->setLayout('ajax');
        $this->_helper->viewRenderer->setNoRender();

        if(!isset($this->forum->section)) return;

        $messageId = (int) $this->_getParam(self::PARAM_MESSAGE_ID);
        $message = $this->forumService->getMessage($messageId);
        if(!$message) return;

        $message->markShowed();

        echo $message->text;
    }

    /**
     * Отвечает за изменение приоритета темы при выводе
     * Используется функцией "прикрепить / открепить" форума
     */
    public function orderAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        // Изменение приоритета темы возможно только модератором при указании ID темы и только через AJAX запрос
        if(!$this->isAjaxRequest() || !$this->forum->moderator || !isset($this->forum->section)) return;

        $this->forum->section->setOrder($this->_getParam('order'));
    }

    /**
     * Закрывает тему 
     */
    public function themecloseAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        // Закрытие темы возможно только модератором при нахождении в разделе
        if(!$this->forum->moderator || !isset($this->forum->section)) return;

        $this->forum->section->setClosed($this->_getParam('close'));
        if (!$this->isAjaxRequest()) {
            $this->_redirector->gotoRoute(array(
                'close' => null
            ));
        }
    }

    /**
     * Вывод критической ошибки при которой завершение обработки запроса пользователя не возможно
     * 
     * @param string $message Сообщение об ошибке
     * @param integer $code Код ошибки, если применим
     * @param array $route Параметры редиректа, иначе редирект в корень проекта
     * @param string $routeName имя описания роута
     */
    protected function criticalError($message, $code = null, $route = null, $routeName = null){
        $this->_flashMessenger->addMessage(array(
            'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
            'message' => $message
        ));
        if ($this->isAjaxRequest()) {
            if (in_array($code, array(self::ERR_CODE_NOFORUM, self::ERR_CODE_NOSECTION), true)) {
                $this->getResponse()->setHttpResponseCode(404);
            } else {
                $this->getResponse()->setHttpResponseCode(500);
            }
            echo $this->view->notifications($this->_flashMessenger->getCurrentMessages(), array('html' => true));
            $this->_flashMessenger->clearCurrentMessages();
        } else {
            if(is_array($route)) $this->_redirector->gotoRoute($route, $routeName, true);
            else $this->_redirector->gotoUrl('/');
        }
    }

    public function editSectionAction()
    {
        $sectionID = $this->_getParam('section_id', 0);

        if (!$sectionID) {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Переданы неверные параметры')));
            $this->_redirector->gotoUrl($_SERVER['HTTP_REFERER']);
        }

        $section = $this->getService('ForumSection')->getOne($this->getService('ForumSection')->find($sectionID));

        if (!$section) {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Категория не найдена')));
            $this->_redirector->gotoUrl($_SERVER['HTTP_REFERER']);
        }

        $form         = new HM_Form_Section();
        $submitButton = $form->getElement('submit');
        $elementGroup = $form->getDisplayGroup('content');
        if ($submitButton) {
            $submitButton->setLabel(_('Сохранить'));
        }
        if ($elementGroup) {
            $elementGroup->setLegend(_('Редактирование категории'));
        }
        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getParams())) {
                $data = array(
                    'section_id' => $form->getValue('section_id'),
                    'title'      => $form->getValue('title'),
                    'last_msg'   => $section->last_msg
                );
                $result = $this->getService('ForumSection')->update($data);

                if ($result) {
                    $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_SUCCESS, 'message' => _('Категория обновлена')));
                } else {
                    $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Произошла ошибка при обновлении категории')));
                }
                $this->_redirector->gotoUrl($form->getValue('cancelUrl'));
            } else {
                $form->populate($this->_request->getParams());
            }
        } else {
            $form->populate($section->getValues());
        }

        $this->view->form = $form;
    }


    /**
     * Экшен отображает для занятия форум в кратком виде.
     * если нужен развернутый вид, как было изначально,
     * нужно указать роутер форума, ане дефолтный в HM_Lesson_Forum_ForumModel::getExecuteUrl
     * и index action вместо view-lesson
     */
    public function viewLessonAction ()
    {
        if (!$this->forum) {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Переданы неверные параметры')));
            $this->_redirector->gotoUrl($_SERVER['HTTP_REFERER']);
        }

        // бубен, чтобы все работало
        $this->_helper->getHelper('viewRenderer')->setScriptAction('view-lesson');
        $this->forum->section->setRouteName(HM_Forum_Library_Constants::ROUTE_SUBJECT);
        $this->forum->section = array($this->forum->section);
        $this->forum->setRouteName(HM_Forum_Library_Constants::ROUTE_SUBJECT);

        $this->view->forum = $this->forum;
    }

    public function subscribeAction()
    {
        $lessonId = $this->_getParam('lesson_id',0);
        $userId   = $this->getService('User')->getCurrentUserId();
        if (!$lessonId || !$userId) {
            $res = array('status' => 'fail', 'msg' => _('Подписка на обновления темы не выполнена.'));
        } else {
            $this->getService('Subscription')->subscribeUserToChannelByLessonId($userId, $lessonId);
            $res = array('status' => 'ok', 'msg' => _('Подписка на обновления темы выполнена успешно.'));
        }
        $this->view->assign($res);
    }

    public function unsubscribeAction()
    {
        $lessonId = $this->_getParam('lesson_id',0);
        $userId   = $this->getService('User')->getCurrentUserId();
        if (!$lessonId || !$userId) {
            $res = array('status' => 'fail', 'msg' => _('Отписка от обновлений темы не выполнена.'));
        } else {
            $this->getService('Subscription')->unsubscribeUserFromChannelByLessonId($userId, $lessonId);
            $res = array('status' => 'ok', 'msg' => _('Отписка от обновлений темы выполнена успешно.'));
        }
        $this->view->assign($res);
    }
}
