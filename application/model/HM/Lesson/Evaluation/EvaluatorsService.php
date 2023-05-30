<?php
class HM_Lesson_Evaluation_EvaluatorsService extends HM_Service_Abstract
{

    /**
     * Возвращает подходящих оценщиков
     * @param $lessonId
     * @return HM_Collection
     */
    public function getSuitableEvaluators($lessonId) {
        /** @var HM_Lesson_Evaluation_EvaluationSettingsService $lesService */
        $lesService = $this->getService('LessonEvaluationSettings');
        $evaluationSettings = $lesService->getEvaluationSettings($lessonId);

        $lessonService = $this->getService('Lesson');
        /** @var HM_Lesson_LessonModel $lesson */
        $lesson = $lessonService->getOne($lessonService->find($lessonId));

        $evaluation_type = $evaluationSettings->evaluation_type;
        //получаем список подходящих оценщиков
        if ($evaluation_type == HM_Lesson_Evaluation_EvaluationSettingsModel::EVALUATION_TYPE_STUDENT) {
            $suitableEvaluators = $lesson->getSuitableEvaluatorStudents($evaluationSettings->mark_required, $evaluationSettings->lesson_required);
        } else {
            /** @var HM_Subject_SubjectService $subjectService */
            $subjectService = $this->getService('Subject');
            $suitableEvaluators = $subjectService->getAssignedTutors($lesson->CID);
        }

        return $suitableEvaluators;
    }

    public function getSuitableEvaluatorsList($lessonId) {
        $suitableEvaluatorsCollection = $this->getSuitableEvaluators($lessonId);
        $suitableEvaluators = array();
        foreach ($suitableEvaluatorsCollection as $evaluator) {
            $suitableEvaluators[$evaluator->MID] = $evaluator->getName();
        }
        return $suitableEvaluators;
    }

    /**
     * Возвращает данные из таблицы schedule_evaluators,
     * где $userId является оценщиком, либо false
     * @param $userId
     * @param null $lessonId
     * @return bool|HM_Collection
     */
    public function isEvaluator($userId, $lessonId = null) {
        $where = array('MID_evaluator = ?' => $userId);
        if ($lessonId) $where['SHEID = ?'] = $lessonId;
        $result = $this->fetchAll($where);
        if (count($result)) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Назначает оценщиков (оценщик <-> оцениваемый), размер массивов должен совпадать
     * @param int $lessonId
     * @param array $evaluator оценщики - массив значений MID
     * @param array $evaluated оцениваемые - массив значений MID
     * @return bool
     */
    public function assignEvaluators($lessonId, $evaluator, $evaluated) {
        $evaluatorCount = count($evaluator);
        $evaluatedCount = count($evaluated);
        if ($evaluatorCount != $evaluatedCount) {
            return false;
        }
        if ($evaluatorCount == 0 || $evaluatedCount == 0) {
            return false;
        }

        /** @var PDO $db */
        $db = $this->getMapper()
            ->getAdapter()
            ->getAdapter()
            ->getConnection();

        //сохраняем в оценщиков базу
        try {
            $db->beginTransaction();

            $sql = 'INSERT INTO schedule_evaluators (SHEID, MID_evaluator, MID_evaluated) VALUES (?, ?, ?)';
            $stmt = $db->prepare($sql);
            $stmt->bindParam(1, $lessonId);
            $stmt->bindParam(2, $evaluator_mid);
            $stmt->bindParam(3, $evaluated_mid);

            //$evaluated_mid = оцениваемый, $evaluator_mid = оценщик
            for ($i = 0; $i < $evaluatorCount; $i++) {
                $evaluator_mid = (int)$evaluator[$i];
                $evaluated_mid = (int)$evaluated[$i];
                $stmt->execute();
            }

            $db->commit();

            //отправляем сообщение оценщикам
            /** @var HM_Lesson_LessonService $lessonService */
            $lessonService = $this->getService('Lesson');
            /** @var HM_Lesson_LessonModel $lesson */
            $lesson = $lessonService->getLesson($lessonId);

            /** @var HM_Subject_SubjectService $subjectService */
            $subjectService = $this->getService('Subject');
            $subject = $subjectService->getById($lesson->CID);

            $teacherId = $lesson->teacher;
            $lessonTitle = $lesson->title;
            $subjectName = $subject->name;
            /** @var HM_Messenger $messenger */
            $messenger = $this->getService('Messenger');

            for ($i = 0; $i < $evaluatorCount; $i++) {
                $evaluator_mid = (int)$evaluator[$i];
                $evaluated_mid = (int)$evaluated[$i];

                $messenger->setOptions(
                    HM_Messenger::TEMPLATE_PRIVATE,
                    array(
                        'text' => 'Вы назначены оценщиком на занятие &laquo;'.$lessonTitle.'&raquo;, курс &laquo;'.$subjectName.'&raquo;.',
                        'subject' => _('Личное сообщение'))
                );

                $messenger->send($teacherId, $evaluator_mid);
            }

        } catch (Exception $e) {
            $db->rollback();
            return false;
        }

        return true;
    }

    /**
     * Возвращает коллекцию оценщиков/оцениваемых по $lessonId
     * @param $lessonId
     * @return bool|HM_Collection
     */
    public function getEvaluators($lessonId) {
        $result = $this->fetchAll(array('SHEID = ?' => $lessonId));
        if (count($result)) {
            return $result;
        } else {
            return false;
        }
    }
}
