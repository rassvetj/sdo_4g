<?php
class HM_Ical_Timezone extends HM_Ical_Component_Abstract
{
    /**
     * Aliases for timezones which are not in the TZ database anymore.
     *
     * @var array
     */
    protected static $timezoneAliases = array(
        'Asia/Katmandu'                    => 'Asia/Kathmandu',
        'Asia/Calcutta'                    => 'Asia/Kolkata',
        'Asia/Saigon'                      => 'Asia/Ho_Chi_Minh',
        'Africa/Asmera'                    => 'Africa/Asmara',
        'Africa/Timbuktu'                  => 'Africa/Bamako',
        'Atlantic/Faeroe'                  => 'Atlantic/Faroe',
        'Atlantic/Jan_Mayen'               => 'Europe/Oslo',
        'America/Argentina/ComodRivadavia' => 'America/Argentina/Catamarca',
        'America/Louisville'               => 'America/Kentucky/Louisville',
        'Europe/Belfast'                   => 'Europe/London',
        'Pacific/Yap'                      => 'Pacific/Truk',
    );

    /**
     * Offsets.
     *
     * @var array
     */
    protected $offsets = array();

    /**
     * getName(): defined by AbstractComponent.
     *
     * @see    AbstractComponent::getName()
     * @return string
     */
    public function getName()
    {
        return 'VTIMEZONE';
    }

    /**
     * Create a Timezone component from a timezone ID.
     *
     * @param  string $timezoneId
     * @return HM_Ical_Timezone
     */
    public static function fromTimezoneId($timezoneId)
    {
        if (isset(self::$timezoneAliases[$timezoneId])) {
            $filename = self::$timezoneAliases[$timezoneId];
        } else {
            $filename = $timezoneId;
        }

        $ical     = HM_Ical::fromUri(__DIR__ . '/Data/Timezones/' . $filename . '.ics');
        $timezone = $ical->getCalendar()->getTimezone($filename);

        if ($timezone->getPropertyValue('TZID') !== $timezoneId) {            
            $timezone->properties()->get('TZID')->setText($timezoneId);
        }

        return $timezone;
    }

    /**
     * Add an offset to the timezone.
     *
     * @param  HM_Ical_Component_AbstractOffset $offset
     * @return self
     */
    public function addOffset(HM_Ical_Component_AbstractOffset $offset)
    {
        $this->offsets[] = $offset;
        return $this;
    }

    /**
     * Set offsets.
     *
     * $offsets must either be an instance of 'Standard' or 'Daylight', or an
     * array consiting of at least one 'Standard' and 'Daylight' component.
     *
     * @param  mixed $offsets
     * @return self
     */
    public function setOffsets($offsets)
    {
        if ($offsets instanceof HM_Ical_Component_AbstractOffset) {
            $offsets = array($offsets);
        } elseif (!is_array($offsets)) {
            throw new HM_Ical_Exception('Offset is no instance of AbstractOffsetComponent, nor an array');
        }

        $this->offsets = array();

        foreach ($offsets as $offset) {
            if (!$offsets instanceof HM_Ical_Component_AbstractOffset) {
                throw new HM_Ical_Exception('Offset is no instance of AbstractOffsetComponent');
            }

            $this->offsets[] = $offset;
        }

        return $this;
    }

    /**
     * Get offsets.
     *
     * @return array
     */
    public function getOffsets()
    {
        return $this->offsets;
    }

    public function __toString()
    {
        $out = 'BEGIN:'.$this->getName().PHP_EOL;
        $out .= $this->properties()->__toString();
        foreach($this->getOffsets() as $offset) {
            $out .= $offset->__toString();
        }
        $out .= 'END:'.$this->getName().PHP_EOL;
        return $out;
    }

}