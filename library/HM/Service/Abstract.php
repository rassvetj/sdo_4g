<?php

abstract class HM_Service_Abstract
{
    protected $_mapperClass = null;
    protected $_modelClass = null;
    protected $_adapterClass = null;

    protected $_mapper = null;
    protected $_serviceContainer = null;
    protected $_acl = null;

    public function __construct($mapperClass = null, $modelClass = null, $adapterClass = null)
    {
        if (null !== $mapperClass) {
            $this->_mapperClass = $mapperClass;
        }

        if (null !== $modelClass) {
            $this->_modelClass = $modelClass;
        }

        if (null !== $adapterClass) {
            $this->_adapterClass = $adapterClass;
        }

        $className = substr(get_class($this), 0, -7); // trim Service
        if (null === $this->_mapperClass) {
            $this->_mapperClass = $className . 'Mapper';
        }

        if (null === $this->_modelClass) {
            $this->_modelClass = $className . 'Model';
        }

        if (null === $this->_adapterClass) {
            $this->_adapterClass = $className . 'Table';
        }

        $loader = Zend_Loader_Autoloader::getInstance();
        $loader->suppressNotFoundWarnings(true);

        if ($loader->autoload($this->_mapperClass)
            && $loader->autoload($this->_modelClass)) {

                $this->setMapper(new $this->_mapperClass($this->_adapterClass, $this->_modelClass));
                //$this->getMapper()->setModelClass($this->_modelClass);
        }

    }

    public function setAcl($acl)
    {
        $this->_acl = $acl;
    }

    public function getAcl()
    {
        return $this->_acl;
    }

    public function setServiceContainer($serviceContainer)
    {
        $this->_serviceContainer = $serviceContainer;
    }

    public function getServiceContainer()
    {
        return $this->_serviceContainer;
    }

    /**
     * @param  $name
     * @return HM_Service_Abstract
     */
    public function getService($name)
    {
        $service = $this->getServiceContainer()->getService($name);
        if (method_exists($service, 'getServiceContainer')) {
            if (null == $service->getServiceContainer()) {
                $service->setServiceContainer($this->getServiceContainer());
            }
        }
        return $service;
    }
    public function setMapper(HM_Mapper_Abstract $mapper)
    {
        $this->_mapper = $mapper;
    }

    /**
     *
     * @return HM_Mapper_Abstract
     */
    public function getMapper()
    {
        return $this->_mapper;
    }

    /**
     * TODO: Избавиться от $unsetNull, это в корне не правильно - удалять все NULL
     * 
     * @param $data
     * @param bool $unsetNull
     * @return HM_Model_Abstract
     */
    public function insert($data, $unsetNull = true)
    {
        if (is_array($data)) {

            if ($unsetNull) {
                // insert NULL в поле NOT NULL даёт ошибку БД в оракле и mssql
                foreach ($data as $key => $value) {
                    if ($value === null) {
                        unset($data[$key]);
                    }
                }
            }

            //Вообще тут это не нужно нафик
            try{
                $pk = $this->getMapper()->getTable()->getPrimaryKey();
                $pKey = 0;
                if(!is_array($pk)){
                    $pKey = $data[$pk];
                }

                $this->getService('Log')->log(
                    $this->getService('User')->getCurrentUserId(),
                    'Insert element',
                    'Success',
                    Zend_Log::NOTICE,
                    get_class($this),
                    $pKey
                );
            }
            catch(Exception $e){

            }
            return $this->getMapper()->insert(call_user_func_array(array($this->_modelClass, 'factory'), array($data, $this->_modelClass)));
        }
    }

    public function update($data, $unsetNull = true)
    {
        if (is_array($data)) {

            if ($unsetNull) {
                // insert NULL в поле NOT NULL даёт ошибку БД в оракле и mssql
                foreach ($data as $key => $value) {
                    if ($value === null) {
                        unset($data[$key]);
                    }
                }
            }

            //Вообще тут это не нужно нафик
            try{
                $pk = $this->getMapper()->getTable()->getPrimaryKey();
                $pKey = 0;
                if(!is_array($pk)){
                    $pKey = $data[$pk];
                }

                $this->getService('Log')->log(
                    $this->getService('User')->getCurrentUserId(),
                    'Update element',
                    'Success',
                    Zend_Log::NOTICE,
                    get_class($this),
                    $pKey
                );
            }
            catch(Exception $e){

            }




            return $this->getMapper()->update(call_user_func_array(array($this->_modelClass, 'factory'), array($data, $this->_modelClass)));
        }
    }

    public function updateWhere($data, $where){
        if (is_array($data)) {
            return $this->getMapper()->updateWhere($data, $where);
        }else{
            return false;
        }

    }

    public function delete($id)
    {
        //Вообще тут это не нужно нафик
        try{
            $this->getService('Log')->log(
                $this->getService('User')->getCurrentUserId(),
                'Delete element',
                'Success',
                Zend_Log::NOTICE,
                get_class($this),
                $id
            );
        }
        catch(Exception $e){

        }

        return $this->getMapper()->delete($id);
    }

    public function deleteBy($where)
    {
        return $this->getMapper()->deleteBy($where);
    }

    /**
     * @return HM_Collection
     */
    public function find()
    {
        $args = func_get_args();

        return call_user_func_array(array($this->getMapper(), 'find'), $args);
    }

    public function fetchRow($where = null)
    {
        return $this->getMapper()->fetchRow($where);
    }

