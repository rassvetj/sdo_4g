<?php
class HM_Interview_InterviewModel extends HM_Model_Abstract
{

    const MESSAGE_TYPE_TASK      = 0; //Task
    const MESSAGE_TYPE_QUESTION  = 1; //Question
    const MESSAGE_TYPE_TEST      = 2; // to prove
    const MESSAGE_TYPE_ANSWER    = 3; // answer
    const MESSAGE_TYPE_CONDITION = 4; //some conditions
    const MESSAGE_TYPE_BALL      = 5; // total ball
    /**
     * Добавлен тип - не выполнено задание, для разграничения Interview без записей
     * @author Artem Smirnov
     * @date 19.02.2013
     */
    const MESSAGE_TYPE_EMPTY     = 6; //Empty

    protected $_primaryName = 'interview_id';

    const BALL_5_MAX = 100;
	const BALL_5_MIN = 85;
	
	const BALL_4_MAX = 84;
	const BALL_4_MIN = 75;
	
	const BALL_3_MAX = 74;
	const BALL_3_MIN = 65;
	
	const BALL_2_MAX = 64;
	const BALL_2_MIN = 1;
	
	const BALL_1_MAX = 0;
	const BALL_1_MIN = 0;
	
	
	public function getType(){
        $types = self::getTypes();
        return $types[$this->type];
    }


    public static function getTypes()
    {
        return array(
            self::MESSAGE_TYPE_TASK => _('Выдано задание'),
            self::MESSAGE_TYPE_QUESTION => _('Вопрос преподавателю'),
            self::MESSAGE_TYPE_ANSWER => _('Ответ преподавателя'),
            self::MESSAGE_TYPE_TEST => _('Решение на проверку'),            
            self::MESSAGE_TYPE_CONDITION => _('Требования на доработку'),
            self::MESSAGE_TYPE_BALL => _('Выставлена оценка'),
            self::MESSAGE_TYPE_EMPTY => _('Не выполнено')
        );
    }

    public static function getStudentTypes()
    {
        return array(
            //self::MESSAGE_TYPE_TASK => _('Задание'),
            //self::MESSAGE_TYPE_QUESTION => _('Вопрос преподавателю'),
            self::MESSAGE_TYPE_TEST => _('Решение на проверку'),
            //self::MESSAGE_TYPE_ANSWER => _('Ответ'),
            //self::MESSAGE_TYPE_CONDITION => _('Требования на доработку'),
            //self::MESSAGE_TYPE_BALL => _('Комментарии к оценке')
        );
    }

    public static function getTeacherTypes()
    {
        return array(
            //self::MESSAGE_TYPE_TASK => _('Задание'),
            //self::MESSAGE_TYPE_QUESTION => _('Вопрос'),
            //self::MESSAGE_TYPE_TEST => _('На проверку'),
            self::MESSAGE_TYPE_ANSWER => _('Ответ преподавателя'),
            self::MESSAGE_TYPE_CONDITION => _('Требования на доработку'),
            self::MESSAGE_TYPE_BALL => _('Выставлена оценка')
        );
    }
	
	
	
	# типы действий для прошедших сессий, в которых студент не прикрепил решение на проверку.
	public static function getTeacherLightTypes()
    {
        return array(            
            self::MESSAGE_TYPE_BALL => _('Выставлена оценка')
        );
    }
	
	/**
	 * типы для прошедших сессий, недоступных студенту.
	**/
	public static function getPastTeacherTypes()
    {
        return array(     
            self::MESSAGE_TYPE_BALL => _('Выставлена оценка')
        );
    }


    static public function factory($data, $default = 'HM_Lesson_LessonModel')
    {

        if (isset($data['type']))
        {
            switch($data['type']) {
                case self::MESSAGE_TYPE_ANSWER:
                    return parent::factory($data, 'HM_Interview_Answer_AnswerModel');
                    break;
               case self::MESSAGE_TYPE_TEST:
                    return parent::factory($data, 'HM_Interview_Test_TestModel');
                    break;
                case self::MESSAGE_TYPE_BALL:
                    return parent::factory($data, 'HM_Interview_Ball_BallModel');
                    break;
                case self::MESSAGE_TYPE_TASK:
                    return parent::factory($data, 'HM_Interview_Task_TaskModel');
                    break;
                case self::MESSAGE_TYPE_CONDITION:
                    return parent::factory($data, 'HM_Interview_Condition_ConditionModel');
                    break;
                case self::MESSAGE_TYPE_QUESTION:
                    return parent::factory($data, 'HM_Interview_Question_QuestionModel');
                    break;
                case self::MESSAGE_TYPE_EMPTY:
                    return parent::factory($data, 'HM_Interview_Task_TaskModel');
                    break;
                default:

                    break;
            }
        }
        if ($default != 'HM_Interview_InterviewModel') {
            return parent::factory($data, $default);
        }
    }


    public function getStyleClass()
    {

    }

    public function getIcon()
    {
        return 'images/content-modules/interview/' . $this->type . '.png';
    }

    public function getDate()
    {
        $date = new Zend_Date();
        $date->set($this->date);

        return $date->toString();

    }

	
	/**
	 * список диапазонов возможных баллов по 100 шкале в 4 бальной системе.
	 * return array
	*/
	static public function getBallListScales(){
		$scales = array();
		for($i = self::BALL_5_MAX; $i >= self::BALL_5_MIN; $i--){ $scales[5][$i] = $i; }
		for($i = self::BALL_4_MAX; $i >= self::BALL_4_MIN; $i--){ $scales[4][$i] = $i; }
		for($i = self::BALL_3_MAX; $i >= self::BALL_3_MIN; $i--){ $scales[3][$i] = $i; }
		for($i = self::BALL_2_MAX; $i >= self::BALL_2_MIN; $i--){ $scales[2][$i] = $i; }
		for($i = self::BALL_1_MAX; $i >= self::BALL_1_MIN; $i--){ $scales[1][$i] = $i; }
		return $scales;		
	}
	
	static public function getLanguageBallListScales()
	{
		return array(
			100 => _('Продвинутый'),
			60 	=> _('Базовый'),
			30 	=> _('Начальный'),
		);
	}
	
	
}
