<?php
interface HM_Ical_Property_Value_Interface
{
    public function getValue();
    public static function fromString($string);
}