    /**
     * @param  $where
     * @param  $order
     * @param  $count
     * @param  $offset
     * @return HM_Collection
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        return $this->getMapper()->fetchAll($where, $order, $count, $offset);
    }

    public function countAll($where = null)
    {
        return $this->getMapper()->countAll($where);
    }

    public function findDependence()
    {
        $args = func_get_args();
        return call_user_func_array(array($this->getMapper(), 'findDependence'), $args);
    }

    /**
     * @param  $joinDependence
     * @param  $where
     * @param  $order
     * @param  $count
     * @param  $offset
     * @return HM_Collection
     */
    public function fetchAllDependence($dependence = null, $where = null, $order = null, $count = null, $offset = null)
    {
        return $this->getMapper()->fetchAllDependence($dependence, $where, $order, $count, $offset);
    }

    /**
     * @param  $joinDependence
     * @param  $where
     * @param  $order
     * @param  $count
     * @param  $offset
     * @return HM_Collection
     */
    public function fetchAllDependenceJoinInner($joinDependence = null, $where = null, $order = null, $count = null, $offset = null)
    {
        return $this->getMapper()->fetchAllDependenceJoinInner($joinDependence, $where, $order, $count, $offset);
    }

    public function fetchAllJoinInner($joinDependence = null, $where = null, $order = null, $count = null, $offset = null)
    {
        return $this->getMapper()->fetchAllJoinInner($joinDependence, $where, $order, $count, $offset);
    }


    public function countAllDependenceJoinInner($joinDependence = null, $where = null)
    {
        return $this->getMapper()->countAllDependenceJoinInner($joinDependence, $where);
    }

    public function findManyToMany()
    {
        $args = func_get_args();

        return call_user_func_array(array($this->getMapper(), 'findManyToMany'), $args);
    }

    public function fetchAllManyToMany($dependence = null, $intersection = null, $where = null, $order = null, $count= null, $offset =null)
    {
        return $this->getMapper()->fetchAllManyToMany($dependence, $intersection, $where, $order, $count, $offset);
    }

    public function fetchAllHybrid($dependence = null, $ManyToManyDependence = null, $ManyToManyIntersection = null, $where = null, $order = null, $count = null, $offset = null)
    {
        return $this->getMapper()->fetchAllHybrid($dependence, $ManyToManyDependence, $ManyToManyIntersection, $where, $order, $count, $offset);
    }

    public function quoteInto($where, $args)
    {
        if (is_array($where)) {
            $quotedWhere = '';
            foreach($where as $key => $w) {
                $quotedWhere .= $this->getMapper()->getTable()->getAdapter()->quoteInto($w, $args[$key]);
            }
            return $quotedWhere;
        } else {
            return $this->getMapper()->getTable()->getAdapter()->quoteInto($where, $args);
        }
    }

    public function quoteIdentifier($ident)
    {
        return $this->getMapper()->getTable()->getAdapter()->quoteIdentifier($ident, true);
    }

    public function getDateTime($time = null, $onlyDate = false)
    {
        if (null == $time) {
            $time = time();
        }
        return ($onlyDate)? date('Y-m-d', $time) : date('Y-m-d H:i:s', $time);
    }

    /**
     * @return Zend_Db_Table_Select
     */
    public function getSelect()
    {
        return $this->getMapper()->getTable()->getAdapter()->select();
    }

    /**
     * @param  $collection
     * @return bool | HM_Model_Abstract
     */
    public function getOne($collection)
    {
        if (count($collection)) {
            return $collection->current();
        }
        return false;
    }

    /**
     * @param  $where
     * @param  $order
     * @param  $dependence
     * @param  $intersection
     * @param  $ManyToManyDependence
     * @return Zend_Paginator
     */
    public function getPaginator($where = null, $order = null, $dependence = null, $intersection = null, $ManyToManyDependence = null)
    {
        $this->getMapper()->setPaginatorOptions(array(
            'where' => $where,
            'order' => $order,
            'dependence' => $dependence,
            'intersection' => $intersection,
            'mtm_dependence' => $ManyToManyDependence
        ));

        Zend_Paginator::setDefaultScrollingStyle('Sliding');
        Zend_View_Helper_PaginationControl::setDefaultViewPartial(
          'pager.tpl'
        );
        //$paginator->setView($view);

        $paginator = new Zend_Paginator($this->getMapper());
        $paginator->setCurrentPageNumber(Zend_Controller_Front::getInstance()->getRequest()->getParam('page', 1));
        //$paginator->setItemCountPerPage(5);

        return $paginator;
    }

    public function getResults($lessonId, $userId)
    {
        $lesson = $this->getOne($this->find($lessonId));
        if ($lesson) {
            switch($lesson->typeID) {
                // todo: Another lesson types
                case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_LEADER:
                case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_STUDENT:
                case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_TEACHER:
                case HM_Event_EventModel::TYPE_POLL:
                case HM_Event_EventModel::TYPE_EXERCISE:
                case HM_Event_EventModel::TYPE_TASK:
                case HM_Event_EventModel::TYPE_TEST:
                    return $this->getService('TestResult')->fetchAll(
                        $this->quoteInto(
                            array('mid = ?', ' AND sheid = ?'),
                            array($userId, $lessonId)
                        ),
                        'stid DESC'
                    );
                    break;
            }
        }

        return new HM_Collection(array());
    }

}