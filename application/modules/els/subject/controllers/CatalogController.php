<?php
class Subject_CatalogController extends HM_Controller_Action
{
    protected $_classifiers = Null;

    protected $_itemType;

    public function init(){
        $this->_itemType = HM_Classifier_Link_LinkModel::TYPE_SUBJECT;
        parent::init();
    }

    public function indexAction()
    {
        $classifierId = (int) $this->_getParam('classifier_id', 0);
        $programId = (int) $this->_getParam('program_id', 0);
        $item = (int) $this->_getParam('item', 0);
        $classifierId = (!$classifierId) ? $item : $classifierId;
        $type = (int) $this->_getParam('type', 0);

        if (!$this->_getParam('ordergrid', '')) {
            $this->_setParam('ordergrid', 'name_ASC');
        }

        //подтверждение перехода при подаче заявки на один курс
        $confId = $this->_getParam('confirm_id', 0);
        if ($confId && $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_STUDENT)) {
            $confSubject = $this->getService('Subject')->getOne($this->getService('Subject')->find($confId));

            if ($confSubject) {
                $curDate   = new HM_Date();
                $isCurrent = false;
                if (strtotime($confSubject->begin)) {
                    $startDate = new HM_Date($confSubject->begin);
                    $isCurrent = ($curDate->getTimestamp() >= $startDate->getTimestamp())? true : false;
                }
                if(
                    (in_array($confSubject->period, array(HM_Subject_SubjectModel::PERIOD_FREE, HM_Subject_SubjectModel::PERIOD_FIXED))) ||
                    ($confSubject->period == HM_Subject_SubjectModel::PERIOD_DATES && $isCurrent) ||
                    ($confSubject->period_restriction_type == HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL && $confSubject->state == HM_Subject_SubjectModel::STATE_ACTUAL)

                ) {
                    $this->view->confirmID = $confId;
                }
            }



        }

        $select = $this->getService('Subject')->getSelect();

        $classifier = $this->getService('Classifier')->getOne($this->getService('Classifier')->fetchAll(array('classifier_id = ?' => $classifierId)));

        $types = $this->getService('Classifier')->getTypes($this->_itemType);

        if (!$type) {
            if (count($types)) {
                $type = array_shift(array_keys($types));
            }
        }
        $select->from(array('s' => 'subjects'),
            array(
                'subid' => 's.subid',
                'name' => 's.name',
                'reg_type' => 's.reg_type',
                'claimant_process_id' => 's.claimant_process_id',
                'classLeft' => new Zend_Db_Expr('MIN(class.lft)'),
                'classes' => new Zend_Db_Expr("''"),
                'student' => 'st.SID'
            )
        )
        ->where($this->quoteInto(
            array(
                's.period IN (?) OR ',
                's.period_restriction_type = ? OR ',
                '(s.period_restriction_type = ?',' AND (s.state = ? ',' OR s.state = ? OR s.state is null) ) OR ',
                '(s.period = ? AND ',
                's.end > ?)',
            ),
            array(
                array(HM_Subject_SubjectModel::PERIOD_FREE, HM_Subject_SubjectModel::PERIOD_FIXED),
                HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT,
                HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL,
                HM_Subject_SubjectModel::STATE_ACTUAL,
                HM_Subject_SubjectModel::STATE_PENDING,
                HM_Subject_SubjectModel::PERIOD_DATES,
                $this->getService('Subject')->getDateTime()
            )
        ))
        ->where('s.reg_type = ?', HM_Subject_SubjectModel::REGTYPE_MODER)
        ->order('classLeft ASC')
        ->group(array('s.subid', 's.name', 's.reg_type', 's.claimant_process_id','st.SID'));

        if ($classifierId && $classifier->level >= 0) {

            $classifier = $this->getService('Classifier')->getOne($this->getService('Classifier')->fetchAll(array('classifier_id = ?' => $classifierId)));

            $select
                ->joinInner(array('c' => 'classifiers_links'), 's.subid = c.item_id AND c.type = '.(int) HM_Classifier_Link_LinkModel::TYPE_SUBJECT, array())
                ->joinInner(array('class' => 'classifiers'), 'c.classifier_id = class.classifier_id', array())
                ->where('class.lft >= ?', $classifier->lft)
                ->where('class.rgt <= ?', $classifier->rgt);

        } else {
            $select
                ->joinLeft(array('c' => 'classifiers_links'), 's.subid = c.item_id AND c.type = '.(int) HM_Classifier_Link_LinkModel::TYPE_SUBJECT, array())
                ->joinLeft(array('class' => 'classifiers'), 'c.classifier_id = class.classifier_id', array());
        }

