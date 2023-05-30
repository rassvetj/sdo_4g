<?php

class HM_Scorm_Track_TrackTable extends HM_Db_Table
{
    protected $_name = "scorm_tracklog";
    protected $_primary = "trackID";

    public function getDefaultOrder()
    {
        return array('scorm_tracklog.trackID ASC');
    }
}