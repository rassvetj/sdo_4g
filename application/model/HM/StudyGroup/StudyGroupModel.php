<?php
class HM_StudyGroup_StudyGroupModel extends HM_Model_Abstract
{
    const TYPE_CUSTOM = 1;
    const TYPE_AUTO = 2;

    public static function getTypes()
    {
        return array(
            self::TYPE_CUSTOM => _('Ручная'),
            self::TYPE_AUTO => _('Автоматическая')
        );
    }

    public function getType()
    {
        $types = self::getTypes();
        return $types[$this->type];
    }
	
	public function getName()
	{
		return $this->name;
	}
	
	public function getId()
	{
		return $this->group_id;
	}
}