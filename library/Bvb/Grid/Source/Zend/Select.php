<?php

/**
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license
 * It is  available through the world-wide-web at this URL:
 * http://www.petala-azul.com/bsd.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to geral@petala-azul.com so we can send you a copy immediately.
 *
 * @package    Bvb_Grid
 * @copyright  Copyright (c)  (http://www.petala-azul.com)
 * @license    http://www.petala-azul.com/bsd.txt   New BSD License
 * @version    $Id: Select.php 1197 2010-05-24 15:30:12Z bento.vilas.boas@gmail.com $
 * @author     Bento Vilas Boas <geral@petala-azul.com >
 */


class Bvb_Grid_Source_Zend_Select extends Bvb_Grid_Source_Db_DbAbstract implements Bvb_Grid_Source_SourceInterface
{

    protected $_select;

    protected $_server;

    protected $_describeTables;

    protected $_cache;

    protected $_fields;

    protected $_fixedRows=array();

    protected $_fixedPk;


    protected $_shotCut=false;

    protected $_totalRecords = null;

    protected $_primaryKey = null;

    /**
     * @param $_shotCut the $_shotCut to set
     */
    public function setShotCut ($_shotCut)
    {
        $this->_shotCut = $_shotCut;
    }
     /**
     * @param $_fixedPk the $_fixedPk to set
     */
	public function setFixedPk ($_fixedPk)
    {
        $this->_fixedPk = $_fixedPk;
    }
 	/**
     *
     * @param $_fixedRows Массив с id фиксированных строк
     */
	public function setFixedRows (array $_fixedRows)
    {
        $this->_fixedRows = $_fixedRows;
    }

	public function __construct (Zend_Db_Select $select)
    {

        $this->_select = $select;
        $this->init($this->_select);
        return $this;
    }


    /**
     * Define the query using Zend_Db_Select instance
     *
     * @param Zend_Db_Select $select
     * @return $this
     */
    public function init (Zend_Db_Select $select)
    {
        $this->_setDb($select->getAdapter());
        $adapter = get_class($select->getAdapter());
        $adapter = str_replace("Zend_Db_Adapter_", "", $adapter);

        if ( stripos($adapter, 'mysql') !== false ) {
            $this->_server = 'mysql';
        } else {
            $adapter = str_replace('Pdo_', '', $adapter);
            $this->_server = strtolower($adapter);
        }

        return $this;
    }


    /**
     * Set db
     * @param Zend_Db_Adapter_Abstract $db
     */
    protected function _setDb (Zend_Db_Adapter_Abstract $db)
    {
        $this->_db = $db;
        return $this;
    }


    public function hasCrud ()
    {
        return true;
    }


    public function getRecord ($table, array $condition)
    {

        $select = new Zend_Db_Select($this->_getDb());
        $select->from($table);

        foreach ( $condition as $field => $value ) {

            if ( stripos($field, '.') !== false ) {
                $field = substr($field, stripos($field, '.') + 1);
            }

            $select->where($field . '=?', $value);
        }

        if ( $this->_cache['use'] == 1 ) {
            $hash = 'Bvb_Grid' . md5($select->__toString());
            if ( ! $result = $this->_cache['instance']->load($hash) ) {
                $final = $select->query(Zend_Db::FETCH_ASSOC);
                $return = $final->fetchAll();
                $this->_cache['instance']->save($result, $hash, array($this->_cache['tag']));
            }
        } else {
            $final = $select->query(Zend_Db::FETCH_ASSOC);
            $return = $final->fetchAll();
        }

        $final = array();

        foreach ( $return[0] as $key => $value ) {
            $final[$key] = $value;
        }

        if ( count($final) == 0 ) {
            return false;
        }

        return $final;
    }


    /**
     * Build the fields based on Zend_Db_Select
     * @param $fields
     * @param $tables
     */
    public function buildFields ()
    {

        $fields = $this->_select->getPart(Zend_Db_Select::COLUMNS);
        $tables = $this->_select->getPart(Zend_Db_Select::FROM);

        $returnFields = array();

        foreach ( $fields as $field => $value ) {
            /**
             * Select all fields from the table
             */
            if ( (string) $value[1] == '*' ) {

                if ( array_key_exists($value[0], $tables) ) {
                    $tableFields = $this->getDescribeTable($tables[$value[0]]['tableName']);
                }
                $tableFields = array_keys($tableFields);

                foreach ( $tableFields as $field ) {
                    $title = ucwords(str_replace('_', ' ', $field));
                    $returnFields[$field] = array('title' => $title, 'field' => $value[0] . '.' . $field);
                }

            } else {
                if ( is_object($value[1]) ) {
                    $title = ucwords(str_replace('_', ' ', $value[2]));
                    $returnFields[$value[2]] = array('title' => $title, 'field' => $value[0] . '.' . $value[2]);
                } elseif ( strlen($value[2]) > 0 ) {
                    $title = ucwords(str_replace('_', ' ', $value[2]));
                    $returnFields[$value[2]] = array('title' => $title, 'field' => $value[0] . '.' . $value[1]);
                } else {
                    $title = ucwords(str_replace('_', ' ', $value[1]));
                    $returnFields[$value[1]] = array('title' => $title, 'field' => $value[0] . '.' . $value[1]);
                }
            }
        }

        if (! empty($this->_fixedRows))
        {
            $returnFields['fixType'] = array(
                'title' => 'fixType',
                'field' => 'fixType');
        }



        $this->_fields = $returnFields;

        return $returnFields;

    }


