#!/usr/bin/env php
<?php
if (!defined('E_DEPRECATED')) {
    define('E_DEPRECATED', 8192);
}
if (!defined('E_USER_DEPRECATED')) {
    define('E_USER_DEPRECATED', 16384);
}
function shutdown() {
//    var_dump(xdebug_get_function_stack());
    var_dump(error_get_last());
}
// register_shutdown_function('shutdown');


//error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);// & ~E_NOTICE

// Указание пути к директории приложения
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Определение текущего режима работы приложения (по умолчанию production)
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));
define('APPLICATION_MODULE', 'ES');
define('HARDCODE_WITHOUT_SESSION', true);
/** Zend_Application */
require_once 'Zend/Application.php';

$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH.'/settings/config.ini'
);

$application->bootstrap();

/*
$cliApp = new \Symfony\Component\Console\Application(
	'eLearning Server Console Application', '4.4'
);

$cliApp->addCommands(array(
		new Es_Command_Wreport()
));

$cliApp->run();
*/
echo "\n";

$start = microtime(true);

//$filename = 'test.csv';
$filename = 'students.csv';
$attachment_file = 'manual.docx';

if (!file_exists($filename)) {
	echo 'ERROR: file "'.$filename.'" does not exist'."\n";
} else {
    if (!is_writable($filename)) {
		echo 'ERROR Writing file "'.$filename.'"'."\n";
	} else {			
		if (!file_exists($attachment_file)) {
			echo 'ERROR: attachment "'.$attachment_file.'" does not exist'."\n";
		} else {
			
			$data = file($filename);
			$total_row = count($data);
			if($total_row < 1) {
				echo 'ERROR: File is empty'."\n";
			} else {
				$count = 0;
				$attachment_content = file_get_contents($attachment_file); //-- Получаем контент вложения
				foreach($data as $k=>$row){
					$row = trim($row);
					if($row != ''){
						$fields = explode(';',$row);
						
						//$fio 		= $fields[0];
						$login 		= trim($fields[1]);
						$password 	= trim($fields[2]);
						$emailTo 	= trim($fields[3]);								
						
						
						//--отсылаем. Если нет ошибок, то делаем unset
						$validator = new Zend_Validate_EmailAddress();
						if (!$validator->isValid($emailTo)) {
							echo 'ERROR: Invalid E-Mail: '.$emailTo."\n";
						} else {	
							
							$mail = new Zend_Mail(Zend_Registry::get('config')->charset);
							
							$mail->setSubject('Вход в Личный кабинет студента РГСУ');
							
							
							$messageText = '';
							
							$messageText .= 'Уважаемый студент РГСУ, для повышения эффективности вашего взаимодействия с Университетом, а также для вашего удобства, для вас создан личный кабинет.<br><br>';
							$messageText .= 'Направляем Ваши учетные данные для доступа в личный кабинет учащегося студента.<br><br>';						
							$messageText .= '<span style="margin-left: 20px;">Адрес входа: <a href="http://sdo.rgsu.net/?utm_source=subscription">Sdo.rgsu.net</a></span><br><br>';
							$messageText .= '<span style="margin-left: 20px;">Логин: <b>'.$login.'</b></span><br><br>';
							$messageText .= '<span style="margin-left: 20px;">Пароль: <b>'.$password.'</b></span><br><br>';
							$messageText .= 'Инструкция во вложении, поможет вам освоить интерфейс системы.<br><br>';
							$messageText .= 'Интересующие Вас вопросы можно адресовать на почту Централизованного деканата <a href="mailto:dekanat@rgsu.net">dekanat@rgsu.net</a><br><br><br>';
							$messageText .= 'С Уважением Централизованный деканат РГСУ.<br>';
													
							//$content = file_get_contents($attachment_file); 							
							//$attachment = new Zend_Mime_Part($content);
							$attachment = new Zend_Mime_Part($attachment_content);
							$attachment->type = 'application/msword';
							//$attachment->type = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
							$attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
							$attachment->encoding = Zend_Mime::ENCODING_BASE64;
							//$attachment->filename = 'Инструкция по работе в личном кабинете студента.docx';							
							$attachment->filename = 'Instruction.docx';							
							$mail->addAttachment($attachment); 
						
							$mail->setFromToDefaultFrom();		
							//$mail->setFrom('dekanat@rgsu.net');							
							
							$mail->addTo($emailTo);						
							$mail->setBodyHtml($messageText, Zend_Registry::get('config')->charset);
							
							try {
								//$t = false;					
								if(!$mail->send()){
								//if(!$t) {
									echo 'ERROR Sending to: '.$emailTo."\n";
								} else {						
									echo 'Ok: '.$emailTo."\n";						
									unset($data[$k]); //--удаляем строку, на которую уже отослали письмо.													
									sleep(4); //--задержка в 4 сек
								}
							} catch (Zend_Mail_Exception $e) {                
								echo 'ERROR Exception: '.$e->getMessage()."\n";
							}	
						}				
						$count++;
						if($count == 1) { //--Читаем файл по 1 строке. Потом поменять на большее число. 100, например
							break;
						}
					}
				}
				
				if(count($data) < $total_row){ //--Если было удаление строк.
					$fp=fopen($filename,'w'); 
					fputs($fp,implode('',$data)); 
					fclose($fp);
				}
			}
		}		
	}		
}
echo "_______________________________________________________\n";
echo 'Sending is complete';
echo "\n";

$time = microtime(true) - $start;
printf('Script work %.4F s.', $time);

?>
