<?php

class HM_Classifier_Image_ImageTable extends HM_Db_Table
{
    protected $_name = "classifiers_images";
    protected $_primary = "classifier_image_id";
    protected $_sequence = "S_94_1_CLASSIFIERS_IMAGES";
 
    protected $_referenceMap = array(
    );
    

    public function getDefaultOrder()
    {
        return array('classifiers_images.classifier_image_id ASC');
    }
}