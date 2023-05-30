<?php
/*
* @todo: fetchAllBy<what>
*/
abstract class HM_Mapper_Abstract implements HM_Mapper_Interface, Zend_Paginator_Adapter_Interface
{
    const DEFAULT_COLLECTION_CLASS = 'HM_Collection';

    protected $_modelClass = null;
    protected $_tableClass = null;
    protected $_collectionClass = null;
    protected $_paginatorOptions = null;

    protected $_adapter;

    public function __construct($tableClass = null, $modelClass = null, $collectionClass = null)
    {
        if (null !== $tableClass) {
            $this->_tableClass = $tableClass;
        }

        if (null !== $modelClass) {
            $this->_modelClass = $modelClass;
        }

        if (null !== $collectionClass) {
            $this->_collectionClass = $collectionClass;
        }

        $className = substr(get_class($this), 0, -6); // trim Mapper
        if (null === $this->_tableClass) {
            $this->_tableClass = $className . 'Table';
        }

        if (null === $this->_modelClass) {
            $this->_modelClass = $className . 'Model';
        }

        if (null == $this->_collectionClass) {
            $this->_collectionClass = self::DEFAULT_COLLECTION_CLASS;
        }

        $this->setAdapter(new $this->_tableClass());
    }

    public function getAdapter()
    {
        return $this->_adapter;
    }

    public function setAdapter($adapter)
    {
        // todo: проверка на Zenb_Db_Table и HM_Adapter_Interface
        $this->_adapter = $adapter;
    }

    /**
     * @param Zend_Db_Table_Abstract $table
     * @return void
     * @deprecated
     */
    public function setTable(Zend_Db_Table_Abstract $table)
    {
        $this->_adapter = $table;
    }

    /**
     * @return HM_Db_Table
     * @deprecated
     */
    public function getTable()
    {
        return $this->_adapter;
    }

    public function setModelClass($name)
    {
        $this->_modelClass = $name;
    }

    public function getModelClass()
    {
        return $this->_modelClass;
    }

    public function setCollectionClass($collectionClass)
    {
        $this->_collectionClass = $collectionClass;
    }

    public function getCollectionClass()
    {
        return $this->_collectionClass;
    }

    public function insert(HM_Model_Abstract $model)
    {
        $result = $this->getTable()->insert($model->getValues());
        if (is_array($result)) {
            foreach($result as $primaryKey => $value) {
                $model->setValue($primaryKey, $value);
            }
        } elseif ($result) {
            $primaryKey = $this->getTable()->getPrimaryKey();
            if (isset($primaryKey[1])) {
                $model->setValue($primaryKey[1], $result);
            }
        }
        return $model;
    }

    protected function _createWhereFromModel($model)
    {
        $primaryKey = $this->getTable()->getPrimaryKey();
        if (is_array($primaryKey)) {
            $wheres = array();
            foreach($primaryKey as $key) {
                $wheres[] = $this->getTable()->getAdapter()->quoteInto(sprintf("%s = ?", $key), $model->getValue($key));
            }

            if (count($wheres)) {
                $where = join(' AND ', $wheres);
            }

        } elseif (null !== $primaryKey) {
            $where = $this->getTable()->getAdapter()->quoteInto(sprintf("%s = ?", $primaryKey), $model->getValue($primaryKey));
        }
        return $where;
    }

    public function update(HM_Model_Abstract $model)
    {
        $where = $this->_createWhereFromModel($model);

        // fix mssql doesn't update identity column
        $primaryKey = $this->getTable()->getPrimaryKey();
        if (is_array($primaryKey)) {
            foreach($primaryKey as $key) {
                unset($model->{$key});
            }
        } elseif (null !== $primaryKey) {
            unset($model->{$primaryKey});
        }

        $this->getTable()->update($model->getValues(), $where);

        $collection = $this->fetchAll($where);
        if (count($collection)) {
        	$model = $collection->current();
        }

        return $model;
    }

    public function updateWhere($data, $where){

        return $this->getTable()->update($data, $where);

    }

