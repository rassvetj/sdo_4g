<?php
abstract class HM_User_Csv_CsvMapper extends HM_Mapper_Abstract
{
    protected function modifyModel(&$model){
        $model['blocked'] = 0;
        $model['isAD'] = 1;
        $model['need_edit'] = 0;
    }
    
    protected function _createModel($rows, &$dependences = array())
    {
        $collectionClass = $this->getCollectionClass();
        $models = new $collectionClass(array(), $this->getModelClass());

        if (count($rows) > 0) {
            $dependences = array();
            foreach($rows as $index => $row) {

                $model = array();

                foreach($row as $key => $val){
                    if($val != "" ){
                        $model[$key] = $val;
                    }
                }
                $this->modifyModel($model);
                $models[count($models)] = $model;
                unset($rows[$index]);
            }
            
            $models->setDependences($dependences);
        }

        //print_r($models); exit;
        return $models;

    }

    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        $rows = $this->getAdapter()->fetchAll($where, $order, $count, $offset);
        return $this->_createModel($rows);
    }

}