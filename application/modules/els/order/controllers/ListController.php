<?php
class Order_ListController extends HM_Controller_Action
{

    protected $service     = 'Subject';
    protected $idParamName = 'subject_id';
    protected $idFieldName = 'subid';
    protected $id          = 0;
	protected $_currentLang = 'rus';

    protected $_claimantsCache = null;

    public function init()
    {
        parent::init();
		
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$this->_currentLang = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);

        if (!$this->isAjaxRequest()) {

			// бубен первый - если не аякс, то значит точно "активные заявки" в гриде
			if ( !$this->_getParam("processed{$gridId}",0)) {
				$default = new Zend_Session_Namespace('default');
				$default->grid_filter_processed = 0;
			}

            $subjectId = (int) $this->_getParam('subject_id', 0);
            if ($subjectId) { // Делаем страницу расширенной
                $this->id = (int) $this->_getParam($this->idParamName, 0);
                $subject = $this->getOne($this->getService($this->service)->find($this->id));

                $this->view->setExtended(
                    array(
                        'subjectName' => $this->service,
                        'subjectId' => $this->id,
                        'subjectIdParamName' => $this->idParamName,
                        'subjectIdFieldName' => $this->idFieldName,
                        'subject' => $subject
                    )
                );
            }
        }


    }

    /**
     * Этой проверкой отсекаем все заявки на курсы BASETYPE_BASE
     * (их согласовывать только в индивид.порядке, поскольку там есть какой-никакой процесс)
     * Все остальные курсы (BASETYPE_PRACTICE, BASETYPE_SESSION) - разрешаем групповое согласование
     *
     * @return unknown
     */
    public function checkBaseAction()
    {
        $this->_helper->ContextSwitch()
            ->setAutoJsonSerialization(true)
            ->addActionContext('check-base', 'json')
            ->initContext('json');

        $ids = array(0);
        $ids = array_merge($ids, explode(',', $this->_getParam('ids')) );
        $collection = $this->getService('Claimant')->fetchAllDependence('BaseSubject', array('SID IN (?)' => $ids));

        if(count($collection) == 0){
            $this->view->status = 'fail';
            $this->view->subjects =  _('Нет отмеченных заявок.');
            return;
        }

        foreach($collection as $element){
            if($element->base_subject){
                $this->view->status = 'fail';
                $this->view->subjects =  _('Бизнес-процесс согласования заявок по данным курсам требует дополнительных условий (выбор или формирование учебной сессии для зачисления претендентов). Вы можете воспользоваться диалогом "Бизнес-процесс" для выполнения этих условий.');
                return;
            }
        }

        $this->view->status = 'success';
        return true;
    }


    public function indexAction()
    {	
        $subjectId = (int) $this->_getParam('subject_id', 0);
       
		//$res = $this->getService('Claimant')->updateClaimant();
		//print_r($res);
        if (!$this->isGridAjaxRequest() && !$this->_hasParam('ordergrid')) {
            $this->_setParam('ordergrid', 'created_DESC');
        }


        $gridId = 'grid';

        $default = new Zend_Session_Namespace('default');
        $processed = (bool) $this->_getParam("processed{$gridId}",
                                             isset($default->grid_filter_processed) ?
                                                   $default->grid_filter_processed:
                                                    0);
        // бубен второй - если был клик по свичу, то запоминаем это состояние.
        if ( array_key_exists("processed{$gridId}", $this->_request->getParams()) ) {
            $default->grid_filter_processed = (int) $this->_getParam("processed{$gridId}");
        }

        // если нет инфы о том что вкладка все и нет инфы о фильтрации то обнуляемся.
        /*if( !array_key_exists("processed{$gridId}", $this->_request->getParams()) &&
            !array_key_exists("order{$gridId}", $this->_request->getParams())) {//order есть всегда
                $default->grid_filter_processed = 0;
        }*/
        //var_dump($this->_request->getParams());
		
        $claimantService = $this->getService('Claimant');
		
        $subjectService = $this->getService('Subject');
		
        $select = $claimantService->getSelect();

		$select_fields = array(
                'c.SID',
                'bulbs'           => 'c.SID',
                'fio'             => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                'created_fio'             => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(pc.LastName, ' ') , pc.FirstName), ' '), pc.Patronymic)"),
                'departments'     => new Zend_Db_Expr('GROUP_CONCAT(d.owner_soid)'),
                'base_subject_id' => 'sbase.subid',
                'base_subject1'   => 'sbase.name',
                's.name',
                's.period',
                's.longtime',
