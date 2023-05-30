<?php
class HM_DisabledPeople_Resume_ResumeModel extends HM_Model_Abstract
{
   public static function getTypes(){
        return array(
			'1' => 'Для учащихся',
			'2' => 'Для соискателей с опытом работа',
			'3' => 'Для школьников',			
		);
	}
}