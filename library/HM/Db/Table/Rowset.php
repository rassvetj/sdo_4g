<?php

class HM_Db_Table_Rowset extends Zend_Db_Table_Rowset
{
    protected $_rowClass = 'HM_Db_Table_Row';

    protected function _getTableFromString($tableName)
    {

        if ($this->_table instanceof Zend_Db_Table_Abstract) {
            $tableDefinition = $this->_table->getDefinition();

            if ($tableDefinition !== null && $tableDefinition->hasTableConfig($tableName)) {
                return new Zend_Db_Table($tableName, $tableDefinition);
            }
        }

        // assume the tableName is the class name
        if (!class_exists($tableName)) {
            try {
                require_once 'Zend/Loader.php';
                Zend_Loader::loadClass($tableName);
            } catch (Zend_Exception $e) {
                require_once 'Zend/Db/Table/Row/Exception.php';
                throw new Zend_Db_Table_Row_Exception($e->getMessage());
            }
        }

        $options = array();

        if (($table = $this->getTable())) {
            $options['db'] = $table->getAdapter();
        }

        if (isset($tableDefinition) && $tableDefinition !== null) {
            $options[Zend_Db_Table_Abstract::DEFINITION] = $tableDefinition;
        }

        return new $tableName($options);
    }

    /**
     * Prepares a table reference for lookup.
     *
     * Ensures all reference keys are set and properly formatted.
     *
     * @param Zend_Db_Table_Abstract $dependentTable
     * @param Zend_Db_Table_Abstract $parentTable
     * @param string                 $ruleKey
     * @return array
     */
    protected function _prepareReference(Zend_Db_Table_Abstract $dependentTable, Zend_Db_Table_Abstract $parentTable, $ruleKey)
    {
        $parentTableName = (get_class($parentTable) === 'Zend_Db_Table') ? $parentTable->getDefinitionConfigName() : get_class($parentTable);
        $map = $dependentTable->getReference($parentTableName, $ruleKey);

        if (!isset($map[Zend_Db_Table_Abstract::REF_COLUMNS])) {
            $parentInfo = $parentTable->info();
            $map[Zend_Db_Table_Abstract::REF_COLUMNS] = array_values((array) $parentInfo['primary']);
        }

        $map[Zend_Db_Table_Abstract::COLUMNS] = (array) $map[Zend_Db_Table_Abstract::COLUMNS];
        $map[Zend_Db_Table_Abstract::REF_COLUMNS] = (array) $map[Zend_Db_Table_Abstract::REF_COLUMNS];

        return $map;
    }    

