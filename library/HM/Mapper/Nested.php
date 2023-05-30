<?php
class HM_Mapper_Nested extends HM_Mapper_Abstract
{
    public function insert(HM_Model_Abstract $model, $objectiveNodeId = null, $position = HM_Db_Table_NestedSet::LAST_CHILD)
    {
        $result = $this->getTable()->insert($model->getValues(), $objectiveNodeId, $position);
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

    public function updateNode(HM_Model_Abstract $model, $id, $objectiveNodeId, $position = HM_Db_Table_NestedSet::LAST_CHILD)
    {
        $this->getTable()->updateNode($model->getValues(), $id, $objectiveNodeId, $position);
        
        $where = $this->_createWhereFromModel($model);
        $collection = $this->fetchAll($where);
        if (count($collection)) {
        	$model = $collection->current();
        }

        return $model;
    }

    public function deleteNode($id, $recursive = false)
    {
        return $this->getTable()->deleteNode($id, $recursive);

    }

    public function getTree($where = null)
	{
        $rows = $this->getTable()->getTree($where);
        return $this->_createModel($rows);        
    }

    public function fetchAll($where = null, $getAsTree = false, $parentAlias = null, $order = null, $count = null, $offset = null)
    {
        $rows = $this->getTable()->fetchAll($where, $getAsTree, $parentAlias, $order, $count, $offset);
        return $this->_createModel($rows);
    }

    public function getChildren($id, $oneLevel = true, $where = null)
    {
        $rows = $this->getTable()->getChildren($id, $oneLevel, $where);
        return $this->_createModel($rows);
    }
}