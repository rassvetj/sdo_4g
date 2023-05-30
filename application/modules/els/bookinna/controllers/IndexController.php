<?php

class Bookinna_IndexController extends HM_Controller_Action {
		
	public function init(){	
		$this->_helper->layout()->disableLayout(); 
		$this->_helper->viewRenderer->setNoRender(true);		
		parent::init();
	}
	
	public function indexAction()
    {	
		ini_set('display_errors', 'Off');
		error_reporting(0);

		
		$allowedIPs = array( //--Список разрешенных ip. после переделать на файл .htaccess
			'192.168.132.220',
			'127.0.0.1',			
			'185.3.141.213', //--bookinna old ip
			'185.3.141.242', //--bookinna
		);
		
	

		$data = array();
		
		
			$request = $this->getRequest();
		
			//if($this->getService('User')->getCurrentUserId() == '5829') : //--Убрать эту проверку позже			
			if ($request->isPost()) {				
				if(!in_array($_SERVER['REMOTE_ADDR'], $allowedIPs) && !in_array($_SERVER['REMOTE_ADDR'], $allowedIPs)){
					$data = array(
						'error' => 'Incorrect IP address'
					);
				} else {
			
					$ulogin = $request->getParam('ulogin', false);
					$upassword = $request->getParam('upassword', false);
					
					$ulogin = trim($ulogin);
					$upassword = trim($upassword);
					
					$ulogin = ($ulogin != '') ? ($ulogin) : (false);
					$upassword = ($upassword != '') ? ($upassword) : (false);
					
					$data = array(
						'error' => 'User can not be found or incorrect data',										
					);
					
					
					if(!$ulogin || !$upassword){
						$data = array(
							'error' => 'Empty one or more parameters'
						);
					} else {
						
						$activeCodes = array(
							'512',
							'544',
							'66048',
							'66080',
							'262656',
							'262688',
							'328192',
							'328224',
						);
						
						$blockedCodes = array(
							'514',
							'546',
							'66050',
							'66082',
							'262658',										
							'262690',										
							'328194',
							'328226',
						);
			
						$this->_userService = $this->getService('User');
						$select = $this->_userService->getSelect();
						$select->from(array('p'=>'People'), 
							array(
								'LastName',
								'FirstName',
								'Patronymic',
								'Phone',
								'EMail',
								'BirthDate',
								'Gender',
								'mid_external' => 'p.mid_external',													
								'EMailEXT' => 'exte.email',													
							)
						);
						
						
						$select->joinLeft(array('exte' => 'student_ext_emails'),
							'exte.mid_external = p.mid_external',
							array()
						);
						
						$select->where($this->quoteInto('Login = ?', $ulogin)); 										
						
						$info = $select->query()->fetch();
						//if(count($info) < 1 || !is_array($info)){ //--Если студента нет с СДО, то и не ищем его в AD
							$data = array(
								'error' => 'User can not be found or incorrect data'
							);
						//} else {	
						
							$LDAP = array(
								'server' => 'srv-dc11.edu.local',					
								'port' => '389',
								'bindDN' => 'ldap_operations', 
								'bindPW' => 'QiSt1Z3W', 
							);
							
							$conn = ldap_connect($LDAP['server'], $LDAP['port']); 					
							
							ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3); 
							ldap_set_option($ldap, LDAP_OPT_REFERRALS,0);
																			
							$bb = ldap_bind($conn, 'edu\\'.$ulogin, $upassword);																
							if($bb === false){ //--коннектимся под админом и проверяем, есть ли пользователь. Если есть и заблочен, то возвращаем только статус
								
								$bb = ldap_bind($conn, $LDAP['bindDN'], $LDAP['bindPW']);
								$dn = 'OU=Students,DC=edu,DC=local';					
								$filter = "(sAMAccountName=".$ulogin.")";
								$attr = array('userAccountControl');								
								$search = ldap_search($conn,$dn,$filter,$attr);				
								$entries = ldap_get_entries($conn, $search); 
								
								$data = array(
									'error' => 'User can not be found or incorrect data',										
								);
								
								if(count($entries) > 0){
									foreach($entries as $i){
										if(in_array($i['useraccountcontrol'][0], $blockedCodes)){
											$data = array(
												'status' => ('Заблокирован'),
												//'status' => $i['useraccountcontrol'][0],
											);												
										}										
									}
								}
								
							} else {
								$dn = 'OU=Students,DC=edu,DC=local';					
								$filter = "(sAMAccountName=".$ulogin.")";								
								$attr = array('displayname', "samaccountname","sn","givenname","userprincipalname","title","extensionattribute1","company","department","telephonenumber","physicaldeliveryofficename","whenchanged","mail","description","company", 'organization', 'userAccountControl');
								$search = ldap_search($conn,$dn,$filter,$attr);				
								$entries = ldap_get_entries($conn, $search); 
								
								$email = ($info['EMail'] != '') ? ($info['EMail']) : (false); //--email из профиля
								
								if($email){
									$validator = new Zend_Validate_EmailAddress();
									$email = ( $validator->isValid($email) ) ? ($email) : (false);									
								}
								
								if(!$email){
									$email = ($info['EMailEXT'] != '') ? ($info['EMailEXT']) : (false); //--email из внешней тиблицы
									if($email){
										$validator = new Zend_Validate_EmailAddress();
										$email = ( $validator->isValid($email) ) ? ($email) : (false);									
									}								
								}
								
								
								if(count($entries) > 0){
									
									foreach($entries as $i){										
										$data = array(
											'fio' => ($i['displayname'][0]) ? ($i['displayname'][0]) : (false),
											'telephonenumber' => ($i['telephonenumber'][0]) ? ($i['telephonenumber'][0]) : (false),
											'email' => ($email) ? ($email) : ( ($i['mail'][0]) ? ($i['mail'][0]) : (false) ), //--email из AD
											'guid' => false,
											'organization' => ($i['company'][0]) ? ($i['company'][0]) : (false),
											
											'status' => ( in_array($i['useraccountcontrol'][0], $activeCodes)) ? ('Активный') : (in_array($i['useraccountcontrol'][0], $blockedCodes) ? ('Заблокированный') : (false)),	
											
											'course' => ((int) $i['title'][0]) ? ((int) $i['title'][0]) : (false),	
											'faculty' => ($i['department'][0]) ? ($i['department'][0]) : (false),
											
										);
									}							
								}
												
								$phone = ($info['Phone'] != '') ? ($info['Phone']) : (false);
								
								$guid = ($info['mid_external'] != '') ? ($info['mid_external']) : (false);
								
							
								$data['fio'] = (!$data['fio']) ? ($fio) : ($data['fio']);
								$data['telephonenumber'] = (!$data['telephonenumber']) ? ($phone) : ($data['telephonenumber']);
								
								$data['guid'] = (!$data['guid']) ? ($guid) : ($data['guid']);
								
								
								
								$status = false; //--статус. учится, отчислен и тд.
								$course = false; //--Курс
								$faculty = false; //--Факультет
								$select2 = $this->_userService->getSelect();
								$select2->from(array('rc'=>'record_cards'), 
									array(
										'status' => 'rc.StatusStud',																							
										'DateTake' => 'rc.DateTake',																						
										'Course' => 'rc.Course',							
										'Faculty' => 'rc.Faculty',							
									)
								);						
								$select2->where($this->quoteInto('StudyCode = ?', $info['mid_external'])); 
								$cards = $select2->query()->fetchAll();						
								if(count($cards) > 0){
									$lastDate = 0;
									foreach($cards as $c){
										$curD = strtotime($c['DateTake']);								
										if($lastDate <= $curD){ //--берем статус по последней дате.
											$lastDate = $curD;
											$status = $c['status'];											
											$course = $c['Course'];
											$faculty = $c['Faculty'];
										}								
									}									
									$data['status'] = ($status) ? ($status) : ($data['status']);
									$data['course'] = ($course) ? ($course) : ($data['course']);
									$data['faculty'] = ($faculty) ? ($faculty) : ($data['faculty']);
								}									
							}
							ldap_close($conn);
										
					}
				}				
				echo Zend_Json::encode($data);
			}	 	
			//endif;  
		exit();
	}	
}