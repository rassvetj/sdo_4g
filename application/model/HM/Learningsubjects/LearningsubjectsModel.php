<?php
class HM_Learningsubjects_LearningsubjectsModel extends HM_Model_Abstract
{
	const MODULE_CODE_FILTER_YES	= 1;
	const MODULE_CODE_FILTER_NO		= 2;
	
	public static function getModuleCodeFilterList()
	{
		return array(
			self::MODULE_CODE_FILTER_YES => _('Да'),
			self::MODULE_CODE_FILTER_NO  => _('Нет'),
		);
	}
    
}