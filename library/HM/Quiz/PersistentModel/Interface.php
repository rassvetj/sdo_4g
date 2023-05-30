<?php

interface HM_Quiz_PersistentModel_Interface
{
    public function getModel();
    public function setupModel();
    public function getItems();
    public function setItems($items);
    public function getResults();
    public function setResults($itemId, $results);
    public function getCurrentItem();
    public function setCurrentItem($itemId);
}
