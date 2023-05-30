<?php
class Orgstructure_ListController extends HM_Controller_Action_Crud
{
    public function init() {


        if ($this->_request->getParam('item', 'department') == 'position') {
            $form = new HM_Form_Position();
        }else{
            $form = new HM_Form_Department();
        }

        $orgId = (int) $this->_getParam('org_id', 0);
        if ($orgId) {
            $item = $this->getOne($this->getService('Orgstructure')->find($orgId));
            if ($item && $item->type != HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT) {
                $form = new HM_Form_Position();
            }
        }

        $this->_setForm($form);
        parent::init();
    }

    public function indexAction()
    {
        $defaultParent = $this->getService('Orgstructure')->getDefaultParent();
        $orgId = (int) $this->_getParam('key', $defaultParent->soid);

        $page = sprintf('%s-%s-%s', $this->getRequest()->getModuleName(), $this->getRequest()->getControllerName(), $this->getRequest()->getActionName());
        $default = Zend_Registry::get('session_namespace_default');
        $all = $default->grid[$page]['grid']['all'];

        $this->view->treeajax= $this->_getParam('treeajax', 'none');

        if($this->_getParam('all') != ""){
            $all = $this->_getParam('all', 0);
        }
        if(is_array($this->_getParam('all'))){
            $all = array_pop($this->_getParam('all'));
            $this->_setParam('all', $all);
        }

        $this->view->all = $all;


        $this->_setParam('all', $all);
        //pr($all);

        $select = $this->getService('Orgstructure')->getSelect();

        $select->from(array('so' => 'structure_of_organ'), array(
            'so.soid',
            'org_id' => 'so.soid',
            'so.name',
            'so.type',
            'so.is_manager',
            'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
            'mid' => 'so.mid'
        ));

        $select->joinLeft(array('p' => 'People'), 'so.mid = p.MID', array());
        $select->joinLeft(array('s' => 'Students'), 's.MID = p.MID', array('courses' => new Zend_Db_Expr('GROUP_CONCAT(s.CID)')));

        $select->joinLeft(
            array('cl' => 'classifiers_links'),
            $this->getService('ClassifierLink')->quoteInto('cl.item_id = so.soid AND cl.type = ?', HM_Classifier_Link_LinkModel::TYPE_STRUCTURE),
            array()//array('classifiers' => new Zend_Db_Expr('GROUP_CONCAT(c.classifier_id)'))
        );

        $select->joinLeft(
            array('c' => 'classifiers'),
            'c.classifier_id = cl.classifier_id',
            array('classifiers' => new Zend_Db_Expr('GROUP_CONCAT(c.name)'))
        );
        //$select->where('p.blocked = 0 OR p.blocked IS NULL'); //--разкомментировать!! Закомменчено для проверки. Т.к. все студенты заблокированы

        //Тут добавляем $all


        if($orgId > 0){
            $orgElement = $this->getService('Orgstructure')->find($orgId)->current();


            if($all == 1){
                $select->where('so.lft > ?', $orgElement->lft);
                $select->where('so.rgt < ?', $orgElement->rgt);

            }else{
                $select->where('so.lft > ?', $orgElement->lft);
                $select->where('so.rgt < ?', $orgElement->rgt);
                $select->where('so.level = ?', $orgElement->level + 1);
            }
        }else{
            if($all != 1){
                $select->where('so.level = ?', 0);
            }
        }

        $select->where('so.blocked = ?', 0);
        $select->group(array('so.soid', 'so.name', 'so.type', 'so.mid', 'is_manager', 'p.LastName', 'p.FirstName', 'p.Patronymic'));
        $select->order('type');

        $grid = $this->getGrid($select,
            array(
                'soid' => array('hidden' => true),
                'org_id' => array('hidden' => true),
                'mid' => array('hidden' => true),
                'is_manager' => array('hidden' => true),
                'name' => array(
                    'title' => _('Название'),
                    'callback' => array('function' => array($this, 'updateName'), 'params' => array('{{name}}', '{{org_id}}', '{{type}}', '{{is_manager}}'))
                ),
                'type' => array('hidden' => true),
//                array(
//                    'title' => _('Тип'),
//                    'callback' => array('function' => array($this, 'updateType'), 'params' => array('{{type}}', '{{at_vacancy_id}}'))
//                ),
                'fio' => array(
                    'title' => _('Назначен')
                ),
                'classifiers' => array(
                    'title' => _('Классификация')
                ),
                'courses' => array(
                    'title' => _('Курсы'),
                    'callback' => array(
                        'function' => array($this, 'coursesCache'),
                        'params' => array('{{courses}}', $select)
                    )
                )
            ),
            array(
                'name' => null,
                'type' => array('values' => HM_Orgstructure_OrgstructureModel::getTypes()),
                'fio' => null,
                'classifiers' => null,
                'courses' => null,
            )
        );

        $grid->updateColumn('classifiers',
            array('callback' =>
                array('function' => array($this, 'updateClassifiers'),
                      'params'   => array('{{classifiers}}', $select)
                )
            )
        );

        $grid->updateColumn('fio',
            array('callback' =>
                array('function' => array($this, 'updateFio'),
                      'params'   => array('{{fio}}', '{{mid}}')
                )
            )
        );

        $grid->addAction(array(
            'module' => 'orgstructure',
            'controller' => 'list',
            'action' => 'classifier'
        ),

		// ВНИМАНИЕ! при мерже с Вымпелкомом эти правки не применять (должно быть "изменить функции")
            array('org_id'),
            _('Классифицировать')
            //$this->view->icon('edit')
        );

        $grid->addAction(array(
            'module' => 'orgstructure',
            'controller' => 'list',
            'action' => 'edit'
        ),
            array('org_id'),
            $this->view->icon('edit')
        );

        $grid->addAction(array(
            'module' => 'orgstructure',
            'controller' => 'list',
            'action' => 'delete'
        ),
            array('org_id'),
            $this->view->icon('delete')
        );

//#15141        $grid->addMassAction(array('controller' => 'list','module' => 'orgstructure' ,'action' => 'move', 'parent' => $orgId), _('Переместить'), _('Вы уверены?'));
        $grid->addMassAction(array('controller' => 'list','module' => 'orgstructure' , 'action' => 'delete-by', 'parent' => $orgId), _('Удалить'), _('Вы уверены?'));

        // в 4.5 нет индивидуального назначения прорамм, только через учебные группы
        // в будущем планируется назначение программ через профиль должности
        /*$grid->addMassAction(array(
                'controller' => 'list',
                'module' => 'orgstructure' ,
                'action' => 'assign-programm',
                'parent' => $orgId
            ),
            _('Назначить программы обучения'),
            _('Вы уверены?')
        );*/

        $programms = $this->getService('Programm')->fetchAll(array('programm_type is null or programm_type != ?' => HM_Programm_ProgrammModel::TYPE_ASSESSMENT), 'programm.name ASC'); // асесьмент назначается через профили должностей 

        $programms = $programms->getList('programm_id', 'name');

        $grid->addSubMassActionSelect(array(
                $this->view->url(
                    array('action' => 'assign-programm', 'parent' => $orgId)
                )
            ),
            'programm_id',
            $programms
        );

        $userId = $this->getService('User')->getCurrentUserId();
        //для назначения на курсы должны отображать список активных курсов, для удаления - список всех курсов
        #$collection = $this->getService('Dean')->getActiveSubjectsResponsibilities($userId);
        #$fullCollection = $this->getService('Dean')->getSubjectsResponsibilities($userId);
        if (count($collection)) {
            $grid->addMassAction(
                array(
                    'module' => 'assign',
                    'controller' => 'student',
                    'action' => 'do-soids',
                    'do' => 'assign',
                ),
                _('Hазначить учебные курсы'),
                _('Вы уверены, что хотите назначить выбранные учебные курсы сотрудникам отмеченных подразделений, включая все уровни вложенности? Если курсы предполагают согласование, будут созданы заявки на обучение.')
            );

            $grid->addSubMassActionSelect(
                $this->view->url(
                    array(
                    'module' => 'assign',
                    'controller' => 'student',
                    'action' => 'do-soids',
                    'do' => 'assign',
                    )
                ),
                'subjectId[]',
                $collection->getList('subid', 'name')
            );

            $grid->addMassAction(
                array(
                    'module' => 'assign',
                    'controller' => 'student',
                    'action' => 'do-soids',
                    'do' => 'unassign',
                ),
                _('Отменить назначение учебных курсов'),
                _('Вы уверены, что хотите отменить назначение учебных курсов сотрудникам отмеченных подразделений, включая все уровни вложенности?')
            );

            $grid->addSubMassActionSelect(
                $this->view->url(
                    array(
                        'module' => 'assign',
                        'controller' => 'student',
                        'action' => 'do-soids',
                        'do' => 'unassign',
                    )
                ),
                'subjectId[]',
                $fullCollection->getList('subid', 'name')
            );
        }

// ушло в OrgstructureService
//         $headUnitTitle = $this->getService('Option')->getOption('headStructureUnitName');
//         if (!strlen($headUnitTitle)) {
//             $headUnitTitle = _(self::DEFAULT_HEAD_STRUCTURE_ITEM_TITLE);
//         }

        $departments = array(0 => $defaultParent->name);

        $collection = $this->getService('Orgstructure')->fetchAll(
            $this->getService('Orgstructure')->quoteInto(
                array('type = ?', ' AND soid <> ?'),
                array(HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT, $orgId)
            ),
            'name'
        );

        if (count($collection)) {
            $list = $collection->getList('soid', 'name');
            foreach($list as $itemKey => $item) {
                $departments[$itemKey] = $item;
            }
        }

        $grid->addSubMassActionSelect(array(
            $this->view->url(
                array('action' => 'move', 'parent' => $orgId)
            )
        ),
            'to',
            $departments
        );

        $grid->setGridSwitcher(array(
            array('name' => 'strictly', 'title' => _('непосредственное подчинение'), 'params' => array('all' => 0)),
            array('name' => 'all', 'title' => _('все уровни вложенности'), 'params' => array('all' => 1)),
        ));

        if (!$this->isAjaxRequest()) {
            $tree = $this->getService('Orgstructure')->getTreeContent($defaultParent->soid, true, $orgId);

            $tree = array(
                0 => array(
                    'title' => $defaultParent->name,
                    'count' => 0,
                    'key' => $defaultParent->soid,
                    'isLazy' => true,
                    'isFolder' => true,
                    'expand' => true
                ),
                1 => $tree
            );
            $this->view->tree = $tree;
        }

        $this->view->orgId = $orgId;
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid->deploy();
    }