    public function findDependentRowset($dependentTable, $ruleKey = null, Zend_Db_Table_Select $select = null)
    {
        $db = $this->getTable()->getAdapter();

        if (is_string($dependentTable)) {
            $dependentTable = $this->_getTableFromString($dependentTable);
        }

        if (!$dependentTable instanceof Zend_Db_Table_Abstract) {
            $type = gettype($dependentTable);
            if ($type == 'object') {
                $type = get_class($dependentTable);
            }
            require_once 'Zend/Db/Table/Row/Exception.php';
            throw new Zend_Db_Table_Row_Exception("Dependent table must be a Zend_Db_Table_Abstract, but it is $type");
        }

        // even if we are interacting between a table defined in a class and a
        // table via extension, ensure to persist the definition
        if (($tableDefinition = $this->_table->getDefinition()) !== null
            && ($dependentTable->getDefinition() == null)) {
            $dependentTable->setOptions(array(Zend_Db_Table_Abstract::DEFINITION => $tableDefinition));
        }

        if ($select === null) {
            $select = $dependentTable->select();
        } else {
            $select->setTable($dependentTable);
        }

        $map = $this->_prepareReference($dependentTable, $this->getTable(), $ruleKey);

        for ($i = 0; $i < count($map[Zend_Db_Table_Abstract::COLUMNS]); ++$i) {
            $parentColumnName = $db->foldCase($map[Zend_Db_Table_Abstract::REF_COLUMNS][$i]);
            //$value = $this->_data[$parentColumnName];
            // Use adapter from dependent table to ensure correct query construction
            $dependentDb = $dependentTable->getAdapter();
            $dependentColumnName = $dependentDb->foldCase($map[Zend_Db_Table_Abstract::COLUMNS][$i]);
            $dependentColumn = $dependentDb->quoteIdentifier($dependentColumnName, true);
            $dependentInfo = $dependentTable->info();
            //$type = $dependentInfo[Zend_Db_Table_Abstract::METADATA][$dependentColumnName]['DATA_TYPE'];
            //$select->where("$dependentColumn = ?", $value, $type);
            if (is_array($this->_data) && count($this->_data)) {
                $count = 0;
                foreach($this->_data as $item) {
                    $value = $item[$parentColumnName];
                    $type = $dependentInfo[Zend_Db_Table_Abstract::METADATA][$dependentColumnName]['DATA_TYPE'];
                    if ($count > 0) {
                        if (!isset($value)) $value = 0;
                        $select->orWhere("$dependentColumn = ?", $value, $type);
                    } else {
                        if (!isset($value)) $value = 0;
                        $select->where("$dependentColumn = ?", $value, $type);
                    }
                    $count++;
                }
            } else {
                $select->where('1 = 0');
            }
        }
        return $dependentTable->fetchAll($select);
    }

