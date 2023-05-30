<?php
class HM_Ical_Parser
{
    protected $rawData;

    protected $stream;

    protected $ical;

    protected $components;

    protected $regex;

    public function __construct($stream)
    {
        if (!is_resource($stream)) {
            throw new HM_Ical_Parser_Exception('Stream must be as resource');
        }

        $this->stream = $stream;

        // Base regex types
        $this->regex = array(
            'iana-token'   => '[A-Za-z\d\-]+',
            'x-name'       => '[Xx]-[A-Za-z\d\-]+',
            'safe-char'    => '[\x20\x09\x21\x23-\x2B\x2D-\x39\x3C-\x7E\x80-\xFB]',
            'qsafe-char'   => '[\x20\x09\x21\x23-\x7E\x80-\xFB]',
            'tsafe-char'   => '[\x20\x21\x23-\x2B\x2D-\x39\x3C-\x5B\x5D-\x7E\x80-\xFB]',
            'value-char'   => '[\x20\x09\x21-\x7E\x80-\xFB]',
            'escaped-char' => '(?:\\\\|\\;|\\,|\\\\N|\\\\n)'
        );

        // Regex types based on base type
        $this->regex['param-text']    = '(' . $this->regex['safe-char'] . '*)';
        $this->regex['quoted-string'] = '"(' . $this->regex['qsafe-char'] . '*)"';
        $this->regex['name']          = '(?:'. $this->regex['x-name'] . '|' . $this->regex['iana-token'] . ')';
        $this->regex['param-name']    = '(?:('. $this->regex['x-name'] . ')|(' . $this->regex['iana-token'] . '))';
        $this->regex['param-value']   = '(?:'. $this->regex['quoted-string'] . '|' . $this->regex['param-text'] . ')';
        $this->regex['text']          = '((?:' . $this->regex['tsafe-char'] . '|' . $this->regex['escaped-char'] . '|[:"])*)';
        $this->regex['value']         = $this->regex['value-char'] . '*';

    }

    public function parse()
    {
        $this->ical = new HM_Ical();
        $this->components = new SplStack();

        while (null !== ($this->rawData = $this->getNextUnfoldedLine())) {
            $this->parseLine();
        }

        if (count($this->components) > 0) {
            throw new HM_Ical_Parser_Exception('Unexpected end in input stream');
        }

        return $this->ical;
    }

    public function parseLine()
    {
        $this->currentPos = 0;
        $propertyName     = $this->getPropertyName();

        if ($propertyName === 'BEGIN') {
            return $this->handleComponentBegin();
        } elseif ($propertyName === 'END') {
            return $this->handleComponentEnd();
        }
        // At this point, the property name really is a property name (Not a
        // component name), so make a new property and add it to the component.
        if (count($this->components) === 0) {
            throw new HM_Ical_Parser_Exception('Found property outside of a component');
        }

        $currentComponent = $this->components->top();

        if ($currentComponent instanceof HM_Ical_Calendar) {
            if ($propertyName == 'VERSION') {
                return $currentComponent->setVersion($this->getValue());
            } elseif ($propertyName == 'PRODID') {
                return $currentComponent->setProductId($this->getValue());
            } elseif ($propertyName == 'CALSCALE') {
                return $currentComponent->setCalendarScale($this->getValue());
            }

        }

        $property = new HM_Ical_Property($propertyName);

        if ($currentComponent instanceof HM_Ical_Experimental || $currentComponent instanceof HM_Ical_Iana) {
            $valueTypes = null;
        } else {
            $valueTypes = HM_Ical_Property::getValueTypesFromName($propertyName);
        }
        // Handle parameter values
        while ($this->rawData[$this->currentPos - 1] !== ':') {
            $parameterName  = $this->getNextParameterName();
            $parameterValue = $this->getNextParameterValue();

            $property->setParameter($parameterName, new HM_Ical_Property_Value_Text($parameterValue));
        }

        // Handle property values
        if ($valueTypes === null) {
            // Contents of experimental and IANA components are treated as raw values.
            $value = new HM_Ical_Property_Value_Raw($this->getValue());
        } else {
            // Check if an alternate value type is specified
            $valueType = $property->getParameter('VALUE');

            if ($valueType !== null) {
                $valueType = strtoupper($valueType->getText());

                if (!in_array($valueType, $valueTypes)) {
                    throw new HM_Ical_Parser_Exception(sprintf('A disallowed value type "%s" was specified for property %', $valueType, $propertyName));
                }
            } else {
                $valueType = $valueTypes[0];
            }

            switch ($propertyName) {
                default:
                    $value = $this->createPropertyValue($propertyName, $this->getValue(), $valueType);
                    break;
            }
        }

        $this->components->top()->properties()->add($property->setValue($value));


    }

