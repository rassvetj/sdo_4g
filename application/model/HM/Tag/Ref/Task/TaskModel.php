<?php
class HM_Tag_Ref_Task_TaskModel extends HM_Tag_Ref_RefModel
{
    /* (non-PHPdoc)
     * @see HM_Model_Abstract::getServiceName()
     */
    public function getServiceName()
    {
        return 'TagRefTask';
    }
    
    /* (non-PHPdoc)
     * @see HM_Tag_Ref_RefModel_Interface::getType()
     */
    public function getType()
    {
        return self::TYPE_TASK;
    }
}