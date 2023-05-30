<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';
class HM_View_Infoblock_ActivitiesBlock extends HM_View_Infoblock_ScreenForm
{

    protected $id = 'activitiesblock';
    
    public function activitiesBlock($title = null, $attribs = null, $options = null)
    {
        $subject = false;
        if (isset($options['subjectId'])) {
            $subject = Zend_Registry::get('serviceContainer')->getService('Subject')->getOne(
                Zend_Registry::get('serviceContainer')->getService('Subject')->find($options['subjectId'])
            );
            $this->view->subject = 'subject';
            $this->view->subject_id = $options['subjectId'];
        }
//         if (isset($options['course_id'])) {
//             $subject = Zend_Registry::get('serviceContainer')->getService('Course')->getOne(
//                 Zend_Registry::get('serviceContainer')->getService('Course')->find($options['course_id'])
//             );
//             $this->view->subject = 'course';
//             $this->view->subject_id = $options['course_id'];
//         }
//         if (isset($options['resource_id'])) {
//             $subject = Zend_Registry::get('serviceContainer')->getService('Resource')->getOne(
//                 Zend_Registry::get('serviceContainer')->getService('Resource')->find($options['resource_id'])
//             );
//             $this->view->subject = 'resource';
//             $this->view->subject_id = $options['resource_id'];
//         }
        
        $activities = array();
        $urls = HM_Activity_ActivityModel::getTabUrls();
        if ($subject) {
            $names = HM_Activity_ActivityModel::getTabActivities();
            foreach($names as $activityId => $activityName) {
                if (($subject->services & $activityId)) {
                    
                    //if($activityId == HM_Activity_ActivityModel::ACTIVITY_FORUM){
                    //     $activities[$activityId] = array('id' => $activityId, 'name' => $activityName, 'url' => '');
                    //}else{
                        $activities[$activityId] = array('id' => $activityId, 'name' => $activityName, 'url' => $urls[$activityId]);
                    //}
                }
            }
        }
        
        $isModerator = Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_TEACHER, HM_Role_RoleModelAbstract::ROLE_DEAN));
        if (!count($activities) && !$isModerator) return null;
        
        $this->view->isModerator = $isModerator;
        $this->view->activities = $activities;
        $this->view->options = $options;
        
        $content = $this->view->render('activitiesBlock.tpl');

        return parent::screenForm($title, $content, $attribs);
    }
}