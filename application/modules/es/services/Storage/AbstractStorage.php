<?php
/**
 * Description of AbstractStorage
 *
 * @author slava
 */
abstract class Es_Service_Storage_AbstractStorage implements Es_Service_Storage_StorageBehavior {
    
    /**
     *
     * @var \Zend_Config
     */
    private $connectionConfig = null;
    
    /**
     *
     * @var mixed
     */
    private $connection = null;
    
    /**
     *
     * @var \Es_Service_Dispatcher 
     */
    private $esEventDispatcher = null;
    
    /**
     * 
     * @return \Es_Service_Dispatcher
     */
    public function getEsEventDispatcher() {
        return $this->esEventDispatcher;
    }

    /**
     * 
     * @param \Es_Service_Dispatcher $dispatcher
     */
    public function setEsEventDispatcher(\Es_Service_Dispatcher $dispatcher) {
        $this->esEventDispatcher = $dispatcher;
    }
    
    /**
     * 
     * @return \Zend_Config
     */
    public function getConnectionConfig() {
        return $this->connectionConfig;
    }

    /**
     * 
     * @param \Zend_Config $config
     */
    public function setConnectionConfig(\Zend_Config $config) {
        $this->connectionConfig = $config;
    }
    
    /**
     * 
     * @return mixed
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * 
     * @param mixed $connection
     */
    public function setConnection($connection) {
        $this->connection = $connection;
    }
    
}

?>
