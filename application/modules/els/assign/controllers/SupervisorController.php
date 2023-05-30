<?php
class Assign_SupervisorController extends HM_Controller_Action_Assign
{

    protected $service     = 'Subject';
    protected $idParamName = 'subject_id';
    protected $idFieldName = 'subid';
    protected $id          = 0;



    protected $_responsobilities = null;

    //protected $_fixedRow = false;

    protected $_assignOptions = array(
        'role'                  => 'Supevisor',
        'courseStatuses'        => array(2),
        'table'                 => 'supervisors',
        'tablePersonField'      => 'user_id',

    );

    public function init()
    {
        parent::init();

        if (!$this->isAjaxRequest()) {
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

    public function indexAction()
    {
        $gridId = 'grid';
        $default = new Zend_Session_Namespace('default');

        $notAll = !$this->_getParam('all', isset($default->grid['assign-supervisor-index'][$gridId]['all']) ? $default->grid['assign-supervisor-index'][$gridId]['all'] : null);

        $sorting = $this->_request->getParam("order{$gridId}");
        if ($sorting == ""){
            $this->_request->setParam("order{$gridId}", 'fio_ASC');
        }

        $select = $this->getService('User')->getSelect();

        if ($notAll) {
            $this->_request->setParam("masterOrdergrid", 'notempty DESC');
            $select->from(
                array('t1' => 'People'),
                array(
                    'MID',
                    'notempty' => "CASE WHEN (t1.LastName IS NULL AND t1.FirstName IS NULL AND  t1.Patronymic IS NULL) OR (t1.LastName = '' AND t1.FirstName = '' AND t1.Patronymic = '') THEN 0 ELSE 1 END",
                    'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(t1.LastName, ' ') , t1.FirstName), ' '), t1.Patronymic)"),
                    'login'                 => 't1.Login',
                    'status'                => 't1.blocked',
                    'responsibility_type'   => 'sr.responsibility_type',
                    'responsibility_type_id'   => 'sr.responsibility_type',
                    'responsibilities'      => new Zend_Db_Expr('GROUP_CONCAT(sr.responsibility_id)'),
                )
            )
                ->joinInner(
                    array('t2' => $this->_assignOptions['table']),
                    't1.MID = t2.'.$this->_assignOptions['tablePersonField'],
                    array()
                )

                ->joinLeft(
                    array('sr' => 'supervisors_responsibilities'),
                    't1.MID = sr.user_id',
                    array()
                )
                ->group(array('t1.MID', 't1.LastName', 't1.FirstName', 't1.Patronymic', 't1.blocked', 't1.Login', 'sr.responsibility_type'));
        } else {

            $select->from(
                array('t1' => 'People'),
                array(
                    'MID',
                    'notempty' => "CASE WHEN (t1.LastName IS NULL AND t1.FirstName IS NULL AND  t1.Patronymic IS NULL) OR (t1.LastName = '' AND t1.FirstName = '' AND t1.Patronymic = '') THEN 0 ELSE 1 END",
                    'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(t1.LastName, ' ') , t1.FirstName), ' '), t1.Patronymic)"),
                    'login' => 't1.Login',
                    'status' => 't1.blocked',
                )
            )->joinLeft(
                    array('t2' => $this->_assignOptions['table']),
                    't1.MID = t2.'.$this->_assignOptions['tablePersonField'],
                    array(
                        'assigned' => 't2.'.$this->_assignOptions['tablePersonField']
                    )
                )
                ->group(array('t1.MID', 't1.LastName', 't1.FirstName', 't1.Patronymic'/*, 'role'*/, 't1.blocked', 't1.Login', 't2.'.$this->_assignOptions['tablePersonField']));
        }

        $grid = $this->getGrid(
            $select,
            array(
                'MID' => array('hidden' => true),
                'notempty' => array('hidden' => true),
                'responsibility_type_id' => array('hidden' => true),
                'fio' => array('title' => _('ФИО'), 'decorator' => $this->view->cardLink($this->view->url(array('module' => 'user', 'controller' => 'list','action' => 'view', 'gridmod' => null,'user_id' => '')).'{{MID}}',_('Карточка пользователя')).'<a href="'.$this->view->url(array('module' => 'user', 'controller' => 'edit', 'action' => 'card' ,'role' => 'supervisor', 'user_id' => '')) . '{{MID}}'.'">'.'{{fio}}</a>'),
                'login' => array('title' => _('Логин')),
                'status' => array('title' => _('Статус')),
                'assigned' => array('title' => _('Назначен?')),
                'responsibility_type' => array('title' => _('Тип области ответственности ')),
                'responsibilities' => array('title' => _('Области ответственности')),
            ),
            array(
                'fio' => null,
                'login' => null,
                'status' => array(
                    'values' => array(
                        '0' => _('Активный'),
                        '1' => _('Заблокирован')
                    )
                )

            )
        );

        $grid->setGridSwitcher(array(
            array('name' => 'all_deans', 'title' => _('всех наблюдателей'), 'params' => array('all' => 0), 'order' => 'fio'),
            array('name' => 'all_users', 'title' => _('всех пользователей'), 'params' => array('all' => 1), 'order' => 'assigned', 'order_dir' => 'DESC'),
        ));

        $grid->updateColumn('fio',
            array('callback' =>
            array('function' => array($this, 'updateFio'),
                'params'   => array('{{fio}}', '{{MID}}')
            )
            )
        );

        $grid->updateColumn('status',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateStatus'),
                    'params' => array('{{status}}')
                )
            )
        );

        $grid->updateColumn('assigned',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateAssigned'),
                    'params' => array('{{assigned}}')
                )
            )
        );

        $grid->updateColumn('responsibility_type',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateResponsibilityType'),
                    'params' => array('{{responsibility_type}}')
                )
            )
        );

        $grid->updateColumn('responsibilities',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateResponsibilities'),
                    'params' => array('{{responsibility_type_id}}', '{{responsibilities}}')
                )
            )
        );

        /*
        $grid->updateColumn('responsobility',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateResponsibility'),
                    'params' => array('{{responsobility}}', '{{MID}}',$select, '{{assigned}}')
                )
            )
        );

        $grid->updateColumn('orgStruct',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateResponsibilityOrg'),
                    'params' => array('{{orgStruct}}', '{{MID}}')
                )
            )
        );
        */

        if(!$notAll) $grid->setClassRowCondition("'{{assigned}}' != ''", "selected");

        $url = array('action' => 'do');
        $grid->addMassAction(
            $url,
            _('Назначить роль'),
            _('Вы уверены?')
        );

        $url = array('action' => 'unassign');
        $grid->addMassAction(
            $url,
            _('Удалить роль'),
            _('Вы уверены?')
        );

        $grid->addAction(array('module' => 'message',
                'controller' => 'send',
                'action' => 'index'),
            array('MID'),
            _('Отправить сообщение'));

        $grid->addMassAction(array('module' => 'message',
                'controller' => 'send',
                'action' => 'index'),
            _('Отправить сообщение'));


        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid->deploy();
    }

    public function updateFio($fio, $userId)
    {
        $fio = trim($fio);
        if (!strlen($fio)) {
            $fio = sprintf(_('Пользователь #%d'), $userId);
        }
        return $fio;
    }

    public function updateStatus($field)
    {
        if ($field == 0) {
            return _('Активный');
        } else {
            return _('Заблокирован');
        }
    }

    public function updateResponsibilityType($type)
    {
        switch($type) {
            case HM_Role_Supervisor_Responsibility_ResponsibilityModel::SUBJECT_RESPONSIBILITY_TYPE:
                return _('по курсам');
                break;
            case HM_Role_Supervisor_Responsibility_ResponsibilityModel::GROUP_RESPONSIBILITY_TYPE:
                return _('по учебным группам');
                break;
            case HM_Role_Supervisor_Responsibility_ResponsibilityModel::PROGRAMM_RESPONSIBILITY_TYPE:
                return _('по учебным программам');
                break;
            case HM_Role_Supervisor_Responsibility_ResponsibilityModel::STUDENT_RESPONSIBILITY_TYPE:
                return _('по студентам');
                break;
            default:
                return _('не назначена');
                break;
        }

    }

    public function updateResponsibilities($type, $responsibilities)
    {
        $responsibilities = explode(',', $responsibilities);
        $resultNames = array();

        if($responsibilities) {
            switch($type) {
                case HM_Role_Supervisor_Responsibility_ResponsibilityModel::SUBJECT_RESPONSIBILITY_TYPE:
                    $resultNames = $this->getService('Subject')
                        ->fetchAll(array(
                            'subid IN (?)' => $responsibilities
                        ))
                        ->getList('subid', 'name');

                    break;
                case HM_Role_Supervisor_Responsibility_ResponsibilityModel::GROUP_RESPONSIBILITY_TYPE:
                    $resultNames = $this->getService('StudyGroup')
                        ->fetchAll(array(
                            'group_id IN (?)' => $responsibilities
                        ))
                        ->getList('group_id', 'name');
                    break;
                case HM_Role_Supervisor_Responsibility_ResponsibilityModel::PROGRAMM_RESPONSIBILITY_TYPE:
                    $resultNames = $this->getService('Programm')
                        ->fetchAll(array(
                            'programm_id IN (?)' => $responsibilities
                        ))
                        ->getList('programm_id', 'name');
                    break;
                case HM_Role_Supervisor_Responsibility_ResponsibilityModel::STUDENT_RESPONSIBILITY_TYPE:
                    $resultNames = array();
                    $studentObjects = $this->getService('User')
                        ->fetchAll(array(
                            'MID IN (?)' => $responsibilities
                        ));
                    foreach($studentObjects as $studentObject) {
                        $resultNames[] = $studentObject->getName();
                    }
                    break;
            }

            //$fields = explode(',',$resultNames);
            $result = (is_array($resultNames) && (count($resultNames) > 1)) ? array('<p class="total">' . count($resultNames)  . ' области ответственности</p>') : array();
            foreach($resultNames as $resultName){
                $result[] = "<p>{$resultName}</p>";
            }
            if($result)
                return implode('',$result);
            else
                return _('Нет');
        }
    }

    public function doAction()
    {
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                $errors=false;
                foreach($ids as $id) {

                    $fetch = $this->getService('Supervisor')->fetchAll(array('user_id = ?' => $id));
                    try{
                        if(count($fetch) == 0){
                            $this->getService('Supervisor')->insert(
                                array(
                                    $this->_assignOptions['tablePersonField'] => $id,
                                )
                            );
                        }
                    }catch (Zend_Db_Exception  $e){
                        $errors=true;
                    }
                }

                if($errors==false){
                    $this->_flashMessenger->addMessage(_('Пользователи успешно назначены'));
                }else{
                    $this->_flashMessenger->addMessage(_('В ходе назначения пользователей возникли несущественные ошибки.'));
                }

            }
        } else {
            $this->_flashMessenger->addMessage(_('Пожалуйста выберите пользователей и укажите курс'));
        }

        $this->_redirector->gotoSimple('index', 'supervisor', 'assign');
    }

    public function unassignAction()
    {
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach($ids as $id) {
                    $this->getService('Supervisor')->deleteBy(
                        $this->quoteInto(array('user_id = ?'), array($id))
                    );

                    $this->getService('SupervisorResponsibility')->deleteBy(
                        $this->quoteInto(array('user_id = ?'), array($id))
                    );
                }
                $this->_flashMessenger->addMessage(_('Назначения успешно удалены'));
            }
        } else {
            $this->_flashMessenger->addMessage(_('Пожалуйста выберите пользователей и укажите курс'));
        }

        $this->_redirector->gotoSimple('index', 'supervisor', 'assign');
    }


    public function updateAssigned($assigned)
    {
        if($assigned != ""){
            return _('Да');
        }else{
            return _('Нет');
        }
    }
}