    /**
     * Get table description and then save it to a array.
     *
     * @param array|string $table
     * @return array
     */
    public function getDescribeTable ($table)
    {
        if($table =="fixType" || $table instanceof Zend_Db_Select){
            return;
        }

        if ( ! isset($this->_describeTables[$table]) || ! is_array($this->_describeTables[$table]) ) {

            if ( $this->_cache['use'] == 1 ) {
                $hash = 'Bvb_Grid' . md5($table);
                if ( ! $result = $this->_cache['instance']->load($hash) ) {
                    $result = $this->_getDb()->describeTable($table);
                    $this->_cache['instance']->save($result, $hash, array($this->_cache['tag']));
                }

            } else {
                $result = $this->_getDb()->describeTable($table);
            }
            $this->_describeTables[$table] = $result;
        }

        return $this->_describeTables[$table];
    }


    public function _getDb ()
    {
        return $this->_db;
    }


    public function execute ()
    {
/*        // Для зафиксированных строк
        if (! empty($this->_fixedRows))
        {
            $in = implode(",", $this->_fixedRows);
            $sel1 = clone $this->_select;
            $sel2 = clone $this->_select;

            $fixedSelect = clone $this->_select;
            $sel1->columns(array(
                'fixType' => new Zend_Db_Expr("'unfix'")));

            $sel2->columns(array(
                'fixType' => new Zend_Db_Expr("'fix'")));
            $sel2->reset('limit');

            $sel1->where($this->_fixedPk . ' NOT IN (' . $in . ')');
            $sel2->reset('where');
            $sel2->where($this->_fixedPk . ' IN (' . $in . ')');

            $countMain = $sel1->getPart(Zend_Db_Select::LIMIT_COUNT);
            $offsetMain = $sel1->getPart(Zend_Db_Select::LIMIT_OFFSET);

            $temp = $sel2->getPart(Zend_Db_Select::FROM);
            $columns= $sel2->getPart(Zend_Db_Select::COLUMNS);

            $sel2->reset(Zend_Db_Select::FROM);
            $sel2->reset(Zend_Db_Select::COLUMNS);

            foreach($temp as $key => $value){
                if($value['joinType'] == Zend_Db_Select::FROM){
                    $sel2->from(array($key => $value['tableName']));
                }elseif($value['joinType'] == Zend_Db_Select::INNER_JOIN){
                    $sel2->joinInner(array($key => $value['tableName']), $value['joinCondition']);
                }
                elseif($value['joinType'] == Zend_Db_Select::LEFT_JOIN){
                    $sel2->joinLeft(array($key => $value['tableName']), $value['joinCondition']);
                }
             }


            $sel2->reset(Zend_Db_Select::COLUMNS);
            foreach($columns as $value){

                if($value[1] instanceof Zend_Db_Expr){
                    $sel2->columns(array($value[2]=>$value[1]));
                }else{
                    if($value[2] != ''){
                        $sel2->columns(array($value[2] => $value[0].'.'.$value[1]));
                    }else{
                        $sel2->columns(array($value[0].'.'.$value[1]));
                    }
                }
            }

            // some hack here...
            $order = '';
            if($countMain != 0){
                $sel2->limit(1000000, 0);

                $orderPart = $sel2->getPart(Zend_Db_Select::ORDER);
                if ($orderPart) {
                    $order = $orderPart[0][0] . ' ' . $orderPart[0][1];
                }
                $sel1->reset(Zend_Db_Select::ORDER);
                $sel2->reset(Zend_Db_Select::ORDER);
            }else{
                $sel1->reset(Zend_Db_Select::ORDER);
                $orderPart = $sel2->getPart(Zend_Db_Select::ORDER);
                if ($orderPart) {
                    $order = $orderPart[0][0] . ' ' . $orderPart[0][1];
                }
                $sel2->reset(Zend_Db_Select::ORDER);
                //$sel2->order(array('fixType', $order));
                $sel1->reset(Zend_Db_Select::ORDER);
                if($offsetMain != 0){
                    //$sel1->order(array($order));
                }
            }
            $fixedSelect->reset();
            $fixedSelect->union(array(
                                      $sel2 ,
                                      $sel1
                                ),
                                Zend_Db_select::SQL_UNION_ALL);

            $hackSelect = clone $this->_select;
            $hackSelect->reset();
            $hackSelect->from($fixedSelect);
            if (strlen($order)) {
                $sel1->order(array($order));
            }
/*             else {
                $hackSelect->order(array('fixType'));
            }* /

            $final = $fixedSelect->query(Zend_Db::FETCH_ASSOC);
        } else
        {
            $final = $this->_select->query(Zend_Db::FETCH_ASSOC);
        }*/

        if (! empty($this->_fixedRows))
        {
            $in = implode(",", $this->_fixedRows);
            $fixedSelect = clone $this->_select;
            $fixType=false;
            $select_columns = $this->_select->getPart(Zend_Db_Select::COLUMNS);
            while(count($select_columns)){
                if(in_array("fixType",array_shift($select_columns))){
                    $fixType=true;
                    break;
                }
            }
            if(!$fixType)
                $this->_select->columns(array(
                    'fixType' => new Zend_Db_Expr("'unfix'")));

            $fixedSelect->columns(array(
                'fixType' => new Zend_Db_Expr("'fix'")));
            $fixedSelect->reset('limit');

            $this->_select->where($this->_fixedPk . ' NOT IN (' . $in . ')');
            $fixedSelect->reset('where');
            $fixedSelect->where($this->_fixedPk . ' IN (' . $in . ')');

            $fixedSelect->reset(Zend_Db_Select::LIMIT_COUNT);
            $fixedSelect->reset(Zend_Db_Select::LIMIT_OFFSET);

             $final = $this->_select->query(Zend_Db::FETCH_ASSOC);
        }else{
            $final = $this->_select->query(Zend_Db::FETCH_ASSOC);
        }

        if ( $this->_cache['use'] == 1 ) {
            $hash = 'Bvb_Grid' . md5($this->_select->__toString());
            if ( ! $result = $this->_cache['instance']->load($hash) ) {
                $result = $final->fetchAll();
                $this->_cache['instance']->save($result, $hash, array($this->_cache['tag']));
            }
        } else {
            $result = $final->fetchAll();
        }

        if(!empty($this->_fixedRows)){
            //Подумать потом как лучше. Пока так.
            $sqlString = (string) $fixedSelect;
            $sqlString = str_replace('INNER JOIN', 'LEFT JOIN', $sqlString);
            $fetch = $fixedSelect->getAdapter()->query($sqlString)->fetchAll();

            $result = array_merge($fetch, $result);
        }
        return $result;
    }


