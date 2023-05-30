<div id="schedule-daily">
<div id="schedule-daily-wrapper-1">
<?php if ($this->subjects):
    if ($this->lessonCount > $this->lessonLimit): ?>
        <div>Слишком много занятий для отображения в виджете.<br/>
        Показано <?=$this->lessonLimit?> записей из <?=$this->lessonCount?>.</div><br/>
    <?php endif;
    $isStudent = Zend_Registry::get('serviceContainer')->getService('Acl')
                                                       ->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')
                                                                                                            ->getCurrentUserRole(),
                                                                      array(HM_Role_RoleModelAbstract::ROLE_ENDUSER));
    ?>
    <?php foreach($this->subjects as $subjectId => $subject): ?>
        <?php $url = Zend_Registry::get('serviceContainer')->getService('Subject')->getDefaultUri($subject['subject_id']);?>
        <?php ksort($subject['lessons'])?>
        <div class="schedule-daily-subject">
        	<a href="<?php echo $url;?>?page_id=m0602"><?php echo $subject['title']?></a>
        </div>
        <div>
			<?php if (count($subject['lessons'])):?>
			    <?php foreach($subject['lessons'] as $lesson):?>
			        <?php if ($lesson instanceof HM_Lesson_LessonModel):?>
                    <?php
                         if (isset($this->students[$lesson->SHEID])) {
                             $lesson->students = $this->students[$lesson->SHEID];
                         }
                    ?>
			        <?php echo $this->lessonPreview($lesson, $this->titles, 'lesson-preview-min')?>
			        <?php endif;?>
			    <?php endforeach;?>
			<?php else:?>
			    <?php echo _('Отсутствуют данные для отображения')?>
			<?php endif;?>
            
        </div>
    <?php endforeach;?>
<?php else:?>
    <div align="center"><?php echo _('Отсутствуют данные для отображения')?></div>
<?php endif;?>
</div>
<!--hr style="clear: both">
<div align="right">
    <?php echo sprintf(_('Расписание на другой день: %s'), $this->DatePicker('infoblock-schedule-daily-begin', $this->begin, array('showOn' => 'button','dateFormat'=>'dd.mm.yy','onSelect' => new Zend_Json_Expr('function() {reloadscheduledaily()}'))))?>
</div-->
</div>

<?php
if (!$this->ajax) {
    $this->inlineScript()->captureStart();
?>
function reloadscheduledaily() {
    $('#schedule-daily #schedule-daily-wrapper-1').load('/infoblock/schedule/index/begin/'+$('#infoblock-schedule-daily-begin').val().replace('.','-').replace('.','-').replace('.','-'));
}

<?php
    $this->inlineScript()->captureEnd();
}
?>