<?php

class HM_Lesson_Journal_JournalTable extends HM_Db_Table
{
    protected $_name 	= "schedule_journal";
    protected $_primary = "journal_id";
    

    public function getDefaultOrder()
    {
        return array('schedule_journal.journal_id ASC');
    }
}