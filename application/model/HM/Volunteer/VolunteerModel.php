<?php
class HM_Volunteer_VolunteerModel extends HM_Model_Abstract
{
    //--заявки на вступление в волонтеры
	const VOLUNTEER_NEW 	= 0; //--новая 
	const VOLUNTEER_APPROVE = 1; //--одобрена. Вы волонтер
	const VOLUNTEER_REJECT 	= 2; //--отклонена
	const VOLUNTEER_EXILE 	= 3; //--исключили
	
	//--заявки на участие в мероприятииях
	const EVENT_NEW 	= 0; //--новая
	const EVENT_APPROVE = 1; //--одобрена
	const EVENT_REJECT 	= 2; //--отклонена
	const EVENT_EXILE 	= 3; //--исключили
	
	//--роли волонтеров на мероприятиях
	const ROLE_NONE 		= 0; //--нет
	const ROLE_VOLUNTEER	= 1; //--волонтер
	const ROLE_COORDINATOR	= 2; //--координатор
	const ROLE_TEAMLEADER	= 3; //--тим.лид.

	//--статусы мероприятия.
	//const EVENT_PAST	= 'past'; //--прошедшее.
	//const EVENT_PRESENT	= 'present'; //--настоящее. Активное.
	//const EVENT_FUTURE	= 'future'; //--будущее
	//const EVENT_ALL	= 'all'; //--все	
	//const EVENT_ALL	= 0; //--все
	const EVENT_PAST	= 1; //--прошедшее.
	const EVENT_PRESENT	= 2; //--настоящее. Активное.
	const EVENT_FUTURE	= 3; //--будущее
	
	
	
	//const EVENT_CLOSED	= 1; //--закрыто мероприятие.

	
	static public function getRoles() {
        return array(
			self::ROLE_NONE	=> _('Нет'),
			self::ROLE_VOLUNTEER	=> _('Волонтер'),	
			self::ROLE_COORDINATOR	=> _('Координатор'),	
			self::ROLE_TEAMLEADER	=> _('Team Leader'),	
        );
    }
	
	static public function getMemberStatusEvents() {
        return array(
			self::EVENT_NEW		=> _('Новая'),
			self::EVENT_APPROVE	=> _('Одобрена'),
			self::EVENT_REJECT	=> _('Отклонена'),
			self::EVENT_EXILE	=> _('Исключены'),				
        );
    }
	
	
	static public function getStatusEvents() {
        return array(
			//self::EVENT_ALL		=> _('Все'),
			self::EVENT_PAST	=> _('Прошедшие'),
			self::EVENT_PRESENT	=> _('Текущие'),
			self::EVENT_FUTURE	=> _('Будущие'),				
        );
    }
	
	static public function getStatusItemEvents() {
        return array(			
			self::EVENT_PAST	=> _('Прошло'),
			self::EVENT_PRESENT	=> _('Идет'),
			self::EVENT_FUTURE	=> _('Еще не началось'),				
        );
    }
	
}


