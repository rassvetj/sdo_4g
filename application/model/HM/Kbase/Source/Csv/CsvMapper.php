<?php
class HM_Kbase_Source_Csv_CsvMapper extends HM_Mapper_Abstract
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
                
				$model['e_publishing_url'] = str_replace (array("\n", "\r", "\t") , '~~' ,$model['e_publishing_url'] );
				$model['e_educational_url'] = str_replace (array("\n", "\r", "\t") , '~~' ,$model['e_educational_url'] );
				
				
				$model['e_publishing_url'] = str_replace (array('http://') , '~~http://' ,$model['e_publishing_url'] );
				$model['e_educational_url'] = str_replace (array('http://') , '~~http://' ,$model['e_educational_url'] );
				
				$model['e_publishing_url'] = str_replace (array('~~~~') , '~~' ,$model['e_publishing_url'] );
				$model['e_educational_url'] = str_replace (array('~~~~') , '~~' ,$model['e_educational_url'] );				
				
				
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