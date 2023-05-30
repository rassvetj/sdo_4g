<?php
class HM_Report_Table extends HM_Model_Abstract
{

    public function hasRelation($name)
    {
        return isset($this->relations[$name]);
    }

    public function hasRelations()
    {
        return isset($this->relations);
    }

    public function getRelation($name)
    {
        if (!($this->relations[$name] instanceof HM_Report_Table_Relation)) {
            $relations = $this->relations;
            $relations[$name] = new HM_Report_Table_Relation($this->relations[$name]);
            $this->relations = $relations;
        }
        return $this->relations[$name];
    }

    public function getMultiJoinNames()
    {
        return $this->multiJoinNames;
    }


    public function getRelationJoinOn(array $names)
    {
        $join = '';
        foreach($names as $name) {
            if (strlen($join)) {
                $join .= ' AND ';
            }

            if (isset($this->getRelation($name)->joinOn)) {
                $join .= $this->getRelation($name)->joinOn;
                continue;
            }

            $join .= sprintf('%s.%s = %s.%s', $name, $this->getRelation($name)->foreign, $this->name, $this->getRelation($name)->local);
        }
        return $join;
    }

    public function getRelationJoinType($tableName)
    {
        $join = 'inner';

        if (isset($this->getRelation($tableName)->joinType)) {
            $join = $this->getRelation($tableName)->joinType;
        }

        return $join;
    }

    public function join(Zend_Db_Select $select, $tableNames)
    {
        if (is_string($tableNames)) {
            $tableNames = array($tableNames);
        }

        reset($tableNames);

        switch(strtolower($this->getRelationJoinType(current($tableNames)))) {
            case 'left':
                $select->joinLeft(array($this->name => $this->table), $this->getRelationJoinOn($tableNames), array());
                break;
            case 'right':
                $select->joinRight(array($this->name => $this->table), $this->getRelationJoinOn($tableNames), array());
                break;
            default:
                $select->join(array($this->name => $this->table), $this->getRelationJoinOn($tableNames), array());

        }

        return $select;
    }

    public function getRelations()
    {
        return $this->relations;
    }

    public function getPrimaryKeys()
    {
        $keys = array();
        if (isset($this->primary)) {
            $keys = explode(',', $this->primary);
            array_walk($keys, 'trim');
        }
        return $keys;
    }
}