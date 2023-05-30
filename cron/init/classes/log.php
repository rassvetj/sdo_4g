<?php 
class Log_cCron extends Zend_Log {
/**  
 * EMERG = 0
 * ALERT = 1
 * CRIT = 2
 * ERR = 3
 * WARN = 4
 * NOTICE = 5
 * INFO = 6
 * DEBUG = 7
 * EXCEPTION = 8
 * SUCCESS = 9
*/
	protected $_format = false;
	protected $_mode = 'w'; //--перезаписываем файл.
	protected $_path = false;
	
	public $msg = array(		
		'BEGIN'					=> 'Запуск скрипта',
		'END'					=> 'Завершение скрипта',
		'DATA_IS_EMPTY'			=> 'Нет данных',
		'EXCEPTION'				=> 'Exception: "%s"',
		
		'OK_SCORE_UPDATE'		=> 'Оценка изменена студенту;%s;%s;в сессии;%s;%s;c;%s;на;%s;%s;%s;',
		'ERR_SCORE_UPDATE'		=> 'Не удалось изменить оценку студенту;%s;%s;в сессии;%s;%s;c;%s;на;%s;%s;%s;',
		'ERR_SEND_USER_TO_PAST'	=> 'Не удалось перевести в прошедшее обучение студента;%s;%s;в сессии;%s;%s;',
		'NOT_MATCHES'			=> 'Совпадения не найдены. Ни одна запись не была изменена.',		

		'ERR_SET_SCORE'			=> 'Не удалось изменить балл студенту;%s;%s;в сессии;%s;%s;c;%s;на;%s',
		'OK_SET_SCORE'			=> 'Балл успешно изменен студенту;%s;%s;в сессии;%s;%s;c;%s;на;%s',
		'OK_BLOCKED'			=> 'Заблокирован;%s;%s;%s',
		'OK_PROGRAMM_REMOVE_DESCRIPTION' => 'Удалено назначение на программу;ID студента;Код студента;ФИО;ID программы;Код программы;Программа;Дата назначения на программу',
		'OK_PROGRAMM_REMOVE'	=> 'Удалено назначение на программу;%s;%s;%s;%s;%s;%s;%s',
		'STUDENT_ASSIGN_REMOVE_DESCRIPTION'		=> 'Сообщение;ID студента;Код студента;ФИО;ID сессии;Код сессии;Сессия;Назначен;Дата назначения;Планируемая дата окончания;Балл;Программа студента;Программа сессии',
		'OK_STUDENT_ASSIGN_REMOVE'				=> 'Удалено назначение на сессию;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s',
		'ERR_STUDENT_HAS_BALL'					=> 'Невозможно удалить назначение из-за итогового бала;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s',
		
		'STUDENT_ASSIGN_ON_SUBJECT_DESCRIPTION' 	=> 'Описание сообщения;ID студента;Код студента;ФИО;ID сессии;ID сессии из 1С;Сессия;ID программы;Код программы;Программа;Дата начала;Дата окончания;Продление 1;Продление 2;Код подгруппы сессии (ин.яз.);Код подгруппы студента (ин.яз.)',
		'OK_STUDENT_ASSIGN_ON_SUBJECT' 				=> 'Студент успешно назначен на сессию;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s',
		'ERR_STUDENT_ASSIGN_ON_SUBJECT' 			=> 'Не удалось назначить студента на сессию;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s',
		'NOTICE_STUDENT_ASSIGN_ON_SUBJECT_SUBGROUP' => 'Студент не назначен на сессию из-за подгруппы;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s',
		'RECALCULATE_MARK_ON_LESSONS_DESCRIPTION' 	=> 'Описание сообщения;ID студента;Код студента;ФИО;ID сессии;ID сессии из 1С;Сессия;Балл до;Балл после;mark_current;mark_landmark;Начало сессии;Окончание сессии;Продление 1;Продление 2',
		'OK_RECALCULATE_MARK_ON_LESSONS' 			=> 'Итоговый балл изменен;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s',
	);
	
	public function setDefaultParams(){
		
		$this->_format =  (!$this->_format) ? ('%timestamp%;%priorityName% (%priority%);%message%'.PHP_EOL) : ($this->_format);
		
		$formatter = new Zend_Log_Formatter_Simple($this->_format);
		if(!$formatter){
			return false;
		}		
		$writer = new Zend_Log_Writer_Stream($this->_path, $this->_mode);		
		if(!$writer){
			return false;
		}		
		$writer->setFormatter($formatter);	
		
		$this->addWriter($writer);
		
		$this->addPriority('EXCEPTION', 8);
		$this->addPriority('SUCCESS', 9);
		
		return true;	
	}
	
	public function setFormat($format) {		
		$this->_format = $format;
	}
	
	public function setMode($mode) {
		$this->_mode = $mode;
	}
	
	public function setPath($path) {
		$this->_path = $path;
	}	

	public function addMessageTemplate($template_code, $template_msg) {
		if(isset($this->msg[$template_code])){
			return 'Error. Template already exist';
		}
		$this->msg[$template_code] = $template_msg;
		return true;
	}	

}