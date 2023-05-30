<?php
class HM_Ical_PropertyList
{
    /**
     * Properties.
     *
     * @var array
     */
    protected $properties = array();

    /**
     * Add a property.
     *
     * @param  HM_Ical_Property $property
     * @return self
     */
    public function add(HM_Ical_Property $property)
    {
        $name = $property->getName();
        $hash = spl_object_hash($property);

        if (!isset($this->properties[$name])) {
            $this->properties[$name] = array();
        }

        $this->properties[$name][$hash] = $property;

        return $this;
    }

    /**
     * Set a property.
     *
     * @param  Property $property
     * @return self
     */
    public function set(HM_Ical_Property $property)
    {
        return $this->removeAll($property->getName())->add($property);
    }

    /**
     * Remove a single property.
     *
     * @param  Property $property
     * @return self
     */
    public function remove(HM_Ical_Property $property)
    {
        $name = $property->getName();
        $hash = spl_object_hash($property);

        if (isset($this->properties[$name])) {
            if (isset($this->properties[$name][$hash])) {
                unset($this->properties[$name][$hash]);

                if (count($this->properties[$name]) === 0) {
                    unset($this->properties[$name]);
                }
            }
        }

        return $this;
    }

    /**
     * Remove all properties of a specific name.
     *
     * @param  string $name
     * @return self
     */
    public function removeAll($name)
    {
        if (isset($this->properties[$name])) {
            unset($this->properties[$name]);
        }

        return $this;
    }

    /**
     * Clears the list of all properties.
     *
     * @return self
     */
    public function clear()
    {
        $this->properties = array();

        return $this;
    }

    /**
     * Get a single property of a specific name.
     *
     * @param  string $name
     * @return HM_Ical_Property
     */
    public function get($name)
    {
        if (isset($this->properties[$name])) {
            return reset($this->properties[$name]);
        }

        return null;
    }

    /**
     * Get all properties of a specific name.
     *
     * @param  string $name
     * @return array
     */
    public function getAll($name)
    {
        if (isset($this->properties[$name])) {
            return $this->properties[$name];
        }

        return array();
    }

    public function __toString()
    {
        $out = '';
        if (is_array($this->properties) && count($this->properties)) {
            foreach($this->properties as $hash) {
                if (is_array($hash) && count($hash)) {
                    foreach($hash as $property) {
                        $out .= $property->__toString();
                    }
                }
            }
        }

        return $out;
    }

}