<?php
/**
 * Description of Sql
 *
 * @author slava
 */
class Es_Schema_AbstractSql implements Es_Schema_SchemaBehavior {
    
    protected $structureSql = null;
    protected $dataSql = null;
    
    public function getStructureSql() {
        return $this->structureSql;
    }

    public function setStructureSql($structureSql) {
        $this->structureSql = $structureSql;
    }

    public function getDataSql() {
        return $this->dataSql;
    }

    public function setDataSql($dataSql) {
        $this->dataSql = $dataSql;
    }
    
    public function createSchema() {
        
    }

    public function loadData() {
        
    }
    
}

?>
