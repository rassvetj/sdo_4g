<?php
class HM_Ical_Property_Value_Text extends HM_Ical_Property_Value_Abstract
{
    /**
     * Text.
     *
     * @var string
     */
    protected $text;

    /**
     * Create a new text value.
     *
     * @param  string $text
     * @return void
     */
    public function __construct($text)
    {
        $this->setValue($text);
    }

    public function setText($text)
    {
        $this->setValue($text);
    }

    public function getText()
    {
        return $this->getValue();
    }

    /**
     * Set text.
     *
     * @param  string $text
     * @return self
     */
    public function setValue($text)
    {
        $this->text = (string) $text;
        return $this;
    }

    /**
     * Get text.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->text;
    }

    /**
     * fromString(): defined by Value interface.
     *
     * @see    Value::fromString()
     * @param  string $string
     * @return self
     */
    public static function fromString($string)
    {
        return new self($string);
    }

}