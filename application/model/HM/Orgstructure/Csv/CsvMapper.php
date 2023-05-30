<?php

class HM_Orgstructure_Csv_CsvMapper extends HM_Mapper_Abstract {

    protected function _createModel($rows) {
        $collectionClass = $this->getCollectionClass();
        $models = new $collectionClass(array(), $this->getModelClass());
        
        if (count($rows) > 0) {
            foreach($rows as $index => $row) {
                $model = array();
                foreach($row as $key => $val){
                    if($val != "" ){
                        $model[$key] = $val;
                    }
                }
			
                //--Отключаем фильтр для int, т.к. у преподов id символьный
                //$this->filterInt($model['soid_external']);
                //$this->filterInt($model['owner_soid_external']);
                //$this->filterInt($model['mid_external']);
                
                $models[count($models)] = $model;
                unset($rows[$index]);
            }
        }

        return $models;
    }

    public function fetchAll($where = null, $order = null, $count = null, $offset = null) {
        $rows = $this->getAdapter()->fetchAll($where, $order, $count, $offset);

        return $this->_createModel($rows);
    }
}
