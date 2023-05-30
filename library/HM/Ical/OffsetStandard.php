<?php
class HM_Ical_OffsetStandard extends HM_Ical_Component_AbstractOffset
{
    public function getName()
    {
        return 'STANDARD';
    }

    public function __toString()
    {
        $out = 'BEGIN:'.$this->getName().PHP_EOL;
        $out .= $this->properties()->__toString();
        $out .= 'END:'.$this->getName().PHP_EOL;
        return $out;
    }
}