<?php
class HM_Role_GraduatedService extends HM_Service_Abstract
{
    public function insert($data)
    {
        $data['end']      = $data['created'] = $this->getDateTime();
        $data['progress'] = intval($this->getService('Subject')->getUserProgress($data['CID'],$data['MID']));
        $assign = parent::insert($data);
        if ($assign) {

            // удаляем из слушеателей (на всякий случай, если еще не удалён)
            $this->getService('Student')->deleteBy(
                $this->quoteInto(
                    array('mid = ?', ' AND cid = ?'),
                    array($assign->MID, $assign->CID)
                )
            );

            // Отправка сообщения
            $subjectMark = $this->getOne($this->getService('SubjectMark')->fetchAll(
                $this->quoteInto(
                    array('mid = ?', ' AND cid = ?'),
                    array($assign->MID, $assign->CID)
                )
            ));

            $mark = '-';

            if ($subjectMark) {
                $mark = $subjectMark->mark;
            }

            $messenger = $this->getService('Messenger');
            $messenger->setOptions(
                HM_Messenger::TEMPLATE_GRADUATED,
                array(
                    'subject_id' => $assign->CID,
                    'grade' => $mark,
                    'role' => _('Прошедший обучение')
                ),
                'subject',
                $assign->CID
            );

            $messenger->send(HM_Messenger::SYSTEM_USER_ID, $assign->MID);
        }
        return $assign;
    }

    public function isUserExists($subjectId, $userId)
    {
        $collection = $this->fetchAll(array('CID = ?' => $subjectId, 'MID = ?' => $userId));
        return count($collection);
    }
}