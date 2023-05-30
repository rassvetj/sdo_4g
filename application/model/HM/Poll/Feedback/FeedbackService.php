<?php
class HM_Poll_Feedback_FeedbackService extends HM_Service_Abstract
{
    public function save($userId, $subjectId, $lessonId, $status, $orderId = 0)
    {
        $lesson = $this->getOne(
            $this->getService('Lesson')->find($lessonId)
        );

        $subject = $this->getOne(
            $this->getService('Subject')->find($subjectId)
        );

        if ($lesson && $subject) {
            $feedback = $this->getOne($this->find($userId, $subjectId, $lessonId));
            if (!$feedback) {

                $place = _('Дистанционно');
                $begin = $this->getDateTime();
                $end   = $this->getDateTime();

                $claimant = false;

                if ($orderId > 0) {
                    $claimant = $this->getOne(
                        $this->getService('Claimant')->find($orderId)
                    );

                    if ($claimant) {
                        if ($claimant->palce) {
                            $place = $claimant->place;
                        }
                        if ($claimant->begin) {
                            $begin = $claimant->begin;
                        }
                        if ($claimant->end) {
                            $end = $claimant->end;
                        }
                    }
                }

                if (!$claimant) {
                    $claimant = $this->getOne(
                        $this->getService('Claimant')->fetchAll(
                            $this->quoteInto(
                                array('MID = ?', ' AND CID = ?'),
                                array($userId, $subjectId)
                            )
                        ),
                        'SID DESC',
                        1
                    );

                    if ($claimant) {
                        if ($claimant->place) {
                            $place = $claimant->place;
                        }
                        if ($claimant->begin) {
                            $begin = $claimant->begin;
                        }
                        if ($claimant->end) {
                            $end = $claimant->end;
                        }
                    } else {
                        $student = $this->getOne(
                            $this->getService('Student')->fetchAll(
                                $this->quoteInto(
                                    array('MID = ?', ' AND CID = ?'),
                                    array($userId, $subjectId)
                                )
                            )
                        );
                        if ($student) {
                            $begin = $student->time_registered;
                        }
                    }
                }

                $feedback = $this->insert(array(
                    'user_id' => $userId,
                    'subject_id' => $subjectId,
                    'lesson_id' => $lessonId,
                    'status' => $status,
                    'begin' => $begin,
                    'end' => $end,
                    'place' => $place,
                    'created' => $begin,
                    'title' => $lesson->title,
                    'subject_name' => $subject->name
                ));
            } else {
                $feedback = $this->update(array(
                    'user_id' => $userId,
                    'subject_id' => $subjectId,
                    'lesson_id' => $lessonId,
                    'status' => $status,
                    'title' => $lesson->title,
                    'subject_name' => $subject->name
                ));
            }

            if ($feedback && $lesson->teacher) {
                $teacher = $this->getOne(
                    $this->getService('User')->find($lesson->teacher)
                );

                if ($teacher) {
                    $feedback->trainer = $teacher->getName();
                    $feedback->trainer_id = $teacher->MID;
                    $feedback = $this->update($feedback->getValues());
                }
            }

            return $feedback;
        }

        return false;
    }

    public function assign($userId, $subjectId, $lessonId, $orderId = 0)
    {
        return $this->save($userId, $subjectId, $lessonId, HM_Poll_Feedback_FeedbackModel::STATUS_SENT, $orderId);
    }

    public function cancel($userId, $subjectId, $lessonId)
    {
        $feedback = $this->getOne($this->find($userId, $subjectId, $lessonId));
        if ($feedback) {
            if (in_array($feedback->status, array(HM_Poll_Feedback_FeedbackModel::STATUS_INPROGRESS, HM_Poll_Feedback_FeedbackModel::STATUS_DONE))) {
                return false;
            }
        }

        return $this->save($userId, $subjectId, $lessonId, HM_Poll_Feedback_FeedbackModel::STATUS_CANCELED);
    }

}