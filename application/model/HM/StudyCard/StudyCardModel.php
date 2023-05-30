<?php
class HM_StudyCard_StudyCardModel extends HM_Model_Abstract
{
	
	const VID_BASE  		= 'Итоговый';
	const VID_PRACTIC  		= 'Практика';
	const VID_COURSE_WORK  	= 'Курсовая';
	
	const TIPE_ZACHET  		= 'Зачет';
	const TIPE_EXAM 		= 'Экзамен';
	const TIPE_TEST		    = 'Контрольная работа';
	
	
	static public function getTypes() {
        return array(
            'Зачет'    	=> _('Зачет'),
			'Экзамен'	=> _('Экзамен'),                        
        );
    }
	
	static public function getMark() {
        return array(
            'Зачтено'    		=> _('Зачтено'),
			'Отлично'    		=> _('Отлично'),            
            'Хорошо'    		=> _('Хорошо'),
            'Удовлетворительно'	=> _('Удовлетворительно'),            
        );
    }
}