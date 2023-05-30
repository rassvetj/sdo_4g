<?php
class HM_Ical_Property_Value_UtcOffset implements HM_Ical_Property_Value_Interface
{
    /**
     * Whether the offset is positive.
     *
     * @var boolean
     */
    protected $positive;

    /**
     * Hour offset.
     *
     * @var integer
     */
    protected $hours;

    /**
     * Minute offset.
     *
     * @var integer
     */
    protected $minutes;

    /**
     * Second offset.
     *
     * @var integer
     */
    protected $seconds;

    protected $value;

    /**
     * Create a new offset value.
     *
     * @param  boolean $positive
     * @param  integer $hours
     * @param  integer $minutes
     * @param  integer $seconds
     * @return void
     */
    public function __construct($positive, $hours, $minutes, $seconds = 0)
    {
        $this->setOffset($positive, $hours, $minutes, $seconds);
    }

    /**
     * Set offset.
     *
     * @param  boolean $positive
     * @param  integer $hours
     * @param  integer $minutes
     * @param  integer $seconds
     * @return self
     */
    public function setOffset($positive, $hours, $minutes, $seconds = 0)
    {
        $this->positive = (bool) $positive;

        if ($hours < 0 || $hours > 23) {
            throw new HM_Ical_Exception('Hours must be between 0 and 23');
        } elseif ($minutes < 0 || $minutes > 59) {
            throw new HM_Ical_Exception('Minutes must be between 0 and 59');
        } elseif ($seconds < 0 || $seconds > 59) {
            throw new HM_Ical_Exception('Seconds must be between 0 and 59');
        }

        $this->hours   = (int) $hours;
        $this->minutes = (int) $minutes;
        $this->seconds = (int) $seconds;

        return $this;
    }

    /**
     * fromString(): defined by Value interface.
     *
     * @see    Value::fromString()
     * @param  string $string
     * @return HM_Ical_Property_Value_Interface
     */
    public static function fromString($string)
    {
        if (!preg_match('(^(?<positive>[+-])(?<hours>\d{2})(?<minutes>\d{2})(?<seconds>\d{2})?$)S', $string, $match)) {
            return null;
        }

        return new self($match['positive'] === '+', $match['hours'], $match['minutes'], isset($match['seconds']) ? $match['seconds'] : 0);
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function __toString()
    {
        return ($this->positive ? '+' : '-').str_pad($this->hours, 2, '0', STR_PAD_LEFT).str_pad($this->minutes, 2, '0', STR_PAD_LEFT).str_pad($this->seconds, 2, '0', STR_PAD_LEFT);
    }

}