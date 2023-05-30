<?php
/**
 * Created by JetBrains PhpStorm.
 * Date: 12.04.12
 * Time: 14:29
 * To change this template use File | Settings | File Templates.
 */

class HM_Form_Modifier_BaseTypePractice extends HM_Form_Modifier
{
    /**
     * @return array
     */
    protected function _getActions()
    {

        $processes = $this->getService('Process')->fetchAll(array('type = ?' => HM_Process_ProcessModel::PROCESS_ORDER, 'process_id IN (?)' => HM_Subject_SubjectModel::getSessionProcessIds()));

        $processList = $processes->getList('process_id', 'name');
        $processList = array(0 => _('Без согласования')) + $processList;

        return array(
            array(
                'name' => 'claimant_process_id',
                'type' => 'setOption',
                'paramName' => 'multiOptions',
                'paramValue' => $processList
            ),
//            array(
//                'name'         => 'rooms',
//                'type'         => 'changeType',
//                'element_type' => 'hidden'
//            ),
//            array(
//                'name'         => 'plan_users',
//                'type'         => 'changeType',
//                'element_type' => 'hidden'
//            ),
        );
    }


}