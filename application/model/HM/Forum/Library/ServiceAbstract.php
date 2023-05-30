<?php

/**
 * Абстракция сервисов реализующих модели доступа к данным
 */
abstract class HM_Forum_Library_ServiceAbstract extends HM_Service_Abstract{
    
    private $_cacheLocal = array();
    
    /**
     * Поместить данные в кеш
     * 
     * @param mixed $key key
     * @param bool $multi массив на входе является массивом ключей, иначе сам является ключом
     * @return mixed | null
     */
    protected function cacheGet($key, $multi = null){
        if($multi && is_array($key)){
            $result = array();
            foreach($key as $part) $result[$part] = $this->cacheGet($part);
            return $result;
        }
        
        $key = $this->_cacheRenderKey($key);
        return isset($this->_cacheLocal[$key]) ? $this->_cacheLocal[$key] : null;
    }
    
    /**
     * Получить данные из кеша
     * 
     * @param mixed $key key
     * @param mixed $value value
     * @param bool $multi массив на входе является массивом ключей, иначе сам является ключом
     * @param mixed value 
     */
    protected function cacheSet($key, $value, $multi = null){
        if($multi && is_array($key)){
            if(is_array($value)) $key = array_combine($key, array_values($value));
            foreach($key as $part => $value) $this->cacheSet($part, $value);
            return;
        }
        
        $key = $this->_cacheRenderKey($key);
        $this->_cacheLocal[$key] = $value;
    }
    
    /**
     * Проверить существование данных в кеше
     * 
     * @param mixed $key key
     * @param bool $multi массив на входе является массивом ключей, иначе сам является ключом
     * @return bool
     */    
    protected function cacheIsset($key, $multi = null){
        if($multi && is_array($key)){
            $result = array();
            foreach($key as $part) $result[$part] = $this->cacheIsset($part);
            return $result;
        }
        
        $key = $this->_cacheRenderKey($key);
        return isset($this->_cacheLocal[$key]);
    }
    
    /**
     * Удалить данные из кеша
     * 
     * @param mixed $key key
     * @param bool $multi массив на входе является массивом ключей, иначе сам является ключом
     */
    protected function cacheUnset($key, $multi = null){
        if($multi && is_array($key)){
            foreach($key as $part) $this->cacheUnset($part);
        }
        
        $key = $this->_cacheRenderKey($key);
        unset($this->_cacheLocal[$key]);
    }
    
    /**
     * Сформировать ключ ячейки для хранения данных в кеше
     * 
     * @param $key mixed
     * @return scalar 
     */
    private function _cacheRenderKey($key){
        if(is_scalar($key)) return $key;
        elseif(is_bool($key)) throw new HM_Exception('Unable to use a boolean type as a key');
        return md5(serialize($key));
    }
    
    /**
     * Убирает данные предназначенные для несуществующих колонок таблицы
     * 
     * @param array $data
     * @return array 
     */
    protected function _prepareData(array $data)
    {
        $keys = array();
        $columns = $this->getMapper()->getTable()->info(Zend_Db_Table_Abstract::COLS);
        foreach ($columns as $key) {
            $keys[strtolower($key)] = true;
        }
        $data = array_intersect_key($data, $keys);
        return $data;
    }
    
}