//                'stateStatus'   => 'st.status', // работает неверно, противоречит другому столбцу с таким же названием "статус"
                'c.created',
                'c.created_by',
                's.begin',
                's.end',
				'c.MID',
				'c.dublicate',  
                'stateCurrent' => 'st.current_state',

            );
		if ( $processed ) {
			$select_fields[] = 'c.status';
		}
        $select ->from(array('c' => 'claimants'), $select_fields)
				->joinLeft(array('p' => 'People'), 'c.MID = p.MID', array())
				->joinLeft(array('pc' => 'People'), 'c.created_by = pc.MID', array())
                ->joinLeft(array('s' => 'subjects'), 'c.CID = s.subid', array())
                ->joinLeft(array('sbase' => 'subjects'), 'c.base_subject = sbase.subid', array())
                ->joinLeft(array('d' => 'structure_of_organ'),
                          'd.mid = c.MID',
                          array());

        $select->joinLeft(array('st' => 'state_of_process'), 'c.SID = st.item_id AND process_type = ' . HM_Process_ProcessModel::PROCESS_ORDER, array());


        if (!$processed) {
            $select->where('c.status = ?', HM_Role_ClaimantModel::STATUS_NEW);
        }

        $arGroup = array(
                            'c.MID',
							'c.dublicate', 	
                            'p.LastName',
                            'p.FirstName',
                            'p.Patronymic',
                            'pc.LastName',
                            'pc.FirstName',
                            'pc.Patronymic',
                            'c.SID',
                            'process' => 'c.SID',
                            's.name',
                            's.period',
                            's.longtime',
                            'sbase.subid',
                            'sbase.name',
                            'c.created',
                            'c.created_by',
                            's.begin',
                            's.end',
                            'st.current_state',
                        );
        if ( $processed ) {
	        $arGroup[] = 'c.status';
		}
        $select->group($arGroup);

		// Область ответственности
        if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),HM_Role_RoleModelAbstract::ROLE_DEAN)){
            $options = $this->getService('Dean')->getResponsibilityOptions($this->getService('User')->getCurrentUserId());
            if($options['unlimited_subjects'] != 1){
                $select->joinLeft(array('d2' => 'deans'), 'd2.subject_id = s.subid', array())
                    ->where('(d2.MID = ? OR d2.MID IS NULL)', $this->getService('User')->getCurrentUserId());
            }
            if($options['unlimited_classifiers'] != 1){
                $select->joinLeft(
                    array('cl' => 'classifiers_links'),
                    '(cl.type = '.HM_Classifier_Link_LinkModel::TYPE_PEOPLE.' AND cl.item_id = c.MID) OR (cl.type = '.HM_Classifier_Link_LinkModel::TYPE_STRUCTURE.' AND cl.item_id = d.soid)',
                    array()
                );
            }
            $userId = $this->getService('User')->getCurrentUserId();
            if($options['unlimited_classifiers'] != 1 && $options['unlimited_subjects'] != 1){
                $responsibilities = $this->getService('DeanResponsibility')->getResponsibilities($userId);
                $area = $responsibilities->getList('classifier_id', 'classifier_id');
                $subjectResp = $this->getService('Dean')->getAssignedSubjectsResponsibilities($userId);
                $subj = $subjectResp->getList('subject_id', 'MID');
                $subj = count($subj) ? array_keys($subj) : array(0);
                $area = count($area) ? $area : array(0);
                $select->where($this->quoteInto('(cl.classifier_id IN (?) OR d2.subject_id IN (?))', array($area, $subj)));
            }elseif($options['unlimited_classifiers'] == 1 && $options['unlimited_subjects'] != 1){
                $subjectResp = $this->getService('Dean')->getAssignedSubjectsResponsibilities($userId);
                $subj = $subjectResp->getList('subject_id', 'MID');
                if(count($subj))
                    $select->where($this->quoteInto('d2.subject_id IN (?)', array(array_keys($subj))));
                else
                     $select->where('d2.subject_id IN (?)', array(0));
            }elseif($options['unlimited_classifiers'] != 1 && $options['unlimited_subjects'] == 1){
                $responsibilities = $this->getService('DeanResponsibility')->getResponsibilities($userId);
                $area = $responsibilities->getList('classifier_id', 'classifier_id');
                if (count($area)) {
                $select->where($this->quoteInto('cl.classifier_id IN (?)', array($area)));
                } else {
                    $select->where('cl.classifier_id IN (?)', array(0));
                }
            }
        }

        if ($subjectId) {
            $select->where('c.CID = ' . (int)$subjectId . ' or c.base_subject = ' . (int)$subjectId);
        }
        if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_ENDUSER, HM_Role_RoleModelAbstract::ROLE_USER))){
            //$select->where('c.MID = ?', $this->getService('User')->getCurrentUserId());
        }

        if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),HM_Role_RoleModelAbstract::ROLE_ENDUSER)){
            $select->joinLeft(array('p1' => 'People'), 'c.MID = p1.MID AND p1.head_mid = ' . $this->getService('User')->getCurrentUserId(), array());
            $select->where('(c.MID = ? or p1.MID != NULL)', $this->getService('User')->getCurrentUserId());
        }


		$grid_fields = array(
            'MID' => array('hidden' => true),
			'dublicate'=>array('hidden'=>true),  	
            'SID' => array('hidden' => true),
            'base_subject_id' => array('hidden' => true),
			'process' => array(
			     'title' => _('БП'), // бизнес проуцесс
			     /*'callback' => array(
			         'function' => array($this, 'updateProcess'),
			         'params' => array('{{base_subject_id}}', '{{process}}', _('Бизнес-процесс согласования заявки')),
			     ),*/
		     ),
            'bulbs' => array('title' => _('БП')),
			'period' => array('hidden' => true),
			'longtime' => array('hidden' => true),
            'fio' => array('title' => _('ФИО'), 'decorator' => $this->view->cardLink($this->view->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'gridmod' => null, 'user_id' => ''), null, true) . '{{MID}}') . '<a href="'.$this->view->url(array('module' => 'user', 'controller' => 'edit', 'action' => 'card', 'gridmod' => null,'user_id' => ''), null, true) . '{{MID}}'.'">'.'{{fio}}'.'</a>'),
            'departments' => array(
                'title' => _('Подразделение'),
                'callback' => array(
                    'function' => array($this, 'departmentsCache'),
                    'params' => array('{{departments}}', $select)
                )
            ),
            'name' => ($subjectId > 0 ? array('hidden' => true) : array(
                'title' => _('Учебный курс/сессия'),
                'callback' => array(
                    'function' => array($this, 'updateSubjectSession'),
                    'params' => array('{{base_subject1}}', '{{name}}')
                )
            )),
            'created_by' => array(
                'title' => _('Инициатор заявки'),
                'callback' => array(
                    'function' => array($this, 'updateCreatedBy'), 
                    'params' => array('{{type}}', '{{created_by}}', '{{created_fio}}')
                )
            ),
            'created_fio' => array('hidden' => true),
            'created' => array('title' => _('Дата поступления заявки')),
            'base_subject1' => array('hidden' => true),