    /**
     * Handle the beginning of a component.
     *
     * @return void
     */
    protected function handleComponentBegin()
    {
        $componentName = strtoupper($this->getValue());
        if (count($this->components) > 0) {
            $currentComponent = $this->components->top();

            if ($currentComponent instanceof HM_Ical_Experimental || $currentComponent instanceof HM_Ical_Iana) {
                if (HM_Ical::isXName($componentName)) {
                    $componentType = HM_Ical_Component_Abstract::COMPONENT_EXPERIMENTAL;
                } elseif (HM_Ical::isIanaToken($componentName)) {
                    $componentType = HM_Ical_Component_Abstract::COMPONENT_IANA;
                } else {
                    $componentType = HM_Ical_Component_Abstract::COMPONENT_NONE;
                }
            } else {
                $componentType = HM_Ical_Component_Abstract::getTypeFromName($componentName);
            }
        } else {
            $componentType = HM_Ical_Component_Abstract::getTypeFromName($componentName);
        }

        if ($componentType === HM_Ical_Component_Abstract::COMPONENT_NONE) {
            throw new HM_Ical_Parser_Exception(sprintf('"%s" is not a valid component name', $componentName));
        } elseif ($componentType === HM_Ical_Component_Abstract::COMPONENT_EXPERIMENTAL) {
            $component = new HM_Ical_Experimental($componentName);
        } elseif ($componentType === HM_Ical_Component_Abstract::COMPONENT_IANA) {
            $component = new HM_Ical_Iana($componentName);
        } else {
            $className = 'HM_Ical_' . $componentType;
            $component = new $className();
        }

        if ($componentType === 'Calendar') {
            if (count($this->components) > 0) {
                throw new HM_Ical_Parser_Exception('VCALENDAR component found inside another component');
            }

            $this->ical->addCalendar($component);
        } else {
            if (count($this->components) === 0) {
                throw new HM_Ical_Parser_Exception(sprintf('%s Component found outside of VCALENDAR component', $componentName));
            }

            // Assume that it could be added to the current component, and set
            // it to false if it cold not.
            $addedToComponent = true;

            if ($currentComponent instanceof HM_Ical_Experimental || $currentComponent instanceof HM_Ical_Iana) {
                if ($componentType === HM_Ical_Component_Abstract::COMPONENT_EXPERIMENTAL) {
                    $currentComponent->addExperimentalComponent($component);
                } elseif ($currentComponent === HM_Ical_Component_Abstract::COMPONENT_IANA) {
                    $currentComponent->addIanaComponent($component);
                } else {
                    $addedToComponent = false;
                }
            } else {
                switch ($currentComponent->getName()) {

                    case 'VTIMEZONE':
                        if ($component instanceof HM_Ical_Component_AbstractOffset) {
                            $currentComponent->addOffset($component);
                        } else {
                            $addedToComponent = false;
                        }
                        break;

                    case 'VCALENDAR':
                        switch ($componentType) {
                            case 'Timezone':
                                $currentComponent->addTimezone($component);
                                break;

                            case HM_Ical_Component_Abstract::COMPONENT_EXPERIMENTAL:
                                $currentComponent->addExperimentalComponent($component);
                                break;

                            case HM_Ical_Component_Abstract::COMPONENT_IANA:
                                $currentComponent->addIanaComponent($component);
                                break;

                            default:
                                $addedToComponent = false;
                                break;
                        }
                        break;

                    default:
                        $addedToComponent = false;
                        break;
                }
            }

            if (!$addedToComponent) {
                throw new HM_Ical_Parser_Exception(sprintf('%s component found inside %s component', $componentName, $currentComponent->getName()));
            }
        }

        $this->components->push($component);
    }

