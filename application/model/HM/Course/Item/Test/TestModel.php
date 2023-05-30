<?php 

class HM_Course_Item_Test_TestModel extends HM_Course_Item_ItemModel{
    
    public function getExecuteUrl(){
        return 'test_start.php?tid=' . $this->vol1 . 
               '&ModID=' . $this->oid . 
               '&cid=' . $this->cid;
    }

}