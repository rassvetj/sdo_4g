<?php
interface HM_Adapter_Interface
{
    public function fetchAll($where = null, $order = null, $count = null, $offset = null);
}