<?php
class HM_Tag_Ref_Blog_BlogModel extends HM_Tag_Ref_RefModel
{
    public function getServiceName()
    {
        return 'TagRefBlog';
    }
    
    /* (non-PHPdoc)
     * @see HM_Tag_Ref_RefModel_Interface::getType()
     */
    public function getType()
    {
        return self::TYPE_BLOG;
    }
}