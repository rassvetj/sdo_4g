<?php
class StudyGroups_UsersController extends HM_Controller_Action
{

    protected $departmentCache = array();
    protected $id          = 0;
	private $_exportType = false;
	private $_roleCache  = false;

    public function init()
    {
        parent::init();
        if (!$this->isAjaxRequest()) {
            $this->id = $this->_request->getParam('subject_id',0);
            $subject = $this->getOne($this->getService('Subject')->find( $this->id));
            if ($subject) {
                $this->view->setExtended(
                    array(
                        'subjectName'        => 'Subject',
                        'subjectId'          => $this->id,
                        'subjectIdParamName' => 'subject_id',
                        'subjectIdFieldName' => 'subid',
                        'subject'            => $subject
                    )
                );
            }
        }

    }

    protected function _getGridId()
    {
        return 'grid'. $this->_getParam('group_id', 0);
    }

    public function indexAction()
    {	
        $groupId  = $this->_getParam('group_id', 0);
        $group    = $this->getOne($this->getService('StudyGroup')->find($groupId));
        if(!$group) {
            $this->_redirector->gotoSimple('index', 'list', 'study-groups');
        }
        $this->view->setSubHeader($group->name);

        $gridId     = $this->_getGridId();
		$exportType = $this->_getParam('_exportTo' . $gridId, false);
		
		$this->_exportType = $exportType;
		
		$default = new Zend_Session_Namespace('default');
        $notAll = !$this->_getParam('all', isset($default->grid['study-groups-users-index'][$gridId]['all']) ? $default->grid['study-groups-users-index'][$gridId]['all'] : null);

        $sorting = $this->_request->getParam("order".$gridId);
        if ($sorting == '') {
            $this->_request->setParam("order".$gridId, 'stgid_DESC');
        }

        if (!$this->isGridAjaxRequest() && $this->_request->getParam('end[from]grid') == "") {
            $this->_request->setParam('end[from]grid', date('d.m.Y', strtotime('-1 day')));
        }

        $select = $this->getService('User')->getSelect();
        
		$select->from(array('t1' => 'People'), array(
			'MID'             => 't1.MID',
			'notempty'        => 't1.MID',
			'fio'             => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(t1.LastName, ' ') , t1.FirstName), ' '), t1.Patronymic)"),
			'departments'     => 't1.MID',
			'positions'       => 't1.MID',
			'login'           => 't1.Login',
			'groups'          => new Zend_Db_Expr('GROUP_CONCAT(sgc.group_id)'),
			'email'           => 't1.Email',
			'email_confirmed' => 't1.email_confirmed',
			'roles'           => 't1.MID',
			'status'          => 't1.blocked',
			'ldap'            => 't1.isAD',
			'tags'            => 't1.MID',
			'stgid'           => 't1.MID',
		));
		
		if((int)$group->type == HM_StudyGroup_StudyGroupModel::TYPE_AUTO){
            $select->joinInner(array('sgc' => 'study_groups_users'), 'sgc.user_id = t1.MID AND sgc.group_id = '.$groupId, array());
        } else {
            if($notAll) {
                $select->joinInner(array('sgc' => 'study_groups_custom'), 'sgc.user_id = t1.MID AND sgc.group_id = '.$groupId, array());
            } else {
                $select->joinLeft(array('sgc' => 'study_groups_users'), 'sgc.user_id = t1.MID', array());
            }
        }
		$select->group(array('t1.MID', 't1.LastName' ,  't1.FirstName', 't1.Patronymic', 't1.Login', 't1.Email', 't1.email_confirmed', 't1.blocked', 't1.isAD'));
		
		$roles = HM_Role_RoleModelAbstract::getBasicRoles(false);
        unset($roles[HM_Role_RoleModelAbstract::ROLE_USER]);
        $roles = array_merge(array("ISNULL" => _('Пользователь')), $roles);

        $grid = $this->getGrid($select,
            array(
                'MID'             => array('hidden' => true),
                'notempty'        => array('hidden' => true),
                'email_confirmed' => array('hidden' => true),
                'stgid'           => array('hidden' => true),
                'fio'             => array('title'  => _('ФИО')),
                'login'           => array('title'  => _('Логин')),
                'email'           => array('title'  => _('E-mail')),
                'departments'     => array('title'  => _('Подразделение')),
                'positions'       => array('title'  => _('Должность')),
                'groups'          => array('title'  => _('Группы')),
                'roles'           => array('title'  => _('Роли')),
                'status'          => array('title'  => _('Статус')),
                'ldap'            => array('hidden' => true),
                'tags'            => array('title'  => _('Метки')),
            ),
            array(
				'fio'             => null,
                'login'           => null,
                'departments'     => array('callback' => array('function' => array($this, 'deparmentFilter'), 'params'   => array())),
                'positions'       => array('callback' => array('function' => array($this, 'positionFilter'),  'params'   => array())),
                'groups'          => array('callback' => array('function' => array($this, 'groupsFilter'),    'params'   => array())),                
				'email'           => null,
                'date_registered' => array('render' => 'Date'),
                'roles'           => array('values' => $roles),
                'status'          => array('values' => array(0 => _('Активный'), 1 => _('Заблокирован'))),
                'tags'            => array('callback' => array('function' => array($this, 'filterTags'))),
            ),
            $gridId,
            'notempty'
        );
		
		$grid->updateColumn('fio', array('decorator' => 
			  $this->view->cardLink($this->view->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'gridmod' => null, 'user_id' => ''), null, true) . '{{MID}}')
			. '<a href="' . $this->view->url(array('module' => 'user', 'controller' => 'edit', 'action' => 'card', 'gridmod' => null, 'user_id' => ''), null, true)
			. '{{MID}}' . '">' . '{{fio}}</a>'
		));
		
		$grid->updateColumn('email', array('callback' => array(
			'function' => array($this, 'updateEmail'),
			'params'   => array('{{email}}', '{{email_confirmed}}', $this->getService('Option')->getOption('regValidateEmail'))
		)));
		
		$grid->updateColumn('status', array('callback' => array(
			'function' => array($this, 'updateStatus'),
			'params'   => array('{{status}}')
		)));
		
		$grid->updateColumn('fio', array('callback' => array(
			'function' => array($this, 'updateName'),
			'params'   => array('{{fio}}', '{{MID}}')
		)));
		
		
		# При выгрузке всех пользователей выгрузка ложиться. Поэтому, максимум облегчаем запрос.
		if(in_array($this->_exportType, array('excel', 'word')) && !$notAll){
			$grid->updateColumn('departments', array('callback' => array('function' => array($this, 'emptyCap'), 'params' => array())));
			$grid->updateColumn('positions',   array('callback' => array('function' => array($this, 'emptyCap'), 'params' => array())));			
			$grid->updateColumn('tags',        array('callback' => array('function' => array($this, 'emptyCap'), 'params' => array())));
		} else {
			$grid->updateColumn('departments', array('callback' => array(
				'function' => array($this, 'departmentsCache'),
				'params'   => array('{{departments}}', $select)
			)));
			
			$grid->updateColumn('positions', array('callback' => array(
				'function' => array($this, 'departmentsCache'),
				'params'   => array('{{positions}}', $select, true)  
			)));
			
			$grid->updateColumn('tags', array('callback' => array(
				'function' => array($this, 'displayTags'),
				'params'   => array('{{tags}}', $this->getService('TagRef')->getUserType()) 
			)));
		}
		
		$grid->updateColumn('groups', array('callback' => array(
			'function' => array($this, 'groupsCache'),
			'params'   => array('{{groups}}', $select, true)
		)));
		
		$grid->updateColumn('roles', array('callback' => array(
			'function' => array($this, 'updateRole'),
			'params' => array('{{roles}}', $select)
		)));

        if((int)$group->type == HM_StudyGroup_StudyGroupModel::TYPE_CUSTOM){
            $grid->setGridSwitcher(array(
                array('name' => 'users',     'title' => _('пользователей группы'), 'params' => array('all' => 0)),
                array('name' => 'all_users', 'title' => _('всех пользователей'),   'params' => array('all' => 1))
            ));
            $grid->setClassRowCondition("in_array(".$groupId.", array({{groups}}))", "selected");
        }

        if((int)$group->type == HM_StudyGroup_StudyGroupModel::TYPE_CUSTOM && $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_DEAN)){
            $grid->addMassAction(array('module' => 'study-groups', 'controller' => 'users', 'action' => 'include'), _('Включить в группу'));
            $grid->addMassAction(array('module' => 'study-groups', 'controller' => 'users', 'action' => 'exclude'), _('Исключить из группы'));
        }

        $grid->addAction(array(
                'module'     => 'user',
                'controller' => 'list',
                'action'     => 'login-as'
            ),
            array('MID'),
            _('Войти от имени пользователя'),
            _('Вы действительно хотите войти в систему от имени данного пользователя? При этом все функции Вашей текущей роли будут недоступны. Вы сможете вернуться в свою роль при помощи обратной функции "Выйти из режима". Продолжить?') // не работает??
        );

		$this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid            = $grid->deploy();
    }

    public function deparmentFilter($data)
    {	
		$value   = trim($data['value']);
        $select  = $data['select'];
		$userIds = array();
		
		if(strlen($value) < 3){ return; }
		
		$parentItems = $this->getService('Orgstructure')->fetchAll($this->getService('User')->quoteInto('name LIKE LOWER(?)', "%" . $value . "%"));
		if(!count($parentItems)){
			$select->where('1=0');
			return;
		}
		
		$select->joinInner(
			array('organDep' => 'structure_of_organ'), 
			$this->getService('User')->quoteInto('organDep.mid = t1.MID AND owner_soid IN (?)', $parentItems->getList('soid')),
			array()
		);
    }

	public function emptyCap()	
	{
		return _('n/a');
	}

    public function positionFilter($data)
    {
        $value   = trim($data['value']);
        $select  = $data['select'];
		$userIds = array();
		
		if(strlen($value) < 3){ return; }
		
		$select->joinInner(
			array('organ' => 'structure_of_organ'), 
			$this->getService('User')->quoteInto('organ.mid = t1.MID AND organ.name LIKE LOWER(?)', "%" . $value . "%"),
			array()
		);
    }

    public function includeAction()
    {
        $ids = explode(',', $this->_request->getParam('postMassIds_'.$this->_getGridId()));
        $groupId = $this->_getParam('group_id', 0);

        if(!$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_DEAN)) {
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Не достаточно прав')
            ));
            $this->_redirector->gotoSimple('index', 'users', 'study-groups', array('group_id' => $groupId));
        }

        foreach ($ids as $id) {
            if(!$this->getService('StudyGroupCustom')->isGroupUser($groupId, $id)) {
                $this->getService('StudyGroupCustom')->addUser($groupId, $id);
            }
        }

        $this->_flashMessenger->addMessage(_('Пользователи успешно включены в группу'));
        $this->_redirector->gotoSimple('index', 'users', 'study-groups', array('group_id' => $groupId));
    }

    public function excludeAction()
    {
        $ids = explode(',', $this->_request->getParam('postMassIds_'.$this->_getGridId()));
        $groupId = $this->_getParam('group_id', 0);

        if(!$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_DEAN)) {
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Не достаточно прав')
            ));
            $this->_redirector->gotoSimple('index', 'users', 'study-groups', array('group_id' => $groupId));
        }

        foreach ($ids as $id) {
            $this->getService('StudyGroupCustom')->removeUser($groupId, $id);
        }

        $this->_flashMessenger->addMessage(_('Пользователи успешно исключены из группы'));
        $this->_redirector->gotoSimple('index', 'users', 'study-groups', array('group_id' => $groupId));

    }

    /**
     * @param string $field Поле из таблицы
     * @return string Возвращаем статус
     */
    public function updateStatus($field)
    {
        if ($field == 0) {
            return _('Активный');
        } else {
            return _('Заблокирован');
        }
    }

    /**
     * @param string $field Поле для обработки
     * @param string $separator Разделитель
     * @return string
     */
    public function updateRole($field, $select = false, $separator = ', ')
    {
		$mid = (int)$field;
		
		if ($select instanceof Zend_Db_Select) {
			if($this->_roleCache === false){
				$userIds = array();
				$items   = $select->query()->fetchAll();
				if(empty($items)){ return; }
				foreach($items as $i){ $userIds[$i['MID']] = $i['MID']; }
				$roleSelect = $this->getService('User')->getSelect();        
				$roleSelect->from('roles', array('mid', 'role'));
				$roleSelect->where($this->getService('User')->quoteInto('mid IN (?)', $userIds));
				$roles = $roleSelect->query()->fetchAll();
				if(empty($roles)){ return; }
				foreach($roles as $role){
					$this->_roleCache[$role['mid']] = $role['role'];
				}
			}
		}
		
		if(!array_key_exists($mid, $this->_roleCache)){
			return _('Нет');
		}
		
		$field = $this->_roleCache[$mid];
		
		if(empty($field)){
			return _('Нет');
		}
		
		$roles = HM_Role_RoleModelAbstract::getBasicRoles(false, true);

        ksort($roles, SORT_STRING); // решает проблему совпадения кодов ролей (например, atmanager и manager)
        //if ($field == '') return $roles['user'];
        $str = str_replace(array_keys($roles), array_values($roles), $field);

        // #5337 - сворачивание высоких ячеек
        $fields = explode(',', $str);
        //$result = (is_array($fields) && (count($fields) > 1)) ? array('<p class="total">' . Zend_Registry::get('serviceContainer')->getService('User')->pluralFormRolesCount(count($fields)) . '</p>') : array();
        $result[] = "<p>" . $roles[HM_Role_RoleModelAbstract::ROLE_ENDUSER] . "</p>";
        $i = 1;
        foreach ($fields as $value) {
            if(in_array($value, $roles) && $value != $roles[HM_Role_RoleModelAbstract::ROLE_ENDUSER]) {
                $result[] = "<p>{$value}</p>";
                $i++;
            }
        }
        $result = array_reverse($result);
        $result[] = ($i > 1) ? '<p class="total">' . Zend_Registry::get('serviceContainer')->getService('User')->pluralFormRolesCount($i) . '</p>' : '';
        $result = array_reverse($result);

        if ($result)
            return implode('', $result);
        else
            return _('Нет');

    }

    public function updateName($name, $userId)
    {
        $name = trim($name);
        if (!strlen($name)) {
            $name = sprintf(_('Пользователь #%d'), $userId);
        }
        return $name;
    }

    public function updateDate($date)
    {
        if (!strtotime($date)) return '';
        return $date;
    }
	
	public function filterTags($data)
	{
		$value   = trim($data['value']);
        $select  = $data['select'];
		$userIds = array();
		
		if(strlen($value) < 3){ return; }
		
		$subSelect = $this->getService('User')->getSelect();
		$subSelect->from(array('tg' => 'tag'), array('mid' => 'ref.item_id'));
		$subSelect->join(array('ref' => 'tag_ref'), 'ref.tag_id = tg.id AND ref.item_type = ' . $this->getService('TagRefUser')->getUserType(), array());
		$subSelect->where($this->getService('User')->quoteInto('LOWER(tg.body) LIKE ?', '%' . $value . '%'));
		
		$items = $subSelect->query()->fetchAll();
		foreach($items as $item){
			$userIds[$item->mid] = $item->mid;
		}
		if(empty($userIds)){
			$select->where('1=0');
			return;
		}
		$select->where($this->getService('User')->quoteInto('t1.MID IN (?)', $userIds));
	}
	
}