<?php

class HM_Infoblock_Fact_FactTable extends HM_Db_Table
{
    protected $_name = "interesting_facts";
    protected $_primary = "interesting_facts_id";
    protected $_sequence = "S_100_INTERESTING_FACTS";

  

    public function getDefaultOrder()
    {
        return array('interesting_facts.interesting_facts_id');
    }
}