    /**
     * Handle the ending of a component.
     *
     * @return void
     */
    protected function handleComponentEnd()
    {
        if (count($this->components) === 0) {
            throw new HM_Ical_Parser_Exception('Found component ending tag without a beginning tag');
        }

        $currentComponent = $this->components->pop();
        $componentName    = strtoupper($this->getValue());

        if ($componentName !== $currentComponent->getName()) {
            throw new HM_Ical_Parser_Exception(sprintf('Ending tag does not match current component'));
        }
    }


    /**
     * Get a property name.
     *
     * @return string
     */
    protected function getPropertyName()
    {
        if (!preg_match(
            '(\G(?<name>' . $this->regex['name'] . ')[;:])S',
            $this->rawData, $match, 0, $this->currentPos
        )) {
            throw new HM_Ical_Parser_Exception('Could not find a property name, component BEGIN or END tag');
        }

        $this->currentPos += strlen($match[0]);

        return strtoupper($match['name']);
    }

    /**
     * Get the value of a property.
     *
     * @param  string $kind
     * @return string
     */
    protected function getValue()
    {
        if (!preg_match(
            '(\G(?<value>' . $this->regex['value'] . ')\r\n)S',
            $this->rawData, $match, 0, $this->currentPos
        )) {
            throw new HM_Ical_Parser_Exception('Could not find a property value');
        }

        $this->currentPos += strlen($match[0]);

        return $match['value'];
    }

    /**
     * Get the next value of a property.
     *
     * A property may have multiple values, if the values are separated by
     * commas in the content line.
     *
     * @param  string $kind
     * @return string
     * @todo   Handle escaped commas properly
     */
    protected function getNextValue($kind = null)
    {
        if (!preg_match(
            '(\G(?<value>' . $this->regex['value'] . ')(?:,|\r\n))S',
            $this->rawData, $match, 0, $this->currentPos
        )) {
            throw new HM_Ical_Parser_Exception('Could not find next property value');
        }

        $this->currentPos += strlen($match[0]);

        return $match['value'];
    }

    /**
     * Get the next parameter name.
     *
     * @return string
     */
    protected function getNextParameterName()
    {
        if (!preg_match(
            '(\G(?<name>' . $this->regex['name'] . ')=)S',
            $this->rawData, $match, 0, $this->currentPos
        )) {
            throw new HM_Ical_Parser_Exception('Could not find a parameter name');
        }

        $this->currentPos += strlen($match[0]);

        return $match['name'];
    }

    /**
     * Get the next parameter value.
     *
     * @return string
     */
    protected function getNextParameterValue()
    {
        if (!preg_match(
            '(\G(?<value>' . $this->regex['param-value'] . ')[,:])S',
            $this->rawData, $match, 0, $this->currentPos
        )) {
            throw new HM_Ical_Parser_Exception('Could not find a parameter value');
        }

        $this->currentPos += strlen($match[0]);

        return $match['value'];
    }

    /**
     * Create a property value.
     *
     * @param  string $propertyName
     * @param  string $string
     * @param  string $valueType
     * @return Value\AbstractValue
     */
    protected function createPropertyValue($propertyName, $string, $valueType)
    {
        $value     = null;
        $className = 'HM_Ical_Property_Value_' . $valueType;

        if (null === ($value = $className::fromString($string))) {
            throw new HM_Ical_Parser_Exception(sprintf('Value of property %s doesn\'t match %s type', $propertyName, $valueType));
        }

        return $value;
    }


    /**
     * Get the next unfolded line from the stream.
     *
     * @return string
     */
    protected function getNextUnfoldedLine()
    {
        if (feof($this->stream)) {
            return null;
        }

        $rawData = $this->buffer . fgets($this->stream);

        while (
            !feof($this->stream) && ($this->buffer = fgetc($this->stream))
            && ($this->buffer === ' ' || $this->buffer === "\t")
        ) {
            $rawData      = rtrim($rawData, "\r\n") . fgets($this->stream);
            $this->buffer = '';
        }

        return $rawData;
    }
}