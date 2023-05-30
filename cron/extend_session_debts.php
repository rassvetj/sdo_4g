#!/usr/bin/env php
<?php
/* Скрипт продлевает сессиис студентам, у которых недостаточно баллов для закрытия этой сессии. */
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
echo "This script extends sessions.\n";
echo "________________________________________\n";

$start = microtime(true);
	//--Берем все сессии, у которых поле [time_ended_debt] пустое. (Если поле НЕ пустое, значит это уже продленная сессия, и прдливать ее еще раз не надо.)
	//--проверяем каждого студента в каждой сессии на кол-во баллов. Если баллов < 65, продливаем на +10 месяцев от даты окончания сенссии. Сброс попыток? Где?
	//--иначе завершаем курс? Как?
	//--устанавливаем в сессия в поле time_ended_debt дату +10 месчцев при продлении у студентов этой сессии.
	$curTime = date('Y-m-d H:i:s');
	$acceptBall = 65; //--балл, при котором считается сессия закрыта и продлевать ее нет смысла
	$extendForSeconds = 26297438; //-- продляем на 10 месяцев
	
	$subjectService = new HM_Subject_SubjectService();
	
	try {
	$select = $subjectService->getSelect();
	$select->from(
		array('subj' => 'subjects'),
		array(
			'subjectId' => 'subj.subid',
			//'userId' => 'pe.MID',
			'userId' => 'st.MID',
			'subjectEnd' => 'subj.end',
			//'mark' => 'cm.mark',
			'mark' => new Zend_Db_Expr("CAST (REPLACE(cm.mark, ',','.') AS float)"),
			'studentId' => 'st.SID',
		)
	);
	$select->join(
		array('st' => 'Students'),
		'st.CID = subj.subid',
		array()
	);
	
	$select->join(
		array('pe' => 'People'),
		'pe.MID = st.MID',
		array()
	);
	
	$select->joinLeft( //--оценки может и не быть
		array('cm' => 'courses_marks'),
		'cm.cid = subj.subid AND cm.mid = st.MID',
		array()
	);
		
		
	$select->where($subjectService->quoteInto('subj.base = ?', HM_Subject_SubjectModel::BASETYPE_SESSION)); //--только сессии
	$select->where($subjectService->quoteInto('subj.end < ?', $curTime)); //--Если сессия уже закончилась по сроку давности.	
	$select->where('subj.time_ended_debt IS NULL'); //--только не продленные сессии.	
	
	$select->where('st.time_ended_debtor IS NULL'); //--только не продленные сессии у студента персонально.
	
	//$select->where($subjectService->quoteInto('cm.mark < ? OR cm.mark IS NULL', $acceptBall)); //--Если у студента меньше баллов пределенного значения.	
	$select->where($subjectService->quoteInto("CAST(REPLACE(cm.mark, ',','.') AS float) < ? OR cm.mark IS NULL", $acceptBall)); //--Если у студента меньше баллов пределенного значения.	
		
	$rows = $select->query()->fetchAll();
	
	} catch (Exception $e) {
		echo 'Exception: ',  $e->getMessage(), "\n";
		exit();
	}

	
	if(count($rows) > 0){
		$updateSession = array();
		foreach($rows as $i){
			$updateSession[$i['subjectId']]['studentIDs'][] = $i['studentId'];
			if(!isset($updateSession[$i['subjectId']]['subjectEnd']) || empty($updateSession[$i['subjectId']]['subjectEnd'])){
				//$updateSession[$i['subjectId']]['subjectEnd'] = $i['subjectEnd'];
				$updateSession[$i['subjectId']]['userIDs'][] = $i['userId'];
				$updateSession[$i['subjectId']]['subjectEndExt'] = date('Y-m-d', (strtotime($i['subjectEnd']) + $extendForSeconds));
			}
		}
		
		if( count($updateSession) > 0){
			$debtorService = new HM_Debtors_DebtorsService(); //--таблица Students.
			$testService = new HM_Test_TestService(); //--таблицв test для удвоения попыток тестирования.
			$updateSession = array_filter($updateSession);
			$criteria = '';
			foreach($updateSession as $sessionId => $v){				
				if(count($v['studentIDs']) > 0){
					$criteria = implode(',', $v['studentIDs']);
					$criteria = trim(trim($criteria), ',');					
					
					try {    					
						$isUpdStyudents = 0;
						
						$isUpdStyudents = $debtorService->updateWhere(	 //--обновляем записи таблицы Students				
							array(
								'time_ended_debtor' => $v['subjectEndExt'],							
							),
							$debtorService->quoteInto(
								array(
									'SID IN (?) AND ',
									'CID = ?',
								),
								array(
									new Zend_Db_Expr(implode(',', $v['studentIDs'])),						
									$sessionId, //--избыточный, но на всякий случай.
								)
							)						
						);	
																		
						if($isUpdStyudents > 0){ 
							//--если были продлены студенты. То продливаем и саму сессию. Продление сессина надо для препода - для наглядности и как факт продления сессии у стуеднтов в таблице Students
													
							$tt = $subjectService->updateWhere( //--обновляем строку subject. Обычный update не работает, поэтому делаем updateWhere
								array(									
									'time_ended_debt' => $v['subjectEndExt'],
								),
								$debtorService->quoteInto(
									'subid = ? AND time_ended_debt IS NULL',
									$sessionId
								)								
							);
														
							if($tt > 0 && count($v['userIDs']) > 0){
								//--Сброс попыток.
								//if(count($logDataTest) > 0){
									//foreach($logDataTest as $sessionId => $usersId){
										//if(count($v['userIDs']) > 0){												
											$dLog = Zend_Db_Table::getDefaultAdapter();	
											$whereLog = $dLog->quoteInto(
												'cid = '.trim($sessionId).' AND mid IN (?)',											
												new Zend_Db_Expr(implode(',', $v['userIDs']))
											);
											$isDelLog = $dLog->delete('loguser', $whereLog); //--удаляем лог
											
											$dataTest = array(
												'qty' => '0'
											);
											$isUpdCountTest = $dLog->update('testcount', $dataTest, $whereLog); //--сбрасываем кол-во попыток до 0
											var_dump('$isUpdCountTest='.$isUpdCountTest);
											//var_dump('$isDelLog='.$isDelLog);
											//var_dump('$isUpdCountTest='.$isDelLog);
										//}
									//}										
								//}
								
								
								
								
								
								
								
								
								/*
								//--анулируем попытки. Удаляем из лога записи студента. Таблица loguser
								$selectLogTest = $testService->getSelect();
																
								$selectLogTest->from(
									array('lg' => 'loguser'),
									array('stid')
								);
								$selectLogTest->where(
									$subjectService->quoteInto(										
										'cid = '.trim($sessionId).' AND mid IN (?)',										
										new Zend_Db_Expr(implode(',', $v['userIDs']))											
									)
								);	
								
																
								$logs = $selectLogTest->query()->fetchAll();
								$logDelIDs = array(); //--id для удаления
								if(count($logs) > 0){
									foreach($logs as $l){
										if(!empty($l['stid'])){
											$logDelIDs[] = $l['stid'];
										}
									}
								}
								
								$dLog = Zend_Db_Table::getDefaultAdapter();	
								//var_dump($v['userIDs']);
									
								if(count($v['userIDs']) > 0){	//--сбрасываем кол-во попыток для тестов
									$whereTestcount = $dLog->quoteInto(
										'cid = '.trim($sessionId).' AND mid IN (?)',											
										new Zend_Db_Expr(implode(',', $v['userIDs']))																					
									);
									
									$dataTest = array(
										'qty' => '0'
									);
									
									$isUpdCountTest = $dLog->update('testcount', $dataTest, $whereTestcount);
									if($isUpdCountTest > 0){
										
									} else {
										//echo 'Error test count reset. Session #'.$sessionId."\n"; //--А если попыток и так 0. Поэтому не выводим это сообщение.
									}
								}							
								
																
								if(count($logDelIDs) > 0){	//--Удаляем из лога факт прохождения теста.								
									$whereLog = $dLog->quoteInto(
										array(
											'stid IN (?) ',											
										),
										array(
											new Zend_Db_Expr(implode(',', $logDelIDs)),											
										)										
									);
									$isDelLog = $dLog->delete('loguser', $whereLog);
									if($isDelLog > 0){
										
									} else {
										echo 'Error test log reset. Session #'.$sessionId."\n";
									}
								}	
								*/
								
								echo 'Session #'.$sessionId."\n";
							} else {
								echo 'Error session update. Session #'.$sessionId."\n";
							}
						} else {
							echo 'Error user update. Session #'.$sessionId."\n";
						}					
					} catch (Exception $e) {
						echo 'Error Exception: ',  $e->getMessage(), "\n";
					}										
				} else {
					echo 'No users for update. Session #'.$sessionId."\n";
				}
			}			
		} else {
			echo 'No session for update'."\n";
		}		
	} else {
		echo 'No session for update'."\n";
	}		
		
$time = microtime(true) - $start;
echo "________________________________________\n";
printf('Script work %.4F s.', $time);
echo "\n________________________________________\n";

?>
