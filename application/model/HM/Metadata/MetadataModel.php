<?php
class HM_Metadata_MetadataModel extends HM_Model_Abstract
{

    const SEPARATOR_ITEM  = '~|~';
    const SEPARATOR_VALUE = '~=~';

    public function getString()
    {
        $string = '';
        foreach($this->getValues() as $name => $value) {
            $string .= $name.self::SEPARATOR_VALUE.$value.self::SEPARATOR_ITEM;
        }

        return $string;
    }

    public function parseString($string)
    {
        $fields = explode(self::SEPARATOR_ITEM, $string);
        if (count($fields)) {
            foreach($fields as $field) {
                $parts = explode(self::SEPARATOR_VALUE, $field);
                if (count($parts) == 2) {
                    $parts[0] = trim($parts[0]);
                    $parts[1] = trim($parts[1]);
                    $this->{$parts[0]} = $parts[1];
                }
            }
        }
    }

    
}