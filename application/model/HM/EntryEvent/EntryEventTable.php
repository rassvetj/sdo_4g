<?php
# Нужен для фиксации принятия согласия на участие в определенном мероприятии.
class HM_EntryEvent_EntryEventTable extends HM_Db_Table
{
    protected $_name = "entry_events";
    protected $_primary = "entry_id";
    protected $_sequence = "";

    protected $_referenceMap = array(
    );

    public function getDefaultOrder()
    {
        return array('entry_events.entry_id ASC');
    }
}