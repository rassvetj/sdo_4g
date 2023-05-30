<?php
class Assign_AdminController extends HM_Controller_Action_Assign
{

    protected $service     = 'Subject';
    protected $idParamName = 'subject_id';
    protected $idFieldName = 'subid';
    protected $id          = 0;



    protected $_responsobilities = null;

    //protected $_fixedRow = false;

    protected $_assignOptions = array(
        'role'                  => 'Admin',
        'courseStatuses'        => array(2),
        'table'                 => 'admins',
        'tablePersonField'      => 'MID',
        'tableCourseField'      => 'subject_id',
        'courseTable'           => 'subjects',
        'courseTablePrimaryKey' => 'subid',
        'courseTableTitleField' => 'name',
        'courseIdParamName'     => 'subject_id'
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

    	$notAll = !$this->_getParam('all', isset($default->grid['assign-admin-index'][$gridId]['all']) ? $default->grid['assign-admin-index'][$gridId]['all'] : null);

        $sorting = $this->_request->getParam("order{$gridId}");
        if ($sorting == ""){
            $this->_request->setParam("order{$gridId}", 'fio_ASC');
        }

        if ($this->_request->getParam("assigned{$gridId}",false)) {
            $this->_request->setParam("assigned{$gridId}",urldecode(urldecode($this->_request->getParam("assigned{$gridId}"))));
        }
        $select = $this->getService('User')->getSelect();

        if ($notAll) {

                $this->_request->setParam('masterOrdergrid', 'notempty DESC');

                $select->from(
                            array('t1' => 'People'),
                            array(
                                'MID',
                                'notempty' => "CASE WHEN (t1.LastName IS NULL AND t1.FirstName IS NULL AND  t1.Patronymic IS NULL) OR (t1.LastName = '' AND t1.FirstName = '' AND t1.Patronymic = '') THEN 0 ELSE 1 END",
                                'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(t1.LastName, ' ') , t1.FirstName), ' '), t1.Patronymic)"),
                            	'login' => 't1.Login',
                                'status' => 't1.blocked'
                            )
                        )
                        ->joinInner(array('a' => 'admins'),
                            'a.MID = t1.MID',
                            array(
                            )
                        )
                        ->group(array('t1.MID', 't1.LastName', 't1.FirstName', 't1.Patronymic', 't1.blocked', 't1.Login'));
        } else {

            $select->from(
                array('t1' => 'People'),
                array(
                    'MID',
                    'notempty' => "CASE WHEN (t1.LastName IS NULL AND t1.FirstName IS NULL AND  t1.Patronymic IS NULL) OR (t1.LastName = '' AND t1.FirstName = '' AND t1.Patronymic = '') THEN 0 ELSE 1 END",
                    'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(t1.LastName, ' ') , t1.FirstName), ' '), t1.Patronymic)"),
                    'login' => 't1.Login',
                    'status' => 't1.blocked'
                )
            )
            ->joinLeft(array('a' => 'admins'),
                'a.MID = t1.MID',
                array('assigned' => 'a.MID')
            )
            ->group(array('t1.MID', 't1.LastName', 't1.FirstName', 't1.Patronymic', 't1.blocked', 't1.Login', 'a.MID'));
        }

            $grid = $this->getGrid(
                $select,
                array(
                    'MID' => array('hidden' => true),
                    'notempty' => array('hidden' => true),
                   // 'employer' => array('title' => _('Место работы')),
                    'login' => array('title' => _('Логин')),
                 	'assigned' => array('title' => _('Назначен?')),
                    //'role' => array('title' => _('Роли')),
                    'status' => array('title' => _('Статус')),
                   // 'responsobility' => array('title' => _('Области ответственности')),
                    'fio' => array('title' => _('ФИО'), 'decorator' => $this->view->cardLink($this->view->url(array('module' => 'user', 'controller' => 'list','action' => 'view', 'gridmod' => null,'user_id' => '')).'{{MID}}',_('Карточка пользователя')).'<a href="'.$this->view->url(array('module' => 'user', 'controller' => 'edit', 'action' => 'card', 'gridmod' => null,'user_id' => '')) . '{{MID}}'.'">'.'{{fio}}</a>'),
                ),
                array(
                    'fio' => null,
                    'login' => null,
                    'assigned' => array(
                        'values' => array(
                            '*' => _('Да'),
                            'ISNULL'    => _('Нет')
                        )
                    ),
                    'status' => array(
                        'values' => array(
                            '0' => _('Активный'),
                            '1' => _('Заблокирован')
                        )
                    )
                )
            );

        $grid->setGridSwitcher(array(
  			array('name' => 'all_admins', 'title' => _('всех администраторов'), 'params' => array('all' => 0), 'order' => 'fio'),
  			array('name' => 'all_users', 'title' => _('всех пользователей'), 'params' => array('all' => 1), 'order' => 'assigned', 'order_dir' => 'DESC'),
        ));

        $grid->updateColumn('fio',
            array('callback' =>
                array('function' => array($this, 'updateFio'),
                      'params'   => array('{{fio}}', '{{MID}}')
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

        $grid->updateColumn('status',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateStatus'),
                    'params' => array('{{status}}')
                )
            )
        );

        if(!$notAll) $grid->setClassRowCondition("'{{assigned}}' != ''", "selected");

        if ($this->id) $grid->setClassRowCondition("'{{course}}' != ''", "selected");

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
                        array(''),
                        _('Отправить сообщение'));
        $grid->addMassAction(array('module' => 'message', 
        						   'controller' => 'send', 
        						   'action' => 'index'),
                             _('Отправить сообщение'));
        
        $grid->setHeadCheckbox('all', _('Отображать пользователей только данной роли'), 1);

        if($this->_fixedRow==true){
            $grid->addFixedRows($this->_getParam('module'), $this->_getParam('controller'),$this->_getParam('action'), $this->_fixedRowsPrimary);
            $grid->updateColumn('fixType', array('hidden' => true));
        }

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid->deploy();



    }


