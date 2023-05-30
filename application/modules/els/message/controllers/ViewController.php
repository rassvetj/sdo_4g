<?php

class Message_ViewController extends HM_Controller_Action_Activity implements Es_Entity_EventViewer {

    private $_users = array();

    public function indexAction() {
        $subject = $this->_getParam('subject', false);
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $select = $this->getService('Message')->getSelect();
        $select->from(
                        array('t1' => 'messages'), array('tempss' => 'from', 'from', 'toWhom' => 'to', 'message_id', 'created', 
						'message' => new Zend_Db_Expr("SUBSTRING(message, 1, 8000)") //--fix для поиска
						
						))
                ->where('(t1.from = ' . $this->getService('User')->getCurrentUserId() . ' OR t1.to = ' . $this->getService('User')->getCurrentUserId() . ')')
                ->order('created DESC');

        // в глобальном сервисе (через гл.меню) показываем вообще все мои сообщения
        if ($subject && $subjectId) {
            $select
                    ->where('subject = ?', $subject)
                    ->where('subject_id = ?', $subjectId);
        }
        // Не знаю почему, но если использовать
        // "to" , то вместо От отображается "-", пришлось to заменить на toWhom

        $grid = $this->getGrid(
                $select, array(
            'message_id' => array('hidden' => true),
            'tempss' => array('hidden' => true),
            'from' => array('title' => _('Тип')),
            'toWhom' => array('title' => _('От/Кому')),
            'message' => array('title' => _('Сообщение'), 'escape' => false),
            'created' => array('title' => _('Дата'), 'format' => 'date')
                ), array(
            'message_id' => null,
            'from' => array('values' => array($this->getService('User')->getCurrentUserId() => _('Исходящие'),
                    '!=' . $this->getService('User')->getCurrentUserId() => _('Входящие')
                )
            ),
            'message' => null,            
			'toWhom' =>
				array('values' => $toWhom,
					'callback' => array(
						'function'=>array($this, 'toFromFilter'),
						'params'=>array()
				)
			),
			
			
            'created' => array('render' => 'date')
                )
        );

        $grid->updateColumn('message', array(
            'callback' =>
            array(
                'function' => array($this, 'getSubString'),
                'params' => array('{{message}}', '{{message_id}}')
            )
                )
        );
		
        $grid->updateColumn('toWhom', array(
            'callback' =>
            array(
                'function' => array($this, 'getUser'),
                'params' => array('{{tempss}}', '{{toWhom}}')
            )
                )
        );		



        $grid->updateColumn('from', array(
            'callback' =>
            array(
                'function' => array($this, 'getDirection'),
                'params' => array('{{from}}')
            )
                )
        );


		if (
			$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TUTOR) ||
			$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TEACHER)
		){
			$grid->addAction(array(
				'module' => 'message',
				'controller' => 'edit',
				'action' => 'delete',
			),
				array('message_id'),
				$this->view->icon('delete', _('Удалить'), "if (confirm('"._('Удалить сообщение?')."')) return true; return false;")
			);
		}

        //$left1 = new Bvb_Grid_Extra_Column();
        //$left1->position('left')->name('direction')->title(_('Тип'))->callback(array('function' => array($this, 'getDirection'), 'params' => array('{{from}}', '{{to}}')));
        //$left2 = new Bvb_Grid_Extra_Column();
        //$left2->position('left')->name('user')->title(_('От/Кому'))->callback(array('function' => array($this, 'getUser'), 'params' => array('{{from}}', '{{to}}')));
        //$grid->addExtraColumns($left1, $left2);