    /**
     * @param  string|Zend_Db_Table_Abstract  $matchTable
     * @param  string|Zend_Db_Table_Abstract  $intersectionTable
     * @param  string                         OPTIONAL $callerRefRule
     * @param  string                         OPTIONAL $matchRefRule
     * @param  Zend_Db_Table_Select           OPTIONAL $select
     * @return Zend_Db_Table_Rowset_Abstract Query result from $matchTable
     * @throws Zend_Db_Table_Row_Exception If $matchTable or $intersectionTable is not a table class or is not loadable.
     */
    public function findManyToManyRowset($matchTable, $intersectionTable, $callerRefRule = null,
                                         $matchRefRule = null, Zend_Db_Table_Select $select = null)
    {
        $db = $this->getTable()->getAdapter();

        if (is_string($intersectionTable)) {
            $intersectionTable = $this->_getTableFromString($intersectionTable);
        }

        if (!$intersectionTable instanceof Zend_Db_Table_Abstract) {
            $type = gettype($intersectionTable);
            if ($type == 'object') {
                $type = get_class($intersectionTable);
            }
            require_once 'Zend/Db/Table/Row/Exception.php';
            throw new Zend_Db_Table_Row_Exception("Intersection table must be a Zend_Db_Table_Abstract, but it is $type");
        }

        // even if we are interacting between a table defined in a class and a
        // table via extension, ensure to persist the definition
        if (($tableDefinition = $this->_table->getDefinition()) !== null
            && ($intersectionTable->getDefinition() == null)) {
            $intersectionTable->setOptions(array(Zend_Db_Table_Abstract::DEFINITION => $tableDefinition));
        }

        if (is_string($matchTable)) {
            $matchTable = $this->_getTableFromString($matchTable);
        }

        if (! $matchTable instanceof Zend_Db_Table_Abstract) {
            $type = gettype($matchTable);
            if ($type == 'object') {
                $type = get_class($matchTable);
            }
            require_once 'Zend/Db/Table/Row/Exception.php';
            throw new Zend_Db_Table_Row_Exception("Match table must be a Zend_Db_Table_Abstract, but it is $type");
        }

        // even if we are interacting between a table defined in a class and a
        // table via extension, ensure to persist the definition
        if (($tableDefinition = $this->_table->getDefinition()) !== null
            && ($matchTable->getDefinition() == null)) {
            $matchTable->setOptions(array(Zend_Db_Table_Abstract::DEFINITION => $tableDefinition));
        }

        if ($select === null) {
            $select = $matchTable->select();
        } else {
            $select->setTable($matchTable);
        }

        // Use adapter from intersection table to ensure correct query construction
        $interInfo = $intersectionTable->info();
        $interDb   = $intersectionTable->getAdapter();
        $interName = $interInfo['name'];
        $interSchema = isset($interInfo['schema']) ? $interInfo['schema'] : null;
        $matchInfo = $matchTable->info();
        $matchName = $matchInfo['name'];
        $matchSchema = isset($matchInfo['schema']) ? $matchInfo['schema'] : null;

        $matchMap = $this->_prepareReference($intersectionTable, $matchTable, $matchRefRule);

        for ($i = 0; $i < count($matchMap[Zend_Db_Table_Abstract::COLUMNS]); ++$i) {
            $interCol = $interDb->quoteIdentifier($intersectionTable->getTableName() . '.' . $matchMap[Zend_Db_Table_Abstract::COLUMNS][$i], true);
            $matchCol = $interDb->quoteIdentifier($matchTable->getTableName() . '.' . $matchMap[Zend_Db_Table_Abstract::REF_COLUMNS][$i], true);
            $joinCond[] = "$interCol = $matchCol";
        }
        $joinCond = implode(' AND ', $joinCond);

        $select->from(array($intersectionTable->getTableName() => $interName), Zend_Db_Select::SQL_WILDCARD, $interSchema)
               ->joinInner(array($matchTable->getTableName() => $matchName), $joinCond, Zend_Db_Select::SQL_WILDCARD, $matchSchema)
               ->setIntegrityCheck(false);

        $callerMap = $this->_prepareReference($intersectionTable, $this->getTable(), $callerRefRule);

        for ($i = 0; $i < count($callerMap[Zend_Db_Table_Abstract::COLUMNS]); ++$i) {
            $callerColumnName = $db->foldCase($callerMap[Zend_Db_Table_Abstract::REF_COLUMNS][$i]);
            //$value = $this->_data[$callerColumnName];
            $interColumnName = $interDb->foldCase($callerMap[Zend_Db_Table_Abstract::COLUMNS][$i]);
            $interCol = $interDb->quoteIdentifier($intersectionTable->getTableName().".$interColumnName", true);
            $interInfo = $intersectionTable->info();
            //$type = $interInfo[Zend_Db_Table_Abstract::METADATA][$interColumnName]['DATA_TYPE'];
            //$select->where($interDb->quoteInto("$interCol = ?", $value, $type));
            if (is_array($this->_data) && count($this->_data)) {
                $count = 0;
                foreach($this->_data as $item) {
                    $value = $item[$callerColumnName];
                    $type = $interInfo[Zend_Db_Table_Abstract::METADATA][$interColumnName]['DATA_TYPE'];;
                    if ($count > 0) {
                        $select->orWhere("$interCol = ?", $value, $type);
                    } else {
                        $select->where("$interCol = ?", $value, $type);
                    }
                    $count++;
                }
            } else {
                $select->where('1 = 0');
            }
        }
        $stmt = $select->query();

        $config = array(
            'table'    => $matchTable,
            'data'     => $stmt->fetchAll(Zend_Db::FETCH_ASSOC),
            'rowClass' => $matchTable->getRowClass(),
            'readOnly' => false,
            'stored'   => true
        );

        $rowsetClass = $matchTable->getRowsetClass();
        if (!class_exists($rowsetClass)) {
            try {
                require_once 'Zend/Loader.php';
                Zend_Loader::loadClass($rowsetClass);
            } catch (Zend_Exception $e) {
                require_once 'Zend/Db/Table/Row/Exception.php';
                throw new Zend_Db_Table_Row_Exception($e->getMessage());
            }
        }
        $rowset = new $rowsetClass($config);
        return $rowset;
    }
}
