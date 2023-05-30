<?php
class HM_Tag_Ref_RefService extends HM_Service_Abstract implements HM_Tag_Ref_RefService_Interface
{
    public function getItemTitle($itemId){}
    public function getItemDescription($itemId){}
    public function getItemViewAction($itemId){}
    public function getFilterSelect($search, Zend_Db_Select $select){}
    public function getIcon(){}

    public function getBlogType()
    {
        return HM_Tag_Ref_RefModel::TYPE_BLOG;
    }

    public function getResourceType()
    {
        return HM_Tag_Ref_RefModel::TYPE_RESOURCE;
    }

    public function getCourseType()
    {
        return HM_Tag_Ref_RefModel::TYPE_COURSE;
    }

    public function getTestType()
    {
        return HM_Tag_Ref_RefModel::TYPE_TEST;
    }

    public function getExercisesType()
    {
        return HM_Tag_Ref_RefModel::TYPE_EXERCISES;
    }

    public function getPollType()
    {
        return HM_Tag_Ref_RefModel::TYPE_POLL;
    }

    public function getTaskType()
    {
        return HM_Tag_Ref_RefModel::TYPE_TASK;
    }

    public function getUserType()
    {
        return HM_Tag_Ref_RefModel::TYPE_USER;
    }

    public function copy($type, $fromItemId, $toItemId)
    {
        $collection = $this->fetchAll(
            $this->quoteInto(
                array('item_type = ?', ' AND item_id = ?'),
                array($type, $fromItemId)
            )
        );

        if (count($collection)) {
            foreach($collection as $item) {
                $item->item_id = $toItemId;
                $this->insert($item->getValues());
            }
        }
    }
}