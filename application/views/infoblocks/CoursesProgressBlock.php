<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';
require_once APPLICATION_PATH . '/views/helpers/Score.php';

class HM_View_Infoblock_CoursesProgressBlock extends HM_View_Infoblock_ScreenForm
{
    protected $id = 'CoursesProgress';

    public function coursesProgressBlock($title = null, $attribs = null, $options = null)
    {
        $services = Zend_Registry::get('serviceContainer');

        $subjects = $services->getService('Student')->getSubjects()->getList('subid');

        if (count($subjects)) {
            $lessonAssigns = $services->getService('LessonAssign')->fetchAllDependenceJoinInner('Lesson', $services->getService('Lesson')->quoteInto(
                array(
                    'self.MID = ?',
                    ' AND Lesson.typeID IN (?)',
                    ' AND Lesson.CID IN (?)',
                    ' AND Lesson.isfree != ?',
                ), array(
                    $this->getService('User')->getCurrentUserId(),
                    array(HM_Event_EventModel::TYPE_COURSE, HM_Event_EventModel::TYPE_LECTURE),
                    $subjects,
                    HM_Lesson_LessonModel::MODE_FREE_BLOCKED
                ))
            );

            if (count($lessonAssigns)) {

                $courses = array();
                foreach ($lessonAssigns as $lessonAssign) {

                    $lesson = $lessonAssign->lessons->current();
                    $params = $lesson->getParams();
                    if (!$params['module_id']) continue;

                    $course = array(
                        'isfree' => $lesson->isfree,
                        'lesson' => $lesson,
                        'lessonAssign' => $lessonAssign,
//                        'lessonUrl' => $this->view->url(array('action' => 'my', 'controller' => 'list', 'module' => 'lesson', 'subject_id' => $lesson->CID), false, true) . '/#' . $lesson->SHEID,
                        'launchUrl' => $this->view->url(array('action' => 'index', 'controller' => 'execute', 'module' => 'lesson', 'lesson_id' => $lesson->SHEID, 'subject_id' => $lesson->CID), false, true),
                        'statsUrl' => $this->view->url(array(
                            'action' => 'listlecture',
                            'controller' => 'result',
                            'module' => 'lesson',
                            'lesson_id' => $lesson->SHEID,
                            'subject_id' => $lesson->CID,
                            'userdetail' => 'yes' . $userId = $this->getService('User')->getCurrentUserId(),
                            'switcher' => 'listlecture',
                        ), false, true),
                        'isStatsAllowed' => true, //($lessonAssign->V_DONE != HM_Lesson_Assign_AssignModel::PROGRESS_STATUS_NOSTART), // in_array($lesson->material->format, HM_Course_CourseModel::getInteractiveFormats()), // @todo: не показывать те форматы, которые в принципе не имеют статистики
                        'isLaunchAllowed' => true, //($lessonAssign->V_DONE != HM_Lesson_Assign_AssignModel::PROGRESS_STATUS_DONE),
                        'progress' => (int)$this->getService('Lesson')->getTotalCoursePercent($lesson->SHEID, $this->getService('User')->getCurrentUserId(), $params['module_id']),
                    );
                    if ($lesson->getType()==HM_Event_EventModel::TYPE_COURSE){
                        $res=$this->getService('Course')->getOne($this->getService('Course')->find($params['module_id']));
                        $course['new_window']=$res->new_window;
                    }
                    if (!$lesson->isfree) {
                		if ($lesson->timetype == 2) {
                		    $datetime = _('не ограничено');
                		} elseif($lesson->timetype == 1){

                            $begin = $lesson->getBeginDateRelative();
                            $end = $lesson->getEndDateRelative();

            				if(!$begin) {
            					$datetime = sprintf(_("до %s "), $end);
            				} elseif(!$end) {
            					$datetime = sprintf(_("с %s "), $begin);
            				} elseif ($begin != $end) {
            					$datetime = sprintf(_("с %s по %s"), $begin, $end);
            				} else {
            					$datetime = $begin;
            				}
                		} else {
                			$begin = $lesson->getBeginDate();
                			$end = $lesson->getEndDate();
                			if ($begin == $end)	$datetime = sprintf(_("%s, с %s по %s"), $begin, $lesson->getBeginTime(), $lesson->getEndTime());
                			elseif(!$end)		$datetime = sprintf(_("с %s "), $begin);
                			else $datetime = sprintf(_("с %s по %s"), $begin, $end);
                        }

                        $course['datetime'] = $datetime;
                        $course['datetimeLabel'] = $lesson->recommend ? _('рекомендуемое время выполнения') : _('время выполнения');
                        
                        $course['status'] = ($lessonAssign->V_STATUS == -1) ? 'incomplete' : 'passed';
                        $course['statusLabel'] = $this->view->score(array(
                            'score' => $lessonAssign->V_STATUS,
                            'user_id' => $userId,
                            'lesson_id' => $lesson->SHEID,
                            'scale_id' => $lesson->getScale(),
                            'mode' => HM_View_Helper_Score::MODE_DEFAULT,
                        ));
                        
                    } else {
                        
                        switch ($lessonAssign->V_DONE) {
                        	case HM_Lesson_Assign_AssignModel::PROGRESS_STATUS_NOSTART:
                        		$course['status'] = 'not-attempted';
                        		$course['statusLabel'] = _('не начат');
                        		break;
                        	case HM_Lesson_Assign_AssignModel::PROGRESS_STATUS_INPROCESS:
                        		$course['status'] = 'incomplete';
                        		$course['statusLabel'] = _('в процессе');
                        		break;
                        	case HM_Lesson_Assign_AssignModel::PROGRESS_STATUS_DONE:
                    		    $course['status'] = 'passed';
                    		    $course['statusLabel'] = _('завершен');
                        		break;
                        }
                        
                    }

                    if (!isset($courses[$params['module_id']])) {
                        $courses[$params['module_id']] = $course; // например, если чела дважды назначили на один и тот же курс - результаты удваиваются; 
                    }
                }

                uasort($courses, array('HM_View_Infoblock_CoursesProgressBlock', '_sort'));
                $this->view->courses = $courses;
            }
        }

		$content = $this->view->render('coursesProgressBlock.tpl');
        if ($title == null) return $content;
        return parent::screenForm($title, $content, $attribs);
    }
    
    public function _sort($course1, $course2)
    {
        if ($course1['subject_id'] == $course2['subject_id']) {
            if ($course1['lesson']->order == $course2['lesson']->order) {
                return ($course1['lesson']->SHEID < $course2['lesson']->SHEID) ? -1 : 1;
            } else {
                return ($course1['lesson']->order < $course2['lesson']->order) ? -1 : 1;
            }
        } else {
            return ($course1['subject_id'] < $course2['subject_id']) ? -1 : 1;
        }
    }        
}