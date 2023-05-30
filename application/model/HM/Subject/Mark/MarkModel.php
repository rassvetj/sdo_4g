<?php
class HM_Subject_Mark_MarkModel extends HM_Model_Abstract
{
    const MARK_PATTERN = "^[1-9]{1}\d?$|^0$|^100$";

    const MARK_NOT_CONFIRMED = 0;
    const MARK_CONFIRMED = 1;
	
	const MAX_MARK_CURRENT	= 80;
	const MAX_MARK_LANDMARK = 20;
	
	//-- экспорт результатов обучения
	const BALL_CURRENT 	= 1; //--текущий балл по занятиям исключая занятия "зачет" и "экзамен"
	const BALL_EXAM 	= 2; //--итоговый балл по занятиям с "зачет" и "экзамен"
		
    const MARK_SYNC_1C = 1;

    protected $_primaryName = 'cid';

    static public function filterMark($mark)
    {
         if((!preg_match("/".self::MARK_PATTERN."/",(int) $mark)&&$mark>0) || $mark<0 || empty($mark)){
              return ($mark === 0.0 || $mark === 0 || $mark === "0") ? 0 : -1; // #11421
         }
         return $mark;
    }
	
	
	static public function filterMarkCurrent($mark)
	{
		if($mark > self::MAX_MARK_CURRENT){
			return self::MAX_MARK_CURRENT;
		}
		return $mark;
	}
	
	static public function filterMarkLandmark($mark)
	{
		if($mark > self::MAX_MARK_LANDMARK){
			return self::MAX_MARK_LANDMARK;
		}
		return $mark;
	}
	
	static function getFiveScaleMark($mark){				
		$mark = intval($mark);
		if($mark > 84){
			return 5;
		} elseif( 84 >= $mark && $mark >= 75 ){
			return 4;
		} elseif( 74 >= $mark && $mark >= 65 ){
			return 3;
		} elseif( 65 > $mark && $mark > 0 ){
			return 2;
		} 
		return false;
	}
	
	static public function getTextFiveScaleMark($mark, $exam_type){
		if($exam_type == HM_Subject_SubjectModel::EXAM_TYPE_TEST){
			if(in_array($mark, array(5,4,3)))	{ return 'зачтено'; }
			elseif($mark == 2)					{ return 'не зачтено'; }
			return 'неявка';
		}
		
		switch ($mark) {
			case 5:
				return 'отлично';				
			case 4:
				return 'хорошо';				
			case 3:
				return 'удовлетворительно';				
			case 2:				
				return 'неуд.';
		}
		return 'неявка';
	}
	
	public function getBall($get_biggest = false)
	{
		$sum_separated	= (empty($this->mark_current) && empty($this->mark_landmark)) ? false : $this->mark_current + $this->mark_landmark;
		
		if($sum_separated === false){
			return $this->mark;
		}
		
		if($get_biggest){
			return ($this->mark > $sum_separated) ? $this->mark : $sum_separated;
		}
		
		return $sum_separated;
	}
	
	public function getHumanInfo()
	{
		$data     = array();
		$codeList = HM_Subject_SubjectModel::getFailPassMessageList();
		if(empty($this->info) || $this->info === TRUE){ return false; }
		$info = json_decode($this->info, true);
		if(empty($info)){ return false; }
		foreach($info as $code => $item){
			$data[$code] = array(
				'description' => $codeList[$code],
				'lessons'     => is_array($item) ? $item : false,
			);
		}
		return $data;
	}
	
	public function getStudentBall()
	{
		if(empty($this->info) || $this->info === TRUE){ 
			return $this->getBall();
		}
		return $this->mark_current;
	}
	
	
}
