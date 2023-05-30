<?php
class HM_Metadata_Group_GroupService extends HM_Service_Abstract
{
    public function delete($id)
    {
        $this->getService('MetadataItem')->deleteBy($this->quoteInto('group_id = ?', $id));
        return parent::delete($id);
    }
}