<?php
/**
 * Created by JetBrains PhpStorm.
 * Date: 12.04.12
 * Time: 14:29
 * To change this template use File | Settings | File Templates.
 */

class HM_Form_Modifier_BaseTypeSession extends HM_Form_Modifier_BaseTypePractice
{
    /**
     * @return array
     */
    protected function _getActions()
    {

        $processes = $this->getService('Process')->fetchAll(array('type = ?' => HM_Process_ProcessModel::PROCESS_ORDER, 'process_id IN (?)' => HM_Subject_SubjectModel::getSessionProcessIds()));
        $processList = array(0 => _('Без согласования')) + $processes->getList('process_id', 'name');

        return array(
            array(
                'name' => 'claimant_process_id',
                'type' => 'setOption',
                'paramName' => 'multiOptions',
                'paramValue' => $processList
            ),
//            array(
//                'name'         => 'name',
//                'type'         => 'setOptions',
//                'paramValue' => array('readonly' => true)
//            ),
            array(
                'name'         => 'code',
                'type'         => 'changeType',
                'element_type' => 'hidden'
            ),
            array(
                'name'         => 'supplier_id',
                'type'         => 'changeType',
                'element_type' => 'hidden'
            ),
            array(
                'name'         => 'type',
                'type'         => 'changeType',
                'element_type' => 'hidden'
            ),
            array(
                'name'         => 'period',
                'type'         => 'setValue',
                'value'        => HM_Subject_SubjectModel::PERIOD_DATES // не работает - переопределяется setDefaults; нужно менять там
            ),
            array(
                'name'         => 'period',
                'type'         => 'changeType',
                'element_type' => 'hidden'
            ),
            array(
                'name'         => 'begin',
                'type'         => 'setOptions',
                'paramValue' => array('required' => true)
            ),
            array(
                'name'         => 'end',
                'type'         => 'setOptions',
                'paramValue'   => array('required' => true)
            ),
            array(
                'name'         => 'longtime',
                'type'         => 'changeType',
                'element_type' => 'hidden'
            ),
            array(
                'name'         => 'auto_done',
                'type'         => 'changeType',
                'element_type' => 'hidden'
            ),
        );
    }


}