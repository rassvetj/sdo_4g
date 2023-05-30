<?php

class HM_Formula_FormulaTable extends HM_Db_Table
{
    protected $_name = "formula";
    protected $_primary = "id";
    protected $_sequence = "S_27_1_FORMULA";

    /*protected $_dependentTables = array(
        "HM_Role_StudentTable",
        "HM_Course_Item_ItemTable",
        "HM_Module_Test_TestTable"
    );*/

    protected $_referenceMap = array(
        'Subject' => array(
            'columns'       => 'id',
            'refTableClass' => 'HM_Subject_SubjectTable',
            'refColumns'    => 'formula_id',
            'propertyName'  => 'subject',
        ),

    );

    public function getDefaultOrder()
    {
        return array('formula.name ASC');
    }
}