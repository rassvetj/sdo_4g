<?php
/**
 * Created by JetBrains PhpStorm.
 * Date: 12.04.12
 * Time: 14:29
 * To change this template use File | Settings | File Templates.
 */

class HM_Form_Modifier_BaseTypeBase extends HM_Form_Modifier
{
    /**
     * @return array
     */
    protected function _getActions()
    {

        $processes = $this->getService('Process')->fetchAll(array('type = ?' => HM_Process_ProcessModel::PROCESS_ORDER, 'process_id IN (?)' => HM_Subject_SubjectModel::getTrainingProcessIds()));

        $processList = $processes->getList('process_id', 'name');


//        $this->getForm()->removeDisplayGroup('subjectPeriodGroup');

        return array(
            array(
                'name' => 'claimant_process_id',
                'type' => 'setOption',
                'paramName' => 'multiOptions',
                'paramValue' => $processList
            ),
			/*
            array(
                'name'         => 'period',
                'type'         => 'setOptions',
                'paramValue' => array('disabled' => true)
            ),
			*/		
            array(
                'name'         => 'begin',
                'type'         => 'setOptions',
                'paramValue' => array('disabled' => true)
            ),

            array(
                'name'         => 'end',
                'type'         => 'setOptions',
                'paramValue' => array('disabled' => true)
            ),

            array(
                'name'         => 'longtime',
                'type'         => 'setOptions',
                'paramValue' => array('disabled' => true)
            ),

            array(
                'name'         => 'auto_done',
                'type'         => 'setOptions',
                'paramValue' => array('disabled' => true)
            ),


            array(
                'name'         => 'rooms',
                'type'         => 'setOptions',
                'paramValue' => array('disabled' => true)
            ),

        );
    }


}