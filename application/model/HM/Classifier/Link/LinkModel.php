<?php
class HM_Classifier_Link_LinkModel extends HM_Model_Abstract
{
    const TYPE_SUBJECT	= 0;
    const TYPE_RESOURCE	= 1;
    const TYPE_UNIT		= 2;

    const TYPE_PEOPLE   = 3;
    const TYPE_STRUCTURE= 4;

    const TYPE_COURSE   = 10;
    const TYPE_TEST     = 11;
    const TYPE_POLL     = 13;
    const TYPE_EXERCISE = 14;
    const TYPE_TASK     = 15;

    static public function getTypes()
    {
        $types = array(
            self::TYPE_SUBJECT	=> _('Учебные курсы'),
            self::TYPE_RESOURCE	=> _('Объекты Базы знаний'),
            self::TYPE_PEOPLE	=> _('Учетные записи'),
            self::TYPE_STRUCTURE => _('Элементы оргструктуры')
        );

        $event = new sfEvent(null, HM_Extension_ExtensionService::EVENT_FILTER_CLASSIFIER_LINK_TYPES);
        Zend_Registry::get('serviceContainer')->getService('EventDispatcher')->filter($event, $types);
        $types = $event->getReturnValue();

        return $types;
    }

    static public function getResourceTypes()
    {
        return array(
            self::TYPE_COURSE,
            self::TYPE_EXERCISE,
            self::TYPE_POLL,
            self::TYPE_TEST,
            self::TYPE_TASK
        );
    }

    static public function getUnitTypes()
    {
        return array(
            self::TYPE_UNIT,
            self::TYPE_PEOPLE,
            self::TYPE_STRUCTURE
        );
    }

    /**
     * @str String like "0 2"
     * @return String like "объекты Базы знаний<br> учетные записи<br>"
     */
    static public function IdsToNames($str){
    	$types = explode(' ', trim($str));
    	$typesAll = self::getTypes();
    	$return = array();
    	foreach($types as $type){
    	    if (isset($typesAll[$type])) {
    	        $return[] = "<p>" . str_replace(' ', '&nbsp;', $typesAll[$type]) . "</p>";
    	    }
    	}
    	if (is_array($return) && (count($return) > 1)) {
    	   $first = '<p class="total">' . Zend_Registry::get('serviceContainer')->getService('Classifier')->pluralFormCountTypes(count($return)) . '</p>';
    	   array_unshift($return, $first);
    	}

    	return implode($return);
    }
}