<?php
class Message_ContactController extends HM_Controller_Action_Activity
{
    protected function indexActionGrid($select = null, $isModerator, $enablePersonalInfo)
    {
        $sorting = $this->_request->getParam("ordergrid");
        if ($sorting == ""){
            $this->_request->setParam("ordergrid", 'fio_ASC');
        }         
        
        if($select == null){
            $select = $this->getService('User')->getSelect();
            $select->from(
                array('t1' => 'People'),
                array(
                    'MID' => 't1.MID',
                    'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(t1.LastName, ' ') , t1.FirstName), ' '), t1.Patronymic)"),
                    'Phone' => 't1.Phone',
                    'EMail' => 't1.Email',
                )
            );
        }
        
        // unused code
        //$roles = HM_Role_RoleModelAbstract::getBasicRoles(false,true);
        //$userService = $this->getService('User');
        
        $grid = $this->getGrid(
            $select,
            array(
                'MID' => array('hidden' => true),
                'role' => array('hidden' => true),
                'Gender' => array('hidden' => true),
                'fio'    => array(
                    'title' => _('ФИО'),
                    'decorator' => $this->view->cardLink($this->view->url(
                            array(
                                'action' => 'view',
                                'controller' => 'list',
                                'module' => 'user',
                                'user_id' => ''
                                )).'{{MID}}',_('Карточка пользователя')).' {{fio}}'),
                'Phone' => array('title' => _('Рабочий телефон'), 'decorator' => $enablePersonalInfo ? '{{Phone}}' : ''),
                'Fax' => array('title' => _('Мобильный телефон'), 'decorator' => $enablePersonalInfo ? '{{Fax}}' : ''),
                'EMail' => array('title' => _('E-Mail'), 'decorator' => $enablePersonalInfo ? '{{EMail}}' : '')
            ),
            array(
                'fio' => null,
                'Login' => null,
                'EMail' => null
            ),
            'grid'
        );
        
        
        if (
            ($isModerator || !$this->getService('Option')->getOption('disable_messages')) &&
            Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:message:send:instant-send')
        ) {
            $grid->addMassAction(
                array('module' => 'message', 'controller' => 'send', 'action' => 'index', 'subject' => $subject, 'subject_id' => $subjectId),
                _('Отправить сообщение')
            );
        }

        $grid->setPrimaryKey(array('MID'));

        $this->view->grid = $grid->deploy();
    }

    protected function indexActionList($select, $isModerator, $enablePersonalInfo)
    {
        $page = $this->_getParam('page', 1);
        
        $paginator = Zend_Paginator::factory ($select);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage(12);
        
        $items = $paginator->getCurrentItems();
        
        $userModel = new HM_User_UserModel(array());
        $mids      = array();
        $itemIndex = array();

        $roles = HM_Role_RoleModelAbstract::getBasicRoles(true, true);

        $activityService = $this->getService('Activity');
        
        foreach ($items as &$item) {
            
            $itemMid = $item['MID'];
            $userModel->MID = $itemMid;
            $userRole = HM_Role_RoleModelAbstract::getMaxRole(array_unique(explode(',', $item['role'])));
            
            $item['photo']        = $userModel->getPhoto();
            $item['role']         = $roles[$userRole ? $userRole : HM_Role_RoleModelAbstract::ROLE_ENDUSER];
            $item['is_moderator'] = $activityService->isUserActivityPotentialModerator($itemMid);
            $item['online']       = false;
            $item['last_visit']   = _('Не в сети');
            $item['position']     = '';
            
            $itemIndex[$itemMid] = $item;
            $mids[$itemMid] = $itemMid;
        }
        
        if (count($mids)) {
            
            $select = $this->getService('User')->getSelect();
            $select->from(array('s' => 'sessions'), array(
                's.mid',
                'position'   => 'p.name',
                'department' => 'pp.name',
                'stop'       => new Zend_Db_Expr('MAX(s.stop)')
            ));
            $select->joinLeft(array('p' => 'structure_of_organ'), 'p.mid = s.mid', array());
            $select->joinLeft(array('pp' => 'structure_of_organ'), 'pp.soid = p.owner_soid', array());
            $select->where('s.mid IN ('.implode(',', $mids).')');
            $select->group(array('s.mid', 'p.name', 'pp.name'));
            
            $userStops = $select->query()->fetchAll();
            
            foreach ($userStops as $stop) {
                
                $dt = new HM_Date($stop['stop']);
                $itemMid = $stop['mid'];
                
                $itemIndex[$itemMid]['online']     = mktime() - $dt->getTimestamp() < 600;
                $itemIndex[$itemMid]['last_visit'] = (($itemIndex[$itemMid]['Gender'] === '1') ? _('Был онлайн') : (($itemIndex[$itemMid]['Gender'] === '2') ? _('Была онлайн') : _('Был(а) онлайн'))) .' '.$dt->toString();
                $itemIndex[$itemMid]['position']   = ($stop['department'] ? $stop['department'].' / ' : '').$stop['position'];
                
            }
        }
        
        $this->view->enablePersonalInfo = $enablePersonalInfo;
        $this->view->items     = $itemIndex;
        $this->view->paginator = $paginator;
    }
    
    public function indexAction()
    {
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        
        $subject = $this->_getParam('subject', false);
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $onlyModerators = empty($subject); // если на уровне Портала - то только модераторов 

        $this->getService('Activity')->initializeActivityCabinet('message', 'subject', $subjectId);
        
        $isModerator = $this->getService('Activity')->isUserActivityPotentialModerator(
            $this->getService('User')->getCurrentUserId()
        );

        $select = $this->getService('Activity')->getActivityUsersSelect($onlyModerators);

        $enablePersonalInfo = ($isModerator || !$this->getService('Option')->getOption('disable_personal_info'));

        $switcher = $this->_getParam('switcher', 'list');
        
        switch ($switcher) {
            case 'grid':
                $this->indexActionGrid($select, $isModerator, $enablePersonalInfo);
                break;
            default:
                $this->indexActionList($select, $isModerator, $enablePersonalInfo);
        }
        
        $this->view->subject = $subject;
        $this->view->switcher = $switcher;

    }

    public function updateRole($field, $separator = ', ') {
        $roles = HM_Role_RoleModelAbstract::getBasicRoles(true, true);
        
        if ($field == '') {
            return $roles[HM_Role_RoleModelAbstract::ROLE_ENDUSER];
        }

        $userRoles = explode(',', $field);
        $fields = array();
        
        foreach ($userRoles as $userRole) {
            if (isset($roles[$userRole])) {
                $fields[] = $roles[$userRole];
            }
        }
        
        // #5337 - сворачивание высоких ячеек
        $result = (is_array($fields) && (count($fields) > 1)) ? array('<p class="total">' . Zend_Registry::get('serviceContainer')->getService('User')->pluralFormRolesCount(count($fields)) . '</p>') : array();
        
        foreach ($fields as $value) {
            $result[] = "<p>{$value}</p>";
        }
        
        if ($result) {
            return implode($result);
        } else {
            return _('Нет');
        }
    }

}