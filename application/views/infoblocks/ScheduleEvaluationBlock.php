<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';
class HM_View_Infoblock_ScheduleEvaluationBlock extends HM_View_Infoblock_ScreenForm
{
    protected $id = 'schedule';

    public function scheduleEvaluationBlock($title = null, $attribs = null, $options = null)
    {
        /** @var HM_Subject_SubjectModel $subject */
        $subject = $options['subject'];
        /** @var HM_Lesson_Evaluation_EvaluatorsService $lessonEvaluatorsService */
        $lessonEvaluatorsService = $this->getService('LessonEvaluators');
        /** @var Zend_Db_Table_Select $select */
        $select = $lessonEvaluatorsService->getSelect();

        $select->from(array('se' => 'schedule_evaluators'), array(
            'lesson_id' => 'sch.SHEID',
            'subject_id' => 'sch.CID',
            'title' => 'sch.title',
            'date_limit' => 'ses.date_limit',
        ));
        $select->joinLeft(array('sch' => 'schedule'), 'sch.SHEID = se.SHEID', array());
        $select->joinLeft(array('ses' => 'schedule_evaluation_settings'), 'ses.SHEID = se.SHEID', array());
        //$select->where('se.SHEID = ?', $lessonId);
        $select->where('sch.CID = ?', $subject->subid);
        $select->where('se.MID_evaluator = ?', $this->getService('User')->getCurrentUserId());
        $select->group(array(
            'sch.SHEID',
            'sch.CID',
            'sch.title',
            'ses.date_limit',
        ));

        $lessons = $select->query()->fetchAll();
        foreach ($lessons as &$lesson) {
            $lesson['date_limit'] = strtotime($lesson['date_limit']);
            if ($lesson['date_limit']) {
                $lesson['date_limit'] = date('d.m.Y', $lesson['date_limit']);
            }
            //сейчас url формируется только для занятий с типом "Задание"
            $lesson['url'] = $this->view->url(
                array(
                    'action' => 'extended',
                    'controller' => 'result',
                    'module' => 'lesson',
                    'subject_id' => $lesson['subject_id'],
                    'lesson_id' => $lesson['lesson_id']
                ),
                false,
                true
            );
        }

        $this->view->lessons = $lessons;

        $content = $this->view->render('scheduleEvaluationBlock.tpl');
        return parent::screenForm($title, $content, $attribs);
    }

}