<?php
class HM_Scorm_Track_Data_DataModel extends HM_Model_Abstract
{
    const STATUS_COMPLETED = 'completed';
    const STATUS_PASSED = 'passed';
    const STATUS_FAILED = 'failed';
    const STATUS_INCOMPLETE = 'incomplete';
    const STATUS_INCOMPLETED = 'incompleted';
    const STATUS_NOT_ATTEMPTED = 'not_attempted';
    const STATUS_UNKNOWN = 'unknown';
    const STATUS_BROWSED = 'browsed';

    const STATUS_NOT_ATTEMPTED_RAW = 'not attempted';
    
    const MODE_NORMAL = 'normal';
    const MODE_BROWSE = 'browse';
    const MODE_REVIEW = 'review';

    const EXIT_SUSPEND = 'suspend';
    const EXIT_LOGOUT = 'logout';

    public function __construct($data)
    {
        $this->score_raw = '';
        $this->status = '';
        $this->total_time = '00:00:00';
        $this->session_time = '00:00:00';
        $this->timemodified = 0;

        if (($data['cmi.core.lesson_status'] == 'not attempted')
            || ($data['cmi.completition_status'] == 'not attempted')) {
            $data['status'] = 'not_attempted';
        }

        if (isset($data['cmi.core.score.raw'])) {
            $data['score_raw'] = $data['cmi.core.score.raw'];
        }

        if (isset($data['cmi.score.raw'])) {
            $data['score_raw'] = $data['cmi.score.raw'];
        }

        if (isset($data['cmi.core.session_time'])) {
            $data['session_time'] = $data['cmi.core.session_time'];
        }

        if (isset($data['cmi.session_time'])) {
            $data['session_time'] = $data['cmi.session_time'];
        }

        if (isset($data['cmi.core.total_time'])) {
            $data['total_time'] = $data['cmi.core.total_time'];
        }

        if (isset($data['cmi.total_time'])) {
            $data['total_time'] = $data['cmi.total_time'];
        }

        $data['mode'] = self::MODE_NORMAL;
        $data['credit'] = 'credit';

        parent::__construct($data);

    }

    static public function getStatuses()
    {
        return array(
            self::STATUS_COMPLETED => _('завершено'),
            self::STATUS_INCOMPLETE => _('не завершено'),
            self::STATUS_INCOMPLETED => _('не завершено'),
            self::STATUS_NOT_ATTEMPTED => _('не начиналось'),
            self::STATUS_UNKNOWN => _('неизвестно'),
            self::STATUS_PASSED => _('пройдено успешно'),
            self::STATUS_FAILED => _('пройдено неуспешно'),
            self::STATUS_BROWSED => _('просмотрено')
        );
    }

    static public function getStatus($status)
    {
        $statuses = self::getStatuses();
        if ($status == self::STATUS_NOT_ATTEMPTED_RAW)
            $status = self::STATUS_NOT_ATTEMPTED;
        return $statuses[$status];
    }

    static public function getNonMarkableStatuses()
    {
        return array(
            self::STATUS_NOT_ATTEMPTED,
            self::STATUS_INCOMPLETE,
            self::STATUS_INCOMPLETED,
            self::STATUS_UNKNOWN,
        );
    }

    static public function getSuccessfullStatuses()
    {
        return array(
            self::STATUS_COMPLETED,
            self::STATUS_PASSED,
        );
    }

    static public function getCompletionStatusVocabulary()
    {
        return array(
            self::STATUS_UNKNOWN,
            self::STATUS_COMPLETED,
            self::STATUS_INCOMPLETE,
            self::STATUS_NOT_ATTEMPTED_RAW
        );
    }

    static public function getSuccessStatusVocabulary()
    {
        return array(
            self::STATUS_UNKNOWN,
            self::STATUS_PASSED,
            self::STATUS_FAILED
        );
    }

    static public function getLessonStatusVocabulary()
    {
        return array(
            self::STATUS_PASSED,
            self::STATUS_COMPLETED,
            self::STATUS_FAILED,
            self::STATUS_INCOMPLETE,
            self::STATUS_BROWSED,
            self::STATUS_NOT_ATTEMPTED_RAW
        );
    }
    
    static public function getStatusVocabulary()
    {
        return array(
            self::STATUS_UNKNOWN,
            self::STATUS_COMPLETED,
            self::STATUS_INCOMPLETE,
            self::STATUS_NOT_ATTEMPTED_RAW,
            self::STATUS_PASSED,
            self::STATUS_FAILED
        );
    }

    public function merge(HM_Scorm_Track_Data_DataModel $data)
    {
        $this->mergeValues($data->getValues());
    }

    public function mergeValues($values)
    {
        if (is_array($values) && count($values)) {
            foreach($values as $key => $value) {
                $this->{$key} = $value;
            }
        }
    }

}