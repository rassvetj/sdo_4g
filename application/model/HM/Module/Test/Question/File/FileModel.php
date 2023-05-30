<?php
class HM_Module_Test_Question_File_FileModel extends HM_Model_Abstract
{
    /**
     * очередной костыль...
     * сложность возникает при работе с mssql
     * убрал проверку && !is_array($value)
     * неизвестно для чего она в абстрактной модели, но тут нам необходимо
     * объяснить зенду, что одна из $value - картинка
     * объяснить это можно посредством передачи зенду массива,
     * а эта нехорошая проверка не пускала наш массив
     */
    public function getValues($keys = null, $excludes = null)
    {
        $values = array();
        if (is_array($this->_data) && count($this->_data)) {
            foreach($this->_data as $key => $value) {
                if (!is_object($value) || $value instanceof Zend_Db_Expr) {
                    if (is_array($keys) && !in_array($key, $keys)) continue;
                    if (is_array($excludes) && in_array($key, $excludes)) continue;
                    $values[$key] = $value; //$this->getTextWithLang($key);
                }
            }
        }
        return $values;
    }
}