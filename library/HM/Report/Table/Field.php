<?php
class HM_Report_Table_Field extends HM_Model_Abstract
{
    private $_table = null;

    private $_options = array();

    public function setTable(HM_Report_Table $table)
    {
        $this->_table = $table;
    }

    /**
     * @return HM_Report_Table
     */
    public function getTable()
    {
        return $this->_table;
    }

    public function setOptions($options)
    {
        $this->_options = $options;
        if (isset($options['alias']) && strlen($options['alias'])) {
            $this->name = $options['alias'];
        }
    }

    public function setOption($key, $value)
    {
        $this->_options[$key] = $value;
    }

    public function getFunctions()
    {
        if (!isset($this->function)) {
            return array();
        }
        return $this->function;
    }

    public function getAggregation()
    {
        if (!isset($this->aggregation)) {
            return array();
        }
        return $this->aggregation;
        //return explode(',', $this->aggregation);
    }

    public function isAggregation()
    {
        if (isset($this->isAggregation) && $this->isAggregation) {
            return true;
        }

        $options = $this->getOptions();
        if (isset($options['aggregation']) && strlen($options['aggregation'])) {
            return true;
        }
        return false;
    }

    public function getOptions()
    {
        return $this->_options;
    }

    public function getOption($key)
    {
        return isset($this->_options[$key]) ? $this->_options[$key] : null;
    }

    public function getValuesList()
    {
        $ret = array();
        $values = explode(';', $this->values);
        foreach($values as $value) {
            $parts = explode(':', $value);
            $ret[$parts[0]] = $parts[1];
        }
        return $ret;
    }

    public function getColumn()
    {
        $field = $this->table.'.'.$this->field;
        if (isset($this->expression)) {
            $field = new Zend_Db_Expr($this->expression);
        }

        $options = $this->getOptions();

        if (isset($options['function']) && strlen($options['function'])) {
            $functions = $this->getFunctions();
            if (isset($functions[$options['function']])) {
                $function = $functions[$options['function']];
                if (isset($function['name'])) {
                    $field = new Zend_Db_Expr(sprintf("%s(%s)", strtoupper($function['name']), $field));
                }

                if (isset($function['expression'])) {
                    $field = new Zend_Db_Expr($function['expression']);
                }

                if (isset($function['type'])) {
                    $this->type = $function['type'];
                }
            }
        }

        if ($this->isAggregation()) {

            if ($options['aggregation'] == 'count_distinct') {
                $field = new Zend_Db_Expr(sprintf("COUNT(DISTINCT(%s))", $field)); // dirty hack
            } elseif ($options['aggregation'] == 'group_concat_distinct') {
                $field = new Zend_Db_Expr(sprintf("GROUP_CONCAT(DISTINCT(%s))", $field)); // dirty hack
            } else {
                $field = new Zend_Db_Expr(sprintf("%s(%s)", strtoupper($options['aggregation']), $field));
            }

            if ($options['aggregation'] == 'avg') {
                $field = new Zend_Db_Expr(sprintf("ROUND(%s, 2)", $field)); // dirty hack
            }
        }

        return array($this->name => $field);
    }

    public function getGroup($group)
    {
        if ($this->isAggregation()) {
              return array();
        }

        $field = $this->table.'.'.$this->field;
        $fields = array($field => $field);
        if (isset($this->expression)) {
            if (isset($this->expressionFields)) {
                $fields = array();
                $expressionFields = explode(',', $this->expressionFields);
                if (count($expressionFields)) {
                    foreach($expressionFields as $field) {
                        $field = trim($field);
                        if (!isset($fields[$field])) {
                            $field = $this->table.'.'.$field;
                            $fields[$field] = $field;
                        }
                    }
                }
            } else {
                $fields = array($this->expression => new Zend_Db_Expr($this->expression));
            }
        }

        $options = $this->getOptions();

        if (isset($options['function']) && strlen($options['function'])) {
            $functions = $this->getFunctions();
            if (isset($functions[$options['function']])) {
                //$fields = array($this->name => $this->name);
                $function = $functions[$options['function']];
                if (isset($function['expression'])) {
                    $fields = array($this->name => $function['expression']);
                }
            }
        }

        $return = array();
        foreach($fields as $field) {
            if (!in_array($field, $group)) {
                $return[] = $field;
            }
        }

        return $return;
    }