    public function fetchDetail (array $where)
    {

        foreach ( $where as $field => $value ) {

            if ( array_key_exists($field, $this->_fields) ) {
                $field = $this->_fields[$field]['field'];
            }
            $this->_select->where($field . '= ?', $value);
        }

        $this->_select->reset(Zend_Db_Select::LIMIT_COUNT);
        $this->_select->reset(Zend_Db_Select::LIMIT_OFFSET);

        if ( $this->_cache['use'] == 1 ) {
            $hash = 'Bvb_Grid' . md5($this->_select->__toString());
            if ( ! $result = $this->_cache['instance']->load($hash) ) {
                $final = $this->_select->query(Zend_Db::FETCH_ASSOC);
                $result = $final->fetchAll();
                $this->_cache['instance']->save($result, $hash, array($this->_cache['tag']));
            }
        } else {
            $final = $this->_select->query(Zend_Db::FETCH_ASSOC);
            $result = $final->fetchAll();
        }


        if ( ! isset($result[0]) ) {
            return false;
        }

        return $result[0];
    }


    /**
     * Count the rows total without the limit
     *
     * @return void
     */
    public function getTotalRecords ()
    {
        if($this->_totalRecords!==null  && empty($this->_fixedRows)){
            return $this->_totalRecords;
        }


        $hasExp = false;

        $selectCount = clone $this->_select;

        foreach ( $selectCount->getPart('columns') as $value ) {
            if ( $value[1] instanceof Zend_Db_Expr ) {
               $hasExp = true;
               break;
            }
        }

        $selectCount->reset(Zend_Db_Select::LIMIT_OFFSET);
        $selectCount->reset(Zend_Db_Select::LIMIT_COUNT);
        $selectCount->reset(Zend_Db_Select::ORDER);

        if($hasExp == false)
        {
            $selectCount->reset(Zend_Db_Select::COLUMNS);
            $selectCount->columns(new Zend_Db_Expr('COUNT(*) AS TOTAL '));
        }else{

            $select = $selectCount->getAdapter()->select();
            $select->from(array('temp' => $selectCount), array('TOTAL' => new Zend_Db_Expr('COUNT(*)') ));
            $selectCount = $select;


        }
        if ( $this->_cache['use'] == 1 ) {
            $hash = 'Bvb_Grid' . md5($selectCount->__toString());
            if ( ! $result = $this->_cache['instance']->load($hash) ) {
                $final = $selectCount->query(Zend_Db::FETCH_ASSOC);
                $result = $final->fetchAll();
                if ( count($result) > 1 ) {
                    $result = count($result);
                } else {
                    $result = $result[0]['total'];
                }
                $this->_cache['instance']->save($result, $hash, array($this->_cache['tag']));
            }
        } else {

            $final = $selectCount->query(Zend_Db::FETCH_ASSOC);
            $result = $final->fetchAll();
            if (count($result) > 1)
            {
                $result = count($result);
            } elseif (count($result) == 1)
            {

                // Это добавил я. А то что-то странное
                if ($result[0]['TOTAL'] === "")
                {
                    $result = 1;
                } else
                {
                    $result = $result[0]['TOTAL'];
                }
            } else
            {
                if(empty($this->_fixedPk)){
                    $result =  0;
                }else{
                    $result = count($this->_fixedPk);
                }
            }

        }
        $this->_totalRecords = $result;

        if(!empty($this->_fixedRows) && $result == 0){
            $result = 1;
        }


        return $result;
    }



