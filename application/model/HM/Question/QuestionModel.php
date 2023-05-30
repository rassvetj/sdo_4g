<?php
/**
 *  For good practice we need to refactor this code and move all switch state into child classes
 */
class HM_Question_QuestionModel extends HM_Model_Abstract
{    
    
    /* один верный вариант 
	 несколько верных вариантов 
	 на соответствие 
	 на упорядочивание 
	 на классификацию 
	 с прикрепленным файлом 
	 заполнение формы 
	 свободный ответ 
	 выбор по карте на картинке 
	 выбор из набора картинок 
	 внешний объект 
	 тренажер */
    
    //один верный вариант 
    const TYPE_ONE = 1;
    
     //несколько верных вариантов
    const TYPE_MULTIPLE = 2;
    
     //на соответствие
    const TYPE_CONFORMITY = 3;
    
     //на упорядочивание 
    const TYPE_SORT = 12;
    
     // на классификацию 
    const TYPE_CLASS = 13;
    
     //с прикрепленным файлом
    const TYPE_ATTACH = 4;
    
     //заполнение формы 
    const TYPE_FORM = 5;
    
     // модернизированный на заполнение формы 
    const TYPE_FILLINGAPS = 14;
    
     // свободный ответ
    const TYPE_FREE = 6;
    
     //выбор по карте на картинке 
    const TYPE_MAP = 7;
    
     //выбор из набора картинок
    const TYPE_IMAGE = 8;
    
     //внешний объект
    const TYPE_OBJECT = 9;
        
     //тренажер
    const TYPE_TRAINING = 10;

    const SEPARATOR = "~\x03~";

    static public function factory($data, $default = 'HM_Model_Abstract')
    {
        switch($data['qtype']) {
            case self::TYPE_ONE:
                return new HM_Question_Type_OneModel($data);
                break;
            case self::TYPE_MULTIPLE:
                return new HM_Question_Type_MultipleModel($data);
                break;
        }
        return new $default($data);
    }

    static public function getFeedbackType($type = null){
        $types = array(
                //один верный вариант
            self::TYPE_ONE        => 'RADIO',
             //несколько верных вариантов
            self::TYPE_MULTIPLE   => 'CHECKBOX',
             //на соответствие
            self::TYPE_CONFORMITY => 'COMPARE',
             //на упорядочивание
            self::TYPE_SORT       => 'SORT',
                // на классификацию
            self::TYPE_CLASS      => 'CLASS',
            self::TYPE_FILLINGAPS => 'FILL'
        );
        return $types[$type];

    }


    public function validate($answers)
    {
        $adata = $this->proccessAdata();
        $qdata = $this->proccessQdata();

        switch($this->qtype){


        }
    }

    public function proccessAdata()
    {
        switch($this->qtype){
            case self::TYPE_SORT:
            case self::TYPE_CLASS:
            case self::TYPE_FILLINGAPS:
            case self::TYPE_CONFORMITY:
            case self::TYPE_MULTIPLE:
                $data = $this->adata;
                $result = explode(self::SEPARATOR, $data);
                $res = array();
                foreach($result as $key => $answer){
                    $res[$key + 1] = $answer;
                }
                $result = $res;
                break;
            case self::TYPE_ONE:
            default:
                $result = $this->adata;


        }

       return $result;
    }

    public function proccessQdata()
    {
        $data = explode(self::SEPARATOR, $this->qdata);

        switch($this->qtype){
            case HM_Question_QuestionModel::TYPE_FILLINGAPS:
                $result = explode(self::SEPARATOR, $this->qdata);
                $resArr = array();
                $i = 1;

                foreach($result as $k => $res){
                    if($temp = unserialize($res)){
                        //pr($temp['right']);
                        //pr(array('question' => $i, 'val' => $temp['right']));
                        $resArr[] = array('question' => $k, 'val' => $temp['right']);

                    }

                }

                $result = $resArr;
                break;
            case HM_Question_QuestionModel::TYPE_CONFORMITY:
            case HM_Question_QuestionModel::TYPE_CLASS:
            case HM_Question_QuestionModel::TYPE_SORT:
                $result = explode(self::SEPARATOR, $this->qdata);

                $name =  array_shift($result);

                $count = (int) count($result) / 3;
                $res = array();
                for($i = 0; $i < $count; $i++){
                    $questionArr = array_slice($result, 0, 3);
                    array_shift($result);
                    array_shift($result);
                    array_shift($result);

                    $res[] = array('question' => $questionArr[0], 'name' => $questionArr[1], 'group' => $questionArr[2]);
                }
                $result = $res;
                break;
            case HM_Question_QuestionModel::TYPE_ONE:
            default:
                $result = explode(self::SEPARATOR, $this->qdata);
                $name =  array_shift($result);

                $keys = array();
                $values = array();
                foreach($result as $key => $value){
                    if($key == 0 || $key %2 == 0){
                       $keys[] =  $result[$key];
                    }
                }
                $result = $keys;

                break;
        }

        return $result;
    }

    public function isKnowledgeBaseQuestion()
    {
        if ($this->tests) {
            foreach($this->tests as $test) {
                if (!$test->subject_id) return true;
            }

            return false;
        }

        return null;
    }

    public function getTitle()
    {
        if (!$this->title) {
            $data = explode(self::SEPARATOR, $this->qdata);
            if (isset($data[0])) {
                $this->title = $data[0];
            }
        }

        return $this->title;
    }

    public function getAnswerData()
    {
        return array((int) $this->adata - 1 => true);
    }

    public function getAnswers()
    {
        if (!$this->answers) {
            $answers = array();
            $qdata = explode(self::SEPARATOR, $this->qdata);
            if (is_array($qdata) && count($qdata)) {
                $adata = $this->getAnswerData();
                $index = 0;
                foreach($qdata as $key => $answer) {
                    if ($key > 1 && $key % 2 == 0) {
                        $answers[] = array(
                            'title' => $answer,
                            'true' => (isset($adata[$index]) && $adata[$index])
                        );
                        $index++;
                    }
                }
            }
            $this->answers = $answers;
        }

        return $this->answers;
    }

    public function couldBeExportedToTxt()
    {
        return in_array($this->qtype, array(self::TYPE_ONE, self::TYPE_MULTIPLE));
    }

    public function exportToTxt()
    {
        $txt = $this->getTitle()."\r\n";
        $answers = $this->getAnswers();
        if (is_array($answers) && count($answers)) {
            foreach($answers as $answer) {
                $txt .= sprintf('(%s) %s', ($answer['true'] ? '!' : '?'), $answer['title'])."\r\n";
            }
        }

        return $txt;
    }

}