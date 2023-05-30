<?php
abstract class HM_Ical_Component_CustomAbstract extends HM_Ical_Component_Abstract
{
    /**
     * Experimental components.
     *
     * @var array
     */
    protected $experimentalComponents = array();

    /**
     * IANA components
     *
     * @var array
     */
    protected $ianaComponents = array();

    /**
     * Add an experimental component.
     *
     * @param  HM_Ical_Experimental $component
     * @return self
     */
    public function addExperimentalComponent(HM_Ical_Experimental $component)
    {
        $this->experimentalComponents[] = $component;
        return $this;
    }

    /**
     * Add an IANA component.
     *
     * @param  HM_Ical_Iana $component
     * @return self
     */
    public function addIanaComponent(HM_Ical_Iana $component)
    {
        $this->ianaComponents[] = $component;
        return $this;
    }

}