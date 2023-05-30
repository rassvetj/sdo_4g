<?php
class StudentCertificate_SetwidgetController extends HM_Controller_Action_Crud {

	protected $_studCertService = null;

    protected $_studentCertificateID  = 0;
	

	public function init()
    {	
		$this->_userService = $this->getService('User');
		parent::init();
    }

    public function indexAction()
    {				
		$this->view->setHeader(_('Вывод виджетов на главняю страницу.'));
		
		//--обображаем виджеты новые у всех судентов на главной страничке
		$this->getHelper('viewRenderer')->setNoRender();
		
		$config = Zend_Registry::get('config');
			
		//--РАзобрать, как сделать запрос через Zend Select 
		//--Строка ниже не работает. 
		//$this->getService('User')->fetchAll('SELECT * FROM [sdo_test].[dbo].[People] ORDER BY MID OFFSET 0 ROWS FETCH NEXT 10 ROWS ONLY');		
		$connOptions = array("UID"=>"", "PWD"=>"", "Database"=>"sdo_test");
		$conn = sqlsrv_connect( "srv-sql-dev", $connOptions );		  
		$query_check_user='SELECT [MID], [mid_external] FROM [sdo_test].[dbo].[People] ORDER BY [MID] OFFSET 0 ROWS FETCH NEXT 100 ROWS ONLY';
		
		$result_user=sqlsrv_query($conn,$query_check_user, array(), array( "Scrollable" => SQLSRV_CURSOR_KEYSET ));
		
		$row_count = sqlsrv_num_rows($result_user);
		
		$row_count = 0; //--Для работы скрипта закомментировать эту строку
		
		$sdudID = array();
		if($row_count > 0){
			while( $row = sqlsrv_fetch_array( $result_user, SQLSRV_FETCH_ASSOC) ) {
				
				$roles = $this->_userService->getUserRoles($row['MID']);	
				if( //--Если это студент или конечный юзер
				(	in_array('student',$roles) ||
					in_array('enduser',$roles)
				) && ( //--И это не админ и не тьютор
					!in_array('admin', $roles) 		&&
					!in_array('tutor', $roles)		&&
					!in_array('dean', $roles)		&&
					!in_array('developer', $roles)	&&
					!in_array('manager', $roles)	&&
					!in_array('supervisor', $roles)	&&
					!in_array('teacher', $roles)				
				)){
					$len = strlen($row['mid_external']);								
					if($len == 6){ //--Если код студента 6 цифр - это студент. Иначе не студент
						$sdudID[] = $row['MID'];					
					}							
				}	
			}			
		}
		
		$infoblock = $this->getService('infoblock');		
		
		//10, 11, 12, 13 - для того, что бы не было конфликтов с прочими инфоблоками.		
		if(count($sdudID) > 0){
			echo 'Инфоблоки отображены на глявной странице у след. студентов:<hr>';
			foreach($sdudID as $id){
				$tt = $infoblock->fetchAll("role = 'enduser' AND user_id='".$id."'");
				$user_block = array();
				foreach($tt as $t){
					$user_block[] = $t->block;
				}
				
				echo $id;
				
				if(!in_array('cardStudent', $user_block)){
					$data_1 = array(  'role'     => 'enduser',
									'user_id'  => $id,
									'block'    => 'cardStudent',
									'x'        => 10,
									'y'        => 0,						
					);
					$isInsert_1 = $infoblock->insert($data_1);
					if(!$isInsert_1) {
						echo ' - ОШИБКА-1 - ';
					}								
				}
				
				if(!in_array('recordCardBlock', $user_block)){
					$data_2 = array(  'role'     => 'enduser',
									'user_id'  => $id,
									'block'    => 'recordCardBlock',
									'x'        => 11,
									'y'        => 0,						
					);
					$isInsert_2 = $infoblock->insert($data_2);
					
					if(!$isInsert_2) {
						echo ' - ОШИБКА-2 - ';
					}			
				}
				
				if(!in_array('certificateBlock', $user_block)){
					$data_3 = array(  'role'     => 'enduser',
									'user_id'  => $id,
									'block'    => 'certificateBlock',
									'x'        => 12,
									'y'        => 0,						
					);
					$isInsert_3 = $infoblock->insert($data_3);
										if(!$isInsert_3) {
						echo ' - ОШИБКА-3 - ';
					}								
				}
					
				if(!in_array('ticketBlock', $user_block)){
					$data_4 = array(  'role'     => 'enduser',
									'user_id'  => $id,
									'block'    => 'ticketBlock',
									'x'        => 13,
									'y'        => 0,						
					);
					$isInsert_4 = $infoblock->insert($data_4);
					if(!$isInsert_4) {
						echo ' - ОШИБКА-4 - ';
					}		
				}				
				echo '<br>';
			}
		}
		else {
			echo 'Нет ни одного студента в текущей выборке.';
			
		}	

		//2677 студентов.		
		//20 без кода студента
		//2709 всего пользователей	
		
		
	}
	

}