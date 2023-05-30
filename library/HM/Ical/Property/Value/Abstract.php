<?php
abstract class HM_Ical_Property_Value_Abstract implements HM_Ical_Property_Value_Interface
{
    public function __toString()
    {
        return $this->getValue();
    }
}