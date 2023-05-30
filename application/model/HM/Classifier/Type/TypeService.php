<?php
class HM_Classifier_Type_TypeService extends HM_Service_Abstract
{
    public function delete($id)
    {
        $this->getService('Classifier')->deleteByType($id);

        return parent::delete($id);
    }

    public function getClassifierTypes($linkType, $types_ids = array()){

        if(!count($types_ids)){
            $types = $this->fetchAll(
                $this->quoteInto('link_types LIKE ?', '%'.(int) $linkType.'%')
            );
        }
        else{
            $types = $this->fetchAll(
                $this->quoteInto(
                    array('link_types LIKE ?', ' AND type_id IN (?)'), 
                    array('%'.(int) $linkType.'%', $types_ids)
                ), 'type_id'
            );
        }

        return $types;
    }
    
    public function getNonBuiltInTypes($linkType)
    {
        $condition = array('link_types LIKE ?');
        $bind = array('%'.(int) $linkType.'%');
        
        if (count($builtInTypes = self::getBuiltInTypeIds())) {
            $condition[] = ' AND type_id NOT IN (?)';
            $bind[] = $builtInTypes;
        }
    
        $types = $this->fetchAll(
            $this->quoteInto($condition, $bind)
        );
        
        if (count($types)) {
            return $types->getList('type_id');
        }
        return array();
    }
    
    static public function getBuiltInTypeIds()
    {
        return array(
        );
    }
    public function getClassifierTypesNames($linkType, $types_ids = array())
    {
    	$return = array();
    	$types = $this->getClassifierTypes($linkType, $types_ids = array());
    	foreach ($types as $type) {
    		$return[$type->type_id] = $type->name;
    	}
    	return $return;
    }
}