//            'type' => array('title' => _('Источник')),
//            'stateStatus' => array('title' => _('Статус')),
            'stateCurrent' => array('title' => _('Следующий шаг')),
            'begin' => array('hidden' => true), //array('title' => _('Дата начала обучения')),
            'end' => array('hidden' => true), //array('title' => _('Дата окончания обучения')),
        );

        $basic = $this->getService('Subject')->fetchAll(array('base = ?' => HM_Subject_SubjectModel::BASETYPE_BASE));
        $baseList = $basic->getList('subid', 'name');

        $res = array_reverse($baseList, true);
        $res = $res + array(0 => _('--Нет--'));
        $resArray = array_reverse($res, true);
		$grid_filters = array(
               'fio' => true,
               'name' => null,
//               'base_subject1' => array(
//                   'values'=> $resArray,
//                   'callback'=> array('function'=> array($this,'customBaseFilter'),'params'=>array())
//               ),
               'created_by' => null,
               'created' => array('render' => 'Date'),
//                'begin' => array('render' => 'Date'),
//                'end' => array('render' => 'Date'),
               'stateCurrent' => array('values' => $this->getService('Process')->getProcessStates(HM_Process_ProcessModel::PROCESS_ORDER, false),'style'=>'width:95px;'),
               'stateStatus' => array('values' => HM_Role_ClaimantProcess::getStatuses(),'style'=>'width:95px;'),
			   'status' => array('values' => $claimantService->getStatuses(),'style'=>'width:70px;'),
           );

		if ( $processed ) {
			$grid_fields['status'] = array(
			     'title' => _('Статус'),
                 'callback' => array('function' =>create_function('$statusID',
                 '$claimantService = Zend_Registry::get(\'serviceContainer\')->getService(\'Claimant\'); return $claimantService->getStatusTitle($statusID);'),
                 'params' => array('{{status}}')));
            $grid_fields['stateCurrent'] = array('hidden' => true);
            $grid_fields['process'] = array('hidden' => true);
		}

        $grid = $this->getGrid($select, $grid_fields,$grid_filters);

        $grid->setGridSwitcher(array(
  			array('name' => 'orders_active', 'title' => _('активные заявки'), 'params' => array('processed' => 0)),
  			array('name' => 'orders_all', 'title' => _('все, включая обработанные'), 'params' => array('processed' => 1)),
        ));

        $grid->updateColumn('created', array(
           'format' => array(
               'date',
               array('date_format' => HM_Locale_Format::getDateFormat())
           )
        )
        );

        $grid->updateColumn('begin', array(
           'format' => array(
               'date',
               array('date_format' => HM_Locale_Format::getDateFormat())
            ),
            'callback' => array(
                'function' => array($this, 'updateDateBegin'),
                'params' => array('{{begin}}', '{{period}}')
           )
        )
        );

        $grid->updateColumn('end', array(
            'callback' => array(
                'function' => array($this, 'updateDateEnd'),
                'params' => array('{{end}}', '{{period}}', '{{longtime}}')
           )
        )
        );

        $grid->updateColumn('stateStatus', array(
                'callback' => array(
                    'function' => array($this, 'updateStateStatus'),
                    'params' => array('{{stateStatus}}')
                )
            )
        );

        $grid->updateColumn('stateCurrent', array(
                'callback' => array(
                    'function' => array($this, 'updateStateCurrent'),
                    'params' => array('{{stateCurrent}}')
                )
            )
        );

        $grid->updateColumn('bulbs', array(
            'callback' => array('function' => array($this, 'printWorkflow'), 'params' => array('{{base_subject_id}}', '{{SID}}'))
        ));

        /*$grid->updateColumn('fio', array("style" => "width:95px;"));
        $grid->updateColumn('reg_type', array('style' => "width:95px;"));
        $grid->updateColumn('status', array('style' => "width:95px;"));*/
