<?php
class HM_Kbase_Source_Import_Manager
{
	const CACHE_NAME = 'HM_Kbase_Source_Import_Manager';    
   
	protected 	$_toInserted = array(); # даные для вставки
	private 		$_restoredFromCache = false;
   
	public function getService($name)
	{
		return Zend_Registry::get('serviceContainer')->getService($name);
	}
   
	public function init($items)
	{
		if ($this->_restoredFromCache) {
			return true;
		}
	   
		if (count($items)) {
			foreach($items as $item) {	
				$this->_toInserted[] = $item;
			}
		}		
		$this->saveToCache();	   
	}
	  
	public function saveToCache()
	{
		return Zend_Registry::get('cache')->save(
			array(
				 'inserts' => $this->_toInserted,                 
			),
			self::CACHE_NAME
		);
	}

    public function clearCache()
    {
        return Zend_Registry::get('cache')->remove(self::CACHE_NAME);
    }

    public function restoreFromCache()
    {
		if ($actions = Zend_Registry::get('cache')->load(self::CACHE_NAME)) {
            $this->_toInserted = $actions['inserts'];            
            $this->_restoredFromCache = true;
            return true;
        }

        return false;
    }
	
	public function getInsert()
    {
        return $this->_toInserted;
    }
	
	public function getInsertCount()
    {
        return count($this->_toInserted);
    }
	
	
	public function import()
    {
		try {
			if ($this->getInsertCount()) {
				
				$this->_serviceKS = $this->getService('KbaseSource');			
				$this->_serviceKS->deleteBy($this->_serviceKS->quoteInto('1=1')); # удалить старые данные
				$cur_date = date('Y-m-d H:i:s',time());
				
				foreach($this->_toInserted as $i){
					$this->_serviceKS->insert(
						array(
							'direction' 		=> $i->direction,
							'code' 				=> $i->code,
							'years'				=> $i->years,
							'discipline' 		=> $i->discipline,
							'e_publishing' 		=> $i->e_publishing,
							'e_publishing_url' 	=> $i->e_publishing_url,
							'e_educational'		=> $i->e_educational,
							'e_educational_url'	=> $i->e_educational_url,
							'date_update'		=> $cur_date,
						)
					);
				}						
			}
		} catch (Exception $e) {
				#echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
		}
	}
}