    public function getQuery(Zend_Db_Select $select)
    {

        // mysql CAST expression hack
        if ($select->getAdapter() instanceof Zend_Db_Adapter_Pdo_Mysql) {
            if (isset($this->expression)) {
                $this->expression = preg_replace('/AS VARCHAR\([0-9]+\)/', 'AS CHAR', $this->expression);
            }
        }

/*        $columns = $select->getPart(Zend_Db_Select::COLUMNS);
        if (empty($columns)) {
            $columns = array();
        }*/

        $select->columns($this->getColumn());
        $group = $select->getPart(Zend_Db_Select::GROUP);
        if (empty($group)) {
            $group = array();
        }

        $select->group($this->getGroup($group));

        return $select;
    }

    public function getGridField(HM_Controller_Action $controller)
    {
        $options = $this->getOptions();

        $field = array();
        $field['title'] = $this->title;
        if (isset($options['hidden']) && $options['hidden']) {
            $field['hidden'] = true;
        }

        if (isset($options['hiden']) && $options['hiden']) { // HARDCODE
            $field['hidden'] = true;
        }

        if (isset($options['sort']) && !$controller->getRequest()->getParam('ordergrid', false)) {
            if (strtolower($options['sort']) == 'asc') {
                $controller->getRequest()->setParam('ordergrid', $this->name.'_ASC');
            } else {
                $controller->getRequest()->setParam('ordergrid', $this->name.'_DESC');
            }
        }

        if (!in_array($this->type, array('date'))) {
            if (isset($options['filter']) && !$controller->getRequest()->getParam($this->name.'grid', false)) {
                $controller->getRequest()->setParam($this->name.'grid', $options['filter']);
            } elseif (!isset($options['filter']) && $controller->getRequest()->getParam($this->name.'grid', false)) {
                $controller->getRequest()->setParam($this->name.'grid', null); // unset
            }
        }

        if (isset($options['filter']) && is_array($options['filter'])) {
            if ($this->type == 'date') {
                $dateFilterFields = array('from', 'to');
                foreach($dateFilterFields as $key) {
                    if (!$controller->getRequest()->getParam($this->name.'['.$key.']grid', false)) {
                        if (isset($options['filter'][$key])) {
                            $value = $options['filter'][$key];
                            $date = new HM_Date($value);
                            $value = $date->get('dd.MM.yyy');
                            $controller->getRequest()->setParam($this->name.'['.$key.']grid', $value);
                        } else {
                            $controller->getRequest()->setParam($this->name.'['.$key.']grid', '');
                        }
                    }
                }
            }
        }

        if (isset($options['title']) && strlen($options['title'])) {
            $field['title'] = $options['title'];
        }

        switch($this->type) {
            case 'date':
                $field['format'] = array('Date', array('date_format' => 'dd.MM.yyyy'));
                break;
            case 'datetimestamp':
            case 'datetime':
                $field['format'] = array('DateTime');
                break;
        }

        if (isset($this->callback)) {
            $field['callback'] = array(
                'function' => array($controller, $this->callback),
                'params' => array($this->name, '{{'.$this->name.'}}')
            );
        }

        if (isset($options['function']) && strlen($options['function'])) {
            $functions = $this->getFunctions();
            if (isset($functions[$options['function']])) {
                $function = $functions[$options['function']];
                if (isset($function['callback'])) {
                    $field['callback'] = array(
                        'function' => array($controller, $function['callback']),
                        'params' => array($this->name, '{{'.$this->name.'}}')
                    );
                }
            }
        }
        return array($this->name => $field);
    }

    public function getGridFilter()
    {
        $options = $this->getOptions();
        if (!isset($options['hidden']) || (isset($options['hidden']) && !$options['hidden'])) {

            $filter = null;
            switch($this->type) {
                case 'datetime': // todo: сделать чтобы работало
                case 'date':
                    $filter = array('render' => 'Date');
                    break;
                case 'datetimestamp':
                    $filter = array('render' => 'DateTimeStamp');
                    break;
            }

            if ($this->searchType) {
                if ($this->searchType == 'sqlExp') {
                    if ($this->searchSqlExp) {
                        $filter['searchType']   = $this->searchType;
                        $filter['searchSqlExp'] = $this->searchSqlExp;
                    }
                } else {
                    $filter['searchType'] = $this->searchType;
                }
            }

            if ($this->callback) {
                $method = $this->callback . 'Filter';
                if (method_exists('HM_Controller_Action_Report', $method)) {
                    $filter['callback'] = array('function' => array('HM_Controller_Action_Report', $method));
                }
                return array(); // @todo
            }

            return array($this->name => $filter);
        }
//        array('callback' => )
        return array();
    }
}