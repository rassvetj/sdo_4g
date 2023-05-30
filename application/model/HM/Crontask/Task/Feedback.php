<?php
class HM_Crontask_Task_Feedback extends HM_Crontask_Task_TaskModel  implements HM_Crontask_Task_Interface
{
    public function getTaskId()
    {
        return 'sendFeedbackMessage';
    }

    public function run()
    {
        $serviceContainer = Zend_Registry::get('serviceContainer');
        $where = array(
            '(status NOT IN (?) OR status IS NULL)' => array(HM_Poll_Feedback_FeedbackModel::STATUS_DONE),
            'end > ?' => date('Y-m-d'),
        );

        $polls = $serviceContainer->getService('PollFeedback')->fetchAllDependence(array('Lesson', 'User'),$where);
        
        $lessonAssignDates = array();
        if ($lessonIds = $polls->getList('lesson_id')) {
            $collection = $serviceContainer->getService('LessonAssign')->fetchAll(array('SHEID IN (?)' => $lessonIds));
            foreach ($collection as $lessonAssign) {
                list($created,) = explode(' ', $lessonAssign->created);
                $lessonAssignDates[$lessonAssign->SHEID][$lessonAssign->MID] = array(
                    'created' => new HM_Date($created),
                    'beginRelative' => new HM_Date($lessonAssign->beginRelative),
                    'endRelative' => new HM_Date($lessonAssign->endRelative),
                );
            }
        }
        
        foreach ($polls as $poll) {
            $lesson = $serviceContainer->getService('PollFeedback')->getOne($poll->lessons);
            $user = $serviceContainer->getService('PollFeedback')->getOne($poll->users);

            $flag = false;
            if (!$lesson || !$user) {
                continue;
            }

            $now = new HM_Date();
            $pollCreatedDate = $lessonAssignDates[$lesson->SHEID][$user->MID]['created']; // нужна только дата, без времени
            $pollBeginRelativeDate = $lessonAssignDates[$lesson->SHEID][$user->MID]['beginRelative'];
            $pollEndRelativeDate = $lessonAssignDates[$lesson->SHEID][$user->MID]['endRelative'];
            
            if (in_array($lesson->notice, array(HM_Lesson_Poll_PollModel::NOTICE_REPEAT, HM_Lesson_Poll_PollModel::NOTICE_DATE))) {

                if ($pollCreatedDate && ($lesson->notice == HM_Lesson_Poll_PollModel::NOTICE_REPEAT)) {
                    // с даты назначения опроса
                    $compareDate = $pollCreatedDate;  
                } elseif ($pollBeginRelativeDate && ($lesson->notice == HM_Lesson_Poll_PollModel::NOTICE_DATE)) {
                    // с даты доступности
                    $compareDate = $pollBeginRelativeDate;
                }

                $measure = new Zend_Measure_Time($now->sub($compareDate)->toValue(), Zend_Measure_Time::SECOND);
                $measure->convertTo(Zend_Measure_Time::DAY);
                $period = floor($measure->getValue());
                
                if ($lesson->notice_days <= 1) {
                    $flag = true;
                } elseif (($period >= 0) && ($period % $lesson->notice_days == 0) && !$pollEndRelativeDate->isEarlier(new HM_Date())) {
                    $flag = true;
                }
            }

            if ($flag) {
                
                switch($lesson->typeID) {
                    case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_TEACHER:
                        $templateId = HM_Messenger::TEMPLATE_POLL_TEACHERS;
                        break;
                    case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_LEADER:
                        $templateId = HM_Messenger::TEMPLATE_POLL_LEADERS;
                        break;
                    default:
                        $templateId = HM_Messenger::TEMPLATE_POLL_STUDENTS;
                }

                $messenger = $serviceContainer->getService('Messenger');
                $messenger->setTemplate( $templateId);
                $messenger->assign(
                    array(
                        'lesson'     => $lesson->title,
                        'subject_id' => $lesson->CID,
                        'url_lesson' => Zend_Registry::get('view')->serverUrl($lesson->getExecuteUrl()),
                        'title'      => (strlen($poll->title))? $poll->title : $lesson->title,
                        'begin'      => $pollBeginRelativeDate->get(Zend_Date::DATES),
                        'end'        => $pollEndRelativeDate->get(Zend_Date::DATES),
                    )
                );
                $messenger->setRoom('subject', $lesson->CID);
                $messenger->send(HM_Messenger::SYSTEM_USER_ID, $user->MID);
            }
        }
    }
}
