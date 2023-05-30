<?php
class HM_Ical
{

    /**
     * List of calendars.
     *
     * @var array
     */
    protected $calendars = array();

    /**
     * Check if a string is an IANA token.
     *
     * @param  string $string
     * @return boolean
     */
    public static function isIanaToken($string)
    {
        return (bool) preg_match('(^[A-Za-z\d\-]+$)S', $string);
    }

    /**
     * Check if a string is an X-Name.
     *
     * @param  string $string
     * @return boolean
     */
    public static function isXName($string)
    {
        return (bool) preg_match('(^[Xx]-[A-Za-z\d\-]+$)S', $string);
    }

    /**
     * Add a calendar.
     *
     * @param HM_Ical_Calendar $calendar
     * @return void
     */
    public function addCalendar(HM_Ical_Calendar $calendar)
    {
        $this->calendars[] = $calendar;
    }

    /**
     * Get a calendar with a specific index.
     *
     * Usually, an Ical object will only consist of a single calendar, so the
     * default value for $index is 0.
     *
     * @param  integer $index
     * @return HM_Ical_Calendar
     */
    public function getCalendar($index = 0)
    {
        if (isset($this->calendars[$index])) {
            return $this->calendars[$index];
        }

        return null;
    }

    public static function fromUri($uri)
    {
        $parser = new HM_Ical_Parser(fopen($uri, 'r'));

        return $parser->parse();
    }

    public function __toString()
    {
        $out = '';
        if (is_array($this->calendars)) {
            foreach($this->calendars as $cal) {
                $out .= $cal->__toString();
            }
        }

        return $out;
    }

}