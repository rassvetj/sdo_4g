<?php
class HM_Tag_Ref_Course_CourseModel extends HM_Tag_Ref_RefModel
{
    public function getServiceName()
    {
        return 'TagRefCourse';
    }
    
    /* (non-PHPdoc)
     * @see HM_Tag_Ref_RefModel_Interface::getType()
     */
    public function getType()
    {
        return self::TYPE_COURSE;
    }
}