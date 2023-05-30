<?php
abstract class HM_Ical_Component_AbstractOffset extends HM_Ical_Component_Abstract
{
    public function setStart($dateTime)
    {
        $property = $this->properties()->get('DTSTART');

        if ($property === null) {
            $property = new Property('DTSTART');
            $property->setValue(HM_Ical_Property_Value_DateTime::fromString($dateTime));
            $this->properties()->add($property);
        } elseif ($property->getValue() instanceof HM_Ical_Property_Value_DateTime) {
            $property->setValue(HM_Ical_Property_Value_DateTime::fromString($dateTime));
        } else {
            throw new HM_Ical_Exception('Value type of DTSTART property is not DateTime');
        }
    }

    public function getStart()
    {
        return $this->properties()->get('DTSTART');
    }

    public function getOffsetFrom()
    {
        return $this->properties()->get('TZOFFSETFROM');
    }

    public function getOffsetTo()
    {
        return $this->properties()->get('TZOFFSETTO');
    }

}