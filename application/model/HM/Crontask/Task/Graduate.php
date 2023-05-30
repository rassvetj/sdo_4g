<?php
class HM_Crontask_Task_Graduate extends HM_Crontask_Task_TaskModel  implements HM_Crontask_Task_Interface
{
    public function getTaskId()
    {
        return 'assignGraduated';
    }

    public function run()
    {
        $serviceContainer = Zend_Registry::get('serviceContainer');
        $where = $serviceContainer->getService('Student')->quoteInto(array(
            'self.time_ended_planned < ? AND ',
            'Subject.period = ?'    
        ), array(
            $serviceContainer->getService('Student')->getDateTime(),
            HM_Subject_SubjectModel::PERIOD_FIXED
        ));

        $students = $serviceContainer->getService('Student')->fetchAllDependenceJoinInner('Subject', $where);

        if( count($students) ) {
            foreach ( $students as $student ) {
                $serviceContainer->getService('Subject')
                    ->assignGraduated($student->CID, $student->MID, HM_Role_GraduatedModel::STATUS_EXPIRED);
            }
        }
    }
}
