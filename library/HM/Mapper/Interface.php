<?php
interface HM_Mapper_Interface
{
    public function setModelClass($name);
    public function getModelClass();

    public function setCollectionClass($name);
    public function getCollectionClass();

    public function insert(HM_Model_Abstract $model);
    public function delete($id);
    public function update(HM_Model_Abstract $model);
    public function find();
    public function fetchAll($where = null, $order = null, $count = null, $offset = null);
}
