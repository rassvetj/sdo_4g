<?php
class HM_DataSheet_Header
{
    private $_name = null;
    private $_title = null;
    private $_enableCheckbox = false;
    private $_fields = array();

    public function __construct($name, $title = null, $enableCheckBox = false, $fields = array())
    {
        $this->_name = $name;
        $this->_title = $title;
        $this->_enableCheckbox = $enableCheckBox;
        if (is_array($fields) && count($fields)) {
            foreach($fields as $fieldId => $field) {
                if (!isset($field['render'])) {
                    $field['render'] = 'text';
                }

                $this->_fields[$fieldId] = new HM_DataSheet_Header_Field($field['title'], $field['render'], $field);
            }
        }
    }

    public function setName($name)
    {
        $this->_name = $name;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function setTitle($title)
    {
        $this->_title = $title;
    }

    public function getTitle()
    {
        return $this->_title;
    }

    public function enableCheckbox()
    {
        $this->_enableCheckbox = true;
    }

    public function disableCheckbox()
    {
        $this->_enableCheckbox = false;
    }

    public function isCheckboxEnabled()
    {
        return $this->_enableCheckbox;
    }

    public function setFields($fields = array())
    {
        $this->_fields = $fields;
    }

    public function getFields()
    {
        return $this->_fields;
    }

    public function getField($id)
    {
        if (isset($this->_fields[$id])) {
            return $this->_fields[$id];
        }
        return null;
    }

    public function getFieldsCount()
    {
        return count($this->_fields);
    }
}