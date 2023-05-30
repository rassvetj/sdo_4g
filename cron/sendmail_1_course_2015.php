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
//----------------------------------------------------------------------------------------------------------------------
$header_send="<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml'>
  <head>
    <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
    <title>Визитка</title>
    <style type='text/css'>
      #outlook a{padding:0;}
      .ReadMsgBody{width:100%;} .ExternalClass{width:100%;}
      .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {line-height: 100%;}
      body, table, td, p, a, li, blockquote{-webkit-text-size-adjust:100%; -ms-text-size-adjust:100%;}
      table, td{mso-table-lspace:0pt; mso-table-rspace:0pt;}
      img{-ms-interpolation-mode:bicubic;}
      body{margin:0; padding:0;}
      img{border:0; height:auto; line-height:100%; outline:none; text-decoration:none;}
      table{border-collapse:collapse !important;}
      body, #bodyTable, #bodyCell{height:100% !important; margin:0; padding:0; width:100% !important;}

      #bodyCell{padding:20px;}
      #templateContainer{width:768px;}
      body, #bodyTable{
        /*@editable*/ background-color:#FFFFFF;
      }
      #bodyCell{
        /*@editable*/ 
      }
      #templateContainer{
        /*@editable*/ border:1px solid #EEEEEE;
      }
      h1{
        display:block;
        margin-top:0;
        margin-right:0;
        margin-bottom:10px;
        margin-left:0;
        /*@editable*/ color:#666666 !important;
        /*@editable*/ font-family:Arial;
        /*@editable*/ font-size:18px;
        /*@editable*/ font-style:normal;
        /*@editable*/ font-weight:normal;
        /*@editable*/ line-height:100%;
        /*@editable*/ letter-spacing:normal;
        /*@editable*/ text-align:left;
      }
      h2{
        display:block;
        margin-top:0;
        margin-right:0;
        margin-bottom:10px;
        margin-left:0;
        /*@editable*/ color:#666666 !important;
        /*@editable*/ font-family:Helvetica;
        /*@editable*/ font-size:17px;
        /*@editable*/ font-style:normal;
        /*@editable*/ font-weight:bold;
        /*@editable*/ line-height:100%;
        /*@editable*/ letter-spacing:normal;
        /*@editable*/ text-align:left;
      }
      h3{
        display:block;
        margin-top:0;
        margin-right:0;
        margin-bottom:10px;
        margin-left:0;
        /*@editable*/ color:#666666 !important;
        /*@editable*/ font-family:Helvetica;
        /*@editable*/ font-size:16px;
        /*@editable*/ font-style:italic;
        /*@editable*/ font-weight:normal;
        /*@editable*/ line-height:100%;
        /*@editable*/ letter-spacing:normal;
        /*@editable*/ text-align:left;
      }
      h4{
        display:block;
        margin-top:0;
        margin-right:0;
        margin-bottom:10px;
        margin-left:0;
        /*@editable*/ color:#666666 !important;
        /*@editable*/ font-family:Helvetica;
        /*@editable*/ font-size:14px;
        /*@editable*/ font-style:italic;
        /*@editable*/ font-weight:normal;
        /*@editable*/ line-height:100%;
        /*@editable*/ letter-spacing:normal;
        /*@editable*/ text-align:left;
      }
      p {
        margin: 0 !important;
      }
      #templatePreheader{
        /*@editable*/ background-color:#FFFFFF;
        /*@editable*/ border-bottom:1px solid #EEEEEE;
      }
      .preheaderContent{
        /*@editable*/ color:#808080;
        /*@editable*/ font-family:Helvetica;
        /*@editable*/ font-size:10px;
        /*@editable*/ line-height:125%;
        /*@editable*/ text-align:left;
      }
      .preheaderContent a:link, .preheaderContent a:visited, .preheaderContent a .yshortcuts{
        /*@editable*/ color:#606060;
        /*@editable*/ font-weight:normal;
        /*@editable*/ text-decoration:underline;
      }
      #templateHeader{
        /*@editable*/ background-color:#FFFFFF;
      }
      .headerContent{
        /*@editable*/ height: 40px;
        /*@editable*/ color:#666666;
        /*@editable*/ font-family:Arial;
        /*@editable*/ font-size:12px;
        /*@editable*/ font-weight:normal;
        /*@editable*/ line-height:100%;
        /*@editable*/ padding-top:30px;
        /*@editable*/ padding-right:45px;
        /*@editable*/ padding-bottom:30px;
        /*@editable*/ padding-left:45px;
        /*@editable*/ vertical-align:middle;
      }
      .headerContent a:link, .headerContent a:visited, .headerContent a .yshortcuts{
        /*@editable*/ color:#3467a0;
        /*@editable*/ font-weight:normal;
        /*@editable*/ text-decoration:underline;
      }
      .headerLeft {
        /*@editable*/ background-image: url('http://rgsu.net/netcat_files/email/header-left.jpg');
        /*@editable*/ background-position: top left;
        /*@editable*/ background-repeat: no-repeat;
        line-height: 150%!important;
        text-align: left;
      }
      .headerRight {
        /*@editable*/ background-image: url('http://rgsu.net/netcat_files/email/header-right.jpg');
        /*@editable*/ background-position: top right;
        /*@editable*/ background-repeat: no-repeat;
        line-height: 150%!important;
        text-align: right;
      }
      #headerImage{
        height:auto;
        max-width:600px;
      }
      #templateBody{
        /*@editable*/ background-color:#FFFFFF;
        /*@editable*/ border-bottom:1px solid #EEEEEE;
      }
      .bodyContent{
        padding-top:45px;
        padding-right:45px;
        padding-bottom:70px;
        padding-left:45px;
        /*@editable*/ color:#666666;
        /*@editable*/ font-family:Arial;
        /*@editable*/ font-size:14px;
        /*@editable*/ line-height:150%;
        /*@editable*/ text-align:left;
      }
      .bodyContent a:link, .bodyContent a:visited,.bodyContent a .yshortcuts{
        /*@editable*/ color:#EB4102;
        /*@editable*/ font-weight:normal;
        /*@editable*/ text-decoration:underline;
      }

      .bodyContent img{
        display:inline;
        height:auto;
        max-width:560px;
      }
      #templateFooter{
        /*@editable*/ background-color:#eeeeee;
      }
      .footerContent{
        padding-top:20px;
        padding-right:45px;
        padding-bottom:20px;
        padding-left:45px;
        /*@editable*/ color:#666666;
        /*@editable*/ font-family:Arial;
        /*@editable*/ font-size:12px;
        /*@editable*/ line-height:150%;
        /*@editable*/ text-align:left;
      }
      .footerLeft{
        text-align: left;
        float: left;
      }
      .footerRight {
        text-align: right;
        float: right;
      }
      .footerContent a:link, .footerContent a:visited, .footerContent a .yshortcuts, .footerContent a span{
        /*@editable*/ color:#606060;
        /*@editable*/ font-weight:normal;
        /*@editable*/ text-decoration:underline;
      }
      .social-icon{
        display: block;
        padding: 3px;
      }
      .automate {
        color: #CCCCCC;
        font-size: 11px;
      }

    </style>
    </head>
    <body leftmargin='0' marginwidth='0' topmargin='0' marginheight='0' offset='0' style='-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;margin: 0;padding: 0;background-color: #FFFFFF;height: 100% !important;width: 100% !important;'>
      <center>
        <table align='center' border='0' cellpadding='0' cellspacing='0' height='100%' width='100%' id='bodyTable' style='-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;margin: 0;padding: 0;background-color: #FFFFFF;border-collapse: collapse !important;height: 100% !important;width: 100% !important;'>
          <tr>
            <td align='center' valign='top' id='bodyCell' style='-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;margin: 0;padding: 20px;height: 100% !important;width: 100% !important;'>
              <!-- Шаблон -->
              <table border='0' cellpadding='0' cellspacing='0' id='templateContainer' style='-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 768px;border: 1px solid #EEEEEE;border-collapse: collapse !important;'>
                <tr>
                  <td align='center' valign='top' style='-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;'>
                    <!-- Шапка -->
                    <table border='0' cellpadding='0' cellspacing='0' width='100%' id='templateHeader' style='-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;background-color: #FFFFFF;border-collapse: collapse !important;'>
                      <tr>
                        <td valign='top' class='headerContent headerLeft' background='http://rgsu.net/netcat_files/email/header-left.jpg' style='-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;height: 40px;color: #666666;font-family: Arial;font-size: 12px;font-weight: normal;line-height: 150%!important;padding-top: 30px;padding-right: 45px;padding-bottom: 30px;padding-left: 45px;vertical-align: middle;background-image: url(http://rgsu.net/netcat_files/email/header-left.jpg);background-position: top left;background-repeat: no-repeat;text-align: left;'>
                          <a href='http://rgsu.net/' style='-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;color: #3467a0;font-weight: normal;text-decoration: underline;'><img src='http://rgsu.net/netcat_files/email/newlogo-small.png' style='width: 110px;height: 50px;-ms-interpolation-mode: bicubic;border: 0;line-height: 100%;outline: none;text-decoration: none;max-width: 600px;' id='headerImage' alt='РГСУ'></a>
                        </td>
                        <td valign='top' class='headerContent headerRight' background='http://rgsu.net/netcat_files/email/header-right.jpg' width='360' style='-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;height: 40px;color: #666666;font-family: Arial;font-size: 12px;font-weight: normal;line-height: 150%!important;padding-top: 30px;padding-right: 45px;padding-bottom: 30px;padding-left: 45px;vertical-align: middle;background: url(http://rgsu.net/netcat_files/email/header-right.jpg) top right;background-repeat: no-repeat;text-align: right;'>
                          <p style='-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;margin: 0 !important;'>Call-центр: <a href='tel:+74952556767' style='-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;color: #3467a0;font-weight: normal;text-decoration: underline;'>+7 (495) 255-67-67</a></p>
                          <p style='-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;margin: 0 !important;'>Приемная комиссия: <a href='tel:+74952556777' style='-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;color: #3467a0;font-weight: normal;text-decoration: underline;'>+7 (495) 255-67-77</a></p>
                        </td>
                      </tr>
                    </table>
                    <!-- END Шапка -->
                  </td>
                </tr>
                <tr>
                  <td align='center' valign='top' style='-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;'>
                    <!-- BEGIN BODY // -->
                    <table border='0' cellpadding='0' cellspacing='0' width='100%' id='templateBody' style='-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;background-color: #FFFFFF;border-bottom: 1px solid #EEEEEE;border-collapse: collapse !important;'>
                      <tr>
                        <td valign='top' class='bodyContent' style='-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;padding-top: 45px;padding-right: 45px;padding-bottom: 70px;padding-left: 45px;color: #666666;font-family: Arial;font-size: 14px;line-height: 150%;text-align: left;'>";