    protected function _createWhereFromId($id)
    {
        $primaryKey = $this->getTable()->getPrimaryKey();
        if (is_array($primaryKey) && (count($primaryKey) > 1)) {
            $wheres = array();
            foreach($primaryKey as $index => $key) {
                $wheres[] = $this->getTable()->getAdapter()->quoteInto(sprintf("%s = ?", $key), $id[$index]);
            }

            if (count($wheres)) {
                $where = join(' AND ', $wheres);
            }

        } elseif (null !== $primaryKey) {
            if (is_array($primaryKey)) {
                $where = $this->getTable()->getAdapter()->quoteInto(sprintf("%s = ?", array_shift($primaryKey)), $id);
            } elseif (is_string($primaryKey)) {
                $where = $this->getTable()->getAdapter()->quoteInto(sprintf("%s = ?", $primaryKey), $id);
            }
        }
        return $where;
    }

    public function delete($id)
    {
        $where = $this->_createWhereFromId($id);

        return $this->getTable()->delete($where);
    }

    public function deleteBy($where)
    {
        return $this->getTable()->delete($where);
    }

    protected function _createModel($rows, &$dependences = array())
    {
        $collectionClass = $this->getCollectionClass();
        $models = new $collectionClass(array(), $this->getModelClass());
        if (count($rows) < 1) {
            return $models;
        }

        if (count($rows) > 0) {
            foreach($rows as $row) {
                $models[count($models)] = $row->toArray();
            }

            $models->setDependences($dependences);
        }

        return $models;

    }

    public function find()
    {
        $args = func_get_args();

        $rows = call_user_func_array(array($this->getTable(), 'find'), $args);

        return $this->_createModel($rows);

    }

    public function fetchRow($where = null)
    {
        $collection = $this->fetchAll($where, null, 1);

        return $collection->isEmpty() ? null : $collection->current();
    }

    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        $rows = $this->getTable()->fetchAll($where, $order, $count, $offset);

