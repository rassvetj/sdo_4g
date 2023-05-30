<?php
class HM_Ical_OffsetDaylight extends HM_Ical_Component_AbstractOffset
{
    public function getName()
    {
        return 'DAYLIGHT';
    }

    public function __toString()
    {
        $out = 'BEGIN:'.$this->getName().PHP_EOL;
        $out .= $this->properties()->__toString();
        $out .= 'END:'.$this->getName().PHP_EOL;
        return $out;    	
    }
}