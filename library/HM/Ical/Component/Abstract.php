<?php
abstract class HM_Ical_Component_Abstract
{
    /**
     * Component types.
     */
    const COMPONENT_NONE         = 0;
    const COMPONENT_EXPERIMENTAL = 1;
    const COMPONENT_IANA         = 2;

    /**
     * Map of component names to componen types.
     *
     * @var array
     */
    protected static $nameToTypeMap = array(
        'VCALENDAR' => 'Calendar',
        'VALARM'    => 'Alarm',
        'VTIMEZONE' => 'Timezone',
        'STANDARD'  => 'OffsetStandard',
        'DAYLIGHT'  => 'OffsetDaylight',
        'VEVENT'    => 'Event',
        'VTODO'     => 'Todo',
        'VJOURNAL'  => 'JournalEntry',
        'VFREEBUSY' => 'FreeBusyTime'
    );

    /**
     * Properties.
     *
     * @var PropertyList
     */
    protected $properties;

    /**
     * Create a new component.
     *
     * @return void
     */
    public function __construct()
    {
        $this->properties = new HM_Ical_PropertyList();
    }

    /**
     * Get all properties.
     *
     * @return HM_ICal_PropertyList
     */
    public function properties()
    {
        return $this->properties;
    }

    /**
     * Get the iCalendar conforming component name.
     *
     * It is important that the returned name is uppercased.
     *
     * @return string
     */
    abstract public function getName();

    /**
     * Get component type from name.
     *
     * @param string $name
     */
    public static function getTypeFromName($name)
    {
        if (!isset(self::$nameToTypeMap[$name])) {
            if (HM_Ical::isXName($name)) {
                return self::COMPONENT_EXPERIMENTAL;
            } elseif (HM_Ical::isIanaToken($name)) {
                return self::COMPONENT_IANA;
            } else {
                return self::COMPONENT_NONE;
            }
        }

        return self::$nameToTypeMap[$name];
    }

    /**
     * Get the value of a single instance property.
     *
     * @param  string $name
     * @return mixed
     */
    public function getPropertyValue($name)
    {
        $property = $this->properties()->get($name);

        if ($property === null) {
            return null;
        }

        $value = $property->getValue();

        if ($value instanceof HM_Ical_Property_Value_Text) {
            return $value->getText();
        }

        return null;
    }

}