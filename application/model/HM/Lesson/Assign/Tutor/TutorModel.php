<?php
class HM_Lesson_Assign_Tutor_TutorModel extends HM_Model_Abstract
{
    # Степерь двойки для побитового сравения
	const ROLE_LECTOR 	= 1;
    const ROLE_PRACTICE = 2;
    const ROLE_LAB		= 4;
	
	const ROLE_ALL		= 'all';
	
	
	public static function getRolesName($role_id){
		$role = '';
		if(	(self::ROLE_LECTOR & $role_id) == self::ROLE_LECTOR	){
			$role .= ', л';			
		}
		
		if(	(self::ROLE_PRACTICE & $role_id) == self::ROLE_PRACTICE	){
			$role .= ', пр';			
		}
		
		if(	(self::ROLE_LAB & $role_id) == self::ROLE_LAB	){
			$role .= ', лаб';			
		}
		return trim($role, ', ');		
	}
	
	/**
	 * Есть ли указанная роль в переданном коде ролей
	 * $single_id - роль, которая ищится 
	 * $role_code - код роли в котором ищится указанная роль.
	*/
	public static function issetRole($single_id, $role_code){
		return ( ($single_id & $role_code) == $single_id ) ? true : false;
	}
	
	/**
	 * тьютор может быть назначен на сессию с несколькими ролями. Поэтому создается такая карта возможных ролей для определения роли.
	*/
	public static function getAvailableLectorRoleIDs(){
		return array(
			self::ROLE_LECTOR, 										
			self::ROLE_LECTOR + self::ROLE_PRACTICE, 				
			self::ROLE_LECTOR + self::ROLE_LAB, 					
			self::ROLE_LECTOR + self::ROLE_PRACTICE + self::ROLE_LAB,
		);		
	}
	
	public static function getAvailablePracticeRoleIDs(){
		return array(
			self::ROLE_PRACTICE, 										
			self::ROLE_PRACTICE + self::ROLE_LECTOR, 				
			self::ROLE_PRACTICE + self::ROLE_LAB, 					
			self::ROLE_PRACTICE + self::ROLE_LECTOR + self::ROLE_LAB,					
		);		
	}
	
	public static function getAvailableLabRoleIDs(){
		return array(
			self::ROLE_LAB, 										
			self::ROLE_LAB + self::ROLE_LECTOR, 				
			self::ROLE_LAB + self::ROLE_PRACTICE, 					
			self::ROLE_LAB + self::ROLE_PRACTICE + self::ROLE_LECTOR,		
		);		
	}
	
}