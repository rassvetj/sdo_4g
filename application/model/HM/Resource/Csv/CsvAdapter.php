<?php
class HM_Resource_Csv_CsvAdapter extends HM_Adapter_Csv_Abstract
{
    // Сколько первых строк будет пропущено
    protected $_skipLines = 1; // не стоит менять этот параметр; как миниммум одна строка нужна при динамической генерации sample

    static public function getStaticFields()
    {
        return array(
            0  => 'resource_id_external',
            1  => 'status',
            2  => 'title', 
            3  => 'description',
        );
    }
    
    static public function getClassifiersFields() {
        $return = array();
        if (count($classifierTypes = Zend_Registry::get('serviceContainer')->getService('ClassifierType')->getClassifierTypesNames(HM_Classifier_Link_LinkModel::TYPE_RESOURCE))) {
            foreach ($classifierTypes as $classifierTypeId => $name) {
                $return[] = "classifier_{$classifierTypeId}";
            }
        } 
        return $return;       
    }
    
    public function getMappingArray()
    {
        return array_merge(self::getStaticFields(), self::getClassifiersFields());
    }
    
    public static function getResourceFields()
    {
        return array_merge(self::getStaticFields(), self::getClassifiersFields());
    }    
}