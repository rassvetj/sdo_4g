<?php
class HM_Lesson_Evaluation_EvaluationSettingsModel extends HM_Model_Abstract
{
    const EVALUATION_TYPE_TEACHER = 0;
    const EVALUATION_TYPE_STUDENT = 1;
    const EVALUATION_TYPE_TUTOR   = 2;
    const EVALUATION_TYPE_SELF    = 3;

    const EVALUATION_MODE_UNDEFINED = -1;
    const EVALUATION_MODE_AUTO = 0;
    const EVALUATION_MODE_MANUAL = 1;

    static public function getEvaluationTypes(){
        return array(
            self::EVALUATION_TYPE_TEACHER => _('Оценка преподавателем'),
            self::EVALUATION_TYPE_STUDENT => _('Взаимная оценка'),
            self::EVALUATION_TYPE_TUTOR   => _('Экспертная оценка'),
            self::EVALUATION_TYPE_SELF    => _('Самооценка')
        );
    }
}