    public function doAction()
    {
        //$subjectId = (int) $this->_getParam($this->_assignOptions['courseIdParamName'], 0);

        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                $errors=false;
                foreach($ids as $id) {
                    if (method_exists($this, '_preAssign')) {
                        $this->_preAssign($id, $courseId);
                    }


                    $fetch = $this->getService($this->_assignOptions['role'])->fetchAll(array('MID = ?' => $id));
                    try{
                        if(count($fetch) == 0){
                            $this->getService($this->_assignOptions['role'])->insert(
                                array(
                                    $this->_assignOptions['tablePersonField'] => $id
                                )
                            );
                        }
                    }catch (Zend_Db_Exception  $e){
                        $errors=true;
                    }


                    if (method_exists($this, '_postAssign')) {
                        $this->_postAssign($id, $courseId);
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

        if (method_exists($this, '_finishAssign')) {
            $this->_finishAssign();
        }

        $this->_redirector->gotoSimple('index', null, null, array($this->_assignOptions['courseIdParamName'] => $subjectId));
    }

    public function unassignAction()
    {
        //$subjectId = 0;

        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach($ids as $id) {
                    if($this->getService('User')->getCurrentUserId() == $id){
                        $this->_flashMessenger->addMessage(_('Вы не можете удалить себя'));
                        continue;
                    }
                    if (method_exists($this, '_preUnassign')) {
                        $this->_preUnassign($id, $courseId);
                    }
                    $this->getService($this->_assignOptions['role'])->deleteBy(
                    sprintf("%s = %d", $this->_assignOptions['tablePersonField'], $id)
                    );
                    if (method_exists($this, '_postUnassign')) {
                        $this->_postUnassign($id, $courseId);
                    }
                }
                $this->_flashMessenger->addMessage(_('Назначения успешно удалены'));
            }
        } else {
            $this->_flashMessenger->addMessage(_('Пожалуйста выберите пользователей и укажите курс'));
        }

        if (method_exists($this, '_finishUnassign')) {
            $this->_finishUnassign();
        }
        $this->_redirector->gotoSimple('index', null, null, array($this->_assignOptions['courseIdParamName'] => $subjectId));
    }





    protected function _postAssign($id, $subjectId)
    {

    }

    public function updateDate($date){

        if($date == ""){
            return _('Нет');
        }else{
            $date = new Zend_Date($date);

            if($date instanceof Zend_Date){
                return $date->toString(HM_Locale_Format::getDateFormat());
            }else{
                return _('Нет');
            }

        }


    }


    public function updateFio($fio, $userId)
    {
        $fio = trim($fio);
        if (!strlen($fio)) {
            $fio = sprintf(_('Пользователь #%d'), $userId);
        }
        return $fio;
    }

    public function updateStatus($field) {
        if ($field == 0) {
            return _('Активный');
        } else {
            return _('Заблокирован');
        }
    }

    public function updateAssigned($assigned){

        if($assigned != ""){
            return _('Да');
        }else{
            return _('Нет');
        }


    }



}