//        $grid->updateColumn('type', array(
//        'callback' => array(
//        'function' => array(
//            $this,
//            'updateType'),
//        'params' => array(
//            '{{type}}'))));
        //$grid->updateColumn('actions',array('style' => 'width: 150px;'));

        // Прячем массовые действия т.к. все заявки будут обрабатываться осознанно и в соответствии с бизнесс процессом  !!!
        if (!$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_ENDUSER))) {
            $grid->addMassAction(array('action' => 'accept-by'), _('Принять заявки'));
            $grid->addMassAction(array('action' => 'reject-by'), _('Отклонить заявки'));
            $grid->addMassAction(array('module' => 'message',
            						   'controller' => 'send',
            						   'action' => 'index'),
                                 _('Отправить сообщение'));

            $grid->addAction(array('module' => 'message',
                    'controller' => 'send',
                    'action' => 'index'),
                array('MID'),
                _('Отправить сообщение'));
            
	   $grid->addAction(array(
            'module' => 'order',
            'controller' => 'union',
            'action' => 'index'
        ),
            array('MID','dublicate'),
            _('Объединить дубликаты')    
        );
        //подсвечиваем дубликаты
        $grid->setClassRowCondition("{{dublicate}}>0","dublicate");  
             $grid->setActionsCallback(
                array('function' => array($this,'updateActions'),
                      'params'   => array('{{dublicate}}')
                )
            );
        //обновляем у таблички колонку fio если там есть дубликаты
        $grid->updateColumn('fio',
            array('callback' =>
                array('function' => array($this, 'updateFiodublicate'),
                      'params'   => array('{{fio}}','{{dublicate}}')
                )
            )
        ); 		

        }
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid->deploy();
    }
    
    public function updateCreatedBy($typeID, $createdBy, $createdFio)
    {
		
		if($this->_currentLang == 'eng')
			$createdFio = $this->translit($createdFio);	
		
        if ($typeID == HM_Role_ClaimantModel::TYPE_SAP) {
            $type = HM_Role_ClaimantModel::getType($typeID); 
            return $type;
        } elseif ($createdBy) {
            $fio = $this->view->cardLink($this->view->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'gridmod' => null, 'user_id' => $createdBy), null, true)) . '<a href="'.$this->view->url(array('module' => 'user', 'controller' => 'edit', 'action' => 'card', 'gridmod' => null, 'user_id' => $createdBy), null, true) . '">' . $createdFio . '</a>';
            return $fio;
        }
        return '';
    }
    
    /**
     * метод обрабатывает данные из поля dublicate
     * елси значения в этом поле отличны от нуля 
     * выталкиваем из массива _actions последнюю
     * партию, добавленных строк - ссылка объединить
     * @param array {{dublicate}}
     * @param array $actions
     * @return array $tmp
     * @author GlazyrinAE <glazyrin.andre@mail.ru>
     */
    public function updateActions($type, $actions) 
    {      
        if ($type > 0) 
        {           
            return $actions;
        } 
        else 
        {
            $tmp = explode('<li>', $actions);
            array_pop($tmp);            
            return implode('<li>', $tmp);
        }
    }
     /**
     * метод обрабатывает данные из поля dublicate
     * елси значения в этом поле отличны от нуля 
     * добавляет пометку - дубликат
     * @param array {{fio}}
     * @param array {{dublicate}}
     * @return array $type
     * @author GlazyrinAE <glazyrin.andre@mail.ru>
     */
    public function updateFiodublicate($type1, $type2)
    {
		if($this->_currentLang == 'eng')
			$type1 = $this->translit($type1);	
		
        if($type2>0)
            return trim($type1)."</br><a style='text-decoration:none;color:red' href=''>дубликат</a>";
        else 
            return $type1;
   
    }

