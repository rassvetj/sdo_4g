<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';
class HM_View_Infoblock_ScheduledAccessToSubjectBlock extends HM_View_Infoblock_ScreenForm
{
    const MAX_ITEMS = 10;

    protected $id = 'schedule';

    public function scheduledAccessToSubjectBlock($title = null, $attribs = null, $options = null)
    {
        $subject = $options['subject'];
        $user = $options['user'];

        if($user && Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR))){
            $assigns = $this->getService('LessonAssign')->fetchAllDependenceJoinInner('Lesson', $where = $this->getService('Lesson')->quoteInto(
                    array('self.MID = ?', ' AND Lesson.CID = ?', ' AND Lesson.isfree = ?'),
                    array(
                        (int) $user,
                        $subject->subid,
                        HM_Lesson_LessonModel::MODE_PLAN,
                    ))
            );
        } else {
            $assigns = $this->getService('LessonAssign')->fetchAllDependenceJoinInner('Lesson', $where = $this->getService('Lesson')->quoteInto(
                    array('self.MID = ?', ' AND Lesson.CID = ?', ' AND Lesson.isfree = ?'),
                    array(
                        (int) $this->getService('User')->getCurrentUserId(),
                        $subject->subid,
                        HM_Lesson_LessonModel::MODE_PLAN,
                    ))
            );
        }

//         exit($where);

        $results = $lessonsCache = array();
        foreach($assigns as $assign) {

            $lesson = $assign->lessons->current();
			if(!$lesson){ continue; }
            if (isset($lessonsCache[$lesson->SHEID])) {
            	continue; // см. #14732 - в scheduleID могут быть дубликаты; здесь решаем только внешнее проявление проблемы;
            } else {
            	$lessonsCache[$lesson->SHEID] = true;
            }
            
            if ($lesson->isRelative()) {
                $lesson->setAssigns(array($assign));
                $lesson->end = $lesson->getEndDateRelative();
            }

            if ($lesson->isConditionalLesson() && !$this->getService('Lesson')->isLaunchConditionSatisfied($lesson->SHEID, $lesson, false)){
                continue; // не выполнено условие
            }

//            timetype почему-то везде приводится к TIMETYPE_DATES, используем хак
//            $datetimeFormat = ($lesson->timetype == HM_Lesson_LessonModel::TIMETYPE_TIMES) ? 'd.m H:i' : 'd.m';
            $datetimeFormat = (($lesson->timetype == HM_Lesson_LessonModel::TIMETYPE_DATES) && (date('H:i', strtotime($lesson->begin)) != '00:00')) ? 'd.m H:i' : 'd.m';

            if (
                ($lesson->timetype != HM_Lesson_LessonModel::TIMETYPE_FREE) &&
                strtotime($lesson->end) &&
                (strtotime($lesson->end) < time())
            ) {
                // время прошло
                if (
                    ($assign->V_STATUS == -1) &&
                    $lesson->vedomost &&
                    $lesson->recommend
                ) {
                    // ахтунг! оценки нет, но даты рекомендательные => можно исправить ситуацию
                    $lesson->setSortOrder(HM_Lesson_LessonModel::SORT_ORDER_OVERDUE);
                    $result['date'] = _('до') . ' ' . date($datetimeFormat, strtotime($lesson->end));
                    $result['status'] = 'overdue';

                } else {
                    continue;
                }
            } else {
                // время не прошло или оно бесконечно
                if ($lesson->timetype == HM_Lesson_LessonModel::TIMETYPE_FREE) {
                    $lesson->setSortOrder(HM_Lesson_LessonModel::SORT_ORDER_FREE);
                    $result['status'] = 'infinite';
                    $result['date'] = 'O}{O';
                } else {
                    if (strtotime($lesson->begin) < time()) {
                        // уже идёт
                        $result['status'] = 'in-process';
                        $result['date'] = _('до') . ' ' . date($datetimeFormat, strtotime($lesson->end));
                    } else {
                        $result['status'] = 'not-started';
                        $result['date'] = _('c') . ' ' . date($datetimeFormat, strtotime($lesson->begin));
                    }
                }
            }
            $result['lesson'] = $lesson;
            $results[] = $result;
        }

        uasort($results, array('HM_View_Infoblock_ScheduledAccessToSubjectBlock', _sortLessons));
        $this->view->lessons = $results;
        $this->view->isBrs = $subject->mark_type;
        $content = $this->view->render('scheduledAccessToSubjectBlock.tpl');
        return parent::screenForm($title, $content, $attribs);
    }

    private function _sortLessons($lesson1, $lesson2)
    {
        /**
         * добавлена сортировка по установленному порядку, с большим приорететом чем сортировка по getSortOrder
         * @author Artem Smirnov
         * @date 17.01.2013
         */
        $sortOrder1 = $lesson1['lesson']->getSortOrder();
        $sortOrder2 = $lesson2['lesson']->getSortOrder();
        if($lesson1['lesson']->getOrder() == $lesson2['lesson']->getOrder()){
            if ($sortOrder1 == $sortOrder2) {
                $attrToCompare = (strtotime($lesson1['lesson']->begin) < time() && strtotime($lesson2['lesson']->begin) < time()) ? 'end' : 'begin';
                return $lesson1['lesson']->$attrToCompare < $lesson2['lesson']->$attrToCompare ? -1 : 1;
            } elseif ($sortOrder1 == HM_Lesson_LessonModel::SORT_ORDER_OVERDUE) {
                return -1;
            } elseif ($sortOrder2 == HM_Lesson_LessonModel::SORT_ORDER_OVERDUE) {
                return 1;
            } elseif ($sortOrder1 == HM_Lesson_LessonModel::SORT_ORDER_FREE) {
                return 1;
            } elseif ($sortOrder2 == HM_Lesson_LessonModel::SORT_ORDER_FREE) {
                return -1;
            }
        }
        else{
            return ($lesson1['lesson']->getOrder() > $lesson2['lesson']->getOrder())? 1:-1;
        }
    }
}