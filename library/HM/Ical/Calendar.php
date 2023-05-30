<?php
class HM_Ical_Calendar extends HM_Ical_Component_Abstract
{

    const DEFAULT_VERSION = '2.0';
    const DEFAULT_CALENDARSCALE = 'GREGORIAN';
    const DEFAULT_PRODID = '-//Hypermethod//NONSGML HM_Ical//RU';

    protected $version = null;

    protected $calendarScale = null;

    protected $productId = null;

    protected $timezones = null;

    protected $events = null;

    /**
     * getName(): defined by HM_Ical_Component_Abstract.
     *
     * @see    HM_Ical_Component_Abstract::getName()
     * @return string
     */
    public function getName()
    {
        return 'VCALENDAR';
    }

    /**
     * Set product ID.
     *
     * @param  HM_Ical_Property $productId
     * @return self
     */
    public function setProductId($productId)
    {
        if (!$productId instanceof HM_Ical_Property) {
            $productId = new HM_Ical_Property('PRODID', HM_Ical_Property_Value_Text::fromString($productId));
        }

        $this->productId = $productId;
        return $this;
    }

    /**
     * Get product ID.
     *
     * @return HM_Ical_Property
     */
    public function getProductId()
    {
        if ($this->productId === null) {
            $this->productId = new HM_Ical_Property('PRODID', HM_Ical_Property_Value_Text::fromString(self::DEFAULT_PRODID));
        }

        return $this->productId;
    }


    /**
     * Set calendar version.
     *
     * @param  mixed $version
     * @return self
     */
    public function setVersion($version)
    {
        if (!$version instanceof HM_Ical_Property) {
            $version = new HM_Ical_Property('VERSION', HM_Ical_Property_Value_Text::fromString($version));
        }

        $this->version = $version;
        return self;
    }

    /**
     * Get version property.
     *
     * @return HM_Ical_Property
     */
    public function getVersion()
    {
        if ($this->version === null) {
            $this->version = new HM_Ical_Property('VERSION', new HM_Ical_Property_Value_Text(self::DEFAULT_VERSION));
        }

        return $this->version;
    }

    /**
     * Set calendar scale.
     *
     * @param  mixed $calendarScale
     * @return self
     */
    public function setCalendarScale($calendarScale = null)
    {
        if ($calendarScale !== null && !$calendarScale instanceof HM_Ical_Property) {
            $calendarScale = new HM_Ical_Property('CALSCALE', HM_Ical_Property_Value_Text::fromString($calendarScale));
        }

        $this->calendarScale = $calendarScale;
        return $this;
    }

    /**
     * Get calendar scale.
     *
     * @return Property\CalendarScale
     */
    public function getCalendarScale()
    {
        if (null === $this->calendarScale) {
            $this->calendarScale = new HM_Ical_Property('CALSCALE', HM_Ical_Property_Value_Text::fromString(self::DEFAULT_CALENDARSCALE));
        }
        return $this->calendarScale;
    }

    /**
     * Set method.
     *
     * @param  mixed $method
     * @return self
     */
    public function setMethod($method = null)
    {
        if ($method !== null && !$method instanceof HM_Ical_Property) {
            $method = new HM_Ical_Property('METHOD', HM_Ical_Property_Value_Text::fromString($method));
        }

        $this->method = $method;
        return $this;
    }

    /**
     * Get method.
     *
     * @return Property\Method
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Add an event.
     *
     * @param  HM_Ical_Event $event
     * @return string
     */
    public function addEvent(HM_Ical_Event $event)
    {
        //$uid = $event->getUid()->getUid();
        $this->events[] = $event;
        //return $uid;
    }

    public function getEvents()
    {
        return $this->events;
    }

    /**
     * Add a timezone.
     *
     * @param  HM_Ical_Timezone $timezone
     * @return self
     */
    public function addTimezone(HM_Ical_Timezone $timezone)
    {
        $this->timezones[] = $timezone;
        return $this;
    }

    /**
     * Get a timezone.
     *
     * @param  string $timezoneId
     * @return HM_Ical_Timezone
     */
    public function getTimezone($timezoneId)
    {
        foreach ($this->timezones as $timezone) {
            if ($timezone->getPropertyValue('TZID') === $timezoneId) {
                return $timezone;
            }
        }

        return null;
    }

    /**
     * Remove a timezone.
     *
     * @param  string $timezoneId
     * @return self
     */
    public function removeTimezone($timezoneId)
    {
        foreach ($this->timezones as $key => $timezone) {
            if ($timezone->getPropertyValue('TZID') === $timezoneId) {
                unset($this->timezones[$key]);
                break;
            }
        }

        return $this;
    }

    public function __toString()
    {
        $out = 'BEGIN:'.$this->getName().PHP_EOL;
        $out .= $this->getProductId()->__toString();
        $out .= $this->getVersion()->__toString();
        $out .= $this->getCalendarScale()->__toString();
        $out .= $this->properties()->__toString();

        if (is_array($this->timezones)) {
            foreach($this->timezones as $timezone) {
                $out .= $timezone->__toString();
            }
        }

        if (is_array($this->events)) {
            foreach($this->events as $event) {
                $out .= $event->__toString();
            }
        }

        $out .= 'END:'.$this->getName().PHP_EOL;

        return $out;
    }
}