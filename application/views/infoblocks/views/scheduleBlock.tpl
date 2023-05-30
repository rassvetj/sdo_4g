<span id="infoblock-schedule">
<div align="center">
    <?php echo sprintf(_('Расписание с %s по %s'), $this->DatePicker('infoblock-schedule-begin', $this->begin, array('showOn' => 'button','onSelect' => new Zend_Json_Expr('function() {reloadSchedule()}'))), $this->DatePicker('infoblock-schedule-end', $this->end, array('showOn' => 'button', 'onSelect' => new Zend_Json_Expr('function() {reloadSchedule()}'))))?>
</div>
<?php if ($this->subjects):?>
    <table border="0" widht="100%" class="infoblock-schedule-table">
    <?php foreach($this->subjects as $subjectId => $subject):?>
        <?php ksort($subject['lessons'])?>
        <tr><td class="infoblock-schedule-subject"><a href="<?php echo $this->url(array('module' => 'lesson', 'controller' => 'list', 'action' => 'my', 'subject_id' => $subject['subject_id']), null, true)?>?page_id=m0602"><?php echo $subject['title']?></a></td></tr>
        <?php foreach($subject['lessons'] as $lesson):?>
            <tr>
                <td class="infoblock-schedule-dates">
                    <?php echo sprintf(_('%s - %s'), date('d.m.y', strtotime($lesson->begin)), date('d.m.y', strtotime($lesson->end)))?>
                </td>
                <td class="infoblock-schedule-lesson">
                    <a <?php if ($lesson->overdue):?>class="overdue"<?php endif;?> href="<?php echo $this->url(array('module' => 'lesson', 'controller' => 'execute', 'action' => 'index', 'lesson_id' => $lesson->SHEID), null, true)?>"><?php echo $lesson->title?></a>
                </td>
            </tr>
        <?php endforeach;?>
    <?php endforeach;?>
    </table>
<?php else:?>
    <div align="center"><?php echo _('Отсутствуют данные для отображения')?></div>
<?php endif;?>
</span>

<?php
if (!$this->ajax) {
    $this->inlineScript()->captureStart();
?>
function reloadSchedule() {
    $('#scheduleBlock #infoblock-schedule').load('/infoblock/schedule/index/begin/'+$('#infoblock-schedule-begin').val().replace('.','-').replace('.','-').replace('.','-')+'/end/'+$('#infoblock-schedule-end').val().replace('.','-').replace('.','-').replace('.','-'));
}

<?php
    $this->inlineScript()->captureEnd();
}
?>