    public function assignProgrammAction()
    {
        $programmId = $this->_getParam('programm_id', 0);

        if($programmId > 0){
            $postMassIds = $this->_getParam('postMassIds_grid', '');
            if (strlen($postMassIds)) {
                $ids = explode(',', $postMassIds);
                if (count($ids)) {
                    $orgElements = $this->getService('Orgstructure')->fetchAll(array('soid IN (?)' => $ids));

                    foreach($orgElements as $element){
                        if($element->mid > 0){
                            $this->getService('Programm')->assignToUser($element->mid, $programmId);
                        }
                    }
                    $this->_flashMessenger->addMessage(_('Программа успешно назначена'));
                    $this->_redirectToIndex();

                }
            }



        }

        $this->_flashMessenger->addMessage(_('Программа не выбрана'));
        $this->_redirectToIndex();
    }
    public function classifierAction(){

        $request = $this->getRequest();
        $orgId = (int) $request->getParam('org_id', 0);

        $form = new HM_Form_Classifier();

        if($request->isPost() && $form->isValid($params = $request->getParams())){
            $this->getService('OrgstructureUnit')->setClassifiers(
                $orgId,
                $form->getSubForm('classifierStep2')->getClassifierTypes(),
                $form->getSubForm('classifierStep2')->getClassifierValues()
            );
            $this->_flashMessenger->addMessage(_('Классификация успешно изменена'));
            $this->_redirector->gotoSimple('index');
        }

        $this->view->form = $form;

    }