$footer_send="</td>
                      </tr>
                    </table>
                    <!-- // END BODY -->
                  </td>
                </tr>
                <tr>
                  <td align='center' valign='top' style='-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;'>
                    <!-- Футер // -->
                    <table border='0' cellpadding='0' cellspacing='0' width='100%' id='templateFooter' style='-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;background-color: #eeeeee;border-collapse: collapse !important;'>
                      <tr>
                        <td valign='top' class='footerContent footerLeft' style='-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;padding-top: 20px;padding-right: 45px;padding-bottom: 20px;padding-left: 45px;color: #666666;font-family: Arial;font-size: 12px;line-height: 150%;text-align: left;float: left;'>
                            <p style='-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;margin: 0 !important;'>С любовью от РСГУ</p>
                            <p class='automate' style='-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;color: #CCCCCC;font-size: 11px;margin: 0 !important;'>Это сообщение сформировано автоматически, не отвечайте на него</p>
                        </td>
                        <td valign='top' class='footerContent footerRight' style='-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;padding-top: 20px;padding-right: 45px;padding-bottom: 20px;padding-left: 45px;color: #666666;font-family: Arial;font-size: 12px;line-height: 150%;text-align: right;float: right;'>
                          <table border='0' cellpadding='0' cellspacing='0' width='100%' id='templateFooter' style='-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse !important;'>
                            <tr>
                              <td valign='top' style='-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;'><a href='https://www.facebook.com/rgsu.official' class='social-icon' style='-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;display: block;padding: 3px;color: #606060;font-weight: normal;text-decoration: underline;'><img src='http://rgsu.net/netcat_files/email/fb.png' alt='Fb' style='-ms-interpolation-mode: bicubic;border: 0;height: auto;line-height: 100%;outline: none;text-decoration: none;'></a></td>
                              <td valign='top' style='-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;'><a href='https://vk.com/rgsu_official' class='social-icon' style='-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;display: block;padding: 3px;color: #606060;font-weight: normal;text-decoration: underline;'><img src='http://rgsu.net/netcat_files/email/vk.png' alt='VK' style='-ms-interpolation-mode: bicubic;border: 0;height: auto;line-height: 100%;outline: none;text-decoration: none;'></a></td>
                              <td valign='top' style='-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;'><a href='https://instagram.com/rssu_official' class='social-icon' style='-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;display: block;padding: 3px;color: #606060;font-weight: normal;text-decoration: underline;'><img src='http://rgsu.net/netcat_files/email/insta.png' alt='Insta' style='-ms-interpolation-mode: bicubic;border: 0;height: auto;line-height: 100%;outline: none;text-decoration: none;'></a></td>
                              <td valign='top' style='-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;'><a href='https://twitter.com/RGSU_official' class='social-icon' style='-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;display: block;padding: 3px;color: #606060;font-weight: normal;text-decoration: underline;'><img src='http://rgsu.net/netcat_files/email/tw.png' alt='Tw' style='-ms-interpolation-mode: bicubic;border: 0;height: auto;line-height: 100%;outline: none;text-decoration: none;'></a></td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                    </table>
                    <!-- // END Футер -->
                  </td>
                </tr>
              </table>
              <!-- // END Шаблон -->
            </td>
          </tr>
        </table>
      </center>
    </body>
