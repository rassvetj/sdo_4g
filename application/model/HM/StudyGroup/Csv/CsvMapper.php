<?php
class HM_StudyGroup_Csv_CsvMapper extends HM_Mapper_Abstract
{
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
                $model['type'] = HM_StudyGroup_StudyGroupModel::TYPE_CUSTOM;
                
                $this->filterInt($model['id_external']);
                $this->filterInt($model['foundation_year']);
                $this->filterInt($model['programm_id_external']);
				
				$dt = DateTime::createFromFormat('d.m.Y', $model['begin_learning']);
				$model['begin_learning'] = $dt ? $dt->format('Y-m-d') : NULL;
                
                $models[count($models)] = $model;
                unset($rows[$index]);
            }
            
            $models->setDependences($dependences);
        }

        return $models;
    }

    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        $rows = $this->getAdapter()->fetchAll($where, $order, $count, $offset);
        return $this->_createModel($rows);
    }

}