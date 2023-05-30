<?php
class HM_Debtors_Csv_CsvMapper extends HM_Mapper_Abstract
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
                    $val = trim($val);
                    $val = trim($val, " ");
					
					if($val != "" ){
						if($key == 'time_ended_debtor' || $key == 'time_ended_debtor_2' || $key == 'time_begin_debtor' || $key == 'time_begin_debtor_2'){
							$timestamp = strtotime($val);
							if($timestamp){
								$model[$key] = date('Y-m-d', $timestamp);	
							} else {
								$model[$key] = false;
							}							
						} else {
							$model[$key] = $val;
						}
                    }
                }
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