</html>";
//----------------------------------------------------------------------------------------------------------------------





echo "\n";

$start = microtime(true);

//$filename = 'test.csv';
$filename = 'students_1_course_2015.csv';
//;HramovSV;HramovSV@rgsu.net
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
							$messageText .= $header_send;
							$messageText .= '<center>Дорогие друзья!</center><br>
Мы рады приветствовать Вас в личном кабинете студента Российского государственного социального университета. Мы рады за сделанный Вами выбор и искренне желаем, чтобы студенческие годы стали удивительным и незабываемым периодом в жизни.
Просим уделить несколько минут Вашего времени и <a style="color:#3467A0;" href="http://sdo.rgsu.net/student-certificate/?utm_source=mail&utm_campaign=student2015&utm_content=text">загрузить свою фотографию</a> в личном кабинете.
<br><br>'; /* http://sdo.rgsu.net/user/edit/ */

							$messageText .= 'Направляем Ваши учетные данные для доступа в личный кабинет учащегося студента.<br>';						
							$messageText .= '<span style="margin-left: 20px;">Адрес входа: <a style="color:#3467A0;" href="http://sdo.rgsu.net/?utm_source=mail&utm_campaign=student2015&utm_content=link">sdo.rgsu.net</a></span><br>';
							$messageText .= '<span style="margin-left: 20px;">Логин: <b><span style="font-family: Times; font-style: italic; font-size: 20px;">'.$login.'</span></b></span><br>';
							$messageText .= '<span style="margin-left: 20px;">Пароль: <b><span style="font-family: Times; font-style: italic; font-size: 20px;">'.$password.'</span></b></span><br><br>';
							
							$messageText .= 'Так же Вы можете использовать свой логин и пароль для подключения к сети WiFi <b>RGSU_STUDENT</b> на территории РГСУ.<br><br>';
							$messageText .= 'Инструкция во вложении, поможет Вам освоить интерфейс системы.<br><br>';
							$messageText .= 'Интересующие Вас вопросы можно адресовать на почту Централизованного деканата <a style="color:#3467A0;"  href="mailto:central_office@rgsu.net">central_office@rgsu.net</a><br><br>';							
							$messageText .= $footer_send;
													
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
							//$mail->setFrom('central_office@rgsu.net');							
							
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
						if($count == 100) { //--Читаем файл по 1 строке. Потом поменять на большее число. 100, например
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
