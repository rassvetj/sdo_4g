<?php
class HM_Scorm_Report_ReportService extends HM_Service_Abstract
{
    public function storeReport($post, $userId, $lessonId)
    {
        if (
            !$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER)
        //    !in_array($this->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_STUDENT))
        ) return true;

        // todo: не факт что оно в 1251
//        $post = iconv('windows-1251', 'UTF-8', $post);

        if ($report = $this->isUserReportExists($userId, $lessonId)) {

            $report->updated = $this->getDateTime();
            $report->report_data = $post;

            return $this->update($report->getValues());
        }

        return $this->insert(
            array(
                'mid' => $userId,
                'lesson_id' => $lessonId,
                'report_data' => $post,
                'updated' => $this->getDateTime(),
             )
        );
    }

    public function isUserReportExists($userId, $lessonId)
    {
        $report = $this->getOne(
            $this->fetchAll(
                $this->quoteInto(
                    array('mid = ?', ' AND lesson_id = ?'),
                    array($userId, $lessonId)
                )
            )
        );

        return $report;
    }

}