        if ($programId > 0) {
            $select->joinInner(array('prog' => 'programm_events'), 'prog.item_id = s.subid AND prog.isElective = 1 AND prog.type = ' . HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT . ' AND prog.programm_id = ' . $programId, array());
        }

        //if ($this->getService('User')->getCurrentUserId()) {
        // при таком условии гость вообще никогда не увидит
            $userId = (int)$this->getService('User')->getCurrentUserId();
            $select->joinLeft(array('st' => 'Students'), 's.subid = st.CID AND st.MID = ' . $userId, array())
                   /*->where('st.CID IS NULL')*/;
            $select->joinLeft(array('cl' => 'claimants'), 's.subid = cl.CID AND cl.status = ' . HM_Role_ClaimantModel::STATUS_NEW . ' AND cl.MID = ' . $userId, array())
                   ->where('cl.CID IS NULL');
        //}
        $grid = $this->getGrid($select, array(
            'subid' => array('hidden' => true),
            'classLeft' => array('hidden' => true),
            'classes' => array('title' => _('Классификация')),
            'name' => array(
                'title' => _('Название'),
                'decorator' => $this->view->cardLink($this->view->url(array('module' => 'subject', 'controller' => 'list', 'action' => 'card', 'subject_id' => ''), null, true) . '{{subid}}', _('Карточка учебного курса')) . '{{name}}'
            ),
            'reg_type' => array('hidden' => true),
            'claimant_process_id' => array(
                'title' => _('Согласование заявок'),
                'callback' => array('function' => array($this, 'updateClaimProcType'), 'params' => array('{{claimant_process_id}}'))
            ),
            'student' => array('hidden' => true)
        ),
            array(
                'name' => null,
            )

        );
        $grid->updateColumn('classes',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateClassifiers'),
                    'params' => array('{{subid}}', $select, $type)
                )
            )
        );

        $grid->updateColumn('name',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateName'),
                    'params' => array('{{name}}', '{{subid}}')
                )
            )
        );

//        if ($this->getService('Option')->getOption('regAllow') !== '0') {
        $grid->addAction(array(
            'module' => 'user',
            'controller' => 'reg',
            'action' => 'subject'
        ),
            array('subid'),
            _('Подать заявку'),
            $this->getService('User')->getCurrentUserId() ? _('Вы уверены?') : null
        );

        $grid->addMassAction(array(
                'module' => 'user',
                'controller' => 'reg',
                'action' => 'subjects',
                'status' => array(
                'status' => $status)
        ),
            _('Подать заявку'),
            _('Вы уверены?')
        );
