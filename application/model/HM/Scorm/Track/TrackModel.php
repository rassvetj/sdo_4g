<?php

class HM_Scorm_Track_TrackModel extends HM_Model_Abstract
{
    private $_trackData = null;

    public function __construct($data)
    {
        if (isset($data['trackdata']) && strlen($data['trackdata'])) {
            $this->_trackData = new HM_Scorm_Track_Data_DataModel(unserialize($data['trackdata']));
        }
        parent::__construct($data);
    }


    public function getData()
    {
        if ((null == $this->_trackData) && strlen($this->trackdata)) {
            $this->_trackData = new HM_Scorm_Track_Data_DataModel(unserialize($this->trackdata));
        }

        if (null == $this->_trackData) {
            $this->_trackData = new HM_Scorm_Track_Data_DataModel(array());
        }

        return $this->_trackData;
    }

    public function getDataValue($name)
    {
        $data = $this->getData();
        if (isset($data->{$name})) return $data->{$name};
        return null;
    }

    public function setData(HM_Scorm_Track_Data_DataModel $data)
    {
        $this->trackdata = '';
        $values = $data->getValues();
        if (is_array($values) && count($values)) {
            $this->trackdata = serialize($values);
        }
    }
}