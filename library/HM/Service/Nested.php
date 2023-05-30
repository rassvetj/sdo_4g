<?php
class HM_Service_Nested extends HM_Service_Abstract
{

    public function insert($data, $objectiveNodeId = 0, $position = HM_Db_Table_NestedSet::LAST_CHILD)
    {
        if (is_array($data)) {
            return $this->getMapper()->insert(call_user_func_array(array($this->_modelClass, 'factory'), array($data, $this->_modelClass)), $objectiveNodeId, $position);
        }
    }

    public function updateNode($data, $id, $objectiveNodeId, $position = HM_Db_Table_NestedSet::LAST_CHILD)
    {
        if (is_array($data)) {
            return $this->getMapper()->updateNode(call_user_func_array(array($this->_modelClass, 'factory'), array($data, $this->_modelClass)), $id, $objectiveNodeId, $position);
        }
    }

    public function deleteNode($id, $recursive = false)
    {
        return $this->getMapper()->deleteNode($id, $recursive);
    }

    public function getTree($where = null)
	{
        return $this->getMapper()->getTree($where);
    }

    public function getChildren($id, $oneLevel = true, $where = null)
    {
        return $this->getMapper()->getChildren($id, $oneLevel, $where);
    }

    public function fetchAll($where = null, $getAsTree = false, $parentAlias = null, $order = null, $count = null, $offset = null)
    {
        return $this->getMapper()->fetchAll($where, $getAsTree, $parentAlias, $order, $count, $offset);
    }
}