        return $this->_createModel($rows);
    }

    public function countAll($where)
    {
        $count = 0;

        $primaryKeys = $this->getTable()->getPrimaryKey();
        if (is_array($primaryKeys)) {
            $primaryKeys = join(',', $primaryKeys);
        }

        $select = $this->getTable()->select();
        $select->from(array('self' => $this->getTable()->getTableName()), array('cnt' => new Zend_Db_Expr(sprintf('COUNT(%s)', 'self.'.$primaryKeys))));

        if (null != $where) {
            $select->where($where);
        }

        $stmt = $select->query();
        $data = $stmt->fetch();
        $count = $data['cnt'];

        return $count;
    }

    protected function _fetchDependences($dependence, $rows, $dependences = null)
    {
        if ((null == $dependences) || !is_array($dependences)) {
            $dependences = array();
        }

        if (count($rows)) {
            $referenceMap = $this->getTable()->getReferenceMap();
            if (is_array($dependence) && count($dependence)) {
                foreach($dependence as $ref) {
                    $propertyName = $ref;
                    $rule = false;
                    if (isset($referenceMap[$ref]['propertyName'])) {
                        $propertyName = $referenceMap[$ref]['propertyName'];
                    }
                    if (isset($referenceMap[$ref])) {
                        $parts = explode('_', $this->getModelClass()); 
                    
                        if (count($parts) <= 3) { // должно работать для HM_User_UserModel и не должно для HM_At_Session_User_UserModel	
                            $rule = substr($parts[count($parts)-1], 0, -5); // trim Table
                        }

                        $dependenceTableName = $referenceMap[$ref]['refTableClass'];
                        $dependenceTable = new $dependenceTableName();
                        $dependenceReferenceMap = $dependenceTable->getReferenceMap();

                        if (!isset($dependenceReferenceMap[$rule])) {
                            foreach($dependenceReferenceMap as $ruleName => $reference) {
                                if (($reference['refTableClass'] == $this->_tableClass)
                                    && ($reference['refColumns'] == $referenceMap[$ref]['columns'])) {
                                    $rule = $ruleName;
                                    break;
                                }
                            }
                        }

                        $refRows = $rows->findDependentRowset($referenceMap[$ref]['refTableClass'], $rule);
                        if (count($refRows)) {
                            $columns = $referenceMap[$ref]['columns'];
                            $refColumns = $referenceMap[$ref]['refColumns'];
                            $refClass = substr($referenceMap[$ref]['refTableClass'], 0, -5) . 'Model'; // Trim Table and add Model
                            foreach($refRows as $refRow) {
                                $dependences[$columns][$refRow->$refColumns][$propertyName][] = array('row' => $refRow->toArray(), 'refClass' => $refClass);
                            }
                        }
                    }
                }

            } elseif (is_string($dependence)) {
                $propertyName = $dependence;
                if (isset($referenceMap[$dependence]['propertyName'])) {
                    $propertyName = $referenceMap[$dependence]['propertyName'];
                }
                if (isset($referenceMap[$dependence])) {
                    $parts = explode('_', $this->getModelClass());
                    $rule = substr($parts[count($parts)-1], 0, -5); // trim Table

                    $dependenceTableName = $referenceMap[$dependence]['refTableClass'];
                    $dependenceTable = new $dependenceTableName();
                    $dependenceReferenceMap = $dependenceTable->getReferenceMap();

                    if (!isset($dependenceReferenceMap[$rule])) {
                        foreach($dependenceReferenceMap as $ruleName => $reference) {
                            if ($reference['refTableClass'] == $this->_tableClass
                            && ($reference['refColumns'] == $referenceMap[$dependence]['columns'])
                            && ($reference['columns'] == $referenceMap[$dependence]['refColumns'])) { // <- в редких случаях это нужно, напр, Sibling в HM_Orgstructure_OrgstructureTable
                                $rule = $ruleName;
                                break;
                            }
                        }
                    }

                    $refRows = $rows->findDependentRowset($referenceMap[$dependence]['refTableClass'], $rule);
                    if (count($refRows)) {
                        $columns = $referenceMap[$dependence]['columns'];
                        $refColumns = $referenceMap[$dependence]['refColumns'];
                        $refClass = substr($referenceMap[$dependence]['refTableClass'], 0, -5) . 'Model'; // Trim Table and add Model
                        foreach($refRows as $refRow) {
                            $dependences[$columns][$refRow->$refColumns][$propertyName][] = array('row' => $refRow->toArray(), 'refClass' => $refClass);
                        }
                    }
                }
            }
        }

        return $dependences;
    }

    public function findDependence()
    {
        $args = func_get_args();

        if (isset($args[0])) {
            $dependence = $args[0];
            unset($args[0]);
        }

        $rows = call_user_func_array(array($this->getTable(), 'find'), $args);

        $dependences = $this->_fetchDependences($dependence, $rows);

        return $this->_createModel($rows, &$dependences);
    }

    public function fetchAllDependence($dependence = null, $where = null, $order = null, $count = null, $offset = null)
    {

        $rows = $this->getTable()->fetchAll($where, $order, $count, $offset);

        $dependences = $this->_fetchDependences($dependence, $rows);

        return $this->_createModel($rows, $dependences);
    }

    public function fetchAllJoinInner($dependence = null, $where = null, $order = null, $count = null, $offset = null)
    {
        $rows = $this->fetchAllInnerJoinRows($dependence, $where, $order, $count, $offset);
        $dependences = array();
        return $this->_createModel($rows, $dependences);

    }

    public function fetchAllDependenceJoinInner($dependence = null, $where = null, $order = null, $count = null, $offset = null)
    {
        $rows = $this->fetchAllInnerJoinRows($dependence, $where, $order, $count, $offset);
        $dependences = $this->_fetchDependences($dependence, $rows);
        return $this->_createModel($rows, $dependences);
    }

    protected function fetchAllInnerJoinRows($dependence = null, $where = null, $order = null, $count = null, $offset = null)
    {
        $referenceMap = $this->getTable()->getReferenceMap();
        $primaryKeys = $this->getTable()->getPrimaryKey();

        if (is_string($primaryKeys)) {
            $primaryKeys = array(1 => $primaryKeys);
        }

        if (isset($referenceMap[$dependence]) && isset($primaryKeys[1])) {

            $dependenceTableName = $referenceMap[$dependence]['refTableClass'];
            $dependenceTable = new $dependenceTableName();

            $select = $this->getTable()->select();

            $subSelect = $this->getTable()->select();
            $subSelect->distinct();
            $subSelect->from(array('self' => $this->getTable()->getTableName()), array($primaryKeys[1]));
            $subSelect->joinInner(
                array($dependence => $dependenceTable->getTableName()),
                sprintf('%s.%s = %s.%s', 'self', $referenceMap[$dependence]['columns'], $dependence, $referenceMap[$dependence]['refColumns']),
                array()
            );
            if (null != $where) {
                $subSelect->where($where);
            }

            if (null != $order) {
                if (is_string($order)) {
                    if (false !== strstr($order, $dependence.'.')) {
                        $subSelect->order($order);
                        $order = null;
                    }
                } elseif (is_array($order)) {
                    $subSelectOrder = array();
                    foreach($order as $orderIndex => $orderValue) {
                        if (false !== strstr($orderValue, $dependence.'.')) {
                            unset($order[$orderIndex]);
                            $subSelectOrder[] = $orderValue;
                        }
                    }
                    $subSelect->order($subSelectOrder);
                    if (!count($order)) {
                        $order = null;
                    }
                }
            }

            $select->from(array('self' => $this->getTable()->getTableName()));
            //$select->where('self.'.$referenceMap[$dependence]['columns'].' IN ('.$subSelect.')');
            $select->where('self.'.$primaryKeys[1].' IN ('.$subSelect.')');

            if (null !== $order) {
                $select->order($order);
            }

            if (null !== $count) {
                $select->limit($count);
            }

            //$select->group('self.'.$referenceMap[$dependence]['columns']);
            $smtp = $select->query();
            $rows = new HM_Db_Table_Rowset(array('data' => $smtp->fetchAll()));
            $rows->setTable($this->getTable());
        } else {
            throw new HM_Exception(sprintf(_('Dependence %s not found'), $dependence));
        }
        return $rows;
    }

    public function countAllDependenceJoinInner($dependence = null, $where = null)
    {
        $count = 0;
        $referenceMap = $this->getTable()->getReferenceMap();
        if (isset($referenceMap[$dependence])) {
            $dependenceTableName = $referenceMap[$dependence]['refTableClass'];
            $dependenceTable = new $dependenceTableName();

            $select = $this->getTable()->select();

            $subSelect = $this->getTable()->select();
            $subSelect->distinct();
            $subSelect->from(array('self' => $this->getTable()->getTableName()), array($referenceMap[$dependence]['columns']));
            $subSelect->joinInner(
                array($dependence => $dependenceTable->getTableName()),
                sprintf('%s.%s = %s.%s', 'self', $referenceMap[$dependence]['columns'], $dependence, $referenceMap[$dependence]['refColumns']),
                array()
            );
            if (null != $where) {
                $subSelect->where($where);
            }

            $select->from(array('self' => $this->getTable()->getTableName()), array('cnt' => new Zend_Db_Expr(sprintf('COUNT(%s)', 'self.'.$referenceMap[$dependence]['columns']))));
            $select->where('self.'.$referenceMap[$dependence]['columns'].' IN ('.$subSelect.')');

            //$select->group('self.'.$referenceMap[$dependence]['columns']);
            $smtp = $select->query();
            $data = $smtp->fetch();
            $count = $data['cnt'];
        } else {
            throw new HM_Exception(sprintf(_('Dependence %s not found'), $dependence));
        }
        ;

        return $count;

    }

    protected function _fetchManyToMany($dependence, $intersection, $rows) {
        $dependences = array();

        if (count($rows)) {
            $referenceMap = $this->getTable()->getReferenceMap();

            if (is_array($dependence) && count($dependence) && is_array($intersection) && count($intersection)) {
                // @todo fetch array dependences
            } elseif (is_string($dependence) && is_string($intersection)) {
                $propertyName = $intersection;
                if (isset($referenceMap[$intersection]['propertyName'])) {
                    $propertyName = $referenceMap[$intersection]['propertyName'];
                }

                if (isset($referenceMap[$intersection]['refTableClass'])) {
                    $intersectionTableName = $referenceMap[$intersection]['refTableClass'];

                    $intersectionTable = new $intersectionTableName();

                    $intersectionReferenceMap = $intersectionTable->getReferenceMap();

                    if (isset($intersectionReferenceMap[$dependence]['propertyName'])) {
                        $propertyName = $intersectionReferenceMap[$dependence]['propertyName'];
                    }

                    if (isset($intersectionReferenceMap[$dependence]['refTableClass'])) {
                        $dependenceTableName = $intersectionReferenceMap[$dependence]['refTableClass'];

                        $refRows = $rows->findManyToManyRowset($dependenceTableName, $intersectionTableName);

                        if (count($refRows)) {
                            $columns = $referenceMap[$intersection]['columns'];
                            $refColumns = $referenceMap[$intersection]['refColumns'];
                            $refClass = substr($intersectionReferenceMap[$dependence]['refTableClass'], 0, -5) . 'Model'; // Trim Table and add Model
                            foreach($refRows as $refRow) {
                                $dependences[$columns][$refRow->$refColumns][$propertyName][] = array('row' => $refRow->toArray(), 'refClass' => $refClass);
                            }
                        }
                    }
                }
            }
        }

        return $dependences;
    }

    public function findManyToMany()
    {
        $args = func_get_args();

        if (isset($args[0])) {
            $dependence = $args[0];
            unset($args[0]);
        }

        if (isset($args[1])) {
            $intersection = $args[1];
            unset($args[1]);
        }


        $rows = call_user_func_array(array($this->getTable(), 'find'), $args);

        $dependences = $this->_fetchManyToMany($dependence, $intersection, $rows);

        return $this->_createModel($rows, &$dependences);
    }

    public function fetchAllManyToMany($dependence = null, $intersection = null, $where = null, $order = null, $count = null, $offset = null)
    {
        $rows = $this->getTable()->fetchAll($where, $order, $count, $offset);

        $dependences = $this->_fetchManyToMany($dependence, $intersection, $rows);

        return $this->_createModel($rows, $dependences);
    }

    public function fetchAllHybrid($dependence = null, $ManyToManyDependence = null, $ManyToManyIntersection = null, $where = null, $order = null, $count = null, $offset = null)
    {
        $rows = $this->getTable()->fetchAll($where, $order, $count, $offset);
        $dependences = $this->_fetchManyToMany($ManyToManyDependence, $ManyToManyIntersection, $rows);
        $dependences = $this->_fetchDependences($dependence, $rows, $dependences);

        return $this->_createModel($rows, $dependences);
    }

    public function setPaginatorOptions($options)
    {
        $this->_paginatorOptions = $options;
    }

    public function count()
    {
        if (null == $this->_paginatorOptions) {
            return 0;
        }

        $select = $this->getTable()->select()->from($this->getTable()->getTableName(), array('cnt' => 'COUNT(*)'))->where($this->_paginatorOptions['where']);
        $smtp = $this->getTable()->getAdapter()->query($select);
        while($row = $smtp->fetch()) {
            return $row['cnt'];
        }
        return 0;
    }

    public function getItems($offset, $itemCountPerPage)
    {
        if (null == $this->_paginatorOptions) {
            return array();
        }

        if (null != $this->_paginatorOptions['mtm_dependence']) {
            return $this->fetchAllHybrid(
                $this->_paginatorOptions['dependence'],
                $this->_paginatorOptions['mtm_dependence'],
                $this->_paginatorOptions['intersection'],
                $this->_paginatorOptions['where'],
                $this->_paginatorOptions['order'],
                $itemCountPerPage,
                $offset
            );
        }


        if (null != $this->_paginatorOptions['intersection']) {
            return $this->fetchAllManyToMany(
                $this->_paginatorOptions['dependence'],
                $this->_paginatorOptions['intersection'],
                $this->_paginatorOptions['where'],
                $this->_paginatorOptions['order'],
                $itemCountPerPage,
                $offset
            );
        }

        if (null != $this->_paginatorOptions['dependence']) {
            return $this->fetchAllDependence(
                $this->_paginatorOptions['dependence'],
                $this->_paginatorOptions['where'],
                $this->_paginatorOptions['order'],
                $itemCountPerPage,
                $offset
            );
        }

        return $this->fetchAll(
            $this->_paginatorOptions['where'],
            $this->_paginatorOptions['order'],
            $itemCountPerPage,
            $offset
        );
    }

    protected function filterInt(&$string){
        $string = preg_replace("/[^0-9]/", '', $string);
    }
    
}