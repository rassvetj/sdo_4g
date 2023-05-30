<?php
class HM_Task_TaskService extends HM_Test_Abstract_AbstractService
{
    public function delete($id)
    {
        //удаляем метки
        $this->getService('TagRef')->deleteBy($this->quoteInto(array('item_id=?',' AND item_type=?'),
                                                               array($id,HM_Tag_Ref_RefModel::TYPE_TASK)));
        return parent::delete($id);
    }

    protected function _updateData($test)
    {
        return $this->getService('Test')->updateWhere(
            array('data' => $test->data),
            $this->quoteInto(array('test_id = ?', ' AND type = ?'), array($test->test_id, HM_Test_TestModel::TYPE_TASK))
        );
    }

    public function publish($id)
    {
        $this->update(array(
            'task_id' => $id,
            'status' => HM_Task_TaskModel::STATUS_STUDYONLY,
        ));
    }
    
    public function unpublish($id)
    {
        $this->update(array(
            'task_id' => $id,
            'status' => HM_Task_TaskModel::STATUS_UNPUBLISHED,
        ));
    }

    public function getDefaults()
    {
        $user = $this->getService('User')->getCurrentUser();
        return array(
            'created' => $this->getDateTime(),
            'updated' => $this->getDateTime(),
            'created_by' => $user->MID,
            'status' => 0, //public
        );
    }

    public function copy($test, $subjectId = null)
    {
        $newTest = parent::copy($test, $subjectId);

        if ($newTest) {
            $this->getService('TagRef')->copy(HM_Tag_Ref_RefModel::TYPE_TASK, $test->task_id, $newTest->task_id);
        }
        return $newTest;
    }

    public function saveFile($file)
    {

    }

}