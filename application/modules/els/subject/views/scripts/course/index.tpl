<div style="padding-right: 10px;">
    <?php if ($this->course->format == HM_Course_CourseModel::FORMAT_FREE):?>
        <?php echo $this->headSwitcher(array(
                                           'module' => 'subject',
                                           'controller' => 'course',
                                           'action' => 'index',
                                           'switcher' => 'index',
                                           'subject_id' => (int) $this->subjectId,
                                           'course_id' => (int) $this->courseId,
                                           'owner' => $this->course->chain
        ), 'course');?>
    <?php endif;?>
    <?php echo $this->action('index', 'index', 'course', array('subject_id' => $this->subjectId, 'course_id' => $this->courseId, 'withoutContextMenu' => true));?>
</div>