<?php
class HM_Poll_PollService extends HM_Test_Abstract_AbstractService
{

    public function delete($id)
    {
        //удаляем метки
        $this->getService('TagRef')->deleteBy($this->quoteInto(array('item_id=?',' AND item_type=?'),
                                                               array($id,HM_Tag_Ref_RefModel::TYPE_POLL)));
        return parent::delete($id);
    }

    protected function _updateData($test)
    {
        return $this->getService('Test')->updateWhere(
            array('data' => $test->data),
            $this->quoteInto(array('test_id = ?', ' AND type = ?'), array($test->test_id, HM_Test_TestModel::TYPE_POLL))
        );
    }

    public function publish($id)
    {
        $this->update(array(
            'quiz_id' => $id,
            'status' => HM_Poll_PollModel::STATUS_STUDYONLY,
        ));
    }
    
    public function unpublish($id)
    {
        $this->update(array(
            'quiz_id' => $id,
            'status' => HM_Poll_PollModel::STATUS_UNPUBLISHED,
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
            $this->getService('TagRef')->copy(HM_Tag_Ref_RefModel::TYPE_POLL, $test->quiz_id, $newTest->quiz_id);
        }
        return $newTest;
    }
    
    public function clearLesson($subjectId, $pollId)
    {
        $test = $this->getService('Test')->getOne(
            $this->getService('Test')->fetchAll(
                $this->getService('Test')->quoteInto(
                    array('type = ?', " AND test_id = ?", ' AND cid = ?'),
                    array(HM_Test_TestModel::TYPE_POLL, $pollId, $subjectId)
                )
            )
        );
    
        if ($test) {
            $this->getService('Lesson')->deleteBy(
                $this->getService('Lesson')->quoteInto(
                    array('typeID = ?', " AND params LIKE ?", ' AND CID = ?'),
                    array(HM_Event_EventModel::TYPE_POLL, '%module_id=' . $test->tid . ';%', $subjectId)
                ));
        }
        $this->getService('Test')->delete($test->tid);
    }
}