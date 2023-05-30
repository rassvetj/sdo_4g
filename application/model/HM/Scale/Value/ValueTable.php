<?php
class HM_Scale_Value_ValueTable extends HM_Db_Table
{
	protected $_name    = "scale_values";
	protected $_primary = "value_id";
	protected $_sequence = 'S_100_1_SCALE_VALUES';

    protected $_referenceMap = array(
        'Scale' => array(
            'columns'       => 'scale_id',
            'refTableClass' => 'HM_Scale_ScaleTable',
            'refColumns'    => 'scale_id',
            'propertyName'  => 'scale'
        ),
        'CriterionScaleValue' => array(
            'columns'       => 'value_id',
            'refTableClass' => 'HM_At_Criterion_ScaleValue_ScaleValueTable',
            'refColumns'    => 'value_id',
            'propertyName'  => 'criterionScaleValue'
        ),
        'EvaluationResult' => array(
            'columns'       => 'value_id',
            'refTableClass' => 'HM_At_Evaluation_Results_ResultsTable',
            'refColumns'    => 'value_id',
            'propertyName'  => 'evaluation_result'
        ),
        'EvaluationIndicator' => array(
            'columns'       => 'value_id',
            'refTableClass' => 'HM_At_Criterion_Indicator_IndicatorTable',
            'refColumns'    => 'value_id',
            'propertyName'  => 'evaluation_indicator'
        ),
    );
}