//        }

        if ($this->getService('User')->getCurrentUserId()) $grid->setClassRowCondition("'{{student}}' != ''", "selected");

        $grid->setActionsCallback(
            array('function' => array($this,'updateActions'),
                'params'   => array('{{student}}')
            )
        );

        $tree = $this->getService('Classifier')->getTreeContent($this->_itemType, 0, $type, false, $classifierId);
        $this->view->types = $types;
        $this->view->type = $type;
        $this->view->tree = $tree;

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid->deploy();

    }

    public function updateActions($isStudent, $actions)
    {
        return ($isStudent)? "" : $actions;
    }

    public function updateClaimProcType($claimProcId)
    {
        return $claimProcId ? _('Согласование организатором обучения') : _('Автоматическое назначение на курс');
    }

    public function getTreeBranchAction()
    {
        $key = (int) $this->_getParam('key', 0);

        $children = $this->getService('Classifier')->getTreeContent($this->_itemType, $key);

        echo Zend_Json::encode($children);
	    exit;
    }

    public function prepareAction()
    {
        /*
        $node = $this->getService('Classifier')->insert(
            array(
                'name' => _('Школьная программа'),
                'type' => HM_Classifier_ClassifierModel::TYPE_RESOURCE
            )
        );

        if ($node) {
            $this->getService('Classifier')->insert(
                array(
                    'name' => _('Математика'),
                    'type' => HM_Classifier_ClassifierModel::TYPE_RESOURCE
                ),
                $node->classifier_id
            );

            $this->getService('Classifier')->insert(
                array(
                    'name' => _('Русский язык'),
                    'type' => HM_Classifier_ClassifierModel::TYPE_RESOURCE
                ),
                $node->classifier_id
            );
        }

        $node = $this->getService('Classifier')->insert(
            array(
                'name' => _('Группы работников'),
                'type' => HM_Classifier_ClassifierModel::TYPE_GROUP
            )
        );

        if ($node) {
            $this->getService('Classifier')->insert(
                array(
                    'name' => _('Группа 1'),
                    'type' => HM_Classifier_ClassifierModel::TYPE_GROUP
                ),
                $node->classifier_id
            );

            $this->getService('Classifier')->insert(
                array(
                    'name' => _('Группа 2'),
                    'type' => HM_Classifier_ClassifierModel::TYPE_GROUP
                ),
                $node->classifier_id
            );
        }

        $node = $this->getService('Classifier')->insert(
            array(
                'name' => _('Виды деятельности и темы обучения'),
                'type' => HM_Classifier_ClassifierModel::TYPE_ACTIVITY
            )
        );

        if ($node) {
            $this->getService('Classifier')->insert(
                array(
                    'name' => _('Тема 1'),
                    'type' => HM_Classifier_ClassifierModel::TYPE_ACTIVITY
                ),
                $node->classifier_id
            );

            $this->getService('Classifier')->insert(
                array(
                    'name' => _('Тема 2'),
                    'type' => HM_Classifier_ClassifierModel::TYPE_ACTIVITY
                ),
                $node->classifier_id
            );
        }

        $node = $this->getService('Classifier')->insert(
            array(
                'name' => _('Образовательные огранизации'),
                'type' => HM_Classifier_ClassifierModel::TYPE_EDUCATION
            )
        );

        if ($node) {
            $this->getService('Classifier')->insert(
                array(
                    'name' => _('ВУЗ'),
                    'type' => HM_Classifier_ClassifierModel::TYPE_EDUCATION
                ),
                $node->classifier_id
            );

            $this->getService('Classifier')->insert(
                array(
                    'name' => _('Школа'),
                    'type' => HM_Classifier_ClassifierModel::TYPE_EDUCATION
                ),
                $node->classifier_id
            );
        }

        //pr($this->getService('Classifier')->getTree('node.type = '.HM_Classifier_ClassifierModel::TYPE_RESOURCE));
        */
        die('done');

    }


    public function updateClassifiers($subjectId,Zend_Db_Select $select, $type)
    {

        if($this->_classifiers == Null){

            $query = $select->query();
            $fetch = $query->fetchAll();
            $ids = array();
            foreach($fetch as $value){
                $ids[] = $value['subid'];
            }
            $values = $select->getAdapter()->quote($ids);
            $temp = $this->getService('ClassifierLink')->fetchAllDependenceJoinInner('Classifier', 'self.item_id IN (' . $values . ') AND self.type = ' . HM_Classifier_Link_LinkModel::TYPE_SUBJECT . ' AND Classifier.type = ' . (int) $type);
            $this->_classifiers = $temp;

        }

        $ret = array();
        foreach($this->_classifiers as $val){
            if($val->item_id == $subjectId){
                foreach($val->classifiers as $class){
                    $ret[] = $class->name;
                }
            }

        }

        return implode(',', $ret);
    }


    public function updateName($name, $subjectId)
    {
        if (
            in_array($this->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_GUEST, HM_Role_RoleModelAbstract::ROLE_USER, HM_Role_RoleModelAbstract::ROLE_STUDENT)) ||
            $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER)
        ){
            $type = $this->_getParam('type', null);
            $item = $this->_getParam('item', null);
            $classifierId = $this->_getParam('classifier_id', null);

            $marker = '';
            if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_STUDENT)) {
                if (empty($this->subjectsCache)) {
                    $this->subjectsCache = Zend_Registry::get('serviceContainer')->getService('User')->getSubjects()->getList('subid');
                }
                if (in_array($subjectId, $this->subjectsCache)) {
                    $marker = HM_View_Helper_Footnote::marker(1);
                    $this->view->footnote(_('Вы уже зачислены на этот курс'), 1);
                }
            }

            return '<a href="' . $this->view->url(array('module' => 'subject', 'controller' => 'list', 'action' => 'description', 'subject_id' => $subjectId, 'item' => $item, 'type' => $type, 'classifier_id' => $classifierId)) . '">' . $name . '</a>' . $marker;
        }else{
            return $name;
        }
    }


}