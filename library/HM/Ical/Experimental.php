<?php
class HM_Ical_Experimental extends HM_Ical_Component_CustomAbstract
{
    /**
     * Component name.
     *
     * @var string
     */
    protected $name;

    /**
     * @param  string $name
     * @return void
     */
    public function __construct($name)
    {
        if (!HM_Ical::isXName($name)) {
            throw new HM_Ical_Parser_Exception(sprintf('"%s" is not a valid x-name', $name));
        }

        $this->name = strtoupper($name);

        parent::__construct();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

}