    public function getMassActionsIds ($table)
    {
        $select = clone $this->_select;

        $select->reset(Zend_Db_Select::COLUMNS);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::GROUP);

        $pks = $this->getPrimaryKey($table);

        $from = $select->getPart(Zend_Db_Select::FROM);
        $prefix = '';
        if (count($from)) {
            foreach($from as $fromAlias => $fromTable) {
                if (strtolower($table) == strtolower($fromTable['tableName']) && $fromTable['joinType'] == Zend_Db_Select::FROM ) {
                    $prefix = $fromAlias.'.';
                }
            }
        }
        
        $groupFields = array();

        if ( count($pks) > 1 ) {
            $concat = array();

            foreach ( $pks as $conc ) {
                if (false === strstr($conc, $prefix)) {
                    $groupFields[] = $prefix.$conc;
                    $concat[] = $prefix.$this->_getDb()->quoteIdentifier($conc, true);
                } else {
                    $groupFields[] = $conc;
                    $concat[] = $this->_getDb()->quoteIdentifier($conc, true);
                }
                $concat[] = "'-'";
            }
            array_pop($concat);
        } else {
            if (false === strstr($pks[0], $prefix)) {
                $groupFields[] = $prefix.$pks[0];
                $concat = array($prefix.$this->_getDb()->quoteIdentifier($pks[0], true));
            } else {
                $groupFields[] = $pks[0];
                $concat = array($this->_getDb()->quoteIdentifier($pks[0], true));
            }
        }

        $strConcat = 'CONCAT('.join(", CONCAT(", $concat).", ''".str_repeat(")", count($concat));

        $select->columns(array('ids' => new Zend_Db_Expr("$strConcat")));
        $select->group($groupFields);

        $final = $select->query(Zend_Db::FETCH_ASSOC);
        $result = $final->fetchAll();

