<?php
class HM_Classifier_ClassifierModel extends HM_Model_Abstract
{
	// DEPRICATE!
    // fetch from classifiers_types table
    const TYPE_ACTIVITY     = 1;
    const TYPE_RESOURCE     = 2;
    const TYPE_EDUCATION    = 3;
    const TYPE_SUBSIDIARIES = 4;
    const TYPE_GROUP        = 5;
    
    protected $_childCount = 0;

    static public function getTypes()
    {
    	// DEPRICATE!
    	// fetch from classifiers_types table
        return array(
            self::TYPE_ACTIVITY     => _('Классификатор видов деятельности и тем обучения'),
            self::TYPE_EDUCATION    => _('Справочник образовательных организаций'),
            self::TYPE_SUBSIDIARIES => _('Справочник дочерних обществ и организаций'),
            self::TYPE_RESOURCE     => _('Классификатор информационных ресурсов'),
            self::TYPE_GROUP        => _('Справочник групп обучающихся работников')
        );
    }

    static public function getType($type)
    {
        $types = self::getTypes();

        return $types[$type];
    }

    public function getIcon()
    {

        $image = Zend_Registry::get('serviceContainer')->getService('ClassifierImage')->fetchAll(array('type = ?' => HM_Classifier_Image_ImageModel::TYPE_CLASSIFIER, 'item_id = ?' => $this->classifier_id));
        $image = Zend_Registry::get('serviceContainer')->getService('ClassifierImage')->getOne($image);

        $path = Zend_Registry::get('serviceContainer')->getService('ClassifierImage')->getImageSrc($image->classifier_image_id);

        if($image){
            return Zend_Registry::get('view')->serverUrl($path);
        }
        return;

    }



}