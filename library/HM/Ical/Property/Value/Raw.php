<?php

class HM_Ical_Property_Value_Raw extends HM_Ical_Property_Value_Abstract
{
    /**
     * String.
     *
     * @var string
     */
    protected $string;

    /**
     * Create a new raw value.
     *
     * @param  string $string
     * @return void
     */
    public function __construct($string)
    {
        $this->setValue($string);
    }

    /**
     * Set raw.
     *
     * @param  string $string
     * @return self
     */
    public function setValue($string)
    {
        $this->string = (string) $string;
        return $this;
    }

    /**
     * Get raw.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->string;
    }

    /**
     * @param  string $string
     * @return HM_Ical_Property_Value_Raw
     */
    public static function fromString($string)
    {
        return new self($string);
    }

}