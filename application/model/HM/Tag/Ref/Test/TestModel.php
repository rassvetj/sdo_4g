<?php
class HM_Tag_Ref_Test_TestModel extends HM_Tag_Ref_RefModel
{
    /* (non-PHPdoc)
     * @see HM_Model_Abstract::getServiceName()
     */
    public function getServiceName()
    {
        return 'TagRefTest';
    }
    
    /* (non-PHPdoc)
     * @see HM_Tag_Ref_RefModel_Interface::getType()
     */
    public function getType()
    {
        return self::TYPE_TEST;
    }
}