<?php
class HM_Ical_Event extends HM_Ical_Component_Abstract
{
    public function getName()
    {
        return 'VEVENT';
    }

    public function __toString()
    {
        $out = 'BEGIN:'.$this->getName().PHP_EOL;
        $out .= $this->properties()->__toString();
        $out .= 'END:'.$this->getName().PHP_EOL;

        return $out;
    }
}