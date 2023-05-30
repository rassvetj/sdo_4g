<?php
class Report_ListController extends HM_Controller_Action_Crud
{
    private $_reportConfig = null;

    public function indexAction()
    {
        $this->_reportConfig = new HM_Report_Config();

        $order = $this->_request->getParam("ordergrid");
        if ($order == ""){
            $this->_request->setParam("ordergrid", $order = 'name_ASC');
        }

        $select = $this->getService('Report')->getSelect();
        $select->from(array('r' => 'reports'), array())
            ->joinLeft(array('rr' => 'reports_roles'), 'r.report_id = rr.report_id', array(
                'report_id' => 'r.report_id', 
                'name' => 'r.name', 
                'domain' => 'r.domain', 
                'input' => 'r.name', 
                //'status' => 'r.status', 
                'role' => new Zend_Db_Expr('GROUP_CONCAT(rr.role)'),
            ))
            ->group(array('r.report_id', 'r.name', 'r.domain', 'r.status'));
        $grid = $this->getGrid($select,
            array(
                'report_id' => array('hidden' => true),
                'name' => array(
                    'title' => _('Название'),
                    'decorator' => ' <a href="' . $this->view->url(array('module' => 'report', 'controller' => 'generator', 'action' => 'construct', 'report_id' => '{{report_id}}'), null, true, false) . '">{{name}}</a>'
                ),
                'domain' => array(
                    'title' => _('Область данных'),
                    'callback' => array('function' => array($this, 'updateDomainField'), 'params' => array('{{domain}}'))
                ),
                'input' => array(
                    'title' => _('Входные параметры'),
                    'callback' => array('function' => array($this, 'updateInputField'), 'params' => array('{{input}}'))
                ),
                'role' => array(
                    'title' => _('Роли'),
                    'callback' => array('function' => array($this, 'updateRole'), 'params' => array('{{role}}'))
                ),
                /*
                'status' => array(
                    'title' => _('Статус'),
                    'callback' => array('function' => array($this, 'updateStatusField'), 'params' => array('{{status}}'))
                ),
                */
            ),
            array(
                'name' => null,
                'domain' => array('values' => $this->_reportConfig->getDomains())
            )
        );

        $grid->addAction(array(
            'module' => 'report',
            'controller' => 'list',
            'action' => 'edit'
        ),
            array('report_id'),
            $this->view->icon('edit')
        );

        $grid->addAction(array(
            'module' => 'report',
            'controller' => 'list',
            'action' => 'delete'
        ),
            array('report_id'),
            $this->view->icon('delete')
        );

        $grid->addMassAction(
            array('module' => 'report', 'controller' => 'list', 'action' => 'delete-by'),
            _('Удалить'),
            _('Вы уверены?')
        );

        $grid->addMassAction(array('action' => 'assign'), _('Открыть для роли'));
        $grid->addMassAction(array('action' => 'unassign'), _('Закрыть для роли'));
        
        $roles = HM_Role_RoleModelAbstract::getBasicRoles(false);
        unset($roles[HM_Role_RoleModelAbstract::ROLE_USER]);
        $roles = array_merge( array("ISNULL" => _('Пользователь')), $roles);
        unset($roles[HM_Role_RoleModelAbstract::ROLE_USER]);
        //unset($roles[HM_Role_RoleModelAbstract::ROLE_STUDENT]);
        unset($roles['ISNULL']);
        
        $grid->addSubMassActionSelect(array($this->view->url(array('action' => 'assign'))),
            					     'role[]',
                                      $roles);
        $grid->addSubMassActionSelect(array($this->view->url(array('action' => 'unassign'))),
            					     'role[]',
                                      $roles);

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid->deploy();
    }

