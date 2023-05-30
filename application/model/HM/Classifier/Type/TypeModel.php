<?php
class HM_Classifier_Type_TypeModel extends HM_Model_Abstract
{
    const TYPES_SEPARATOR = ' ';
	//const BUILTIN_TYPE_DIRECTIONS = 1;

    public function getTypes()
    {
        if(trim($this->link_types) == '') return array();
        return explode(self::TYPES_SEPARATOR, $this->link_types);
    }

    public function setTypes($types)
    {
        $this->link_types = (string) join(self::TYPES_SEPARATOR, $types);
    }


    public function getIcon()
    {

        $image = Zend_Registry::get('serviceContainer')->getService('ClassifierImage')->fetchAll(array('type = ?' => HM_Classifier_Image_ImageModel::TYPE_CATEGORY, 'item_id = ?' => $this->type_id));
        $image = Zend_Registry::get('serviceContainer')->getService('ClassifierImage')->getOne($image);

        $path = Zend_Registry::get('serviceContainer')->getService('ClassifierImage')->getImageSrc($image->classifier_image_id);

        if($image){
            return Zend_Registry::get('view')->serverUrl($path);
        }
        return;
    }




}