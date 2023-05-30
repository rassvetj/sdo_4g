<?php
	$request = Zend_Controller_Front::getInstance()->getRequest();
	$lang = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);
class Notice_IndexController extends HM_Controller_Action
{
    public function indexAction()
    {

        if (!$this->_hasParam('ordergrid_notice')) {
            $this->_setParam('ordergrid_notice', 'title_DESC');
        }

        //$this->view->isModerator = $isModerator = $this->getService('Notice')->isCurrentUserActivityModerator();
        $isModerator = true;

        $select = $this->getService('Notice')->getSelect();
        $select->from(
            'notice',
            array(
                'id',
            	'event',
				'event_translation',
            	'receiver',
                'title',
				'title_translation',
                'message',
				//'message_translation',
                'enabled'
            )
        )->where('type != ?', HM_Notice_NoticeModel::TEMPLATE_SENDALL);

        $grid = $this->getGrid(
            $select,
            array(
                'id' => array('hidden' => true),
                'event' => array(
					'title' => _('Событие'),
					'callback' => array(
						'function' => array($this, 'updateEvent'),
						'params' => array('{{event}}', '{{event_translation}}')
					)
				),
				'event_translation' => array('title' => _('Событие').(' (en)'),'hidden' => false),
                'receiver' => array('title' => _('Адресат'), 'helper' => array('name' => 'receiverType', 'params' => array('{{receiver}}'))),
                'title' => array(
					'title' => _('Заголовок'),
					'callback' => array(
						'function' => array($this, 'updateTitle'),
						'params' => array('{{title}}', '{{title_translation}}')
					)
				),
				'title_translation' => array('title' => _('Заголовок').(' (en)'),'hidden' => false),
                'enabled' => array('title' => _('Активность')),
                'message' => array('hidden' => true)
            ),
            array(
            	'event' => null,
                'title'   => null,
            	'receiver' => array('values' => HM_Notice_NoticeModel::getReceivers()),
                'message' => null
            ),
            'grid_news'
        );


        $grid->updateColumn('enabled',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateEnabled'),
                    'params' => array('{{enabled}}')
                )
            )
        );
		
   /*     $grid->updateColumn('event',
			array('callback' =>
				array('function' => array($this, 'updateEvent'),
					  'params' => array('{{event}}', '{{event_translation}}')
				)
			)
		);		
		
		
        $grid->updateColumn('title',
			array('callback' =>
				array('function' => array($this, 'updateTitle'),
					  'params' => array('{{title}}', '{{title_translation}}')
				)
			)
		);		
		*/


        if ($isModerator) {

            $grid->addAction(array(
                'module' => 'notice',
                'controller' => 'index',
                'action' => 'edit'
            ),
                array('id'),
                $this->view->icon('edit')
            );

        }

        $grid->addMassAction(array('action' => 'enable'), _('Включить'));
        $grid->addMassAction(array('action' => 'disable'), _('Выключить'));
        
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid->deploy();

        $this->view->subjectName = $subjectName;
        $this->view->subjectId = $subjectId;
    }

    public function enableAction(){
        $ids = explode(',', $this->_request->getParam('postMassIds_grid_news'));

        $array = array('enabled' => 1);
        $res = $this->getService('Notice')->updateWhere($array, array('id IN (?)' => $ids));
        if ($res > 0) {
            $this->_flashMessenger->addMessage(_('Сообщения включены!'));
            $this->_redirector->gotoSimple('notice', '', '');
        } else {
            $this->_flashMessenger->addMessage(_('Произошла ошибка во время включения сообщений!'));
            $this->_redirector->gotoSimple('notice', '', '');
        }
        
    }
    
    public function disableAction(){
        $ids = explode(',', $this->_request->getParam('postMassIds_grid_news'));

        $array = array('enabled' => 0);
        $res = $this->getService('Notice')->updateWhere($array, array('id IN (?)' => $ids));
        if ($res > 0) {
            $this->_flashMessenger->addMessage(_('Сообщения выключены!'));
            $this->_redirector->gotoSimple('notice', '', '');
        } else {
            $this->_flashMessenger->addMessage(_('Произошла ошибка во время выключения сообщений!'));
            $this->_redirector->gotoSimple('notice', '', '');
        }
    }
    
    
    public function editAction()
    {
        $notice_id = (int) $this->_getParam('id', 0);

//        if (!$this->getService('Notice')->isCurrentUserActivityModerator()) {
//            $this->_flashMessenger->addMessage(array('message' => _('Вы не являетесь модератором данного вида взаимодействия'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
//            $this->_redirector->gotoSimple('index', 'index', 'notice', array('subject' => $subjectName, 'subject_id' => $subjectId));
//        }

        $form = new HM_Form_Notice();
        $form->setAction($this->view->url(array('module' => 'notice', 'controller' => 'index', 'action' => 'edit')));

        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {

                $this->getService('Notice')->update(array(
					'event_translation' => $form->getValue('event_translation'),
                    'title' => $form->getValue('title'),
                    'message' => $form->getValue('message'),
                    'title_translation' => $form->getValue('title_translation'),
                    'message_translation' => $form->getValue('message_translation'),					
                    'id' => $form->getValue('id'),
                    'enabled' => $form->getValue('enabled')
                ));

                $this->_flashMessenger->addMessage(_('Оповещение успешно изменено'));
                $this->_redirector->gotoSimple('index', 'index', 'notice');

            }
        } else {
            if ($notice_id) {
                $notice = $this->getOne($this->getService('Notice')->find($notice_id));
                $values = array();
                if ($notice) {
                    $values = $notice->getValues();
                }
                $form->setDefaults($values);
            }
        }

        $this->view->form = $form;

    }


    public function updateEnabled($param){
        if($param == 1)
            return _('Да');
        return _('Нет');
    }

	
	public function updateEvent($event, $event_translation='') {  
		
		if($lang == 'eng' && $event_translation != '') /*{
			$event = $event_translation;		
			 exit($event_translation);
		}	*/
		return $event_translation;
		else
        return $event;
    }
		
	public function updateTitle($title, $title_translation='') {  
		
		if($lang == 'eng' && $title_translation != '') /*{
			$title = $title_translation;		
			 exit($title_translation);
		}	*/
		 return $title_translation;
		else
        return $title;
    }
	
}