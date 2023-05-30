<?php
class Resource_CatalogController extends HM_Controller_Action
{
    protected $_classifiers = Null;
    protected $_resources = Null;

    public function init()
    {
        $this->_itemType = HM_Classifier_Link_LinkModel::TYPE_RESOURCE;
        parent::init();
    }

    public function indexAction()
    {
        $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base . 'css/content-modules/material-icons.css');

        $classifierId = (int) $this->_getParam('classifier_id', 0);
        $types = $this->getService('Classifier')->getTypes($this->_itemType);

        $type = (int) $this->_getParam('type', 0);
        if ( !$type ) {
            $type = array_shift(array_keys($types));
            $this->_request->setParam('type', $type);
        }
        $select = $this->getService('Subject')->getSelect();

        $classifier = $this->getService('Classifier')->getOne($this->getService('Classifier')->fetchAll(array('classifier_id = ?' => $classifierId)));


        if ($classifierId /*&& $classifier->level > 0*/) {

            $classifier = $this->getService('Classifier')->getOne($this->getService('Classifier')->fetchAll(array('classifier_id = ?' => $classifierId)));

            $select->from(array('r' => 'resources'),
                array(
                    'resource_id' => 'r.resource_id',
                    'name' => 'r.title',
                    'restype' => 'r.type',
                    'filetype',
                    'filename',
                    'activity_type',
                    'created' => 'r.created',
                    'left' => new Zend_Db_Expr('MIN(class.lft)'),
                    'classes' => new Zend_Db_Expr("''"),

                )
            )
            ->joinInner(array('c' => 'classifiers_links'), 'r.resource_id = c.item_id AND c.type = '.(int) $this->_itemType, array())
            ->joinInner(array('class' => 'classifiers'), 'c.classifier_id = class.classifier_id', array())
            ->where('class.lft >= ?', $classifier->lft)
            ->where('class.rgt <= ?', $classifier->rgt)
            ->where('r.status = '.HM_Resource_ResourceModel::STATUS_PUBLISHED)
            ->order('left ASC')
            ->group(array(
                'r.resource_id', 
                'r.title', 
                'r.type', 
                'r.filetype',
                'r.filename',
                'r.activity_type', 
                'r.created'
            ));

        } else {
            $select->from(array('r' => 'resources'),
                array(
                    'resource_id' => 'r.resource_id',
                    'name' => 'r.title',
                    'restype' => 'r.type',
                    'filetype',
                    'filename',
                    'activity_type',
                    'keywords' => new Zend_Db_Expr("''"),
                	'created' => 'r.created',
                    'left' => new Zend_Db_Expr('MIN(class.lft)'),
                    'classes' => new Zend_Db_Expr("''"),
                )
            )
            ->joinLeft(array('c' => 'classifiers_links'), 'r.resource_id = c.item_id AND c.type = '.(int) $this->_itemType, array())
            ->joinLeft(array('class' => 'classifiers'), 'c.classifier_id = class.classifier_id', array())
            //->where('class.type = ? OR class.type IS NULL', $type)
            ->where('r.status = '.HM_Resource_ResourceModel::STATUS_PUBLISHED)
            ->order('left ASC')
            ->group(array('r.resource_id', 'r.title', 'r.type', 'r.created'));
        }
        $grid = $this->getGrid($select, array(
            'resource_id' => array('hidden' => true),
            'filetype' => array('hidden' => true),
            'filename' => array('hidden' => true),
            'activity_type' => array('hidden' => true),
            'restype' => array('title' => _('Тип ресурса')),
        	'keywords' => array('title' => _('Ключевые слова')),
        	'created' => array('title' => _('Дата создания'), 'format' => 'date'),
            'left' => array('hidden' => true),
            'classes' => array('title' => _('Классификация')),
            'name' => array(
                'title' => _('Название'),
                'callback' => array(
                    'function' => array($this, 'updateResourceName'),
                    'params' => array('{{resource_id}}', '{{name}}', '{{restype}}', '{{filetype}}', '{{filename}}', '{{activity_type}}')
                ),
            ),
        ),
            array(
                'name' => null,
                'created' => array('render' => 'date'),
                'restype' => array('values' => HM_Resource_ResourceModel::getTypes())
            )

        );
        $grid->updateColumn('classes',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateClassifiers'),
                    'params' => array('{{resource_id}}', $select, $type)
                )
            )
        );
        $grid->updateColumn('keywords',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateKeywords'),
                    'params' => array('{{resource_id}}', $select)
                )
            )
        );

        $grid->updateColumn('restype',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateResourceType'),
                    'params' => array('{{restype}}')
                )
            )
        );

 /*       $grid->addAction(array(
            'module' => 'user',
            'controller' => 'reg',
            'action' => 'subject'
        ),
            array('subid'),
            _('Зарегистрироваться'),
            $this->getService('User')->getCurrentUserId() ? _('Вы уверены?') : null
        );*/

        $this->view->types = $types;
        $this->view->type = $type;
        $this->view->tree = $this->getService('Classifier')->getTreeContent($this->_itemType, 0, $type);

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid->deploy();

    }

    public function updateRegType($type)
    {
        $types = HM_Subject_SubjectModel::getRegTypes();
        return $types[$type];
    }

    public function getTreeBranchAction()
    {
        $key = (int) $this->_getParam('key', 0);

        $children = $this->getService('Classifier')->getTreeContent($this->_itemType,$key);

        echo Zend_Json::encode($children);
	    exit;
    }

    public function updateClassifiers($subjectId,Zend_Db_Select $select, $type)
    {

        if($this->_classifiers == Null){

            $query = $select->query();
            $fetch = $query->fetchAll();
            $ids = array();
            foreach($fetch as $value){
                $ids[] = $value['resource_id'];
            }
            $values = $select->getAdapter()->quote($ids);
            $temp = $this->getService('ClassifierLink')->fetchAllDependenceJoinInner('Classifier', 'self.item_id IN (' . $values . ') AND self.type = ' . $this->_itemType . ' AND Classifier.type = ' . (int) $type);
            $this->_classifiers = $temp;

        }

        if(count($this->_classifiers)){
            $ret = array();
            $count = 0;
            foreach($this->_classifiers as $val){
                if($val->item_id == $subjectId){
                    foreach($val->classifiers as $class){
//                        $ret[] = $class->name;
                        $ret[] = "<p>{$class->name}</p>";
                        $count++;
                    }    
                }
            }
            array_unshift($ret, '<p class="total">' . Zend_Registry::get('serviceContainer')->getService('Classifier')->pluralFormCount($count) . '</p>');
        }

//        return implode(',', $ret);                
        if($ret)
            return implode('',$ret);
        else
            return _('Нет');
    }

    public function updateKeywords($resourceId,Zend_Db_Select $select)
    {

        if($this->_resources == Null){

            $query = $select->query();
            $fetch = $query->fetchAll();
            $ids = array();
            foreach($fetch as $value){
                $ids[] = $value['resource_id'];
            }

            $temp = $this->getService('Resource')->fetchAll(array('resource_id IN (?)' => $ids));
            $this->_resources = $temp;

        }

        foreach($this->_resources as $val){
            if($val->resource_id == $resourceId){
                return $val->keywords;
            }
        }

        return '';

    }

    public function updateResourceType($type)
    {
        $types = HM_Resource_ResourceModel::getTypes();
        return $types[$type];
    }
}