    public function updateClassifiers($classifiers)
    {
        $classifiers = array_unique(explode(',', $classifiers));
        $classifiers = array_unique($classifiers);
        $classifiers = implode(', <br/>', $classifiers);
        return $classifiers;
    }
    
    public function updateType($type, $vacancy_id)
    {
        $types = HM_Orgstructure_OrgstructureModel::getTypes();
        return $types[$type];
    }

    public function getTreeBranchAction()
    {
        $key = (int) $this->_getParam('key', 0);

        $children = $this->getService('Orgstructure')->getTreeContent($key, false);

        echo Zend_Json::encode($children);
	    exit;
    }

    public function cardAction() {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getResponse()->setHeader('Content-type', 'text/html; charset=' . Zend_Registry::get('config')->charset);
        $orgId = (int) $this->_getParam('org_id', 0);
        $this->view->subject = $this->getService('Orgstructure')->getOne(
            $this->getService('Orgstructure')->findDependence('User', $orgId)
        );

    }

    public function updateName($name, $orgId, $type, $isManager)
    {
        if ($type == HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT) {
            $name = '<a href="'.$this->view->url(array('module' => 'orgstructure', 'controller' => 'index', 'action' => 'index', 'org_id' => $orgId), null, true).'">'._($name).'</a>';
        }

        return $this->view->cardLink(
                $this->view->url(array(
                    'module' => 'orgstructure',
                    'controller' => 'list',
                    'action' => 'card',
                    'org_id' => '')
                ) . $orgId,
                HM_Orgstructure_OrgstructureService::getIconTitle($type, $isManager),
                'icon-custom',
                'pcard',
                'pcard',
                'orgstructure-icon-small ' . HM_Orgstructure_OrgstructureService::getIconClass($type, $isManager)
            ) . _($name);
    }

