<?php
class HM_Question_Txt_TxtAdapter implements HM_Adapter_Interface
{
    private $_filename = null;

    public function __construct($filename = null)
    {
        $this->_filename = $filename;
    }

    public function setFileName($filename)
    {
        $this->_filename = $filename;
    }

    public function needToUploadFile()
    {
        return true;
    }

    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        
		$questions = array();
		$questions2 = array(); //--массив для заданий. "Тесты" без вариантов ответа
        if ((null !== $this->_filename) && file_exists($this->_filename) && is_readable($this->_filename)) {
            Zend_Registry::get('serviceContainer')->getService('Unmanaged')->detectFileEncoding($this->_filename);
            if ($lines = file($this->_filename)) {                
				for($i=0; $i<=count($lines); $i++) {
                    $line = $lines[$i];
                    if (preg_match('/^[0-9]+\.(.+)/u', $line, $matches)) {
                        
                        if (isset($matches[1])) {
                            $question = trim($matches[1]);
							$questions2[]['title'] = $question;
                            $answers = array();
                            $type = HM_Question_QuestionModel::TYPE_ONE;
                            $true = 0;

                            // Собираем ответы
                            for($j=$i+1; $j<=count($lines);$j++) {
                                $line = $lines[$j];
                                if (preg_match('/^[0-9]+\.(.+)/u', $line, $matches) || $j == (count($lines))) {
                                   
                                    if (count($answers)) {
                                        if ($true > 1) $type = HM_Question_QuestionModel::TYPE_MULTIPLE;
                                        $questions[] = array('title' => $question,  'qtype' => $type, 'answers' => $answers);
                                    }
                                    break;
                                }

                                if (preg_match('/^\(([\?\!])\)(.+)/u', $line, $matches)) {
                                    if (isset($matches[1]) && isset($matches[2])) {
                                        if ($matches[1] == '!') $true++;
                                        $answers[] = array('true' => ($matches[1] == '!') , 'text' => trim($matches[2]));
                                    }
                                }
                            }
                        }
                    }
                }
            }
            @unlink($this->_filename);
        }
		
		if(count($questions) < 1){ //--Если нет ни одного варианта ответа, то это тесты.
			return $questions2;
		}		
        return $questions;
    }
}