<?php
class HM_Resource_Csv_CsvMapper extends HM_Mapper_Abstract
{

    protected function _createModel($rows, &$dependences = array())
    {
        $collectionClass = $this->getCollectionClass();
        $models = new $collectionClass(array(), $this->getModelClass());
        if (count($rows) < 1) {
            return $models;
        }
        $rowsRet = array();


        if (count($rows) > 0) {
            $dependences = array();

            $services    = Zend_Registry::get('serviceContainer');

            foreach($rows as $index => $row) {

                $fields = $this->getAdapter()->getResourceFields();
                $data = $this->getFields($fields, $row);
                $models[count($models)] = $data;

            }
            $models->setDependences($dependences);
        }


        return $models;

    }

    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {

        $adapter = $this->getAdapter();

        $rows = $adapter->fetchAll($where, $order, $count, $offset);
        return $this->_createModel($rows);
    }


    public function clean($field, $row)
    {
        return trim($field);
    }

    public function getFields($fields, $row)
    {
        $res = array();

        foreach($fields as $field){
            $res[$field] = $row[$field];
        }
        return $res;
    }

}