        $return = array();
        foreach ( $result as $value ) {
            $return[] = $value['ids'];
        }
        if(is_array($this->_fixedRows)){
            $return = array_merge($return, $this->_fixedRows);
        }
        return implode(',',$return);

    }

    public function getTableList ()
    {
        return $this->_select->getPart(Zend_Db_Select::FROM);
    }


    public function getFilterValuesBasedOnFieldDefinition ($field)
    {
        $tableList = $this->getTableList();

        $explode = explode('.', $field);
        $tableName = reset($explode);
        $field = end($explode);

        if ( array_key_exists($tableName, $tableList) ) {
            $tableName = $tableList[$tableName]['tableName'];
        }

        $table = $this->getDescribeTable($tableName);


        if ( ! isset($table[$field]) ) {
            return 'text';
        }
        $type = $table[$field]['DATA_TYPE'];

        $return = 'text';

        if ( substr($type, 0, 4) == 'enum' ) {
            preg_match_all('/\'(.*?)\'/', $type, $result);

            $return = array_combine($result[1], $result[1]);
        }


        return $return;
    }


    public function getFieldType ($field)
    {

        $tableList = $this->getTableList();

        $explode = explode('.', $field);
        $tableName = reset($explode);
        $field = end($explode);

        if ( array_key_exists($tableName, $tableList) ) {
            $tableName = $tableList[$tableName]['tableName'];
        }

        $table = $this->getDescribeTable($tableName);
        $type = $table[$field]['DATA_TYPE'];

        if ( substr($type, 0, 3) == 'set' ) {
            return 'set';
        }

        return $type;
    }


    public function getMainTable ()
    {
        $return = array();

        $from = $this->_select->getPart(Zend_Db_Select::FROM);

        foreach ( $from as $key => $tables ) {

            if ( $tables['joinType'] == 'from' || count($from) == 1 ) {
                $return['table'] = $tables['tableName'];
                break;
            }
        }

        if ( count($return) == 0 ) {
            $table = reset($from);
            $return['table'] = $table['tableName'];
        }

        return $return;
    }


    public function buildQueryOrder ($field, $order, $reset = false)
    {
        if(!array_key_exists($field,$this->_fields))
        {
            return $this;
        }

        foreach ( $this->_select->getPart(Zend_Db_Select::COLUMNS) as $col ) {
            if ( ($col[0] . '.' . $col[2] == $field) && is_object($col[1]) ) {
                $field = $col[2];
            }
        }

        if ( $reset === true ) {
            $this->_select->reset('order');
        }

        $this->_select->order($field . ' ' . $order);
        return $this;

    }


    public function buildQueryLimit ($start, $offset)
    {
        $this->_select->limit($start, $offset);
    }


    public function getSelectObject ()
    {
        return $this->_select;
    }


    public function getSelectOrder ()
    {

        $result = $this->_select->getPart(Zend_Db_Select::ORDER);

        if ( count($result) == 0 ) {
            return array();
        }

        return $result[0];
    }


    public function getDistinctValuesForFilters ($field, $fieldValue,$order = 'name ASC')
    {

        $distinct = clone $this->_select;

        $distinct->reset(Zend_Db_Select::COLUMNS);
        $distinct->reset(Zend_Db_Select::ORDER);
        $distinct->reset(Zend_Db_Select::LIMIT_COUNT);
        $distinct->reset(Zend_Db_Select::LIMIT_OFFSET);

        $distinct->columns(array('field' => new Zend_Db_Expr("DISTINCT({$field})")));
        $distinct->columns(array('value' => $fieldValue));
        $distinct->order($order);

        if ( $this->_cache['use'] == 1 ) {
            $hash = 'Bvb_Grid' . md5($distinct->__toString());
            if ( ! $result = $this->_cache['instance']->load($hash) ) {
                $result = $distinct->query(Zend_Db::FETCH_ASSOC);
                $result = $result->fetchAll();
                $this->_cache['instance']->save($result, $hash, array($this->_cache['tag']));
            }
        } else {
            $result = $distinct->query(Zend_Db::FETCH_ASSOC);
            $result = $result->fetchAll();
        }

        $final = array();

        foreach ( $result as $value ) {
            $final[$value['field']] = $value['value'];
        }

        return $final;
    }


    public function getSqlExp (array $value, $where = array())
    {


        $cols = array();
        foreach ( $this->_select->getPart('columns') as $col ) {
            if ( $col[1] instanceof Zend_Db_Expr ) {
                $cols[$col[2]] = $col[1]->__toString();
            }
        }


        if(array_key_exists($value['value'],$cols))
        {
            $value['value'] = $cols[$value['value']];
        }

        $valor = '';
        foreach ( $value['functions'] as $final ) {
            $valor .= $final . '(';
        }
        $valor .= $value['value'] . str_repeat(')', count($value['functions']));


        $select = clone $this->_select;
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);
        $select->reset(Zend_Db_Select::GROUP);
        $select->columns(new Zend_Db_Expr($valor . ' AS TOTAL'));

        foreach ($where as $key=>$value)
        {
            if(strlen(trim($value))<1)
            {
                continue;
            }
            $select->where($key.'=?',$value);
        }


        if ( $this->_cache['use'] == 1 ) {
            $hash = 'Bvb_Grid' . md5($select->__toString());
            if ( ! $result = $this->_cache['instance']->load($hash) ) {
                $final = $select->query(Zend_Db::FETCH_ASSOC);
                $result = $final->fetchColumn();
                $this->_cache['instance']->save($result, $hash, array($this->_cache['tag']));
            }
        } else {

            $final = $select->query(Zend_Db::FETCH_ASSOC);
            $result = $final->fetchColumn();
        }

        return $result;
    }


    public function getColumns ()
    {
        return $this->_select->getPart('columns');
    }


    public function addFullTextSearch ($filter, $field)
    {

        $full = $field['search'];

        if ( ! isset($full['indexes']) ) {
            $indexes = $field['field'];
        } elseif ( is_array($full['indexes']) ) {
            $indexes = implode(',', array_values($full['indexes']));
        } elseif ( is_string($full['indexes']) ) {
            $indexes = $full['indexes'];
        }

        $extra = isset($full['extra']) ? $full['extra'] : 'boolean';

        if ( ! in_array($extra, array('boolean', 'queryExpansion', false)) ) {
            throw new Bvb_Grid_Exception('Unrecognized value in extra key');
        }

        if ( $extra == 'boolean' ) {
            $extra = 'IN BOOLEAN MODE';
        } elseif ( $extra == 'queryExpansion' ) {
            $extra = ' WITH QUERY EXPANSION ';
        } else {
            $extra = '';
        }

        if ( $extra == 'IN BOOLEAN MODE' ) {
            $filter = preg_replace("/\s+/", " +", $this->_getDb()->quote(' ' . $filter));
        } else {
            $filter = $this->_getDb()->quote($filter);
        }

        $this->_select->where(new Zend_Db_Expr("MATCH ($indexes) AGAINST ($filter $extra) "));
        return;
    }


    public function addCondition ($filter, $op, $completeField)
    {

        $explode = explode('.', $completeField['field']);
        $field = end($explode);

        $simpleField = false;

        $columns = $this->getColumns();

        foreach ( $columns as $value ) {
            if ( $field == $value[2] ) {
                if ( is_object($value[1]) ) {
                    $field = $value[1]->__toString();
                    $simpleField = true;
                } else {
                    $field = $value[0] . '.' . $value[1];
                }
                break;
            } elseif ( $field == $value[0] ) {
                $field = $value[0] . '.' . $value[1];
                break;
            }
        }

        if ( strpos($field, '.') === false && $simpleField === false ) {
            $field = $completeField['field'];
        }

        /**
         * Reserved words from myslq dont contain any special charaters.
         * But select expressions may.
         *
         * SELECT IF(City.Population>500000,1,0)....
         *
         * We can not quoteIdentifier this fields...
         */

        if ( preg_match("/^[a-z_]$/i", $field) ) {
            $field = $this->_getDb()->quoteIdentifier($field);
        }

        $sqlOp = 'where';
        // Это нужно, чтобы функции перетаскиваать в having
        if (preg_match('/^COUNT(.*)$/iu', $field))
        {
            $sqlOp = 'having';
            //$this->_select->having($field . ' = ?', $filter);

            //return;
        }
        if (preg_match('/^\(COUNT(.*)\)$/iu', $field))
        {
            $sqlOp = 'having';
            //$this->_select->having($field . ' = ?', $filter);

            //return;
        }
        if (preg_match('/^SUM(.*)$/iu', $field))
        {
            $sqlOp = 'having';
            //$this->_select->having($field . ' = ?', $filter);

            //return;
        }
        if (preg_match('/^AVG(.*)$/iu', $field))
        {
            $sqlOp = 'having';
            //$this->_select->having($field . ' = ?', $filter);

            //return;
        }

        if (preg_match('/^GROUP_CONCAT(.*)$/iu', $field))
        {
            $sqlOp = 'having';
            //$this->_select->having($field . ' = ?', $filter);

            //return;
        }
        if (preg_match('/^MAX(.*)$/iu', $field))
        {
            $sqlOp = 'having';
            //$this->_select->having($field . ' = ?', $filter);

            //return;
        }
        if (preg_match('/^ROUND(.*)$/iu', $field))
        {
            $sqlOp = 'having';
        }
        if (preg_match('/^\(ROUND(.*)\)$/iu', $field))
        {
            $sqlOp = 'having';
        }


        switch ($op) {
            case 'equal':
            case '=':
                $this->_select->$sqlOp($field . ' = ?', $filter);
                break;
            case 'REGEX':
                $this->_select->$sqlOp($field . " REGEXP " . $this->_getDb()->quote($filter));
                break;
            case 'rlike':
                $this->_select->$sqlOp('LOWER('.$field.')' . " LIKE " . 'LOWER('. $this->_getDb()->quote($filter . "%").')');
                break;
            case 'llike':
                $this->_select->$sqlOp('LOWER('.$field.')' . " LIKE " . 'LOWER('.$this->_getDb()->quote("%" . $filter).')');
                break;
            case '>=':
                $this->_select->$sqlOp($field . " >= ?", $filter);
                break;
            case '>':
                $this->_select->$sqlOp($field . " > ?", $filter);
                break;
            case '<>':
            case '!=':
                $this->_select->$sqlOp($field . " <> ?", $filter);
                break;
            case '<=':
                $this->_select->$sqlOp($field . " <= ?", $filter);
                break;
            case '<':
                $this->_select->$sqlOp($field . " < ?", $filter);
                break;
            case 'IN':
                $filter = explode(',', $filter);
                $this->_select->$sqlOp($field . " IN  (?)", $filter);
                break;
            case 'range':
                $start = substr($filter, 0, strpos($filter, '<>'));
                $end = substr($filter, strpos($filter, '<>') + 2);
                $this->_select->$sqlOp($field . " between " . $this->_getDb()->quote($start) . " and " . $this->_getDb()->quote($end));
                break;
            case 'IS NULL':
               $this->_select->$sqlOp($field . " IS NULL");
            break;
            case 'like':
            default:
                $this->_select->$sqlOp('LOWER('.$field.')' . " LIKE " . 'LOWER('.$this->_getDb()->quote("%" . $filter . "%").')');
                break;
        }
    }

    /**
     * Returns server name (mysql|pgsql|etc)
     */
    public function getSourceName ()
    {
        return $this->_server;
    }


    public function insert ($table, array $post)
    {
        if ( $this->_cache['use'] == 1 ) {
            $this->_cache['instance']->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array($this->_cache['tag']));
        }
        return $this->_getDb()->insert($table, $post);
    }


    public function update ($table, array $post, array $condition)
    {
        if ( $this->_cache['use'] == 1 ) {
            $this->_cache['instance']->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array($this->_cache['tag']));
        }
        return $this->_getDb()->update($table, $post, $this->buildWhereCondition($condition));
    }


    public function delete ($table, array $condition)
    {
        if ( $this->_cache['use'] == 1 ) {
            $this->_cache['instance']->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array($this->_cache['tag']));
        }
        return $this->_getDb()->delete($table, $this->buildWhereCondition($condition));
    }


    public function buildWhereCondition (array $condition)
    {

        $where = '';
        foreach ( $condition as $field => $value ) {

            if ( stripos($field, '.') !== false ) {
                $field = substr($field, stripos($field, '.') + 1);
            }


            $where .= 'AND ' . $this->_getDb()->quoteIdentifier($field) . ' = ' . $this->_getDb()->quote($value) . ' ';
        }
        return " ( " . substr($where, 3) . " )";

    }


    public function resetOrder ()
    {
        $this->_select->reset('order');
    }

    /**
     * Позволяет предварительно отсортировать по любому полю из таблицы (целесообразно сортировать по скрытому полю), и только потом применить юзерскую сортировку.
     * Полезно, например, при сортирвке уч. записей по ФИО - чтобы показывать пустые в самом конце таблицы
     *
     * @param unknown_type $masterOrder
     */
    public function addMasterOrder ($masterOrder)
    {
        $order = $this->_select->getPart('order');
        $this->_select->reset('order');
        $this->_select->order(array($masterOrder, is_array($order[0]) ? implode(' ', $order[0]) : null));
    }


    public function setCache ($cache)
    {

        if ( ! is_array($cache) ) {
            $cache = array('use' => 0);
        }

        if ( isset($cache['use']['db']) && $cache['use']['db'] == 1 ) {
            $cache['use'] = 1;
        } else {
            $cache['use'] = 0;
        }

        $this->_cache = $cache;
    }


    public function  buildForm($fields = array())
    {
        $table = $this->getMainTable();
        $cols = $this->getDescribeTable($table['table']);

        return $this->buildFormElements($cols);

    }


    public function buildFormElements ($cols, $info = array())
    {
        $final = array();
        $form = array();

        $return = array();

        foreach ( $cols as $column => $detail ) {

            $label = ucwords(str_replace('_', ' ', $column));

            $next = false;

            if ( $detail['PRIMARY'] == 1 ) {
                continue;
            }

            if ( ! isset($info['referenceMap']) ) {
                $info['referenceMap'] = array();
            }

            if ( count($info['referenceMap']) > 0 ) {

                foreach ( $info['referenceMap'] as $dep ) {

                    if ( is_array($dep['columns']) && in_array($column, $dep['columns']) ) {
                        $refColumn = $dep['refColumns'][array_search($column, $dep['columns'])];
                    } elseif ( is_string($dep['columns']) && $column == $dep['columns'] ) {
                        $refColumn = $dep['refColumns'];
                    } else {
                        continue;
                    }

                    $t = new $dep['refTableClass']();

                    $in = $t->info();

                    if ( (count($in['cols']) == 1 && count($in['primary']) == 0) || count($in['primary']) > 1 ) {
                        throw new Exception('Columns:' . count($in['cols']) . ' Keys:' . count($in['primary']));
                        # break;
                    }

                    if ( count($in['primary']) == 1 ) {
                        $field1 = array_shift($in['primary']);
                        $field2 = $refColumn;
                    }

                    $final['values'][$column] = array();
                    $r = $t->fetchAll()->toArray();

                    if ( $detail['NULLABLE'] == 1 ) {
                        $final['values'][$column][""] = "-- Empty --";
                    }

                    foreach ( $r as $field ) {
                        $final['values'][$column][$field[$field1]] = $field[$field2];
                    }

                    $return[$column] = array('type' => 'select', 'label' => $label, 'default' => $final['values'][$column]);


                    $next = true;

                }

            }

            if ( $next === true ) {
                continue;
            }

            if ( stripos($detail['DATA_TYPE'], 'enum') !== false ) {
                preg_match_all('/\'(.*?)\'/', $detail['DATA_TYPE'], $result);

                $options = array();
                foreach ( $result[1] as $match ) {
                    $options[$match] = ucfirst($match);
                }

                $return[$column] = array('type' => 'select', 'label' => $label, 'required' => ($detail['NULLABLE'] == 1) ? false : true, 'default' => $options);

                continue;
            }

            if ( stripos($detail['DATA_TYPE'], 'set') !== false ) {
                preg_match_all('/\'(.*?)\'/', $detail['DATA_TYPE'], $result);

                $options = array();
                foreach ( $result[1] as $match ) {
                    $options[$match] = ucfirst($match);
                }

                $return[$column] = array('type' => 'multiSelect', 'label' => $label, 'required' => ($detail['NULLABLE'] == 1) ? false : true, 'default' => $options);
                continue;
            }

            switch ($detail['DATA_TYPE']) {

                case 'varchar':
                case 'char':
                    $length = $detail['LENGTH'];
                    $return[$column] = array('type' => 'smallText', 'length' => $length, 'label' => $label, 'required' => ($detail['NULLABLE'] == 1) ? false : true, 'default' => (! is_null($detail['DEFAULT']) ? $detail['DEFAULT'] : ""));
                    break;
                case 'date':
                    $return[$column] = array('type' => 'date', 'label' => $label, 'required' => ($detail['NULLABLE'] == 1) ? false : true, 'default' => (! is_null($detail['DEFAULT']) ? $detail['DEFAULT'] : ""));
                    break;
                case 'datetime':
                case 'timestamp':
                    $return[$column] = array('type' => 'datetime', 'label' => $label, 'required' => ($detail['NULLABLE'] == 1) ? false : true, 'default' => (! is_null($detail['DEFAULT']) ? $detail['DEFAULT'] : ""));
                    break;

                case 'text':
                case 'mediumtext':
                case 'longtext':
                case 'smalltext':
                    $return[$column] = array('type' => 'longtext', 'label' => $label, 'required' => ($detail['NULLABLE'] == 1) ? false : true, 'default' => (! is_null($detail['DEFAULT']) ? $detail['DEFAULT'] : ""));
                    break;

                case 'int':
                case 'bigint':
                case 'mediumint':
                case 'smallint':
                case 'tinyint':
                    $isZero = (! is_null($detail['DEFAULT']) && $detail['DEFAULT'] == "0") ? true : false;
                    $return[$column] = array('type' => 'number', 'label' => $label, 'required' => ($isZero == false && $detail['NULLABLE'] == 1) ? false : true, 'default' => (! is_null($detail['DEFAULT']) ? $detail['DEFAULT'] : ""));
                    break;

                case 'float':
                case 'decimal':
                case 'double':
                    $return[$column] = array('type' => 'decimal', 'label' => $label, 'required' => ($detail['NULLABLE'] == 1) ? false : true, 'default' => (! is_null($detail['DEFAULT']) ? $detail['DEFAULT'] : ""));
                    break;

                default:
                    break;
            }
        }


        return $this->buildFormElementsFromArray($return);
    }


    /**
     * Get the primary table key
     * This is important because we only allow edit, add or remove records
     * From tables that have on primary key
     *
     * @return array
     */
    public function getPrimaryKey ($table)
    {
        if ($this->_primaryKey !== null) return $this->_primaryKey;

        $views = array(
            'lessons' => array('SHEID')
        );

        if (isset($views[$table])) {
            $tb = $this->getTableList();
            foreach($views[$table] as $pkk) {
                foreach ( $tb as $key => $value ) {
                    if ( $value['tableName'] == $table ) {
                        $prefix = $this->_getCaseSensitiveKey($table, $key) . '.';
                        break;
                    }
                }
                $keys[] = $prefix . $this->_getCaseSensitiveKey($table, $pkk);
            }
            return $keys;
        }

        $pk = $this->getDescribeTable($table);
        $tb = $this->getTableList();
        $keys = array();
        foreach ( $pk as $pkk => $primary ) {
            if ( $primary['PRIMARY'] == 1 ) {

                foreach ( $tb as $key => $value ) {
                    if ( $value['tableName'] == $primary['TABLE_NAME'] ) {
                        $prefix = $this->_getCaseSensitiveKey($table, $key) . '.';
                        break;
                    }
                }
                $keys[] = $prefix . $this->_getCaseSensitiveKey($table, $pkk);
            }
        }

        return $keys;
    }

    protected function _getCaseSensitiveKey($table, $key) {
        $adapter = strtolower(get_class($this->_getDb()));
        $adapter = str_replace("zend_db_adapter_", "", $adapter);
        $adapter = str_replace("hm_db_adapter_", "", $adapter);

        if (in_array($adapter, array('pdo_oci', 'oracle'))) {
            foreach($GLOBALS['_arrFieldNames'] as $tableName => $tableFields) {
                if (strtolower($tableName) == strtolower($table)) {
                    foreach($tableFields as $field) {
                        if (strtolower($field) == strtolower($key)) {
                            return $field;
                        }
                    }
                }
            }
        }
        return $key;
    }

    public function setPrimaryKey($key)
    {
        $this->_primaryKey = $key;
    }
}