//        $filters = new Bvb_Grid_Filters();
//        $filters->addFilter('direction', array('values' => array(_('Исходящее'), _('Входящее'))));
//        $grid->addFilters($filters);

        /*
          $grid->addMassAction(
          array('module' => 'message', 'controller' => 'send', 'action' => 'index'),
          _('Отправить сообщение')
          ); */
        /*
          $grid->updateColumn('created', array(
          'callback' => array(
          'function' => array(
          new HM_Message_MessageModel(array()),
          'dateTime'),
          'params' => array(
          '{{created}}')))
          );
         */
        $this->getService('Activity')->initializeActivityCabinet('', 'subject', $subjectId);
        $isModerator = $this->getService('Activity')->isUserActivityPotentialModerator(
                $this->getService('User')->getCurrentUserId()
        );
        $this->view->disableMessages = !$isModerator && $this->getService('Option')->getOption('disable_messages');

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid->deploy();
        $this->getService('EventServerDispatcher')->trigger(
            Es_Service_Dispatcher::EVENT_UNSUBSCRIBE,
            $this,
            array('filter' => $this->getFilterByRequest($this->getRequest()))
        );
    }

    public function getFilterByRequest(\Zend_Controller_Request_Http $request) {
        $filter = $this->getService('ESFactory')->newFilter();
        $filter->setUserId($this->getService('User')->getCurrentUserId());
        $from = $request->getParam('from', null);
        if ($from !== null) {
            $groupIds = array();
            if (is_array($from)) {
                foreach ($from as $sender) {
                    $group = $this->getService('ESFactory')->eventGroup(
                        HM_Message_MessageESTrigger::EVENT_GROUP_NAME_PREFIX, (int)$sender
                    );
                    if ($group->getId() !== null) $groupIds[] = $group->getId();
                }
            } else {
                $group = $this->getService('ESFactory')->eventGroup(
                    HM_Message_MessageESTrigger::EVENT_GROUP_NAME_PREFIX, (int)$from 
                );
                if ($group->getId() !== null) $groupIds[] = $group->getId();
            }
            $filter->setGroupId($groupIds);
        }
        $filter->setEventId($request->getParam('eventId', null));
        return $filter;
    }

    public function oneAction() {

        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();




        $messageId = (int) $this->_getParam('message_id', 0);

        $res = $this->getService('Message')->getOne($this->getService('Message')->find($messageId));
        echo $res->message;
    }

    public function getUser($from, $to) {

        $userId = $to;
        if ($from != $this->getService('User')->getCurrentUserId()) {
            $userId = $from;
        }

        if (!isset($this->_users[$userId])) {
            $user = $this->getService('User')->getOne(
                    $this->getService('User')->find($userId)
            );
            if ($user) {
                $this->_users[$user->MID] = $user;
            }
        }

        if (isset($this->_users[$userId])) {
            $tt = '<div>' . $this->view->cardLink($this->view->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'user_id' => $userId))) . $this->_users[$userId]->getName() . '</div>';

            return $tt;
        }

        if ($userId == HM_Messenger::SYSTEM_USER_ID) {
            return $this->getService('Option')->getOption('dekanName');
        }

        return sprintf(_('Пользователь #%d удалён'), $userId);
    }

    public function getDirection($from) {
        if ($from == $this->getService('User')->getCurrentUserId()) {
            return _('Исходящее');
        }

        return _('Входящее');
    }

    /**
     * get real sub string, removing all formatting from a string anyway, not only when provided string
     * is too long to output as before.
     *
     * @param string $field provided html/text message
     * @param int $id message id
     * @param int $len length of a string cut before pasting read more link, default:300
     *
     * @return string
     */
    public function getSubString($field, $id, $len = 300) {
        $field = nl2br($field);
        $field = strip_tags($field);
        if (strlen($field) > $len) {
            $subtext = wordwrap($field, $len, "<br/>");
            $res = explode("<br/>", $subtext);
            $url = $this->view->url(array('module' => 'message',
                'controller' => 'view',
                'action' => 'one',
                'message_id' => $id
                    )
            );

            $result = $res[0] . "... " . $this->view->cardLink($url, _('Полный текст сообщения'), 'text');
        } else {
            $result = $field;
        }

        return $result;
    }
	
	
	/**
	 * Для фильтра по полю "От/Кому"
	 * Если указать себя в качестве отбора, то найдет все сообщения
	*/
	public function toFromFilter($data){
        
		$value=$data['value'];
		$select=$data['select'];		
		
		if($this->getService('Option')->getOption('dekanName') == $value){			
			$select->where($this->quoteInto('t1.from IN (?) OR t1.to IN (?)', HM_Messenger::SYSTEM_USER_ID));	
		} else {		
			$usesrSelect = $this->getService('User')->getSelect()->from('People', array('MID'))->where(
				$this->quoteInto("(LOWER(CONCAT(CONCAT(CONCAT(CONCAT(LastName, ' ') , FirstName), ' '), Patronymic)) LIKE LOWER(?))", '%'.$value.'%')		
			);
			$res = $usesrSelect->query()->fetchAll();
			$userIDs = array();		
			if(count($res)){
				foreach($res as $u){
					$userIDs[$u['MID']] = $u['MID'];
				}
			}			
			if(count($userIDs)){				
				$select->where($this->quoteInto('t1.from IN (?) OR t1.to IN (?)', $userIDs));						
			} else {
				$select->where('1=0');		
			}
		}
    }

}