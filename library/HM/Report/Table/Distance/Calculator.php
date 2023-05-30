<?php
/**
 * Калькулятор кратчайшего пути в графе таблиц по алгоритму Дейкстры
 */

class HM_Report_Table_Distance_Calculator
{
    private $_tables = null;
    private $_passed = array();
    private $_distances = null;
    private $_paths = null;

    const INFINITY = 999;

    public function __construct($tables)
    {
        $this->_tables = $tables;
    }

    private function _calculate(HM_Report_Table $table)
    {
        $passed = array(); $distances = array();
        while(count($this->_tables) != count($this->_passed)) {
            foreach($table->getRelations() as $relationName => $relation) {
                if (!isset($this->_passed[$relationName])) {
                    if (($this->_distances[$table->name] + 1) < $this->_distances[$relationName]) {
                        $this->_distances[$relationName] = $this->_distances[$table->name] + 1;
                        $this->_paths[$relationName] = array_merge($this->_paths[$table->name], array($relationName));
                        $passed[$relationName] = true;
                    }
                } else {
                    $passed[$relationName] = true;
                }

                $distances[$relationName] = $this->_distances[$relationName];
            }

            if (count($table->getRelations()) == count($passed)) {
                $this->_passed[$table->name] = $table;
            }

            asort($distances);

            foreach(array_keys($distances) as $relationName)
            //foreach($table->getRelations() as $relationName => $relation)
            {
                if (!isset($this->_passed[$relationName]) && isset($passed[$relationName])) {
                    $this->_calculate($this->_tables[$relationName]);
                }
            }
            return true;
        }
    }

    public function calculate($from)
    {
        $this->_passed = array();
        $this->_distances[$from] = 0;
        foreach($this->_tables as $tableName => $table)
        {
            if ($tableName == $from) continue;
            $this->_distances[$tableName] = self::INFINITY;
        }

        $this->_paths[$from] = array($from);

        $table = $this->_tables[$from];
        $this->_calculate($table);
        $this->_passed = array();
    }

    public function getPaths()
    {
        return $this->_paths;
    }

    public function getPath($name)
    {
        return $this->_paths[$name];
    }
}