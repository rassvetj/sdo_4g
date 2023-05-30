<?php
class HM_Lesson_Evaluation_EvaluationSettingsService extends HM_Service_Abstract
{

    /**
     * @param $lessonId
     * @return bool|HM_Lesson_Evaluation_EvaluationSettingsModel
     */
    public function getEvaluationSettings($lessonId) {
        return $this->getOne($this->fetchAll(array('SHEID = ?' => $lessonId)));
    }
}