//    public function updateType($type)
//    {
//        $types = HM_Role_ClaimantModel::getTypes();
//        return $types[$type];
//    }

    public function printWorkflow($isBase, $claimantId)
    {
        if ($this->_claimantsCache === null) {
            $this->_claimantsCache = array();
            $collection = $this->getService('Claimant')->fetchAll();
            if (count($collection)) {
                foreach ($collection as $item) {
                    $this->_claimantsCache[$item->SID] = $item;
                }
            }
        }
        if(intval($claimantId) > 0 && count($this->_claimantsCache) && array_key_exists($claimantId,$this->_claimantsCache)){
            $model = $this->_claimantsCache[$claimantId];
            $this->getService('Process')->initProcess($model);
            return $isBase? $this->view->workflowBulbs($model) : '';
        }
        return '';
    }

    public function acceptLastAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $concreteSubject =  (int) $this->_getParam('concrete_subject', 0);

        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));
        if (is_array($ids) && count($ids)) {
            foreach($ids as $id) {
                $this->getService('Claimant')->accept($id, $concreteSubject);
            }
        }
        $this->_flashMessenger->addMessage(_('Заявки успешно приняты'));
        $this->_redirector->gotoSimple('index', 'list', 'order', array('subject_id' => $subjectId));
    }


    public function acceptAction()
    {
        $claimantId = $this->_getParam('claimant_id', 0);

        $subjectId = (int) $this->_getParam('subject_id', 0);

        $model =  $this->getService('Claimant')->find($claimantId)->current();
        $this->getService('Process')->initProcess($model);
        $result = $model->getProcess()->goToNextState();

        $this->_flashMessenger->addMessage($result);
        $this->_redirector->gotoSimple('index', 'list', 'order', array('subject_id' => $subjectId));
    }

    public function acceptByAction()
    {
        $subjectId = (int) $this->_getParam('concrete_subject', 0);
        $claimantIds = explode(',',$this->_getParam('postMassIds_grid',array()));
        $results = array();

        if (count($claimantIds)) {
            foreach ($claimantIds as $claimantId) {
                $model =  $this->getService('Claimant')->find($claimantId)->current();
                $this->getService('Process')->initProcess($model);
                $result[] = $model->getProcess()->goToNextState(); // здесь информативное сообщение!!
            }
            //$this->_flashMessenger->addMessage(implode(' ', $result));
            $this->_flashMessenger->addMessage(_('Заявки успешно приняты'));
        }
        $this->_redirector->gotoSimple('index', 'list', 'order');
    }

    public function rejectByAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $ids = explode(',', $this->_getParam('postMassIds_grid'));

        $form = new HM_Form_Comment();
        $request = $this->getRequest();

        if ($request->isPost() && $form->isValid($request->getPost())) {
            // reject
            $orders = $this->getService('Claimant')->find($ids);

            foreach($orders as $order) {
                if ($order->status == HM_Role_ClaimantModel::STATUS_REJECTED) continue;

                $this->getService('Process')->initProcess($order);
                $result = $order->getProcess()->goToFail(array('message' => (strlen($form->getValue('comments_'.$order->SID)) ? $form->getValue('comments_'.$order->SID) : $form->getValue('comments_all'))));

                //$this->getService('Claimant')->reject($order->SID, (strlen($form->getValue('comments_'.$order->SID)) ? $form->getValue('comments_'.$order->SID) : $form->getValue('comments_all')));
            }

            $this->_flashMessenger->addMessage(_('Заявки успешно отклонены'));
            $this->_redirector->gotoSimple('index', 'list', 'order', array('subject_id' => $subjectId));
        } else {
            $form->setDefault('subject_id', $subjectId);
            $form->setDefault('postMassIds_grid', $this->_getParam('postMassIds_grid', ''));
        }
        $this->view->form = $form;
    }

    public function rejectAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);

        $claimantId = $this->_getParam('claimant_id', 0);

        $ids = array($claimantId);

        $orders = $this->getService('Claimant')->findDependence(array('User', 'Subject'), $ids);
        $rejected = array();
        foreach ($orders as $order) {
            if ($order->status == HM_Role_ClaimantModel::STATUS_REJECTED) {
                $rejected[] = $order->SID;
            }
        }
        $ids = array_diff($ids, $rejected);
        if (count($ids)) {
            $form = new HM_Form_Comment();

            $form->setDefault('subject_id', $subjectId);
            $form->setDefault('postMassIds_grid', implode(',', $ids));

            $this->view->form = $form;

        } else {
            $this->_flashMessenger->addMessage(_('Заявки не выбраны'));
            $this->_redirector->gotoSimple('index', 'list', 'order', array('subject_id' => $subjectId));
        }
    }

    public function rejectLastAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $ids = explode(',', $this->_getParam('postMassIds_grid'));
        $orders = $this->getService('Claimant')->findDependence(array('User', 'Subject'), $ids);
        $rejected = array();
        foreach ($orders as $order) {
            if ($order->status == HM_Role_ClaimantModel::STATUS_REJECTED) {
                $rejected[] = $order->SID;
            }
        }
        $ids = array_diff($ids, $rejected);
        if (count($ids)) {
            $form = new HM_Form_Comment();

            $form->setDefault('subject_id', $subjectId);
            $form->setDefault('postMassIds_grid', $this->_getParam('postMassIds_grid', ''));

            $this->view->form = $form;

        } else {
            $this->_flashMessenger->addMessage(_('Заявки не выбраны'));
            $this->_redirector->gotoSimple('index', 'list', 'order', array('subject_id' => $subjectId));
        }
    }

    public function customBaseFilter($params)
    {
            $params['select']->where('c.base_subject = ?', $params['value']);

    }

    public function updateDateBegin($date, $period)
    {
        if (empty($date)) return '';
        switch ($period) {
            case HM_Subject_SubjectModel::PERIOD_FREE:
                return _('Без ограничений');
            case HM_Subject_SubjectModel::PERIOD_FIXED:
                return _('Дата регистрации на курс');
        }
        return $date;
    }

    public function updateDateEnd($date, $period, $longtime)
    {
        if (empty($date)) return '';
        switch ($period) {
            case HM_Subject_SubjectModel::PERIOD_FREE:
                return _('Без ограничений');
            case HM_Subject_SubjectModel::PERIOD_FIXED:
                return sprintf(_('Через %s дней'), $longtime);
        }
        return $this->getDateForGrid($date);
    }



    public function workflowAction()
    {
        $claimantId = $this->_getParam('index', 0);

        if(intval($claimantId) > 0){

            $model =  $this->getService('Claimant')->find($claimantId)->current();
            $this->getService('Process')->initProcess($model);
            $this->view->model = $model;
        }
    }


    public function updateStateStatus($status)
    {
        $statuses = HM_Role_ClaimantProcess::getStatuses();
        return $statuses[$status];
    }

    public function updateStateCurrent($state)
    {
        $types = $this->getService('Process')->getProcessStates(HM_Process_ProcessModel::PROCESS_ORDER);
        return $types[$state];
    }

    public function updateSubjectSession($subjectName, $sessionName)
    {
        $return = array();
        if ($subjectName) $return[] = $subjectName;
        if ($sessionName) $return[] = $sessionName;
        return implode(' / ', $return);
    }

    public function updateProcess($isBase, $id, $processTitle = '')
    {
        return '<img src="/images/icons/workflow.png" data-workflow_id="' . $id . '" class="grid-workflow" title="' . $processTitle . '"/>';
    }
	
	//Транслит с русского на английски1
	public function translit($str='') {
		
		$cyrForTranslit = array('а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п',
						   'р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',
						   'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П',
						   'Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я'); 
		$latForTranslit = array('a','b','v','g','d','e','yo','zh','z','i','y','k','l','m','n','o','p',
							'r','s','t','u','f','h','ts','ch','sh','sch','','y','','ae','yu','ya',
							'A','B','V','G','D','E','Yo','Zh','Z','I','Y','K','L','M','N','O','P',
							'R','S','T','U','F','H','Ts','Ch','Sh','Sch','','Y','','Ae','Yu','Ya'); 
							
		return str_replace($cyrForTranslit, $latForTranslit, $str);
	} 
	
	
}