    public function editAction()
    {
        $reportId = $this->_getParam('report_id', 0);
        $form = new HM_Form_Domain();

        if ($this->_request->isPost()) {

            if ($form->isValid($this->_request->getPost())) {

                $name = $this->_getParam('name', '');
                $domain = $this->_getParam('domain', '');
                $status = $this->_getParam('status', 0);

                if ($reportId) {
                    $report = $this->getService('Report')->update(array('report_id' => $reportId, 'name' => $name, 'domain' => $domain, 'status' => $status));
                    $this->getService('ReportRole')->removalAllRoles($reportId);
                    $this->getService('ReportRole')->assignRole($reportId, $this->_getParam('roles', array()));
                    $this->_redirector->gotoSimple('index', 'list', 'report');
                } else {
                    $report = $this->getService('Report')->insert(array('name' => $name, 'domain' => $domain, 'status' => $status));
                    $this->getService('ReportRole')->assignRole($report->report_id, $this->_getParam('roles', array()));
                    $this->_redirector->gotoSimple('construct', 'generator', 'report', array('report_id' => $report->report_id));
                }
            }

        } elseif ($reportId) {
            if ($report = $this->getService('Report')->getOne($this->getService('Report')->find($reportId))) {
                $roles = $this->getService('ReportRole')->fetchAll($this->getService('ReportRole')->quoteInto('report_id = ?', $reportId))->getList('role');
                $form->setDefaults(array(
                    'name' => $report->name,
                    'domain' => $report->domain,
                    'status' => $report->status,
                    'roles' => $roles
                ));
            }
        }

        $this->view->form = $form;

    }

    public function updateDomainField($domain)
    {
        $domains = $this->_reportConfig->getDomains();
        return $domains[$domain];
    }

    public function updateStatusField($status)
    {
        return $status ? _('Опубликован') : '<span class="nowrap">' . _('Не опубликован') . '</span>';
    }

    public function updateInputField($input)
    {
        return '';
    }

    public function deleteAction()
    {
        $reportId = (int) $this->_getParam('report_id', 0);
        $this->delete($reportId);
        $this->_flashMessenger->addMessage(_('Отчёт успешно удалён'));
        $this->_redirector->gotoSimple('index', 'list', 'report', array());
    }

    public function delete($reportId)
    {
        $this->getService('Report')->delete($reportId);
        return true;
    }

    public function treeAction()
    {

        $this->view->gridAjaxRequest = $this->isAjaxRequest();
        $this->view->tree = $this->getService('Report')->getTreeContent(new HM_Report_Config());
    }
    
    public function updateRole($field, $separator = ',') {
        if ($field == '') return _('Нет');
        $roles = HM_Role_RoleModelAbstract::getBasicRoles();
        $str = str_replace(array_keys($roles), array_values($roles), $field);

        $fields = explode(',',$str);
        $result = (is_array($fields) && (count($fields) > 1)) ? array('<p class="total">' . Zend_Registry::get('serviceContainer')->getService('User')->pluralFormRolesCount(count($fields)) . '</p>') : array();
        foreach($fields as $value){
            $result[] = "<p>{$value}</p>";
        }
        if($result)
            return implode('',$result);
        else
            return _('Нет');

    }

    public function unassignAction()
    {
        $arRoles = HM_Role_RoleModelAbstract::getBasicRoles();
        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));
        $roles = $this->_request->getParam('role',array());
        $service = $this->getService('ReportRole');

        foreach ($ids as $report_id) {
            foreach ( $roles as $role) {
                if ( array_key_exists($role, $arRoles)) {
                    $service->removalRole($report_id, $role);
                }
            }
        }

        $this->_flashMessenger->addMessage(_('Отчетные формы закрыты для данных ролей'));
        $this->_redirector->gotoSimple('index', 'list', 'report');
    }


    public function assignAction() {

        $roles = HM_Role_RoleModelAbstract::getBasicRoles();
        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));
        $role = $this->_request->getParam('role');
        $service = $this->getService('ReportRole');
        // Флаг, есть ли ошибки
        $error = false;
        foreach ($ids as $report_id) {
            $res = $service->assignRole($report_id, $role);
            if ($res === false) {
                $error = true;
            }
        }
        if ($error === true) {
            $this->_flashMessenger->addMessage(_('Некоторым отчетные формы уже были открыты для данных ролей'));
        } else {
            $this->_flashMessenger->addMessage(_('Отчетные формы успешно открыты для данных ролей'));
        }
        $this->_redirector->gotoSimple('index', 'list', 'report');

    }
    
    public function setDefaults(Zend_Form $form)
    {
        /*$faqId = (int) $this->_getParam('faq_id', 0);

        $faq = $this->getOne($this->getService('Faq')->find($faqId));
        if ($faq) {
            $values = $faq->getValues();
            if (strlen($faq->roles)) {*/

        //}
    }
    
}