    public function updateFio($fio, $userId)
    {
        $fio = trim($fio);
        if(!$userId) return $fio;

        return $this->view->cardLink(
                   $this->view->url(array(
                                         'module' => 'user',
                                         'controller' => 'list',
                                         'action' => 'view',
                                         'user_id' => ''), null, true).$userId
               ).
               '<a href="'.$this->view->url(array(
                                                 'module' => 'user',
                                                 'controller' => 'edit',
                                                 'action' => 'card',
                                                 'user_id' => ''
                                            ), null, true) . $userId.'">'. $fio . '</a>';

    }

    protected function _redirectToIndex()
    {
        $orgId = (int) $this->_getParam('parent', 0);
        $this->_redirector->gotoSimple('index', 'list', 'orgstructure', array('key' => $orgId), null, true);

    }

    protected function _getMessages() {

        return array(
            self::ACTION_INSERT => _('Элемент успешно создан'),
            self::ACTION_UPDATE => _('Элемент успешно обновлён'),
            self::ACTION_DELETE => _('Элемент успешно удалён'),
            self::ACTION_DELETE_BY => _('Элементы успешно удалены')
        );
    }

    public function setDefaults(Zend_Form $form) {
        $orgId = (int) $this->_getParam('org_id', 0);
        $item = $this->getService('Orgstructure')->getOne(
            $this->getService('Orgstructure')->find($orgId)
        );
        $values = $item->getValues();

        $mid = $values['mid'];
        if ($values['mid'] = array($values['mid'] => '')) {
            $user = $this->getOne(
                $this->getService('User')->find($mid)
            );
            if ($user) {
                $values['mid'][$mid] = $user->getName();
            }
        }

        $form->setDefaults($values);
    }

    public function create(Zend_Form $form)
    {

        $values = array(
            'name' => $form->getValue('name'),
            'type' => $form->getValue('type'),
            'code' => $form->getValue('code'),
            'info' => $form->getValue('info'),
            'owner_soid' => $form->getValue('owner_soid'),
            'is_manager' => $form->getValue('is_manager')
        );

        if ($form->getElement('mid')) {
            $values['mid'] = $form->getValue('mid');

            if (is_array($values['mid'])) {
                if (count($values['mid'])) {
                    $values['mid'] = $values['mid'][0];
                } else {
                    $values['mid'] = 0;
                }
            }
        }

        $this->getService('Orgstructure')->insert(
            $values,
            $form->getValue('owner_soid')
        );

    }

    public function update(Zend_Form $form) {

        $values = array(
             'soid' => $form->getValue('soid'),
             'name' => $form->getValue('name'),
             'code' => $form->getValue('code'),
             'info' => $form->getValue('info'),
            'is_manager' => $form->getValue('is_manager')
        );

        if ($form->getElement('mid')) {
            $values['mid'] = $form->getValue('mid');

            if (is_array($values['mid'])) {
                if (count($values['mid'])) {
                    $values['mid'] = $values['mid'][0];
                } else {
                    $values['mid'] = 0;
                }
            }
        }

        $this->getService('Orgstructure')->update(
            $values
        );

        $this->_setParam('parent', $form->getValue('owner_soid'));
    }

    public function delete($id)
    {
        $unit = $this->getService('Orgstructure')->getOne(
            $this->getService('Orgstructure')->find($id)
        );

        if ($unit) {
            $this->_request->setParam('parent', $unit->owner_soid);
        }
        return $this->getService('Orgstructure')->deleteNode($id, true);
    }

    public function moveAction()
    {
        $parent = $this->_getParam('to', null);
        if (null !== $parent) {
            $postMassIds = $this->_getParam('postMassIds_grid', '');
            if (strlen($postMassIds)) {
                $ids = explode(',', $postMassIds);
                if (count($ids)) {
                    $errorFlag = false;
                    foreach($ids as $id) {
                        $this->getService('Orgstructure')->updateNode(
                            array('soid' => $id, 'owner_soid' => $parent),
                            $id,
                            $parent
                        );
                    }
                    $this->_flashMessenger->addMessage(_('Элемент успешно перемещён'));
                }
            }
            $this->_setParam('parent', $parent);
        }
        $this->_redirectToIndex();
    }

}