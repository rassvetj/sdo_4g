<?php
class HM_Db_Table extends Zend_Db_Table_Abstract implements HM_Db_Table_Interface {

    protected $_rowsetClass = 'HM_Db_Table_Rowset';
    private $_rawPrimary = null;

    public function init()
    {
        parent::init();

        if ($this->getAdapter() instanceof HM_Db_Adapter_Sqlsrv) {
            $this->_sequence = true;
        }

        if ($this->getAdapter() instanceof HM_Db_Adapter_Pdo_Mssql) {
            $this->_sequence = true;
        }        

        if ($this->getAdapter() instanceof HM_Db_Adapter_Pdo_Mysql) {
            $this->_sequence = true;
        }
        
    }

    protected function _setupPrimaryKey()
    {
        if (($this->getAdapter() instanceof HM_Db_Adapter_Pdo_Oci
            || $this->getAdapter() instanceof HM_Db_Adapter_Oracle)
            && (null == $this->_rawPrimary)) {
            if (is_string($this->_primary)) {
                $this->_rawPrimary = array(1 => $this->_primary);
                $this->_primary = strtoupper($this->_primary);
            } else if(is_array($this->_primary)) {
                $this->_rawPrimary = $this->_primary;
                foreach($this->_primary as $index => $key) {
                    $this->_primary[$index] = strtoupper($key);
                }
            }
        }

        return parent::_setupPrimaryKey();
    }

    public function getDefaultOrder()
    {

    }

    public function getTableName()
    {
        return $this->_name;
    }

    public function getPrimaryKey()
    {

        if ($this->_rawPrimary !== null) {
            return $this->_rawPrimary;
        }
        return $this->_primary;
    }

    public function getReferenceMap()
    {
        return $this->_referenceMap;
    }

    public function insert(array $data)
    {
        $data = $this->_prepareData($data);
        
        $sequence = null;
        if (is_string($this->_sequence) && method_exists($this->_db, 'isUseTriggerForSequence')) {
            if ($this->_db->isUseTriggerForSequence()) {
                $sequence = $this->_sequence;
                $this->_sequence = null;
            }
        }
        
        $pkData = parent::insert($data);

        if (null !== $sequence) {
            $primary = (array) $this->_primary;
            $pkIdentity = $primary[(int)$this->_identity];
            $this->_sequence = $sequence;
            $data[$pkIdentity] = $this->_db->lastSequenceId($sequence);
            
            $pkData = array_intersect_key($data, array_flip($primary));
            
            if (count($primary) == 1) {
                reset($pkData);
                return current($pkData);
            }

        }
        return $pkData;
    }

    public function update($data, $where)
    {
        $data = $this->_prepareData($data);
        return parent::update($data, $where);
    
    }

    /**
     * Функция для подготовки данных для закидывания в базу
     * 
     * @param unknown_type $data входящий массив
     * @return unknown 
     */
    protected function _prepareData($data){
        $info = $this->info();
        $temp = $info['metadata'];
        $temp = array_change_key_case($temp);
        
        $adapter = $this->getAdapter();

        foreach($data as $key => &$value){
            if ($value instanceof Zend_Db_Expr) continue;
            if(isset($temp[strtolower($key)])){
                $dataType = $temp[strtolower($key)]['DATA_TYPE'];
                $length   = $temp[strtolower($key)]['LENGTH'];
            }elseif(isset($temp[strtolower($key)."_"])){
                $dataType = $temp[strtolower($key)."_"]['DATA_TYPE'];
                $length   = $temp[strtolower($key)."_"]['LENGTH'];
            }else{
                $dataType = $temp[strtoupper($key)]['DATA_TYPE'];
                $length   = $temp[strtoupper($key)]['LENGTH'];
            }
            
            if(is_callable(array($adapter, '_func'.ucfirst(strtolower($dataType))))){
                $value = $adapter->{'_func'.ucfirst(strtolower($dataType))}($value, $length);
            }
        }
        return $data;
    }

      
    
} 
